<?php
session_start();
include('../includes/header.php');
?>

<div class="container mt-4">
    <h2>Add New User</h2>
    <form action="store.php" method="POST">
        <div class="mb-3">
            <label>Username</label>
            <input type="text" name="username" class="form-control" required>
        </div>
        <div class="mb-3">
            <label>Email</label>
            <input type="email" name="email" class="form-control" required>
        </div>
        <div class="mb-3">
            <label>Password</label>
            <input type="password" name="password" class="form-control" required>
        </div>
        <div class="mb-3">
            <label>Role</label>
            <select name="role" class="form-select">
                <option value="admin">Admin</option>
                <option value="customer" selected>Customer</option>
            </select>
        </div>
        <button type="submit" class="btn btn-primary" name="submit">Add User</button>
        <a href="index.php" class="btn btn-secondary">Cancel</a>
    </form>
</div>

<?php include('../includes/footer.php'); ?>
