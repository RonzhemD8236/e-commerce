

<?php
session_start();
include('../includes/config.php');

// Make sure output is always JSON for AJAX
header('Content-Type: application/json');

// Initialize response
$response = [
    "success" => false,
    "message" => "",
    "newStock" => 0
];

// ===== ADD TO CART (AJAX from product_details) =====
if (isset($_POST['type']) && $_POST['type'] === 'add') {

    $id = intval($_POST['item_id']);
    $qty = intval($_POST['item_qty']);

    // Get current stock
    $sql = "SELECT quantity FROM stock WHERE item_id = $id";
    $res = mysqli_query($conn, $sql);
    $row = mysqli_fetch_assoc($res);
    $stock = intval($row['quantity']);

    if ($qty > $stock) {
        $response['message'] = "Not enough stock!";
        echo json_encode($response);
        exit;
    }

    // Deduct stock
    mysqli_query($conn, "UPDATE stock SET quantity = quantity - $qty WHERE item_id = $id");

    // Add to session cart
// Add to session cart
if (!isset($_SESSION['cart_products'][$id])) {
    $_SESSION['cart_products'][$id] = [
        "item_id" => $id,  // ← ADD THIS LINE
        "item_name" => $_POST['item_name'],
        "item_price" => $_POST['item_price'],
        "item_qty" => $qty
    ];
} else {
    $_SESSION['cart_products'][$id]['item_qty'] += $qty;
}

    // Return updated stock
    $newStock = $stock - $qty;

    $response['success'] = true;
    $response['newStock'] = $newStock;
    echo json_encode($response);
    exit;
}



// ===== UPDATE CART (From view_cart.php — NOT AJAX) =====
if (isset($_POST['update_cart'])) {

    if (!empty($_POST['product_qty'])) {
        foreach ($_POST['product_qty'] as $id => $newQty) {

            $newQty = intval($newQty);
            if ($newQty < 1) $newQty = 1;

            $oldQty = $_SESSION['cart_products'][$id]['item_qty'];

            // Quantity lowered → return stock
            if ($newQty < $oldQty) {
                $returnQty = $oldQty - $newQty;
                mysqli_query($conn, "UPDATE stock SET quantity = quantity + $returnQty WHERE item_id = $id");
            }

            // Quantity increased → check stock
            if ($newQty > $oldQty) {
                $needed = $newQty - $oldQty;

                $res = mysqli_query($conn, "SELECT quantity FROM stock WHERE item_id = $id");
                $row = mysqli_fetch_assoc($res);

                if ($row['quantity'] >= $needed) {
                    mysqli_query($conn, "UPDATE stock SET quantity = quantity - $needed WHERE item_id = $id");
                } else {
                    // Max allowed is oldQty + available
                    $newQty = $oldQty + $row['quantity'];
                    mysqli_query($conn, "UPDATE stock SET quantity = 0 WHERE item_id = $id");
                }
            }

            // Update session cart
            $_SESSION['cart_products'][$id]['item_qty'] = $newQty;
        }
    }

    // Remove items
    if (!empty($_POST['remove_code'])) {
        foreach ($_POST['remove_code'] as $remove_id) {

            $qtyToReturn = $_SESSION['cart_products'][$remove_id]['item_qty'];

            mysqli_query($conn, "UPDATE stock SET quantity = quantity + $qtyToReturn WHERE item_id = $remove_id");

            unset($_SESSION['cart_products'][$remove_id]);
        }
    }

    // Redirect (NOT JSON)
    header("Location: view_cart.php");
    exit;
}

?>
