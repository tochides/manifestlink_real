<?php
require __DIR__ . '/vendor/autoload.php';

const RESEND_API_KEY = 're_8tNmSbdZ_KaWVDGuWpH6zpcVEmrqoMHkH';
const EMAIL_FROM     = 'srgedaya@usa.edu.ph';
const EMAIL_TO       = 'your_test_email@example.com';

try {
    // ✅ Directly call the global Resend class
    $resend = \Resend::client(RESEND_API_KEY);

    $response = $resend->emails->send([
        'from'    => 'ManifestLink <' . EMAIL_FROM . '>',
        'to'      => [EMAIL_TO],
        'subject' => '✅ Test Email from Resend PHP SDK',
        'html'    => '<h1>Hello World!</h1><p>If you see this, Resend PHP is working 🎉</p>',
    ]);

    print_r($response);

} catch (Exception $e) {
    echo "❌ Exception: " . $e->getMessage() . PHP_EOL;
}
