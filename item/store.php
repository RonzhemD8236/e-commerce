<?php
session_start();

 
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    $_SESSION['auth_error'] = 'Please log in as admin to access this page.';
    header("Location: ../admin/login.php");
    exit();
}

require('../includes/config.php');

 
$_SESSION['desc']        = trim($_POST['description'] ?? '');
$_SESSION['short_desc']  = trim($_POST['short_description'] ?? '');
$_SESSION['specs']       = trim($_POST['specifications'] ?? '');
$_SESSION['cost']        = trim($_POST['cost_price'] ?? '');
$_SESSION['sell']        = trim($_POST['sell_price'] ?? '');
$_SESSION['qty']         = trim($_POST['quantity'] ?? '');
$_SESSION['category']    = trim($_POST['category'] ?? '');

if (isset($_POST['submit'])) {

    
    
    
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

    
    
    
    $uploadedImages = [];
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/jpg'];
    $max_file_size = 5 * 1024 * 1024; 
    $target_dir = "../uploads/";

    
    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0755, true);
    }

    if (isset($_FILES['image_path']) && !empty($_FILES['image_path']['name'][0])) {
        foreach ($_FILES['image_path']['name'] as $key => $name) {
            $tmp_name = $_FILES['image_path']['tmp_name'][$key];
            $error    = $_FILES['image_path']['error'][$key];
            $size     = $_FILES['image_path']['size'][$key];

            if ($error === UPLOAD_ERR_OK) {
                
                if ($size > $max_file_size) {
                    $_SESSION['imageError'] = "Image too large. Maximum size is 5MB.";
                    header("Location: create.php");
                    exit();
                }

                
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $mime = finfo_file($finfo, $tmp_name);
                finfo_close($finfo);

                if (!in_array($mime, $allowed_types)) {
                    $_SESSION['imageError'] = "Invalid image type. Only JPG, PNG, and GIF allowed.";
                    header("Location: create.php");
                    exit();
                }

                
                $extension = pathinfo($name, PATHINFO_EXTENSION);
                $filename = time() . "_" . rand(1000,9999) . "." . $extension;
                $target_file = $target_dir . $filename;

                if (move_uploaded_file($tmp_name, $target_file)) {
                    $uploadedImages[] = "uploads/" . $filename;
                } else {
                    $_SESSION['imageError'] = "Failed to upload image: " . htmlspecialchars($name);
                    header("Location: create.php");
                    exit();
                }
            } elseif ($error !== UPLOAD_ERR_NO_FILE) {
                $_SESSION['imageError'] = "Error uploading image: " . htmlspecialchars($name);
                header("Location: create.php");
                exit();
            }
        }
    }

    
    if (empty($uploadedImages)) {
        $uploadedImages[] = "uploads/default.png";
    }

    
    $db_path = json_encode($uploadedImages);

    
    
    
    $stmt = $conn->prepare("INSERT INTO item(description, short_description, specifications, cost_price, sell_price, category, image_path)
                            VALUES (?, ?, ?, ?, ?, ?, ?)");
    
    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }

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
        die("Error inserting item: " . $stmt->error);
    }

    $item_id = $stmt->insert_id;
    $stmt->close();

    
    
    
    $stmt2 = $conn->prepare("INSERT INTO stock(item_id, quantity) VALUES (?, ?)");
    
    if (!$stmt2) {
        die("Prepare failed: " . $conn->error);
    }

    $qty_int = intval($_SESSION['qty']);
    $stmt2->bind_param("ii", $item_id, $qty_int);

    if (!$stmt2->execute()) {
        die("Error inserting stock: " . $stmt2->error);
    }
    $stmt2->close();

    
    
    
    unset($_SESSION['desc'], $_SESSION['short_desc'], $_SESSION['specs'], $_SESSION['cost'], $_SESSION['sell'], $_SESSION['qty'], $_SESSION['category']);
    unset($_SESSION['descError'], $_SESSION['shortDescError'], $_SESSION['specsError'], $_SESSION['costError'], $_SESSION['sellError'], $_SESSION['qtyError'], $_SESSION['imageError'], $_SESSION['categoryError']);

    header("Location: index.php?msg=created");
    exit();
} else {
    header("Location: create.php");
    exit();
}
?>