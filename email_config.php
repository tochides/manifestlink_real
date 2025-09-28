<?php
// Ensure Composer autoload is loaded for SendGrid and other dependencies
if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require_once __DIR__ . '/../vendor/autoload.php';
} elseif (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
}
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


// SendGrid API Key
define('SENDGRID_API_KEY', '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');

/**
 * Send OTP Email using Gmail SMTP
 * 
 * @param string $to_email Recipient email address
 * @param string $user_name Recipient's full name
 * @param string $otp The 6-digit OTP code
 * @return bool True if email sent successfully, false otherwise
 */

// Send OTP Email using SendGrid API
function sendOTPEmail($to_email, $user_name, $otp) {
    if (!class_exists('SendGrid\\Mail\\Mail')) {
        error_log('SendGrid library not found. Please run composer install.');
        return false;
    }
    $email = new \SendGrid\Mail\Mail();
    $email->setFrom(EMAIL_FROM, EMAIL_FROM_NAME);
    $email->setSubject(EMAIL_SUBJECT_PREFIX . "QR Code Access Verification");
    $email->addTo($to_email, $user_name);
    $email->addContent("text/html", getEmailTemplate($user_name, $otp));
    try {
        $sendgrid = new \SendGrid(SENDGRID_API_KEY);
        $response = $sendgrid->send($email);
        if ($response->statusCode() >= 200 && $response->statusCode() < 300) {
            return true;
        } else {
            error_log('SendGrid error: ' . $response->statusCode() . ' ' . $response->body());
            return false;
        }
    } catch (Exception $e) {
        error_log('SendGrid Exception: ' . $e->getMessage());
        return false;
    }
}

/**
 * Send OTP Email using Gmail SMTP with PHPMailer
 */

// Old SMTP and mail() functions removed; now using SendGrid API only.

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