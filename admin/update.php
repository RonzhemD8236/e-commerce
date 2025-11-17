<?php
session_start();
include('../includes/config.php');

if (isset($_POST['submit'])) {
    $id = $_POST['id'];
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $role = $_POST['role'];
    $password = trim($_POST['password']);

    // Update users table
    if (!empty($password)) {
        $passwordHashed = password_hash($password, PASSWORD_DEFAULT); // secure hash
        $sql = "UPDATE users SET name=?, email=?, role=?, password=? WHERE id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssi", $name, $email, $role, $passwordHashed, $id);
    } else {
        $sql = "UPDATE users SET name=?, email=?, role=? WHERE id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssi", $name, $email, $role, $id);
    }

    if ($stmt->execute()) {

        // Handle customer table logic
        if ($role === 'customer') {
            $checkSql = "SELECT * FROM customer WHERE user_id=?";
            $stmtCheck = $conn->prepare($checkSql);
            $stmtCheck->bind_param("i", $id);
            $stmtCheck->execute();
            $resultCheck = $stmtCheck->get_result();

            if ($resultCheck->num_rows === 0) {
                $insertSql = "INSERT INTO customer (user_id, fname, lname, title, addressline, town, zipcode, phone, image_path) 
                              VALUES (?, '', '', '', '', '', '', '', '')";
                $stmtInsert = $conn->prepare($insertSql);
                $stmtInsert->bind_param("i", $id);
                $stmtInsert->execute();
            }
        } else {
            $deleteSql = "DELETE FROM customer WHERE user_id=?";
            $stmtDelete = $conn->prepare($deleteSql);
            $stmtDelete->bind_param("i", $id);
            $stmtDelete->execute();
        }

        $_SESSION['success'] = "User updated successfully.";
        header("Location: index.php");
        exit();
    } else {
        $_SESSION['error'] = "Failed to update user.";
        header("Location: edit.php?id=$id");
        exit();
    }
} else {
    header("Location: index.php");
    exit();
}
?>
