<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

header('Content-Type: application/json');

$userId = $_GET['user_id'] ?? '';
$userEmail = $_GET['email'] ?? '';
$port = $_GET['port'] ?? '';
$destination = $_GET['destination'] ?? '';
$vessel = $_GET['vessel'] ?? '';

if (empty($userId) && empty($userEmail)) {
    echo json_encode(['success' => false, 'message' => 'Please provide either user ID or email']);
    exit();
}

if (!empty($userId) && !is_numeric($userId)) {
    echo json_encode(['success' => false, 'message' => 'Invalid user ID']);
    exit();
}

try {
    require_once '../connect.php';
    
    // Get user information
    if (!empty($userId)) {
        $query = "SELECT id, full_name, contact_number, email, address, age, sex, created_at 
                  FROM users 
                  WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $userId);
    } else {
        $query = "SELECT id, full_name, contact_number, email, address, age, sex, created_at 
                  FROM users 
                  WHERE email = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $userEmail);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    
    if ($user) {
        // Check if user already has a recent manifest entry (within last 5 minutes)
        $recentCheck = "SELECT id FROM manifest WHERE user_id = ? AND scan_time > DATE_SUB(NOW(), INTERVAL 5 MINUTE)";
        $recentStmt = $conn->prepare($recentCheck);
        $recentStmt->bind_param('i', $user['id']);
        $recentStmt->execute();
        $recentResult = $recentStmt->get_result();
        $recentEntry = $recentResult->fetch_assoc();
        
        $manifestCreated = false;
        $alreadyScanned = false;
        
        if ($recentEntry) {
            $alreadyScanned = true;
            // Update vessel_name for the recent entry
            if (!empty($vessel)) {
                $updateVessel = "UPDATE manifest SET vessel_name = ? WHERE id = ?";
                $updateStmt = $conn->prepare($updateVessel);
                $updateStmt->bind_param('si', $vessel, $recentEntry['id']);
                $updateStmt->execute();
            }
        } else {
            // Create new manifest entry for manual entry
            $scanLocation = $port ?: 'Port Terminal';
            $scannedBy = $_SESSION['admin_username'] ?? 'Port Staff';
            $boardingStatus = 'scanned';
            $manifestDestination = $destination ?: 'Manual Entry';
            $manifestVessel = $vessel ?: null;
            
            $manifestQuery = "INSERT INTO manifest (user_id, scan_location, scanned_by, boarding_status, vessel_name, destination) VALUES (?, ?, ?, ?, ?, ?)";
            $manifestStmt = $conn->prepare($manifestQuery);
            $manifestStmt->bind_param('isssss', $user['id'], $scanLocation, $scannedBy, $boardingStatus, $manifestVessel, $manifestDestination);
            if ($manifestStmt->execute()) {
                $manifestCreated = true;
            }
        }
        
        // Get QR code information for this user
        $qrQuery = "SELECT qr_data, qr_image_path, created_at as qr_created_at 
                    FROM qr_codes 
                    WHERE user_id = ? 
                    ORDER BY created_at DESC 
                    LIMIT 1";
        
        $qrStmt = $conn->prepare($qrQuery);
        $qrStmt->bind_param("i", $user['id']);
        $qrStmt->execute();
        $qrResult = $qrStmt->get_result();
        $qrCode = $qrResult->fetch_assoc();
        
        // Get OTP information for this user
        $otpQuery = "SELECT otp, expires_at, created_at as otp_created_at 
                     FROM otp_verification 
                     WHERE user_id = ? 
                     ORDER BY created_at DESC 
                     LIMIT 5";
        
        $otpStmt = $conn->prepare($otpQuery);
        $otpStmt->bind_param("i", $user['id']);
        $otpStmt->execute();
        $otpResult = $otpStmt->get_result();
        $otps = [];
        while ($row = $otpResult->fetch_assoc()) {
            $otps[] = $row;
        }
        
        // Prepare response data
        $response = [
            'success' => true,
            'user' => $user,
            'qr_code' => $qrCode,
            'recent_otps' => $otps,
            'manifest_created' => $manifestCreated,
            'already_scanned' => $alreadyScanned
        ];
        
        echo json_encode($response);
    } else {
        echo json_encode(['success' => false, 'message' => 'User not found']);
    }
    
} catch (Exception $e) {
    error_log('Error in get_user_manifest.php: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
?> 