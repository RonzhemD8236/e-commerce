<?php
session_start();

// ✅ Authentication Check - Admin Only
// Authentication: Admin Only
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    $_SESSION['auth_error'] = 'Please log in as admin to access this page.';
    header("Location: ../admin/login.php");
    exit();
}


include("../includes/config.php");

// Import PHPMailer classes
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../PHPMailer/src/Exception.php';
require '../PHPMailer/src/PHPMailer.php';
require '../PHPMailer/src/SMTP.php';

// Check if form data exists
if (!isset($_POST['status']) || !isset($_POST['orderId'])) {
    $_SESSION['message'] = 'Missing required data';
    $_SESSION['message_type'] = 'danger';
    header("Location: orders.php");
    exit();
}

$status = $_POST['status'];
$orderId = intval($_POST['orderId']);

// Map lowercase values to proper case matching database ENUM
$statusMap = [
    'pending' => 'Pending',
    'processing' => 'Processing',
    'shipped' => 'Shipped',
    'delivered' => 'Delivered',
    'cancelled' => 'Canceled'
];

// Convert to proper case
$status = isset($statusMap[strtolower($status)]) ? $statusMap[strtolower($status)] : null;

// Validate status value
$validStatuses = ['Pending', 'Processing', 'Shipped', 'Delivered', 'Canceled'];
if (!$status || !in_array($status, $validStatuses)) {
    $_SESSION['message'] = 'Invalid status value: ' . htmlspecialchars($_POST['status']);
    $_SESSION['message_type'] = 'danger';
    header("Location: orders.php");
    exit();
}

// Check if order exists and get customer email
$checkSql = "SELECT o.orderinfo_id, c.email, c.fname, c.lname 
             FROM orderinfo o
             INNER JOIN customer c USING(customer_id)
             WHERE o.orderinfo_id = ?";
$checkStmt = mysqli_prepare($conn, $checkSql);

if (!$checkStmt) {
    $_SESSION['message'] = 'Database error: ' . mysqli_error($conn);
    $_SESSION['message_type'] = 'danger';
    header("Location: orders.php");
    exit();
}

mysqli_stmt_bind_param($checkStmt, "i", $orderId);
mysqli_stmt_execute($checkStmt);
$checkResult = mysqli_stmt_get_result($checkStmt);

if (mysqli_num_rows($checkResult) === 0) {
    $_SESSION['message'] = 'Order not found';
    $_SESSION['message_type'] = 'danger';
    mysqli_stmt_close($checkStmt);
    header("Location: orders.php");
    exit();
}

$orderData = mysqli_fetch_assoc($checkResult);
$customerEmail = $orderData['email'];
$customerName = $orderData['fname'] . ' ' . $orderData['lname'];
mysqli_stmt_close($checkStmt);

// Update the order status
$sql = "UPDATE orderinfo SET status = ?, updated_at = NOW() WHERE orderinfo_id = ?";
$stmt = mysqli_prepare($conn, $sql);

if (!$stmt) {
    $_SESSION['message'] = 'Database error: ' . mysqli_error($conn);
    $_SESSION['message_type'] = 'danger';
    header("Location: orders.php");
    exit();
}

mysqli_stmt_bind_param($stmt, "si", $status, $orderId);

if (mysqli_stmt_execute($stmt)) {
    // Update date_shipped for Shipped and Delivered statuses
    if ($status === 'Delivered' || $status === 'Shipped') {
        $updateShipDate = "UPDATE orderinfo 
                          SET date_shipped = CURDATE() 
                          WHERE orderinfo_id = ? AND date_shipped IS NULL";
        $shipStmt = mysqli_prepare($conn, $updateShipDate);
        
        if ($shipStmt) {
            mysqli_stmt_bind_param($shipStmt, "i", $orderId);
            mysqli_stmt_execute($shipStmt);
            mysqli_stmt_close($shipStmt);
        }
    }
    
    // Send email notification
    $emailSent = sendOrderStatusEmail($conn, $orderId, $status, $customerEmail, $customerName);
    
    if ($emailSent) {
        $_SESSION['message'] = "Order #$orderId status updated to '$status' and email sent successfully";
        $_SESSION['message_type'] = 'success';
    } else {
        $_SESSION['message'] = "Order #$orderId status updated to '$status' but email failed to send";
        $_SESSION['message_type'] = 'warning';
    }
} else {
    $_SESSION['message'] = 'Error updating order: ' . mysqli_error($conn);
    $_SESSION['message_type'] = 'danger';
}

mysqli_stmt_close($stmt);
mysqli_close($conn);

header("Location: orders.php");
exit();

/**
 * Send order status update email to customer
 */
function sendOrderStatusEmail($conn, $orderId, $status, $customerEmail, $customerName) {
    try {
        // Get order items using prepared statement
        $itemsSql = "SELECT 
                        i.description, 
                        ol.quantity, 
                        i.sell_price,
                        (ol.quantity * i.sell_price) AS item_total
                     FROM orderline ol
                     INNER JOIN item i USING(item_id)
                     WHERE ol.orderinfo_id = ?";
        
        $itemsStmt = mysqli_prepare($conn, $itemsSql);
        if (!$itemsStmt) {
            error_log("Failed to prepare items query: " . mysqli_error($conn));
            return false;
        }
        
        mysqli_stmt_bind_param($itemsStmt, "i", $orderId);
        mysqli_stmt_execute($itemsStmt);
        $itemsResult = mysqli_stmt_get_result($itemsStmt);
        
        // Build items table HTML
        $itemsHtml = '';
        $subtotal = 0;
        
        while ($item = mysqli_fetch_assoc($itemsResult)) {
            $itemTotal = $item['item_total'];
            $subtotal += $itemTotal;
            
            $itemsHtml .= "
            <tr>
                <td style='padding: 12px; border-bottom: 1px solid #eee;'>" . htmlspecialchars($item['description']) . "</td>
                <td style='padding: 12px; border-bottom: 1px solid #eee; text-align: center;'>" . intval($item['quantity']) . "</td>
                <td style='padding: 12px; border-bottom: 1px solid #eee; text-align: right;'>₱" . number_format($item['sell_price'], 2) . "</td>
                <td style='padding: 12px; border-bottom: 1px solid #eee; text-align: right;'>₱" . number_format($itemTotal, 2) . "</td>
            </tr>";
        }
        
        mysqli_stmt_close($itemsStmt);
        
        // Status-specific message
        $statusMessages = [
            'Pending' => 'Your order has been received and is pending confirmation.',
            'Processing' => 'Your order is now being processed and prepared for shipment.',
            'Shipped' => 'Great news! Your order has been shipped and is on its way.',
            'Delivered' => 'Your order has been successfully delivered. Thank you for your purchase!',
            'Canceled' => 'Your order has been canceled. If you have questions, please contact us.'
        ];
        
        $statusMessage = $statusMessages[$status] ?? 'Your order status has been updated.';
        
        // Status color
        $statusColors = [
            'Pending' => '#ffc107',
            'Processing' => '#007bff',
            'Shipped' => '#6f42c1',
            'Delivered' => '#28a745',
            'Canceled' => '#dc3545'
        ];
        
        $statusColor = $statusColors[$status] ?? '#6c757d';
        
        // Get delivery information using prepared statement
        $deliveryInfoSql = "SELECT 
                                o.created_at,
                                o.shipping,
                                c.fname,
                                c.lname,
                                c.phone,
                                c.addressline,
                                c.town,
                                c.state,
                                c.zipcode
                            FROM orderinfo o
                            INNER JOIN customer c USING(customer_id)
                            WHERE o.orderinfo_id = ?";
        
        $deliveryStmt = mysqli_prepare($conn, $deliveryInfoSql);
        if (!$deliveryStmt) {
            error_log("Failed to prepare delivery info query: " . mysqli_error($conn));
            return false;
        }
        
        mysqli_stmt_bind_param($deliveryStmt, "i", $orderId);
        mysqli_stmt_execute($deliveryStmt);
        $deliveryResult = mysqli_stmt_get_result($deliveryStmt);
        $deliveryInfo = mysqli_fetch_assoc($deliveryResult);
        mysqli_stmt_close($deliveryStmt);
        
        if (!$deliveryInfo) {
            error_log("Delivery info not found for order: $orderId");
            return false;
        }
        
        $orderDate = date('F j, Y g:i A', strtotime($deliveryInfo['created_at']));
        $shippingMethod = 'Standard Delivery';
        $paymentMethod = 'Cash on Delivery';
        $deliveryName = htmlspecialchars($deliveryInfo['fname'] . ' ' . $deliveryInfo['lname']);
        $deliveryPhone = htmlspecialchars($deliveryInfo['phone'] ?? 'N/A');
        
        // Build full address from customer table
        $addressParts = array_filter([
            $deliveryInfo['addressline'],
            $deliveryInfo['town'],
            $deliveryInfo['state'],
            $deliveryInfo['zipcode']
        ]);
        $deliveryAddress = !empty($addressParts) ? htmlspecialchars(implode(', ', $addressParts)) : 'N/A';
        
        // Calculate shipping fee
        $shippingFee = floatval($deliveryInfo['shipping'] ?? 50.00);
        
        // Email HTML template
        $emailBody = "
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
                    <h1 style='color: white; margin: 0; font-size: 28px;'>Order Status Update</h1>
                    <p style='color: rgba(255,255,255,0.9); margin: 10px 0 0 0; font-size: 16px;'>Thank you for your purchase!</p>
                </div>
                
                <!-- Order Info -->
                <div style='padding: 30px;'>
                    <div style='background: #f8f9fa; padding: 20px; border-radius: 8px; margin-bottom: 30px; border-left: 4px solid #bb86fc;'>
                        <h2 style='margin: 0 0 15px 0; color: #8b5cf6; font-size: 18px;'>Order #" . str_pad($orderId, 2, '0', STR_PAD_LEFT) . "</h2>
                        <p style='margin: 5px 0; color: #666;'><strong>Date:</strong> $orderDate</p>
                        <p style='margin: 5px 0; color: #666;'><strong>Status:</strong> <span style='color: $statusColor; font-weight: 600;'>$status</span></p>
                    </div>
                    
                    <p style='color: #666; line-height: 1.8; margin-bottom: 30px; font-size: 15px;'>$statusMessage</p>
                    
                    <!-- Customer Details -->
                    <div style='margin-bottom: 30px;'>
                        <h3 style='color: #8b5cf6; font-size: 16px; margin-bottom: 15px; border-bottom: 2px solid #e0e0e0; padding-bottom: 10px;'>Delivery Information</h3>
                        <p style='margin: 5px 0; color: #333;'><strong>Name:</strong> $deliveryName</p>
                        <p style='margin: 5px 0; color: #333;'><strong>Phone:</strong> $deliveryPhone</p>
                        <p style='margin: 5px 0; color: #333;'><strong>Address:</strong> $deliveryAddress</p>
                        <p style='margin: 5px 0; color: #333;'><strong>Shipping:</strong> $shippingMethod</p>
                        <p style='margin: 5px 0; color: #333;'><strong>Payment:</strong> $paymentMethod</p>
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
                            $itemsHtml
                        </tbody>
                    </table>
                    
                    <!-- Order Summary -->
                    <div style='background: #f8f9fa; padding: 20px; border-radius: 8px;'>
                        <div style='display: flex; justify-content: space-between; margin-bottom: 10px;'>
                            <span style='color: #666;'>Subtotal:</span>
                            <span style='font-weight: 600; color: #333;'>₱" . number_format($subtotal, 2) . "</span>
                        </div>
                        <div style='display: flex; justify-content: space-between; margin-bottom: 15px; padding-bottom: 15px; border-bottom: 2px solid #e0e0e0;'>
                            <span style='color: #666;'>Shipping Fee:</span>
                            <span style='font-weight: 600; color: #333;'>₱" . number_format($shippingFee, 2) . "</span>
                        </div>
                        <div style='display: flex; justify-content: space-between;'>
                            <span style='font-size: 18px; font-weight: 600; color: #8b5cf6;'>Grand Total:</span>
                            <span style='font-size: 20px; font-weight: 700; color: #8b5cf6;'>₱" . number_format($subtotal + $shippingFee, 2) . "</span>
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
                    <p style='margin: 0; color: #999; font-size: 13px;'>© 2025 Lensify Store. All rights reserved.</p>
                </div>
            </div>
        </body>
        </html>";
        
        // Configure PHPMailer
        $mail = new PHPMailer(true);
        
        $mail->isSMTP();
        $mail->Host = 'sandbox.smtp.mailtrap.io';
        $mail->SMTPAuth = true;
        $mail->Username = '83b005d0a437d4';
        $mail->Password = 'eaac622c51900b';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 2525;
        
        $mail->setFrom('noreply@lensify.com', 'Lensify');
        $mail->addAddress($customerEmail, $customerName);
        
        $mail->isHTML(true);
        $mail->Subject = "Order #" . str_pad($orderId, 4, '0', STR_PAD_LEFT) . " - Status Updated to $status";
        $mail->Body = $emailBody;
        $mail->AltBody = "Your order #$orderId status has been updated to $status. Subtotal: ₱" . number_format($subtotal, 2) . " | Shipping: ₱" . number_format($shippingFee, 2) . " | Grand Total: ₱" . number_format($subtotal + $shippingFee, 2);
        
        $mail->send();
        return true;
        
    } catch (Exception $e) {
        error_log("Email sending failed: {$mail->ErrorInfo}");
        return false;
    }
}
?>