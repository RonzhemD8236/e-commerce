<?php
session_start();
require('../includes/config.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $item_id      = intval($_POST['item_id']);
    $desc         = trim($_POST['description'] ?? '');
    $short_desc   = trim($_POST['short_description'] ?? '');
    $specs        = trim($_POST['specifications'] ?? '');
    $cost         = trim($_POST['cost_price'] ?? '');
    $sell         = trim($_POST['sell_price'] ?? '');
    $qty          = trim($_POST['quantity'] ?? '');
    $category     = trim($_POST['category'] ?? '');

    // ==========================
    // VALIDATION
    // ==========================
    $errors = [];
    if (empty($desc))        $errors['descError']       = 'Description is required';
    if (empty($short_desc))  $errors['shortDescError']  = 'Short description is required';
    if (empty($specs))       $errors['specsError']      = 'Specifications are required';
    if (!is_numeric($cost) || $cost <= 0) $errors['costError'] = 'Invalid cost price';
    if (!is_numeric($sell) || $sell <= 0) $errors['sellError'] = 'Invalid sell price';
    if (!is_numeric($qty) || $qty < 0)    $errors['qtyError']  = 'Quantity must be valid';
    if (empty($category))                 $errors['categoryError'] = 'Please select a category';

    if (!empty($errors)) {
        foreach ($errors as $key => $val) {
            $_SESSION[$key] = $val;
        }
        // Save old input
        $_SESSION['desc']       = $desc;
        $_SESSION['short_desc'] = $short_desc;
        $_SESSION['specs']      = $specs;
        $_SESSION['cost']       = $cost;
        $_SESSION['sell']       = $sell;
        $_SESSION['qty']        = $qty;
        $_SESSION['category']   = $category;

        header("Location: edit.php?id=$item_id");
        exit();
    }

    // ==========================
    // GET CURRENT IMAGES
    // ==========================
    $stmt = $conn->prepare("SELECT image_path FROM item WHERE item_id = ?");
    $stmt->bind_param("i", $item_id);
    $stmt->execute();
    $stmt->bind_result($old_images_json);
    $stmt->fetch();
    $stmt->close();

    $images = json_decode($old_images_json, true) ?: [];

    // ==========================
    // HANDLE REMOVED IMAGES
    // ==========================
    $keep_images = $_POST['keep_images'] ?? [];

    // Delete images that are not kept
    foreach ($images as $img) {
        if (!in_array($img, $keep_images) && $img !== "uploads/default.png" && file_exists("../" . $img)) {
            unlink("../" . $img);
        }
    }

    // Keep only the remaining images
    $images = $keep_images;

    // ==========================
    // HANDLE NEW IMAGE UPLOADS
    // ==========================
    if (!empty($_FILES['image_path']['name'][0])) {
        $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
        $target_dir = "../uploads/";

        foreach ($_FILES['image_path']['name'] as $key => $filename) {
            $tmp_name = $_FILES['image_path']['tmp_name'][$key];
            $file_type = $_FILES['image_path']['type'][$key];
            $error = $_FILES['image_path']['error'][$key];

            if ($error === UPLOAD_ERR_OK) {
                if (!in_array($file_type, $allowed_types)) {
                    $_SESSION['imageError'] = 'Invalid image format. Only JPG, PNG, GIF allowed.';
                    header("Location: edit.php?id=$item_id");
                    exit();
                }

                $new_name = time() . "_" . rand(1000,9999) . "_" . basename($filename);
                $target_file = $target_dir . $new_name;

                if (move_uploaded_file($tmp_name, $target_file)) {
                    $images[] = "uploads/" . $new_name; // Add new image
                } else {
                    $_SESSION['imageError'] = "Failed to upload image: $filename";
                    header("Location: edit.php?id=$item_id");
                    exit();
                }
            }
        }
    }

    // If no images exist, use default
    if (empty($images)) {
        $images[] = "uploads/default.png";
    }

    $images_json = json_encode($images);

    // ==========================
    // UPDATE ITEM TABLE
    // ==========================
    $stmt = $conn->prepare("UPDATE item 
                            SET description = ?, 
                                short_description = ?, 
                                specifications = ?, 
                                cost_price = ?, 
                                sell_price = ?, 
                                category = ?, 
                                image_path = ? 
                            WHERE item_id = ?");
    $stmt->bind_param("sssddssi", $desc, $short_desc, $specs, $cost, $sell, $category, $images_json, $item_id);
    $result1 = $stmt->execute();
    $stmt->close();

    // ==========================
    // UPDATE STOCK TABLE
    // ==========================
    $stmt2 = $conn->prepare("UPDATE stock SET quantity = ? WHERE item_id = ?");
    $stmt2->bind_param("ii", $qty, $item_id);
    $result2 = $stmt2->execute();
    $stmt2->close();

    // ==========================
    // CLEAR SESSION + RESULT
    // ==========================
    unset($_SESSION['desc'], $_SESSION['short_desc'], $_SESSION['specs'], $_SESSION['cost'], $_SESSION['sell'], $_SESSION['qty'], $_SESSION['category']);
    unset($_SESSION['descError'], $_SESSION['shortDescError'], $_SESSION['specsError'], $_SESSION['costError'], $_SESSION['sellError'], $_SESSION['qtyError'], $_SESSION['imageError'], $_SESSION['categoryError']);

    if ($result1 && $result2) {
        echo "<script>
                alert('Item updated successfully!');
                window.location.href = 'index.php';
              </script>";
        exit;
    } else {
        echo "<script>
                alert('Error updating item.');
                window.location.href = 'edit.php?id=$item_id';
              </script>";
        exit;
    }

} else {
    header("Location: index.php");
    exit;
}
?>
