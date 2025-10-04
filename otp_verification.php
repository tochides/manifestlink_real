<?php
include_once 'connect.php';
include_once 'email_config.php'; // PHPMailer setup

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $otp   = rand(100000, 999999); // 6-digit OTP
    $expires_at = date("Y-m-d H:i:s", strtotime("+10 minutes"));
    $created_at = date("Y-m-d H:i:s");
    $updated_at = $created_at;

    if (empty($email)) {
        echo "❌ Email is required.";
        exit;
    }

    // Remove old OTPs for this email
    $stmt = $conn->prepare("DELETE FROM otp_verification WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->close();

    // Insert new OTP
    $stmt = $conn->prepare("INSERT INTO otp_verification (user_id, email, otp, expires_at, created_at, updated_at) 
                            VALUES (?, ?, ?, ?, ?, ?)");
    $user_id = 0; // default, or fetch actual user_id if available
    $stmt->bind_param("isssss", $user_id, $email, $otp, $expires_at, $created_at, $updated_at);
    $stmt->execute();
    $stmt->close();

    // Send email with PHPMailer
    try {
        $mail->addAddress($email);
        $mail->Subject = "Your OTP Code";
        $mail->Body    = "Your OTP code is: <b>$otp</b><br>It will expire in 10 minutes.";

        if ($mail->send()) {
            echo "✅ OTP sent successfully to $email.";
        } else {
            echo "❌ Failed to send OTP Email.";
        }
    } catch (Exception $e) {
        echo "❌ Mailer Error: " . $mail->ErrorInfo;
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
        /* same CSS as before */
        .otp-container { max-width: 500px; margin: 50px auto; padding: 2rem; background: white; border-radius: 16px;
            box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1), 0 10px 10px -5px rgba(0,0,0,0.04); }
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
                <input type="email" id="email" name="email" required placeholder="Enter your registered email">
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
                <input type="text" id="otp" name="otp" class="otp-input" maxlength="6" required placeholder="000000" pattern="[0-9]{6}">
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
</body>
</html>
