<?php
echo "<h2>QR Code Generation Test</h2>";

// Check GD extension
if (extension_loaded('gd')) {
    echo "<p style='color: green;'>✅ GD extension is ENABLED</p>";
} else {
    echo "<p style='color: red;'>❌ GD extension is NOT ENABLED</p>";
    exit;
}

// Check if phpqrcode library is available
if (file_exists('phpqrcode/qrlib.php')) {
    echo "<p style='color: green;'>✅ phpqrcode library found</p>";
} else {
    echo "<p style='color: red;'>❌ phpqrcode library not found</p>";
    exit;
}

// Check qrcodes directory
if (is_dir('qrcodes') && is_writable('qrcodes')) {
    echo "<p style='color: green;'>✅ qrcodes directory is writable</p>";
} else {
    echo "<p style='color: red;'>❌ qrcodes directory is not writable</p>";
    exit;
}

// Test QR code generation
try {
    include_once 'phpqrcode/qrlib.php';
    
    $test_data = "Test QR Code - ManifestLink";
    $test_filename = "qrcodes/test_qr_" . time() . ".png";
    
    // Generate test QR code
    QRcode::png($test_data, $test_filename, QR_ECLEVEL_L, 8);
    
    if (file_exists($test_filename)) {
        echo "<p style='color: green;'>✅ QR code generated successfully!</p>";
        echo "<p><strong>Test file:</strong> $test_filename</p>";
        echo "<p><strong>File size:</strong> " . filesize($test_filename) . " bytes</p>";
        
        // Display the test QR code
        echo "<div style='text-align: center; margin: 20px 0;'>";
        echo "<img src='$test_filename' alt='Test QR Code' style='border: 1px solid #ccc; padding: 10px;' />";
        echo "<br><small>Test QR Code - Scan to verify</small>";
        echo "</div>";
        
        // Clean up test file
        unlink($test_filename);
        echo "<p style='color: blue;'>ℹ️ Test file cleaned up</p>";
        
    } else {
        echo "<p style='color: red;'>❌ Failed to generate QR code</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}

echo "<h3>System Information:</h3>";
echo "<p><strong>PHP Version:</strong> " . phpversion() . "</p>";
echo "<p><strong>Current Directory:</strong> " . getcwd() . "</p>";
echo "<p><strong>php.ini location:</strong> " . php_ini_loaded_file() . "</p>";

$gd_info = gd_info();
echo "<p><strong>GD Version:</strong> " . $gd_info['GD Version'] . "</p>";
echo "<p><strong>PNG Support:</strong> " . ($gd_info['PNG Support'] ? 'Yes' : 'No') . "</p>";
echo "<p><strong>JPEG Support:</strong> " . ($gd_info['JPEG Support'] ? 'Yes' : 'No') . "</p>";
?> 