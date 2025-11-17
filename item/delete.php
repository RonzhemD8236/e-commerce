<?php
require('../includes/config.php');

if (isset($_GET['id'])) {

    // Sanitize ID
    $item_id = intval($_GET['id']);

    // =============================
    // 1. GET IMAGE PATHS BEFORE DELETE
    // =============================
    $stmt = $conn->prepare("SELECT image_path FROM item WHERE item_id = ? LIMIT 1");
    $stmt->bind_param("i", $item_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {

        $data = $result->fetch_assoc();
        $image_paths_json = $data['image_path'];

        // Decode JSON to array
        $image_paths = json_decode($image_paths_json, true);

        // =============================
        // 2. DELETE STOCK FIRST
        // =============================
        $stmtStock = $conn->prepare("DELETE FROM stock WHERE item_id = ?");
        $stmtStock->bind_param("i", $item_id);
        $stmtStock->execute();

        // =============================
        // 3. DELETE ITEM
        // =============================
        $stmtDelete = $conn->prepare("DELETE FROM item WHERE item_id = ? LIMIT 1");
        $stmtDelete->bind_param("i", $item_id);
        $stmtDelete->execute();

        // =============================
        // 4. DELETE IMAGE FILES
        // =============================
        if (!empty($image_paths) && is_array($image_paths)) {
            foreach ($image_paths as $img) {
                $image_path = "../" . $img; // Adjust to correct folder path
                if (!empty($img) && $img !== "uploads/default.png" && file_exists($image_path)) {
                    unlink($image_path);
                }
            }
        }
    }
}

header("Location: index.php?msg=deleted");
exit;
?>
