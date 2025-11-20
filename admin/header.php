<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
$role = $_SESSION['role'] ?? 'guest';

$current_dir = dirname($_SERVER['PHP_SELF']);
if (strpos($current_dir, '/admin') !== false) {
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
  
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
  
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />
  
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>

  <link href="<?php echo htmlspecialchars($css_path); ?>" rel="stylesheet" type="text/css">
  
  <title>Admin Dashboard - Lensify</title>

 <style>
  .profile-img {
    width: 35px;
    height: 35px;
    object-fit: cover;
    border-radius: 25%;
    margin-right: 8px;
  }

  .navbar {
    background-color: white !important;
  }

  .navbar .nav-link,
  .navbar .navbar-brand {
    color: #000 !important;
    transition: color 0.3s ease, transform 0.3s ease;
  }

  .navbar .nav-link:hover,
  .navbar .navbar-brand:hover {
    color: #2563eb !important;
    transform: translateY(-2px);
  }

  .dropdown-menu {
    background-color: #fff;
    border-radius: 10px;
  }

  .navbar .dropdown-item:hover {
    color: #2563eb !important; /* hover color for dropdown items */
    transition: color 0.3s ease;
  }
</style>

</head>

<body>
  <nav class="navbar navbar-expand-lg navbar-dark">
    <div class="container-fluid">
      <a class="navbar-brand fw-bold" href="dashboard.php">
      Lensify
      </a>

      <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
        data-bs-target="#navbarAdmin" aria-controls="navbarAdmin" aria-expanded="false"
        aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>

      <div class="collapse navbar-collapse" id="navbarAdmin">
        <ul class="navbar-nav me-auto mb-2 mb-lg-0">
          <li class="nav-item"><a class="nav-link" href="/lensify/e-commerce/admin/dashboard.php">Dashboard</a></li>
          <li class="nav-item"><a class="nav-link" href="/lensify/e-commerce/admin/index.php">Users</a></li>
          <li class="nav-item"><a class="nav-link" href="/lensify/e-commerce/item/index.php">Products</a></li>
          <li class="nav-item"><a class="nav-link" href="/lensify/e-commerce/admin/orders.php">Orders</a></li>
        </ul>

        <!-- ✅ Profile / Logout -->
        <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
          <?php
            $adminImg = $_SESSION['profile_img'] ?? '../uploads/default-profile.png';
            $adminName = $_SESSION['email'] ?? 'Admin';
          ?>
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
              <img src="<?= htmlspecialchars($adminImg) ?>" alt="Profile" class="profile-img">
              <?= htmlspecialchars($adminName) ?>
            </a>
            <ul class="dropdown-menu dropdown-menu-end">
              <li><a class="dropdown-item" href="/lensify/e-commerce/admin/profile.php"><i class="bi bi-person-circle me-2"></i>My Profile</a></li>
              <li><hr class="dropdown-divider"></li>
              <li><a class="dropdown-item text-danger" href="/lensify/e-commerce/admin/logout.php"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
            </ul>
          </li>
        </ul>
      </div>
    </div>
  </nav>