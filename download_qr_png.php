<?php
include 'connect.php';

// Get user_id from GET
$user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;

if ($user_id <= 0) {
    die('Invalid user ID');
}

// Fetch QR code info
$stmt = $conn->prepare("SELECT q.qr_image_path, u.full_name FROM qr_codes q JOIN users u ON q.user_id = u.id WHERE q.user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($qr_image_path, $full_name);
$stmt->fetch();
$stmt->close();

if (!$qr_image_path || !file_exists($qr_image_path)) {
    die('QR code file not found');
}

try {
    // Set headers for download
    $filename = 'QR_Code_' . preg_replace('/[^a-zA-Z0-9]/', '_', $full_name) . '_' . $user_id . '.png';
    header('Content-Type: image/png');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Content-Length: ' . filesize($qr_image_path));
    header('Cache-Control: no-cache, must-revalidate');
    header('Expires: Sat, 26 Jul 1997 05:00:00 GMT');
    
    // Output the PNG file directly
    readfile($qr_image_path);
    
} catch (Exception $e) {
    die('Error downloading file: ' . $e->getMessage());
}
?> 