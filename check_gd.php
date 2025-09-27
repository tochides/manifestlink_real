<?php
echo "<h2>PHP GD Extension Check</h2>";

// Check if GD extension is loaded
if (extension_loaded('gd')) {
    echo "<p style='color: green;'>✅ GD extension is ENABLED</p>";
    
    // Get GD version
    $gd_info = gd_info();
    echo "<p><strong>GD Version:</strong> " . $gd_info['GD Version'] . "</p>";
    echo "<p><strong>FreeType Support:</strong> " . ($gd_info['FreeType Support'] ? 'Yes' : 'No') . "</p>";
    echo "<p><strong>JPEG Support:</strong> " . ($gd_info['JPEG Support'] ? 'Yes' : 'No') . "</p>";
    echo "<p><strong>PNG Support:</strong> " . ($gd_info['PNG Support'] ? 'Yes' : 'No') . "</p>";
    
} else {
    echo "<p style='color: red;'>❌ GD extension is NOT ENABLED</p>";
    echo "<p>This is why you're getting the 'Call to undefined function ImageCreate()' error.</p>";
}

echo "<h3>PHP Configuration File Location:</h3>";
echo "<p><strong>php.ini location:</strong> " . php_ini_loaded_file() . "</p>";

echo "<h3>Available Extensions:</h3>";
$extensions = get_loaded_extensions();
sort($extensions);
echo "<p>Total loaded extensions: " . count($extensions) . "</p>";
echo "<p>Extensions containing 'gd':</p>";
foreach ($extensions as $ext) {
    if (stripos($ext, 'gd') !== false) {
        echo "- $ext<br>";
    }
}

echo "<h3>How to Fix:</h3>";
echo "<ol>";
echo "<li>Open your php.ini file: <strong>" . php_ini_loaded_file() . "</strong></li>";
echo "<li>Find the line: <code>;extension=gd</code> or <code>;extension=gd2</code></li>";
echo "<li>Remove the semicolon (;) at the beginning</li>";
echo "<li>Save the file</li>";
echo "<li>Restart Apache in XAMPP Control Panel</li>";
echo "</ol>";
?> 