<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH'])) {
        echo 'fail';
        exit;
    }
    header('Location: admin_login.php');
    exit();
}
include '../connect.php';

$user_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($user_id > 0) {
    // Check if user exists
    $stmt = $conn->prepare('SELECT id FROM users WHERE id = ?');
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows === 1) {
        $stmt->close();
        // Delete user (will also delete related QR codes and OTPs due to foreign key ON DELETE CASCADE)
        $stmt = $conn->prepare('DELETE FROM users WHERE id = ?');
        $stmt->bind_param('i', $user_id);
        if ($stmt->execute()) {
            if (!empty($_SERVER['HTTP_X_REQUESTED_WITH'])) {
                echo 'success';
                exit;
            }
            header('Location: admin_users.php?msg=deleted');
            exit();
        } else {
            if (!empty($_SERVER['HTTP_X_REQUESTED_WITH'])) {
                echo 'fail';
                exit;
            }
            header('Location: admin_users.php?msg=deletefail');
            exit();
        }
    } else {
        $stmt->close();
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH'])) {
            echo 'fail';
            exit;
        }
        header('Location: admin_users.php?msg=notfound');
        exit();
    }
} else {
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH'])) {
        echo 'fail';
        exit;
    }
    header('Location: admin_users.php?msg=invalid');
    exit();
} 