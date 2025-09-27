<?php
require_once 'connect.php';

echo "<h2>🔍 OTP Debug Information</h2>";

// Check recent OTP records
$result = $conn->query("SELECT * FROM otp_verification ORDER BY created_at DESC LIMIT 5");
echo "<h3>Recent OTP Records:</h3>";
if ($result && $result->num_rows > 0) {
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>ID</th><th>User ID</th><th>Email</th><th>OTP</th><th>Expires At</th></tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['id'] . "</td>";
        echo "<td>" . $row['user_id'] . "</td>";
        echo "<td>" . $row['email'] . "</td>";
        echo "<td><strong>" . $row['otp'] . "</strong></td>";
        echo "<td>" . $row['expires_at'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>No OTP records found.</p>";
}

// Check current time
echo "<h3>Time Check:</h3>";
echo "PHP Current Time: " . date('Y-m-d H:i:s') . "<br>";
$result = $conn->query("SELECT NOW() as db_time");
if ($result) {
    $row = $result->fetch_assoc();
    echo "Database Current Time: " . $row['db_time'] . "<br>";
}

echo "<br><a href='test_otp.php'>Back to OTP Test Page</a>";
?> 