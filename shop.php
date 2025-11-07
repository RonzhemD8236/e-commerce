<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Dashboard | Lensify</title>
    <link rel="stylesheet" href="../assets/bootstrap.min.css">
</head>
<body>
<div class="container mt-5">
    <h3>Welcome, <?php echo htmlspecialchars($_SESSION['full_name']); ?>!</h3>
    <p>You are logged in as <strong><?php echo $_SESSION['role']; ?></strong>.</p>
    <a href="logout.php" class="btn btn-danger mt-3">Logout</a>
</div>
</body>
</html>
