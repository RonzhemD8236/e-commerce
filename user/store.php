<?php
session_start();
include("../includes/config.php");

// Get form data safely
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

// Insert new user into `users`
$sql = "INSERT INTO users (email, password, role) VALUES (?, ?, 'customer')";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $email, $passwordHashed);

if ($stmt->execute()) {
    $userId = $stmt->insert_id;

    // Create a blank profile in `customer`
    $sqlProfile = "INSERT INTO customer (user_id, fname, lname, phone, address, town, zipcode, image_path)
                   VALUES (?, '', '', '', '', '', '', '')";
    $stmtProfile = $conn->prepare($sqlProfile);
    $stmtProfile->bind_param("i", $userId);
    $stmtProfile->execute();

    // Set session variables
    $_SESSION['user_id'] = $userId;
    $_SESSION['role'] = 'customer';
    $_SESSION['email'] = $email;
    $_SESSION['new_user'] = true; // FLAG for profile.php

    // Redirect to profile page
    header("Location: profile.php");
    exit();
} else {
    $_SESSION['message'] = 'Registration failed. Email may already be in use.';
    header("Location: register.php");
    exit();
}
