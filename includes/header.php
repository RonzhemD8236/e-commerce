<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" integrity="sha512-z3gLpd7yknf1YoNbCzqRKc4qyor8gaKU1qmn+CShxbuBusANI9QpRohGBreCFkKxLhei6S9CQXFEbbKuqLg0DA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
  <link href="includes/style/style.css" rel="stylesheet" type="text/css">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>
  <title>shop </title>
</head>

<body>
  <nav class="navbar navbar-expand-lg bg-body-tertiary">
    <div class="container-fluid">
      <a class="navbar-brand" href="#">My Shop</a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navbarSupportedContent">
        <ul class="navbar-nav me-auto mb-2 mb-lg-0">
          <li class="nav-item">
            <a class="nav-link active" aria-current="page" href="/Lensify/e-commerce/index.php">Home</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="#">Link</a>
          </li>
          <li class="nav-item dropdown">
            <?php if (isset($_SESSION['user_id'])) {
              echo '<a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
              Dropdown
            </a>';

              echo '<ul class="dropdown-menu">';
              if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
                echo "<li><a class='dropdown-item' href='../index.php'>item</a></li>";
                echo "<li><a class='dropdown-item' href='../item/admin/index.php'>Users</a></li>";
                echo "<li><a class='dropdown-item' href='../item/admin/orders.php'>orders</a></li>";
              } else {
                echo '<li><a class="dropdown-item" href="/Lensify/e-commerce/user/profile.php"> profile</a></li>';
                echo '<li><a class="dropdown-item" href="../user/myorders.php"> My Orders</a></li>';
              }
              echo "</ul>";
            }
            ?>


          </li>

        </ul>
 <form action="search.php" method="GET" class="d-flex flex-column align-items-start">
  <div class="d-flex w-100">
    <input class="form-control me-2" type="search" placeholder="Search" aria-label="Search" name="search">
    <button class="btn btn-outline-success" type="submit"></button>
  </div>

  <?php
  if (isset($_SESSION['email'])) {
      echo "<p class='text-muted ms-1 mt-1'>{$_SESSION['email']}</p>";
  }
  ?>
</form>

<?php
if (!isset($_SESSION['user_id'])) {
  echo "<div class='navbar-nav ms-auto'>
                <a href='http://{$_SERVER['SERVER_NAME']}/shop/user/login.php' class='nav-item nav-link'>Login</a></div>";
} else {
  echo "<div class='navbar-nav ms-auto'>
                <a href='http://{$_SERVER['SERVER_NAME']}/shop/user/logout.php'  class='nav-item nav-link'>Logout</a></div>";
}
?>
      </div>
    </div>
  </nav>