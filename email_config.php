<?php
/**
 * Email Configuration for ManifestLink OTP System
 * 
 * This file contains email settings and helper functions for sending OTP verification emails.
 * Configured to use Gmail SMTP for reliable email delivery.
 */

// Load PHPMailer if available
if (file_exists('vendor/autoload.php')) {
    require_once 'vendor/autoload.php';
}

// Email Configuration
define('EMAIL_FROM', 'srgedaya@usa.edu.ph'); // Your actual email
define('EMAIL_FROM_NAME', 'ManifestLink');
define('EMAIL_SUBJECT_PREFIX', 'ManifestLink - ');

// Gmail SMTP Configuration
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'srgedaya@usa.edu.ph'); // Your actual email
define('SMTP_PASSWORD', 'nfmp xowi zbav qcoo'); // Your Gmail app password

/**
 * Send OTP Email using Gmail SMTP
 * 
 * @param string $to_email Recipient email address
 * @param string $user_name Recipient's full name
 * @param string $otp The 6-digit OTP code
 * @return bool True if email sent successfully, false otherwise
 */
function sendOTPEmail($to_email, $user_name, $otp) {
    // Check if PHPMailer is available
    if (class_exists('PHPMailer\PHPMailer\PHPMailer')) {
        return sendOTPEmailSMTP($to_email, $user_name, $otp);
    }
    
    // Fallback: Configure PHP settings programmatically and use mail()
    ini_set('SMTP', 'smtp.gmail.com');
    ini_set('smtp_port', '587');
    ini_set('sendmail_from', EMAIL_FROM);
    
    return sendOTPEmailGmail($to_email, $user_name, $otp);
}

/**
 * Send OTP Email using Gmail SMTP with PHPMailer
 */
function sendOTPEmailSMTP($to_email, $user_name, $otp) {
    try {
        $mail = new PHPMailer\PHPMailer\PHPMailer(true);
        
        // Server settings
        $mail->isSMTP();
        $mail->Host       = SMTP_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = SMTP_USERNAME;
        $mail->Password   = SMTP_PASSWORD;
        $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = SMTP_PORT;
        
        // Fix SSL certificate issues for local development
        $mail->SMTPOptions = array(
            'ssl' => array(
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            )
        );
        
        // Recipients
        $mail->setFrom(EMAIL_FROM, EMAIL_FROM_NAME);
        $mail->addAddress($to_email, $user_name);
        
        // Content
        $mail->isHTML(true);
        $mail->Subject = EMAIL_SUBJECT_PREFIX . "QR Code Access Verification";
        $mail->Body    = getEmailTemplate($user_name, $otp);
        
        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Email sending failed: " . $mail->ErrorInfo);
        return false;
    }
}

/**
 * Send OTP Email using Gmail SMTP with basic PHP functions
 */
function sendOTPEmailGmail($to_email, $user_name, $otp) {
    // Configure PHP to use Gmail SMTP
    ini_set('SMTP', SMTP_HOST);
    ini_set('smtp_port', SMTP_PORT);
    ini_set('sendmail_from', EMAIL_FROM);
    
    $subject = EMAIL_SUBJECT_PREFIX . "QR Code Access Verification";
    $message_body = getEmailTemplate($user_name, $otp);
    
    $headers = array();
    $headers[] = "MIME-Version: 1.0";
    $headers[] = "Content-type: text/html; charset=UTF-8";
    $headers[] = "From: " . EMAIL_FROM_NAME . " <" . EMAIL_FROM . ">";
    $headers[] = "Reply-To: " . EMAIL_FROM;
    $headers[] = "X-Mailer: PHP/" . phpversion();
    
    return mail($to_email, $subject, $message_body, implode("\r\n", $headers));
}

/**
 * Get HTML Email Template
 */
function getEmailTemplate($user_name, $otp) {
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

/**
 * Clean up expired OTPs from database
 * Run this periodically (e.g., via cron job)
 */
function cleanupExpiredOTPs($conn) {
    $stmt = $conn->prepare("DELETE FROM otp_verification WHERE expires_at < NOW()");
    return $stmt->execute();
}

/**
 * Get OTP statistics (for admin purposes)
 */
function getOTPStats($conn) {
    $stats = array();
    
    // Total OTPs generated today
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM otp_verification WHERE DATE(created_at) = CURDATE()");
    $stmt->execute();
    $result = $stmt->get_result();
    $stats['today_total'] = $result->fetch_assoc()['total'];
    
    // Expired OTPs
    $stmt = $conn->prepare("SELECT COUNT(*) as expired FROM otp_verification WHERE expires_at < NOW()");
    $stmt->execute();
    $result = $stmt->get_result();
    $stats['expired'] = $result->fetch_assoc()['expired'];
    
    return $stats;
}

/**
 * Setup Instructions for Gmail SMTP:
 * 
 * 1. Enable 2-Factor Authentication on your Gmail account
 * 2. Generate an App Password:
 *    - Go to Google Account settings
 *    - Security → 2-Step Verification → App passwords
 *    - Generate a new app password for "Mail"
 * 3. Replace 'your-app-password-here' above with your actual app password
 * 4. Install PHPMailer (optional but recommended):
 *    - Run: composer require phpmailer/phpmailer
 *    - Or download manually from: https://github.com/PHPMailer/PHPMailer
 * 
 * Alternative: Use the basic mail() function (less reliable but works for testing)
 */
?> 