<?php
session_start();
require('../includes/config.php');

 
 
 
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    $_SESSION['auth_error'] = 'Please log in as admin to access this page.';
    header("Location: ../admin/login.php");
    exit();
}

 
 
 
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['error_message'] = 'Invalid item ID.';
    header("Location: index.php");
    exit();
}

 
$item_id = intval($_GET['id']);

 
 
 
$stmt = $conn->prepare("SELECT image_path FROM item WHERE item_id = ?");
if (!$stmt) {
    die("Prepare failed: " . $conn->error);
}

$stmt->bind_param("i", $item_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result && $result->num_rows > 0) {

    $data = $result->fetch_assoc();
    $image_paths_json = $data['image_path'];
    $stmt->close();

    
    $image_paths = json_decode($image_paths_json, true);

    
    
    
    $stmtStock = $conn->prepare("DELETE FROM stock WHERE item_id = ?");
    if (!$stmtStock) {
        die("Prepare failed: " . $conn->error);
    }
    
    $stmtStock->bind_param("i", $item_id);
    
    if (!$stmtStock->execute()) {
        $_SESSION['error_message'] = 'Error deleting stock data: ' . $stmtStock->error;
        $stmtStock->close();
        header("Location: index.php");
        exit();
    }
    
    $stock_deleted = $stmtStock->affected_rows;
    $stmtStock->close();

    
    
    
    $stmtDelete = $conn->prepare("DELETE FROM item WHERE item_id = ?");
    if (!$stmtDelete) {
        die("Prepare failed: " . $conn->error);
    }
    
    $stmtDelete->bind_param("i", $item_id);
    
    if (!$stmtDelete->execute()) {
        $_SESSION['error_message'] = 'Error deleting item: ' . $stmtDelete->error;
        $stmtDelete->close();
        header("Location: index.php");
        exit();
    }
    
    $item_deleted = $stmtDelete->affected_rows;
    $stmtDelete->close();

    
    if ($item_deleted === 0) {
        $_SESSION['error_message'] = 'Item could not be deleted or does not exist.';
        header("Location: index.php");
        exit();
    }

    
    
    
    if (!empty($image_paths) && is_array($image_paths)) {
        foreach ($image_paths as $img) {
            $image_path = "../" . $img;
            if (!empty($img) && $img !== "uploads/default.png" && file_exists($image_path)) {
                if (!unlink($image_path)) {
                    
                    error_log("Failed to delete image: " . $image_path);
                }
            }
        }
    }

    $_SESSION['success_message'] = 'Item deleted successfully.';
    header("Location: index.php?msg=deleted");
    exit();

} else {
    
    $stmt->close();
    $_SESSION['error_message'] = 'Item not found.';
    header("Location: index.php");
    exit();
}

$conn->close();
?>