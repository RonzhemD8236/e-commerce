<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    $_SESSION['auth_error'] = 'Please log in as admin to access this page.';
    header("Location: ../admin/login.php");
    exit();
}

include('header.php');
include('../includes/config.php');

$sql = "SELECT 
            o.orderinfo_id,
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

$stmt = $conn->prepare($sql);
$stmt->execute();
$result = $stmt->get_result();
$itemCount = mysqli_num_rows($result);

?>

<style>
.hero-banner {
    background: linear-gradient(
                    135deg,
                    rgba(102, 126, 234, 0.6) 0%,
                    rgba(118, 75, 162, 0.6) 100%
                ),
                url('https://i.pinimg.com/1200x/31/7d/27/317d272366bd3ebba9a022262f0fe81e.jpg') center/cover;
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

.status-processing {
    background-color: #dbeafe;
    color: #1e40af;
}

.status-shipped {
    background-color: #e0e7ff;
    color: #4338ca;
}

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

.modal {
    display: none;
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5);
    animation: fadeIn 0.3s;
}

.modal.show {
    display: flex;
    align-items: center;
    justify-content: center;
}

.modal-content {
    background-color: white;
    padding: 30px;
    border-radius: 12px;
    width: 90%;
    max-width: 500px;
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
    animation: slideDown 0.3s;
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

@keyframes slideDown {
    from { transform: translateY(-50px); opacity: 0; }
    to { transform: translateY(0); opacity: 1; }
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}

.modal-header h2 {
    margin: 0;
    font-size: 1.5rem;
    color: #1f2937;
}

.close-modal {
    background: none;
    border: none;
    font-size: 1.5rem;
    cursor: pointer;
    color: #6b7280;
    padding: 0;
    width: 30px;
    height: 30px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.close-modal:hover {
    color: #1f2937;
}

.form-group {
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    margin-bottom: 8px;
    font-weight: 500;
    color: #374151;
}

.form-group select {
    width: 100%;
    padding: 10px 15px;
    border: 1px solid #d1d5db;
    border-radius: 6px;
    font-size: 1rem;
    background-color: white;
}

.form-group select:focus {
    outline: none;
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.modal-actions {
    display: flex;
    gap: 10px;
    justify-content: flex-end;
    margin-top: 25px;
}

.btn-cancel {
    padding: 10px 20px;
    background-color: #e5e7eb;
    color: #374151;
    border: none;
    border-radius: 6px;
    font-weight: 500;
    cursor: pointer;
}

.btn-cancel:hover {
    background-color: #d1d5db;
}

.btn-submit {
    padding: 10px 20px;
    background-color: #667eea;
    color: white;
    border: none;
    border-radius: 6px;
    font-weight: 500;
    cursor: pointer;
}

.btn-submit:hover {
    background-color: #5568d3;
}

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

    <div class="search-action-bar">
        <div class="search-box">
            <input 
                type="text" 
                id="searchOrder" 
                placeholder="Search by order ID..." 
            >
        </div>
        <div class="btn-add-order" style="cursor: default;">Total Orders: <?php echo $itemCount; ?></div>
    </div>

    <?php include("../includes/alert.php"); ?>

    <div class="order-tabs">
        <button class="order-tab active" data-status="all">
            All Orders
        </button>
        <button class="order-tab" data-status="pending">
            Pending
        </button>
        <button class="order-tab" data-status="processing">
            Processing
        </button>
        <button class="order-tab" data-status="shipped">
            Shipped
        </button>
        <button class="order-tab" data-status="delivered">
            Delivered
        </button>
        <button class="order-tab" data-status="cancelled">
            Cancelled
        </button>
    </div>

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
                    echo "<tr class='order-row' data-status='" . strtolower($row['status']) . "'>";
                    echo "<td>" . htmlspecialchars($row['orderId']) . "</td>";
                    echo "<td><strong>₱" . number_format($row['total'], 2) . "</strong></td>";
                    echo "<td>" . htmlspecialchars($row['payment_method']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['shipping_method']) . "</td>";
                    
                    // Status badge
                    $statusLower = strtolower($row['status']);
                    $statusClass = 'status-pending';
                    $statusText = ucfirst($statusLower);
                    
                    if ($statusLower === 'delivered') {
                        $statusClass = 'status-delivered';
                    } elseif ($statusLower === 'cancelled') {
                        $statusClass = 'status-cancelled';
                    } elseif ($statusLower === 'processing') {
                        $statusClass = 'status-processing';
                    } elseif ($statusLower === 'shipped') {
                        $statusClass = 'status-shipped';
                    }
                    
                    echo "<td><span class='status-badge {$statusClass}'>{$statusText}</span></td>";
                    echo "<td>" . date('Y-m-d H:i:s', strtotime($row['created_at'])) . "</td>";
                    
                    $orderIdValue = 0;
                    if (isset($row['orderId'])) {
                        $orderIdValue = (int)$row['orderId'];
                    } elseif (isset($row['orderid'])) {
                        $orderIdValue = (int)$row['orderid'];
                    } elseif (isset($row['orderinfo_id'])) {
                        $orderIdValue = (int)$row['orderinfo_id'];
                    }
                    
                    if ($orderIdValue > 0) {
                        echo "<td>
                                <div class='action-buttons'>
                                    <a href='orderDetails.php?orderinfo_id=" . urlencode($orderIdValue) . "' class='btn-view'>View</a>
                                    <button class='btn-update' onclick='openUpdateModal({$orderIdValue}, \"" . htmlspecialchars($row['status']) . "\")'>Update</button>
                                </div>
                              </td>";
                    } else {
                        $orderIdValue = isset($row['orderinfo_id']) ? (int)$row['orderinfo_id'] : 0;
                        if ($orderIdValue > 0) {
                            echo "<td>
                                    <div class='action-buttons'>
                                        <a href='orderDetails.php?orderinfo_id=" . urlencode($orderIdValue) . "' class='btn-view'>View</a>
                                        <button class='btn-update' onclick='openUpdateModal({$orderIdValue}, \"" . htmlspecialchars($row['status']) . "\")'>Update</button>
                                    </div>
                                  </td>";
                        } else {
                            echo "<td><span class='text-muted'>N/A</span></td>";
                        }
                    }
                    
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

<div id="updateModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Update Order Status</h2>
            <button class="close-modal" onclick="closeUpdateModal()">&times;</button>
        </div>
        
        <form method="POST" action="updateorder.php">
            <input type="hidden" name="orderId" id="modalOrderId">
            
            <div class="form-group">
                <label for="modalStatus">Order Status</label>
                <select name="status" id="modalStatus" required>
                    <option value="pending">Pending</option>
                    <option value="processing">Processing</option>
                    <option value="shipped">Shipped</option>
                    <option value="delivered">Delivered</option>
                    <option value="cancelled">Cancelled</option>
                </select>
            </div>
            
            <div class="modal-actions">
                <button type="button" class="btn-cancel" onclick="closeUpdateModal()">Cancel</button>
                <button type="submit" class="btn-submit">Update Order</button>
            </div>
        </form>
    </div>
</div>

<script>
// Modal Functions
function openUpdateModal(orderId, currentStatus) {
    document.getElementById('modalOrderId').value = orderId;
    document.getElementById('modalStatus').value = currentStatus.toLowerCase();
    document.getElementById('updateModal').classList.add('show');
}

function closeUpdateModal() {
    document.getElementById('updateModal').classList.remove('show');
}

window.onclick = function(event) {
    const modal = document.getElementById('updateModal');
    if (event.target === modal) {
        closeUpdateModal();
    }
}

document.querySelectorAll('.order-tab').forEach(tab => {
    tab.addEventListener('click', function() {
        document.querySelectorAll('.order-tab').forEach(t => {
            t.classList.remove('active');
        });
        this.classList.add('active');
        
        const status = this.dataset.status.toLowerCase();
        document.querySelectorAll('.order-row').forEach(row => {
            const rowStatus = row.dataset.status.toLowerCase();
            if (status === 'all' || rowStatus === status) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    });
});

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