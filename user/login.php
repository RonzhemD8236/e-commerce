<?php
session_start();
include("../includes/config.php");

if (isset($_SESSION['user_id']) && !empty($_SESSION['user_id'])) {

    if (isset($_SESSION['role'])) {
        if ($_SESSION['role'] === 'admin') {
            header("Location: ../admin/dashboard.php");
        } else {
            header("Location: ../homepage.php");
        }
    } else {
        header("Location: ../homepage.php");
    }
    exit();
}


if (isset($_POST['submit'])) {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $errors = [];


    if (empty($email)) {
        $errors[] = 'Email is required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Invalid email format.';
    }

    if (empty($password)) {
        $errors[] = 'Password is required.';
    }

    if (empty($errors)) {

        $sql = "SELECT u.id, u.username, u.email, u.password, u.role, u.active, c.customer_id 
                FROM users u 
                LEFT JOIN customer c ON u.id = c.user_id 
                WHERE u.email = ? 
                LIMIT 1";
        
        $stmt = $conn->prepare($sql);
        
        if ($stmt) {

            $stmt->bind_param("s", $email);
            

            $stmt->execute();

            $stmt->store_result();
            
            if ($stmt->num_rows === 1) {

                $stmt->bind_result($user_id, $username, $user_email, $hashed_password, $role, $active, $customer_id);
                $stmt->fetch();

                if ($role === 'admin') {
                    $_SESSION['message'] = 'Please use the admin login page.';
                    $_SESSION['message_type'] = 'warning';
                    header("Location: ../admin/login.php");
                    exit();
                }
                

                if (!$active) {
                    $_SESSION['message'] = 'Your account has been deactivated. Please contact admin.';
                    $_SESSION['message_type'] = 'danger';
                } elseif (password_verify($password, $hashed_password)) {
    
                    $_SESSION['user_id'] = $user_id;
                    $_SESSION['username'] = $username;
                    $_SESSION['email'] = $user_email;
                    $_SESSION['role'] = $role;
                    $_SESSION['customer_id'] = $customer_id;
                    

                    $_SESSION['message'] = 'Login successful! Welcome back, ' . htmlspecialchars($username) . '!';
                    $_SESSION['message_type'] = 'success';
                    

                    if (isset($_SESSION['redirect_after_login']) && !empty($_SESSION['redirect_after_login'])) {
                        $redirect_url = $_SESSION['redirect_after_login'];
                        unset($_SESSION['redirect_after_login']); 
                        header("Location: " . $redirect_url);
                        exit();
                    }
                    
                    header("Location: ../homepage.php");
                    exit();
                } else {

                    $_SESSION['message'] = 'Wrong email or password.';
                    $_SESSION['message_type'] = 'danger';
                }
            } else {

                $_SESSION['message'] = 'Wrong email or password.';
                $_SESSION['message_type'] = 'danger';
            }
            

            $stmt->close();
        } else {

            $_SESSION['message'] = 'Database error. Please try again later.';
            $_SESSION['message_type'] = 'danger';
            error_log("Login prepare statement failed: " . $conn->error);
        }
    } else {

        $_SESSION['message'] = implode('<br>', $errors);
        $_SESSION['message_type'] = 'danger';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
     
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
     
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    
    <style>
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    html, body {
        height: 100%;
        width: 100%;
        margin: 0;
        padding: 0;
        overflow: hidden;
    }

    body {
        background: url('../uploads/login-bg.jpeg') no-repeat center center fixed;
        background-size: cover;
        display: flex;
        align-items: center;
        justify-content: flex-end;
        padding-right: 8%;
    }

    .login-container {
        background: rgba(255, 255, 255, 0.95);
        padding: 2.5rem;
        border-radius: 10px;
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
        width: 100%;
        max-width: 420px;
        backdrop-filter: blur(10px);
    }

    .login-container h3 {
        color: #333;
        font-weight: 600;
    }

    .btn-signin {
        background-color: black;
        color: white;
        font-weight: 500;
        padding: 0.75rem;
        border: none;
    }

    .btn-signin:hover {
        background-color: black;
        color: white;
    }

    .login-container a {
        color: black;
        text-decoration: none;
    }

    .login-container a:hover {
        text-decoration: underline;
    }

    .admin-login-link {
        margin-top: 1rem;
        padding-top: 1rem;
        border-top: 1px solid #dee2e6;
        text-align: center;
        font-size: 0.9rem;
    }
    </style>
</head>
<body>

 
<div class="login-container">
    <?php include("../includes/alert.php"); ?>
    <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" 
      method="POST" 
      id="loginForm"
      novalidate>
    <h3 class="text-center mb-4">Login</h3>

    <div class="mb-3">
        <label class="form-label">Email address</label>
        <input type="text" class="form-control" name="email" id="email" autocomplete="off"
               value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
        <small class="text-danger" id="emailError"></small>
    </div>

    <div class="mb-3 position-relative">
        <label class="form-label">Password</label>
        <input type="password" class="form-control" id="password" name="password" autocomplete="off">
        <span id="togglePassword" style="position:absolute; top:35px; right:10px; cursor:pointer;">
            <i class="bi bi-eye"></i>
        </span>
        <small class="text-danger" id="passwordError"></small>
    </div>

    <button type="submit" class="btn btn-signin w-100 mb-3" name="submit">Login</button>

    <div class="text-center">
        <p>Not a member? <a href="register.php">Register</a></p>
    </div>

    <div class="admin-login-link">
        <p class="text-muted">Are you an admin? <a href="../admin/login.php">Admin Login</a></p>
    </div>
</form>

</div>

<script>

document.getElementById('togglePassword').addEventListener('click', function () {
    const passwordField = document.getElementById('password');
    const icon = this.querySelector('i');
    if (passwordField.type === 'password') {
        passwordField.type = 'text';
        icon.classList.replace('bi-eye', 'bi-eye-slash');
    } else {
        passwordField.type = 'password';
        icon.classList.replace('bi-eye-slash', 'bi-eye');
    }
});

document.getElementById('loginForm').addEventListener('submit', function (e) {
    let valid = true;
    document.getElementById('emailError').textContent = '';
    document.getElementById('passwordError').textContent = '';

    const email = document.getElementById('email').value.trim();
    const password = document.getElementById('password').value.trim();
    const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

    if (email === '') {
        document.getElementById('emailError').textContent = 'Email is required.';
        valid = false;
    } else if (!emailPattern.test(email)) {
        document.getElementById('emailError').textContent = 'Invalid email format.';
        valid = false;
    }

    if (password === '') {
        document.getElementById('passwordError').textContent = 'Password is required.';
        valid = false;
    }

    if (!valid) e.preventDefault();
});
</script>

</body>
</html>