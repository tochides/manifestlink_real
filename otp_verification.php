<?php
session_start();

include_once __DIR__ . "/connect.php";
include_once __DIR__ . "/email_config.php";

$message = "";

// Request OTP
if (isset($_POST['request_otp'])) {
    $email = trim($_POST['email'] ?? '');

    if ($email === '') {
        $message = "<div class='alert error'>Email is required.</div>";
    } else {
        // Generate OTP
        $otp = rand(100000, 999999);
        $expires_at = date("Y-m-d H:i:s", strtotime("+10 minutes"));

        // Delete old
        $del = $conn->prepare("DELETE FROM otp_verifications WHERE email = ?");
        $del->bind_param("s", $email);
        $del->execute();

        // Insert new
        $stmt = $conn->prepare("INSERT INTO otp_verifications (email, otp, expires_at) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $email, $otp, $expires_at);
        $stmt->execute();

        // Send email
        sendOTPEmail($email, "User", $otp);

        $_SESSION['otp_email'] = $email;
        $message = "<div class='alert success'>OTP sent to <b>$email</b>. It expires in 10 minutes.</div>";
    }
}

// Verify OTP
if (isset($_POST['verify_otp'])) {
    $email = $_SESSION['otp_email'] ?? '';
    $otp   = trim($_POST['otp'] ?? '');

    if ($email === '' || $otp === '') {
        $message = "<div class='alert error'>Please enter the OTP.</div>";
    } else {
        $stmt = $conn->prepare("SELECT * FROM otp_verifications WHERE email = ? AND otp = ? AND expires_at > NOW()");
        $stmt->bind_param("ss", $email, $otp);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {
            // Delete used OTP
            $del = $conn->prepare("DELETE FROM otp_verifications WHERE email = ?");
            $del->bind_param("s", $email);
            $del->execute();

            // ✅ Redirect after success
            header("Location: qr_page.php"); 
            exit;
        } else {
            $message = "<div class='alert error'>Invalid or expired OTP.</div>";
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
