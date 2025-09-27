<?php
include 'connect.php';

// Get user_id from GET
$user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;

// Fetch user info and QR code info
$user = null;
$qr = null;
if ($user_id > 0) {
    $stmt = $conn->prepare("SELECT u.full_name, u.contact_number, u.email, u.address, u.age, u.sex, q.qr_image_path FROM users u JOIN qr_codes q ON u.id = q.user_id WHERE u.id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->bind_result($fullName, $contactNumber, $email, $address, $age, $sex, $qr_filename);
    if ($stmt->fetch()) {
        $user = [
            'full_name' => $fullName,
            'contact_number' => $contactNumber,
            'email' => $email,
            'address' => $address,
            'age' => $age,
            'sex' => $sex
        ];
        $qr = $qr_filename;
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your QR Code - ManifestLink</title>
    <meta name="description" content="Your generated QR code for ManifestLink registration.">
    <link rel="icon" type="image/png" href="logo.png">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/generatedqr.css">
    
    <!-- Custom JavaScript -->
    <script src="script.js?v=<?php echo time(); ?>"></script>
</head>
<body>
    <div class="animated-bg">
        <div class="bg-circle circle-1"></div>
        <div class="bg-circle circle-2"></div>
        <div class="bg-circle circle-3"></div>
        <div class="bg-circle circle-4"></div>
        <div class="bg-circle circle-5"></div>
    </div>
    
    <!-- Professional QR Display Section -->
    <section class="qr-display-section">
        <div class="container">
            <div class="qr-display-card">
                <div class="qr-display-header">
                    <div class="header-icon">
                        <i class="fas fa-qrcode"></i>
                    </div>
                    <h1>Your QR Code</h1>
                    <p>Show this QR code at the port for verification.</p>
                </div>
                
                <?php if ($user && $qr): ?>
                    <?php if (isset($_GET['verified']) && $_GET['verified'] == '1'): ?>
                        <div class="alert success">
                            <i class="fas fa-shield-check"></i>
                            Email verification successful! Your QR code is ready.
                        </div>
                    <?php else: ?>
                        <div class="alert success">
                            <i class="fas fa-check-circle"></i>
                            Registration Successful! Your QR code is ready.
                        </div>
                    <?php endif; ?>
                    
                    <!-- Success Animation -->
                    <div class="success-animation">
                        <div class="success-checkmark">
                            <i class="fas fa-check"></i>
                        </div>
                    </div>
                    
                    <div class="qr-display-content">
                        <div class="qr-image-container">
                            <div class="qr-preview">
                                <img id="qrImage" src="<?php echo htmlspecialchars($qr); ?>" alt="QR Code" />
                            </div>
                        </div>
                        
                        <div class="download-section">
                            <h3>Download Options</h3>
                            <div class="download-buttons">
                                <a href="download_qr_png.php?user_id=<?php echo urlencode($user_id); ?>" class="btn btn-primary download-btn" data-format="png">
                                    <i class="fas fa-download"></i>
                                    <span class="btn-text">Download PNG</span>
                                    <div class="btn-spinner"></div>
                                </a>
                                <a href="download_qr_jpg.php?user_id=<?php echo urlencode($user_id); ?>" class="btn btn-outline download-btn" data-format="jpg">
                                    <i class="fas fa-download"></i>
                                    <span class="btn-text">Download JPG</span>
                                    <div class="btn-spinner"></div>
                                </a>
                            </div>
                            
                            <div class="download-info">
                                <i class="fas fa-info-circle"></i> 
                                PNG format preserves transparency, JPG format is more compatible with most devices.
                            </div>
                        </div>
                    </div>
                    
                    <div class="user-info-section">
                        <h3>Passenger Information</h3>
                        <div class="user-info-grid">
                            <div class="info-item">
                                <i class="fas fa-user-circle"></i>
                                <strong>Full Name:</strong>
                                <span><?php echo htmlspecialchars($user['full_name']); ?></span>
                            </div>
                            <div class="info-item">
                                <i class="fas fa-phone"></i>
                                <strong>Contact Number:</strong>
                                <span><?php echo htmlspecialchars($user['contact_number']); ?></span>
                            </div>
                            <div class="info-item">
                                <i class="fas fa-envelope"></i>
                                <strong>Email:</strong>
                                <span><?php echo htmlspecialchars($user['email']); ?></span>
                            </div>
                            <div class="info-item">
                                <i class="fas fa-map-marker-alt"></i>
                                <strong>Address:</strong>
                                <span><?php echo htmlspecialchars($user['address']); ?></span>
                            </div>
                            <div class="info-item">
                                <i class="fas fa-birthday-cake"></i>
                                <strong>Age:</strong>
                                <span><?php echo htmlspecialchars($user['age']); ?> years old</span>
                            </div>
                            <div class="info-item">
                                <i class="fas fa-venus-mars"></i>
                                <strong>Sex:</strong>
                                <span><?php echo htmlspecialchars($user['sex']); ?></span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="action-buttons">
                        <a href="index.html" class="btn btn-primary">
                            <i class="fas fa-arrow-left"></i> Back to Home
                        </a>
                        <a href="register.php" class="btn btn-outline">
                            <i class="fas fa-user-plus"></i> Register Another
                        </a>
                    </div>
                <?php else: ?>
                    <div class="alert error">
                        <i class="fas fa-exclamation-triangle"></i>
                        QR code not found. Please register first.
                    </div>
                    
                    <div class="action-buttons">
                        <a href="register.php" class="btn btn-primary">
                            <i class="fas fa-user-plus"></i> Register Now
                        </a>
                        <a href="index.html" class="btn btn-outline">
                            <i class="fas fa-arrow-left"></i> Back to Home
                        </a>
                    </div>
                <?php endif; ?>
                
                <div class="qr-display-footer">
                    <div class="security-badge">
                        <i class="fas fa-shield-alt"></i>
                        <span>256-bit SSL Encryption</span>
                    </div>
                </div>
            </div>
        </div>
    </section>
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
</body>
</html>
