<?php
session_start();
include('../includes/config.php');

// Import PHPMailer classes (Manual Installation)
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

// Manual PHPMailer include - adjust path if needed
require '../phpmailer/src/Exception.php';
require '../phpmailer/src/PHPMailer.php';
require '../phpmailer/src/SMTP.php';

// ---------- LOGIN CHECK ----------
if (!isset($_SESSION['user_id'])) {
    die("❌ You must be logged in to checkout.");
}

// ---------- CART CHECK ----------
if (!isset($_SESSION["cart_products"]) || count($_SESSION["cart_products"]) === 0) {
    die("❌ Your cart is empty.");
}

// ---------- GET POST DATA ----------
$shipping_method = $_POST['shipping_method'] ?? 'delivery';
$payment_method  = $_POST['payment_method'] ?? 'cod';

$card_number = $_POST['card_number'] ?? null;
$card_name   = $_POST['card_name'] ?? null;
$card_expiry = $_POST['card_expiry'] ?? null;
$card_cvv    = $_POST['card_cvv'] ?? null;

$ewallet_number = $_POST['ewallet_number'] ?? null;
$ewallet_name   = $_POST['ewallet_name'] ?? null;
$ewallet_id     = $_POST['ewallet_id'] ?? null;

// Shipping cost
$shipping_cost = ($shipping_method === 'delivery') ? 50 : 0;

try {
    // ---------- TRANSACTION START ----------
    mysqli_begin_transaction($conn);

    // ---------- GET CUSTOMER INFO ----------
    $user_id = (int)$_SESSION['user_id'];
    $result = $conn->query("SELECT customer_id, fname, lname, email, phone, addressline FROM customer WHERE user_id = $user_id LIMIT 1");
    if (!$result || $result->num_rows === 0) {
        throw new Exception("Customer not found.");
    }
    $customer = $result->fetch_assoc();
    $customer_id = (int)$customer['customer_id'];
    $customer_email = $customer['email'];
    $customer_name = $customer['fname'] . ' ' . $customer['lname'];
    $customer_phone = $customer['phone'];
    $customer_address = $customer['addressline'];

    // ---------- INSERT INTO orderinfo ----------
    $shipping_method_safe = $conn->real_escape_string($shipping_method);
    $payment_method_safe  = $conn->real_escape_string($payment_method);
    $shipping_cost_safe   = (float)$shipping_cost;

    $sql_orderinfo = "
        INSERT INTO orderinfo(customer_id, date_placed, shipping, shipping_method, payment_method, status)
        VALUES ($customer_id, NOW(), $shipping_cost_safe, '$shipping_method_safe', '$payment_method_safe', 'Pending')
    ";
    if (!$conn->query($sql_orderinfo)) {
        throw new Exception("Order insert failed: " . $conn->error);
    }
    $orderinfo_id = $conn->insert_id;

    // Calculate totals for email
    $merchandise_total = 0;
    $order_items_html = '';

    // ---------- INSERT ORDER LINES AND UPDATE STOCK ----------
    foreach ($_SESSION["cart_products"] as $cart_itm) {
        $item_id = (int)($cart_itm['item_id'] ?? 0);
        $qty     = (int)($cart_itm['item_qty'] ?? 0);
        $price   = (float)($cart_itm['item_price'] ?? 0);
        $item_name = htmlspecialchars($cart_itm['item_name'] ?? 'Unknown Item');

        if ($item_id === 0 || $qty <= 0) {
            throw new Exception("Invalid cart item: {$cart_itm['item_name']}");
        }

        $subtotal = $price * $qty;
        $merchandise_total += $subtotal;

        // Build email content for this item
        $order_items_html .= "
        <tr>
            <td style='padding: 15px; border-bottom: 1px solid #e0e0e0; color: #333;'>{$item_name}</td>
            <td style='padding: 15px; border-bottom: 1px solid #e0e0e0; text-align: center; color: #666;'>{$qty}</td>
            <td style='padding: 15px; border-bottom: 1px solid #e0e0e0; text-align: right; color: #666;'>₱" . number_format($price, 2) . "</td>
            <td style='padding: 15px; border-bottom: 1px solid #e0e0e0; text-align: right; font-weight: 600; color: #333;'>₱" . number_format($subtotal, 2) . "</td>
        </tr>";

        // Insert order line
        $sql_orderline = "
            INSERT INTO orderline(orderinfo_id, item_id, quantity)
            VALUES ($orderinfo_id, $item_id, $qty)
        ";
        if (!$conn->query($sql_orderline)) {
            throw new Exception("Order line insert failed for item_id: $item_id - " . $conn->error);
        }

        // Update stock
        $sql_stock = "UPDATE stock SET quantity = quantity - $qty WHERE item_id = $item_id";
        if (!$conn->query($sql_stock)) {
            throw new Exception("Stock update failed for item_id: $item_id - " . $conn->error);
        }
    }

    // Calculate grand total
    $grand_total = $merchandise_total + $shipping_cost;

    // ---------- COMMIT ----------
    mysqli_commit($conn);

    // ---------- SEND EMAIL RECEIPT ----------
    $mail = new PHPMailer(true);
    $mail->CharSet = 'UTF-8'; // ✅ Add this line
    
    try {
        // Mailtrap SMTP Configuration
        $mail->isSMTP();
        $mail->Host       = 'sandbox.smtp.mailtrap.io'; // Mailtrap SMTP server
        $mail->SMTPAuth   = true;
        $mail->Username   = '83b005d0a437d4'; // Replace with your Mailtrap username
        $mail->Password   = 'eaac622c51900b'; // Replace with your Mailtrap password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 2525; // Mailtrap port

        // Recipients
        $mail->setFrom('noreply@lensify.com', 'Lensify Store');
        $mail->addAddress($customer_email, $customer_name);

        // Content
        $mail->isHTML(true);
        $mail->Subject = "Order Confirmation #$orderinfo_id - Lensify";
        
        // Payment method display
        $payment_display = ucfirst(str_replace('_', ' ', $payment_method));
        if ($payment_method === 'cod') {
            $payment_display = 'Cash on Delivery';
        }
        
        $shipping_display = $shipping_method === 'delivery' ? 'Standard Delivery' : 'Store Pick-up';
        
        // Email HTML Template
        $mail->Body = "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        </head>
        <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; background-color: #f4f4f4;'>
            <div style='max-width: 650px; margin: 30px auto; background: white; border-radius: 10px; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.1);'>
                <!-- Header -->
                <div style='background: linear-gradient(135deg, #8b5cf6 0%, #bb86fc 100%); padding: 40px 30px; text-align: center;'>
                    <h1 style='color: white; margin: 0; font-size: 28px;'>Order Confirmation</h1>
                    <p style='color: rgba(255,255,255,0.9); margin: 10px 0 0 0; font-size: 16px;'>Thank you for your purchase!</p>
                </div>
                
                <!-- Order Info -->
                <div style='padding: 30px;'>
                    <div style='background: #f8f9fa; padding: 20px; border-radius: 8px; margin-bottom: 30px; border-left: 4px solid #bb86fc;'>
                        <h2 style='margin: 0 0 15px 0; color: #8b5cf6; font-size: 18px;'>Order #$orderinfo_id</h2>
                        <p style='margin: 5px 0; color: #666;'><strong>Date:</strong> " . date('F j, Y g:i A') . "</p>
                        <p style='margin: 5px 0; color: #666;'><strong>Status:</strong> <span style='color: #f59e0b; font-weight: 600;'>Pending</span></p>
                    </div>
                    
                    <!-- Customer Details -->
                    <div style='margin-bottom: 30px;'>
                        <h3 style='color: #8b5cf6; font-size: 16px; margin-bottom: 15px; border-bottom: 2px solid #e0e0e0; padding-bottom: 10px;'>Delivery Information</h3>
                        <p style='margin: 5px 0; color: #333;'><strong>Name:</strong> {$customer_name}</p>
                        <p style='margin: 5px 0; color: #333;'><strong>Phone:</strong> {$customer_phone}</p>
                        <p style='margin: 5px 0; color: #333;'><strong>Address:</strong> {$customer_address}</p>
                        <p style='margin: 5px 0; color: #333;'><strong>Shipping:</strong> {$shipping_display}</p>
                        <p style='margin: 5px 0; color: #333;'><strong>Payment:</strong> {$payment_display}</p>
                    </div>
                    
                    <!-- Order Items -->
                    <h3 style='color: #8b5cf6; font-size: 16px; margin-bottom: 15px; border-bottom: 2px solid #e0e0e0; padding-bottom: 10px;'>Order Items</h3>
                    <table style='width: 100%; border-collapse: collapse; margin-bottom: 30px;'>
                        <thead>
                            <tr style='background: #f8f9fa;'>

                                <th style='padding: 15px; text-align: left; font-weight: 600; color: #8b5cf6; border-bottom: 2px solid #e0e0e0;'>Product</th>
                                <th style='padding: 15px; text-align: center; font-weight: 600; color: #8b5cf6; border-bottom: 2px solid #e0e0e0;'>Qty</th>
                                <th style='padding: 15px; text-align: right; font-weight: 600; color: #8b5cf6; border-bottom: 2px solid #e0e0e0;'>Price</th>
                                <th style='padding: 15px; text-align: right; font-weight: 600; color: #8b5cf6; border-bottom: 2px solid #e0e0e0;'>Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            {$order_items_html}
                        </tbody>
                    </table>
                    
                    <!-- Order Summary -->

                    <div style='background: #f8f9fa; padding: 20px; border-radius: 8px;'>
                        <div style='display: flex; justify-content: space-between; margin-bottom: 10px;'>
                            <span style='color: #666;'>Subtotal:</span>
                            <span style='font-weight: 600; color: #333;'>₱" . number_format($merchandise_total, 2) . "</span>
                        </div>
                        <div style='display: flex; justify-content: space-between; margin-bottom: 15px; padding-bottom: 15px; border-bottom: 2px solid #e0e0e0;'>
                            <span style='color: #666;'>Shipping Fee:</span>
                            <span style='font-weight: 600; color: #333;'>₱" . number_format($shipping_cost, 2) . "</span>
                        </div>
                        <div style='display: flex; justify-content: space-between;'>
                            <span style='font-size: 18px; font-weight: 600; color: #8b5cf6;'>Grand Total:</span>
                            <span style='font-size: 20px; font-weight: 700; color: #8b5cf6;'>₱" . number_format($grand_total, 2) . "</span>
                        </div>
                    </div>
                    
                    <!-- Footer Message -->
                    <div style='margin-top: 30px; padding: 20px; background: #f0f0f0; border-radius: 8px; text-align: center;'>
                        <p style='margin: 0; color: #666; font-size: 14px;'>We'll send you a notification when your order ships.</p>
                        <p style='margin: 10px 0 0 0; color: #666; font-size: 14px;'>If you have any questions, please contact our support team.</p>
                    </div>
                </div>
                
                <!-- Footer -->
                <div style='background: #2a2a2a; padding: 20px; text-align: center;'>
                    <p style='margin: 0; color: #999; font-size: 13px;'>© " . date('Y') . " Lensify Store. All rights reserved.</p>
                </div>
            </div>
        </body>
        </html>";

        // Plain text alternative
        $mail->AltBody = "Order Confirmation #$orderinfo_id\n\n" .
                         "Thank you for your purchase, {$customer_name}!\n\n" .
                         "Order Date: " . date('F j, Y g:i A') . "\n" .
                         "Total Amount: ₱" . number_format($grand_total, 2) . "\n\n" .
                         "We'll send you updates about your order.";

        $mail->send();
        $email_sent = true;
    } catch (Exception $e) {
        // Log email error but don't fail the order
        error_log("Email could not be sent. Mailer Error: {$mail->ErrorInfo}");
        $email_sent = false;
    }

    // Clear cart
    unset($_SESSION["cart_products"]);

    // ---------- CONFIRMATION PAGE ----------
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Order Confirmed - Lensify</title>
        <style>
            body {
                font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Arial, sans-serif;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                margin: 0;
                padding: 20px;
                min-height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
            }
            .confirmation-container {
                background: white;
                max-width: 600px;
                padding: 50px 40px;
                border-radius: 20px;
                box-shadow: 0 20px 60px rgba(0,0,0,0.3);
                text-align: center;
            }
            .success-icon {
                width: 80px;
                height: 80px;
                background: linear-gradient(135deg, #8b5cf6 0%, #bb86fc 100%);
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                margin: 0 auto 30px;
                animation: scaleIn 0.5s ease;
            }
            .success-icon::after {
                content: '✓';
                color: white;
                font-size: 48px;
                font-weight: bold;
            }
            h1 {
                color: #8b5cf6;
                font-size: 32px;
                margin: 0 0 15px 0;
            }
            p {
                color: #666;
                font-size: 16px;
                line-height: 1.6;
                margin: 0 0 30px 0;
            }
            .order-number {
                background: #f8f9fa;
                padding: 20px;
                border-radius: 12px;
                margin: 30px 0;
                border-left: 4px solid #8b5cf6;
            }
            .order-number strong {
                color: #8b5cf6;
                font-size: 20px;
            }
            .email-status {
                background: #d1fae5;
                color: #065f46;
                padding: 15px;
                border-radius: 8px;
                margin: 20px 0;
                font-size: 14px;
            }
            .email-status.warning {
                background: #fef3c7;
                color: #92400e;
            }
            .btn {
                display: inline-block;
                background: linear-gradient(135deg, #8b5cf6 0%, #bb86fc 100%);
                color: white;
                padding: 15px 40px;
                border-radius: 10px;
                text-decoration: none;
                font-weight: 600;
                margin: 10px;
                transition: transform 0.3s ease, box-shadow 0.3s ease;
            }
            .btn:hover {
                transform: translateY(-2px);
                box-shadow: 0 10px 25px rgba(139, 92, 246, 0.4);
            }
            .btn-secondary {
                background: white;
                color: #8b5cf6;
                border: 2px solid #8b5cf6;
            }
            @keyframes scaleIn {
                from {
                    transform: scale(0);
                }
                to {
                    transform: scale(1);
                }
            }
        </style>
    </head>
    <body>
        <div class="confirmation-container">
            <div class="success-icon"></div>
            <h1>Order Confirmed!</h1>
            <p>Thank you for your purchase. Your order has been successfully placed and is being processed.</p>
            
            <div class="order-number">
                <p style="margin: 0;"><strong>Order #<?= $orderinfo_id ?></strong></p>
                <p style="margin: 10px 0 0 0; font-size: 14px;">Total: ₱<?= number_format($grand_total, 2) ?></p>
            </div>
            
            <?php if ($email_sent): ?>
                <div class="email-status">
                    ✉️ A confirmation email has been sent to <strong><?= htmlspecialchars($customer_email) ?></strong>
                </div>
            <?php else: ?>
                <div class="email-status warning">
                    ⚠️ Your order was successful, but we couldn't send the confirmation email. Please check your order history.
                </div>
            <?php endif; ?>
            
            <div style="margin-top: 40px;">
                <a href="/lensify/e-commerce2/index.php" class="btn">Continue Shopping</a>
                <a href="/lensify/e-commerce2/customer/orders.php" class="btn btn-secondary">View Orders</a>
            </div>
        </div>
    </body>
    </html>
    <?php

} catch (Exception $e) {
    mysqli_rollback($conn);
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Order Error</title>
        <style>
            body {
                font-family: Arial, sans-serif;
                background: #f44336;
                padding: 50px 20px;
                text-align: center;
            }
            .error-box {
                background: white;
                max-width: 500px;
                margin: 0 auto;
                padding: 40px;
                border-radius: 10px;
                box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            }
            h2 {
                color: #f44336;
            }
        </style>
    </head>
    <body>
        <div class="error-box">
            <h2>❌ Error Processing Order</h2>
            <p><?= htmlspecialchars($e->getMessage()) ?></p>
            <a href="/lensify/e-commerce2/customer/cart.php" style="color: #f44336;">Return to Cart</a>
        </div>
    </body>
    </html>
    <?php
}
?>