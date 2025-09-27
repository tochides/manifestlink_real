<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

include '../connect.php';

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['manifest_id']) || !isset($input['status'])) {
    echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
    exit();
}

$manifest_id = intval($input['manifest_id']);
$status = $input['status'];

// Validate status
$valid_statuses = ['scanned', 'boarded', 'departed'];
if (!in_array($status, $valid_statuses)) {
    echo json_encode(['success' => false, 'message' => 'Invalid status']);
    exit();
}

// Update the manifest record
$stmt = $conn->prepare("UPDATE manifest SET boarding_status = ? WHERE id = ?");
$stmt->bind_param("si", $status, $manifest_id);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Status updated successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $conn->error]);
}

$stmt->close();
$conn->close();
?> 