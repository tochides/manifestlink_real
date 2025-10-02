<?php
require 'email_config.php';

if (sendOTPEmail('your_email@example.com', 'Test User', '123456')) {
    echo "Email sent successfully!\n";
} else {
    echo "Email failed.\n";
}
