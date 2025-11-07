<?php
session_start();
include("../includes/header.php");
include("../includes/config.php");

if (isset($_POST['submit'])) {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    // Query using user_id
    $sql = "SELECT user_id, email, password, role FROM users WHERE email=? LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($user_id, $user_email, $hashed_password, $role);

    if ($stmt->num_rows === 1) {
        $stmt->fetch();

        if (password_verify($password, $hashed_password)) {
            $_SESSION['email'] = $user_email;
            $_SESSION['user_id'] = $user_id;
            $_SESSION['role'] = $role;

            header("Location: ../index.php"); // Redirect to homepage after login
            exit();
        } else {
            $_SESSION['message'] = 'Wrong email or password';
        }
    } else {
        $_SESSION['message'] = 'Wrong email or password';
    }
}
?>

<div class="row col-md-8 mx-auto">
    <?php include("../includes/alert.php"); ?>
    <form action="" method="POST">
        <div class="mb-3">
            <label>Email address</label>
            <input type="email" name="email" class="form-control" required>
        </div>
        <div class="mb-3">
            <label>Password</label>
            <input type="password" name="password" class="form-control" required>
        </div>
        <button type="submit" name="submit" class="btn btn-primary">Sign in</button>
        <p class="mt-3">Not a member? <a href="register.php">Register</a></p>
    </form>
</div>

<?php include("../includes/footer.php"); ?>
