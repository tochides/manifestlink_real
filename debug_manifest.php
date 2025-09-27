<?php
require_once 'connect.php';

echo "<h2>Manifest Table Debug</h2>";

// Check table structure
echo "<h3>Table Structure:</h3>";
$result = $conn->query("DESCRIBE manifest");
if ($result) {
    echo "<table border='1'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['Field'] . "</td>";
        echo "<td>" . $row['Type'] . "</td>";
        echo "<td>" . $row['Null'] . "</td>";
        echo "<td>" . $row['Key'] . "</td>";
        echo "<td>" . $row['Default'] . "</td>";
        echo "<td>" . $row['Extra'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
}

// Check sample data
echo "<h3>Sample Data:</h3>";
$result = $conn->query("SELECT * FROM manifest ORDER BY scan_time DESC LIMIT 10");
if ($result && $result->num_rows > 0) {
    echo "<table border='1'>";
    echo "<tr><th>ID</th><th>User ID</th><th>Scan Location</th><th>Scanned By</th><th>Status</th><th>Destination</th><th>Scan Time</th></tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['id'] . "</td>";
        echo "<td>" . $row['user_id'] . "</td>";
        echo "<td>" . htmlspecialchars($row['scan_location']) . "</td>";
        echo "<td>" . htmlspecialchars($row['scanned_by']) . "</td>";
        echo "<td>" . htmlspecialchars($row['boarding_status']) . "</td>";
        echo "<td>" . htmlspecialchars($row['destination']) . "</td>";
        echo "<td>" . $row['scan_time'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>No data found in manifest table.</p>";
}

// Check if there are any entries with "3307"
echo "<h3>Entries with '3307':</h3>";
$result = $conn->query("SELECT * FROM manifest WHERE scan_location LIKE '%3307%' OR scan_location = '3307'");
if ($result && $result->num_rows > 0) {
    echo "<table border='1'>";
    echo "<tr><th>ID</th><th>User ID</th><th>Scan Location</th><th>Scanned By</th><th>Status</th><th>Destination</th><th>Scan Time</th></tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['id'] . "</td>";
        echo "<td>" . $row['user_id'] . "</td>";
        echo "<td>" . htmlspecialchars($row['scan_location']) . "</td>";
        echo "<td>" . htmlspecialchars($row['scanned_by']) . "</td>";
        echo "<td>" . htmlspecialchars($row['boarding_status']) . "</td>";
        echo "<td>" . htmlspecialchars($row['destination']) . "</td>";
        echo "<td>" . $row['scan_time'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>No entries found with '3307'.</p>";
}
?> 