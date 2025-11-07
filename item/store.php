<?php
session_start();
include('../includes/config.php');

$_SESSION['cost'] = trim($_POST['cost_price']);
$_SESSION['sell'] = trim($_POST['sell_price']);
$_SESSION['desc'] = trim($_POST['description']);
$_SESSION['qty']  = $_POST['quantity'];

if (isset($_POST['submit'])) {
    $cost = trim($_POST['cost_price']);
    $sell = trim($_POST['sell_price']);
    $desc = trim($_POST['description']);
    $qty  = $_POST['quantity'];

    // Validation
    if (empty($desc)) {
        $_SESSION['descError'] = 'Please input a Product description';
        header("Location: create.php");
        exit();
    }

    if (empty($cost) || !is_numeric($cost)) {
        $_SESSION['costError'] = 'Error product price format';
        header("Location: create.php");
        exit();
    }

    if (empty($sell) || !is_numeric($sell)) {
        $_SESSION['sellError'] = 'Error product price format';
        header("Location: create.php");
        exit();
    }

    // ✅ Fix typo and handle image upload properly
    if (isset($_FILES['image_path']) && $_FILES['image_path']['error'] == 0) {
        $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
        $file_type = $_FILES['image_path']['type'];

        if (in_array($file_type, $allowed_types)) {
            $target_dir = "../uploads/";
            $filename = time() . "_" . basename($_FILES['image_path']['name']);
            $target_file = $target_dir . $filename;

            if (move_uploaded_file($_FILES['image_path']['tmp_name'], $target_file)) {
                $db_path = "uploads/" . $filename;
            } else {
                $_SESSION['imageError'] = "Failed to upload image.";
                header("Location: create.php");
                exit();
            }
        } else {
            $_SESSION['imageError'] = "Invalid image type. Only JPG, PNG, and GIF allowed.";
            header("Location: create.php");
            exit();
        }
    } else {
        $db_path = "uploads/default.png";
    }

    // Insert item
    $sql = "INSERT INTO item(description, cost_price, sell_price, image_path) 
            VALUES('{$desc}', '{$cost}', '{$sell}', '{$db_path}')";
    $result = mysqli_query($conn, $sql);

    // Insert stock
    $q_stock = "INSERT INTO stock(item_id, quantity) 
                VALUES(LAST_INSERT_ID(), {$qty})";
    $result2 = mysqli_query($conn, $q_stock);

    // ✅ After successful insert, clear session values before redirect
    if ($result && $result2) {
        unset($_SESSION['cost'], $_SESSION['sell'], $_SESSION['desc'], $_SESSION['qty']);
        unset($_SESSION['costError'], $_SESSION['sellError'], $_SESSION['descError'], $_SESSION['qtyError'], $_SESSION['imageError']);
        header("Location: index.php");
        exit();
    }
}
?>
