<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

// Default to 'guest' if not logged in
$role = $_SESSION['role'] ?? 'guest';

// ✅ Determine correct CSS path based on current directory
$current_dir = dirname($_SERVER['PHP_SELF']);
if (strpos($current_dir, '/user') !== false || 
    strpos($current_dir, '/admin') !== false || 
    strpos($current_dir, '/item') !== false) {
    $css_path = '../includes/style/style.css';
} else {
    $css_path = 'includes/style/style.css';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lensify - Your Camera Shop</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>

    <!-- ✅ Custom CSS -->
    <link href="<?php echo htmlspecialchars($css_path); ?>" rel="stylesheet" type="text/css">

    <style>
        .profile-img {
            width: 35px;
            height: 35px;
            object-fit: cover;
            border-radius: 50%;
            margin-right: 8px;
        }
        .navbar {
            background-color: white !important;
        }
        .navbar .nav-link, .navbar .navbar-brand {
            color: #000 !important;
        }
        .navbar .nav-link:hover, .navbar .dropdown-item:hover {
            color: red !important;
        }
        .dropdown-menu {
            background-color: #fff;
            border-radius: 10px;
        }
        .cart-icon { 
            position: relative;
            display: inline-block;
        }
        .cart-count {
            position: absolute;
            top: -8px;
            right: -12px;
            background-color: #dc3545;
            color: white;
            font-size: 10px;
            font-weight: bold;
            padding: 2px 6px;
            border-radius: 10px;
            min-width: 18px;
            text-align: center;
            line-height: 1.2;
        }
    </style>
</head>
<body>

<!-- User Navigation -->
<nav class="navbar navbar-expand-lg navbar-light">
    <div class="container-fluid">
        <a class="navbar-brand fw-bold" href="/lensify/e-commerce/homepage.php">
            Lensify
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
            data-bs-target="#navbarUser" aria-controls="navbarUser" aria-expanded="false"
            aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarUser">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link" href="/lensify/e-commerce/categories.php">Categories</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/lensify/e-commerce/index.php">Products</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/lensify/e-commerce/contact.php">Contact</a>
                </li>
            </ul>

            <!-- Right Side Nav Items -->
            <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                <!-- Shopping Cart -->
                <li class="nav-item me-3">
                    <a href="/lensify/e-commerce/cart/view_cart.php" class="nav-link cart-icon">
                        <i class="fas fa-shopping-cart fa-lg"></i>
                        <?php
                            $cartCount = $_SESSION['cart_count'] ?? 0;
                            if ($cartCount > 0) echo "<span class='cart-count'>{$cartCount}</span>";
                        ?>
                    </a>
                </li>

                <!-- User Account / Login -->
                <?php if (isset($_SESSION['user_id'])):
                    $profileImg = $_SESSION['profile_img'] ?? '/lensify/e-commerce/uploads/default-profile.png';
                    $userName = $_SESSION['email'] ?? 'User';
                ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <img src="<?= htmlspecialchars($profileImg) ?>" alt="Profile" class="profile-img">
                            <?= htmlspecialchars($userName) ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="/lensify/e-commerce/user/profile.php"><i class="bi bi-person-circle me-2"></i>Profile</a></li>
                            <li><a class="dropdown-item" href="/lensify/e-commerce/user/myorders.php"><i class="bi bi-bag-check me-2"></i>My Orders</a></li>
                            <li><a class="dropdown-item" href="/lensify/e-commerce/user/purchased.php"><i class="bi bi-box-seam me-2"></i>Purchased Products</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="/lensify/e-commerce/user/logout.php"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
                        </ul>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a href="/lensify/e-commerce/user/login.php" class="nav-link">
                            <i class="bi bi-box-arrow-in-right me-1"></i>Login
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<!-- PAGE CONTENT STARTS BELOW -->