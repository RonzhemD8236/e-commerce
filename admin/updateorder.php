<?php
session_start();

// ✅ Authentication Check
// Authentication: Admin Only
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    $_SESSION['auth_error'] = 'Please log in as admin to access this page.';
    header("Location: ../admin/login.php");
    exit();
}

// ✅ Validate orderinfo_id parameter BEFORE including header (so we can redirect)
$orderId = 0;

// Check GET parameters
if (isset($_GET['orderinfo_id']) && !empty($_GET['orderinfo_id'])) {
    $orderId = (int)$_GET['orderinfo_id'];
} elseif (isset($_GET['orderId']) && !empty($_GET['orderId'])) {
    $orderId = (int)$_GET['orderId'];
} elseif (isset($_GET['id']) && !empty($_GET['id'])) {
    $orderId = (int)$_GET['id'];
} elseif (isset($_SESSION['orderId']) && !empty($_SESSION['orderId'])) {
    $orderId = (int)$_SESSION['orderId'];
}

// Also check POST in case it's a form submission
if ($orderId === 0 && isset($_POST['orderinfo_id']) && !empty($_POST['orderinfo_id'])) {
    $orderId = (int)$_POST['orderinfo_id'];
} elseif ($orderId === 0 && isset($_POST['orderId']) && !empty($_POST['orderId'])) {
    $orderId = (int)$_POST['orderId'];
}

if ($orderId === 0 || $orderId < 1) {
    // User-friendly error message with redirect option
    $_SESSION['message'] = 'Invalid or missing order ID. Please select an order from the orders list.';
    $_SESSION['message_type'] = 'danger';
    header("Location: orders.php");
    exit();
}

// Now include config (but not header yet, in case we need to redirect)
include('../includes/config.php');

// Include PHPMailer files (must be before POST processing so function can use them)
require '../phpmailer/src/Exception.php';
require '../phpmailer/src/PHPMailer.php';
require '../phpmailer/src/SMTP.php';

// Import PHPMailer classes after requiring the files
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// ✅ PROCESS POST REQUEST - Update Order Status
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['status']) && isset($_POST['orderId'])) {
    $status = $_POST['status'];
    $orderId = (int)$_POST['orderId'];
    
    // Map lowercase values to proper case matching database ENUM
    $statusMap = [
        'pending' => 'Pending',
        'processing' => 'Processing',
        'shipped' => 'Shipped',
        'delivered' => 'Delivered',
        'cancelled' => 'Canceled',
        'canceled' => 'Canceled'
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
        
        // ✅ ALWAYS send email notification to customer for EVERY status update
        // This ensures customers are notified whenever their order status changes
        $emailSent = sendOrderStatusEmail($conn, $orderId, $status, $customerEmail, $customerName);
        
        if ($emailSent) {
            // ✅ GREEN: Both status update and email sent successfully
            $_SESSION['message'] = "✅ Order #$orderId status updated to '$status' and email sent successfully to customer";
            $_SESSION['message_type'] = 'success';
        } else {
            // ⚠️ YELLOW: Status updated but email failed
            $_SESSION['message'] = "⚠️ Order #$orderId status updated to '$status' but email failed to send. Please check email configuration.";
            $_SESSION['message_type'] = 'warning';
            // Log the error for debugging
            error_log("Failed to send order status email for order #$orderId to $customerEmail");
        }
    } else {
        // ❌ RED: Both status update and email failed
        $_SESSION['message'] = '❌ Failed to update order status and email notification: ' . mysqli_error($conn);
        $_SESSION['message_type'] = 'danger';
    }
    
    mysqli_stmt_close($stmt);
    mysqli_close($conn);
    
    header("Location: orders.php");
    exit();
}

// Include header only if we're displaying the page (not redirecting)
include('../admin/header.php');

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
        
        // Use current real-time date
        date_default_timezone_set('Asia/Manila');
        $orderDate = date('F j, Y g:i A', time());
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
                    <p style='margin: 0; color: #999; font-size: 13px;'>© " . date('Y') . " Lensify Store. All rights reserved.</p>
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
        
        // Set message date to current real-time
        $mail->MessageDate = date('D, j M Y H:i:s O');
        
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

$_SESSION['orderId'] = $orderId;

// ✅ Query using order_transaction_details view (PREPARED STATEMENT)
$sql = "SELECT * FROM order_transaction_details WHERE orderinfo_id = ?";

$stmt = mysqli_prepare($conn, $sql);
if (!$stmt) {
    // If prepared statement fails, try alternative query without view
    error_log("Prepared statement failed: " . mysqli_error($conn));
    
    // Fallback: Use JOIN query directly (in case view doesn't exist)
    $orderIdEscaped = (int)$orderId;
    $sql = "SELECT 
                o.orderinfo_id,
                o.date_placed,
                o.date_shipped,
                o.shipping,
                o.status AS order_status,
                o.payment_method,
                o.shipping_method,
                c.customer_id,
                CONCAT(c.fname, ' ', c.lname) AS customer_name,
                c.email AS customer_email,
                c.phone AS customer_phone,
                CONCAT_WS(', ', c.addressline, c.town, c.state, c.country, c.zipcode) AS full_address,
                i.item_id,
                i.description AS item_name,
                i.short_description AS item_short_desc,
                i.category AS item_category,
                i.sell_price AS item_price,
                ol.quantity,
                ol.quantity * i.sell_price AS subtotal,
                s.quantity AS available_stock,
                u.username,
                u.role AS user_role,
                u.profile_img,
                c.image_path AS customer_image_path
            FROM orderinfo o
            INNER JOIN customer c ON o.customer_id = c.customer_id
            INNER JOIN orderline ol ON o.orderinfo_id = ol.orderinfo_id
            INNER JOIN item i ON ol.item_id = i.item_id
            LEFT JOIN stock s ON i.item_id = s.item_id
            LEFT JOIN users u ON c.user_id = u.id
            WHERE o.orderinfo_id = $orderIdEscaped";
    
    $result = mysqli_query($conn, $sql);
    
    if (!$result) {
        die("Error: Database query failed. " . mysqli_error($conn));
    }
} else {
    mysqli_stmt_bind_param($stmt, "i", $orderId);
    if (!mysqli_stmt_execute($stmt)) {
        mysqli_stmt_close($stmt);
        die("Error: Failed to execute statement. " . mysqli_error($conn));
    }
    $result = mysqli_stmt_get_result($stmt);
    
    if (!$result) {
        mysqli_stmt_close($stmt);
        die("Error: Failed to get result set. " . mysqli_error($conn));
    }
}

// Get all order items
$orderItems = [];
$customer = null;
while ($row = mysqli_fetch_assoc($result)) {
    if ($customer === null) {
        // Store customer and order info from first row
        $customer = $row;
    }
    $orderItems[] = $row;
}

// Close statement if it was created
if (isset($stmt) && $stmt !== false && is_object($stmt)) {
    mysqli_stmt_close($stmt);
}

if (!$customer) {
    die("Error: Order not found.");
}

// ✅ Set profile picture path - check both profile_img and customer_image_path
$profilePicture = "../uploads/default-profile.png"; // Default

if (!empty($customer['profile_img']) && file_exists("../uploads/" . $customer['profile_img'])) {
    $profilePicture = "../uploads/" . $customer['profile_img'];
} elseif (!empty($customer['customer_image_path']) && file_exists("../uploads/" . $customer['customer_image_path'])) {
    $profilePicture = "../uploads/" . $customer['customer_image_path'];
}

// ✅ Calculate status badge color
$statusColors = [
    'Processing' => 'warning',
    'Delivered' => 'success',
    'Canceled' => 'danger',
    'Pending' => 'secondary',
    'Shipped' => 'info'
];
$badgeColor = $statusColors[$customer['order_status']] ?? 'secondary';
?>

<style>
    .order-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        padding: 2rem;
        border-radius: 10px 10px 0 0;
        color: white;
        margin-bottom: 0;
    }
    
    .order-card {
        border: none;
        border-radius: 10px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.07);
        overflow: hidden;
        margin-bottom: 1.5rem;
    }
    
    .profile-image {
        width: 90px;
        height: 90px;
        object-fit: cover;
        border: 4px solid #ffffff;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }
    
    .info-section {
        background: #ffffff;
        padding: 2rem;
    }
    
    .info-label {
        color: #6c757d;
        font-size: 0.875rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 0.25rem;
    }
    
    .info-value {
        color: #212529;
        font-size: 1rem;
        margin-bottom: 1rem;
    }
    
    .section-title {
        font-size: 1.125rem;
        font-weight: 600;
        color: #495057;
        margin-bottom: 1.5rem;
        padding-bottom: 0.5rem;
        border-bottom: 2px solid #e9ecef;
    }
    
    .items-table {
        background: white;
    }
    
    .items-table thead {
        background: #f8f9fa;
    }
    
    .items-table thead th {
        font-weight: 600;
        color: #495057;
        text-transform: uppercase;
        font-size: 0.813rem;
        letter-spacing: 0.5px;
        padding: 1rem;
        border: none;
    }
    
    .items-table tbody td {
        padding: 1rem;
        vertical-align: middle;
        border-color: #e9ecef;
    }
    
    .items-table tfoot {
        background: #f8f9fa;
        font-weight: 600;
    }
    
    .items-table tfoot td {
        padding: 1.25rem 1rem;
        font-size: 1.125rem;
    }
    
    .status-badge {
        padding: 0.5rem 1.25rem;
        font-size: 0.875rem;
        font-weight: 600;
        border-radius: 50px;
        letter-spacing: 0.5px;
    }
    
    .btn-back {
        background: #6c757d;
        color: white;
        padding: 0.625rem 1.5rem;
        border-radius: 6px;
        font-weight: 500;
        transition: all 0.3s ease;
        border: none;
    }
    
    .btn-back:hover {
        background: #5a6268;
        color: white;
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
    }
    
    .info-badge {
        display: inline-block;
        padding: 0.25rem 0.75rem;
        background: #e7f3ff;
        color: #0066cc;
        border-radius: 4px;
        font-size: 0.875rem;
        font-weight: 500;
    }
</style>

<div class="container my-5">
    <!-- Order Header -->
    <div class="order-card">
        <div class="order-header d-flex justify-content-between align-items-center">
            <div>
                <h2 class="mb-1">Order #<?= str_pad($customer['orderinfo_id'], 4, '0', STR_PAD_LEFT) ?></h2>
                <p class="mb-0 opacity-75">Placed on <?= date('F j, Y', strtotime($customer['date_placed'])) ?></p>
            </div>
            <span class="status-badge bg-<?= $badgeColor ?>"><?= htmlspecialchars($customer['order_status']) ?></span>
        </div>
        
        <div class="info-section">
            <div class="row">
                <!-- Customer Information -->
                <div class="col-lg-6 mb-4 mb-lg-0">
                    <h5 class="section-title">Customer Information</h5>
                    <div class="d-flex align-items-start">
                        <img src="<?= htmlspecialchars($profilePicture) ?>" 
                             alt="Customer Profile" 
                             class="rounded-circle profile-image me-3"
                             onerror="this.src='../uploads/default-profile.png'">
                        <div class="flex-grow-1">
                            <div class="info-label">Full Name</div>
                            <div class="info-value">
                                <?= htmlspecialchars($customer['customer_name']) ?>
                                <?php if (!empty($customer['username'])): ?>
                                    <span class="info-badge">@<?= htmlspecialchars($customer['username']) ?></span>
                                <?php endif; ?>
                            </div>
                            
                            <div class="info-label">Email Address</div>
                            <div class="info-value"><?= htmlspecialchars($customer['customer_email']) ?></div>
                            
                            <div class="info-label">Contact Number</div>
                            <div class="info-value"><?= htmlspecialchars($customer['customer_phone']) ?></div>
                            
                            <div class="info-label">Shipping Address</div>
                            <div class="info-value">
                                <?= nl2br(htmlspecialchars($customer['full_address'])) ?>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Order Information -->
                <div class="col-lg-6">
                    <h5 class="section-title">Order Information</h5>
                    
                    <div class="info-label">Order ID</div>
                    <div class="info-value">#<?= str_pad($customer['orderinfo_id'], 4, '0', STR_PAD_LEFT) ?></div>
                    
                    <div class="info-label">Date Placed</div>
                    <div class="info-value"><?= date('F j, Y', strtotime($customer['date_placed'])) ?></div>
                    
                    <div class="info-label">Date Shipped</div>
                    <div class="info-value">
                        <?= $customer['date_shipped'] ? date('F j, Y', strtotime($customer['date_shipped'])) : '<span class="text-muted">Not yet shipped</span>' ?>
                    </div>
                    
                    <div class="info-label">Payment Method</div>
                    <div class="info-value"><?= htmlspecialchars(ucwords(str_replace('_', ' ', $customer['payment_method']))) ?></div>
                    
                    <div class="info-label">Shipping Method</div>
                    <div class="info-value"><?= htmlspecialchars(ucwords($customer['shipping_method'])) ?></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Order Items -->
    <div class="order-card">
        <div class="info-section">
            <h5 class="section-title">Order Items</h5>
            
            <div class="table-responsive">
                <table class="table items-table mb-0">
                    <thead>
                        <tr>
                            <th>Item Description</th>
                            <th>Category</th>
                            <th class="text-center" style="width: 100px;">Quantity</th>
                            <th class="text-end" style="width: 130px;">Unit Price</th>
                            <th class="text-end" style="width: 130px;">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $itemsSubtotal = 0;
                        foreach ($orderItems as $item) {
                            $itemsSubtotal += $item['subtotal'];
                            echo "<tr>";
                            echo "<td>";
                            echo "<strong>" . htmlspecialchars($item['item_name']) . "</strong>";
                            if (!empty($item['item_short_desc'])) {
                                echo "<br><small class='text-muted'>" . htmlspecialchars($item['item_short_desc']) . "</small>";
                            }
                            echo "</td>";
                            echo "<td><span class='badge bg-light text-dark'>" . htmlspecialchars($item['item_category']) . "</span></td>";
                            echo "<td class='text-center'>";
                            echo $item['quantity'];
                            if (!empty($item['available_stock'])) {
                                echo "<br><small class='text-muted'>Stock: " . $item['available_stock'] . "</small>";
                            }
                            echo "</td>";
                            echo "<td class='text-end'>₱" . number_format($item['item_price'], 2) . "</td>";
                            echo "<td class='text-end'><strong>₱" . number_format($item['subtotal'], 2) . "</strong></td>";
                            echo "</tr>";
                        }
                        ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="4" class="text-end">Subtotal:</td>
                            <td class="text-end">₱<?= number_format($itemsSubtotal, 2) ?></td>
                        </tr>
                        <tr>
                            <td colspan="4" class="text-end">Shipping Fee:</td>
                            <td class="text-end">₱<?= number_format($customer['shipping'], 2) ?></td>
                        </tr>
                        <tr>
                            <td colspan="4" class="text-end"><strong>Grand Total:</strong></td>
                            <td class="text-end"><strong>₱<?= number_format($itemsSubtotal + $customer['shipping'], 2) ?></strong></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="mt-4">
        <a href="orders.php" class="btn btn-back">
            <i class="bi bi-arrow-left me-2"></i>Back to Orders
        </a>
    </div>
</div>

<?php
include('../includes/footer.php');
?>