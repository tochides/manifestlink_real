<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/vendor/autoload.php';

function sendOTPEmail($to, $user_name, $otp) {
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'srgedaya@usa.edu.ph';   // ✅ your email
        $mail->Password   = 'buum djqn hdtq mfmc';   // ✅ app password from Google (NOT real password)
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        $mail->setFrom('srgedaya@usa.edu.ph', 'ManifestLink');
        $mail->addAddress($to, $user_name);

        $mail->isHTML(true);
        $mail->Subject = 'Your OTP Code';
        $mail->Body    = getEmailTemplate($user_name, $otp);

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Mailer Error: " . $mail->ErrorInfo);
        return false;
    }
}

function getEmailTemplate($user_name, $otp) {
    return "
    <html>
    <body>
        <h2>Hello {$user_name},</h2>
        <p>Your ManifestLink verification code is:</p>
        <h1 style='color:#3b82f6'>{$otp}</h1>
        <p><b>This code expires in 10 minutes.</b></p>
        <p>Please do not share this code with anyone.</p>
    </body>
    </html>";
}
