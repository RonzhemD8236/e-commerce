<?php
session_start();
include('../includes/header.php');
include('../includes/config.php');

// ---------- LOGIN CHECK ----------
if (!isset($_SESSION['user_id'])) {
    header("Location: /lensify/e-commerce/user/login.php");
    exit();
}

// ---------- GET CUSTOMER ID ----------
$user_id = (int)$_SESSION['user_id'];
$stmt = $conn->prepare("SELECT customer_id, fname, lname FROM customer WHERE user_id = ? LIMIT 1");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("❌ Customer not found.");
}

$customer = $result->fetch_assoc();
$customer_id = (int)$customer['customer_id'];
$customer_name = $customer['fname'] . ' ' . $customer['lname'];
$stmt->close();

// ---------- FETCH USER'S ORDERS (FIXED) ----------
$stmt = $conn->prepare("SELECT o.orderinfo_id AS orderId, o.date_placed, o.shipping, o.status, o.payment_method, o.shipping_method FROM orderinfo o WHERE o.customer_id = ? ORDER BY o.date_placed DESC");
$stmt->bind_param("i", $customer_id);
$stmt->execute();
$result = $stmt->get_result();
$orderCount = $result->num_rows;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders - Lensify</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background-image: url('../uploads/orders-bg.jpg');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            background-repeat: no-repeat;
            min-height: 100vh;
            position: relative;
        }

        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, #1a1a1a 0%, #2d2d2d 100%);
            z-index: -2;
        }

        body::after {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            z-index: -1;
        }

        .orders-container {
            width: calc(100% - 200px);
            margin: 0 100px;
            padding: 40px 0;
            min-height: calc(100vh - 200px);
        }

        .page-header {
            background: linear-gradient(135deg, rgba(20, 20, 20, 0.95) 0%, rgba(40, 40, 40, 0.95) 100%);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 40px;
            text-align: center;
            margin-bottom: 40px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.5);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .page-header h1 {
            color: white;
            font-size: 2.5em;
            font-weight: 700;
            margin-bottom: 10px;
        }

        .page-header p {
            color: rgba(255, 255, 255, 0.8);
            font-size: 1.1em;
        }

        .order-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }

        .stat-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
            border: 1px solid rgba(255, 255, 255, 0.3);
            transition: transform 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
        }

        .stat-card i {
            font-size: 2em;
            color: #333;
            margin-bottom: 15px;
        }

        .stat-card h3 {
            color: #666;
            font-size: 0.9em;
            margin-bottom: 10px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .stat-card .number {
            font-size: 2em;
            font-weight: 700;
            color: #1a1a1a;
        }

        .empty-state {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 20px;
            padding: 80px 40px;
            text-align: center;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        }

        .empty-state i {
            font-size: 5em;
            color: #ddd;
            margin-bottom: 30px;
        }

        .empty-state h2 {
            color: #333;
            font-size: 2em;
            margin-bottom: 15px;
        }

        .empty-state p {
            color: #666;
            font-size: 1.1em;
            margin-bottom: 30px;
        }

        .shop-btn {
            display: inline-block;
            background: linear-gradient(135deg, #1a1a1a 0%, #2d2d2d 100%);
            color: white;
            padding: 15px 40px;
            border-radius: 12px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.4);
        }

        .shop-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 25px rgba(0, 0, 0, 0.6);
            color: white;
        }

        .order-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 20px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
            border: 1px solid rgba(255, 255, 255, 0.3);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .order-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.4);
        }

        .order-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-bottom: 20px;
            border-bottom: 2px solid #e0e0e0;
            margin-bottom: 25px;
            flex-wrap: wrap;
            gap: 15px;
        }

        .order-id {
            font-size: 1.3em;
            font-weight: 700;
            color: #1a1a1a;
        }

        .order-date {
            color: #666;
            font-size: 0.95em;
        }

        .order-date i {
            margin-right: 5px;
        }

        .status-badge {
            padding: 8px 20px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.9em;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .status-pending {
            background: #fff3cd;
            color: #856404;
        }

        .status-processing {
            background: #cfe2ff;
            color: #084298;
        }

        .status-shipped {
            background: #d1ecf1;
            color: #0c5460;
        }

        .status-delivered {
            background: #d1e7dd;
            color: #0f5132;
        }

        .status-cancelled {
            background: #f8d7da;
            color: #842029;
        }

        .status-canceled {
            background: #f8d7da;
            color: #842029;
        }

        .order-items {
            margin: 25px 0;
        }

        .item-row {
            display: flex;
            gap: 20px;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 12px;
            margin-bottom: 15px;
            align-items: center;
            transition: background 0.3s ease;
        }

        .item-row:hover {
            background: #e9ecef;
        }

        .item-image {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 10px;
            border: 2px solid #dee2e6;
            flex-shrink: 0;
        }

        .item-details {
            flex: 1;
            min-width: 0;
        }

        .item-name {
            font-weight: 600;
            color: #1a1a1a;
            font-size: 1.05em;
            margin-bottom: 5px;
        }

        .item-quantity {
            color: #666;
            font-size: 0.9em;
        }

        .item-price {
            font-weight: 700;
            color: #1a1a1a;
            font-size: 1.1em;
            text-align: right;
            flex-shrink: 0;
        }

        .order-summary {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 12px;
            margin-top: 25px;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            color: #666;
        }

        .summary-row.total {
            border-top: 2px solid #dee2e6;
            margin-top: 10px;
            padding-top: 15px;
            font-size: 1.2em;
            font-weight: 700;
            color: #1a1a1a;
        }

        .order-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 2px solid #e0e0e0;
            flex-wrap: wrap;
            gap: 15px;
        }

        .order-info {
            display: flex;
            gap: 30px;
            flex-wrap: wrap;
        }

        .info-item {
            display: flex;
            align-items: center;
            gap: 8px;
            color: #666;
            font-size: 0.95em;
        }

        .info-item i {
            color: #333;
        }

        @media (max-width: 768px) {
            .orders-container {
                width: calc(100% - 40px);
                margin: 0 20px;
            }

            .page-header h1 {
                font-size: 2em;
            }

            .order-header {
                flex-direction: column;
                align-items: flex-start;
            }

            .item-row {
                flex-direction: column;
                text-align: center;
            }

            .item-image {
                margin: 0 auto;
            }

            .item-price {
                text-align: center;
            }

            .order-footer {
                flex-direction: column;
                align-items: flex-start;
            }

            .order-info {
                flex-direction: column;
                gap: 10px;
            }
        }
    </style>
</head>
<body>
    <br><br><br><br>
    <div class="orders-container">
        <div class="page-header">
            <h1><i class="fas fa-shopping-bag"></i> My Orders</h1>
            <p>Track and manage your purchases</p>
        </div>

        <div class="order-stats">
            <div class="stat-card">
                <i class="fas fa-box"></i>
                <h3>Total Orders</h3>
                <div class="number"><?= $orderCount ?></div>
            </div>
        </div>

        <?php if ($orderCount === 0): ?>
            <div class="empty-state">
                <i class="fas fa-shopping-cart"></i>
                <h2>No Orders Yet</h2>
                <p>Start shopping and your orders will appear here!</p>
                <a href="../index.php" class="shop-btn">
                    <i class="fas fa-store"></i> Start Shopping
                </a>
            </div>
        <?php else: ?>
            <?php 
            while ($order = $result->fetch_assoc()) {
                $orderId = (int)$order['orderId'];
                
                // Fetch items for this order
                $itemsStmt = $conn->prepare("SELECT i.description AS item_name, i.image_path, i.sell_price, ol.quantity FROM orderline ol INNER JOIN item i USING (item_id) WHERE ol.orderinfo_id = ?");
                $itemsStmt->bind_param("i", $orderId);
                $itemsStmt->execute();
                $itemsResult = $itemsStmt->get_result();
                $subtotal = 0;
            ?>
                <div class="order-card">
                    <div class="order-header">
                        <div>
                            <div class="order-id">
                                <i class="fas fa-hashtag"></i> Order <?= $orderId ?>
                            </div>
                            <div class="order-date">
                                <i class="far fa-calendar"></i>
                                <?= date('F j, Y - g:i A', strtotime($order['date_placed'])) ?>
                            </div>
                        </div>
                        <div class="status-badge status-<?= strtolower($order['status']) ?>">
                            <?= htmlspecialchars($order['status']) ?>
                        </div>
                    </div>

                    <div class="order-items">
                        <?php while ($item = $itemsResult->fetch_assoc()): 
                            $itemTotal = $item['sell_price'] * $item['quantity'];
                            $subtotal += $itemTotal;
                            
                            // Get first image from JSON array
                            $firstImage = '../uploads/default.png'; // Default fallback
                            
                            $images = json_decode($item['image_path'], true);
                            if (is_array($images) && !empty($images)) {
                                // Take the first image from the array
                                $firstImage = $images[0];
                                
                                // Add ../ if path doesn't start with it
                                if (strpos($firstImage, '../') !== 0 && strpos($firstImage, 'uploads/') === 0) {
                                    $firstImage = '../' . $firstImage;
                                }
                            }
                            
                            // Verify file exists, otherwise use default
                            if (!file_exists($firstImage)) {
                                $firstImage = '../uploads/default.png';
                            }
                        ?>
                            <div class="item-row">
                                <img src="<?= htmlspecialchars($firstImage) ?>" 
                                     alt="<?= htmlspecialchars($item['item_name']) ?>" 
                                     class="item-image"
                                     onerror="this.src='../uploads/default.png'">
                                <div class="item-details">
                                    <div class="item-name">
                                        <?= htmlspecialchars($item['item_name']) ?>
                                    </div>
                                    <div class="item-quantity">
                                        <i class="fas fa-times"></i> Quantity: <?= $item['quantity'] ?>
                                    </div>
                                </div>
                                <div class="item-price">
                                    ₱<?= number_format($itemTotal, 2) ?>
                                </div>
                            </div>
                        <?php endwhile; 
                        $itemsStmt->close();
                        ?>
                    </div>

                    <div class="order-summary">
                        <div class="summary-row">
                            <span>Subtotal</span>
                            <span>₱<?= number_format($subtotal, 2) ?></span>
                        </div>
                        <div class="summary-row">
                            <span>Shipping Fee</span>
                            <span>₱<?= number_format($order['shipping'], 2) ?></span>
                        </div>
                        <div class="summary-row total">
                            <span>Total</span>
                            <span>₱<?= number_format($subtotal + $order['shipping'], 2) ?></span>
                        </div>
                    </div>

                    <div class="order-footer">
                        <div class="order-info">
                            <div class="info-item">
                                <i class="fas fa-credit-card"></i>
                                <strong>Payment:</strong> <?= ucfirst(str_replace('_', ' ', htmlspecialchars($order['payment_method']))) ?>
                            </div>
                            <div class="info-item">
                                <i class="fas fa-truck"></i>
                                <strong>Shipping:</strong> <?= ucfirst(htmlspecialchars($order['shipping_method'])) ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php } 
            $stmt->close();
            ?>
        <?php endif; ?>
    </div>

    <?php include('../includes/footer.php'); ?>
</body>
</html>