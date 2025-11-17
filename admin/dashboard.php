<?php
session_start();
include('../includes/config.php');
include('header.php'); // Admin header

// ✅ Restrict to logged-in admins
//if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
  //  header("Location: ../user/login.php");
    //exit();
//}

// Fetch Key Metrics
$totalUsersQuery = $conn->query("SELECT COUNT(*) as total_users FROM users WHERE role='customer'");
$totalUsers = $totalUsersQuery->fetch_assoc()['total_users'] ?? 0;

$totalOrdersQuery = $conn->query("SELECT COUNT(*) as total_orders FROM orderinfo");
$totalOrders = $totalOrdersQuery->fetch_assoc()['total_orders'] ?? 0;

$totalProductsQuery = $conn->query("SELECT COUNT(*) as total_products FROM item");
$totalProducts = $totalProductsQuery->fetch_assoc()['total_products'] ?? 0;

// Optional: total revenue
$totalRevenueQuery = $conn->query("SELECT SUM(ol.quantity * i.sell_price) as total_revenue 
                                  FROM orderline ol
                                  JOIN item i ON ol.item_id = i.item_id");
$totalRevenue = $totalRevenueQuery->fetch_assoc()['total_revenue'] ?? 0;

// Fetch Recent Products / Inventory
$productsQuery = $conn->query("SELECT item_id, title, sell_price, image_path 
                               FROM item ORDER BY item_id DESC LIMIT 6");
?>

<div class="container mt-4">

    <!-- Key Metrics -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card text-white bg-primary mb-3">
                <div class="card-body">
                    <h5 class="card-title">Total Users</h5>
                    <p class="card-text fs-4"><?= $totalUsers ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-success mb-3">
                <div class="card-body">
                    <h5 class="card-title">Total Orders</h5>
                    <p class="card-text fs-4"><?= $totalOrders ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-warning mb-3">
                <div class="card-body">
                    <h5 class="card-title">Total Products</h5>
                    <p class="card-text fs-4"><?= $totalProducts ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-danger mb-3">
                <div class="card-body">
                    <h5 class="card-title">Total Revenue</h5>
                    <p class="card-text fs-4">₱<?= number_format($totalRevenue, 2) ?></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Products / Inventory -->
    <h4 class="mb-3">Recent Products / Inventory</h4>
    <div class="row">
        <?php while($product = $productsQuery->fetch_assoc()): ?>
            <div class="col-md-4">
                <div class="card mb-3">
                    <img src="<?= htmlspecialchars($product['image_path']) ?>" class="card-img-top" 
                         alt="<?= htmlspecialchars($product['title']) ?>" style="height:200px; object-fit:cover;">
                    <div class="card-body">
                        <h5 class="card-title"><?= htmlspecialchars($product['title']) ?></h5>
                        <p class="card-text">Price: ₱<?= number_format($product['sell_price'], 2) ?></p>
                        <a href="manage_products.php" class="btn btn-sm btn-primary">Manage</a>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
    </div>

</div>
