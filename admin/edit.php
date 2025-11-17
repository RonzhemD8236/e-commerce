<?php
session_start();
include('../includes/config.php');
include('../includes/header.php');

$id = $_GET['id'];

// Fetch user info
$sqlUser = "SELECT * FROM users WHERE id=?";
$stmtUser = $conn->prepare($sqlUser);
$stmtUser->bind_param("i", $id);
$stmtUser->execute();
$resultUser = $stmtUser->get_result();
$user = $resultUser->fetch_assoc();

// If user is a customer, fetch name from customer profile
$profileName = '';
if ($user['role'] === 'customer') {
    $sqlProfile = "SELECT fname, lname FROM customer WHERE user_id=?";
    $stmtProfile = $conn->prepare($sqlProfile);
    $stmtProfile->bind_param("i", $id);
    $stmtProfile->execute();
    $resultProfile = $stmtProfile->get_result();
    if ($resultProfile->num_rows > 0) {
        $profile = $resultProfile->fetch_assoc();
        $profileName = trim($profile['fname'] . ' ' . $profile['lname']);
    }
}

// Use profile name if available, otherwise fallback to users.name
$nameValue = !empty($profileName) ? $profileName : $user['name'];
?>

<div class="container mt-4">
    <h2>Edit User</h2>
    <form action="update.php" method="POST">
        <input type="hidden" name="id" value="<?= $user['id'] ?>">
        <div class="mb-3">
            <label>Name</label>
            <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($nameValue) ?>" required>
        </div>
        <div class="mb-3">
            <label>Email</label>
            <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" required>
        </div>
        <div class="mb-3">
            <label>Password (leave blank to keep current)</label>
            <input type="password" name="password" class="form-control">
        </div>
        <div class="mb-3">
            <label>Role</label>
            <select name="role" class="form-select">
                <option value="admin" <?= $user['role']=='admin'?'selected':'' ?>>Admin</option>
                <option value="customer" <?= $user['role']=='customer'?'selected':'' ?>>Customer</option>
            </select>
        </div>
        <button type="submit" class="btn btn-primary" name="submit">Update User</button>
        <a href="index.php" class="btn btn-secondary">Cancel</a>
    </form>
</div>

<?php include('../includes/footer.php'); ?>
