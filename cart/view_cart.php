<?php
session_start();
include('../includes/header.php');
include('../includes/config.php');
?>

<style>
    * {
        box-sizing: border-box;
    }
    
    body {
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
        background-image: url('../uploads/your-image-name.jpg');
        background-size: cover;
        background-position: center;
        background-attachment: fixed;
        background-repeat: no-repeat;
        margin: 0;
        padding: 0;
    }
    
    body::before {
        content: '';
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(26, 26, 26, 0.85);
        z-index: -1;
    }
    
    .cart-container {
        max-width: 100%;
        margin: 0;
        padding: 20px 50px;
    }
    
    .cart-header {
        text-align: center;
        margin-bottom: 40px;
        padding: 20px 0;
    }
    
    .cart-header h1 {
        color: #bb86fc;
        font-size: 32px;
        font-weight: 600;
        margin: 0;
    }
    
    .cart-content {
        background: white;
        border-radius: 12px;
        padding: 50px 60px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.3);
    }
    
    .empty-cart {
        text-align: center;
        padding: 60px 20px;
        color: #666;
    }
    
    .empty-cart-icon {
        font-size: 64px;
        margin-bottom: 20px;
        opacity: 0.5;
    }
    
    .cart-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
        margin-bottom: 30px;
    }
    
    .cart-table thead {
        background: #f8f9fa;
        border-radius: 8px;
    }
    
    .cart-table th {
        padding: 18px 15px;
        text-align: left;
        font-weight: 600;
        color: #333;
        font-size: 14px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        border-bottom: 2px solid #e0e0e0;
    }
    
    .cart-table th:first-child {
        border-top-left-radius: 8px;
    }
    
    .cart-table th:last-child {
        border-top-right-radius: 8px;
        text-align: center;
    }
    
    .cart-table td {
        padding: 25px 15px;
        border-bottom: 1px solid #f0f0f0;
        color: #333;
        vertical-align: middle;
    }
    
    .cart-table tbody tr:hover {
        background: #f8f9fa;
    }
    
    .cart-table tbody tr:last-child td {
        border-bottom: none;
    }
    
    .product-name {
        font-weight: 500;
        color: #333;
        font-size: 15px;
    }
    
    .quantity-input-group {
        display: flex;
        flex-direction: column;
        gap: 5px;
    }
    
    .quantity-input {
        width: 80px;
        padding: 8px 12px;
        border: 2px solid #e0e0e0;
        border-radius: 6px;
        font-size: 14px;
        text-align: center;
        transition: border-color 0.3s ease;
    }
    
    .quantity-input:focus {
        outline: none;
        border-color: #bb86fc;
    }
    
    .stock-info {
        font-size: 12px;
        color: #666;
        margin-top: 4px;
    }
    
    .price-cell {
        font-weight: 500;
        color: #333;
        font-size: 15px;
    }
    
    .total-cell {
        font-weight: 600;
        color: #8b5cf6;
        font-size: 16px;
    }
    
    .remove-checkbox {
        width: 20px;
        height: 20px;
        cursor: pointer;
        accent-color: #bb86fc;
    }
    
    .cart-summary {
        background: #f8f9fa;
        padding: 25px 30px;
        border-radius: 8px;
        margin-bottom: 30px;
        border-left: 4px solid #bb86fc;
    }
    
    .cart-summary-total {
        font-size: 24px;
        font-weight: 700;
        color: #333;
        text-align: center;
    }
    
    .cart-summary-total .amount {
        color: #8b5cf6;
    }
    
    .cart-actions {
        display: flex;
        gap: 15px;
        justify-content: center;
        align-items: center;
        flex-wrap: wrap;
        padding: 20px 0;
    }
    
    .btn {
        padding: 12px 30px;
        border-radius: 8px;
        font-size: 15px;
        font-weight: 600;
        text-decoration: none;
        cursor: pointer;
        transition: all 0.3s ease;
        border: none;
        display: inline-block;
    }
    
    .btn-primary {
        background: linear-gradient(135deg, #8b5cf6 0%, #bb86fc 100%);
        color: white;
        box-shadow: 0 4px 15px rgba(187, 134, 252, 0.3);
    }
    
    .btn-primary:hover {
        background: linear-gradient(135deg, #7c3aed 0%, #a855f7 100%);
        box-shadow: 0 6px 20px rgba(187, 134, 252, 0.4);
        transform: translateY(-2px);
    }
    
    .btn-secondary {
        background: white;
        color: #8b5cf6;
        border: 2px solid #bb86fc;
    }
    
    .btn-secondary:hover {
        background: #f3e8ff;
        border-color: #8b5cf6;
    }
    
    .btn-outline {
        background: transparent;
        color: #666;
        border: 2px solid #ddd;
    }
    
    .btn-outline:hover {
        background: #f8f9fa;
        border-color: #999;
        color: #333;
    }
    
    @media (max-width: 968px) {
        .cart-container {
            padding: 20px;
        }
        
        .cart-content {
            padding: 20px;
            overflow-x: auto;
        }
        
        .cart-actions {
            flex-direction: column;
        }
        
        .btn {
            width: 100%;
        }
    }
</style>

<div class="cart-container">
    <div class="cart-header">
        <h1>🛒 Shopping Cart</h1>
    </div>

    <div class="cart-content">
        <form method="POST" action="cart_update.php">
            <table class="cart-table">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Quantity</th>
                        <th>Price</th>
                        <th>Total</th>
                        <th>Remove</th>
                    </tr>
                </thead>
                <tbody>

                <?php
                $total = 0;

                if (!empty($_SESSION['cart_products'])) {

                    foreach ($_SESSION['cart_products'] as $id => $itm) {

                        $name  = htmlspecialchars($itm['item_name']);
                        $qty   = (int)$itm['item_qty'];
                        $price = (float)$itm['item_price'];
                        $subtotal = $qty * $price;
                        $total += $subtotal;

                        // Get current stock from DB
                        $stmt = $conn->prepare("SELECT quantity FROM stock WHERE item_id = ?");
                        $stmt->bind_param("i", $id);
                        $stmt->execute();
                        $stmt->bind_result($stock);
                        $stmt->fetch();
                        $stmt->close();

                        if ($stock < 0) { $stock = 0; }

                        // Total available = current DB stock + quantity in cart
                        $availableForCart = $stock + $qty;

                        echo "<tr>
                                <td class='product-name'>$name</td>
                                <td>
                                    <div class='quantity-input-group'>
                                        <input type='number' 
                                               class='quantity-input'
                                               name='product_qty[$id]' 
                                               value='$qty' 
                                               min='1' 
                                               max='$availableForCart'>
                                        <small class='stock-info'>Max: $availableForCart available</small>
                                    </div>
                                </td>
                                <td class='price-cell'>₱" . number_format($price, 2) . "</td>
                                <td class='total-cell'>₱" . number_format($subtotal, 2) . "</td>
                                <td style='text-align:center;'><input type='checkbox' class='remove-checkbox' name='remove_code[]' value='$id'></td>
                              </tr>";
                    }
                } else {
                    echo "<tr><td colspan='5' class='empty-cart'>
                            <div class='empty-cart-icon'>🛒</div>
                            <h2>Your cart is empty</h2>
                            <p>Add some items to get started!</p>
                          </td></tr>";
                }
                ?>

                </tbody>
            </table>

            <?php if (!empty($_SESSION['cart_products'])): ?>
            <div class="cart-summary">
                <div class="cart-summary-total">
                    Total Amount: <span class="amount">₱<?= number_format($total, 2); ?></span>
                </div>
            </div>

            <div class="cart-actions">
                <button type="submit" name="update_cart" class="btn btn-primary">
                    Update Cart
                </button>
                <a href="checkout.php" class="btn btn-primary">
                    Proceed to Checkout
                </a>
                <a href="../index.php" class="btn btn-secondary">
                    Continue Shopping
                </a>
            </div>
            <?php else: ?>
            <div class="cart-actions">
                <a href="../index.php" class="btn btn-primary">
                    Start Shopping
                </a>
            </div>
            <?php endif; ?>

        </form>
    </div>
</div>

<?php include('../includes/footer.php'); ?>