<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: admin_login.php');
    exit();
}

include '../connect.php';

$manifest_id = isset($_GET['manifest_id']) ? intval($_GET['manifest_id']) : 0;

if (!$manifest_id) {
    echo '<div class="error">Invalid manifest ID</div>';
    exit();
}

// Get manifest details with user information
$query = "SELECT m.*, u.full_name, u.contact_number, u.email, u.address, u.age, u.sex, u.created_at as user_registered
          FROM manifest m 
          LEFT JOIN users u ON m.user_id = u.id 
          WHERE m.id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $manifest_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo '<div class="error">Manifest record not found</div>';
    exit();
}

$manifest = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manifest Details</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 20px; background: #f5f5f5; }
        .modal-container { background: white; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.15); overflow: hidden; }
        .modal-header { background: #3b82f6; color: white; padding: 20px; }
        .modal-header h2 { margin: 0; font-size: 20px; }
        .modal-body { padding: 20px; }
        .detail-section { margin-bottom: 24px; }
        .detail-section h3 { color: #374151; margin-bottom: 12px; font-size: 16px; border-bottom: 2px solid #e5e7eb; padding-bottom: 8px; }
        .detail-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
        .detail-item { display: flex; flex-direction: column; }
        .detail-label { font-size: 12px; color: #6b7280; margin-bottom: 4px; font-weight: 500; }
        .detail-value { font-size: 14px; color: #1f2937; font-weight: 500; }
        .status-badge { display: inline-block; padding: 4px 12px; border-radius: 12px; font-size: 12px; font-weight: 500; }
        .status-scanned { background: #dbeafe; color: #1e40af; }
        .status-boarded { background: #fef3c7; color: #92400e; }
        .status-departed { background: #dcfce7; color: #166534; }
        .modal-footer { padding: 20px; background: #f9fafb; border-top: 1px solid #e5e7eb; text-align: right; }
        .btn { padding: 8px 16px; border: none; border-radius: 6px; cursor: pointer; font-size: 14px; }
        .btn-secondary { background: #6b7280; color: white; }
        .btn-primary { background: #3b82f6; color: white; }
        .error { color: #dc2626; padding: 20px; text-align: center; }
        @media (max-width: 768px) { .detail-grid { grid-template-columns: 1fr; } }
    </style>
</head>
<body>
    <div class="modal-container">
        <div class="modal-header">
            <h2>Manifest Entry Details</h2>
        </div>
        
        <div class="modal-body">
            <!-- Passenger Information -->
            <div class="detail-section">
                <h3>Passenger Information</h3>
                <div class="detail-grid">
                    <div class="detail-item">
                        <div class="detail-label">Full Name</div>
                        <div class="detail-value"><?php echo htmlspecialchars($manifest['full_name']); ?></div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Contact Number</div>
                        <div class="detail-value"><?php echo htmlspecialchars($manifest['contact_number']); ?></div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Email Address</div>
                        <div class="detail-value"><?php echo htmlspecialchars($manifest['email']); ?></div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Age & Sex</div>
                        <div class="detail-value"><?php echo $manifest['age']; ?> years old, <?php echo $manifest['sex']; ?></div>
                    </div>
                    <div class="detail-item" style="grid-column: 1 / -1;">
                        <div class="detail-label">Address</div>
                        <div class="detail-value"><?php echo htmlspecialchars($manifest['address']); ?></div>
                    </div>
                </div>
            </div>

            <!-- Boarding Information -->
            <div class="detail-section">
                <h3>Boarding Information</h3>
                <div class="detail-grid">
                    <div class="detail-item">
                        <div class="detail-label">Scan Time</div>
                        <div class="detail-value"><?php echo date('M d, Y H:i:s', strtotime($manifest['scan_time'])); ?></div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Scan Location</div>
                        <div class="detail-value"><?php echo htmlspecialchars($manifest['scan_location']); ?></div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Scanned By</div>
                        <div class="detail-value"><?php echo htmlspecialchars($manifest['scanned_by']); ?></div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Boarding Status</div>
                        <div class="detail-value">
                            <span class="status-badge status-<?php echo $manifest['boarding_status']; ?>">
                                <?php echo ucfirst($manifest['boarding_status']); ?>
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Vessel Information -->
            <div class="detail-section">
                <h3>Vessel Information</h3>
                <div class="detail-grid">
                    <div class="detail-item">
                        <div class="detail-label">Vessel Name</div>
                        <div class="detail-value"><?php echo htmlspecialchars($manifest['vessel_name'] ?? 'Not specified'); ?></div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Vessel Number</div>
                        <div class="detail-value"><?php echo htmlspecialchars($manifest['vessel_number'] ?? 'Not specified'); ?></div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Destination</div>
                        <div class="detail-value"><?php echo htmlspecialchars($manifest['destination'] ?? 'Not specified'); ?></div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Departure Time</div>
                        <div class="detail-value"><?php echo $manifest['departure_time'] ? date('M d, Y H:i', strtotime($manifest['departure_time'])) : 'Not specified'; ?></div>
                    </div>
                </div>
            </div>

            <!-- Additional Information -->
            <?php if ($manifest['notes']) { ?>
            <div class="detail-section">
                <h3>Notes</h3>
                <div class="detail-value" style="background: #f9fafb; padding: 12px; border-radius: 6px; border-left: 4px solid #3b82f6;">
                    <?php echo nl2br(htmlspecialchars($manifest['notes'])); ?>
                </div>
            </div>
            <?php } ?>

            <!-- System Information -->
            <div class="detail-section">
                <h3>System Information</h3>
                <div class="detail-grid">
                    <div class="detail-item">
                        <div class="detail-label">Manifest ID</div>
                        <div class="detail-value">#<?php echo $manifest['id']; ?></div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">User ID</div>
                        <div class="detail-value">#<?php echo $manifest['user_id']; ?></div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Record Created</div>
                        <div class="detail-value"><?php echo date('M d, Y H:i:s', strtotime($manifest['created_at'])); ?></div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">User Registered</div>
                        <div class="detail-value"><?php echo date('M d, Y H:i:s', strtotime($manifest['user_registered'])); ?></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal-footer">
            <button class="btn btn-secondary" onclick="window.close()">Close</button>
            <button class="btn btn-primary" onclick="printDetails()">Print Details</button>
        </div>
    </div>

    <script>
        function printDetails() {
            window.print();
        }
    </script>
</body>
</html> 