<?php
include '../connect.php';
// Fetch all QR codes with user info
$qrcodes = [];
$sql = "SELECT qr_codes.*, users.full_name, users.email FROM qr_codes JOIN users ON qr_codes.user_id = users.id ORDER BY qr_codes.id DESC";
$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $qrcodes[] = $row;
    }
}
?>
<section>
    <h2 style="color:#3b82f6; text-align:center; margin-bottom:24px;">QR Code Management</h2>
    <div style="overflow-x:auto;">
    <table style="width:100%; border-collapse:collapse; margin-top:18px;">
        <thead>
            <tr>
                <th>ID</th>
                <th>User</th>
                <th>Email</th>
                <th>QR Data</th>
                <th>QR Image</th>
                <th>Created At</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($qrcodes) > 0): ?>
                <?php foreach ($qrcodes as $qr): ?>
                    <tr>
                        <td><?php echo $qr['id']; ?></td>
                        <td><?php echo htmlspecialchars($qr['full_name']); ?></td>
                        <td><?php echo htmlspecialchars($qr['email']); ?></td>
                        <td style="max-width:200px; white-space:pre-wrap; word-break:break-all;"><?php echo htmlspecialchars($qr['qr_data']); ?></td>
                        <td>
                            <?php if (!empty($qr['qr_image_path']) && file_exists('../' . $qr['qr_image_path'])): ?>
                                <img src="../<?php echo $qr['qr_image_path']; ?>" alt="QR" style="width:60px; height:60px; object-fit:contain; border:1px solid #e5e7eb; border-radius:4px; background:#fff;">
                            <?php else: ?>
                                <span style="color:#888;">No image</span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo $qr['created_at']; ?></td>
                        <td>
                            <?php if (!empty($qr['qr_image_path']) && file_exists('../' . $qr['qr_image_path'])): ?>
                                <a href="../<?php echo $qr['qr_image_path']; ?>" class="download-btn" style="background:#3b82f6; color:#fff; padding:6px 12px; border-radius:4px; text-decoration:none;" download>Download</a>
                                <a href="../<?php echo $qr['qr_image_path']; ?>" class="download-btn" style="background:#3b82f6; color:#fff; padding:6px 12px; border-radius:4px; text-decoration:none;" target="_blank">View</a>
                            <?php else: ?>
                                <span style="color:#888;">N/A</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="7" style="text-align:center; color:#888;">No QR codes found.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
    </div>
</section> 