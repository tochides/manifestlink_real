<?php
/**
 * Simple Email Test for ManifestLink OTP System
 * This will test if emails can be sent using basic PHP mail() function
 */

echo "<h2>🔧 Simple Email Test</h2>";

// Test basic PHP mail function
echo "<h3>Testing Basic PHP mail() Function</h3>";

if (isset($_POST['test_basic_email'])) {
    $to = $_POST['test_email'] ?? 'test@example.com';
    $subject = 'ManifestLink - Test Email';
    $message = '<h1>Test Email from ManifestLink</h1><p>This is a test email to verify email functionality.</p>';
    
    $headers = array();
    $headers[] = "MIME-Version: 1.0";
    $headers[] = "Content-type: text/html; charset=UTF-8";
    $headers[] = "From: ManifestLink <srgedaya@usa.edu.ph>";
    $headers[] = "Reply-To: srgedaya@usa.edu.ph";
    $headers[] = "X-Mailer: PHP/" . phpversion();
    
    echo "<div style='background: #f0f8ff; padding: 15px; margin: 10px 0; border: 1px solid #0066cc;'>";
    echo "<strong>Attempting to send email to: {$to}</strong><br>";
    
    if (mail($to, $subject, $message, implode("\r\n", $headers))) {
        echo "<strong>✅ Email sent successfully!</strong><br>";
        echo "Check your email inbox (and spam folder) for the test email.";
    } else {
        echo "<strong>❌ Email failed to send</strong><br>";
        echo "This usually means your server doesn't have mail() function configured.";
    }
    echo "</div>";
}

// Test OTP function
echo "<h3>Testing OTP Email Function</h3>";
if (isset($_POST['test_otp_email'])) {
    require_once 'email_config.php';
    
    $test_email = $_POST['test_email'] ?? 'test@example.com';
    $test_name = 'Test User';
    $test_otp = '123456';
    
    echo "<div style='background: #f0f8ff; padding: 15px; margin: 10px 0; border: 1px solid #0066cc;'>";
    echo "<strong>Testing OTP email function...</strong><br>";
    
    if (sendOTPEmail($test_email, $test_name, $test_otp)) {
        echo "<strong>✅ OTP email sent successfully!</strong><br>";
        echo "Check your email for the OTP verification email.";
    } else {
        echo "<strong>❌ OTP email failed to send</strong><br>";
        echo "Check the error logs or try the debug script.";
    }
    echo "</div>";
}

// Show test forms
echo "<h3>Email Test Forms</h3>";
echo "<form method='POST' style='background: #f9f9f9; padding: 20px; margin: 10px 0; border: 1px solid #ddd;'>";
echo "<label><strong>Test Email Address:</strong></label><br>";
echo "<input type='email' name='test_email' value='rjmelocotones@gmail.com' style='width: 300px; padding: 8px; margin: 5px 0;'><br><br>";
echo "<input type='submit' name='test_basic_email' value='Test Basic Email' style='background: #4CAF50; color: white; padding: 10px 20px; border: none; cursor: pointer; margin-right: 10px;'>";
echo "<input type='submit' name='test_otp_email' value='Test OTP Email' style='background: #2196F3; color: white; padding: 10px 20px; border: none; cursor: pointer;'>";
echo "</form>";

// Check server configuration
echo "<h3>Server Configuration Check</h3>";
echo "<div style='background: #f0f0f0; padding: 15px; margin: 10px 0;'>";
echo "<strong>PHP Version:</strong> " . phpversion() . "<br>";
echo "<strong>Mail Function Available:</strong> " . (function_exists('mail') ? '✅ Yes' : '❌ No') . "<br>";
echo "<strong>SMTP Setting:</strong> " . ini_get('SMTP') . "<br>";
echo "<strong>SMTP Port:</strong> " . ini_get('smtp_port') . "<br>";
echo "<strong>Sendmail Path:</strong> " . ini_get('sendmail_path') . "<br>";
echo "</div>";

// Troubleshooting tips
echo "<h3>🔧 Troubleshooting Tips</h3>";
echo "<div style='background: #fff3cd; padding: 15px; margin: 10px 0; border: 1px solid #ffeaa7;'>";
echo "<strong>If emails are not sending:</strong><br>";
echo "1. <strong>Check XAMPP Mail Settings:</strong><br>";
echo "   - Open XAMPP Control Panel<br>";
echo "   - Click 'Config' next to Apache<br>";
echo "   - Select 'PHP (php.ini)'<br>";
echo "   - Find [mail function] section<br>";
echo "   - Set SMTP = smtp.gmail.com<br>";
echo "   - Set smtp_port = 587<br>";
echo "   - Set sendmail_from = srgedaya@usa.edu.ph<br><br>";

echo "2. <strong>Alternative Solution:</strong><br>";
echo "   - Use a different email service (SendGrid, Mailgun)<br>";
echo "   - Or use a local SMTP server<br>";
echo "</div>";

echo "<hr>";
echo "<p><a href='debug_email.php'>Advanced Email Debug</a> | ";
echo "<a href='test_otp.php'>OTP Test Page</a> | ";
echo "<a href='register.php'>Registration Page</a></p>";
?>
