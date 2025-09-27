<?php
include '../connect.php';
// Fetch all users
$users = [];
$sql = "SELECT * FROM users ORDER BY id DESC";
$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $users[] = $row;
    }
}
?>
<section>
    <h2 style="color:#3b82f6; text-align:center; margin-bottom:24px;">User Management</h2>
    <div class="actions" style="margin-bottom:18px; text-align:right;">
        <a href="admin_user_add.php" class="add-btn" style="background:#3b82f6; color:#fff; padding:10px 18px; border-radius:4px; text-decoration:none; margin-left:8px;">+ Add User</a>
    </div>
    <div style="overflow-x:auto;">
    <table style="width:100%; border-collapse:collapse; margin-top:18px;">
        <thead>
            <tr>
                <th>ID</th>
                <th>Full Name</th>
                <th>Contact Number</th>
                <th>Email</th>
                <th>Address</th>
                <th>Age</th>
                <th>Sex</th>
                <th>Created At</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($users) > 0): ?>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?php echo $user['id']; ?></td>
                        <td><?php echo htmlspecialchars($user['full_name']); ?></td>
                        <td><?php echo htmlspecialchars($user['contact_number']); ?></td>
                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                        <td><?php echo htmlspecialchars($user['address']); ?></td>
                        <td><?php echo $user['age']; ?></td>
                        <td><?php echo htmlspecialchars($user['sex']); ?></td>
                        <td><?php echo $user['created_at']; ?></td>
                        <td>
                            <a href="admin_user_edit.php?id=<?php echo $user['id']; ?>" class="edit-btn" style="background:#f59e42; color:#fff; padding:6px 12px; border-radius:4px; text-decoration:none;">Edit</a>
                            <a href="admin_user_delete.php?id=<?php echo $user['id']; ?>" class="delete-btn" style="background:#ef4444; color:#fff; padding:6px 12px; border-radius:4px; text-decoration:none;" onclick="return confirm('Are you sure you want to delete this user?');">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="9" style="text-align:center; color:#888;">No users found.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
    </div>
</section> 