<?php
session_start();
include('../includes/db_connect.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    // ✅ Updated: Use first_name and last_name instead of full_name
    $stmt = $conn->prepare("SELECT user_id, first_name, last_name, password, role, status FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $row = $result->fetch_assoc();

        if ($row['status'] == 'deactivated') {
            $error = "Your account has been deactivated. Contact admin.";
        } elseif (password_verify($password, $row['password'])) {
            // ✅ Combine first + last name for session
            $_SESSION['user_id'] = $row['user_id'];
            $_SESSION['full_name'] = trim($row['first_name'] . ' ' . $row['last_name']);
            $_SESSION['role'] = $row['role'];

            // Redirect after successful login
            header("Location: ../shop.php");
            exit;
        } else {
            $error = "Invalid password!";
        }
    } else {
        $error = "No account found with that email!";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login | Lensify</title>
    <link rel="stylesheet" href="../assets/bootstrap.min.css">
</head>
<body class="bg-light">
<div class="container mt-5 col-md-5">
    <h2 class="text-center mb-4">Login to Your Account</h2>

    <?php if (!empty($_SESSION['success'])) { echo "<div class='alert alert-success'>".$_SESSION['success']."</div>"; unset($_SESSION['success']); } ?>
    <?php if (!empty($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>

    <form method="POST">
        <div class="form-group mb-3">
            <label>Email</label>
            <input type="email" name="email" class="form-control" required>
        </div>
        <div class="form-group mb-3">
            <label>Password</label>
            <input type="password" name="password" class="form-control" required>
        </div>
        <button class="btn btn-success w-100">Login</button>
        <p class="text-center mt-3">Don’t have an account? <a href="signup.php">Sign Up</a></p>
    </form>
</div>
</body>
</html>
