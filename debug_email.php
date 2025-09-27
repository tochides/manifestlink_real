<?php
/**
 * Email Debugging Script
 * This will help us identify exactly what's wrong with email sending
 */

echo "<h2>Email Debugging</h2>";

// Check PHPMailer installation
echo "<h3>1. PHPMailer Check</h3>";
if (file_exists('vendor/autoload.php')) {
    echo "✅ vendor/autoload.php exists<br>";
    require_once 'vendor/autoload.php';
    
    if (class_exists('PHPMailer\PHPMailer\PHPMailer')) {
        echo "✅ PHPMailer class loaded successfully<br>";
    } else {
        echo "❌ PHPMailer class not found<br>";
    }
} else {
    echo "❌ vendor/autoload.php not found<br>";
}

// Check individual PHPMailer files
echo "<h3>2. PHPMailer Files Check</h3>";
$files = ['vendor/PHPMailer.php', 'vendor/SMTP.php', 'vendor/Exception.php'];
foreach ($files as $file) {
    if (file_exists($file)) {
        echo "✅ {$file} exists<br>";
    } else {
        echo "❌ {$file} missing<br>";
    }
}

// Test basic PHPMailer functionality
echo "<h3>3. PHPMailer Basic Test</h3>";
if (class_exists('PHPMailer\PHPMailer\PHPMailer')) {
    try {
        $mail = new PHPMailer\PHPMailer\PHPMailer(true);
        echo "✅ PHPMailer object created successfully<br>";
        
        // Test SMTP settings
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->Port = 587;
        $mail->SMTPAuth = true;
        $mail->Username = 'srgedaya@usa.edu.ph';
        $mail->Password = 'nfmp xowi zbav qcoo';
        $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
        
        // Fix SSL certificate issues for local development
        $mail->SMTPOptions = array(
            'ssl' => array(
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            )
        );
        
        echo "✅ SMTP settings configured<br>";
        
        // Test connection (without sending)
        $mail->SMTPDebug = 0; // Set to 2 for detailed debug
        $mail->setFrom('srgedaya@usa.edu.ph', 'ManifestLink');
        $mail->addAddress('test@example.com', 'Test User');
        $mail->Subject = 'Test Email';
        $mail->Body = 'This is a test email.';
        
        echo "✅ Email object configured<br>";
        
    } catch (Exception $e) {
        echo "❌ PHPMailer error: " . $e->getMessage() . "<br>";
    }
} else {
    echo "❌ Cannot test PHPMailer - class not available<br>";
}

// Test actual email sending
echo "<h3>4. Email Send Test</h3>";
if (isset($_POST['send_debug_email'])) {
    if (class_exists('PHPMailer\PHPMailer\PHPMailer')) {
        try {
            $mail = new PHPMailer\PHPMailer\PHPMailer(true);
            
            // Enable debug output
            $mail->SMTPDebug = 2; // Enable verbose debug output
            $mail->Debugoutput = function($str, $level) {
                echo "<div style='background: #f0f0f0; padding: 5px; margin: 2px; font-family: monospace; font-size: 12px;'>$str</div>";
            };
            
            // Server settings
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->Port = 587;
            $mail->SMTPAuth = true;
            $mail->Username = 'srgedaya@usa.edu.ph';
            $mail->Password = 'nfmp xowi zbav qcoo';
            $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
            
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
            $mail->addAddress('rjmelocotones@gmail.com', 'Rei Jan Melocotones');
            
            // Content
            $mail->isHTML(true);
            $mail->Subject = 'ManifestLink - Debug Test Email';
            $mail->Body = '<h1>Test Email</h1><p>This is a debug test email from ManifestLink OTP system.</p>';
            
            echo "<div style='background: #e8f5e8; padding: 10px; margin: 10px 0; border: 1px solid #4caf50;'>";
            echo "<strong>Attempting to send email...</strong><br>";
            $mail->send();
            echo "<strong>✅ Email sent successfully!</strong>";
            echo "</div>";
            
        } catch (Exception $e) {
            echo "<div style='background: #ffebee; padding: 10px; margin: 10px 0; border: 1px solid #f44336;'>";
            echo "<strong>❌ Email failed to send</strong><br>";
            echo "Error: " . $e->getMessage();
            echo "</div>";
        }
    } else {
        echo "❌ PHPMailer not available<br>";
    }
} else {
    echo "<form method='POST'>";
    echo "<input type='submit' name='send_debug_email' value='Send Debug Email with Full Logging'>";
    echo "</form>";
}

// Alternative: Test with different email service
echo "<h3>5. Alternative Email Service Test</h3>";
echo "<p>If Gmail continues to fail, we can switch to a different service:</p>";
echo "<ul>";
echo "<li><strong>SendGrid</strong> - Free tier available, very reliable</li>";
echo "<li><strong>Mailgun</strong> - Free tier available</li>";
echo "<li><strong>SMTP2GO</strong> - Free tier available</li>";
echo "</ul>";

echo "<hr>";
echo "<p><a href='test_otp.php'>Back to OTP Test</a></p>";
echo "<p><a href='register.php'>Go to Registration</a></p>";
?> 