<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
include 'connect.php'; // Ensure this connects $conn to your database
include 'phpqrcode/qrlib.php'; // Add this line
session_start();

$message = '';
$existingUser = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data and sanitize
    $fullName = trim($_POST['fullName'] ?? '');
    $contactNumber = trim($_POST['contactNumber'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $age = isset($_POST['age']) ? intval($_POST['age']) : null;
    $sex = trim($_POST['sex'] ?? '');

    // Simple validation
    if ($fullName && $contactNumber && $email && $address && $age && $sex) {
        // First, check if user already exists by email
        $checkStmt = $conn->prepare("SELECT id, full_name, contact_number, email, address, age, sex FROM users WHERE email = ?");
        $checkStmt->bind_param("s", $email);
        $checkStmt->execute();
        $result = $checkStmt->get_result();
        
        if ($result->num_rows > 0) {
            // User already exists, get their information
            $existingUser = $result->fetch_assoc();
            
            // Check if they already have a QR code
            $qrCheckStmt = $conn->prepare("SELECT qr_image_path FROM qr_codes WHERE user_id = ?");
            $qrCheckStmt->bind_param("i", $existingUser['id']);
            $qrCheckStmt->execute();
            $qrResult = $qrCheckStmt->get_result();
            
            if ($qrResult->num_rows > 0) {
                // QR code already exists, redirect to show it
                header('Location: generatedqr.php?user_id=' . $existingUser['id'] . '&existing=1');
                exit();
            } else {
                // User exists but no QR code, create one
                $user_id = $existingUser['id'];
                
                // Generate QR code data with admin URL
                $admin_url = "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['REQUEST_URI']) . "/admin/?page=qr_scanner&user_id=" . $user_id;
                $qr_data = "Full Name: " . $existingUser['full_name'] . "\nContact Number: " . $existingUser['contact_number'] . "\nEmail: " . $existingUser['email'] . "\nAddress: " . $existingUser['address'] . "\nAge: " . $existingUser['age'] . "\nSex: " . $existingUser['sex'] . "\nAdmin URL: " . $admin_url;
                $qr_filename = "qrcodes/user_" . $user_id . ".png";
                QRcode::png($qr_data, $qr_filename, QR_ECLEVEL_L, 8);

                // Save QR code info to qr_codes table
                $stmt2 = $conn->prepare("INSERT INTO qr_codes (user_id, qr_data, qr_image_path) VALUES (?, ?, ?)");
                $stmt2->bind_param("iss", $user_id, $qr_data, $qr_filename);
                $stmt2->execute();
                $stmt2->close();

                // Redirect to generatedqr.php with user_id
                header('Location: generatedqr.php?user_id=' . $user_id);
                exit();
            }
        } else {
            // New user, create registration
            $stmt = $conn->prepare("INSERT INTO users (full_name, contact_number, email, address, age, sex) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssis", $fullName, $contactNumber, $email, $address, $age, $sex);
            
            if ($stmt->execute()) {
                $user_id = $stmt->insert_id; // Get the new user's ID

                // Generate QR code data with admin URL (include all required fields)
                $admin_url = "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['REQUEST_URI']) . "/admin/?page=qr_scanner&user_id=" . $user_id;
                $qr_data = "Full Name: $fullName\nContact Number: $contactNumber\nEmail: $email\nAddress: $address\nAge: $age\nSex: $sex\nAdmin URL: " . $admin_url;
                $qr_filename = "qrcodes/user_" . $user_id . ".png";
                QRcode::png($qr_data, $qr_filename, QR_ECLEVEL_L, 8);

                // Save QR code info to qr_codes table
                $stmt2 = $conn->prepare("INSERT INTO qr_codes (user_id, qr_data, qr_image_path) VALUES (?, ?, ?)");
                $stmt2->bind_param("iss", $user_id, $qr_data, $qr_filename);
                $stmt2->execute();
                $stmt2->close();

                // Redirect to generatedqr.php with user_id
                header('Location: generatedqr.php?user_id=' . $user_id);
                exit();
            } else {
                $message = '<div class="alert error">There was a problem saving your information. Please try again.</div>';
            }
            $stmt->close();
        }
        $checkStmt->close();
    } else {
        $message = '<div class="alert error">All fields are required. Please complete the form.</div>';
    }
}

// Check if user is looking up existing QR code
if (isset($_GET['email']) && !empty($_GET['email'])) {
    $lookupEmail = trim($_GET['email']);
    $lookupStmt = $conn->prepare("SELECT u.id, u.full_name, u.contact_number, u.email, u.address, u.age, u.sex, q.qr_image_path 
                                  FROM users u 
                                  LEFT JOIN qr_codes q ON u.id = q.user_id 
                                  WHERE u.email = ?");
    $lookupStmt->bind_param("s", $lookupEmail);
    $lookupStmt->execute();
    $lookupResult = $lookupStmt->get_result();
    
    if ($lookupResult->num_rows > 0) {
        $existingUser = $lookupResult->fetch_assoc();
        if ($existingUser['qr_image_path']) {
            // QR code exists, redirect to show it
            header('Location: generatedqr.php?user_id=' . $existingUser['id'] . '&existing=1');
            exit();
        }
    }
    $lookupStmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Passenger Registration - ManifestLink</title>
    <meta name="description" content="Register for your maritime journey with ManifestLink. Quick and secure passenger registration for Guimaras Port.">
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="logo.png">
    
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="css/register.css?v=<?php echo time(); ?>">
    
    <!-- Custom JavaScript -->
    <script src="script.js?v=<?php echo time(); ?>"></script>
    
    <!-- User-Friendly Navigation Styles (Same as index.html) -->
    <style>
        /* Add right margin to nav logo for desktop spacing */
        @media (min-width: 900px) {
            .nav-logo {
                margin-right: 2.5rem;
            }
        }
        /* Force Register Now button text in nav to be white */
        .btn-register,
        .btn-register span,
        .btn-register i {
            color: #fff !important;
        }
        /* Uniform navbar button sizing */
        .nav-btn {
            min-width: 120px;
            height: 44px;
            font-size: 1.05rem;
            padding: 0.7rem 1.5rem;
            box-sizing: border-box;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 25px;
            font-weight: 600;
        }
        .nav-link, .btn-register, .lang-btn {
            min-width: 120px;
            height: 44px;
            font-size: 1.05rem;
            padding: 0.7rem 1.5rem;
            box-sizing: border-box;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 25px;
            font-weight: 600;
        }
        /* Restore nav-right and language-switcher layout for language button */
        .nav-right {
            display: flex;
            align-items: center;
            gap: 18px;
            margin-left: auto;
        }
        .language-switcher {
            display: flex;
            align-items: center;
        }
        /* Restore language button styles */
        .lang-btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 25px;
            color: white;
            padding: clamp(8px, 1vw, 12px) clamp(14px, 1.5vw, 20px);
            cursor: pointer;
            font-size: clamp(13px, 1.2vw, 15px);
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 3px 12px rgba(102, 126, 234, 0.3);
            white-space: nowrap;
        }
        .lang-btn:hover, .lang-btn:focus {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(102, 126, 234, 0.4);
            background: linear-gradient(135deg, #5a67d8 0%, #6b46c1 100%);
        }
        .lang-btn i {
            font-size: clamp(15px, 1.3vw, 17px);
        }
        .lang-btn span {
            font-weight: 600;
            letter-spacing: 0.5px;
        }
        /* Desktop nav alignment fix */
        .nav-center {
            flex: 1;
            display: flex;
            justify-content: center;
        }
        .nav-menu {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 1.5rem;
        }
    </style>
    
    <!-- Professional Form Styling -->
    <style>
        /* Professional Background */
        body {
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%) !important;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }
        
        /* Enhanced Registration Card */
        .registration-card {
            background: rgba(255, 255, 255, 0.95) !important;
            backdrop-filter: blur(20px) !important;
            border: 1px solid rgba(255, 255, 255, 0.2) !important;
            box-shadow: 
                0 25px 50px -12px rgba(0, 0, 0, 0.15),
                0 0 0 1px rgba(255, 255, 255, 0.1) !important;
            border-radius: 24px !important;
            padding: 3rem !important;
            max-width: 800px !important;
            margin: 2rem auto !important;
            position: relative !important;
            overflow: hidden !important;
        }
        
        .registration-card::before {
            content: '' !important;
            position: absolute !important;
            top: 0 !important;
            left: 0 !important;
            right: 0 !important;
            height: 4px !important;
            background: linear-gradient(90deg, #1e3a8a, #3b82f6, #06b6d4) !important;
            border-radius: 24px 24px 0 0 !important;
        }
        
        /* Professional Header */
        .registration-header {
            text-align: center !important;
            margin-bottom: 2.5rem !important;
            padding-bottom: 2rem !important;
            border-bottom: 2px solid #f1f5f9 !important;
        }
        
        .registration-header h1 {
            font-size: 2.5rem !important;
            font-weight: 800 !important;
            color: #1e293b !important;
            margin-bottom: 0.75rem !important;
            letter-spacing: -0.025em !important;
        }
        
        .registration-header p {
            font-size: 1.125rem !important;
            color: #64748b !important;
            font-weight: 400 !important;
            line-height: 1.6 !important;
        }
        
        /* Enhanced Form Groups with Validation */
        .form-group {
            margin-bottom: 1.75rem !important;
            position: relative !important;
        }
        
        .form-group label {
            display: flex !important;
            align-items: center !important;
            font-weight: 600 !important;
            color: #374151 !important;
            margin-bottom: 0.75rem !important;
            font-size: 0.95rem !important;
            letter-spacing: 0.025em !important;
            gap: 0.5rem !important;
        }
        
        .form-group label i {
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            width: 20px !important;
            height: 20px !important;
            background: linear-gradient(135deg, #3b82f6, #1e3a8a) !important;
            color: white !important;
            border-radius: 6px !important;
            font-size: 0.75rem !important;
            font-weight: 600 !important;
            box-shadow: 0 2px 4px rgba(59, 130, 246, 0.2) !important;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
        }
        
        .form-group:hover label i {
            transform: scale(1.1) !important;
            box-shadow: 0 4px 8px rgba(59, 130, 246, 0.3) !important;
        }
        
        .form-group input,
        .form-group textarea,
        .form-group select {
            width: 100% !important;
            padding: 1rem 1.25rem !important;
            border: 2px solid #e5e7eb !important;
            border-radius: 12px !important;
            font-size: 1rem !important;
            font-weight: 500 !important;
            color: #1f2937 !important;
            background: #ffffff !important;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05) !important;
            position: relative !important;
        }
        
        .form-group input:focus,
        .form-group textarea:focus,
        .form-group select:focus {
            outline: none !important;
            border-color: #3b82f6 !important;
            box-shadow: 
                0 0 0 3px rgba(59, 130, 246, 0.1),
                0 4px 6px -1px rgba(0, 0, 0, 0.1) !important;
            transform: translateY(-1px) !important;
        }
        
        .form-group input::placeholder,
        .form-group textarea::placeholder {
            color: #9ca3af !important;
            font-weight: 400 !important;
        }
        
        /* Remove old CSS-based validation icons */
        .form-group.valid::after,
        .form-group.error::after {
            display: none !important;
        }
        
        /* Validation Messages with Enhanced Icons */
        .validation-message {
            display: none !important;
            margin-top: 0.5rem !important;
            padding: 0.75rem 1rem !important;
            border-radius: 8px !important;
            font-size: 0.875rem !important;
            font-weight: 500 !important;
            animation: slideIn 0.3s ease-out !important;
        }
        
        .validation-message.error {
            background: #fef2f2 !important;
            color: #dc2626 !important;
            border: 1px solid #fecaca !important;
            display: flex !important;
            align-items: center !important;
            gap: 0.75rem !important;
        }
        
        .validation-message.success {
            background: #f0fdf4 !important;
            color: #16a34a !important;
            border: 1px solid #bbf7d0 !important;
            display: flex !important;
            align-items: center !important;
            gap: 0.75rem !important;
        }
        
        .validation-message.info {
            background: #eff6ff !important;
            color: #2563eb !important;
            border: 1px solid #bfdbfe !important;
            display: flex !important;
            align-items: center !important;
            gap: 0.75rem !important;
        }
        
        .validation-message i {
            font-size: 1rem !important;
            width: 16px !important;
            text-align: center !important;
        }
        
        @keyframes slideIn {
            from {
                opacity: 0 !important;
                transform: translateY(-10px) !important;
            }
            to {
                opacity: 1 !important;
                transform: translateY(0) !important;
            }
        }
        
        /* Enhanced Toast Notifications with Better Icons */
        .toast-container {
            position: fixed !important;
            top: 100px !important;
            right: 20px !important;
            z-index: 9999 !important;
            display: flex !important;
            flex-direction: column !important;
            gap: 1rem !important;
        }
        
        .toast {
            background: white !important;
            border-radius: 12px !important;
            padding: 1rem 1.5rem !important;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15) !important;
            border-left: 4px solid !important;
            min-width: 300px !important;
            max-width: 400px !important;
            animation: slideInRight 0.3s ease-out !important;
            display: flex !important;
            align-items: center !important;
            gap: 0.75rem !important;
        }
        
        .toast.success {
            border-left-color: #10b981 !important;
        }
        
        .toast.error {
            border-left-color: #ef4444 !important;
        }
        
        .toast.info {
            border-left-color: #3b82f6 !important;
        }
        
        .toast.warning {
            border-left-color: #f59e0b !important;
        }
        
        .toast-icon {
            font-size: 1.25rem !important;
            flex-shrink: 0 !important;
            width: 24px !important;
            height: 24px !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            border-radius: 50% !important;
            background: rgba(255, 255, 255, 0.9) !important;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1) !important;
        }
        
        .toast.success .toast-icon {
            color: #10b981 !important;
            background: #ecfdf5 !important;
        }
        
        .toast.error .toast-icon {
            color: #ef4444 !important;
            background: #fef2f2 !important;
        }
        
        .toast.info .toast-icon {
            color: #3b82f6 !important;
            background: #eff6ff !important;
        }
        
        .toast.warning .toast-icon {
            color: #f59e0b !important;
            background: #fffbeb !important;
        }
        
        .toast-content {
            flex: 1 !important;
        }
        
        .toast-title {
            font-weight: 600 !important;
            font-size: 0.95rem !important;
            margin-bottom: 0.25rem !important;
            color: #1f2937 !important;
        }
        
        .toast-message {
            font-size: 0.875rem !important;
            color: #6b7280 !important;
            line-height: 1.4 !important;
        }
        
        .toast-close {
            background: none !important;
            border: none !important;
            color: #9ca3af !important;
            cursor: pointer !important;
            padding: 0.5rem !important;
            border-radius: 6px !important;
            transition: all 0.2s !important;
            font-size: 1rem !important;
            width: 32px !important;
            height: 32px !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
        }
        
        .toast-close:hover {
            background: #f3f4f6 !important;
            color: #6b7280 !important;
            transform: scale(1.1) !important;
        }
        
        @keyframes slideInRight {
            from {
                opacity: 0 !important;
                transform: translateX(100%) !important;
            }
            to {
                opacity: 1 !important;
                transform: translateX(0) !important;
            }
        }
        
        /* Form Row Layout */
        .form-row {
            display: grid !important;
            grid-template-columns: 1fr 1fr !important;
            gap: 1.5rem !important;
            margin-bottom: 1.75rem !important;
        }
        
        /* Professional Buttons */
        .form-actions {
            display: flex !important;
            gap: 1rem !important;
            margin-top: 2.5rem !important;
            padding-top: 2rem !important;
            border-top: 2px solid #f1f5f9 !important;
        }
        
        .btn {
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            gap: 0.75rem !important;
            padding: 1rem 2rem !important;
            font-size: 1rem !important;
            font-weight: 600 !important;
            text-decoration: none !important;
            border: none !important;
            border-radius: 12px !important;
            cursor: pointer !important;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
            position: relative !important;
            overflow: hidden !important;
            min-width: 160px !important;
            height: 56px !important;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%) !important;
            color: white !important;
            box-shadow: 
                0 4px 6px -1px rgba(30, 58, 138, 0.2),
                0 2px 4px -1px rgba(30, 58, 138, 0.1) !important;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px) !important;
            box-shadow: 
                0 10px 15px -3px rgba(30, 58, 138, 0.3),
                0 4px 6px -2px rgba(30, 58, 138, 0.2) !important;
        }
        
        .btn-outline {
            background: transparent !important;
            color: #374151 !important;
            border: 2px solid #d1d5db !important;
        }
        
        .btn-outline:hover {
            background: #f9fafb !important;
            border-color: #9ca3af !important;
            transform: translateY(-1px) !important;
        }
        
        /* Security Badge */
        .form-footer {
            text-align: center !important;
            margin-top: 2rem !important;
            padding-top: 1.5rem !important;
            border-top: 1px solid #f1f5f9 !important;
        }
        
        .security-badge {
            display: inline-flex !important;
            align-items: center !important;
            gap: 0.5rem !important;
            padding: 0.75rem 1.5rem !important;
            background: #f0f9ff !important;
            color: #0369a1 !important;
            border-radius: 50px !important;
            font-size: 0.875rem !important;
            font-weight: 500 !important;
            border: 1px solid #bae6fd !important;
        }
        
        .security-badge i {
            color: #0ea5e9 !important;
        }
        
        /* Alert Messages */
        .alert {
            padding: 1rem 1.5rem !important;
            border-radius: 12px !important;
            margin-bottom: 1.5rem !important;
            font-weight: 500 !important;
            display: flex !important;
            align-items: center !important;
            gap: 0.75rem !important;
        }
        
        .alert.error {
            background: #fef2f2 !important;
            color: #dc2626 !important;
            border: 1px solid #fecaca !important;
        }
        
        .alert.success {
            background: #f0fdf4 !important;
            color: #16a34a !important;
            border: 1px solid #bbf7d0 !important;
        }
        
        .alert.info {
            background: #eff6ff !important;
            color: #2563eb !important;
            border: 1px solid #bfdbfe !important;
        }
        
        /* Responsive Design */
        @media (max-width: 768px) {
            .registration-card {
                margin: 1rem !important;
                padding: 2rem !important;
                border-radius: 16px !important;
            }
            
            .registration-header h1 {
                font-size: 2rem !important;
            }
            
            .form-row {
                grid-template-columns: 1fr !important;
                gap: 1rem !important;
            }
            
            .form-actions {
                flex-direction: column !important;
            }
            
            .btn {
                width: 100% !important;
            }
            
            .toast-container {
                right: 10px !important;
                left: 10px !important;
            }
            
            .toast {
                min-width: auto !important;
                max-width: none !important;
            }
        }
        
        @media (max-width: 480px) {
            .registration-card {
                padding: 1.5rem !important;
                margin: 0.5rem !important;
            }
            
            .registration-header h1 {
                font-size: 1.75rem !important;
            }
            
            .form-group input,
            .form-group textarea,
            .form-group select {
                padding: 0.875rem 1rem !important;
            }
        }
        
        /* Lookup Section */
        .lookup-section {
            margin-bottom: 2rem !important;
        }
        
        .lookup-card {
            background: #f8fafc !important;
            border: 2px solid #e2e8f0 !important;
            border-radius: 16px !important;
            padding: 1.5rem !important;
            text-align: center !important;
            transition: all 0.3s ease !important;
        }
        
        .lookup-card:hover {
            border-color: #3b82f6 !important;
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.1) !important;
        }
        
        .lookup-card h3 {
            color: #1e293b !important;
            font-size: 1.25rem !important;
            font-weight: 600 !important;
            margin-bottom: 0.5rem !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            gap: 0.5rem !important;
        }
        
        .lookup-card h3 i {
            color: #3b82f6 !important;
        }
        
        .lookup-card p {
            color: #64748b !important;
            font-size: 0.95rem !important;
            margin-bottom: 1.5rem !important;
        }
        
        .lookup-form {
            display: flex !important;
            gap: 1rem !important;
            align-items: center !important;
            justify-content: center !important;
        }
        
        .lookup-input-group {
            display: flex !important;
            gap: 0.75rem !important;
            width: 100% !important;
            max-width: 500px !important;
        }
        
        .lookup-input-group input {
            flex: 1 !important;
            padding: 0.875rem 1rem !important;
            border: 2px solid #e5e7eb !important;
            border-radius: 8px !important;
            font-size: 0.95rem !important;
            transition: all 0.3s ease !important;
        }
        
        .lookup-input-group input:focus {
            outline: none !important;
            border-color: #3b82f6 !important;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1) !important;
        }
        
        .btn-secondary {
            background: #64748b !important;
            color: white !important;
            border: none !important;
            padding: 0.875rem 1.5rem !important;
            border-radius: 8px !important;
            font-weight: 500 !important;
            cursor: pointer !important;
            transition: all 0.3s ease !important;
            display: flex !important;
            align-items: center !important;
            gap: 0.5rem !important;
            white-space: nowrap !important;
        }
        
        .btn-secondary:hover {
            background: #475569 !important;
            transform: translateY(-1px) !important;
            box-shadow: 0 4px 8px rgba(100, 116, 139, 0.3) !important;
        }
        
        /* Divider */
        .divider {
            text-align: center !important;
            margin: 2rem 0 !important;
            position: relative !important;
        }
        
        .divider::before {
            content: '' !important;
            position: absolute !important;
            top: 50% !important;
            left: 0 !important;
            right: 0 !important;
            height: 1px !important;
            background: #e5e7eb !important;
        }
        
        .divider span {
            background: white !important;
            padding: 0 1rem !important;
            color: #6b7280 !important;
            font-weight: 500 !important;
            font-size: 0.875rem !important;
            position: relative !important;
            z-index: 1 !important;
        }
    </style>
    
    <!-- Enhanced Validation JavaScript -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('registration-form');
            const inputs = form.querySelectorAll('input, textarea, select');
            
            // Create toast container
            const toastContainer = document.createElement('div');
            toastContainer.className = 'toast-container';
            document.body.appendChild(toastContainer);
            
            // Validation rules
            const validationRules = {
                fullName: {
                    required: true,
                    minLength: 2,
                    pattern: /^[a-zA-Z\s]+$/,
                    message: 'Please enter a valid full name (letters and spaces only)'
                },
                contactNumber: {
                    required: true,
                    pattern: /^[\d\s\-\+\(\)]+$/,
                    minLength: 10,
                    message: 'Please enter a valid contact number'
                },
                email: {
                    required: true,
                    pattern: /^[^\s@]+@[^\s@]+\.[^\s@]+$/,
                    message: 'Please enter a valid email address'
                },
                address: {
                    required: true,
                    message: 'Please enter your address'
                },
                age: {
                    required: true,
                    min: 1,
                    max: 120,
                    message: 'Please enter a valid age between 1 and 120'
                },
                sex: {
                    required: true,
                    message: 'Please select your sex'
                }
            };
            
            // Show toast notification
            function showToast(type, title, message) {
                const toast = document.createElement('div');
                toast.className = `toast ${type}`;
                
                const icons = {
                    success: 'fas fa-check-circle',
                    error: 'fas fa-exclamation-circle',
                    warning: 'fas fa-exclamation-triangle',
                    info: 'fas fa-info-circle'
                };
                
                toast.innerHTML = `
                    <i class="${icons[type]} toast-icon"></i>
                    <div class="toast-content">
                        <div class="toast-title">${title}</div>
                        <div class="toast-message">${message}</div>
                    </div>
                    <button class="toast-close" onclick="this.parentElement.remove()">
                        <i class="fas fa-times"></i>
                    </button>
                `;
                
                toastContainer.appendChild(toast);
                
                // Auto remove after 5 seconds
                setTimeout(() => {
                    if (toast.parentElement) {
                        toast.remove();
                    }
                }, 5000);
            }
            
            // Create enhanced validation icon
            function createValidationIcon(type) {
                const icon = document.createElement('div');
                icon.className = 'validation-icon';
                icon.style.cssText = `
                    position: absolute !important;
                    right: 1.25rem !important;
                    top: 50% !important;
                    transform: translateY(-50%) !important;
                    width: 24px !important;
                    height: 24px !important;
                    border-radius: 50% !important;
                    display: flex !important;
                    align-items: center !important;
                    justify-content: center !important;
                    font-size: 0.875rem !important;
                    font-weight: 900 !important;
                    z-index: 10 !important;
                    transition: all 0.4s cubic-bezier(0.68, -0.55, 0.265, 1.55) !important;
                    opacity: 0 !important;
                    scale: 0 !important;
                    pointer-events: none !important;
                `;
                
                if (type === 'valid') {
                    icon.style.background = 'linear-gradient(135deg, #10b981, #059669) !important';
                    icon.style.color = 'white !important';
                    icon.style.boxShadow = '0 4px 12px rgba(16, 185, 129, 0.4) !important';
                    icon.innerHTML = '<i class="fas fa-check"></i>';
                } else {
                    icon.style.background = 'linear-gradient(135deg, #ef4444, #dc2626) !important';
                    icon.style.color = 'white !important';
                    icon.style.boxShadow = '0 4px 12px rgba(239, 68, 68, 0.4) !important';
                    icon.innerHTML = '<i class="fas fa-times"></i>';
                }
                
                return icon;
            }
            
            // Animate validation icon
            function animateValidationIcon(field, type) {
                const formGroup = field.closest('.form-group');
                
                // Remove existing icon
                const existingIcon = formGroup.querySelector('.validation-icon');
                if (existingIcon) {
                    existingIcon.remove();
                }
                
                // Create new icon
                const newIcon = createValidationIcon(type);
                formGroup.appendChild(newIcon);
                
                // Position the icon relative to the input field
                const fieldRect = field.getBoundingClientRect();
                const formGroupRect = formGroup.getBoundingClientRect();
                
                // Calculate position relative to form group
                const iconTop = fieldRect.top - formGroupRect.top + (fieldRect.height / 2);
                newIcon.style.top = `${iconTop}px !important`;
                
                // Animate in
                setTimeout(() => {
                    newIcon.style.opacity = '1 !important';
                    newIcon.style.scale = '1 !important';
                    
                    // Add pulse effect for valid icons
                    if (type === 'valid') {
                        newIcon.style.animation = 'iconPulse 0.6s ease-out !important';
                    }
                }, 50);
                
                return newIcon;
            }
            
            // Add CSS animations
            const style = document.createElement('style');
            style.textContent = `
                @keyframes iconPulse {
                    0% { transform: translateY(-50%) scale(1) !important; }
                    50% { transform: translateY(-50%) scale(1.3) !important; }
                    100% { transform: translateY(-50%) scale(1) !important; }
                }
                
                @keyframes iconBounce {
                    0%, 20%, 53%, 80%, 100% { transform: translateY(-50%) scale(1) !important; }
                    40%, 43% { transform: translateY(-50%) scale(1.1) !important; }
                    70% { transform: translateY(-50%) scale(0.9) !important; }
                    90% { transform: translateY(-50%) scale(1.05) !important; }
                }
                
                @keyframes iconShake {
                    0%, 100% { transform: translateY(-50%) translateX(0) !important; }
                    10%, 30%, 50%, 70%, 90% { transform: translateY(-50%) translateX(-2px) !important; }
                    20%, 40%, 60%, 80% { transform: translateY(-50%) translateX(2px) !important; }
                }
                
                .validation-icon.success {
                    animation: iconPulse 0.6s ease-out !important;
                }
                
                .validation-icon.error {
                    animation: iconShake 0.5s ease-in-out !important;
                }
            `;
            document.head.appendChild(style);
            
            // Validate single field with enhanced feedback
            function validateField(field) {
                const fieldName = field.name;
                const value = field.value.trim();
                const rules = validationRules[fieldName];
                const formGroup = field.closest('.form-group');
                
                // Remove existing validation message
                const existingMessage = formGroup.querySelector('.validation-message');
                if (existingMessage) {
                    existingMessage.remove();
                }
                
                // Remove existing validation classes
                formGroup.classList.remove('valid', 'error');
                
                if (!rules) return true;
                
                let isValid = true;
                let message = '';
                
                // Required validation
                if (rules.required && !value) {
                    isValid = false;
                    message = 'This field is required';
                }
                // Pattern validation
                else if (rules.pattern && value && !rules.pattern.test(value)) {
                    isValid = false;
                    message = rules.message;
                }
                // Min length validation
                else if (rules.minLength && value && value.length < rules.minLength) {
                    isValid = false;
                    message = rules.message;
                }
                // Min/Max value validation
                else if (rules.min !== undefined && value && parseInt(value) < rules.min) {
                    isValid = false;
                    message = rules.message;
                }
                else if (rules.max !== undefined && value && parseInt(value) > rules.max) {
                    isValid = false;
                    message = rules.message;
                }
                
                // Apply validation state with enhanced visual feedback
                if (value) {
                    if (isValid) {
                        formGroup.classList.add('valid');
                        
                        // Animate validation icon
                        const icon = animateValidationIcon(field, 'valid');
                        
                        // Add success sound effect (optional)
                        if (fieldName === 'email') {
                            showToast('success', 'Email Valid', 'Email format looks good!');
                        }
                        
                        // Add subtle field highlight
                        field.style.borderColor = '#10b981 !important';
                        field.style.background = '#f0fdf4 !important';
                        field.style.boxShadow = '0 0 0 3px rgba(16, 185, 129, 0.1) !important';
                        
                        // Add success message
                        const successMessage = document.createElement('div');
                        successMessage.className = 'validation-message success';
                        successMessage.innerHTML = `
                            <i class="fas fa-check-circle"></i>
                            <span>${fieldName === 'fullName' ? 'Name looks good!' : 
                                   fieldName === 'contactNumber' ? 'Phone number is valid!' :
                                   fieldName === 'email' ? 'Email format is correct!' :
                                   fieldName === 'address' ? 'Address entered!' :
                                   fieldName === 'age' ? 'Age is valid!' :
                                   'Selection made!'}</span>
                        `;
                        formGroup.appendChild(successMessage);
                        
                        // Auto-hide success message after 3 seconds
                        setTimeout(() => {
                            if (successMessage.parentElement) {
                                successMessage.style.opacity = '0';
                                successMessage.style.transform = 'translateY(-10px)';
                                setTimeout(() => successMessage.remove(), 300);
                            }
                        }, 3000);
                        
                    } else {
                        formGroup.classList.add('error');
                        
                        // Animate error icon
                        const icon = animateValidationIcon(field, 'error');
                        
                        // Add error styling
                        field.style.borderColor = '#ef4444 !important';
                        field.style.background = '#fef2f2 !important';
                        field.style.boxShadow = '0 0 0 3px rgba(239, 68, 68, 0.1) !important';
                        
                        showToast('error', 'Validation Error', message);
                    }
                } else {
                    // Reset field styling when empty
                    field.style.borderColor = '#e5e7eb !important';
                    field.style.background = '#ffffff !important';
                    field.style.boxShadow = '0 1px 3px rgba(0, 0, 0, 0.05) !important';
                }
                
                // Show validation message for errors
                if (message && !isValid) {
                    const messageElement = document.createElement('div');
                    messageElement.className = 'validation-message error';
                    messageElement.innerHTML = `
                        <i class="fas fa-exclamation-circle"></i>
                        ${message}
                    `;
                    formGroup.appendChild(messageElement);
                }
                
                return isValid;
            }
            
            // Real-time validation with enhanced feedback
            inputs.forEach(input => {
                input.addEventListener('blur', () => validateField(input));
                input.addEventListener('input', () => {
                    const formGroup = input.closest('.form-group');
                    formGroup.classList.remove('valid', 'error');
                    
                    // Remove validation messages
                    const message = formGroup.querySelector('.validation-message');
                    if (message) message.remove();
                    
                    // Remove validation icons
                    const icon = formGroup.querySelector('.validation-icon');
                    if (icon) icon.remove();
                    
                    // Reset field styling
                    input.style.borderColor = '#e5e7eb !important';
                    input.style.background = '#ffffff !important';
                    input.style.boxShadow = '0 1px 3px rgba(0, 0, 0, 0.05) !important';
                });
                
                // Add focus effects
                input.addEventListener('focus', () => {
                    input.style.borderColor = '#3b82f6 !important';
                    input.style.boxShadow = '0 0 0 3px rgba(59, 130, 246, 0.1) !important';
                    input.style.transform = 'translateY(-1px) !important';
                });
            });
            
            // Form submission validation
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                
                let isValid = true;
                const errors = [];
                
                // Validate all fields
                inputs.forEach(input => {
                    if (!validateField(input)) {
                        isValid = false;
                        errors.push(input.name);
                    }
                });
                
                if (isValid) {
                    // Show success message
                    showToast('success', 'Form Valid', 'All fields look good! Submitting...');
                    
                    // Add loading state to submit button
                    const submitBtn = form.querySelector('button[type="submit"]');
                    const originalText = submitBtn.innerHTML;
                    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
                    submitBtn.disabled = true;
                    
                    // Submit form after a short delay
                    setTimeout(() => {
                        form.submit();
                    }, 1500);
                } else {
                    showToast('error', 'Validation Failed', `Please fix ${errors.length} field(s) before submitting.`);
                    
                    // Scroll to first error
                    const firstError = form.querySelector('.form-group.error');
                    if (firstError) {
                        firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    }
                }
            });
            
            // Show welcome message
            setTimeout(() => {
                showToast('info', 'Welcome!', 'Please fill in all required fields to register.');
            }, 1000);
        });
    </script>
</head>
<body>

    <nav class="navbar" role="navigation" aria-label="Main navigation">
        <div class="nav-container">
            <a href="index.html" class="nav-logo" aria-label="ManifestLink Home">
                <img src="logo.png" alt="ManifestLink Logo" class="logo-img">
                <span class="logo-text">ManifestLink</span>
            </a>
            <div class="nav-center">
                <ul class="nav-menu" role="menubar">
                    <li role="none"><a href="#home" class="nav-link nav-btn active" role="menuitem">Home</a></li>
                    <li role="none"><a href="#features" class="nav-link nav-btn" role="menuitem">Features</a></li>
                    <li role="none"><a href="#guide" class="nav-link nav-btn" role="menuitem">Guide</a></li>
                    <li role="none"><a href="#about" class="nav-link nav-btn" role="menuitem">About</a></li>
                    <li role="none"><a href="#contact" class="nav-link nav-btn" role="menuitem">Contact</a></li>
                </ul>
            </div>
            <div class="nav-right">
                <!-- Language Switcher -->
                <div class="language-switcher">
                    <button class="lang-btn nav-btn" id="langToggle" aria-label="Switch Language">
                        <i class="fas fa-globe"></i>
                        <span id="currentLang">EN</span>
                    </button>
                </div>
                <div class="hamburger" aria-label="Toggle navigation menu" role="button" tabindex="0">
                    <span class="bar"></span>
                    <span class="bar"></span>
                    <span class="bar"></span>
                </div>
            </div>
        </div>
    </nav>


    <!-- Professional Registration Form -->
    <section class="registration-section">
        <div class="container">
            <div class="registration-card">
                <div class="registration-header">
                    <div class="header-icon">
                        <img src="logo.png" alt="ManifestLink Logo" class="header-logo">
                    </div>
                    <h1 data-translate="register.title">Passenger Registration</h1>
                    <p data-translate="register.subtitle">Complete your registration to generate your QR code for seamless boarding at Guimaras Port</p>
                </div>
                <?php if (!empty($message)) echo $message; ?>
                
                <!-- Existing QR Code Lookup -->
                <div class="lookup-section">
                    <div class="lookup-card">
                        <h3><i class="fas fa-search"></i> <span data-translate="register.lookup.title">Already Registered?</span></h3>
                        <p data-translate="register.lookup.description">Enter your email to securely access your QR code with email verification</p>
                        <form method="GET" action="otp_verification.php" class="lookup-form">
                            <div class="lookup-input-group">
                                <input type="email" name="email" placeholder="Enter your email address" data-translate-placeholder="register.lookup.email_placeholder" required>
                                <button type="submit" class="btn btn-secondary">
                                    <i class="fas fa-shield-alt"></i>
                                    <span data-translate="register.lookup.button">Secure Access</span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                
                <div class="divider">
                    <span>OR</span>
                </div>
                
                <form id="registration-form" class="registration-form" role="form" aria-labelledby="registration-form-title" method="POST" action="register.php">
                    <h2 id="registration-form-title" data-translate="register.form.title">Passenger Information</h2>
                    
                    <div class="form-group">
                        <label for="fullName">
                            <i class="fas fa-user"></i>
                            Full Name *
                        </label>
                        <input type="text" id="fullName" name="fullName" required aria-required="true" placeholder="Enter your full name">
                    </div>
                    
                    <div class="form-group">
                        <label for="contactNumber">
                            <i class="fas fa-phone"></i>
                            Contact Number *
                        </label>
                        <input type="tel" id="contactNumber" name="contactNumber" required aria-required="true" placeholder="Enter your contact number">
                    </div>
                    
                    <div class="form-group">
                        <label for="email">
                            <i class="fas fa-envelope"></i>
                            Email Address *
                        </label>
                        <input type="email" id="email" name="email" required aria-required="true" placeholder="Enter your email address">
                    </div>
                    
                    <div class="form-group">
                        <label for="address">
                            <i class="fas fa-map-marker-alt"></i>
                            Address *
                        </label>
                        <textarea id="address" name="address" rows="3" required aria-required="true" placeholder="Enter your complete address"></textarea>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="age">
                                <i class="fas fa-calendar"></i>
                                Age *
                            </label>
                            <input type="number" id="age" name="age" required aria-required="true" placeholder="Enter your age" min="1" max="120">
                        </div>
                        
                        <div class="form-group">
                            <label for="sex">
                                <i class="fas fa-venus-mars"></i>
                                Sex *
                            </label>
                            <select id="sex" name="sex" required aria-required="true">
                                <option value="">Select sex</option>
                                <option value="Male">Male</option>
                                <option value="Female">Female</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-qrcode"></i>
                            <span class="btn-text">Generate QR Code</span>
                            <div class="btn-spinner"></div>
                        </button>
                        <a href="index.html" class="btn btn-outline">
                            <i class="fas fa-arrow-left"></i>
                            Back to Home
                        </a>
                    </div>
                </form>
                
                <div class="form-footer">
                    <div class="security-badge">
                        <i class="fas fa-shield-alt"></i>
                        <span>256-bit SSL Encryption</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer" role="contentinfo">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <a href="index.html" class="footer-logo">
                        <img src="logo.png" alt="ManifestLink Logo" class="logo-img">
                        ManifestLink
                    </a>
                    <p>
                        Transforming maritime passenger management with cutting-edge QR technology. 
                        Making travel safer, faster, and more efficient for everyone.
                    </p>
                    <div class="social-links">
                        <a href="#" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" aria-label="Twitter"><i class="fab fa-twitter"></i></a>
                        <a href="#" aria-label="LinkedIn"><i class="fab fa-linkedin-in"></i></a>
                        <a href="#" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
                    </div>
                </div>
            </div>
            
            <div class="footer-bottom">
                <p>&copy; 2024 ManifestLink. All rights reserved.</p>
                <p>Designed and developed for Guimaras Port Authority</p>
            </div>
        </div>
    </footer>
    
    <!-- Language Translation Script -->
    <script>
        // Language data for register page
        const translations = {
            en: {
                // Registration page
                'register.title': 'Passenger Registration',
                'register.subtitle': 'Complete your registration to generate your QR code for seamless boarding at Guimaras Port',
                'register.lookup.title': 'Already Registered?',
                'register.lookup.description': 'Enter your email to securely access your QR code with email verification',
                'register.lookup.email_placeholder': 'Enter your email address',
                'register.lookup.button': 'Secure Access',
                'register.form.title': 'Passenger Information',
                'register.form.fullname': 'Full Name',
                'register.form.contact': 'Contact Number',
                'register.form.email': 'Email Address',
                'register.form.address': 'Address',
                'register.form.age': 'Age',
                'register.form.sex': 'Sex',
                'register.form.submit': 'Generate QR Code',
                'register.form.back': 'Back to Home',
                'register.security': '256-bit SSL Encryption'
            },
            fil: {
                // Registration page
                'register.title': 'Pagparehistro ng Pasahero',
                'register.subtitle': 'Kumpletuhin ang inyong pagparehistro upang makabuo ng QR code para sa walang kahirap-hirap na boarding sa Guimaras Port',
                'register.lookup.title': 'Nakaparehistro na ba kayo?',
                'register.lookup.description': 'Ilagay ang inyong email upang ligtas na ma-access ang inyong QR code gamit ang email verification',
                'register.lookup.email_placeholder': 'Ilagay ang inyong email address',
                'register.lookup.button': 'Ligtas na Access',
                'register.form.title': 'Impormasyon ng Pasahero',
                'register.form.fullname': 'Buong Pangalan',
                'register.form.contact': 'Numero ng Kontak',
                'register.form.email': 'Email Address',
                'register.form.address': 'Address',
                'register.form.age': 'Edad',
                'register.form.sex': 'Kasarian',
                'register.form.submit': 'Gumawa ng QR Code',
                'register.form.back': 'Bumalik sa Tahanan',
                'register.security': '256-bit SSL Encryption'
            }
        };

        // Language switching functionality
        let currentLanguage = localStorage.getItem('language') || 'en';

        function updateLanguage(lang) {
            currentLanguage = lang;
            localStorage.setItem('language', lang);
            
            // Update all elements with data-translate attribute
            document.querySelectorAll('[data-translate]').forEach(element => {
                const key = element.getAttribute('data-translate');
                if (translations[lang] && translations[lang][key]) {
                    element.textContent = translations[lang][key];
                }
            });
            
            // Update placeholder attributes
            document.querySelectorAll('[data-translate-placeholder]').forEach(element => {
                const key = element.getAttribute('data-translate-placeholder');
                if (translations[lang] && translations[lang][key]) {
                    element.placeholder = translations[lang][key];
                }
            });
            
            // Update language button
            document.getElementById('currentLang').textContent = lang.toUpperCase();
        }

        // Initialize language on page load
        document.addEventListener('DOMContentLoaded', function() {
            updateLanguage(currentLanguage);
            
            // Add click event to language switcher
            document.getElementById('langToggle').addEventListener('click', function() {
                const newLang = currentLanguage === 'en' ? 'fil' : 'en';
                updateLanguage(newLang);
            });
        });
    </script>
</body>
    <!-- Navigation JS for enhanced nav behavior -->
    <script src="js/navigation.js?v=<?php echo time(); ?>"></script>
</html>
