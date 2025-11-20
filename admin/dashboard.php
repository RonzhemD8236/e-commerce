<?php
session_start();
include('header.php');
include('../includes/config.php');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    $_SESSION['auth_error'] = 'Please log in as admin to access this page.';
    header("Location: ../admin/login.php");
    exit();
}

 
$stmt = $conn->prepare("SELECT COUNT(*) as total_users FROM users WHERE role='customer'");
$stmt->execute();
$totalUsers = $stmt->get_result()->fetch_assoc()['total_users'] ?? 0;

$stmt = $conn->prepare("SELECT COUNT(*) as total_orders FROM orderinfo");
$stmt->execute();
$totalOrders = $stmt->get_result()->fetch_assoc()['total_orders'] ?? 0;

$stmt = $conn->prepare("SELECT COUNT(*) as total_products FROM item");
$stmt->execute();
$totalProducts = $stmt->get_result()->fetch_assoc()['total_products'] ?? 0;

$stmt = $conn->prepare("
    SELECT SUM(ol.quantity * i.sell_price) as total_revenue 
    FROM orderline ol
    JOIN item i ON ol.item_id = i.item_id
");
$stmt->execute();
$totalRevenue = $stmt->get_result()->fetch_assoc()['total_revenue'] ?? 0;

 
$ordersQuery = $conn->query("SELECT * FROM pending_orders_detail ORDER BY date_placed DESC LIMIT 6");
$stockQuery = $conn->query("
    SELECT i.item_id, i.description, s.quantity
    FROM item i
    JOIN stock s ON i.item_id = s.item_id
    WHERE s.quantity <= 5
    ORDER BY s.quantity ASC
    LIMIT 6
");
?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<style>
body {
    font-family: 'Inter', sans-serif;
    background-color: #f9fafb;
    color: #1f2937;
}

.hero-banner {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%),
                url('https://images.unsplash.com/photo-1460925895917-afdab827c52f?w=1200&h=400&fit=crop') center/cover;
    color: white;
    padding: 60px 40px;
    border-radius: 12px;
    margin-bottom: 40px;
    box-shadow: 0 8px 20px rgba(0,0,0,0.2);
}
.hero-banner h1 {
    font-size: 2.5rem;
    font-weight: 700;
}
.hero-banner p {
    font-size: 1.1rem;
    opacity: 0.95;
}

.metric-cards {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(240px,1fr));
    gap: 20px;
    margin-bottom: 40px;
}
.metric-card {
    background: white;
    border-radius: 12px;
    padding: 24px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.05);
    transition: 0.2s;
}
.metric-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 20px rgba(0,0,0,0.1);
}
.metric-card-header {
    display: flex;
    justify-content: space-between;
    margin-bottom: 12px;
}
.metric-card-title {
    font-size: 0.875rem;
    font-weight: 600;
    color: #6b7280;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

 
.metric-card-icon {
    font-size: 1.5rem;
    padding: 12px;
    border-radius: 10px;
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
}

.icon-primary {
    background: linear-gradient(135deg, #a78bfa, #7c3aed);
}
.icon-success {
    background: linear-gradient(135deg, #6ee7b7, #10b981);
}
.icon-warning {
    background: linear-gradient(135deg, #fcd34d, #f59e0b);
}
.icon-danger {
    background: linear-gradient(135deg, #fca5a5, #ef4444);
}

.metric-card-value {
    font-size: 2rem;
    font-weight: 700;
}
.metric-card-change {
    font-size: 0.875rem;
    color:  #4f46e5;
}

.section-header {
    display: flex;
    justify-content: space-between;
    margin-bottom: 24px;
}
.section-header h2 {
    font-size: 1.5rem;
    font-weight: 700;
}
.btn-view-all {
    padding: 8px 16px;
    background-color: #4f46e5;
    color: white;
    border-radius: 6px;
    text-decoration: none;
}
.btn-view-all:hover { background-color: #3730a3; }

 
.list-card {
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: white;
    padding: 16px 20px;
    border-radius: 16px;
    margin-bottom: 14px;
    box-shadow: 0 4px 14px rgba(0,0,0,0.06);
    transition: 0.2s;
}
.list-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 18px rgba(0,0,0,0.1);
}

.list-card-left {
    display: flex;
    flex-direction: column;
}
.list-subtext {
    color: #6b7280;
    font-size: 0.85rem;
}

 
.list-icon {
    width: 46px;
    height: 46px;
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 22px;
    margin-right: 16px;
    flex-shrink: 0;
}

    .icon-blue {
        background: linear-gradient(135deg, #60a5fa, #2563eb);
    }
    .icon-red {
        background: linear-gradient(135deg, #f87171, #dc2626);
    }
    .icon-yellow {
        background: linear-gradient(135deg, #fbbf24, #f59e0b);
    }

    .small-btn {
        padding: 6px 14px;
        border-radius: 10px !important;
        font-size: 0.85rem;
    }
        .btn-view-all {
    background: none !important;
    border: none !important;
    padding: 0 !important;
    color: gray;  
    font-weight: 600;
    font-size: 0.95rem;
    text-decoration: none;
    cursor: pointer;
}

.btn-view-all:hover {
    color: #3730a3; 
}

</style>


<div class="container-fluid px-4 py-4">


    <div class="hero-banner">
        <h1>Dashboard Overview</h1>
        <p>Welcome back! Here's a quick overview of your store performance.</p>
    </div>


    <div class="metric-cards">

        <div class="metric-card">
            <div class="metric-card-header">
                <span class="metric-card-title">Total Users</span>
                <div class="metric-card-icon icon-primary">👥</div>
            </div>
            <div class="metric-card-value"><?= number_format($totalUsers) ?></div>
            <div class="metric-card-change">Registered customers</div>
        </div>

        <div class="metric-card">
            <div class="metric-card-header">
                <span class="metric-card-title">Total Orders</span>
                <div class="metric-card-icon icon-success">📦</div>
            </div>
            <div class="metric-card-value"><?= number_format($totalOrders) ?></div>
            <div class="metric-card-change">All time orders</div>
        </div>

        <div class="metric-card">
            <div class="metric-card-header">
                <span class="metric-card-title">Total Products</span>
                <div class="metric-card-icon icon-warning">🛍️</div>
            </div>
            <div class="metric-card-value"><?= number_format($totalProducts) ?></div>
            <div class="metric-card-change">In inventory</div>
        </div>

        <div class="metric-card">
            <div class="metric-card-header">
                <span class="metric-card-title">Total Revenue</span>
                <div class="metric-card-icon icon-danger">💰</div>
            </div>
            <div class="metric-card-value">₱<?= number_format($totalRevenue, 2) ?></div>
            <div class="metric-card-change">All time earnings</div>
        </div>
    </div>


    <div style="display:grid; grid-template-columns:1fr 1fr; gap:24px;">


        <div>
            <div class="section-header">
                <h2>Recent Orders</h2>
                <a href="orders.php" class="btn-view-all">View All</a>
            </div>

            <?php while($order = $ordersQuery->fetch_assoc()): ?>
            <div class="list-card">
                <div class="list-icon icon-blue">📦</div>

                <div class="list-card-left">
                    <strong><?= htmlspecialchars($order['customer_name']) ?></strong>
                    <div class="list-subtext">
                        Items: <?= number_format($order['items_count']) ?> •
                        Total: ₱<?= number_format($order['total_amount'], 2) ?>
                    </div>
                </div>

                <i class="fa-solid fa-chevron-right" style="color:#9ca3af;"></i>
            </div>
            <?php endwhile; ?>
        </div>


        <div>
            <div class="section-header">
                <h2>Low Stock Inventory</h2>
                <a href="../item/index.php" class="btn-view-all">View All</a>
            </div>

            <?php while($stock = $stockQuery->fetch_assoc()): ?>
            <div class="list-card">
                <div class="list-icon icon-red">⚠️</div>

                <div class="list-card-left">
                    <strong><?= htmlspecialchars($stock['description']) ?></strong>
                    <div class="list-subtext">Stock: <?= $stock['quantity'] ?></div>
                </div>

                <a href="../item/index.php?id=<?= $stock['item_id'] ?>" class="btn-manage small-btn">Restock</a>
            </div>
            <?php endwhile; ?>
        </div>

    </div>
</div>

<?php include('../includes/footer.php'); ?>
