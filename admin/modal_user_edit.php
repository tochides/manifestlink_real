<?php
include '../connect.php';
$message = '';
$user = null;
$user_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($user_id > 0) {
    $stmt = $conn->prepare('SELECT * FROM users WHERE id = ?');
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result && $result->num_rows === 1) {
        $user = $result->fetch_assoc();
    } else {
        $message = '<div class="alert error">User not found.</div>';
    }
    $stmt->close();
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $user) {
    $fullName = trim($_POST['full_name'] ?? '');
    $contactNumber = trim($_POST['contact_number'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $age = isset($_POST['age']) ? intval($_POST['age']) : null;
    $sex = trim($_POST['sex'] ?? '');
    if ($fullName && $contactNumber && $email && $address && $age && $sex) {
        $stmt = $conn->prepare('SELECT id FROM users WHERE email = ? AND id != ?');
        $stmt->bind_param('si', $email, $user_id);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $message = '<div class="alert error">Another user with this email already exists.</div>';
        } else {
            $stmt->close();
            $stmt = $conn->prepare('UPDATE users SET full_name=?, contact_number=?, email=?, address=?, age=?, sex=? WHERE id=?');
            $stmt->bind_param('ssssisi', $fullName, $contactNumber, $email, $address, $age, $sex, $user_id);
            if ($stmt->execute()) {
                echo 'success';
                exit;
            } else {
                $message = '<div class="alert error">Failed to update user. Please try again.</div>';
            }
        }
        $stmt->close();
    } else {
        $message = '<div class="alert error">All fields are required.</div>';
    }
}
?>
<?php if ($message) echo $message; ?>
<?php if ($user): ?>
<form method="post" autocomplete="off" action="modal_user_edit.php?id=<?php echo $user_id; ?>">
    <div class="form-group" style="margin-bottom:18px;">
        <label for="full_name" style="display:block; margin-bottom:6px; color:#374151;">Full Name</label>
        <input type="text" id="full_name" name="full_name" value="<?php echo htmlspecialchars($user['full_name']); ?>" required style="width:100%; padding:10px; border:1px solid #d1d5db; border-radius:4px;">
    </div>
    <div class="form-group" style="margin-bottom:18px;">
        <label for="contact_number" style="display:block; margin-bottom:6px; color:#374151;">Contact Number</label>
        <input type="text" id="contact_number" name="contact_number" value="<?php echo htmlspecialchars($user['contact_number']); ?>" required style="width:100%; padding:10px; border:1px solid #d1d5db; border-radius:4px;">
    </div>
    <div class="form-group" style="margin-bottom:18px;">
        <label for="email" style="display:block; margin-bottom:6px; color:#374151;">Email</label>
        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required style="width:100%; padding:10px; border:1px solid #d1d5db; border-radius:4px;">
    </div>
    <div class="form-group" style="margin-bottom:18px;">
        <label for="address" style="display:block; margin-bottom:6px; color:#374151;">Address</label>
        <textarea id="address" name="address" required style="width:100%; padding:10px; border:1px solid #d1d5db; border-radius:4px;"><?php echo htmlspecialchars($user['address']); ?></textarea>
    </div>
    <div class="form-group" style="margin-bottom:18px;">
        <label for="age" style="display:block; margin-bottom:6px; color:#374151;">Age</label>
        <input type="number" id="age" name="age" min="1" value="<?php echo $user['age']; ?>" required style="width:100%; padding:10px; border:1px solid #d1d5db; border-radius:4px;">
    </div>
    <div class="form-group" style="margin-bottom:18px;">
        <label for="sex" style="display:block; margin-bottom:6px; color:#374151;">Sex</label>
        <select id="sex" name="sex" required style="width:100%; padding:10px; border:1px solid #d1d5db; border-radius:4px;">
            <option value="">Select</option>
            <option value="Male" <?php if ($user['sex'] === 'Male') echo 'selected'; ?>>Male</option>
            <option value="Female" <?php if ($user['sex'] === 'Female') echo 'selected'; ?>>Female</option>
        </select>
    </div>
    <button type="submit" class="btn" style="width:100%; background:#3b82f6; color:#fff; border:none; padding:12px; border-radius:4px; font-size:16px; cursor:pointer;">Update User</button>
</form>
<?php endif; ?> 