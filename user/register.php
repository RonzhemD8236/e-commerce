<?php
include("../includes/header.php");
include("../includes/config.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $confirmPass = trim($_POST['confirmPass']);

    // Validate fields
    if (empty($email) || empty($password) || empty($confirmPass)) {
        $_SESSION['error'] = "All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error'] = "Invalid email format.";
    } elseif ($password !== $confirmPass) {
        $_SESSION['error'] = "Passwords do not match.";
    } else {
        // Check if email already exists in the database
        $checkSql = "SELECT user_id FROM users WHERE email = ?";
        $checkStmt = $conn->prepare($checkSql);
        $checkStmt->bind_param("s", $email);
        $checkStmt->execute();
        $checkStmt->store_result();

        if ($checkStmt->num_rows > 0) {
            $_SESSION['error'] = "Email is already registered.";
        } else {
            // Hash password securely
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $role = "customer"; // Default role

            // Insert new user into users table
            $insertUserSql = "INSERT INTO users (email, password, role) VALUES (?, ?, ?)";
            $insertUserStmt = $conn->prepare($insertUserSql);
            $insertUserStmt->bind_param("sss", $email, $hashedPassword, $role);

            if ($insertUserStmt->execute()) {
                $userId = $conn->insert_id; // Get auto-generated user_id

                // Insert blank customer profile for new user
                $insertCustomerSql = "INSERT INTO customer (user_id, fname, lname, phone, address)
                      VALUES (?, '', '', '', '')";
                $insertCustomerStmt = $conn->prepare($insertCustomerSql);
                $insertCustomerStmt->bind_param("i", $userId);
                $insertCustomerStmt->execute();


                $_SESSION['success'] = "Registration successful! You can now log in.";
                header("Location: login.php");
                exit();
            } else {
                $_SESSION['error'] = "Error creating account. Please try again.";
            }
        }
    }
}
?>

<br>
<div class="container-fluid container-lg">
    <?php include("../includes/alert.php"); ?>
    <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST">
        <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input type="email" class="form-control" id="email" name="email" required>
        </div>

        <div class="mb-3">
            <label for="password" class="form-label">Password</label>
            <input type="password" class="form-control" id="password" name="password" required>
        </div>

        <div class="mb-3">
            <label for="password2" class="form-label">Confirm Password</label>
            <input type="password" class="form-control" id="password2" name="confirmPass" required>
        </div>

        <button type="submit" class="btn btn-primary">Register</button>
    </form>
</div>

<?php
include("../includes/footer.php");
?>
