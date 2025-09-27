<?php
include '../connect.php';
// Fetch all OTP verifications with user info
$otps = [];
$sql = "SELECT otp_verification.*, users.full_name FROM otp_verification JOIN users ON otp_verification.user_id = users.id ORDER BY otp_verification.id DESC";
$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $otps[] = $row;
    }
}
?>
<section>
    <h2 style="color:#3b82f6; text-align:center; margin-bottom:24px;">OTP Verification Management</h2>
    <div style="overflow-x:auto;">
    <table style="width:100%; border-collapse:collapse; margin-top:18px;">
        <thead>
            <tr>
                <th>ID</th>
                <th>User</th>
                <th>Email</th>
                <th>OTP</th>
                <th>Expires At</th>
                <th>Created At</th>
                <th>Updated At</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($otps) > 0): ?>
                <?php foreach ($otps as $otp): ?>
                    <tr>
                        <td><?php echo $otp['id']; ?></td>
                        <td><?php echo htmlspecialchars($otp['full_name']); ?></td>
                        <td><?php echo htmlspecialchars($otp['email']); ?></td>
                        <td><?php echo htmlspecialchars($otp['otp']); ?></td>
                        <td><?php echo $otp['expires_at']; ?></td>
                        <td><?php echo $otp['created_at']; ?></td>
                        <td><?php echo $otp['updated_at']; ?></td>
                        <td>
                            <a href="#" class="resend-btn" style="background:#3b82f6; color:#fff; padding:6px 12px; border-radius:4px; text-decoration:none;">Resend</a>
                            <a href="#" class="invalidate-btn delete-otp-btn" data-id="<?php echo $otp['id']; ?>" style="background:#ef4444; color:#fff; padding:6px 12px; border-radius:4px; text-decoration:none;">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="8" style="text-align:center; color:#888;">No OTP records found.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
    </div>
</section> 