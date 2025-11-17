<?php
session_start();
include("../includes/config.php");

// Clear previous errors
unset($_SESSION['errors']);
unset($_SESSION['old']);

// Get form data
$username = trim($_POST['username']);
$email = trim(strtolower($_POST['email'])); // lowercase for consistency
$password = trim($_POST['password']);
$confirmPass = trim($_POST['confirmPass']);

// Initialize error array
$errors = [];

// Preserve old input
$old = ['username' => $username, 'email' => $email];

// ✅ Password validation
if (strlen($password) < 8) {
    $errors['password'] = 'Password must be at least 8 characters long';
}

// ✅ Check if passwords match
if ($password !== $confirmPass) {
    $errors['password'] = 'Passwords do not match';
}

// ✅ Validate email format
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors['email'] = 'Invalid email format';
} else {
    // Allowed domains
    $allowedDomains = ['@gmail.com', '@yahoo.com', '@outlook.com'];
    $validDomain = false;
    foreach ($allowedDomains as $domain) {
        if (str_ends_with($email, $domain)) {
            $validDomain = true;
            break;
        }
    }
    if (!$validDomain) {
        $errors['email'] = 'Email must be one of: ' . implode(', ', $allowedDomains);
    }
}

// ✅ Check if username already exists
$sqlUsername = "SELECT id FROM users WHERE username = ?";
$stmtUsername = $conn->prepare($sqlUsername);
$stmtUsername->bind_param("s", $username);
$stmtUsername->execute();
$stmtUsername->store_result();
if ($stmtUsername->num_rows > 0) {
    $errors['username'] = 'Username is already in use';
}

// ✅ Check if email already exists
$sqlEmail = "SELECT id FROM users WHERE email = ?";
$stmtEmail = $conn->prepare($sqlEmail);
$stmtEmail->bind_param("s", $email);
$stmtEmail->execute();
$stmtEmail->store_result();
if ($stmtEmail->num_rows > 0) {
    $errors['email'] = 'Email is already in use';
}

// ✅ If there are errors, redirect back with messages
if (!empty($errors)) {
    $_SESSION['errors'] = $errors;
    $_SESSION['old'] = $old;
    header("Location: register.php");
    exit();
}

// ✅ Hash password securely
$passwordHashed = password_hash($password, PASSWORD_DEFAULT);

// ✅ Get current datetime for created_at
$createdAt = date('Y-m-d H:i:s');

// ✅ Insert new user with username and created_at
$sql = "INSERT INTO users (username, email, password, role, created_at) VALUES (?, ?, ?, 'customer', ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ssss", $username, $email, $passwordHashed, $createdAt);

if ($stmt->execute()) {
    $userId = $stmt->insert_id;

    // ✅ Insert blank profile linked to this user
    $sqlProfile = "INSERT INTO customer 
                   (user_id, email, fname, lname, addressline, town, country, state, zipcode, phone, date_of_birth, image_path) 
                   VALUES (?, ?, '', '', '', '', 'Philippines', 'Metro Manila', '', '', '', '')";
    $stmtProfile = $conn->prepare($sqlProfile);
    $stmtProfile->bind_param("is", $userId, $email);
    $stmtProfile->execute();

    // ✅ Set session variables
    $_SESSION['user_id'] = $userId;
    $_SESSION['role'] = 'customer';
    $_SESSION['email'] = $email;

    header("Location: /lensify/e-commerce/user/profile.php");
    exit();
} else {
    $_SESSION['errors']['general'] = 'Registration failed. Please try again.';
    $_SESSION['old'] = $old;
    header("Location: register.php");
    exit();
}
?>