<?php
session_start();
include("../includes/header.php");
include("../includes/config.php");

if (isset($_POST['submit'])) {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    $sql = "SELECT id, email, password, role FROM users WHERE email=? LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($user_id, $user_email, $hashed_password, $role);

    if ($stmt->num_rows === 1) {
        $stmt->fetch();

        // Verify password using password_verify
        if (password_verify($password, $hashed_password)) {
            $_SESSION['email'] = $user_email;
            $_SESSION['user_id'] = $user_id;
            $_SESSION['role'] = $role;
            header("Location: ../index.php");
            exit();
        } else {
            $_SESSION['message'] = 'Wrong email or password';
        }
    } else {
        $_SESSION['message'] = 'Wrong email or password';
    }
}
?>

<div class="row col-md-8 mx-auto ">
    <?php include("../includes/alert.php"); ?>
    <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST">
        <div class="form-outline mb-4">
            <br>
            <label class="form-label">Email address</label>
            <input type="email" class="form-control" name="email" required />
        </div>

        <div class="form-outline mb-4">
            <label class="form-label">Password</label>
            <input type="password" class="form-control" name="password" required />
        </div>

        <button type="submit" class="btn btn-primary btn-block mb-4" name="submit">Sign in</button>

        <div class="text-center">
            <p>Not a member? <a href="register.php">Register</a></p>
        </div>
    </form>
</div>

<?php include("../includes/footer.php"); ?>
