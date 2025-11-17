<?php
session_start();
require('../includes/config.php');

// Save old input for form repopulation
$_SESSION['desc']        = trim($_POST['description'] ?? '');
$_SESSION['short_desc']  = trim($_POST['short_description'] ?? '');
$_SESSION['specs']       = trim($_POST['specifications'] ?? '');
$_SESSION['cost']        = trim($_POST['cost_price'] ?? '');
$_SESSION['sell']        = trim($_POST['sell_price'] ?? '');
$_SESSION['qty']         = trim($_POST['quantity'] ?? '');
$_SESSION['category']    = trim($_POST['category'] ?? '');

if (isset($_POST['submit'])) {

    // ======================
    // VALIDATION
    // ======================
    if (empty($_SESSION['desc'])) {
        $_SESSION['descError'] = 'Please input a Product description';
        header("Location: create.php");
        exit();
    }

    if (empty($_SESSION['short_desc'])) {
        $_SESSION['shortDescError'] = 'Short description is required';
        header("Location: create.php");
        exit();
    }

    if (empty($_SESSION['specs'])) {
        $_SESSION['specsError'] = 'Specifications are required';
        header("Location: create.php");
        exit();
    }

    if (!is_numeric($_SESSION['cost']) || $_SESSION['cost'] <= 0) {
        $_SESSION['costError'] = 'Invalid cost price value';
        header("Location: create.php");
        exit();
    }

    if (!is_numeric($_SESSION['sell']) || $_SESSION['sell'] <= 0) {
        $_SESSION['sellError'] = 'Invalid selling price value';
        header("Location: create.php");
        exit();
    }

    if (!is_numeric($_SESSION['qty']) || $_SESSION['qty'] < 0) {
        $_SESSION['qtyError'] = 'Quantity must be a valid number';
        header("Location: create.php");
        exit();
    }

    if (empty($_SESSION['category'])) {
        $_SESSION['categoryError'] = 'Please select a category';
        header("Location: create.php");
        exit();
    }

    // ======================
    // IMAGE UPLOAD (MULTIPLE)
    // ======================
    $uploadedImages = []; // Array to hold uploaded image paths
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
    $target_dir = "../uploads/";

    if (isset($_FILES['image_path'])) {
        foreach ($_FILES['image_path']['name'] as $key => $name) {
            $tmp_name = $_FILES['image_path']['tmp_name'][$key];
            $error    = $_FILES['image_path']['error'][$key];
            $type     = $_FILES['image_path']['type'][$key];

            if ($error === UPLOAD_ERR_OK) {
                if (!in_array($type, $allowed_types)) {
                    $_SESSION['imageError'] = "Invalid image type. Only JPG, PNG, and GIF allowed.";
                    header("Location: create.php");
                    exit();
                }

                $filename = time() . "_" . rand(1000,9999) . "_" . basename($name);
                $target_file = $target_dir . $filename;

                if (move_uploaded_file($tmp_name, $target_file)) {
                    $uploadedImages[] = "uploads/" . $filename; // Save relative path
                } else {
                    $_SESSION['imageError'] = "Failed to upload image: $name";
                    header("Location: create.php");
                    exit();
                }
            }
        }
    }

    // If no images uploaded, use default
    if (empty($uploadedImages)) {
        $uploadedImages[] = "uploads/default.png";
    }

    // Convert to JSON for DB storage
    $db_path = json_encode($uploadedImages);

    // ======================
    // INSERT ITEM (PREPARED STATEMENT)
    // ======================
    $stmt = $conn->prepare("INSERT INTO item(description, short_description, specifications, cost_price, sell_price, category, image_path)
                            VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param(
        "sssddss",
        $_SESSION['desc'],
        $_SESSION['short_desc'],
        $_SESSION['specs'],
        $_SESSION['cost'],
        $_SESSION['sell'],
        $_SESSION['category'],
        $db_path
    );

    if (!$stmt->execute()) {
        die("Error inserting item: " . $conn->error);
    }

    $item_id = $stmt->insert_id;
    $stmt->close();

    // ======================
    // INSERT STOCK
    // ======================
    $stmt2 = $conn->prepare("INSERT INTO stock(item_id, quantity) VALUES (?, ?)");
    $stmt2->bind_param("ii", $item_id, $_SESSION['qty']);

    if (!$stmt2->execute()) {
        die("Error inserting stock: " . $conn->error);
    }
    $stmt2->close();

    // ======================
    // CLEAR SESSION + REDIRECT
    // ======================
    unset($_SESSION['desc'], $_SESSION['short_desc'], $_SESSION['specs'], $_SESSION['cost'], $_SESSION['sell'], $_SESSION['qty'], $_SESSION['category']);
    unset($_SESSION['descError'], $_SESSION['shortDescError'], $_SESSION['specsError'], $_SESSION['costError'], $_SESSION['sellError'], $_SESSION['qtyError'], $_SESSION['imageError'], $_SESSION['categoryError']);

    header("Location: index.php?msg=created");
    exit();
}
?>
