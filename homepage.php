<?php
session_start();
include("includes/header.php");
include("includes/config.php");


$sql = "
    SELECT i.item_id, i.description, i.short_description, i.sell_price, i.image_path, s.quantity
    FROM item i
    LEFT JOIN stock s ON i.item_id = s.item_id
    WHERE i.deleted_at IS NULL
    ORDER BY i.created_at DESC
    LIMIT 3
";
$stmt = $conn->prepare($sql);
$stmt->execute();
$products = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<title>Lensify - Your Camera Shop</title>

<style>
        body { 
        font-family: Arial, sans-serif;
        background-image: url('uploads/homepage.jpg');
        background-size: cover;
        background-position: center;
        background-attachment: fixed;
        background-repeat: no-repeat;
        margin: 0;
        padding: 0;
        position: relative;
    }
    
    body::before {
        content: '';
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.6);
        z-index: -1;
    }
    
    .main-container {
        padding-top: 100px; 
    }
    

    .hero { padding: 60px 20px; }
    
    .hero h1 {
        color: #fdfdfdff;
    }
    
    .hero p {
        color: #e7e7e7ff;
    }
    
    .hero .btn-primary {
        background-color: white;
        color: #000;
        border: 2px solid white;
    }
    
    .hero .btn-primary:hover {
        background-color: #f0f0f0;
        color: #000;
        border: 2px solid #f0f0f0;
    }
    
    .hero .btn-outline-dark {
        background-color: transparent;
        color: white;
        border: 2px solid white;
    }
    
    .hero .btn-outline-dark:hover {
        background-color: white;
        color: #000;
        border: 2px solid white;
    }
    
    .hero-img {
        width: 100%;
        max-width: 550px;
        height: 250px;
        object-fit: cover;
        display: block;
        margin: 0 auto;
        border-radius: 10px;
    }

    .feature-box h4 {
        color: #e7e7e7ff;
    }
    
    .feature-box p {
        color: #e7e7e7ff;
    }
    
    .feature-box img {
        width: 100%;
        height: 250px;
        object-fit: cover;
        border-radius: 10px;
    }

    .banner-img {
        width: 100%;
        max-width: 100%;
        height: 300px;
        object-fit: cover;
        border-radius: 10px;
        display: block;
        margin: 0 auto;
    }

    .product-card img {
        width: 100%;
        height: 230px;
        object-fit: cover;
        border-radius: 10px;
    }
    
    .product-card h5 {
        color: #e7e7e7ff;
    }
    
    .product-card .text-muted {
        color: #e7e7e7ff !important;
    }
    
    .product-card strong {
        color: #e7e7e7ff;
    }
    
    h3.text-center {
        color: #e7e7e7ff;
    }

    @media (max-width: 768px) {
        .hero-img { height: 200px; max-width: 100%; }
        .feature-box img { height: 200px; }
        .banner-img { height: 200px; }
        .product-card img { height: 200px; }
    }
</style>
</head>
<body>

<div class="main-container">
    <!-- HERO SECTION -->
    <div class="container hero">
        <div class="row align-items-center">
            <div class="col-lg-6">
                <h1 class="fw-bold mb-3">The Best Camera Store in Taguig</h1>
                <p>Capture your best moments with high-quality DSLR, mirrorless cameras, and lenses. Whether you're a beginner or a pro, we have the perfect gear for you — only here at Lensify.</p>
                <a href="/lensify/e-commerce/user/login.php" class="btn btn-primary me-2">Sign in</a>
                <a href="/lensify/e-commerce/index.php" class="btn btn-outline-dark">Browse Products</a>
            </div>
            <div class="col-lg-6">
                <?php
                $heroImage = '/lensify/e-commerce/uploads/camera3.png';
                if (!file_exists($heroImage)) $heroImage = '/lensify/e-commerce/uploads/default.png';
                $heroCache = file_exists($heroImage) ? filemtime($heroImage) : time();
                ?>
                <img src="<?= $heroImage ?>?v=<?= $heroCache ?>" alt="Camera" class="hero-img" />
            </div>
        </div>
    </div>

    <!-- FEATURES -->
    <div class="container my-5">
        <div class="row g-4">
            <div class="col-lg-6 feature-box">
                <?php
                $feature1 = 'uploads/camera1.jpg';
                if (!file_exists($feature1)) $feature1 = 'uploads/camera1.png';
                $cache1 = file_exists($feature1) ? filemtime($feature1) : time();
                ?>
                <img src="<?= $feature1 ?>?v=<?= $cache1 ?>" alt="Feature 1" />
                <h4 class="mt-3 fw-bold">What you see, what you'll get!</h4>
                <p>All cameras are 100% tested and verified.</p>
            </div>

            <div class="col-lg-6 feature-box">
                <?php
                $feature2 = 'uploads/camera2.jpg';
                if (!file_exists($feature2)) $feature2 = 'uploads/camera2.png';
                $cache2 = file_exists($feature2) ? filemtime($feature2) : time();
                ?>
                <img src="<?= $feature2 ?>?v=<?= $cache2 ?>" alt="Feature 2" />
                <h4 class="mt-3 fw-bold">Honesty? That's Lensify here!</h4>
                <p>No fake specs. No hidden issues. Just transparency.</p>
            </div>
        </div>
    </div>


    <div class="container my-5">
        <?php
        $banner = 'uploads/camera2.jpg';
        if (!file_exists($banner)) $banner = 'uploads/default.png';
        $bannerCache = file_exists($banner) ? filemtime($banner) : time();
        ?>
        <img src="<?= $banner ?>?v=<?= $bannerCache ?>" alt="Banner" class="banner-img" />
    </div>


    <div class="container my-5">
        <h3 class="fw-bold text-center mb-4">Featured Products</h3>
        <div class="row g-4">

        <?php while ($row = $products->fetch_assoc()): 
            $id = $row['item_id'];

            $title = htmlspecialchars($row['description']);
            $short = htmlspecialchars($row['short_description']);
            $price = number_format($row['sell_price'], 2);
            $stock = (int)($row['quantity'] ?? 0);

            $img = !empty($row['image_path']) ? 'uploads/' . basename($row['image_path']) : 'uploads/default.png';
            $cache = file_exists($img) ? filemtime($img) : time();
        ?>

            <div class="col-lg-4 col-md-6">
                <div class="card product-card p-3 shadow-sm text-center" style="border-radius:12px;">
                    <a href="/lensify/e-commerce/product_details.php?id=<?= $id ?>" style="text-decoration:none; color:black;">
                        <img src="<?= $img ?>?v=<?= $cache ?>" class="rounded mb-2" alt="<?= $title ?>">

                        <h5 class="mt-2 fw-bold"><?= $title ?></h5>
                        <p class="text-muted" style="height:40px; overflow:hidden;"><?= $short ?></p>

                        <?php if ($stock > 0): ?>
                            <strong class="d-block mb-2">₱<?= $price ?></strong>
                        <?php else: ?>
                            <strong class="d-block mb-2 text-danger">OUT OF STOCK</strong>
                        <?php endif; ?>
                    </a>
                    <a href="/lensify/e-commerce/product_details.php?id=<?= $id ?>" class="btn btn-dark w-100 mt-2" style="border-radius:6px;">
                        View
                    </a>
                </div>
            </div>

        <?php endwhile; ?>

        </div>
    </div>
</div>

<?php include("includes/footer.php");?>

<!-- Bootstrap JavaScript is already included in header.php, no need to include again -->
</body>
</html>