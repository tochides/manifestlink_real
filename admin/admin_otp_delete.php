<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    http_response_code(403);
    exit('error');
}
include '../connect.php';
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id > 0) {
    $stmt = $conn->prepare('DELETE FROM otp_verification WHERE id = ?');
    $stmt->bind_param('i', $id);
    if ($stmt->execute()) {
        echo 'success';
    } else {
        error_log('Delete failed for id ' . $id . ': ' . $stmt->error . "\n", 3, __DIR__ . '/otp_delete_error.log');
        echo 'error';
    }
    $stmt->close();
} else {
    error_log('Invalid id received: ' . var_export($id, true) . "\n", 3, __DIR__ . '/otp_delete_error.log');
    echo 'error';
} 