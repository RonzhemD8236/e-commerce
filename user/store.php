<?php
session_start();
include("../includes/config.php");

// Only process POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: register.php");
    exit();
}

// Clear previous errors
unset($_SESSION['errors']);
unset($_SESSION['old']);

// Get and sanitize input
$username = trim($_POST['username'] ?? '');
$email = trim(strtolower($_POST['email'] ?? '')); // lowercase for consistency
$password = trim($_POST['password'] ?? '');
$confirmPass = trim($_POST['confirmPass'] ?? '');

$errors = [];
$old = [
    'username' => $username,
    'email' => $email
];

// ===== VALIDATION =====

// Validate username
if (empty($username)) {
    $errors['username'] = 'Username is required.';
} elseif (strlen($username) < 3) {
    $errors['username'] = 'Username must be at least 3 characters.';
} elseif (strlen($username) > 50) {
    $errors['username'] = 'Username must not exceed 50 characters.';
}

// Validate email
if (empty($email)) {
    $errors['email'] = 'Email is required.';
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors['email'] = 'Invalid email format.';
} else {
    // Check allowed domains
    $allowedDomains = ['@gmail.com', '@yahoo.com', '@outlook.com'];
    $validDomain = false;
    foreach ($allowedDomains as $domain) {
        if (str_ends_with($email, $domain)) {
            $validDomain = true;
            break;
        }
    }
    if (!$validDomain) {
        $errors['email'] = 'Email must be one of: gmail.com, yahoo.com, outlook.com';
    }
}

// Validate password
if (empty($password)) {
    $errors['password'] = 'Password is required.';
} elseif (strlen($password) < 8) {
    $errors['password'] = 'Password must be at least 8 characters long.';
}

// Validate confirm password
if (empty($confirmPass)) {
    $errors['password'] = 'Please confirm your password.';
} elseif ($password !== $confirmPass) {
    $errors['password'] = 'Passwords do not match.';
}

// If validation fails, redirect back
if (!empty($errors)) {
    $_SESSION['errors'] = $errors;
    $_SESSION['old'] = $old;
    header("Location: register.php");
    exit();
}

// ===== DATABASE OPERATIONS WITH PREPARED STATEMENTS =====

// Start transaction
$conn->begin_transaction();

try {
    // ✅ PREPARED STATEMENT 1: Check if username already exists
    $sql_check_username = "SELECT id FROM users WHERE username = ? LIMIT 1";
    $stmt_check_user = $conn->prepare($sql_check_username);
    
    if (!$stmt_check_user) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    
    $stmt_check_user->bind_param("s", $username);
    $stmt_check_user->execute();
    $stmt_check_user->store_result();
    
    if ($stmt_check_user->num_rows > 0) {
        $stmt_check_user->close();
        $_SESSION['errors'] = ['username' => 'Username is already in use.'];
        $_SESSION['old'] = $old;
        $conn->rollback();
        header("Location: register.php");
        exit();
    }
    $stmt_check_user->close();
    
    // ✅ PREPARED STATEMENT 2: Check if email already exists
    $sql_check_email = "SELECT id FROM users WHERE email = ? LIMIT 1";
    $stmt_check_email = $conn->prepare($sql_check_email);
    
    if (!$stmt_check_email) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    
    $stmt_check_email->bind_param("s", $email);
    $stmt_check_email->execute();
    $stmt_check_email->store_result();
    
    if ($stmt_check_email->num_rows > 0) {
        $stmt_check_email->close();
        $_SESSION['errors'] = ['email' => 'Email is already in use.'];
        $_SESSION['old'] = $old;
        $conn->rollback();
        header("Location: register.php");
        exit();
    }
    $stmt_check_email->close();
    
    // ✅ Hash password securely
    $passwordHashed = password_hash($password, PASSWORD_DEFAULT);
    
    // ✅ PREPARED STATEMENT 3: Insert into users table
    $role = 'customer'; // Default role
    $active = 1; // Active by default
    
    $sql_insert_user = "INSERT INTO users (username, email, password, role, active, created_at) 
                        VALUES (?, ?, ?, ?, ?, NOW())";
    
    $stmt_insert = $conn->prepare($sql_insert_user);
    
    if (!$stmt_insert) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    
    $stmt_insert->bind_param("ssssi", $username, $email, $passwordHashed, $role, $active);
    
    if (!$stmt_insert->execute()) {
        throw new Exception("Execute failed: " . $stmt_insert->error);
    }
    
    // Get the newly created user ID
    $user_id = $conn->insert_id;
    $stmt_insert->close();
    
    // ✅ PREPARED STATEMENT 4: Insert blank profile into customer table
    $default_country = 'Philippines';
    $default_state = 'Metro Manila';
    $empty_string = '';
    
    $sql_insert_customer = "INSERT INTO customer 
                            (user_id, email, fname, lname, addressline, town, country, state, zipcode, phone, date_of_birth, image_path) 
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt_customer = $conn->prepare($sql_insert_customer);
    
    if (!$stmt_customer) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    
    // Bind all parameters (i=integer, s=string)
    $stmt_customer->bind_param("isssssssssss", 
        $user_id,           // user_id (integer)
        $email,             // email (string)
        $empty_string,      // fname (empty)
        $empty_string,      // lname (empty)
        $empty_string,      // addressline (empty)
        $empty_string,      // town (empty)
        $default_country,   // country
        $default_state,     // state
        $empty_string,      // zipcode (empty)
        $empty_string,      // phone (empty)
        $empty_string,      // date_of_birth (empty)
        $empty_string       // image_path (empty)
    );
    
    if (!$stmt_customer->execute()) {
        throw new Exception("Execute failed: " . $stmt_customer->error);
    }
    
    $stmt_customer->close();
    
    // ✅ Commit transaction - Everything succeeded!
    $conn->commit();
    
    // ✅ Set session variables - Auto login after registration
    $_SESSION['user_id'] = $user_id;
    $_SESSION['role'] = $role;
    $_SESSION['email'] = $email;
    
    // Redirect to profile page
    header("Location: /lensify/e-commerce/user/profile.php");
    exit();
    
} catch (Exception $e) {
    // ✅ Rollback transaction if any error occurs
    $conn->rollback();
    
    // Log error for debugging (don't show detailed error to user)
    error_log("Registration error: " . $e->getMessage());
    
    $_SESSION['errors'] = ['general' => 'Registration failed. Please try again later.'];
    $_SESSION['old'] = $old;
    header("Location: register.php");
    exit();
}

// Close connection
$conn->close();
?>