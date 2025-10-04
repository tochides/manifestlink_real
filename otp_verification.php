<?php
session_start();

require __DIR__ . '/vendor/autoload.php';
include 'connect.php';
include 'email_config.php'; // Gmail PHPMailer

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$message = '';
$email = '';

// --- Function to send OTP ---
function sendOTPEmail($toEmail, $toName, $otp) {
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = EMAIL_FROM;
        $mail->Password   = EMAIL_APP_PASSWORD;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        $mail->setFrom(EMAIL_FROM, EMAIL_FROM_NAME);
        $mail->addAddress($toEmail, $toName);

        $mail->isHTML(true);
        $mail->Subject = 'Your ManifestLink OTP Code';
        $mail->Body    = "
            <h2>Hello, {$toName}</h2>
            <p>Your One-Time Password (OTP) for accessing your QR code is:</p>
            <div style='font-size:22px; font-weight:bold; color:#2563eb;'>{$otp}</div>
            <p>This code will expire in 10 minutes.</p>
            <p>If you did not request this, please ignore this email.</p>
        ";

        return $mail->send();
    } catch (Exception $e) {
        error_log("Email Error: {$mail->ErrorInfo}");
        return false;
    }
}

// --- Handle OTP request ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['request_otp'])) {
    $email = trim($_POST['email'] ?? '');

    if (empty($email)) {
        $message = '<div class="alert error">Please enter your email address.</div>';
    } else {
        $stmt = $conn->prepare("SELECT id, full_name FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();

            $otp = sprintf("%06d", mt_rand(100000, 999999));
            $expires = date('Y-m-d H:i:s', strtotime('+10 minutes'));

            $otpStmt = $conn->prepare("
                INSERT INTO otp_verification (user_id, email, otp, expires_at) 
                VALUES (?, ?, ?, ?) 
                ON DUPLICATE KEY UPDATE otp = ?, expires_at = ?
            ");
            $otpStmt->bind_param("isssss", $user['id'], $email, $otp, $expires, $otp, $expires);

            if ($otpStmt->execute()) {
                if (sendOTPEmail($email, $user['full_name'], $otp)) {
                    $_SESSION['otp_email'] = $email;
                    $message = '<div class="alert success">Verification code sent to your email!</div>';
                } else {
                    $message = '<div class="alert error">Failed to send verification code.</div>';
                }
            } else {
                $message = '<div class="alert error">Error generating verification code.</div>';
            }
            $otpStmt->close();
        } else {
            $message = '<div class="alert error">No account found with this email.</div>';
        }
        $stmt->close();
    }
}

// --- Handle OTP verification ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['verify_otp'])) {
    $email = $_SESSION['otp_email'] ?? '';
    $otp = trim($_POST['otp'] ?? '');

    if (empty($email) || empty($otp)) {
        $message = '<div class="alert error">Please enter the verification code.</div>';
    } else {
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $userResult = $stmt->get_result();

        if ($userResult->num_rows > 0) {
            $user = $userResult->fetch_assoc();
            $user_id = $user['id'];

            $otpStmt = $conn->prepare("SELECT * FROM otp_verification WHERE user_id = ? AND otp = ?");
            $otpStmt->bind_param("is", $user_id, $otp);
            $otpStmt->execute();
            $result = $otpStmt->get_result();

            if ($result->num_rows > 0) {
                $verification = $result->fetch_assoc();

                if (strtotime($verification['expires_at']) > time()) {
                    // ✅ Clear OTP & redirect
                    $clear = $conn->prepare("DELETE FROM otp_verification WHERE user_id = ?");
                    $clear->bind_param("i", $user_id);
                    $clear->execute();

                    unset($_SESSION['otp_email']);

                    // Smooth redirect (instead of white screen)
                    echo "<script>alert('OTP verified successfully! Redirecting...'); 
                          window.location='generatedqr.php?user_id={$user_id}&verified=1';</script>";
                    exit;
                } else {
                    $message = '<div class="alert error">Verification code expired.</div>';
                }
            } else {
                $message = '<div class="alert error">Invalid verification code.</div>';
            }
            $otpStmt->close();
        } else {
            $message = '<div class="alert error">User not found. Request a new code.</div>';
        }
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OTP Verification - ManifestLink</title>
    <link rel="icon" type="image/png" href="logo.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/styles.css">
    <style>
        .otp-container {
            max-width: 500px;
            margin: 50px auto;
            padding: 2rem;
            background: white;
            border-radius: 16px;
            box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1), 0 10px 10px -5px rgba(0,0,0,0.04);
        }
        .otp-header { text-align: center; margin-bottom: 2rem; }
        .otp-header img { width: 80px; height: 80px; margin-bottom: 1rem; }
        .otp-header h1 { color: #1f2937; font-size: 1.875rem; font-weight: 700; margin-bottom: 0.5rem; }
        .otp-header p { color: #6b7280; font-size: 1rem; }
        .otp-form { margin-bottom: 2rem; }
        .form-group { margin-bottom: 1.5rem; }
        .form-group label { display: block; margin-bottom: 0.5rem; color: #374151; font-weight: 500; }
        .form-group input { width: 100%; padding: 0.75rem 1rem; border: 2px solid #e5e7eb; border-radius: 8px; font-size: 1rem; transition: all 0.3s ease; }
        .form-group input:focus { outline: none; border-color: #3b82f6; box-shadow: 0 0 0 3px rgba(59,130,246,0.1); }
        .otp-input { text-align: center; font-size: 1.5rem; font-weight: 600; letter-spacing: 0.5rem; }
        .btn { width: 100%; padding: 0.875rem 1.5rem; border: none; border-radius: 8px; font-size: 1rem; font-weight: 600; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 0.5rem; transition: all 0.3s ease; }
        .btn-primary { background: #3b82f6; color: white; }
        .btn-primary:hover { background: #2563eb; transform: translateY(-1px); }
        .btn-secondary { background: #6b7280; color: white; }
        .btn-secondary:hover { background: #4b5563; }
        .alert { padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.75rem; }
        .alert.success { background: #f0fdf4; color: #16a34a; border: 1px solid #bbf7d0; }
        .alert.error { background: #fef2f2; color: #dc2626; border: 1px solid #fecaca; }
        .resend-section { text-align: center; margin-top: 1.5rem; padding-top: 1.5rem; border-top: 1px solid #e5e7eb; }
        .resend-section p { color: #6b7280; margin-bottom: 1rem; }
        .back-link { text-align: center; margin-top: 2rem; }
        .back-link a { color: #3b82f6; text-decoration: none; font-weight: 500; }
        .back-link a:hover { text-decoration: underline; }
    </style>
</head>
<body>
<div class="otp-container">
    <div class="otp-header">
        <img src="logo.png" alt="ManifestLink Logo">
        <h1>Access Your QR Code</h1>
        <p>Enter your email to receive a verification code</p>
    </div>

    <?php if (!empty($message)) echo $message; ?>

    <?php if (!isset($_SESSION['otp_email'])): ?>
        <!-- Request OTP Form -->
        <form method="POST" class="otp-form">
            <div class="form-group">
                <label for="email"><i class="fas fa-envelope"></i> Email Address</label>
                <input type="email" id="email" name="email"
                       value="<?php echo htmlspecialchars($_SESSION['prefill_email'] ?? '', ENT_QUOTES); ?>"
                       required placeholder="Enter your registered email">
            </div>
            <button type="submit" name="request_otp" class="btn btn-primary">
                <i class="fas fa-paper-plane"></i> Send Verification Code
            </button>
        </form>
    <?php else: ?>
        <!-- Verify OTP Form -->
        <form method="POST" class="otp-form">
            <div class="form-group">
                <label for="otp"><i class="fas fa-key"></i> Verification Code</label>
                <input type="text" id="otp" name="otp" class="otp-input" maxlength="6" required
                       placeholder="000000" pattern="[0-9]{6}">
                <small style="color: #6b7280; display:block; margin-top:0.5rem;">
                    Enter the 6-digit code sent to <?php echo htmlspecialchars($_SESSION['otp_email'], ENT_QUOTES); ?>
                </small>
            </div>
            <button type="submit" name="verify_otp" class="btn btn-primary">
                <i class="fas fa-check"></i> Verify & Access QR Code
            </button>
        </form>
        <div class="resend-section">
            <p>Didn't receive the code?</p>
            <form method="POST" style="display:inline;">
                <input type="hidden" name="email" value="<?php echo htmlspecialchars($_SESSION['otp_email'], ENT_QUOTES); ?>">
                <button type="submit" name="request_otp" class="btn btn-secondary">
                    <i class="fas fa-redo"></i> Resend Code
                </button>
            </form>
        </div>
    <?php endif; ?>

    <div class="back-link">
        <a href="index.html"><i class="fas fa-arrow-left"></i> Back to Home</a>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const otpInput = document.getElementById('otp');
    if (otpInput) {
        otpInput.focus();
        otpInput.addEventListener('input', function() {
            this.value = this.value.replace(/[^0-9]/g, '').substring(0, 6);
        });
    }
});
</script>
</body>
</html>
