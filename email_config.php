<?php
require __DIR__ . '/vendor/autoload.php';

const RESEND_API_KEY      = 're_8tNmSbdZ_KaWVDGuWpH6zpcVEmrqoMHkH';
const EMAIL_FROM_REAL     = 'srgedaya@usa.edu.ph';        // real domain (needs verification)
const EMAIL_FROM_SANDBOX  = 'test@yourdomain.onresend.com'; // fallback (replace with your Resend sandbox domain)
const EMAIL_FROM_NAME     = 'ManifestLink';
const EMAIL_SUBJECT_PREFIX = 'ManifestLink - ';

function sendOTPEmail($to_email, $user_name, $otp) {
    try {
        $resend = \Resend::client(RESEND_API_KEY);

        // Default sender (real domain)
        $from = EMAIL_FROM_NAME . ' <' . EMAIL_FROM_REAL . '>';

        // Build email params
        $params = [
            'from'    => $from,
            'to'      => [$to_email],
            'subject' => EMAIL_SUBJECT_PREFIX . "QR Code Access Verification",
            'html'    => getEmailTemplate($user_name, $otp),
        ];

        try {
            // First try with the real domain
            $response = $resend->emails->send($params);
            return isset($response->id);
        } catch (\Exception $e) {
            // If domain not verified, fall back to sandbox
            if (str_contains($e->getMessage(), 'domain is not verified')) {
                error_log("⚠️ Real domain not verified. Falling back to sandbox domain.");

                $params['from'] = EMAIL_FROM_NAME . ' <' . EMAIL_FROM_SANDBOX . '>';
                $response = $resend->emails->send($params);
                return isset($response->id);
            }
            throw $e;
        }

    } catch (Exception $e) {
        error_log('Resend Exception: ' . $e->getMessage());
        return false;
    }
}

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
                <p>You requested secure access to your QR code. Use the verification code below:</p>
                
                <div class='otp-box'>
                    <h3>Your Verification Code</h3>
                    <div class='otp-code'>{$otp}</div>
                    <p><strong>This code expires in 10 minutes</strong></p>
                </div>
                
                <div class='warning'>
                    <strong>⚠️ Security Notice:</strong><br>
                    • Never share this code with anyone<br>
                    • ManifestLink will never ask for this code via phone or text<br>
                    • If you didn't request this code, ignore this email
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
