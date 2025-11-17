<?php
session_start();
include('../includes/config.php');

if(isset($_POST['submit'])){
    // Get form data and escape
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = $_POST['role'];
    $created_at = date('Y-m-d H:i:s');

    // Insert into users table
    $sql = "INSERT INTO users (username, email, password, role, created_at) 
            VALUES ('$username', '$email', '$password', '$role', '$created_at')";

    if(mysqli_query($conn, $sql)){
        header("Location: index.php");
        exit();
    } else {
        die("Error: ".mysqli_error($conn));
    }
}
?>
