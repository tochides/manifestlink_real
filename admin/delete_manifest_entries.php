<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    http_response_code(403);
    echo 'Unauthorized';
    exit();
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);
$ids = $input['ids'] ?? [];

if (empty($ids) || !is_array($ids)) {
    echo 'No IDs provided';
    exit();
}

// Validate that all IDs are numeric
foreach ($ids as $id) {
    if (!is_numeric($id)) {
        echo 'Invalid ID format';
        exit();
    }
}

try {
    require_once '../connect.php';
    
    // Create placeholders for the IN clause
    $placeholders = str_repeat('?,', count($ids) - 1) . '?';
    
    // Delete the manifest entries
    $query = "DELETE FROM manifest WHERE id IN ($placeholders)";
    $stmt = $conn->prepare($query);
    
    // Bind parameters
    $types = str_repeat('i', count($ids));
    $stmt->bind_param($types, ...$ids);
    
    if ($stmt->execute()) {
        $deletedCount = $stmt->affected_rows;
        if ($deletedCount > 0) {
            echo 'success';
        } else {
            echo 'No entries found to delete';
        }
    } else {
        echo 'Database error';
    }
    
} catch (Exception $e) {
    error_log('Error in delete_manifest_entries.php: ' . $e->getMessage());
    echo 'Database error';
}
?> 