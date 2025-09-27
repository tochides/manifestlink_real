<?php
include '../connect.php';
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullName = trim($_POST['full_name'] ?? '');
    $contactNumber = trim($_POST['contact_number'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $age = isset($_POST['age']) ? intval($_POST['age']) : null;
    $sex = trim($_POST['sex'] ?? '');
    if ($fullName && $contactNumber && $email && $address && $age && $sex) {
        $stmt = $conn->prepare('SELECT id FROM users WHERE email = ?');
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $message = '<div class="alert error">A user with this email already exists.</div>';
        } else {
            $stmt->close();
            $stmt = $conn->prepare('INSERT INTO users (full_name, contact_number, email, address, age, sex) VALUES (?, ?, ?, ?, ?, ?)');
            $stmt->bind_param('ssssis', $fullName, $contactNumber, $email, $address, $age, $sex);
            if ($stmt->execute()) {
                echo 'success';
                exit;
            } else {
                $message = '<div class="alert error">Failed to add user. Please try again.</div>';
            }
        }
        $stmt->close();
    } else {
        $message = '<div class="alert error">All fields are required.</div>';
    }
}
?>
<?php if ($message) echo $message; ?>
<form method="post" autocomplete="off" action="modal_user_add.php">
    <div class="form-group" style="margin-bottom:18px;">
        <label for="full_name" style="display:block; margin-bottom:6px; color:#374151;">Full Name</label>
        <input type="text" id="full_name" name="full_name" required style="width:100%; padding:10px; border:1px solid #d1d5db; border-radius:4px;">
    </div>
    <div class="form-group" style="margin-bottom:18px;">
        <label for="contact_number" style="display:block; margin-bottom:6px; color:#374151;">Contact Number</label>
        <input type="text" id="contact_number" name="contact_number" required style="width:100%; padding:10px; border:1px solid #d1d5db; border-radius:4px;">
    </div>
    <div class="form-group" style="margin-bottom:18px;">
        <label for="email" style="display:block; margin-bottom:6px; color:#374151;">Email</label>
        <input type="email" id="email" name="email" required style="width:100%; padding:10px; border:1px solid #d1d5db; border-radius:4px;">
    </div>
    <div class="form-group" style="margin-bottom:18px;">
        <label for="address" style="display:block; margin-bottom:6px; color:#374151;">Address</label>
        <textarea id="address" name="address" required style="width:100%; padding:10px; border:1px solid #d1d5db; border-radius:4px;"></textarea>
    </div>
    <div class="form-group" style="margin-bottom:18px;">
        <label for="age" style="display:block; margin-bottom:6px; color:#374151;">Age</label>
        <input type="number" id="age" name="age" min="1" required style="width:100%; padding:10px; border:1px solid #d1d5db; border-radius:4px;">
    </div>
    <div class="form-group" style="margin-bottom:18px;">
        <label for="sex" style="display:block; margin-bottom:6px; color:#374151;">Sex</label>
        <select id="sex" name="sex" required style="width:100%; padding:10px; border:1px solid #d1d5db; border-radius:4px;">
            <option value="">Select</option>
            <option value="Male">Male</option>
            <option value="Female">Female</option>
        </select>
    </div>
    <button type="submit" class="btn" style="width:100%; background:#3b82f6; color:#fff; border:none; padding:12px; border-radius:4px; font-size:16px; cursor:pointer;">Add User</button>
</form> 