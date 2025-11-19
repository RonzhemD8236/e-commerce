<?php
session_start();
include('header.php'); // Admin header
include('../includes/config.php');

// ✅ Restrict to logged-in admins
//if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
  //  header("Location: ../user/login.php");
    //exit();
//}

// Fetch Key Metrics
// Total Users
$stmt = $conn->prepare("SELECT COUNT(*) as total_users FROM users WHERE role='customer'");
$stmt->execute();
$totalUsers = $stmt->get_result()->fetch_assoc()['total_users'] ?? 0;

// Total Orders
$stmt = $conn->prepare("SELECT COUNT(*) as total_orders FROM orderinfo");
$stmt->execute();
$totalOrders = $stmt->get_result()->fetch_assoc()['total_orders'] ?? 0;

// Total Products
$stmt = $conn->prepare("SELECT COUNT(*) as total_products FROM item");
$stmt->execute();
$totalProducts = $stmt->get_result()->fetch_assoc()['total_products'] ?? 0;

// Total Revenue
$stmt = $conn->prepare("SELECT SUM(ol.quantity * i.sell_price) as total_revenue 
                        FROM orderline ol
                        JOIN item i ON ol.item_id = i.item_id");
$stmt->execute();
$totalRevenue = $stmt->get_result()->fetch_assoc()['total_revenue'] ?? 0;

// Recent Products
$stmt = $conn->prepare("SELECT item_id, description, sell_price, image_path 
                        FROM item ORDER BY item_id DESC LIMIT 6");
$stmt->execute();
$productsQuery = $stmt->get_result();
?>

<style>
/* Hero Banner */
.hero-banner {
    background: linear-gradient(135deg, rgba(0, 0, 0, 0.7) 0%, rgba(0, 0, 0, 0.85) 100%),
                url('https://images.unsplash.com/photo-1460925895917-afdab827c52f?w=1200&h=400&fit=crop') center/cover;
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

/* Metric Cards */
.metric-cards {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    margin-bottom: 40px;
}

.metric-card {
    background: white;
    border-radius: 12px;
    padding: 24px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    transition: transform 0.2s, box-shadow 0.2s;
}

.metric-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.metric-card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
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
    width: 40px;
    height: 40px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.25rem;
}

.metric-card-value {
    font-size: 2rem;
    font-weight: 700;
    color: #1f2937;
    margin-bottom: 4px;
}

.metric-card-change {
    font-size: 0.875rem;
    color: #10b981;
}

/* Icon colors */
.icon-primary {
    background-color: #dbeafe;
    color: #3b82f6;
}

.icon-success {
    background-color: #d1fae5;
    color: #10b981;
}

.icon-warning {
    background-color: #fef3c7;
    color: #f59e0b;
}

.icon-danger {
    background-color: #fee2e2;
    color: #ef4444;
}

/* Section Header */
.section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 24px;
}

.section-header h2 {
    font-size: 1.5rem;
    font-weight: 700;
    color: #1f2937;
    margin: 0;
}

.btn-view-all {
    padding: 8px 16px;
    background-color: #667eea;
    color: white;
    border: none;
    border-radius: 6px;
    font-weight: 500;
    text-decoration: none;
    display: inline-block;
    transition: background-color 0.2s;
}

.btn-view-all:hover {
    background-color: #5568d3;
    color: white;
}

/* Product Grid */
.product-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 24px;
    margin-bottom: 40px;
}

.product-card {
    background: white;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    transition: transform 0.2s, box-shadow 0.2s;
}

.product-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.product-card-image {
    width: 100%;
    height: 200px;
    object-fit: cover;
    background-color: #f3f4f6;
}

.product-card-body {
    padding: 16px;
}

.product-card-title {
    font-size: 1rem;
    font-weight: 600;
    color: #1f2937;
    margin-bottom: 8px;
    overflow: hidden;
    text-overflow: ellipsis;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
}

.product-card-price {
    font-size: 1.25rem;
    font-weight: 700;
    color: #667eea;
    margin-bottom: 12px;
}

.btn-manage {
    width: 100%;
    padding: 8px 16px;
    background-color: #3b82f6;
    color: white;
    border: none;
    border-radius: 6px;
    font-weight: 500;
    cursor: pointer;
    text-decoration: none;
    display: inline-block;
    text-align: center;
    transition: background-color 0.2s;
}

.btn-manage:hover {
    background-color: #2563eb;
    color: white;
}

.empty-state {
    text-align: center;
    padding: 60px 20px;
    color: #6b7280;
}

.empty-state-icon {
    font-size: 3rem;
    margin-bottom: 16px;
    opacity: 0.5;
}

.empty-state-text {
    font-size: 1.125rem;
    margin-bottom: 8px;
}
</style>

<div class="container-fluid px-4 py-4">
    <!-- Hero Banner -->
    <div class="hero-banner">
        <h1>Dashboard Overview</h1>
        <p>Welcome back! Here's a quick overview of your e-commerce store performance and recent activities.</p>
    </div>

    <!-- Key Metrics -->
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


</div>

<?php
include('../includes/footer.php');
?>