<?php
session_start();
include 'connect.php';
include 'email_config.php'; // SendGrid config

$message = '';
$email = '';

// ------------------------
// Handle OTP request
// ------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['request_otp'])) {
    $email = trim($_POST['email'] ?? '');
    
    if (empty($email)) {
        $message = '<div class="alert error"><i class="fas fa-exclamation-circle"></i> Please enter your email address.</div>';
    } else {
        // Check if user exists
        $stmt = $conn->prepare("SELECT id, full_name, email FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            
            // Generate OTP
            $otp = sprintf("%06d", mt_rand(100000, 999999));
            $expires = date('Y-m-d H:i:s', strtotime('+10 minutes'));
            
            // Store OTP
            $otpStmt = $conn->prepare("
                INSERT INTO otp_verification (user_id, email, otp, expires_at) 
                VALUES (?, ?, ?, ?) 
                ON DUPLICATE KEY UPDATE otp = ?, expires_at = ?
            ");
            $otpStmt->bind_param("isssss", $user['id'], $email, $otp, $expires, $otp, $expires);
            
            if ($otpStmt->execute()) {
                // Send OTP with SendGrid
                if (sendOTPEmail($email, $user['full_name'], $otp)) {
                    $_SESSION['otp_email'] = $email;
                    $message = '<div class="alert success"><i class="fas fa-check-circle"></i> Verification code sent to your email!</div>';
                } else {
                    $message = '<div class="alert error"><i class="fas fa-exclamation-circle"></i> Failed to send verification code. Please try again.</div>';
                }
            } else {
                $message = '<div class="alert error"><i class="fas fa-exclamation-circle"></i> Error generating verification code. Please try again.</div>';
            }
            $otpStmt->close();
        } else {
            $message = '<div class="alert error"><i class="fas fa-exclamation-circle"></i> No account found with this email address.</div>';
        }
        $stmt->close();
    }
}

// ------------------------
// Handle OTP verification
// ------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['verify_otp'])) {
    $email = $_SESSION['otp_email'] ?? '';
    $otp = trim($_POST['otp'] ?? '');
    
    if (empty($email) || empty($otp)) {
        $message = '<div class="alert error"><i class="fas fa-exclamation-circle"></i> Please enter the verification code.</div>';
    } else {
        $userStmt = $conn->prepare("SELECT id, full_name FROM users WHERE email = ?");
        $userStmt->bind_param("s", $email);
        $userStmt->execute();
        $userResult = $userStmt->get_result();
        
        if ($userResult->num_rows > 0) {
            $user = $userResult->fetch_assoc();
            $user_id = $user['id'];
            
            // Check OTP
            $stmt = $conn->prepare("SELECT * FROM otp_verification WHERE user_id = ? AND otp = ?");
            $stmt->bind_param("is", $user_id, $otp);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $verification = $result->fetch_assoc();
                
                if (strtotime($verification['expires_at']) > time()) {
                    // Has QR?
                    $qrStmt = $conn->prepare("SELECT qr_image_path FROM qr_codes WHERE user_id = ?");
                    $qrStmt->bind_param("i", $user_id);
                    $qrStmt->execute();
                    $qrResult = $qrStmt->get_result();
                    
                    if ($qrResult->num_rows > 0) {
                        // Clear OTP + redirect
                        $clearStmt = $conn->prepare("DELETE FROM otp_verification WHERE user_id = ? AND otp = ?");
                        $clearStmt->bind_param("is", $user_id, $otp);
                        $clearStmt->execute();
                        $clearStmt->close();
                        
                        unset($_SESSION['otp_email']);
                        header('Location: generatedqr.php?user_id=' . $user_id . '&verified=1');
                        exit();
                    } else {
                        $message = '<div class="alert error"><i class="fas fa-exclamation-circle"></i> No QR code found for this account.</div>';
                    }
                    $qrStmt->close();
                } else {
                    $message = '<div class="alert error"><i class="fas fa-exclamation-circle"></i> Verification code expired. Request a new one.</div>';
                }
            } else {
                $message = '<div class="alert error"><i class="fas fa-exclamation-circle"></i> Invalid verification code.</div>';
            }
            $stmt->close();
        } else {
            $message = '<div class="alert error"><i class="fas fa-exclamation-circle"></i> User not found. Please request a new code.</div>';
        }
        $userStmt->close();
    }
}

// ------------------------
// Pre-fill email from GET
// ------------------------
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['email'])) {
    $email = trim($_GET['email']);
    if (!empty($email)) {
        $_SESSION['prefill_email'] = $email;
    }
}

// ------------------------
// Helper: Send OTP email
// ------------------------
function sendOTPEmail($toEmail, $toName, $otp) {
    global $SENDGRID_API_KEY, $EMAIL_FROM, $EMAIL_FROM_NAME;
    
    if (!$SENDGRID_API_KEY) return false;

    $email = new \SendGrid\Mail\Mail();
    $email->setFrom($EMAIL_FROM, $EMAIL_FROM_NAME);
    $email->setSubject("Your ManifestLink Verification Code");
    $email->addTo($toEmail, $toName);
    
    // HTML content
    $email->addContent("text/html", "
        <html>
        <body>
            <p>Hello {$toName},</p>
            <p>Your verification code is: <strong>{$otp}</strong></p>
            <p>This code expires in 10 minutes.</p>
            <p>If you didn't request this code, please ignore this email.</p>
        </body>
        </html>
    ");

    $sendgrid = new \SendGrid($SENDGRID_API_KEY);
    try {
        $response = $sendgrid->send($email);
        return $response->statusCode() >= 200 && $response->statusCode() < 300;
    } catch (Exception $e) {
        error_log("SendGrid Error: " . $e->getMessage());
        return false;
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
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link rel="stylesheet" href="css/styles.css">
<style>
/* Your existing styles here (unchanged) */
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
    <form method="POST" class="otp-form">
        <div class="form-group">
            <label for="email"><i class="fas fa-envelope"></i> Email Address</label>
            <input type="email" id="email" name="email"
                   value="<?php echo htmlspecialchars($_SESSION['prefill_email'] ?? $email ?? ''); ?>"
                   required placeholder="Enter your registered email">
        </div>
        <button type="submit" name="request_otp" class="btn btn-primary">
            <i class="fas fa-paper-plane"></i> Send Verification Code
        </button>
    </form>
    <?php else: ?>
    <form method="POST" class="otp-form">
        <div class="form-group">
            <label for="otp"><i class="fas fa-key"></i> Verification Code</label>
            <input type="text" id="otp" name="otp" class="otp-input" maxlength="6" required placeholder="000000" pattern="[0-9]{6}">
            <small style="color: #6b7280; margin-top: 0.5rem; display: block;">
                Enter the 6-digit code sent to <?php echo htmlspecialchars($_SESSION['otp_email']); ?>
            </small>
        </div>
        <button type="submit" name="verify_otp" class="btn btn-primary">
            <i class="fas fa-check"></i> Verify & Access QR Code
        </button>
    </form>
    <div class="resend-section">
        <p>Didn't receive the code?</p>
        <form method="POST" style="display: inline;">
            <input type="hidden" name="email" value="<?php echo htmlspecialchars($_SESSION['otp_email']); ?>">
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
// Auto-focus OTP input
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
