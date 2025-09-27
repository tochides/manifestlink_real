<?php
/**
 * XAMPP Email Configuration Helper
 * This script will help configure XAMPP for Gmail SMTP
 */

echo "<h2>🔧 XAMPP Email Configuration Helper</h2>";

// Check current PHP configuration
echo "<h3>Current PHP Mail Configuration:</h3>";
echo "<div style='background: #f0f0f0; padding: 15px; margin: 10px 0; border: 1px solid #ddd;'>";
echo "<strong>SMTP:</strong> " . ini_get('SMTP') . "<br>";
echo "<strong>SMTP Port:</strong> " . ini_get('smtp_port') . "<br>";
echo "<strong>Sendmail From:</strong> " . ini_get('sendmail_from') . "<br>";
echo "<strong>Sendmail Path:</strong> " . ini_get('sendmail_path') . "<br>";
echo "</div>";

// Show php.ini location
echo "<h3>📁 PHP Configuration File Location:</h3>";
echo "<div style='background: #e8f5e8; padding: 15px; margin: 10px 0; border: 1px solid #4caf50;'>";
echo "<strong>php.ini file location:</strong> " . php_ini_loaded_file() . "<br>";
echo "<strong>Additional ini files:</strong> " . php_ini_scanned_files() . "<br>";
echo "</div>";

// Instructions
echo "<h3>📋 Step-by-Step Instructions:</h3>";
echo "<div style='background: #fff3cd; padding: 20px; margin: 10px 0; border: 1px solid #ffeaa7;'>";
echo "<h4>Step 1: Open php.ini File</h4>";
echo "<ol>";
echo "<li>Open <strong>XAMPP Control Panel</strong></li>";
echo "<li>Click <strong>'Config'</strong> next to Apache</li>";
echo "<li>Select <strong>'PHP (php.ini)'</strong></li>";
echo "<li>The file will open in your default text editor</li>";
echo "</ol>";

echo "<h4>Step 2: Find and Update Mail Settings</h4>";
echo "<ol>";
echo "<li>Press <strong>Ctrl+F</strong> to search</li>";
echo "<li>Search for <strong>'[mail function]'</strong></li>";
echo "<li>Find these lines and update them:</li>";
echo "</ol>";

echo "<div style='background: #f8f9fa; padding: 15px; margin: 10px 0; border: 1px solid #dee2e6; font-family: monospace;'>";
echo "<strong>CHANGE FROM:</strong><br>";
echo "SMTP = localhost<br>";
echo "smtp_port = 25<br>";
echo "sendmail_from = me@example.com<br><br>";
echo "<strong>CHANGE TO:</strong><br>";
echo "SMTP = smtp.gmail.com<br>";
echo "smtp_port = 587<br>";
echo "sendmail_from = srgedaya@usa.edu.ph<br>";
echo "</div>";

echo "<h4>Step 3: Save and Restart</h4>";
echo "<ol>";
echo "<li>Save the php.ini file</li>";
echo "<li>Go back to XAMPP Control Panel</li>";
echo "<li>Stop Apache (click 'Stop')</li>";
echo "<li>Start Apache again (click 'Start')</li>";
echo "</ol>";
echo "</div>";

// Alternative method using ini_set
echo "<h3>🔄 Alternative: Programmatic Configuration</h3>";
echo "<p>If you can't edit php.ini, we can set these values programmatically:</p>";

if (isset($_POST['set_email_config'])) {
    // Set email configuration programmatically
    ini_set('SMTP', 'smtp.gmail.com');
    ini_set('smtp_port', '587');
    ini_set('sendmail_from', 'srgedaya@usa.edu.ph');
    
    echo "<div style='background: #e8f5e8; padding: 15px; margin: 10px 0; border: 1px solid #4caf50;'>";
    echo "<strong>✅ Email configuration set programmatically!</strong><br>";
    echo "SMTP: " . ini_get('SMTP') . "<br>";
    echo "SMTP Port: " . ini_get('smtp_port') . "<br>";
    echo "Sendmail From: " . ini_get('sendmail_from') . "<br>";
    echo "</div>";
    
    // Test email after configuration
    echo "<h4>Testing Email After Configuration:</h4>";
    $to = 'rjmelocotones@gmail.com';
    $subject = 'ManifestLink - Configuration Test';
    $message = '<h1>Configuration Test</h1><p>This email was sent after configuring SMTP settings.</p>';
    
    $headers = array();
    $headers[] = "MIME-Version: 1.0";
    $headers[] = "Content-type: text/html; charset=UTF-8";
    $headers[] = "From: ManifestLink <srgedaya@usa.edu.ph>";
    $headers[] = "Reply-To: srgedaya@usa.edu.ph";
    $headers[] = "X-Mailer: PHP/" . phpversion();
    
    echo "<div style='background: #f0f8ff; padding: 15px; margin: 10px 0; border: 1px solid #0066cc;'>";
    echo "<strong>Attempting to send test email...</strong><br>";
    
    if (mail($to, $subject, $message, implode("\r\n", $headers))) {
        echo "<strong>✅ Email sent successfully!</strong><br>";
        echo "Check your email inbox for the test message.";
    } else {
        echo "<strong>❌ Email still failed to send</strong><br>";
        echo "You may need to use the php.ini method or try a different approach.";
    }
    echo "</div>";
} else {
    echo "<form method='POST'>";
    echo "<input type='submit' name='set_email_config' value='Set Email Config Programmatically' style='background: #2196F3; color: white; padding: 10px 20px; border: none; cursor: pointer;'>";
    echo "</form>";
}

// Show alternative solutions
echo "<h3>🚀 Alternative Solutions</h3>";
echo "<div style='background: #f8f9fa; padding: 20px; margin: 10px 0; border: 1px solid #dee2e6;'>";
echo "<h4>Option 1: Use PHPMailer with SMTP Authentication</h4>";
echo "<p>This bypasses the php.ini configuration and uses direct SMTP authentication.</p>";
echo "<a href='test_phpmailer_smtp.php' style='background: #4CAF50; color: white; padding: 10px 20px; text-decoration: none; border-radius: 4px;'>Test PHPMailer SMTP</a><br><br>";

echo "<h4>Option 2: Use Alternative Email Service</h4>";
echo "<p>Services like SendGrid, Mailgun, or SMTP2GO that are easier to configure.</p>";
echo "<a href='setup_alternative_email.php' style='background: #FF9800; color: white; padding: 10px 20px; text-decoration: none; border-radius: 4px;'>Setup Alternative Email</a>";
echo "</div>";

echo "<hr>";
echo "<p><a href='test_email_simple.php'>Back to Email Test</a> | ";
echo "<a href='register.php'>Go to Registration</a></p>";
?>
