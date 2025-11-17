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
    
    <!-- jQuery FIRST (required for Bootstrap dropdowns) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
    <!-- Bootstrap JS Bundle (Popper included) - Load in HEAD -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
    
    <style>
        /* CRITICAL: Reset all possible conflicts */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        html {
            height: 100%;
        }
        
        body {
            min-height: 100%;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif !important;
            padding-top: 85px !important;
            margin: 0 !important;
        }
        
        /* NAVBAR - ABSOLUTE OVERRIDE */
        nav.navbar,
        .navbar {
            position: fixed !important;
            top: 0 !important;
            left: 0 !important;
            right: 0 !important;
            width: 100% !important;
            z-index: 999999 !important;
            background-color: #ffffff !important;
            background: #ffffff !important;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1) !important;
            padding: 1rem 2rem !important;
            margin: 0 !important;
            border: none !important;
            border-radius: 0 !important;
        }
        
        .navbar-brand {
            font-weight: 700 !important;
            font-size: 1.5rem !important;
            color: #212529 !important;
            text-decoration: none !important;
        }
        
        .navbar-brand:hover {
            color: #0d6efd !important;
        }
        
        .nav-link {
            color: #495057 !important;
            padding: 0.5rem 1rem !important;
            font-weight: 500 !important;
            text-decoration: none !important;
            transition: color 0.2s ease !important;
        }
        
        .nav-link:hover,
        .nav-link:focus {
            color: #0d6efd !important;
        }
        
        /* Profile Image */
        .profile-img {
            width: 35px !important;
            height: 35px !important;
            object-fit: cover !important;
            border-radius: 50% !important;
            margin-right: 8px !important;
            border: 2px solid #dee2e6 !important;
        }
        
        /* Dropdown Toggle */
        .dropdown-toggle {
            display: flex !important;
            align-items: center !important;
            cursor: pointer !important;
            background: transparent !important;
            border: none !important;
        }
        
        .dropdown-toggle::after {
            margin-left: 0.5rem !important;
        }
        
        /* Cart Icon */
        .cart-icon { 
            position: relative !important;
            display: inline-block !important;
        }
        
        .cart-count {
            position: absolute !important;
            top: -8px !important;
            right: -12px !important;
            background-color: #dc3545 !important;
            color: white !important;
            font-size: 10px !important;
            font-weight: bold !important;
            padding: 2px 6px !important;
            border-radius: 10px !important;
            min-width: 18px !important;
            text-align: center !important;
            line-height: 1.2 !important;
        }
        
        /* DROPDOWN MENU - CRITICAL FIXES */
        .dropdown-menu {
            position: absolute !important;
            z-index: 9999999 !important;
            top: 100% !important;
            left: auto !important;
            right: 0 !important;
            display: none !important;
            background-color: #ffffff !important;
            background: #ffffff !important;
            border: 1px solid rgba(0,0,0,0.15) !important;
            border-radius: 8px !important;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15) !important;
            padding: 0.5rem 0 !important;
            margin-top: 0.5rem !important;
            min-width: 220px !important;
        }
        
        .dropdown-menu.show {
            display: block !important;
        }
        
        .dropdown-item {
            display: block !important;
            width: 100% !important;
            padding: 0.5rem 1.5rem !important;
            clear: both !important;
            font-weight: 400 !important;
            color: #212529 !important;
            text-align: inherit !important;
            text-decoration: none !important;
            white-space: nowrap !important;
            background-color: transparent !important;
            border: 0 !important;
            transition: background-color 0.15s ease-in-out !important;
        }
        
        .dropdown-item:hover,
        .dropdown-item:focus {
            background-color: #f8f9fa !important;
            color: #0d6efd !important;
        }
        
        .dropdown-divider {
            height: 0 !important;
            margin: 0.5rem 0 !important;
            overflow: hidden !important;
            border-top: 1px solid #dee2e6 !important;
        }
        
        /* Mobile Responsive */
        @media (max-width: 991px) {
            body {
                padding-top: 70px !important;
            }
            
            .navbar {
                padding: 0.5rem 1rem !important;
            }
            
            .dropdown-menu {
                position: static !important;
                float: none !important;
                box-shadow: none !important;
                border: none !important;
                margin-top: 0 !important;
                padding-left: 1rem !important;
            }
        }
    </style>
    
    <!-- Custom CSS (loaded after to allow overrides if needed) -->
    <link href="<?php echo htmlspecialchars($css_path); ?>" rel="stylesheet" type="text/css">
</head>
<body>

<!-- FIXED NAVBAR -->
<nav class="navbar navbar-expand-lg navbar-light bg-white">
    <div class="container-fluid">
        <a class="navbar-brand fw-bold" href="/lensify/e-commerce/homepage.php">📸 Lensify</a>
        
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu" aria-controls="navMenu" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <div class="collapse navbar-collapse" id="navMenu">
            <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
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
            <ul class="navbar-nav ms-3">
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
                ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <img src="<?php echo htmlspecialchars($profileImg); ?>" alt="Profile" class="profile-img">
                            <span>My Account</span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                            <li><a class="dropdown-item" href="/lensify/e-commerce/user/profile.php"><i class="fas fa-user me-2"></i>Profile</a></li>
                            <li><a class="dropdown-item" href="/lensify/e-commerce/user/myorders.php"><i class="fas fa-shopping-bag me-2"></i>My Orders</a></li>
                            <li><a class="dropdown-item" href="/lensify/e-commerce/user/purchased.php"><i class="fas fa-box-open me-2"></i>Purchased Products</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="/lensify/e-commerce/user/logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
                        </ul>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a href="/lensify/e-commerce/user/login.php" class="nav-link"><i class="fas fa-sign-in-alt me-1"></i>Login</a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<!-- Fallback Dropdown Script - Runs IMMEDIATELY -->
<script>
(function() {
    'use strict';
    
    function initDropdown() {
        const dropdownToggle = document.getElementById('navbarDropdown');
        const dropdownMenu = dropdownToggle ? dropdownToggle.nextElementSibling : null;
        
        if (!dropdownToggle || !dropdownMenu) return;
        
        // Toggle dropdown on click
        dropdownToggle.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            const isOpen = dropdownMenu.classList.contains('show');
            
            // Close all other dropdowns
            document.querySelectorAll('.dropdown-menu.show').forEach(menu => {
                menu.classList.remove('show');
            });
            
            // Toggle this dropdown
            if (!isOpen) {
                dropdownMenu.classList.add('show');
            }
        });
        
        // Close dropdown when clicking outside
        document.addEventListener('click', function(e) {
            if (!dropdownToggle.contains(e.target) && !dropdownMenu.contains(e.target)) {
                dropdownMenu.classList.remove('show');
            }
        });
        
        // Close dropdown on ESC key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                dropdownMenu.classList.remove('show');
            }
        });
    }
    
    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initDropdown);
    } else {
        initDropdown();
    }
})();
</script>

<!-- PAGE CONTENT STARTS BELOW -->