<?php
include('../includes/config.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $item_id = intval($_POST['item_id']);
    $desc = mysqli_real_escape_string($conn, $_POST['desc']);
    $cost = floatval($_POST['cost']);
    $sell = floatval($_POST['sell']);
    $qty = intval($_POST['qty']);

    // Get current image
    $query = "SELECT image_path FROM item WHERE item_id = $item_id";
    $result = mysqli_query($conn, $query);
    $row = mysqli_fetch_assoc($result);
    $old_image = $row['image_path'];

    // Handle image upload
    if (!empty($_FILES['image']['name'])) {
        $target_dir = "../uploads/";
        $file_name = time() . "_" . basename($_FILES["image"]["name"]);
        $target_file = $target_dir . $file_name;
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];

        if (in_array($imageFileType, $allowed_types)) {
            if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
                if (file_exists("../" . $old_image)) {
                    unlink("../" . $old_image);
                }
                // ✅ Store path relative to /shop/
                $image_path = "uploads/" . $file_name;
            } else {
                $image_path = $old_image;
            }
        } else {
            echo "<script>
                    alert('Invalid image format. Only JPG, PNG, and GIF allowed.');
                    window.location.href = 'edit.php?id=$item_id';
                  </script>";
            exit;
        }
    } else {
        $image_path = $old_image;
    }

    // Update item
    $sql = "UPDATE item 
            SET description = '$desc', 
                cost_price = $cost, 
                sell_price = $sell, 
                image_path = '$image_path' 
            WHERE item_id = $item_id";
    $result1 = mysqli_query($conn, $sql);

    // Update stock
    $sql2 = "UPDATE stock SET quantity = $qty WHERE item_id = $item_id";
    $result2 = mysqli_query($conn, $sql2);

    if ($result1 && $result2) {
        echo "<script>
                alert('Item updated successfully!');
                window.location.href = 'index.php';
              </script>";
    } else {
        echo "<script>
                alert('Error updating item.');
                window.location.href = 'edit.php?id=$item_id';
              </script>";
    }
} else {
    header('Location: index.php');
    exit;
}
?>
