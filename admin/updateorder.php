<?php
session_start();
include("../includes/config.php");

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

// Validate status value (matching your UPDATED database ENUM)
$validStatuses = ['Pending', 'Processing', 'Shipped', 'Delivered', 'Canceled'];
if (!$status || !in_array($status, $validStatuses)) {
    $_SESSION['message'] = 'Invalid status value: ' . htmlspecialchars($_POST['status']);
    $_SESSION['message_type'] = 'danger';
    header("Location: orders.php");
    exit();
}

// Check if order exists
$checkSql = "SELECT orderinfo_id FROM orderinfo WHERE orderinfo_id = ?";
$checkStmt = mysqli_prepare($conn, $checkSql);
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
mysqli_stmt_close($checkStmt);

// Update the order status
$sql = "UPDATE orderinfo SET status = ?, updated_at = NOW() WHERE orderinfo_id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "si", $status, $orderId);

if (mysqli_stmt_execute($stmt)) {
    // If status is changed to "Delivered", update the date_shipped if not already set
    if ($status === 'Delivered') {
        $updateShipDate = "UPDATE orderinfo 
                          SET date_shipped = CURDATE() 
                          WHERE orderinfo_id = ? AND date_shipped IS NULL";
        $shipStmt = mysqli_prepare($conn, $updateShipDate);
        mysqli_stmt_bind_param($shipStmt, "i", $orderId);
        mysqli_stmt_execute($shipStmt);
        mysqli_stmt_close($shipStmt);
    }
    
    // If status is changed to "Shipped", also update the date_shipped
    if ($status === 'Shipped') {
        $updateShipDate = "UPDATE orderinfo 
                          SET date_shipped = CURDATE() 
                          WHERE orderinfo_id = ? AND date_shipped IS NULL";
        $shipStmt = mysqli_prepare($conn, $updateShipDate);
        mysqli_stmt_bind_param($shipStmt, "i", $orderId);
        mysqli_stmt_execute($shipStmt);
        mysqli_stmt_close($shipStmt);
    }
    
    $_SESSION['message'] = "Order #$orderId status updated to '$status' successfully";
    $_SESSION['message_type'] = 'success';
} else {
    $_SESSION['message'] = 'Error updating order: ' . mysqli_error($conn);
    $_SESSION['message_type'] = 'danger';
}

mysqli_stmt_close($stmt);
mysqli_close($conn);

header("Location: orders.php");
exit();
?>