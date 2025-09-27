<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

header('Content-Type: application/json');

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);
$qrData = $input['qr_data'] ?? '';
$port = $input['port'] ?? '';
$destination = $input['destination'] ?? '';
$vessel = $input['vessel'] ?? '';

if (empty($qrData)) {
    echo json_encode(['success' => false, 'message' => 'No QR data provided']);
    exit();
}

try {
    require_once '../connect.php';
    
    $userId = null;
    
    // Check if QR data contains admin URL with user_id
    if (preg_match('/Admin URL: .*user_id=(\d+)/', $qrData, $matches)) {
        $userId = $matches[1];
    } else {
        // Parse QR data to extract user information
        $lines = explode("\n", $qrData);
        $userInfo = [];
        foreach ($lines as $line) {
            $parts = explode(':', $line, 2);
            if (count($parts) == 2) {
                $key = trim($parts[0]);
                $value = trim($parts[1]);
                $userInfo[$key] = $value;
            }
        }
        $fullName = $userInfo['Full Name'] ?? '';
        $email = $userInfo['Email'] ?? '';
        $contactNumber = $userInfo['Contact Number'] ?? '';
        if (empty($fullName) && empty($email) && empty($contactNumber)) {
            echo json_encode(['success' => false, 'message' => 'Invalid QR code format']);
            exit();
        }
        // Build query to find user
        $query = "SELECT id FROM users WHERE 1=1";
        $params = [];
        $types = '';
        if (!empty($fullName)) {
            $query .= " AND full_name = ?";
            $params[] = $fullName;
            $types .= 's';
        }
        if (!empty($email)) {
            $query .= " AND email = ?";
            $params[] = $email;
            $types .= 's';
        }
        if (!empty($contactNumber)) {
            $query .= " AND contact_number = ?";
            $params[] = $contactNumber;
            $types .= 's';
        }
        $stmt = $conn->prepare($query);
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        if ($row) {
            $userId = $row['id'];
        } else {
            // If exact match not found, try partial match
            $query = "SELECT id FROM users WHERE full_name LIKE ? OR email LIKE ? OR contact_number LIKE ?";
            $searchTerm = '%' . $fullName . '%';
            $stmt = $conn->prepare($query);
            $stmt->bind_param('sss', $searchTerm, $searchTerm, $searchTerm);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            if ($row) {
                $userId = $row['id'];
            } else {
                echo json_encode(['success' => false, 'message' => 'User not found']);
                exit();
            }
        }
    }
    // Get user details
    $userQuery = "SELECT full_name, contact_number, email FROM users WHERE id = ?";
    $userStmt = $conn->prepare($userQuery);
    $userStmt->bind_param('i', $userId);
    $userStmt->execute();
    $userResult = $userStmt->get_result();
    $user = $userResult->fetch_assoc();
    if (!$user) {
        echo json_encode(['success' => false, 'message' => 'User not found']);
        exit();
    }
    // Check if user already has a recent manifest entry (within last 5 minutes)
    $recentCheck = "SELECT id FROM manifest WHERE user_id = ? AND scan_time > DATE_SUB(NOW(), INTERVAL 5 MINUTE)";
    $recentStmt = $conn->prepare($recentCheck);
    $recentStmt->bind_param('i', $userId);
    $recentStmt->execute();
    $recentResult = $recentStmt->get_result();
    $recentEntry = $recentResult->fetch_assoc();
    if ($recentEntry) {
        // Update vessel_name for the recent entry
        if (!empty($vessel)) {
            $updateVessel = "UPDATE manifest SET vessel_name = ? WHERE id = ?";
            $updateStmt = $conn->prepare($updateVessel);
            $updateStmt->bind_param('si', $vessel, $recentEntry['id']);
            $updateStmt->execute();
        }
        echo json_encode([
            'success' => true, 
            'user_id' => $userId,
            'user_name' => $user['full_name'],
            'user_contact' => $user['contact_number'],
            'message' => 'User already scanned recently',
            'already_scanned' => true
        ]);
        exit();
    }
    // Create new manifest entry
    $scanLocation = $port ?: 'Port Terminal';
    $scannedBy = $_SESSION['admin_username'] ?? 'Port Staff';
    $boardingStatus = 'scanned';
    $manifestVessel = $vessel ?: null;
    $manifestQuery = "INSERT INTO manifest (user_id, scan_location, scanned_by, boarding_status, vessel_name, destination) VALUES (?, ?, ?, ?, ?, ?)";
    $manifestStmt = $conn->prepare($manifestQuery);
    $manifestStmt->bind_param('isssss', $userId, $scanLocation, $scannedBy, $boardingStatus, $manifestVessel, $destination);
    if ($manifestStmt->execute()) {
        echo json_encode([
            'success' => true, 
            'user_id' => $userId,
            'user_name' => $user['full_name'],
            'user_contact' => $user['contact_number'],
            'message' => 'QR code scanned successfully - manifest entry created',
            'manifest_created' => true
        ]);
    } else {
        echo json_encode([
            'success' => true, 
            'user_id' => $userId,
            'user_name' => $user['full_name'],
            'user_contact' => $user['contact_number'],
            'message' => 'User found but manifest entry creation failed'
        ]);
    }
    
} catch (Exception $e) {
    error_log('Error in find_user_by_qr.php: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
?> 