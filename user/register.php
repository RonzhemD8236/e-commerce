<?php
session_start();
include("../includes/config.php");
include("../includes/header.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $confirmPass = trim($_POST['confirmPass']);

    if ($password !== $confirmPass) {
        $_SESSION['message'] = 'Passwords do not match';
        header("Location: register.php");
        exit();
    }

    $passwordHashed = password_hash($password, PASSWORD_DEFAULT);

    // Insert new user
    $sql = "INSERT INTO users (email, password, role) VALUES (?, ?, 'customer')";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $email, $passwordHashed);

    if ($stmt->execute()) {
        $userId = $stmt->insert_id;

        // Insert blank profile
        $sqlProfile = "INSERT INTO customer (user_id, fname, lname, phone, address, town, zipcode, image_path)
                       VALUES (?, '', '', '', '', '', '', '')";
        $stmtProfile = $conn->prepare($sqlProfile);
        $stmtProfile->bind_param("i", $userId);
        $stmtProfile->execute();

        // Set session variables
        $_SESSION['user_id'] = $userId;
        $_SESSION['email'] = $email;
        $_SESSION['role'] = 'customer';
        $_SESSION['new_user'] = true; // Flag for profile page

        // Redirect to profile to fill out details
        header("Location: profile.php");
        exit();
    } else {
        $_SESSION['message'] = 'Registration failed. Email may already be in use.';
        header("Location: register.php");
        exit();
    }
}
?>

<div class="row col-md-8 mx-auto">
    <?php include("../includes/alert.php"); ?>
    <form action="" method="POST">
        <div class="mb-3">
            <label>Email</label>
            <input type="email" name="email" class="form-control" required>
        </div>
        <div class="mb-3">
            <label>Password</label>
            <input type="password" name="password" class="form-control" required>
        </div>
        <div class="mb-3">
            <label>Confirm Password</label>
            <input type="password" name="confirmPass" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-primary">Register</button>
    </form>
</div>

<?php include("../includes/footer.php"); ?>
