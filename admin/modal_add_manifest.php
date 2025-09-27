<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: admin_login.php');
    exit();
}

include '../connect.php';

// Get all users for dropdown
$users_query = "SELECT id, full_name, contact_number FROM users ORDER BY full_name";
$users_result = $conn->query($users_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Manifest Entry</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 20px; background: #f5f5f5; }
        .modal-container { background: white; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.15); overflow: hidden; max-width: 600px; margin: 0 auto; }
        .modal-header { background: #3b82f6; color: white; padding: 20px; }
        .modal-header h2 { margin: 0; font-size: 20px; }
        .modal-body { padding: 20px; }
        .form-group { margin-bottom: 20px; }
        .form-label { display: block; margin-bottom: 8px; font-weight: 500; color: #374151; }
        .form-control { width: 100%; padding: 10px 12px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 14px; box-sizing: border-box; }
        .form-control:focus { outline: none; border-color: #3b82f6; box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1); }
        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
        .form-textarea { min-height: 80px; resize: vertical; }
        .modal-footer { padding: 20px; background: #f9fafb; border-top: 1px solid #e5e7eb; text-align: right; }
        .btn { padding: 10px 20px; border: none; border-radius: 6px; cursor: pointer; font-size: 14px; margin-left: 10px; }
        .btn-secondary { background: #6b7280; color: white; }
        .btn-primary { background: #3b82f6; color: white; }
        .btn-primary:hover { background: #2563eb; }
        .error { color: #dc2626; font-size: 14px; margin-top: 4px; }
        .success { color: #059669; font-size: 14px; margin-top: 4px; }
        @media (max-width: 768px) { .form-row { grid-template-columns: 1fr; } }
    </style>
</head>
<body>
    <div class="modal-container">
        <div class="modal-header">
            <h2>Add New Manifest Entry</h2>
        </div>
        
        <form id="addManifestForm">
            <div class="modal-body">
                <!-- Passenger Selection -->
                <div class="form-group">
                    <label class="form-label">Select Passenger *</label>
                    <select name="user_id" class="form-control" required>
                        <option value="">Choose a passenger...</option>
                        <?php while ($user = $users_result->fetch_assoc()) { ?>
                            <option value="<?php echo $user['id']; ?>">
                                <?php echo htmlspecialchars($user['full_name']); ?> - <?php echo htmlspecialchars($user['contact_number']); ?>
                            </option>
                        <?php } ?>
                    </select>
                </div>

                <!-- Scan Information -->
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Scan Location</label>
                        <input type="text" name="scan_location" class="form-control" value="Port Terminal" placeholder="e.g., Port Terminal 1">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Scanned By</label>
                        <input type="text" name="scanned_by" class="form-control" value="<?php echo htmlspecialchars($_SESSION['admin_username'] ?? 'Port Staff'); ?>" placeholder="Staff member name">
                    </div>
                </div>

                <!-- Boarding Status -->
                <div class="form-group">
                    <label class="form-label">Boarding Status *</label>
                    <select name="boarding_status" class="form-control" required>
                        <option value="scanned">Scanned</option>
                        <option value="boarded">Boarded</option>
                        <option value="departed">Departed</option>
                    </select>
                </div>

                <!-- Vessel Information -->
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Vessel Name</label>
                        <input type="text" name="vessel_name" class="form-control" placeholder="e.g., MV Guimaras Express">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Vessel Number</label>
                        <input type="text" name="vessel_number" class="form-control" placeholder="e.g., GE-001">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Destination</label>
                        <input type="text" name="destination" class="form-control" placeholder="e.g., Iloilo City">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Departure Time</label>
                        <input type="datetime-local" name="departure_time" class="form-control">
                    </div>
                </div>

                <!-- Notes -->
                <div class="form-group">
                    <label class="form-label">Notes</label>
                    <textarea name="notes" class="form-control form-textarea" placeholder="Additional notes about this boarding record..."></textarea>
                </div>

                <div id="formMessage"></div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="window.close()">Cancel</button>
                <button type="submit" class="btn btn-primary">Add Entry</button>
            </div>
        </form>
    </div>

    <script>
        document.getElementById('addManifestForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const data = Object.fromEntries(formData);
            
            // Convert datetime-local to proper format
            if (data.departure_time) {
                data.departure_time = new Date(data.departure_time).toISOString().slice(0, 19).replace('T', ' ');
            }
            
            fetch('add_manifest_entry.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            })
            .then(response => response.json())
            .then(result => {
                const messageDiv = document.getElementById('formMessage');
                if (result.success) {
                    messageDiv.innerHTML = '<div class="success">Manifest entry added successfully!</div>';
                    setTimeout(() => {
                        window.opener.location.reload();
                        window.close();
                    }, 1500);
                } else {
                    messageDiv.innerHTML = '<div class="error">Error: ' + result.message + '</div>';
                }
            })
            .catch(error => {
                document.getElementById('formMessage').innerHTML = '<div class="error">Network error occurred</div>';
            });
        });
    </script>
</body>
</html> 