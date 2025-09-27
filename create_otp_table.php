<?php
require_once 'connect.php';

echo "<h2>Creating OTP Verification Table</h2>";

// SQL to create the OTP verification table
$sql = "
CREATE TABLE IF NOT EXISTS `otp_verification` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `otp` varchar(6) NOT NULL,
  `expires_at` datetime NOT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email_otp` (`email`, `otp`),
  KEY `user_id` (`user_id`),
  KEY `expires_at` (`expires_at`),
  CONSTRAINT `fk_otp_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
";

try {
    if ($conn->query($sql) === TRUE) {
        echo "✅ OTP verification table created successfully!<br>";
    } else {
        echo "❌ Error creating table: " . $conn->error . "<br>";
    }
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "<br>";
}

// Check if table exists
$result = $conn->query("SHOW TABLES LIKE 'otp_verification'");
if ($result->num_rows > 0) {
    echo "✅ Table 'otp_verification' exists in database<br>";
} else {
    echo "❌ Table 'otp_verification' does not exist<br>";
}

echo "<br><a href='test_otp.php'>Go to OTP Test Page</a>";
?> 