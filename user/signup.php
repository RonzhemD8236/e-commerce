<?php
session_start();
include('../includes/db_connect.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);

    // Validate passwords
    if ($password !== $confirm_password) {
        $error = "Passwords do not match!";
    } else {
        // Hash password securely
        $hashed = password_hash($password, PASSWORD_DEFAULT);

        // Check if email already exists
        $check = $conn->prepare("SELECT email FROM users WHERE email = ?");
        $check->bind_param("s", $email);
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {
            $error = "Email already registered!";
        } else {
            // Insert minimal info first (names, zip empty)
            $stmt = $conn->prepare("INSERT INTO users (first_name, last_name, email, password, zip_code, role) VALUES ('', '', ?, ?, '', 'customer')");
            $stmt->bind_param("ss", $email, $hashed);

            if ($stmt->execute()) {
                // Get the new user's ID
                $new_user_id = $stmt->insert_id;

                // Auto-login new user
                $_SESSION['user_id'] = $new_user_id;
                $_SESSION['email'] = $email;
                $_SESSION['role'] = 'customer';

                // Redirect to profile setup page
                header("Location: profile.php");
                exit;
            } else {
                $error = "Error creating account. Please try again.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Signup | Lensify</title>
    <link rel="stylesheet" href="../assets/bootstrap.min.css">
</head>
<body class="bg-light">
<div class="container mt-5 col-md-5">
    <h2 class="text-center mb-4">Create an Account</h2>
    <?php if (!empty($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>
    <form method="POST">
        <div class="form-group mb-3">
            <label>Email Address</label>
            <input type="email" name="email" class="form-control" placeholder="Enter your email" required>
        </div>
        <div class="form-group mb-3">
            <label>Password</label>
            <input type="password" name="password" class="form-control" placeholder="Enter password" required>
        </div>
        <div class="form-group mb-3">
            <label>Confirm Password</label>
            <input type="password" name="confirm_password" class="form-control" placeholder="Re-enter password" required>
        </div>
        <button type="submit" class="btn btn-primary w-100">Sign Up</button>
        <p class="text-center mt-3">Already have an account? <a href="login.php">Login</a></p>
    </form>
</div>
</body>
</html>
