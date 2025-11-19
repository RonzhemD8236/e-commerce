<?php
session_start();
include("../includes/config.php");

// ✅ AUTHENTICATION CHECK: Redirect if already logged in (BEFORE any output)
if (isset($_SESSION['user_id']) && !empty($_SESSION['user_id'])) {
    // Redirect based on role
    if (isset($_SESSION['role'])) {
        if ($_SESSION['role'] === 'admin') {
            header("Location: ../admin/dashboard.php");
        } else {
            header("Location: ../index.php");
        }
    } else {
        header("Location: ../index.php");
    }
    exit();
}

// ✅ Handle form submission BEFORE including header
if (isset($_POST['submit'])) {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $errors = [];

    // ===== SERVER-SIDE VALIDATION =====
    if (empty($email)) {
        $errors[] = 'Email is required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Invalid email format.';
    }

    if (empty($password)) {
        $errors[] = 'Password is required.';
    }

    // ===== PREPARED STATEMENT LOGIN =====
    if (empty($errors)) {
        // ✅ PREPARED STATEMENT: Prevent SQL Injection
        // Join users and customer tables to get all needed data in one query
        $sql = "SELECT u.id, u.username, u.email, u.password, u.role, u.active, c.customer_id 
                FROM users u 
                LEFT JOIN customer c ON u.id = c.user_id 
                WHERE u.email = ? 
                LIMIT 1";
        
        $stmt = $conn->prepare($sql);
        
        if ($stmt) {
            // Bind parameter (s = string)
            $stmt->bind_param("s", $email);
            
            // Execute query
            $stmt->execute();
            
            // Store result to check row count
            $stmt->store_result();
            
            if ($stmt->num_rows === 1) {
                // Bind result variables
                $stmt->bind_result($user_id, $username, $user_email, $hashed_password, $role, $active, $customer_id);
                $stmt->fetch();
                
                // ✅ Check if account is active
                if (!$active) {
                    $_SESSION['message'] = 'Your account has been deactivated. Please contact admin.';
                    $_SESSION['message_type'] = 'danger';
                } elseif (password_verify($password, $hashed_password)) {
                    // ✅ LOGIN SUCCESSFUL - Set session variables
                    $_SESSION['user_id'] = $user_id;
                    $_SESSION['username'] = $username;
                    $_SESSION['email'] = $user_email;
                    $_SESSION['role'] = $role;
                    $_SESSION['customer_id'] = $customer_id;
                    
                    // Set success message
                    $_SESSION['message'] = 'Login successful! Welcome back, ' . htmlspecialchars($username) . '!';
                    $_SESSION['message_type'] = 'success';
                    
                    // ✅ Check if there's a redirect URL stored (user tried to access protected page)
                    if (isset($_SESSION['redirect_after_login']) && !empty($_SESSION['redirect_after_login'])) {
                        $redirect_url = $_SESSION['redirect_after_login'];
                        unset($_SESSION['redirect_after_login']); // Clear it after use
                        header("Location: " . $redirect_url);
                        exit();
                    }
                    
                    // ✅ Default redirect based on role
                    if ($role === 'admin') {
                        header("Location: ../admin/dashboard.php");
                    } else {
                        header("Location: ../index.php");
                    }
                    exit();
                } else {
                    // Wrong password
                    $_SESSION['message'] = 'Wrong email or password.';
                    $_SESSION['message_type'] = 'danger';
                }
            } else {
                // User not found
                $_SESSION['message'] = 'Wrong email or password.';
                $_SESSION['message_type'] = 'danger';
            }
            
            // Close statement
            $stmt->close();
        } else {
            // Database error
            $_SESSION['message'] = 'Database error. Please try again later.';
            $_SESSION['message_type'] = 'danger';
            error_log("Login prepare statement failed: " . $conn->error);
        }
    } else {
        // Validation errors
        $_SESSION['message'] = implode('<br>', $errors);
        $_SESSION['message_type'] = 'danger';
    }
}

// Include header AFTER all redirects
include("../includes/header.php");
?>

<style>
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}


</style>

<!-- ✅ Login Form -->
<div class="main-content">
    <div class="content">
        <div class="login-container">
            <?php include("../includes/alert.php"); ?>
            <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST" id="loginForm" novalidate>
                <h3 class="text-center mb-4">Login</h3>
                <div class="mb-3">
                    <label class="form-label">Email address</label>
                    <input type="text" class="form-control" name="email" id="email" 
                           value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                    <small class="text-danger" id="emailError"></small>
                </div>

                <div class="mb-3 position-relative">
                    <label class="form-label">Password</label>
                    <input type="password" class="form-control" id="password" name="password">
                    <span id="togglePassword" style="position:absolute; top:35px; right:10px; cursor:pointer;">
                        <i class="bi bi-eye"></i>
                    </span>
                    <small class="text-danger" id="passwordError"></small>
                </div>

                <button type="submit" class="btn btn-signin w-100 mb-3" name="submit">Login</button>

                <div class="text-center">
                    <p>Not a member? <a href="register.php">Register</a></p>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// ✅ Password Show/Hide
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

// ✅ Client-side Form Validation (NO HTML5)
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

<?php include("../includes/footer.php"); ?>