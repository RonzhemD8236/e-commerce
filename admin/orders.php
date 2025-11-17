<?php
session_start();
include('header.php'); // Admin header
include('../includes/config.php');

// ---------- UPDATED SQL: include payment_method and shipping_method ----------
$sql = "SELECT 
            o.orderinfo_id AS orderId, 
            SUM(i.sell_price * ol.quantity) AS total, 
            o.status,
            o.payment_method,
            o.shipping_method,
            o.created_at
        FROM orderinfo o 
        INNER JOIN orderline ol USING (orderinfo_id) 
        INNER JOIN item i USING (item_id)
        GROUP BY o.orderinfo_id
        ORDER BY o.created_at DESC";

$result = mysqli_query($conn, $sql);
$itemCount = mysqli_num_rows($result);

?>

<style>
/* Hero Banner */
.hero-banner {
    background: linear-gradient(135deg, rgba(0, 0, 0, 0.7) 0%, rgba(0, 0, 0, 0.85) 100%),
                url('https://images.unsplash.com/photo-1556742049-0cfed4f6a45d?w=1200&h=400&fit=crop') center/cover;
    color: white;
    padding: 60px 40px;
    border-radius: 12px;
    margin-bottom: 30px;
    position: relative;
    overflow: hidden;
}

.hero-banner h1 {
    font-size: 2.5rem;
    font-weight: 700;
    margin-bottom: 15px;
}

.hero-banner p {
    font-size: 1.1rem;
    opacity: 0.95;
    max-width: 800px;
}

/* Search and Action Bar */
.search-action-bar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 25px;
    gap: 20px;
}

.search-box {
    flex: 1;
    max-width: 400px;
}

.search-box input {
    width: 100%;
    padding: 10px 15px;
    border: 1px solid #ddd;
    border-radius: 6px;
    font-size: 0.95rem;
}

.search-box input:focus {
    outline: none;
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

/* Order Filter Tabs */
.order-tabs {
    display: flex;
    gap: 0;
    border-bottom: 2px solid #e5e7eb;
    margin-bottom: 25px;
}

.order-tab {
    padding: 12px 24px;
    background: none;
    border: none;
    color: #6b7280;
    font-weight: 500;
    cursor: pointer;
    position: relative;
    text-decoration: none;
    transition: all 0.2s;
}

.order-tab:hover {
    color: #667eea;
    background-color: #f9fafb;
}

.order-tab.active {
    color: #667eea;
}

.order-tab.active::after {
    content: '';
    position: absolute;
    bottom: -2px;
    left: 0;
    right: 0;
    height: 2px;
    background-color: #667eea;
}

/* Table Styling */
.orders-table {
    background: white;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.orders-table table {
    width: 100%;
    border-collapse: collapse;
}

.orders-table thead {
    background-color: #f9fafb;
    border-bottom: 1px solid #e5e7eb;
}

.orders-table th {
    padding: 16px;
    text-align: left;
    font-weight: 600;
    font-size: 0.875rem;
    color: #374151;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

.orders-table td {
    padding: 16px;
    border-bottom: 1px solid #f3f4f6;
    vertical-align: middle;
}

.orders-table tbody tr:hover {
    background-color: #f9fafb;
}

/* Status Badges */
.status-badge {
    display: inline-block;
    padding: 4px 12px;
    border-radius: 12px;
    font-size: 0.875rem;
    font-weight: 500;
}

.status-delivered {
    background-color: #d1fae5;
    color: #065f46;
}

.status-pending {
    background-color: #fef3c7;
    color: #92400e;
}

.status-cancelled {
    background-color: #fee2e2;
    color: #991b1b;
}

/* Action Buttons */
.action-buttons {
    display: flex;
    gap: 8px;
}

.btn-view {
    padding: 6px 16px;
    background-color: #3b82f6;
    color: white;
    border: none;
    border-radius: 6px;
    font-size: 0.875rem;
    font-weight: 500;
    cursor: pointer;
    text-decoration: none;
    display: inline-block;
}

.btn-view:hover {
    background-color: #2563eb;
    color: white;
}

.btn-update {
    padding: 6px 16px;
    background-color: #6b7280;
    color: white;
    border: none;
    border-radius: 6px;
    font-size: 0.875rem;
    font-weight: 500;
    cursor: pointer;
    text-decoration: none;
    display: inline-block;
}

.btn-update:hover {
    background-color: #4b5563;
    color: white;
}

.btn-add-order {
    padding: 10px 20px;
    background-color: #000;
    color: white;
    border: none;
    border-radius: 6px;
    font-weight: 500;
    cursor: pointer;
    text-decoration: none;
    display: inline-block;
}

.btn-add-order:hover {
    background-color: #1f2937;
    color: white;
}

/* Footer Spacing */
.page-wrapper {
    min-height: calc(100vh - 200px);
    padding-bottom: 50px;
}
</style>

<div class="container-fluid px-4 py-4">
    <!-- Hero Banner -->
    <div class="hero-banner">
        <h1>Order Management</h1>
        <p>Manage and oversee order statuses, payment methods, and shipping details. Monitor all transactions and maintain order data securely.</p>
    </div>

    <!-- Search and Add Order Bar -->
    <div class="search-action-bar">
        <div class="search-box">
            <input 
                type="text" 
                id="searchOrder" 
                placeholder="Search by order ID..." 
            >
        </div>
        <a href="add_order.php" class="btn-add-order">Add New Order</a>
    </div>

    <?php include("../includes/alert.php"); ?>

    <!-- Order Filter Tabs -->
    <div class="order-tabs">
        <button class="order-tab active" data-status="all">
            All Orders
        </button>
        <button class="order-tab" data-status="Pending">
            Pending
        </button>
        <button class="order-tab" data-status="Delivered">
            Delivered
        </button>
    </div>

    <!-- Orders Table -->
    <div class="orders-table">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Total</th>
                    <th>Payment Method</th>
                    <th>Shipping Method</th>
                    <th>Status</th>
                    <th>Created At</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php
            if (mysqli_num_rows($result) > 0) {
                while ($row = mysqli_fetch_assoc($result)) {
                    echo "<tr class='order-row' data-status='{$row['status']}'>";
                    echo "<td>" . htmlspecialchars($row['orderId']) . "</td>";
                    echo "<td><strong>₱" . number_format($row['total'], 2) . "</strong></td>";
                    echo "<td>" . htmlspecialchars($row['payment_method']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['shipping_method']) . "</td>";
                    
                    // Status badge
                    $statusClass = 'status-pending';
                    $statusText = 'Pending';
                    
                    if ($row['status'] === 'Delivered') {
                        $statusClass = 'status-delivered';
                        $statusText = 'Delivered';
                    } elseif ($row['status'] === 'Cancelled') {
                        $statusClass = 'status-cancelled';
                        $statusText = 'Cancelled';
                    }
                    
                    echo "<td><span class='status-badge {$statusClass}'>{$statusText}</span></td>";
                    echo "<td>" . date('Y-m-d H:i:s', strtotime($row['created_at'])) . "</td>";
                    
                    // Actions
                    echo "<td>
                            <div class='action-buttons'>
                                <a href='orderDetails.php?orderinfo_id={$row['orderId']}' class='btn-view'>View</a>
                                <a href='update_order.php?orderinfo_id={$row['orderId']}' class='btn-update'>Update</a>
                            </div>
                          </td>";
                    
                    echo "</tr>";
                }
            } else {
                echo "<tr>
                        <td colspan='7' style='text-align: center; padding: 40px; color: #6b7280;'>
                            No orders found.
                        </td>
                      </tr>";
            }
            ?>
            </tbody>
        </table>
    </div>
</div>

<script>
// Tab filtering
document.querySelectorAll('.order-tab').forEach(tab => {
    tab.addEventListener('click', function() {
        // Update active tab styling
        document.querySelectorAll('.order-tab').forEach(t => {
            t.classList.remove('active');
        });
        this.classList.add('active');
        
        // Filter rows
        const status = this.dataset.status;
        document.querySelectorAll('.order-row').forEach(row => {
            if (status === 'all' || row.dataset.status === status) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    });
});

// Search functionality
document.getElementById('searchOrder').addEventListener('input', function() {
    const searchTerm = this.value.toLowerCase();
    document.querySelectorAll('.order-row').forEach(row => {
        const orderId = row.querySelector('td').textContent.toLowerCase();
        if (orderId.includes(searchTerm)) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
});
</script>

<?php
include('../includes/footer.php');
?>