<?php
require __DIR__ . '/vendor/autoload.php';

use Dotenv\Dotenv;
use SendGrid\Mail\Mail;

// Load .env only if it exists (for local dev)
if (file_exists(__DIR__ . '/.env')) {
    $dotenv = Dotenv::createImmutable(__DIR__);
    $dotenv->load();
}

// Get environment variables (Railway injects automatically)
$SENDGRID_API_KEY   = getenv('SENDGRID_API_KEY');
$EMAIL_FROM         = getenv('EMAIL_FROM');
$EMAIL_FROM_NAME    = getenv('EMAIL_FROM_NAME');

// Optional fallback for local dev
if (!$SENDGRID_API_KEY) { $SENDGRID_API_KEY = "your_local_test_key_here"; }
if (!$EMAIL_FROM) { $EMAIL_FROM = "srgedaya@usa.edu.ph"; }
if (!$EMAIL_FROM_NAME) { $EMAIL_FROM_NAME = "ManifestLink"; }

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
 * Send OTP Email using SendGrid
 */
function sendOTPEmail($to_email, $to_name, $otp) {
    global $SENDGRID_API_KEY, $EMAIL_FROM, $EMAIL_FROM_NAME;

    $email = new Mail();
    $email->setFrom($EMAIL_FROM, $EMAIL_FROM_NAME);
    $email->setSubject("Your ManifestLink Verification Code");
    $email->addTo($to_email, $to_name);
    $email->addContent("text/html", getEmailTemplate($to_name, $otp));

    $sendgrid = new \SendGrid($SENDGRID_API_KEY);
    try {
        $response = $sendgrid->send($email);
        return $response->statusCode() >= 200 && $response->statusCode() < 300;
    } catch (Exception $e) {
        error_log("SendGrid Error: " . $e->getMessage());
        return false;
    }
}
