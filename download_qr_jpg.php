<?php
// Prevent any output before headers
ob_start();
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(0);

include 'connect.php';

// Get user_id from GET
$user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;

if ($user_id <= 0) {
    ob_end_clean();
    header('HTTP/1.1 400 Bad Request');
    exit('Invalid user ID');
}

// Fetch QR code info
$stmt = $conn->prepare("SELECT q.qr_image_path, u.full_name FROM qr_codes q JOIN users u ON q.user_id = u.id WHERE q.user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($qr_image_path, $full_name);
$stmt->fetch();
$stmt->close();

if (!$qr_image_path || !file_exists($qr_image_path)) {
    ob_end_clean();
    header('HTTP/1.1 404 Not Found');
    exit('QR code file not found');
}

// Check if GD extension is available
if (!extension_loaded('gd')) {
    ob_end_clean();
    header('HTTP/1.1 500 Internal Server Error');
    exit('GD extension is required for image processing');
}

$png_image = @imagecreatefrompng($qr_image_path);
if (!$png_image) {
    ob_end_clean();
    header('HTTP/1.1 500 Internal Server Error');
    exit('Failed to load PNG image');
}

// Get image dimensions
$width = imagesx($png_image);
$height = imagesy($png_image);

// Create a new image with white background (JPG doesn't support transparency)
$jpg_image = imagecreatetruecolor($width, $height);
if (!$jpg_image) {
    imagedestroy($png_image);
    ob_end_clean();
    header('HTTP/1.1 500 Internal Server Error');
    exit('Failed to create JPG image');
}

$white = imagecolorallocate($jpg_image, 255, 255, 255);
imagefill($jpg_image, 0, 0, $white);

// Copy the PNG content to the JPG image
imagecopy($jpg_image, $png_image, 0, 0, 0, 0, $width, $height);

// Clean any output buffer
ob_end_clean();

// Set headers for download
$filename = 'QR_Code_' . preg_replace('/[^a-zA-Z0-9]/', '_', $full_name) . '_' . $user_id . '.jpg';

header('Content-Type: image/jpeg');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Cache-Control: no-cache, must-revalidate');
header('Expires: Sat, 26 Jul 1997 05:00:00 GMT');
header('Pragma: no-cache');

// Output the JPG image
imagejpeg($jpg_image, null, 95); // 95% quality

// Clean up
imagedestroy($png_image);
imagedestroy($jpg_image);
exit; 