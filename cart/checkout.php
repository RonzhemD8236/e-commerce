<?php
session_start();
include('../includes/header.php');
include('../includes/config.php');

// ✅ Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    die("<h2>❌ You must be logged in to checkout.</h2>");
}

// ✅ Check if cart has items
if (!isset($_SESSION['cart_products']) || count($_SESSION['cart_products']) === 0) {
    die("<h2>❌ Your cart is empty.</h2>");
}

// Fetch customer info using correct columns
$stmt = $conn->prepare("SELECT customer_id, fname, lname, phone, addressline FROM customer WHERE user_id = ? LIMIT 1");
if (!$stmt) die("Prepare failed: " . $conn->error);
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) die("❌ Customer not found.");
$customer = $result->fetch_assoc();
$customer_id = $customer['customer_id'];
$customer_name = $customer['fname'] . ' ' . $customer['lname'];
$customer_phone = $customer['phone'];
$customer_address = $customer['addressline'];
$stmt->close();

// Calculate merchandise total
$merchandise_total = 0;
foreach ($_SESSION['cart_products'] as $itm) {
    $merchandise_total += $itm['item_price'] * $itm['item_qty'];
}
$default_shipping = 50; // default delivery fee

?>

<style>
    * {
        box-sizing: border-box;
    }
    
    body {
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
        background-image: url('../uploads/checkout-bg.jpg');
        background-size: cover;
        background-position: center;
        background-attachment: fixed;
        background-repeat: no-repeat;
        margin: 0;
        padding: 0;
    }
    
    .checkout-container {
        max-width: 1600px;
        margin: 0 auto;
        padding: 20px 40px;
    }
    
    .checkout-header {
        text-align: center;
        margin-bottom: 40px;
        padding: 20px 0;
    }
    
    .checkout-header h1 {
        color: #bb86fc;
        font-size: 32px;
        font-weight: 600;
        margin: 0;
    }
    
    .checkout-layout {
        display: grid;
        grid-template-columns: 1fr 450px;
        gap: 40px;
        align-items: start;
    }
    
    .main-section {
        background: #2a2a2a;
        border-radius: 12px;
        padding: 30px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.3);
    }
    
    .order-summary {
        background: #2a2a2a;
        border-radius: 12px;
        padding: 30px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.3);
        position: sticky;
        top: 20px;
    }
    
    .section-title {
        font-size: 20px;
        font-weight: 600;
        color: #bb86fc;
        margin: 0 0 20px 0;
        padding-bottom: 15px;
        border-bottom: 2px solid #3a3a3a;
    }
    
    .customer-info {
        background: #1a1a1a;
        padding: 20px;
        border-radius: 8px;
        margin-bottom: 30px;
        border-left: 4px solid #bb86fc;
    }
    
    .customer-info strong {
        font-size: 16px;
        color: #e0e0e0;
        display: block;
        margin-bottom: 8px;
    }
    
    .customer-info p {
        margin: 5px 0;
        color: #b0b0b0;
        font-size: 14px;
    }
    
    .cart-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
        margin-bottom: 30px;
    }
    
    .cart-table thead {
        background: #1a1a1a;
    }
    
    .cart-table th {
        padding: 15px;
        text-align: left;
        font-weight: 600;
        color: #bb86fc;
        font-size: 14px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .cart-table td {
        padding: 20px 15px;
        border-bottom: 1px solid #3a3a3a;
        color: #b0b0b0;
    }
    
    .cart-table tbody tr:last-child td {
        border-bottom: none;
    }
    
    .product-name {
        font-weight: 500;
        color: #e0e0e0;
    }
    
    .option-group {
        margin-bottom: 30px;
    }
    
    .option-label {
        display: flex;
        align-items: center;
        padding: 15px 20px;
        border: 2px solid #3a3a3a;
        border-radius: 8px;
        margin-bottom: 12px;
        cursor: pointer;
        transition: all 0.3s ease;
        background: #1a1a1a;
        color: #e0e0e0;
    }
    
    .option-label:hover {
        border-color: #bb86fc;
        background: #2a1f3a;
    }
    
    .option-label input[type="radio"] {
        margin-right: 12px;
        width: 20px;
        height: 20px;
        cursor: pointer;
    }
    
    .option-label input[type="radio"]:checked + span {
        font-weight: 600;
        color: #bb86fc;
    }
    
    .option-label.selected {
        border-color: #bb86fc;
        background: #2a1f3a;
    }
    
    .payment-details {
        margin-top: 15px;
        padding: 20px;
        background: #1a1a1a;
        border-radius: 8px;
        border: 1px solid #3a3a3a;
    }
    
    .payment-details h4 {
        margin: 0 0 20px 0;
        color: #bb86fc;
        font-size: 16px;
    }
    
    .form-group {
        margin-bottom: 15px;
    }
    
    .form-group label {
        display: block;
        margin-bottom: 8px;
        color: #e0e0e0;
        font-weight: 500;
        font-size: 14px;
    }
    
    .form-group input {
        width: 100%;
        padding: 12px 15px;
        border: 1px solid #3a3a3a;
        border-radius: 6px;
        font-size: 14px;
        transition: border-color 0.3s ease;
        background: #2a2a2a;
        color: #e0e0e0;
    }
    
    .form-group input:focus {
        outline: none;
        border-color: #bb86fc;
    }
    
    .summary-row {
        display: flex;
        justify-content: space-between;
        padding: 12px 0;
        color: #b0b0b0;
        font-size: 15px;
    }
    
    .summary-row.total {
        border-top: 2px solid #3a3a3a;
        margin-top: 15px;
        padding-top: 20px;
        font-size: 18px;
        font-weight: 600;
        color: #bb86fc;
    }
    
    .place-order-btn {
        width: 100%;
        padding: 18px;
        background: linear-gradient(135deg, #8b5cf6 0%, #bb86fc 100%);
        color: white;
        border: none;
        border-radius: 8px;
        font-size: 16px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        margin-top: 20px;
        box-shadow: 0 4px 15px rgba(187, 134, 252, 0.3);
    }
    
    .place-order-btn:hover {
        background: linear-gradient(135deg, #7c3aed 0%, #a855f7 100%);
        box-shadow: 0 6px 20px rgba(187, 134, 252, 0.4);
        transform: translateY(-2px);
    }
    
    .items-summary {
        margin-bottom: 20px;
    }
    
    .item-row {
        display: flex;
        justify-content: space-between;
        padding: 12px 0;
        font-size: 14px;
        color: #b0b0b0;
        border-bottom: 1px solid #3a3a3a;
    }
    
    .item-row:last-child {
        border-bottom: none;
    }
    
    .item-name {
        flex: 1;
        color: #e0e0e0;
    }
    
    .item-qty {
        margin: 0 15px;
        color: #808080;
    }
    
    @media (max-width: 968px) {
        .checkout-layout {
            grid-template-columns: 1fr;
        }
        
        .order-summary {
            position: static;
        }
    }
</style>

<div class="checkout-container">
    <div class="checkout-header">
        <h1>Checkout</h1>
    </div>

    <form method="POST" action="checkout_process.php" id="checkoutForm">
        <div class="checkout-layout">
            <!-- Main Section -->
            <div class="main-section">
                <!-- Customer Info -->
                <h2 class="section-title">Delivery Information</h2>
                <div class="customer-info">
                    <strong><?= htmlspecialchars($customer_name) ?></strong>
                    <p>📞 <?= htmlspecialchars($customer_phone) ?></p>
                    <p>📍 <?= nl2br(htmlspecialchars($customer_address)) ?></p>
                </div>

                <!-- Cart Items -->
                <h2 class="section-title">Order Items</h2>
                <table class="cart-table">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Quantity</th>
                            <th>Price</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($_SESSION['cart_products'] as $itm): 
                            $subtotal = $itm['item_price'] * $itm['item_qty']; ?>
                        <tr>
                            <td class="product-name"><?= htmlspecialchars($itm['item_name']) ?></td>
                            <td><?= $itm['item_qty'] ?></td>
                            <td>₱<?= number_format($itm['item_price'],2) ?></td>
                            <td>₱<?= number_format($subtotal,2) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <!-- Shipping Method -->
                <h2 class="section-title">Shipping Method</h2>
                <div class="option-group">
                    <label class="option-label selected">
                        <input type="radio" name="shipping_method" value="delivery" checked>
                        <span>🚚 Standard Delivery (₱50)</span>
                    </label>
                    <label class="option-label">
                        <input type="radio" name="shipping_method" value="pickup">
                        <span>🏪 Store Pick-up (Free)</span>
                    </label>
                </div>

                <!-- Payment Method -->
                <h2 class="section-title">Payment Method</h2>
                <div class="option-group">
                    <label class="option-label selected">
                        <input type="radio" name="payment_method" value="cod" checked>
                        <span>💵 Cash on Delivery</span>
                    </label>
                    <label class="option-label">
                        <input type="radio" name="payment_method" value="card">
                        <span>💳 Credit/Debit Card</span>
                    </label>
                    <label class="option-label">
                        <input type="radio" name="payment_method" value="ewallet">
                        <span>📱 E-Wallet</span>
                    </label>
                </div>

                <div id="cardDetails" class="payment-details" style="display:none;">
                    <h4>Card Details</h4>
                    <div class="form-group">
                        <label>Card Number</label>
                        <input type="text" name="card_number" maxlength="16" placeholder="1234 5678 9012 3456">
                    </div>
                    <div class="form-group">
                        <label>Expiry Date</label>
                        <input type="month" name="card_expiry">
                    </div>
                    <div class="form-group">
                        <label>CVV</label>
                        <input type="text" name="card_cvv" maxlength="4" placeholder="123">
                    </div>
                    <div class="form-group">
                        <label>Name on Card</label>
                        <input type="text" name="card_name" placeholder="John Doe">
                    </div>
                </div>

                <div id="ewalletDetails" class="payment-details" style="display:none;">
                    <h4>E-Wallet Details</h4>
                    <div class="form-group">
                        <label>Wallet ID</label>
                        <input type="text" name="ewallet_id" placeholder="Enter your wallet ID">
                    </div>
                    <div class="form-group">
                        <label>Account Name</label>
                        <input type="text" name="ewallet_name" placeholder="John Doe">
                    </div>
                    <div class="form-group">
                        <label>Mobile Number</label>
                        <input type="text" name="ewallet_number" placeholder="09123456789">
                    </div>
                </div>
            </div>

            <!-- Order Summary Sidebar -->
            <div class="order-summary">
                <h2 class="section-title">Order Summary</h2>
                
                <div class="items-summary">
                    <?php foreach ($_SESSION['cart_products'] as $itm): ?>
                    <div class="item-row">
                        <span class="item-name"><?= htmlspecialchars($itm['item_name']) ?></span>
                        <span class="item-qty">x<?= $itm['item_qty'] ?></span>
                        <span>₱<?= number_format($itm['item_price'] * $itm['item_qty'], 2) ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>

                <div class="summary-row">
                    <span>Subtotal</span>
                    <span>₱<span id="merchTotal"><?= number_format($merchandise_total,2) ?></span></span>
                </div>
                <div class="summary-row">
                    <span>Shipping Fee</span>
                    <span>₱<span id="shipFee"><?= number_format($default_shipping,2) ?></span></span>
                </div>
                <div class="summary-row total">
                    <span>Total</span>
                    <span>₱<span id="totalPayment"><?= number_format($merchandise_total + $default_shipping,2) ?></span></span>
                </div>

                <button type="submit" class="place-order-btn">Place Order</button>
            </div>
        </div>

        <!-- Pass customer ID -->
        <input type="hidden" name="customer_id" value="<?= $customer_id ?>">
    </form>
</div>

<script>
const paymentRadios = document.querySelectorAll('input[name="payment_method"]');
const cardDiv = document.getElementById('cardDetails');
const walletDiv = document.getElementById('ewalletDetails');
const shippingRadios = document.querySelectorAll('input[name="shipping_method"]');
const allOptions = document.querySelectorAll('.option-label');

// Handle option selection styling
allOptions.forEach(label => {
    const radio = label.querySelector('input[type="radio"]');
    radio.addEventListener('change', () => {
        // Remove selected class from all labels in the same group
        const groupName = radio.name;
        document.querySelectorAll(`input[name="${groupName}"]`).forEach(r => {
            r.closest('.option-label').classList.remove('selected');
        });
        // Add selected class to current label
        label.classList.add('selected');
    });
});

paymentRadios.forEach(r => {
    r.addEventListener('change', ()=> {
        cardDiv.style.display = r.value === 'card' ? 'block' : 'none';
        walletDiv.style.display = r.value === 'ewallet' ? 'block' : 'none';
    });
});

shippingRadios.forEach(r => {
    r.addEventListener('change', ()=> {
        const shipFee = r.value === 'delivery' ? 50 : 0;
        document.getElementById('shipFee').textContent = shipFee.toFixed(2);
        const merchTotal = parseFloat(document.getElementById('merchTotal').textContent.replace(/,/g,''));
        document.getElementById('totalPayment').textContent = (merchTotal + shipFee).toFixed(2);
    });
});
</script>

<?php include('../includes/footer.php'); ?>