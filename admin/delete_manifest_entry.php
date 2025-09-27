<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    http_response_code(403);
    echo 'Unauthorized';
    exit();
}

$manifest_id = $_GET['id'] ?? '';

if (empty($manifest_id) || !is_numeric($manifest_id)) {
    echo 'Invalid manifest ID';
    exit();
}

try {
    require_once '../connect.php';
    
    // Delete the manifest entry
    $query = "DELETE FROM manifest WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $manifest_id);
    
    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            echo 'success';
        } else {
            echo 'Manifest entry not found';
        }
    } else {
        echo 'Database error';
    }
    
} catch (Exception $e) {
    error_log('Error in delete_manifest_entry.php: ' . $e->getMessage());
    echo 'Database error';
}
?> 