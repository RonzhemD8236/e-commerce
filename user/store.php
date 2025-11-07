<?php
session_start();
include("../includes/config.php");

// Get form data
$email = trim($_POST['email']);
$password = trim($_POST['password']);
$confirmPass = trim($_POST['confirmPass']);

// Check if passwords match
if ($password !== $confirmPass) {
    $_SESSION['message'] = 'Passwords do not match';
    header("Location: register.php");
    exit();
}

// Hash password securely
$passwordHashed = password_hash($password, PASSWORD_DEFAULT);

// Insert new user
$sql = "INSERT INTO users (email, password, role) VALUES (?, ?, 'customer')";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $email, $passwordHashed);

if ($stmt->execute()) {
    $userId = $stmt->insert_id;

    // Insert blank profile
    $sqlProfile = "INSERT INTO customer (user_id, fname, lname, title, addressline, town, zipcode, phone, image_path) 
                   VALUES (?, '', '', '', '', '', '', '', '')";
    $stmtProfile = $conn->prepare($sqlProfile);
    $stmtProfile->bind_param("i", $userId);
    $stmtProfile->execute();

    // Set session variables
    $_SESSION['user_id'] = $userId;
    $_SESSION['role'] = 'customer';
    $_SESSION['email'] = $email;

    header("Location: ../user/profile.php");
    exit();
} else {
    $_SESSION['message'] = 'Registration failed. Email may already be in use.';
    header("Location: register.php");
    exit();
}
?>
