<?php
session_start();
include '../connect.php';

// Redirect if already logged in
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header('Location: index.php');
    exit();
}

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($username && $password) {
        $stmt = $conn->prepare('SELECT id, username, password_hash FROM admin_users WHERE username = ?');
        $stmt->bind_param('s', $username);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows === 1) {
            $admin = $result->fetch_assoc();
            if (password_verify($password, $admin['password_hash'])) {
                // Login success
                $_SESSION['admin_logged_in'] = true;
                $_SESSION['admin_username'] = $admin['username'];
                $_SESSION['admin_id'] = $admin['id'];
                header('Location: index.php');
                exit();
            } else {
                $message = '<div class="alert error">Invalid username or password.</div>';
            }
        } else {
            $message = '<div class="alert error">Invalid username or password.</div>';
        }
        $stmt->close();
    } else {
        $message = '<div class="alert error">Please enter both username and password.</div>';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
    <link rel="stylesheet" href="admin.css">
</head>
<body>
    <div class="login-bg">
      <div class="login-card">
        <div class="login-logo">
          <img src="../logo.png" alt="Logo">
        </div>
        <div class="login-title">Admin Login</div>
        <?php if ($message) echo '<div class="login-message">' . $message . '</div>'; ?>
        <form method="post" autocomplete="off">
          <div class="form-group">
            <label for="username">Username</label>
            <input type="text" id="username" name="username" required autofocus>
          </div>
          <div class="form-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" required>
          </div>
          <button type="submit" class="btn login-btn">Login</button>
        </form>
      </div>
    </div>
</body>
</html> 