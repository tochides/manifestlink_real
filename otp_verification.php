<?php
session_start();
include 'connect.php';
include 'email_config.php';

$message = '';
$email = '';

// Handle OTP request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['request_otp'])) {
    $email = trim($_POST['email']);
    
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
            
            // Store OTP in database
            $otpStmt = $conn->prepare("INSERT INTO otp_verification (user_id, email, otp, expires_at) VALUES (?, ?, ?, ?) ON DUPLICATE KEY UPDATE otp = ?, expires_at = ?");
            $otpStmt->bind_param("isssss", $user['id'], $email, $otp, $expires, $otp, $expires);
            
            if ($otpStmt->execute()) {
                // Send OTP email using the email configuration
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

// Handle OTP verification
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['verify_otp'])) {
    $email = $_SESSION['otp_email'] ?? '';
    $otp = trim($_POST['otp']);
    
    if (empty($email) || empty($otp)) {
        $message = '<div class="alert error"><i class="fas fa-exclamation-circle"></i> Please enter the verification code.</div>';
    } else {
        // First, get the user ID for this email
        $userStmt = $conn->prepare("SELECT id, full_name FROM users WHERE email = ?");
        $userStmt->bind_param("s", $email);
        $userStmt->execute();
        $userResult = $userStmt->get_result();
        
        if ($userResult->num_rows > 0) {
            $user = $userResult->fetch_assoc();
            $user_id = $user['id'];
            
            // Verify OTP using the same logic as test_otp.php
            $stmt = $conn->prepare("SELECT * FROM otp_verification WHERE user_id = ? AND otp = ?");
            $stmt->bind_param("is", $user_id, $otp);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $verification = $result->fetch_assoc();
                
                // Check if expired using PHP time comparison
                if (strtotime($verification['expires_at']) > time()) {
                    // Check if user has QR code
                    $qrStmt = $conn->prepare("SELECT qr_image_path FROM qr_codes WHERE user_id = ?");
                    $qrStmt->bind_param("i", $user_id);
                    $qrStmt->execute();
                    $qrResult = $qrStmt->get_result();
                    
                    if ($qrResult->num_rows > 0) {
                        // Clear OTP and redirect to QR code
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
                    $message = '<div class="alert error"><i class="fas fa-exclamation-circle"></i> Verification code has expired. Please request a new one.</div>';
                }
            } else {
                $message = '<div class="alert error"><i class="fas fa-exclamation-circle"></i> Invalid verification code. Please check and try again.</div>';
            }
            $stmt->close();
        } else {
            $message = '<div class="alert error"><i class="fas fa-exclamation-circle"></i> User not found. Please request a new verification code.</div>';
        }
        $userStmt->close();
    }
}

// Handle direct email access (from register.php lookup)
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['email'])) {
    $email = trim($_GET['email']);
    if (!empty($email)) {
        // Pre-fill the email field
        $_SESSION['prefill_email'] = $email;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OTP Verification - ManifestLink</title>
    <meta name="description" content="Verify your email to access your QR code.">
    <link rel="icon" type="image/png" href="logo.png">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/styles.css">
    
    <style>
        .otp-container {
            max-width: 500px;
            margin: 50px auto;
            padding: 2rem;
            background: white;
            border-radius: 16px;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }
        
        .otp-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .otp-header img {
            width: 80px;
            height: 80px;
            margin-bottom: 1rem;
        }
        
        .otp-header h1 {
            color: #1f2937;
            font-size: 1.875rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }
        
        .otp-header p {
            color: #6b7280;
            font-size: 1rem;
        }
        
        .otp-form {
            margin-bottom: 2rem;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: #374151;
            font-weight: 500;
        }
        
        .form-group input {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            font-size: 1rem;
            transition: all 0.3s ease;
        }
        
        .form-group input:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }
        
        .otp-input {
            text-align: center;
            font-size: 1.5rem;
            font-weight: 600;
            letter-spacing: 0.5rem;
        }
        
        .btn {
            width: 100%;
            padding: 0.875rem 1.5rem;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }
        
        .btn-primary {
            background: #3b82f6;
            color: white;
        }
        
        .btn-primary:hover {
            background: #2563eb;
            transform: translateY(-1px);
        }
        
        .btn-secondary {
            background: #6b7280;
            color: white;
        }
        
        .btn-secondary:hover {
            background: #4b5563;
        }
        
        .alert {
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        
        .alert.success {
            background: #f0fdf4;
            color: #16a34a;
            border: 1px solid #bbf7d0;
        }
        
        .alert.error {
            background: #fef2f2;
            color: #dc2626;
            border: 1px solid #fecaca;
        }
        
        .resend-section {
            text-align: center;
            margin-top: 1.5rem;
            padding-top: 1.5rem;
            border-top: 1px solid #e5e7eb;
        }
        
        .resend-section p {
            color: #6b7280;
            margin-bottom: 1rem;
        }
        
        .back-link {
            text-align: center;
            margin-top: 2rem;
        }
        
        .back-link a {
            color: #3b82f6;
            text-decoration: none;
            font-weight: 500;
        }
        
        .back-link a:hover {
            text-decoration: underline;
        }
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
                    <label for="email">
                        <i class="fas fa-envelope"></i>
                        Email Address
                    </label>
                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars(isset($_SESSION['prefill_email']) ? $_SESSION['prefill_email'] : $email); ?>" required placeholder="Enter your registered email">
                </div>
                
                <button type="submit" name="request_otp" class="btn btn-primary">
                    <i class="fas fa-paper-plane"></i>
                    Send Verification Code
                </button>
            </form>
        <?php else: ?>
            <!-- Verify OTP Form -->
            <form method="POST" class="otp-form">
                <div class="form-group">
                    <label for="otp">
                        <i class="fas fa-key"></i>
                        Verification Code
                    </label>
                    <input type="text" id="otp" name="otp" class="otp-input" maxlength="6" required placeholder="000000" pattern="[0-9]{6}">
                    <small style="color: #6b7280; margin-top: 0.5rem; display: block;">
                        Enter the 6-digit code sent to <?php echo htmlspecialchars($_SESSION['otp_email']); ?>
                    </small>
                </div>
                
                <button type="submit" name="verify_otp" class="btn btn-primary">
                    <i class="fas fa-check"></i>
                    Verify & Access QR Code
                </button>
            </form>
            
            <div class="resend-section">
                <p>Didn't receive the code?</p>
                <form method="POST" style="display: inline;">
                    <input type="hidden" name="email" value="<?php echo htmlspecialchars($_SESSION['otp_email']); ?>">
                    <button type="submit" name="request_otp" class="btn btn-secondary">
                        <i class="fas fa-redo"></i>
                        Resend Code
                    </button>
                </form>
            </div>
        <?php endif; ?>
        
        <div class="back-link">
            <a href="index.html">
                <i class="fas fa-arrow-left"></i>
                Back to Home
            </a>
        </div>
    </div>
    
    <script>
        // Auto-focus OTP input
        document.addEventListener('DOMContentLoaded', function() {
            const otpInput = document.getElementById('otp');
            if (otpInput) {
                otpInput.focus();
                
                // Auto-format OTP input
                otpInput.addEventListener('input', function(e) {
                    this.value = this.value.replace(/[^0-9]/g, '').substring(0, 6);
                });
            }
        });
    </script>
</body>
</html> 