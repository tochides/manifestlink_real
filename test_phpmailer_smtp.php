<?php
/**
 * PHPMailer SMTP Test - Bypasses php.ini configuration
 * This uses PHPMailer with direct SMTP authentication
 */

echo "<h2>📧 PHPMailer SMTP Test</h2>";

// Check if PHPMailer is available
if (file_exists('vendor/autoload.php')) {
    require_once 'vendor/autoload.php';
    echo "<div style='background: #e8f5e8; padding: 15px; margin: 10px 0; border: 1px solid #4caf50;'>";
    echo "✅ PHPMailer found and loaded successfully!";
    echo "</div>";
} else {
    echo "<div style='background: #ffebee; padding: 15px; margin: 10px 0; border: 1px solid #f44336;'>";
    echo "❌ PHPMailer not found. Using basic mail() function instead.";
    echo "</div>";
}

// Test email sending
if (isset($_POST['send_test_email'])) {
    $to_email = $_POST['test_email'] ?? 'rjmelocotones@gmail.com';
    $test_otp = '123456';
    
    echo "<div style='background: #f0f8ff; padding: 15px; margin: 10px 0; border: 1px solid #0066cc;'>";
    echo "<strong>Attempting to send email to: {$to_email}</strong><br>";
    
    if (class_exists('PHPMailer\PHPMailer\PHPMailer')) {
        // Use PHPMailer
        try {
            $mail = new PHPMailer\PHPMailer\PHPMailer(true);
            
            // Enable debug output
            $mail->SMTPDebug = 2;
            $mail->Debugoutput = function($str, $level) {
                echo "<div style='background: #f0f0f0; padding: 5px; margin: 2px; font-family: monospace; font-size: 12px;'>$str</div>";
            };
            
            // Server settings
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'srgedaya@usa.edu.ph';
            $mail->Password = 'nfmp xowi zbav qcoo';
            $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;
            
            // Fix SSL certificate issues for local development
            $mail->SMTPOptions = array(
                'ssl' => array(
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true
                )
            );
            
            // Recipients
            $mail->setFrom('srgedaya@usa.edu.ph', 'ManifestLink');
            $mail->addAddress($to_email, 'Test User');
            
            // Content
            $mail->isHTML(true);
            $mail->Subject = 'ManifestLink - OTP Test Email';
            $mail->Body = getOTPEmailTemplate('Test User', $test_otp);
            
            $mail->send();
            echo "<strong>✅ Email sent successfully using PHPMailer!</strong><br>";
            echo "Check your email inbox for the OTP test message.";
            
        } catch (Exception $e) {
            echo "<strong>❌ PHPMailer failed:</strong> " . $e->getMessage() . "<br>";
            echo "Trying fallback method...<br><br>";
            
            // Fallback to basic mail() with programmatic configuration
            ini_set('SMTP', 'smtp.gmail.com');
            ini_set('smtp_port', '587');
            ini_set('sendmail_from', 'srgedaya@usa.edu.ph');
            
            $subject = 'ManifestLink - OTP Test Email (Fallback)';
            $message = getOTPEmailTemplate('Test User', $test_otp);
            
            $headers = array();
            $headers[] = "MIME-Version: 1.0";
            $headers[] = "Content-type: text/html; charset=UTF-8";
            $headers[] = "From: ManifestLink <srgedaya@usa.edu.ph>";
            $headers[] = "Reply-To: srgedaya@usa.edu.ph";
            $headers[] = "X-Mailer: PHP/" . phpversion();
            
            if (mail($to_email, $subject, $message, implode("\r\n", $headers))) {
                echo "<strong>✅ Email sent successfully using fallback method!</strong>";
            } else {
                echo "<strong>❌ Both methods failed. Check your server configuration.</strong>";
            }
        }
    } else {
        // Use basic mail() function
        ini_set('SMTP', 'smtp.gmail.com');
        ini_set('smtp_port', '587');
        ini_set('sendmail_from', 'srgedaya@usa.edu.ph');
        
        $subject = 'ManifestLink - OTP Test Email';
        $message = getOTPEmailTemplate('Test User', $test_otp);
        
        $headers = array();
        $headers[] = "MIME-Version: 1.0";
        $headers[] = "Content-type: text/html; charset=UTF-8";
        $headers[] = "From: ManifestLink <srgedaya@usa.edu.ph>";
        $headers[] = "Reply-To: srgedaya@usa.edu.ph";
        $headers[] = "X-Mailer: PHP/" . phpversion();
        
        if (mail($to_email, $subject, $message, implode("\r\n", $headers))) {
            echo "<strong>✅ Email sent successfully!</strong><br>";
            echo "Check your email inbox for the OTP test message.";
        } else {
            echo "<strong>❌ Email failed to send</strong><br>";
            echo "You need to configure your server's mail settings.";
        }
    }
    echo "</div>";
}

// Email template function
function getOTPEmailTemplate($user_name, $otp) {
    return "
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: #3b82f6; color: white; padding: 20px; text-align: center; border-radius: 8px 8px 0 0; }
            .content { background: #f8f9fa; padding: 30px; border-radius: 0 0 8px 8px; }
            .otp-box { background: white; padding: 30px; text-align: center; border-radius: 8px; margin: 20px 0; border: 2px solid #e5e7eb; }
            .otp-code { font-size: 36px; font-weight: bold; color: #3b82f6; letter-spacing: 8px; margin: 10px 0; }
            .footer { text-align: center; margin-top: 30px; padding-top: 20px; border-top: 1px solid #e5e7eb; color: #6b7280; font-size: 14px; }
            .warning { background: #fef3c7; border: 1px solid #f59e0b; padding: 15px; border-radius: 6px; margin: 20px 0; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>🔐 ManifestLink QR Code Access</h1>
            </div>
            <div class='content'>
                <h2>Hello {$user_name},</h2>
                <p>You requested secure access to your QR code. To proceed, please use the verification code below:</p>
                
                <div class='otp-box'>
                    <h3>Your Verification Code</h3>
                    <div class='otp-code'>{$otp}</div>
                    <p><strong>This code expires in 10 minutes</strong></p>
                </div>
                
                <div class='warning'>
                    <strong>⚠️ Security Notice:</strong><br>
                    • Never share this code with anyone<br>
                    • ManifestLink will never ask for this code via phone or text<br>
                    • If you didn't request this code, please ignore this email
                </div>
                
                <p>Enter this code on the verification page to access your QR code.</p>
                
                <p>Thank you for using ManifestLink!</p>
            </div>
            <div class='footer'>
                <p><strong>ManifestLink</strong> - Guimaras Port Authority</p>
                <p>Secure maritime passenger management system</p>
            </div>
        </div>
    </body>
    </html>";
}

// Show test form
echo "<h3>📧 Send Test Email</h3>";
echo "<form method='POST' style='background: #f9f9f9; padding: 20px; margin: 10px 0; border: 1px solid #ddd;'>";
echo "<label><strong>Test Email Address:</strong></label><br>";
echo "<input type='email' name='test_email' value='rjmelocotones@gmail.com' style='width: 300px; padding: 8px; margin: 5px 0;'><br><br>";
echo "<input type='submit' name='send_test_email' value='Send OTP Test Email' style='background: #4CAF50; color: white; padding: 10px 20px; border: none; cursor: pointer;'>";
echo "</form>";

echo "<hr>";
echo "<p><a href='configure_xampp_email.php'>Configure XAMPP Email</a> | ";
echo "<a href='test_email_simple.php'>Simple Email Test</a> | ";
echo "<a href='register.php'>Registration Page</a></p>";
?>
