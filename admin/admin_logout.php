<?php
session_start();
// Unset all admin session variables
$_SESSION = array();
// Destroy the session
session_destroy();
// Redirect to admin login
header('Location: admin_login.php');
exit();
?> 