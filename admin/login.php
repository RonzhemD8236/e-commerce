<?php
session_start();

// ✅ Redirect if already logged in (BEFORE any output)
if (isset($_SESSION['user_id']) && $_SESSION['role'] === 'admin') {
    header("Location: dashboard.php");
    exit();
}

// ✅ Include ONLY config, NOT header (to avoid HTML output)
include("../includes/config.php");

if (isset($_POST['submit'])) {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $errors = array();

    // ✅ PHP Validation
    if (empty($email)) {
        $errors[] = 'Email is required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Invalid email format.';
    }

    if (empty($password)) {
        $errors[] = 'Password is required.';
    }

    if (empty($errors)) {
        $sql = "SELECT id, email, password, role, active FROM users WHERE email=? LIMIT 1";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();
        $stmt->bind_result($user_id, $user_email, $hashed_password, $role, $active);

        if ($stmt->num_rows === 1) {
            $stmt->fetch();

            if (!$active) {
                $_SESSION['message'] = 'Your account has been deactivated. Please contact support.';
            } elseif ($role !== 'admin') {
                $_SESSION['message'] = 'Access denied. Admins only.';
            } elseif (password_verify($password, $hashed_password)) {
                $_SESSION['email'] = $user_email;
                $_SESSION['user_id'] = $user_id;
                $_SESSION['role'] = $role;
                header("Location: dashboard.php");
                exit();
            } else {
                $_SESSION['message'] = 'Wrong email or password.';
            }
        } else {
            $_SESSION['message'] = 'Wrong email or password.';
        }
    } else {
        $_SESSION['message'] = implode('<br>', $errors);
    }
}
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Lensify</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

    <style type="text/css">
        body {
            background: url('https://i.pinimg.com/1200x/67/4c/03/674c034624b2c27e3a19a33d1ccbe608.jpg') no-repeat center center;
            background-size: cover;
            position: relative;
            min-height: 100vh;
            overflow: hidden;
            margin: 0;
            padding: 0;
        }

        body::before {
            content: '';
            position: absolute;
            top: 0; 
            left: 0;
            width: 100%; 
            height: 100%;
            background-color: rgba(0,0,0,0.5);
            z-index: 0;
        }

        .main-content {
            position: relative;
            z-index: 1;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }

        .login-container {
            background-color: rgba(255,255,255,0.95);
            backdrop-filter: blur(10px);
            padding: 3rem;
            border-radius: 15px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.4);
            max-width: 450px;
            width: 100%;
        }

        .login-container h3 {
            color: #333;
            font-weight: 700;
            margin-bottom: 2rem;
        }

        .form-label {
            font-weight: 600;
            color: #555;
        }

        .form-control {
            padding: 0.75rem;
            border-radius: 8px;
            border: 1px solid #ddd;
        }

        .form-control:focus {
            border-color: #000;
            box-shadow: 0 0 0 0.2rem rgba(0,0,0,0.1);
        }

        .btn-signin {
            background-color: #000 !important;
            border-color: #000 !important;
            color: white !important;
            padding: 0.75rem;
            font-weight: 600;
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        .btn-signin:hover {
            background-color: #333 !important;
            border-color: #333 !important;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.3);
        }

        .text-danger {
            font-size: 0.875rem;
            margin-top: 0.25rem;
            color: #dc3545;
        }

        .alert {
            border-radius: 8px;
            margin-bottom: 1.5rem;
        }

        #togglePassword {
            position: absolute;
            top: 38px;
            right: 15px;
            cursor: pointer;
            color: #666;
            z-index: 10;
        }

        #togglePassword:hover {
            color: #000;
        }

        .position-relative {
            position: relative;
        }

        .mb-3 {
            margin-bottom: 1rem;
        }

        .text-center {
            text-align: center;
        }

        .w-100 {
            width: 100%;
        }

        .alert-dismissible {
            padding-right: 3rem;
        }

        .btn-close {
            position: absolute;
            top: 0.5rem;
            right: 0.5rem;
        }
    </style>
</head>
<body>

<div class="main-content">
    <div class="login-container">
        <?php if (isset($_SESSION['message'])): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <?php echo $_SESSION['message']; unset($_SESSION['message']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST" id="loginForm">
            <h3 class="text-center">Admin Login</h3>
            
            <div class="mb-3">
                <label class="form-label">Email address</label>
                <input type="text" class="form-control" name="email" id="email" 
                       value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" placeholder="Enter your email">
                <small class="text-danger" id="emailError"></small>
            </div>

            <div class="mb-3 position-relative">
                <label class="form-label">Password</label>
                <input type="password" class="form-control" id="password" name="password" placeholder="Enter your password">
                <span id="togglePassword">
                    <i class="bi bi-eye"></i>
                </span>
                <small class="text-danger" id="passwordError"></small>
            </div>

            <button type="submit" class="btn btn-signin w-100" name="submit">Login</button>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>

<script type="text/javascript">
// Toggle Password Visibility
document.getElementById('togglePassword').addEventListener('click', function () {
    var pw = document.getElementById('password');
    var icon = this.querySelector('i');
    if (pw.type === 'password') {
        pw.type = 'text';
        icon.classList.replace('bi-eye', 'bi-eye-slash');
    } else {
        pw.type = 'password';
        icon.classList.replace('bi-eye-slash', 'bi-eye');
    }
});

// Client-side Validation
document.getElementById('loginForm').addEventListener('submit', function (e) {
    var valid = true;
    document.getElementById('emailError').textContent = '';
    document.getElementById('passwordError').textContent = '';

    var email = document.getElementById('email').value.trim();
    var password = document.getElementById('password').value.trim();
    var emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

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