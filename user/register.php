<?php
session_start();
include("../includes/header.php");

// Get errors and old input if available
$errors = $_SESSION['errors'] ?? [];
$old = $_SESSION['old'] ?? [];
unset($_SESSION['errors'], $_SESSION['old']); // clear after use
?>

<style>
nav.navbar { display: none !important; }

body {
    background: url('../uploads/login-bg.jpeg') no-repeat center center;
    background-size: cover;
    background-position: center;
    position: relative;
    min-height: 100vh;
    overflow: hidden;
}

body::before {
    content: '';
    position: absolute;
    top: 0; left: 0;
    width: 100%; height: 100%;
    background-color: rgba(0, 0, 0, 0.5);
    z-index: 0;
    pointer-events: none;
}

header, nav, .navbar { position: relative; z-index: 10; }

.main-content {
    position: relative;
    z-index: 1;
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: flex-end;
    padding: 2rem;
}

.content { max-width: 540px; width: 100%; }

.register-container {
    background-color: rgba(255, 255, 255, 0.85);
    backdrop-filter: blur(10px);
    padding: 2rem;
    border-radius: 10px;
    box-shadow: 0 8px 32px rgba(0,0,0,0.3);
    width: 100%;
}

.btn-register { background-color: #000 !important; border-color: #000 !important; color: #fff !important; }
.btn-register:hover { background-color: #333 !important; border-color: #333 !important; }
.btn-secondary { background-color: #6c757d !important; border-color: #6c757d !important; }
.btn-secondary:hover { background-color: #5a6268 !important; border-color: #545b62 !important; }
</style>

<div class="main-content">
    <div class="content">
        <div class="register-container">
            <?php include("../includes/alert.php"); ?>
            <form action="store.php" method="POST" id="registerForm" novalidate>
                <h3 class="text-center mb-4">Register</h3>
                
                <div class="mb-3">
                    <label for="username" class="form-label">Username</label>
                    <input type="text" class="form-control" id="username" name="username" 
                           value="<?php echo isset($old['username']) ? htmlspecialchars($old['username']) : ''; ?>">
                    <small class="text-danger" id="usernameError"></small>
                    <?php if(isset($errors['username'])): ?>
                        <small class="text-danger d-block"><?php echo $errors['username']; ?></small>
                    <?php endif; ?>
                </div>

                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="text" class="form-control" id="email" name="email" 
                           value="<?php echo isset($old['email']) ? htmlspecialchars($old['email']) : ''; ?>">
                    <small class="text-danger" id="emailError"></small>
                    <?php if(isset($errors['email'])): ?>
                        <small class="text-danger d-block"><?php echo $errors['email']; ?></small>
                    <?php endif; ?>
                </div>

                <div class="mb-3 position-relative">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" class="form-control" id="password" name="password">
                    <span id="togglePassword" style="position:absolute; top:35px; right:10px; cursor:pointer;">
                        <i class="bi bi-eye"></i>
                    </span>
                    <small class="text-danger" id="passwordError"></small>
                    <?php if(isset($errors['password'])): ?>
                        <small class="text-danger d-block"><?php echo $errors['password']; ?></small>
                    <?php endif; ?>
                </div>

                <div class="mb-3 position-relative">
                    <label for="password2" class="form-label">Confirm Password</label>
                    <input type="password" class="form-control" id="password2" name="confirmPass">
                    <span id="togglePassword2" style="position:absolute; top:35px; right:10px; cursor:pointer;">
                        <i class="bi bi-eye"></i>
                    </span>
                    <small class="text-danger" id="confirmError"></small>
                </div>

                <button type="submit" class="btn btn-register w-100 mb-2">Register</button>
                <a href="login.php" class="btn btn-secondary w-100 mb-3">Cancel</a>

                <?php if(isset($errors['general'])): ?>
                    <div class="alert alert-danger mt-2"><?php echo $errors['general']; ?></div>
                <?php endif; ?>

                <div class="text-center">
                    <p>Already a member? <a href="login.php">Login</a></p>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Toggle Password Show/Hide
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

document.getElementById('togglePassword2').addEventListener('click', function () {
    const passwordField = document.getElementById('password2');
    const icon = this.querySelector('i');
    if (passwordField.type === 'password') {
        passwordField.type = 'text';
        icon.classList.replace('bi-eye', 'bi-eye-slash');
    } else {
        passwordField.type = 'password';
        icon.classList.replace('bi-eye-slash', 'bi-eye');
    }
});

// JavaScript validation (NO HTML5 validation)
document.getElementById('registerForm').addEventListener('submit', function(e) {
    let valid = true;

    // Clear previous errors
    document.getElementById('usernameError').textContent = '';
    document.getElementById('emailError').textContent = '';
    document.getElementById('passwordError').textContent = '';
    document.getElementById('confirmError').textContent = '';

    const username = document.getElementById('username').value.trim();
    const email = document.getElementById('email').value.trim();
    const password = document.getElementById('password').value.trim();
    const confirm = document.getElementById('password2').value.trim();
    const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

    // Validate username
    if(username === '') {
        document.getElementById('usernameError').textContent = 'Username is required.';
        valid = false;
    } else if(username.length < 3) {
        document.getElementById('usernameError').textContent = 'Username must be at least 3 characters.';
        valid = false;
    }

    // Validate email
    if(email === '') {
        document.getElementById('emailError').textContent = 'Email is required.';
        valid = false;
    } else if(!emailPattern.test(email)) {
        document.getElementById('emailError').textContent = 'Invalid email format.';
        valid = false;
    } else {
        // Check allowed domains
        const allowedDomains = ['@gmail.com', '@yahoo.com', '@outlook.com'];
        let validDomain = false;
        for(let i = 0; i < allowedDomains.length; i++) {
            if(email.toLowerCase().endsWith(allowedDomains[i])) {
                validDomain = true;
                break;
            }
        }
        if(!validDomain) {
            document.getElementById('emailError').textContent = 'Email must be gmail.com, yahoo.com, or outlook.com';
            valid = false;
        }
    }

    // Validate password
    if(password === '') {
        document.getElementById('passwordError').textContent = 'Password is required.';
        valid = false;
    } else if(password.length < 8) {
        document.getElementById('passwordError').textContent = 'Password must be at least 8 characters long.';
        valid = false;
    }

    // Validate confirm password
    if(confirm === '') {
        document.getElementById('confirmError').textContent = 'Confirm Password is required.';
        valid = false;
    } else if(password !== confirm) {
        document.getElementById('confirmError').textContent = 'Passwords do not match.';
        valid = false;
    }

    if(!valid) e.preventDefault();
});
</script>

<?php include("../includes/footer.php"); ?>