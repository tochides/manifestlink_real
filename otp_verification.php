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
