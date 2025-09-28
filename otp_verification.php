<?php
require __DIR__ . '/vendor/autoload.php';

use Dotenv\Dotenv;
use SendGrid\Mail\Mail;

// Load .env only if available (Railway will inject automatically)
if (file_exists(__DIR__ . '/.env')) {
    $dotenv = Dotenv::createImmutable(__DIR__);
    $dotenv->load();
}

// Get env values (with safe fallbacks)
$SENDGRID_API_KEY   = getenv('SENDGRID_API_KEY');
$EMAIL_FROM         = getenv('EMAIL_FROM') ?: "srgedaya@usa.edu.ph";
$EMAIL_FROM_NAME    = getenv('EMAIL_FROM_NAME') ?: "ManifestLink";

/**
 * HTML email template
 */
function getEmailTemplate($user_name, $otp) {
    return "
    <html>
      <body style='font-family: Arial, sans-serif; background:#f9fafb; padding:20px;'>
        <div style='max-width:600px; margin:0 auto; background:white; border-radius:8px; overflow:hidden;'>
          <div style='background:#3b82f6; color:white; padding:20px; text-align:center;'>
            <h1>🔐 ManifestLink Verification</h1>
          </div>
          <div style='padding:30px;'>
            <p>Hello <strong>{$user_name}</strong>,</p>
            <p>Your verification code is:</p>
            <div style='font-size:32px; font-weight:bold; color:#3b82f6; text-align:center; letter-spacing:10px;'>
              {$otp}
            </div>
            <p style='margin-top:20px;'>This code expires in <strong>10 minutes</strong>.</p>
            <p style='color:#6b7280; font-size:14px;'>
              ⚠️ If you did not request this code, you can ignore this email.
            </p>
          </div>
          <div style='background:#f3f4f6; text-align:center; padding:15px; font-size:13px; color:#6b7280;'>
            ManifestLink - Guimaras Port Authority
          </div>
        </div>
      </body>
    </html>";
}

/**
 * Send OTP email via SendGrid
 */
function sendOTPEmail($toEmail, $toName, $otp) {
    global $SENDGRID_API_KEY, $EMAIL_FROM, $EMAIL_FROM_NAME;

    if (!$SENDGRID_API_KEY) {
        error_log("❌ Missing SENDGRID_API_KEY in environment");
        return false;
    }

    $email = new Mail();
    $email->setFrom($EMAIL_FROM, $EMAIL_FROM_NAME);
    $email->setSubject("Your ManifestLink OTP Code");
    $email->addTo($toEmail, $toName);
    $email->addContent("text/html", getEmailTemplate($toName, $otp));

    $sendgrid = new \SendGrid($SENDGRID_API_KEY);

    try {
        $response = $sendgrid->send($email);
        return $response->statusCode() == 202; // success
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
