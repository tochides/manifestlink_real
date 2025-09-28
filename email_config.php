<?php
require __DIR__ . '/vendor/autoload.php';

use Dotenv\Dotenv;
use SendGrid\Mail\Mail;

// Load .env for local development
if (file_exists(__DIR__ . '/.env')) {
    $dotenv = Dotenv::createImmutable(__DIR__);
    $dotenv->load();
}

// Get environment variables
$SENDGRID_API_KEY = getenv('SENDGRID_API_KEY');
$EMAIL_FROM       = getenv('EMAIL_FROM');
$EMAIL_FROM_NAME  = getenv('EMAIL_FROM_NAME');

// Fallbacks for local dev
if (!$SENDGRID_API_KEY) { 
    $SENDGRID_API_KEY = "YOUR_LOCAL_SENDGRID_KEY"; // replace with your key if testing locally
}
if (!$EMAIL_FROM) { $EMAIL_FROM = "srgedaya@usa.edu.ph"; }
if (!$EMAIL_FROM_NAME) { $EMAIL_FROM_NAME = "ManifestLink"; }

/**
 * Get HTML Email Template
 */
function getEmailTemplate($user_name, $otp) {
    return "
    <html>
        <body>
            <p>Hello {$user_name},</p>
            <p>Your verification code is: <strong>{$otp}</strong></p>
            <p>This code expires in 10 minutes.</p>
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

        // Debug output for development
        if ($response->statusCode() >= 200 && $response->statusCode() < 300) {
            return true;
        } else {
            if (strtolower(getenv('APP_ENV') ?? 'dev') !== 'production') {
                echo "<pre>SendGrid response status: " . $response->statusCode() . "\n";
                echo "Body: " . $response->body() . "\n";
                echo "Headers: "; print_r($response->headers()); echo "</pre>";
            }
            return false;
        }
    } catch (Exception $e) {
        if (strtolower(getenv('APP_ENV') ?? 'dev') !== 'production') {
            echo "<pre>SendGrid Exception: " . $e->getMessage() . "</pre>";
        }
        error_log("SendGrid Error: " . $e->getMessage());
        return false;
    }
}
