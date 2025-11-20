<?php
session_start();
include('../includes/config.php');

 
header('Content-Type: application/json');

 
$response = [
    "success" => false,
    "message" => "",
    "newStock" => 0
];

 
if (isset($_POST['type']) && $_POST['type'] === 'add') {

    $id = intval($_POST['item_id']);
    $qty = intval($_POST['item_qty']);

    
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

    
    $stmt = $conn->prepare("UPDATE stock SET quantity = quantity - ? WHERE item_id = ?");
    $stmt->bind_param("ii", $qty, $id);
    $stmt->execute();
    $stmt->close();

    
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

    
    $newStock = $stock - $qty;

    $response['success'] = true;
    $response['newStock'] = $newStock;
    echo json_encode($response);
    exit;
}



 
if (isset($_POST['update_cart'])) {

    if (!empty($_POST['product_qty'])) {
        foreach ($_POST['product_qty'] as $id => $newQty) {

            $newQty = intval($newQty);
            if ($newQty < 1) $newQty = 1;

            $oldQty = $_SESSION['cart_products'][$id]['item_qty'];

            
            if ($newQty < $oldQty) {
                $returnQty = $oldQty - $newQty;
                
                $stmt = $conn->prepare("UPDATE stock SET quantity = quantity + ? WHERE item_id = ?");
                $stmt->bind_param("ii", $returnQty, $id);
                $stmt->execute();
                $stmt->close();
            }

            
            if ($newQty > $oldQty) {
                $needed = $newQty - $oldQty;

                
                $stmt = $conn->prepare("SELECT quantity FROM stock WHERE item_id = ?");
                $stmt->bind_param("i", $id);
                $stmt->execute();
                $result = $stmt->get_result();
                $row = $result->fetch_assoc();
                $stmt->close();

                if ($row['quantity'] >= $needed) {
                    
                    $stmt = $conn->prepare("UPDATE stock SET quantity = quantity - ? WHERE item_id = ?");
                    $stmt->bind_param("ii", $needed, $id);
                    $stmt->execute();
                    $stmt->close();
                } else {
                    
                    $newQty = $oldQty + $row['quantity'];
                    
                    
                    $stmt = $conn->prepare("UPDATE stock SET quantity = 0 WHERE item_id = ?");
                    $stmt->bind_param("i", $id);
                    $stmt->execute();
                    $stmt->close();
                }
            }

            
            $_SESSION['cart_products'][$id]['item_qty'] = $newQty;
        }
    }

    
    if (!empty($_POST['remove_code'])) {
        foreach ($_POST['remove_code'] as $remove_id) {

            $qtyToReturn = $_SESSION['cart_products'][$remove_id]['item_qty'];

            
            $stmt = $conn->prepare("UPDATE stock SET quantity = quantity + ? WHERE item_id = ?");
            $stmt->bind_param("ii", $qtyToReturn, $remove_id);
            $stmt->execute();
            $stmt->close();

            unset($_SESSION['cart_products'][$remove_id]);
        }
    }

    
    header("Location: view_cart.php");
    exit;
}

?>