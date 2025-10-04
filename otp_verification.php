<?php
session_start();
include_once __DIR__ . "/connect.php";   // ✅ correct file
include_once __DIR__ . "/email_config.php";  // ✅ for PHPMailer + sendOTPEmail

$message = "";

// --- Handle OTP request ---
if (isset($_POST['request_otp'])) {
    $email = trim($_POST['email']);

    if ($email === "") {
        $message = "<div class='alert error'>Please enter a valid email.</div>";
    } else {
        // generate OTP
        $otp = rand(100000, 999999);
        $expires = date("Y-m-d H:i:s", strtotime("+10 minutes"));

        // insert OTP into DB
        $stmt = $conn->prepare("INSERT INTO otp_verifications (email, otp, expires_at) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $email, $otp, $expires);
        $stmt->execute();

        // send OTP email
        if (sendOTPEmail($email, "User", $otp)) {
            $_SESSION['otp_email'] = $email;
            $message = "<div class='alert success'>Verification code sent to {$email}</div>";
        } else {
            $message = "<div class='alert error'>Failed to send email. Try again.</div>";
        }
    }
}

// --- Handle OTP verification ---
if (isset($_POST['verify_otp'])) {
    $enteredOtp = trim($_POST['otp']);
    $email = $_SESSION['otp_email'] ?? '';

    if ($enteredOtp === "" || $email === "") {
        $message = "<div class='alert error'>Invalid request. Please try again.</div>";
    } else {
        $stmt = $conn->prepare("SELECT * FROM otp_verifications WHERE email = ? AND otp = ? AND expires_at > NOW() ORDER BY id DESC LIMIT 1");
        $stmt->bind_param("ss", $email, $enteredOtp);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows > 0) {
            $message = "<div class='alert success'>Email verified successfully!</div>";
            // ✅ here you can update users table or redirect
            unset($_SESSION['otp_email']);
        } else {
            $message = "<div class='alert error'>Invalid or expired OTP. Please try again.</div>";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>OTP Verification</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f5f5f5; }
        .container { max-width: 400px; margin: 50px auto; padding: 20px;
                     background: white; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.2); }
        h2 { text-align: center; }
        .alert { padding: 10px; margin: 10px 0; border-radius: 5px; }
        .alert.success { background: #d4edda; color: #155724; }
        .alert.error { background: #f8d7da; color: #721c24; }
        input[type=text], input[type=email] { width: 100%; padding: 10px; margin: 5px 0 15px; border: 1px solid #ccc; border-radius: 5px; }
        button { width: 100%; padding: 10px; border: none; border-radius: 5px; background: #3b82f6; color: white; font-weight: bold; cursor: pointer; }
        button:hover { background: #2563eb; }
    </style>
</head>
<body>
    <div class="container">
        <h2>OTP Verification</h2>
        <?php if ($message) echo $message; ?>

        <!-- Request OTP -->
        <form method="POST">
            <input type="email" name="email" placeholder="Enter your email" required>
            <button type="submit" name="request_otp">Send Verification Code</button>
        </form>

        <hr>

        <!-- Verify OTP -->
        <form method="POST">
            <input type="text" name="otp" placeholder="Enter OTP" required>
            <button type="submit" name="verify_otp">Verify OTP</button>
        </form>
    </div>
</body>
</html>
