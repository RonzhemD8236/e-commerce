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

    // Get current stock - PREPARED STATEMENT
    $stmt = $conn->prepare("SELECT quantity FROM stock WHERE item_id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();
    
    $stock = intval($row['quantity']);

    if ($qty > $stock) {
        $response['message'] = "Not enough stock!";
        echo json_encode($response);
        exit;
    }

    // Deduct stock - PREPARED STATEMENT
    $stmt = $conn->prepare("UPDATE stock SET quantity = quantity - ? WHERE item_id = ?");
    $stmt->bind_param("ii", $qty, $id);
    $stmt->execute();
    $stmt->close();

    // Add to session cart
    if (!isset($_SESSION['cart_products'][$id])) {
        $_SESSION['cart_products'][$id] = [
            "item_id" => $id,
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

            // Quantity lowered → return stock - PREPARED STATEMENT
            if ($newQty < $oldQty) {
                $returnQty = $oldQty - $newQty;
                
                $stmt = $conn->prepare("UPDATE stock SET quantity = quantity + ? WHERE item_id = ?");
                $stmt->bind_param("ii", $returnQty, $id);
                $stmt->execute();
                $stmt->close();
            }

            // Quantity increased → check stock
            if ($newQty > $oldQty) {
                $needed = $newQty - $oldQty;

                // Check available stock - PREPARED STATEMENT
                $stmt = $conn->prepare("SELECT quantity FROM stock WHERE item_id = ?");
                $stmt->bind_param("i", $id);
                $stmt->execute();
                $result = $stmt->get_result();
                $row = $result->fetch_assoc();
                $stmt->close();

                if ($row['quantity'] >= $needed) {
                    // Deduct needed stock - PREPARED STATEMENT
                    $stmt = $conn->prepare("UPDATE stock SET quantity = quantity - ? WHERE item_id = ?");
                    $stmt->bind_param("ii", $needed, $id);
                    $stmt->execute();
                    $stmt->close();
                } else {
                    // Max allowed is oldQty + available
                    $newQty = $oldQty + $row['quantity'];
                    
                    // Set stock to 0 - PREPARED STATEMENT
                    $stmt = $conn->prepare("UPDATE stock SET quantity = 0 WHERE item_id = ?");
                    $stmt->bind_param("i", $id);
                    $stmt->execute();
                    $stmt->close();
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

            // Return stock - PREPARED STATEMENT
            $stmt = $conn->prepare("UPDATE stock SET quantity = quantity + ? WHERE item_id = ?");
            $stmt->bind_param("ii", $qtyToReturn, $remove_id);
            $stmt->execute();
            $stmt->close();

            unset($_SESSION['cart_products'][$remove_id]);
        }
    }

    // Redirect (NOT JSON)
    header("Location: view_cart.php");
    exit;
}

?>