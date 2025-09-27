<?php
/**
 * PHPMailer Installation Script
 * This script will download and set up PHPMailer for you
 */

echo "<h2>PHPMailer Installation</h2>";

// Check if PHPMailer already exists
if (file_exists('vendor/autoload.php') || file_exists('PHPMailer/PHPMailer.php')) {
    echo "✅ PHPMailer is already installed!<br>";
    echo "<a href='test_otp.php'>Go back to test</a>";
    exit;
}

// Create vendor directory
if (!is_dir('vendor')) {
    mkdir('vendor', 0755, true);
    echo "✅ Created vendor directory<br>";
}

// Download PHPMailer files
$files = [
    'https://raw.githubusercontent.com/PHPMailer/PHPMailer/master/src/PHPMailer.php' => 'vendor/PHPMailer.php',
    'https://raw.githubusercontent.com/PHPMailer/PHPMailer/master/src/SMTP.php' => 'vendor/SMTP.php',
    'https://raw.githubusercontent.com/PHPMailer/PHPMailer/master/src/Exception.php' => 'vendor/Exception.php'
];

echo "<h3>Downloading PHPMailer files...</h3>";

foreach ($files as $url => $local_path) {
    $content = file_get_contents($url);
    if ($content !== false) {
        file_put_contents($local_path, $content);
        echo "✅ Downloaded: " . basename($local_path) . "<br>";
    } else {
        echo "❌ Failed to download: " . basename($local_path) . "<br>";
    }
}

// Create autoload file
$autoload_content = '<?php
// Simple autoloader for PHPMailer
spl_autoload_register(function ($class) {
    $prefix = "PHPMailer\\PHPMailer\\";
    $base_dir = __DIR__ . "/";
    
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }
    
    $relative_class = substr($class, $len);
    $file = $base_dir . $relative_class . ".php";
    
    if (file_exists($file)) {
        require $file;
    }
});
?>';

file_put_contents('vendor/autoload.php', $autoload_content);
echo "✅ Created autoload.php<br>";

// Test if PHPMailer works
echo "<h3>Testing PHPMailer installation...</h3>";
require_once 'vendor/autoload.php';

if (class_exists('PHPMailer\PHPMailer\PHPMailer')) {
    echo "✅ PHPMailer installed successfully!<br>";
    echo "<a href='test_otp.php'>Go back to test</a>";
} else {
    echo "❌ PHPMailer installation failed<br>";
    echo "<p>Manual installation required:</p>";
    echo "<ol>";
    echo "<li>Download PHPMailer from: <a href='https://github.com/PHPMailer/PHPMailer/releases' target='_blank'>https://github.com/PHPMailer/PHPMailer/releases</a></li>";
    echo "<li>Extract the files to your project folder</li>";
    echo "<li>Make sure you have: vendor/autoload.php</li>";
    echo "</ol>";
}
?> 