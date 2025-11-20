<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    $_SESSION['auth_error'] = 'Please log in as admin to access this page.';
    header("Location: ../admin/login.php");
    exit();
}
include('../includes/config.php');

if(isset($_POST['submit'])){
    
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = $_POST['role'];
    $created_at = date('Y-m-d H:i:s');

    
    $allowedRoles = ['admin', 'customer'];
    if (!in_array($role, $allowedRoles)) {
        die("Error: Invalid role specified.");
    }

    
    if (empty($username) || empty($email) || empty($_POST['password'])) {
        die("Error: All fields are required.");
    }

    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        die("Error: Invalid email format.");
    }

    
    $checkSql = "SELECT id FROM users WHERE username = ? LIMIT 1";
    $checkStmt = mysqli_prepare($conn, $checkSql);
    
    if (!$checkStmt) {
        die("Prepare failed: " . mysqli_error($conn));
    }
    
    mysqli_stmt_bind_param($checkStmt, "s", $username);
    mysqli_stmt_execute($checkStmt);
    mysqli_stmt_store_result($checkStmt);
    
    if (mysqli_stmt_num_rows($checkStmt) > 0) {
        mysqli_stmt_close($checkStmt);
        die("Error: Username already exists.");
    }
    mysqli_stmt_close($checkStmt);

    
    $checkEmailSql = "SELECT id FROM users WHERE email = ? LIMIT 1";
    $checkEmailStmt = mysqli_prepare($conn, $checkEmailSql);
    
    if (!$checkEmailStmt) {
        die("Prepare failed: " . mysqli_error($conn));
    }
    
    mysqli_stmt_bind_param($checkEmailStmt, "s", $email);
    mysqli_stmt_execute($checkEmailStmt);
    mysqli_stmt_store_result($checkEmailStmt);
    
    if (mysqli_stmt_num_rows($checkEmailStmt) > 0) {
        mysqli_stmt_close($checkEmailStmt);
        die("Error: Email already exists.");
    }
    mysqli_stmt_close($checkEmailStmt);

    
    $sql = "INSERT INTO users (username, email, password, role, created_at) 
            VALUES (?, ?, ?, ?, ?)";
    
    $stmt = mysqli_prepare($conn, $sql);
    
    if (!$stmt) {
        die("Prepare failed: " . mysqli_error($conn));
    }

    
    mysqli_stmt_bind_param($stmt, "sssss", $username, $email, $password, $role, $created_at);

    
    if(mysqli_stmt_execute($stmt)){
        mysqli_stmt_close($stmt);
        header("Location: index.php?success=1");
        exit();
    } else {
        $error = mysqli_stmt_error($stmt);
        mysqli_stmt_close($stmt);
        die("Error: " . $error);
    }
}
?>