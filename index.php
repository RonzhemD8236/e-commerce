<?php
session_start();
include('./includes/header.php');
include('./includes/config.php');

// ---------------------- CART DISPLAY ----------------------
if (isset($_SESSION["cart_products"]) && count($_SESSION["cart_products"]) > 0) {
    echo '<div class="cart-view-table-front" id="view-cart" 
    style="position:fixed; bottom:20px; right:20px; background:#f9f9f9; 
    border:1px solid #ccc; padding:20px; border-radius:10px; 
    box-shadow:0 4px 12px rgba(0,0,0,0.25); width:420px; height:auto; 
    max-height:70vh; overflow-y:auto; font-size:15px; z-index:999;">';

    echo '<h3>Your Shopping Cart</h3>';
    echo '<form method="POST" action="/shop/cart/cart_update.php">';
    echo '<table width="100%" cellpadding="6" cellspacing="0">';
    echo '<tbody>';
    $total = 0;
    $b = 0;

    foreach ($_SESSION["cart_products"] as $item_id => $cart_itm) {
        // ✅ Fetch missing item name if necessary
        if (empty($cart_itm["item_name"])) {
            $stmt = $conn->prepare("SELECT description FROM item WHERE item_id = ?");
            $stmt->bind_param("i", $item_id);
            $stmt->execute();
            $stmt->bind_result($fetched_name);
            $stmt->fetch();
            $stmt->close();
            $cart_itm["item_name"] = $fetched_name ?? 'Unknown Item';
        }

        $product_name = htmlspecialchars($cart_itm["item_name"]);
        $product_qty = (int)$cart_itm["item_qty"];
        $product_price = (float)$cart_itm["item_price"];
        $product_code = (int)$cart_itm["item_id"];
        $bg_color = ($b++ % 2 == 1) ? 'odd' : 'even';
        $subtotal = $product_price * $product_qty;
        $total += $subtotal;

        echo '<tr class="' . $bg_color . '">';
        echo '<td colspan="2">';

        // ✅ Product name + remove checkbox
        echo "
        <div style='display: flex; justify-content: space-between; align-items: center;'>
            <strong>{$product_name}</strong>
            <label style='font-size: 13px; color: #555;'>
                <input type='checkbox' name='remove_code[]' value='{$product_code}'> Remove
            </label>
        </div>";

        // ✅ Qty controls and subtotal
        echo "
        <div style='display: flex; align-items: center; gap: 6px; margin-top: 5px;'>
            <span style='font-weight: 500;'>Qty</span>
            <input 
                type='number' 
                name='product_qty[$product_code]' 
                value='{$product_qty}' 
                min='1'
                style='width: 50px; height: 28px; text-align: center; font-size: 13px; border: 1px solid #ccc; border-radius: 4px;'
            >
            <button 
                type='button' 
                class='qty-plus' 
                data-code='{$product_code}'
                style='width: 28px; height: 28px; font-size: 16px; font-weight: bold; border: 1px solid #ccc; border-radius: 4px; background-color: green; color: white; cursor: pointer;'
            >+</button>
            <button 
                type='button' 
                class='qty-minus' 
                data-code='{$product_code}'
                style='width: 28px; height: 28px; font-size: 16px; font-weight: bold; border: 1px solid #ccc; border-radius: 4px; background-color: red; color: white; cursor: pointer;'
            >−</button>
        </div>
        <div style='margin-top: 6px; font-size: 14px;'>
            <strong>Subtotal:</strong> ₱" . number_format($subtotal, 2) . "
        </div>
        ";

        echo '</td>';
        echo '</tr>';
    }

   echo '<tr><td colspan="2" style="padding-top: 10px;">';
echo '<div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 8px;">';
echo '<strong style="font-size: 16px;">Total: ₱' . number_format($total, 2) . '</strong>';
echo '<div style="display: flex; gap: 6px;">';
echo '<a href="/shop/cart/view_cart.php"><button type="button" class="btn btn-success btn-sm">Checkout</button></a>';
echo '<button type="submit" name="update_cart" class="btn btn-primary btn-sm" style="margin-right: 45px;">Update Cart</button>';
echo '<button type="submit" name="remove_items" class="btn btn-danger btn-sm" >Remove</button>';
echo '</div>';
echo '</div>';
echo '</td></tr>';

    echo '</tbody>';
    echo '</table>';
    echo '</form>';

    // ✅ JS for dynamic quantity adjustment
    echo "
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.qty-plus').forEach(function(btn) {
            btn.addEventListener('click', function() {
                const code = this.dataset.code;
                const input = document.querySelector('input[name=\"product_qty[' + code + ']\"]');
                if (input) input.value = parseInt(input.value) + 1;
            });
        });

        document.querySelectorAll('.qty-minus').forEach(function(btn) {
            btn.addEventListener('click', function() {
                const code = this.dataset.code;
                const input = document.querySelector('input[name=\"product_qty[' + code + ']\"]');
                if (input && parseInt(input.value) > 1) {
                    input.value = parseInt(input.value) - 1;
                }
            });
        });
    });
    </script>
    ";

    echo '</div>';
}


// ---------------------- PRODUCT LISTING ----------------------
$sql = "SELECT i.item_id AS itemId, i.description AS item_name, i.image_path, i.sell_price 
        FROM item i 
        INNER JOIN stock s USING (item_id)  
        ORDER BY i.item_id ASC";

$results = mysqli_query($conn, $sql);

if ($results) {
    $products_item = '<ul class="products">';

    while ($row = mysqli_fetch_assoc($results)) {
        $item_name = htmlspecialchars($row['item_name']);
        $price = number_format($row['sell_price'], 2);
        $itemId = $row['itemId'];

        // ✅ Image path correction
        $imageFile = !empty($row['image_path']) ? 'uploads/' . basename($row['image_path']) : 'uploads/default.png';
        $cacheBuster = file_exists($imageFile) ? filemtime($imageFile) : time();

        $products_item .= <<<EOT
<li class="product">
<form method="POST" action="cart/cart_update.php">
    <div class="product-content">
        <h3>{$item_name}</h3>
        <div class="product-thumb">
            <img src="{$imageFile}?v={$cacheBuster}" width="150" height="150" alt="{$item_name}">
        </div>
        <div class="product-info" style="margin-top: 7px;">
            <p style="margin: 2px 0;">Price: ₱{$price}</p>
            <fieldset style="border: none; padding: 0; margin: 2px;">
                <label>
                    <span>Quantity</span>
                    <input type="number" size="2" maxlength="2" name="item_qty" value="1" min="1" style="width:85px;" />
                </label>
            </fieldset>
            <input type="hidden" name="item_id" value="{$itemId}" />
            <input type="hidden" name="item_name" value="{$item_name}" />
            <input type="hidden" name="item_price" value="{$row['sell_price']}" />
            <input type="hidden" name="type" value="add" />
            <div align="center" style="margin-top:10px;">
                <button type="submit" class="add_to_cart" style="width:170px;">Add</button>
            </div>
        </div>
    </div>
</form>
</li>
EOT;
    }

    $products_item .= '</ul>';
    echo $products_item;
}

include('./includes/footer.php');
?>
