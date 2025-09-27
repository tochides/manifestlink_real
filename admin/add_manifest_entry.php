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

// Validate required fields
if (!isset($input['user_id']) || !isset($input['boarding_status'])) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit();
}

$user_id = intval($input['user_id']);
$scan_location = $input['scan_location'] ?? 'Port Terminal';
$scanned_by = $input['scanned_by'] ?? 'Port Staff';
$boarding_status = $input['boarding_status'];
$vessel_name = $input['vessel_name'] ?? null;
$vessel_number = $input['vessel_number'] ?? null;
$destination = $input['destination'] ?? null;
$departure_time = $input['departure_time'] ?? null;
$notes = $input['notes'] ?? null;

// Validate status
$valid_statuses = ['scanned', 'boarded', 'departed'];
if (!in_array($boarding_status, $valid_statuses)) {
    echo json_encode(['success' => false, 'message' => 'Invalid boarding status']);
    exit();
}

// Check if user exists
$user_check = $conn->prepare("SELECT id FROM users WHERE id = ?");
$user_check->bind_param("i", $user_id);
$user_check->execute();
if ($user_check->get_result()->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'User not found']);
    exit();
}

// Insert new manifest record
$stmt = $conn->prepare("INSERT INTO manifest (user_id, scan_location, scanned_by, boarding_status, vessel_name, vessel_number, destination, departure_time, notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param("issssssss", $user_id, $scan_location, $scanned_by, $boarding_status, $vessel_name, $vessel_number, $destination, $departure_time, $notes);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Manifest entry added successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $conn->error]);
}

$stmt->close();
$conn->close();
?> 