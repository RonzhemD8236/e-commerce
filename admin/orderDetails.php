<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    $_SESSION['auth_error'] = 'Please log in as admin to access this page.';
    header("Location: ../admin/login.php");
    exit();
}

$orderId = 0;
if (isset($_GET['orderinfo_id']) && !empty($_GET['orderinfo_id'])) {
    $orderId = (int)$_GET['orderinfo_id'];
} elseif (isset($_GET['orderId']) && !empty($_GET['orderId'])) {
    $orderId = (int)$_GET['orderId'];
} elseif (isset($_GET['id']) && !empty($_GET['id'])) {
    $orderId = (int)$_GET['id'];
} elseif (isset($_SESSION['orderId']) && !empty($_SESSION['orderId'])) {
    $orderId = (int)$_SESSION['orderId'];
}

if ($orderId === 0 && isset($_POST['orderinfo_id']) && !empty($_POST['orderinfo_id'])) {
    $orderId = (int)$_POST['orderinfo_id'];
} elseif ($orderId === 0 && isset($_POST['orderId']) && !empty($_POST['orderId'])) {
    $orderId = (int)$_POST['orderId'];
}

if ($orderId === 0 || $orderId < 1) {
    $_SESSION['message'] = 'Invalid or missing order ID. Please select an order from the orders list.';
    $_SESSION['message_type'] = 'danger';
    header("Location: orders.php");
    exit();
}

include('../admin/header.php');
include('../includes/config.php');

$_SESSION['orderId'] = $orderId;

$sql = "SELECT * FROM order_transaction_details WHERE orderinfo_id = ?";

$stmt = mysqli_prepare($conn, $sql);
if (!$stmt) {
    error_log("Prepared statement failed: " . mysqli_error($conn));
    
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

$orderItems = [];
$customer = null;
while ($row = mysqli_fetch_assoc($result)) {
    if ($customer === null) {
        $customer = $row;
    }
    $orderItems[] = $row;
}

if (isset($stmt) && $stmt !== false && is_object($stmt)) {
    mysqli_stmt_close($stmt);
}

if (!$customer) {
    die("Error: Order not found.");
}

$profilePicture = "../uploads/default-profile.png";

if (!empty($customer['profile_img']) && file_exists("../uploads/" . $customer['profile_img'])) {
    $profilePicture = "../uploads/" . $customer['profile_img'];
} elseif (!empty($customer['customer_image_path']) && file_exists("../uploads/" . $customer['customer_image_path'])) {
    $profilePicture = "../uploads/" . $customer['customer_image_path'];
}

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

    <div class="mt-4">
        <a href="orders.php" class="btn btn-back">
            <i class="bi bi-arrow-left me-2"></i>Back to Orders
        </a>
    </div>
</div>

<?php
include('../includes/footer.php');
?>