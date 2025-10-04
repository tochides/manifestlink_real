<?php
session_start();
include_once __DIR__ . '/connect.php';
include_once __DIR__ . '/email_config.php'; // Your PHPMailer functions

// Only handle POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['email'])) {
    $email = trim($_POST['email']);
    
    if (!$email) {
        $message = '<div class="alert error">Please enter your email.</div>';
    } else {
        // Step 1: Check if user exists
        $stmt = $conn->prepare("SELECT id, full_name FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $res = $stmt->get_result();
        
        if ($row = $res->fetch_assoc()) {
            $user_id = $row['id'];
            $user_name = $row['full_name'];

            // Step 2: Delete any previous OTP for this user
            $del = $conn->prepare("DELETE FROM otp_verification WHERE user_id = ?");
            $del->bind_param("i", $user_id);
            $del->execute();
            $del->close();

            // Step 3: Generate new OTP
            $otp = rand(100000, 999999);
            $expires_at = date("Y-m-d H:i:s", strtotime("+10 minutes"));
            $created_at = date("Y-m-d H:i:s");
            $updated_at = $created_at;

            $ins = $conn->prepare("INSERT INTO otp_verification (user_id, email, otp, expires_at, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?)");
            $ins->bind_param("isssss", $user_id, $email, $otp, $expires_at, $created_at, $updated_at);
            
            if ($ins->execute()) {
                $_SESSION['otp_email'] = $email;
                $_SESSION['otp_user_id'] = $user_id;

                // Step 4: Send OTP via email
                if (sendOTPEmail($email, $user_name, $otp)) {
                    $message = '<div class="alert success">✅ OTP sent successfully! Check your email.</div>';
                } else {
                    $message = '<div class="alert error">❌ Failed to send OTP email. Check server logs.</div>';
                }
            } else {
                $message = '<div class="alert error">❌ Database error inserting OTP.</div>';
            }
            $ins->close();
        } else {
            $message = '<div class="alert error">❌ No user found with this email.</div>';
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>OTP Verification - ManifestLink</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link rel="stylesheet" href="css/styles.css">
<style>
/* ... your CSS as before ... */
.otp-container { max-width: 500px; margin: 50px auto; padding: 2rem; background: white; border-radius: 16px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1), 0 10px 10px -5px rgba(0,0,0,0.04); }
.otp-header { text-align: center; margin-bottom: 2rem; }
.otp-header img { width: 80px; height: 80px; margin-bottom: 1rem; }
.otp-header h1 { color: #1f2937; font-size: 1.875rem; font-weight: 700; margin-bottom: 0.5rem; }
.otp-header p { color: #6b7280; font-size: 1rem; }
.otp-form { margin-bottom: 2rem; }
.form-group { margin-bottom: 1.5rem; }
.form-group label { display: block; margin-bottom: 0.5rem; color: #374151; font-weight: 500; }
.form-group input { width: 100%; padding: 0.75rem 1rem; border: 2px solid #e5e7eb; border-radius: 8px; font-size: 1rem; transition: all 0.3s ease; }
.form-group input:focus { outline: none; border-color: #3b82f6; box-shadow: 0 0 0 3px rgba(59,130,246,0.1); }
.btn { width: 100%; padding: 0.875rem 1.5rem; border: none; border-radius: 8px; font-size: 1rem; font-weight: 600; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 0.5rem; transition: all 0.3s ease; }
.btn-primary { background: #3b82f6; color: white; }
.btn-primary:hover { background: #2563eb; transform: translateY(-1px); }
.alert { padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.75rem; }
.alert.success { background: #f0fdf4; color: #16a34a; border: 1px solid #bbf7d0; }
.alert.error { background: #fef2f2; color: #dc2626; border: 1px solid #fecaca; }
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

    <form method="POST" class="otp-form">
        <div class="form-group">
            <label for="email"><i class="fas fa-envelope"></i> Email Address</label>
            <input type="email" id="email" name="email" required placeholder="Enter your registered email">
        </div>
        <button type="submit" class="btn btn-primary">
            <i class="fas fa-paper-plane"></i> Send Verification Code
        </button>
    </form>

    <div class="back-link">
        <a href="index.html"><i class="fas fa-arrow-left"></i> Back to Home</a>
    </div>
</div>
</body>
</html>
