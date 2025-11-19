<?php
session_start();
include('../includes/config.php');

if(isset($_POST['submit'])){
    // Get form data and escape
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = $_POST['role'];
    $created_at = date('Y-m-d H:i:s');

    // Insert into users table
    // Insert into users table
    $sql = "INSERT INTO users (username, email, password, role, created_at) 
            VALUES (?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssss", $username, $email, $password, $role, $created_at);

    if($stmt->execute()){
        header("Location: index.php");
        exit();
    } else {
        die("Error: ".mysqli_error($conn));
    }
}
?>
