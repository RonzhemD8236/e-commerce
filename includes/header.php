<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
  <link href="../includes/style/style.css" rel="stylesheet" type="text/css">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <title>Lensify | Shop</title>
</head>

<body>
  <nav class="navbar navbar-expand-lg bg-body-tertiary">
    <div class="container-fluid">
      <a class="navbar-brand" href="/Lensify/e-commerce/index.php">Lensify</a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent"
        aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>

      <div class="collapse navbar-collapse" id="navbarSupportedContent">
        <ul class="navbar-nav me-auto mb-2 mb-lg-0">
          <li class="nav-item">
            <a class="nav-link active" aria-current="page" href="/Lensify/e-commerce/index.php">Home</a>
          </li>

          <?php if (isset($_SESSION['user_id'])): ?>
            <li class="nav-item dropdown">
              <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown"
                aria-expanded="false">
                Account
              </a>
              <ul class="dropdown-menu">
                <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                  <li><a class="dropdown-item" href="/Lensify/e-commerce/admin/index.php">Dashboard</a></li>
                  <li><a class="dropdown-item" href="/Lensify/e-commerce/admin/items.php">Manage Items</a></li>
                  <li><a class="dropdown-item" href="/Lensify/e-commerce/admin/orders.php">View Orders</a></li>
                  <li><a class="dropdown-item" href="/Lensify/e-commerce/admin/users.php">Manage Users</a></li>
                <?php else: ?>
                  <li><a class="dropdown-item" href="/Lensify/e-commerce/user/profile.php">Profile</a></li>
                  <li><a class="dropdown-item" href="/Lensify/e-commerce/user/myorders.php">My Orders</a></li>
                <?php endif; ?>
              </ul>
            </li>
          <?php endif; ?>
        </ul>

        <!-- SEARCH BAR -->
        <form action="/Lensify/e-commerce/search.php" method="GET" class="d-flex flex-column align-items-start">
          <div class="d-flex w-100">
            <input class="form-control me-2" type="search" placeholder="Search" aria-label="Search" name="search">
            <button class="btn btn-outline-success" type="submit">Search</button>
          </div>

          <?php if (isset($_SESSION['email'])): ?>
            <p class="text-muted ms-1 mt-1"><?php echo htmlspecialchars($_SESSION['email']); ?></p>
          <?php endif; ?>
        </form>

        <!-- LOGIN / LOGOUT -->
        <?php if (!isset($_SESSION['user_id'])): ?>
          <div class="navbar-nav ms-auto">
            <a href="/Lensify/e-commerce/user/login.php" class="nav-item nav-link">Login</a>
            <a href="/Lensify/e-commerce/user/register.php" class="nav-item nav-link">Register</a>
          </div>
        <?php else: ?>
          <div class="navbar-nav ms-auto">
            <a href="/Lensify/e-commerce/user/logout.php" class="nav-item nav-link">Logout</a>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </nav>
