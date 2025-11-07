<?php
session_start();
include('../includes/config.php');
include('../includes/header.php');

// Check if user is logged in and is a customer
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'customer') {
    header("Location: login.php");
    exit();
}

$userId = $_SESSION['user_id'];

// Fetch existing profile
$stmt = $conn->prepare("SELECT * FROM customer WHERE user_id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$profile = $result->fetch_assoc();

// If profile doesn't exist, create blank (rare)
if (!$profile) {
    $stmtInsert = $conn->prepare("INSERT INTO customer (user_id, fname, lname, phone, address, town, zipcode, image_path) VALUES (?, '', '', '', '', '', '', '')");
    $stmtInsert->bind_param("i", $userId);
    $stmtInsert->execute();

    $stmt = $conn->prepare("SELECT * FROM customer WHERE user_id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $profile = $result->fetch_assoc();
}

// Handle profile form submission
if (isset($_POST['submit'])) {
    $lname = trim($_POST['lname']);
    $fname = trim($_POST['fname']);
    $address = trim($_POST['address']);
    $town = trim($_POST['town']);
    $zipcode = trim($_POST['zipcode']);
    $phone = trim($_POST['phone']);
    $imagePath = $profile['image_path'] ?? '';

    // Image upload
    if (!empty($_FILES['image']['name'])) {
        $targetDir = "../uploads/";
        if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);

        $fileName = time() . "_" . basename($_FILES["image"]["name"]);
        $targetFile = $targetDir . $fileName;
        $fileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));
        $allowedTypes = ['jpg','jpeg','png','gif'];

        if (in_array($fileType, $allowedTypes) && $_FILES["image"]["size"] <= 5*1024*1024) {
            if (move_uploaded_file($_FILES["image"]["tmp_name"], $targetFile)) {
                $imagePath = "uploads/".$fileName;
            } else {
                $_SESSION['error'] = "Error uploading image.";
            }
        } else {
            $_SESSION['error'] = "Invalid file type or file too large.";
        }
    }

    // Update profile
    $stmtUpdate = $conn->prepare("UPDATE customer SET lname=?, fname=?, address=?, town=?, zipcode=?, phone=?, image_path=? WHERE user_id=?");
    $stmtUpdate->bind_param("sssssssi", $lname, $fname, $address, $town, $zipcode, $phone, $imagePath, $userId);

    if ($stmtUpdate->execute()) {
        $_SESSION['success'] = 'Profile saved successfully!';

        // Redirect based on new_user flag
        if (isset($_SESSION['new_user']) && $_SESSION['new_user'] === true) {
            unset($_SESSION['new_user']);
            header("Location: ../index.php"); // new user -> homepage
        } else {
            header("Location: profile.php"); // existing user -> stay
        }
        exit();
    } else {
        $_SESSION['error'] = 'Error saving profile.';
    }
}
?>
