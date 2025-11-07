<?php
session_start();
include("../includes/config.php");

// Get and sanitize form data
$email = trim($_POST['email']);
$password = trim($_POST['password']);
$confirmPass = trim($_POST['confirmPass']);

// Validate fields
if (empty($email) || empty($password) || empty($confirmPass)) {
    $_SESSION['message'] = 'All fields are required.';
    header("Location: register.php");
    exit();
}

// Check if passwords match
if ($password !== $confirmPass) {
    $_SESSION['message'] = 'Passwords do not match.';
    header("Location: register.php");
    exit();
}

// Validate email format
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['message'] = 'Invalid email format.';
    header("Location: register.php");
    exit();
}

// Check if email already exists in users table
$checkSql = "SELECT id FROM users WHERE email = ?";
$checkStmt = $conn->prepare($checkSql);
$checkStmt->bind_param("s", $email);
$checkStmt->execute();
$checkStmt->store_result();

if ($checkStmt->num_rows > 0) {
    $_SESSION['message'] = 'Email already exists. Please use another.';
    header("Location: register.php");
    exit();
}

// Hash password securely
$passwordHashed = password_hash($password, PASSWORD_DEFAULT);

// Insert new user into users table
$sql = "INSERT INTO users (email, password, role) VALUES (?, ?, 'customer')";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $email, $passwordHashed);

if ($stmt->execute()) {
    $userId = $stmt->insert_id;

    // Insert blank customer profile
    $sqlProfile = "INSERT INTO customer (user_id, fname, lname, title, addressline, town, zipcode, phone, image_path) 
                   VALUES (?, '', '', '', '', '', '', '', '')";
    $stmtProfile = $conn->prepare($sqlProfile);
    $stmtProfile->bind_param("i", $userId);
    $stmtProfile->execute();

    // Set session variables
    $_SESSION['user_id'] = $userId;
    $_SESSION['role'] = 'customer';
    $_SESSION['email'] = $email;
    $_SESSION['message'] = 'Registration successful! You can now complete your profile.';

    header("Location: profile.php");
    exit();
} else {
    $_SESSION['message'] = 'Registration failed. Please try again.';
    header("Location: register.php");
    exit();
}
?>
