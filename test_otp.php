<?php
/**
 * Test OTP Email System
 * This file helps test if the email system is working properly
 */

session_start();
require_once 'connect.php';
require_once 'email_config.php';

// Function to generate OTP
function generateOTP() {
    return str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
}

$message = '';
$error = '';

// Handle OTP sending
if (isset($_POST['send_otp']) && isset($_POST['user_id'])) {
    $user_id = $_POST['user_id'];
    
    // Get user details
    $stmt = $conn->prepare("SELECT full_name, email FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        $otp = generateOTP();
        $expires = date('Y-m-d H:i:s', strtotime('+10 minutes'));
        
        // Delete any existing OTP for this user
        $stmt = $conn->prepare("DELETE FROM otp_verification WHERE user_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        
        // Insert new OTP
        $stmt = $conn->prepare("INSERT INTO otp_verification (user_id, email, otp, expires_at) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("isss", $user_id, $user['email'], $otp, $expires);
        
        if ($stmt->execute()) {
            // Use the sendOTPEmail function from email_config.php
            if (sendOTPEmail($user['email'], $user['full_name'], $otp)) {
                $message = "OTP sent successfully to " . $user['email'];
                $_SESSION['testing_user_id'] = $user_id;
            } else {
                $error = "Failed to send email. Check email configuration.";
            }
        } else {
            $error = "Failed to save OTP to database.";
        }
    } else {
        $error = "User not found.";
    }
}

// Handle OTP verification
if (isset($_POST['verify_otp']) && isset($_POST['otp_code'])) {
    $otp_code = $_POST['otp_code'];
    $user_id = $_SESSION['testing_user_id'] ?? 0;
    
    if ($user_id > 0) {
        // Debug: Show what we're looking for
        $debug_info = "Looking for OTP: '$otp_code' for User ID: $user_id<br>";
        
        // First, check if OTP exists at all (without expiration check)
        $stmt = $conn->prepare("SELECT * FROM otp_verification WHERE user_id = ? AND otp = ?");
        $stmt->bind_param("is", $user_id, $otp_code);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $debug_info .= "✅ OTP found in database:<br>";
            $debug_info .= "- User ID: " . $row['user_id'] . "<br>";
            $debug_info .= "- Email: " . $row['email'] . "<br>";
            $debug_info .= "- OTP: '" . $row['otp'] . "'<br>";
            $debug_info .= "- Expires: " . $row['expires_at'] . "<br>";
            $debug_info .= "- Current time: " . date('Y-m-d H:i:s') . "<br>";
            
            // Check if expired
            if (strtotime($row['expires_at']) > time()) {
                // Delete the OTP after successful verification
                $stmt = $conn->prepare("DELETE FROM otp_verification WHERE user_id = ? AND otp = ?");
                $stmt->bind_param("is", $user_id, $otp_code);
                $stmt->execute();
                
                $message = "✅ OTP verified successfully! User can now access their QR code.";
                unset($_SESSION['testing_user_id']);
            } else {
                $error = "❌ OTP has expired. Expires: " . $row['expires_at'] . ", Current: " . date('Y-m-d H:i:s');
            }
        } else {
            $debug_info .= "❌ OTP not found in database.<br>";
            
            // Show all OTPs for this user
            $stmt = $conn->prepare("SELECT * FROM otp_verification WHERE user_id = ?");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $debug_info .= "Available OTPs for this user:<br>";
                while ($row = $result->fetch_assoc()) {
                    $debug_info .= "- OTP: '" . $row['otp'] . "' (Expires: " . $row['expires_at'] . ")<br>";
                }
            } else {
                $debug_info .= "No OTPs found for this user.<br>";
            }
            
            $error = "❌ Invalid or expired OTP code.<br><br><strong>Debug Info:</strong><br>" . $debug_info;
        }
    } else {
        $error = "No user selected for testing.";
    }
}

// Get all users
$users = [];
$result = $conn->query("SELECT id, full_name, email, created_at FROM users ORDER BY created_at DESC");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $users[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OTP System Test - ManifestLink</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #333;
            text-align: center;
            margin-bottom: 30px;
        }
        .message {
            padding: 15px;
            margin: 20px 0;
            border-radius: 5px;
        }
        .success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .user-card {
            background: #f8f9fa;
            padding: 15px;
            margin: 10px 0;
            border-radius: 5px;
            border-left: 4px solid #007bff;
        }
        .user-info {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .btn {
            background: #007bff;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
        }
        .btn:hover {
            background: #0056b3;
        }
        .btn-danger {
            background: #dc3545;
        }
        .btn-danger:hover {
            background: #c82333;
        }
        .otp-form {
            background: #e9ecef;
            padding: 20px;
            border-radius: 5px;
            margin: 20px 0;
        }
        .otp-input {
            padding: 10px;
            font-size: 18px;
            width: 200px;
            text-align: center;
            letter-spacing: 5px;
            border: 2px solid #007bff;
            border-radius: 5px;
        }
        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin: 20px 0;
        }
        .stat-card {
            background: #007bff;
            color: white;
            padding: 20px;
            border-radius: 5px;
            text-align: center;
        }
        .stat-number {
            font-size: 2em;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔐 OTP System Test Dashboard</h1>
        
        <?php if ($message): ?>
            <div class="message success"><?php echo $message; ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="message error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <!-- Statistics -->
        <div class="stats">
            <div class="stat-card">
                <div class="stat-number"><?php echo count($users); ?></div>
                <div>Total Users</div>
            </div>
            <div class="stat-card">
                <div class="stat-number">
                    <?php 
                    $result = $conn->query("SELECT COUNT(*) as count FROM otp_verification WHERE expires_at < NOW()");
                    echo $result ? $result->fetch_assoc()['count'] : 0;
                    ?>
                </div>
                <div>Expired OTPs</div>
            </div>
            <div class="stat-card">
                <div class="stat-number">
                    <?php 
                    $result = $conn->query("SELECT COUNT(*) as count FROM otp_verification WHERE expires_at > NOW()");
                    echo $result ? $result->fetch_assoc()['count'] : 0;
                    ?>
                </div>
                <div>Active OTPs</div>
            </div>
        </div>
        
        <!-- OTP Verification Form -->
        <?php if (isset($_SESSION['testing_user_id'])): ?>
            <div class="otp-form">
                <h3>🔑 Enter OTP Code</h3>
                <p>Check the email for the user and enter the 6-digit OTP code:</p>
                <form method="POST">
                    <input type="text" name="otp_code" class="otp-input" placeholder="000000" maxlength="6" pattern="[0-9]{6}" required>
                    <button type="submit" name="verify_otp" class="btn">Verify OTP</button>
                </form>
            </div>
        <?php endif; ?>
        
        <!-- Users List -->
        <h2>👥 Registered Users</h2>
        <?php if (empty($users)): ?>
            <p>No users found. <a href="register.php" class="btn">Register a new user</a></p>
        <?php else: ?>
            <?php foreach ($users as $user): ?>
                <div class="user-card">
                    <div class="user-info">
                        <div>
                            <strong><?php echo htmlspecialchars($user['full_name']); ?></strong><br>
                            <small><?php echo htmlspecialchars($user['email']); ?></small><br>
                            <small>Registered: <?php echo date('M j, Y', strtotime($user['created_at'])); ?></small>
                        </div>
                        <div>
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                <button type="submit" name="send_otp" class="btn">📧 Send OTP</button>
                            </form>
                            <a href="generatedqr.php?user_id=<?php echo $user['id']; ?>" class="btn btn-danger">🔗 View QR</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
        
        <hr style="margin: 30px 0;">
        <p style="text-align: center;">
            <a href="index.html" class="btn">🏠 Back to Home</a>
            <a href="register.php" class="btn">➕ Add New User</a>
        </p>
    </div>
    
    <script>
        // Auto-focus on OTP input
        document.querySelector('.otp-input')?.focus();
        
        // Format OTP input
        document.querySelector('.otp-input')?.addEventListener('input', function(e) {
            this.value = this.value.replace(/[^0-9]/g, '').substring(0, 6);
        });
    </script>
</body>
</html> 