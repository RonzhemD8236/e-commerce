<?php
session_start();

// Authentication check
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    $_SESSION['auth_error'] = 'Please log in as admin to access this page.';
    header("Location: ../admin/login.php");
    exit();
}

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
    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }
    
    $stmt->bind_param("i", $item_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 0) {
        $stmt->close();
        echo "<script>
                alert('Item not found.');
                window.location.href = 'index.php';
              </script>";
        exit();
    }
    
    $row = $result->fetch_assoc();
    $old_images_json = $row['image_path'];
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
        $max_file_size = 5 * 1024 * 1024; // 5MB
        $target_dir = "../uploads/";

        // Ensure directory exists
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0755, true);
        }

        foreach ($_FILES['image_path']['name'] as $key => $filename) {
            $tmp_name = $_FILES['image_path']['tmp_name'][$key];
            $error = $_FILES['image_path']['error'][$key];
            $size = $_FILES['image_path']['size'][$key];

            if ($error === UPLOAD_ERR_OK) {
                // Validate file size
                if ($size > $max_file_size) {
                    $_SESSION['imageError'] = "Image too large. Maximum size is 5MB.";
                    header("Location: edit.php?id=$item_id");
                    exit();
                }

                // Validate MIME type
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $mime = finfo_file($finfo, $tmp_name);
                finfo_close($finfo);

                if (!in_array($mime, $allowed_types)) {
                    $_SESSION['imageError'] = 'Invalid image format. Only JPG, PNG, GIF allowed.';
                    header("Location: edit.php?id=$item_id");
                    exit();
                }

                // Generate unique filename
                $extension = pathinfo($filename, PATHINFO_EXTENSION);
                $new_name = time() . "_" . rand(1000,9999) . "." . $extension;
                $target_file = $target_dir . $new_name;

                if (move_uploaded_file($tmp_name, $target_file)) {
                    $images[] = "uploads/" . $new_name;
                } else {
                    $_SESSION['imageError'] = "Failed to upload image: " . htmlspecialchars($filename);
                    header("Location: edit.php?id=$item_id");
                    exit();
                }
            } elseif ($error !== UPLOAD_ERR_NO_FILE) {
                $_SESSION['imageError'] = "Error uploading image: " . htmlspecialchars($filename);
                header("Location: edit.php?id=$item_id");
                exit();
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
    
    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }

    $cost_float = floatval($cost);
    $sell_float = floatval($sell);
    
    $stmt->bind_param("sssddssi", $desc, $short_desc, $specs, $cost_float, $sell_float, $category, $images_json, $item_id);
    $result1 = $stmt->execute();
    
    if (!$result1) {
        die("Error updating item: " . $stmt->error);
    }
    $stmt->close();

    // ==========================
    // UPDATE STOCK TABLE
    // ==========================
    $stmt2 = $conn->prepare("UPDATE stock SET quantity = ? WHERE item_id = ?");
    
    if (!$stmt2) {
        die("Prepare failed: " . $conn->error);
    }

    $qty_int = intval($qty);
    $stmt2->bind_param("ii", $qty_int, $item_id);
    $result2 = $stmt2->execute();
    
    if (!$result2) {
        die("Error updating stock: " . $stmt2->error);
    }
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