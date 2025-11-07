<?php
session_start();

// Redirect to login if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: user/login.php");
    exit;
}

$full_name = isset($_SESSION['full_name']) ? $_SESSION['full_name'] : 'User';
$role = isset($_SESSION['role']) ? $_SESSION['role'] : 'customer';
?>

<!DOCTYPE html>
<html>
<head>
    <title>Shop | Lensify</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="assets/bootstrap.min.css">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .navbar {
            background-color: #222;
        }
        .navbar-brand, .nav-link, .dropdown-item {
            color: #fff !important;
        }
        .dropdown-menu {
            right: 0;
            left: auto;
        }
        .container h2 {
            margin-top: 50px;
        }
    </style>
</head>
<body>

<!-- ✅ Navigation Bar -->
<nav class="navbar navbar-expand-lg navbar-dark px-3">
    <a class="navbar-brand" href="#">📸 Lensify</a>

    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
        <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navbarNav">
        <!-- Menu Links -->
        <ul class="navbar-nav me-auto">
            <li class="nav-item"><a class="nav-link" href="#">Home</a></li>
            <li class="nav-item"><a class="nav-link" href="#">Cameras</a></li>
            <li class="nav-item"><a class="nav-link" href="#">Lenses</a></li>
            <li class="nav-item"><a class="nav-link" href="#">Accessories</a></li>
            <li class="nav-item"><a class="nav-link" href="#">About</a></li>
        </ul>

        <!-- Profile Dropdown -->
        <ul class="navbar-nav">
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                    👤 <?php echo htmlspecialchars($full_name); ?>
                </a>
                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                    <li><a class="dropdown-item" href="user/profile.php">My Profile</a></li>
                    <li><a class="dropdown-item" href="user/logout.php">Logout</a></li>
                </ul>
            </li>
        </ul>
    </div>
</nav>

<!-- ✅ Main Content -->
<div class="container text-center">
    <h2 class="mt-5">Welcome to Lensify, <?php echo htmlspecialchars($full_name); ?>!</h2>
    <p class="text-muted">Explore our collection of cameras, lenses, and accessories.</p>

    <!-- Example placeholder for products -->
    <div class="row mt-4">
        <div class="col-md-4">
            <div class="card mb-4 shadow-sm">
                <img src="assets/sample_camera.jpg" class="card-img-top" alt="Camera">
                <div class="card-body">
                    <h5 class="card-title">Canon EOS R10</h5>
                    <p class="card-text">Lightweight mirrorless camera perfect for creators.</p>
                    <button class="btn btn-primary w-100">View Details</button>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card mb-4 shadow-sm">
                <img src="assets/sample_camera2.jpg" class="card-img-top" alt="Camera">
                <div class="card-body">
                    <h5 class="card-title">Sony Alpha ZV-E10</h5>
                    <p class="card-text">Designed for vloggers and YouTube creators.</p>
                    <button class="btn btn-primary w-100">View Details</button>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card mb-4 shadow-sm">
                <img src="assets/sample_camera3.jpg" class="card-img-top" alt="Camera">
                <div class="card-body">
                    <h5 class="card-title">Nikon Z50</h5>
                    <p class="card-text">Compact and powerful for travel photography.</p>
                    <button class="btn btn-primary w-100">View Details</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="assets/bootstrap.bundle.min.js"></script>
</body>
</html>
