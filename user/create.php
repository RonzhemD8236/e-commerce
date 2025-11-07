<?php
session_start();
include('../includes/header.php');
include('../includes/config.php');

if ($_SESSION['role'] != 'admin') {
    die('Access denied.');
}
?>

<div class="container mt-4">
    <h2>Add New User</h2>
    <form method="POST" action="storee.php">
        <div class="mb-3">
            <label>Name <span class="text-danger">*</span></label>
            <input type="text" name="name" class="form-control" required>
        </div>
        <div class="mb-3">
            <label>Email <span class="text-danger">*</span></label>
            <input type="email" name="email" class="form-control" required>
        </div>
        <div class="mb-3">
            <label>Password <span class="text-danger">*</span></label>
            <input type="password" name="password" class="form-control" required>
        </div>
        <div class="mb-3">
            <label>Role <span class="text-danger">*</span></label>
            <select name="role" class="form-control" required>
                <option value="customer">Customer</option>
                <option value="admin">Admin</option>
            </select>
        </div>
        <button type="submit" class="btn btn-primary">Save User</button>
        <a href="../index.php" class="btn btn-secondary">Cancel</a>
    </form>
</div>

<?php include('../includes/footer.php'); ?>
