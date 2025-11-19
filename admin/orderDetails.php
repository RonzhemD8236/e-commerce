<?php
session_start();
include('../includes/header.php');
include('../includes/config.php');

// ✅ Validate orderinfo_id parameter
$orderId = isset($_GET['orderinfo_id']) ? (int)$_GET['orderinfo_id'] : 0;
if ($orderId === 0) {
    die("Error: Invalid or missing orderinfo_id.");
}

$_SESSION['orderId'] = $orderId;

// ✅ Query for customer/order info with profile image
$sql = "SELECT 
            c.lname, c.fname, c.addressline, c.town, c.zipcode, c.phone, c.image_path,
            o.orderinfo_id, o.status, o.date_placed, o.date_shipped
        FROM customer c
        INNER JOIN orderinfo o USING(customer_id)
        WHERE o.orderinfo_id = ?
        LIMIT 1";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $orderId);
$stmt->execute();
$result = $stmt->get_result();
$customer = mysqli_fetch_assoc($result);

if (!$customer) {
    die("Error: Order not found.");
}

// ✅ Set profile picture path
$profilePicture = !empty($customer['image_path']) && file_exists("../uploads/" . $customer['image_path']) 
    ? "../uploads/" . $customer['image_path'] 
    : "../uploads/default-profile.png";

// ✅ Query for order items
$sql = "SELECT 
            i.description, 
            ol.quantity, 
            i.sell_price
        FROM orderline ol
        INNER JOIN item i USING(item_id)
        WHERE ol.orderinfo_id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $orderId);
$stmt->execute();
$items = $stmt->get_result();

// ✅ Calculate status badge color
$statusColors = [
    'Processing' => 'warning',
    'Delivered' => 'success',
    'Canceled' => 'danger',
    'Pending' => 'secondary'
];
$badgeColor = $statusColors[$customer['status']] ?? 'secondary';
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
</style>

<div class="container my-5">
    <!-- Order Header -->
    <div class="order-card">
        <div class="order-header d-flex justify-content-between align-items-center">
            <div>
                <h2 class="mb-1">Order #<?= str_pad($customer['orderinfo_id'], 4, '0', STR_PAD_LEFT) ?></h2>
                <p class="mb-0 opacity-75">Placed on <?= date('F j, Y', strtotime($customer['date_placed'])) ?></p>
            </div>
            <span class="status-badge bg-<?= $badgeColor ?>"><?= htmlspecialchars($customer['status']) ?></span>
        </div>
        
        <div class="info-section">
            <div class="row">
                <!-- Customer Information -->
                <div class="col-lg-6 mb-4 mb-lg-0">
                    <h5 class="section-title">Customer Information</h5>
                    <div class="d-flex align-items-start">
                        <img src="<?= htmlspecialchars($profilePicture) ?>" 
                             alt="Customer Profile" 
                             class="rounded-circle profile-image me-3">
                        <div class="flex-grow-1">
                            <div class="info-label">Full Name</div>
                            <div class="info-value"><?= htmlspecialchars("{$customer['fname']} {$customer['lname']}") ?></div>
                            
                            <div class="info-label">Contact Number</div>
                            <div class="info-value"><?= htmlspecialchars($customer['phone']) ?></div>
                            
                            <div class="info-label">Shipping Address</div>
                            <div class="info-value">
                                <?= htmlspecialchars($customer['addressline']) ?><br>
                                <?= htmlspecialchars($customer['town']) ?>, <?= htmlspecialchars($customer['zipcode']) ?>
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
                    
                    <div class="info-label">Order Status</div>
                    <div class="info-value">
                        <span class="badge bg-<?= $badgeColor ?> status-badge"><?= htmlspecialchars($customer['status']) ?></span>
                    </div>
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
                            <th class="text-center" style="width: 120px;">Quantity</th>
                            <th class="text-end" style="width: 150px;">Unit Price</th>
                            <th class="text-end" style="width: 150px;">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $grandTotal = 0;
                        while ($row = mysqli_fetch_assoc($items)) {
                            $total = $row['sell_price'] * $row['quantity'];
                            $grandTotal += $total;
                            echo "<tr>";
                            echo "<td>" . htmlspecialchars($row['description']) . "</td>";
                            echo "<td class='text-center'>{$row['quantity']}</td>";
                            echo "<td class='text-end'>₱" . number_format($row['sell_price'], 2) . "</td>";
                            echo "<td class='text-end'>₱" . number_format($total, 2) . "</td>";
                            echo "</tr>";
                        }
                        ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="3" class="text-end">Grand Total:</td>
                            <td class="text-end">₱<?= number_format($grandTotal, 2) ?></td>
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