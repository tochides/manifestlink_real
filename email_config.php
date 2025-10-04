<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

// =====================
// Email sender settings
// =====================
define('EMAIL_FROM', 'srgedaya@usa.edu.ph');     // Gmail (school or personal)
define('EMAIL_FROM_NAME', 'ManifestLink');       // Sender name
define('EMAIL_APP_PASSWORD', 'buum djqn hdtq mfmc'); // Gmail App Password
define('EMAIL_SUBJECT_PREFIX', 'ManifestLink - ');

// =====================
// Send OTP Email Function
// =====================
function sendOTPEmail($to_email, $user_name, $otp) {
    $mail = new PHPMailer(true);

    try {
        // SMTP Settings
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = EMAIL_FROM;
        $mail->Password   = EMAIL_APP_PASSWORD;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        // Sender & Recipient
        $mail->setFrom(EMAIL_FROM, EMAIL_FROM_NAME);
        $mail->addAddress($to_email, $user_name);

        // Email content
        $mail->isHTML(true);
        $mail->Subject = EMAIL_SUBJECT_PREFIX . "OTP Verification";
        $mail->Body    = getEmailTemplate($user_name, $otp);

        // Send email
        $mail->send();
        return true;

    } catch (Exception $e) {
        error_log("Mailer Error: {$mail->ErrorInfo}");
        return false;
    }
}

// =====================
// Email Template
// =====================
function getEmailTemplate($user_name, $otp) {
    return "
    <html>
    <body style='font-family: Arial, sans-serif;'>
        <h2>Hello {$user_name},</h2>
        <p>Your ManifestLink verification code is:</p>
        <div style='padding:10px; border:1px solid #ccc; display:inline-block; background:#f4f4f4;'>
            <h1 style='color:#3b82f6; letter-spacing:5px;'>{$otp}</h1>
        </div>
        <p><b>This code expires in 10 minutes.</b></p>
        <p>Please do not share this code with anyone.</p>
        <br>
        <p>Thank you,<br><b>ManifestLink Team</b></p>
    </body>
    </html>";
}

// =====================
// Example Usage (for testing)
// =====================
if (sendOTPEmail("testreceiver@gmail.com", "Test User", rand(100000,999999))) {
    echo "✅ OTP Email sent successfully!";
} else {
    echo "❌ Failed to send OTP Email.";
}
