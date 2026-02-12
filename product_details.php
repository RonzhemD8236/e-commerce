<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'customer') {
    $_SESSION['auth_error'] = 'Please log in as admin to access this page.';
    header("Location: ./user/login.php");
    exit();
}
include('./includes/header.php');
include('./includes/config.php');

$bgPath = __DIR__ . '/uploads/login-bg.jpeg';

if (is_dir(__DIR__ . '/uploads')) {
    $files = scandir(__DIR__ . '/uploads');
}

$scheme = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'];
$baseUrl = $scheme . '://' . $host . '/lensify/e-commerce/';

if (file_exists('./review/review_functions.php')) {
    include('./review/review_functions.php');
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "<script>alert('No product selected.'); window.location.href='index.php';</script>";
    exit;
}

$itemId = intval($_GET['id']);

$sql = "
    SELECT i.*, s.quantity AS stock 
    FROM item i
    LEFT JOIN stock s ON i.item_id = s.item_id
    WHERE i.item_id = ?
    LIMIT 1
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $itemId);
$stmt->execute();
$result = $stmt->get_result();

if (!$result || mysqli_num_rows($result) == 0) {
    echo "<script>alert('Product not found.'); window.location.href='index.php';</script>";
    exit;
}

$row = mysqli_fetch_assoc($result);

$images = array();

if (!empty($row['image_path'])) {
    $cleanPath = stripslashes($row['image_path']);
    $decoded = json_decode($cleanPath, true);
    
    if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
        $images = $decoded;
    } else {
        $images = array($cleanPath);
    }
}

if (empty($images)) {
    $images = array('https://via.placeholder.com/400x300?text=No+Image');
}

$processedImages = array();
foreach ($images as $img) {
    if (empty(trim($img))) {
        continue;
    }
    
    if (strpos($img, 'http://') === 0 || strpos($img, 'https://') === 0) {
        $processedImages[] = $img;
        continue;
    }
    
    $img = ltrim($img, '/');
    $fullUrl = $baseUrl . $img;
    $processedImages[] = $fullUrl;
}

if (empty($processedImages)) {
    $processedImages = array('https://via.placeholder.com/400x300?text=Image+Not+Found');
}

$images = $processedImages;

$stock = (int)$row['stock'];
$inStock = $stock > 0;

$reviews = array();
$avgRating = 0;
$totalReviews = 0;
$canReview = false;
$userOrder = null;
$userExistingReview = null;

if (function_exists('getProductReviews')) {
    $reviews = getProductReviews($conn, $itemId);
    $ratingData = getAverageRating($conn, $itemId);
    $avgRating = $ratingData['avg_rating'] ? round($ratingData['avg_rating'], 1) : 0;
    $totalReviews = $ratingData['total_reviews'];
    
    $currentCustomerId = 0;
    if (isset($_SESSION['customer_id'])) {
        $currentCustomerId = $_SESSION['customer_id'];
    } elseif (isset($_SESSION['user_id']) && function_exists('getCustomerIdFromUserId')) {
        $currentCustomerId = getCustomerIdFromUserId($conn, $_SESSION['user_id']);
        if ($currentCustomerId > 0) {
            $_SESSION['customer_id'] = $currentCustomerId;
        }
    }
    
    if ($currentCustomerId > 0) {
        $userOrder = canCustomerReview($conn, $currentCustomerId, $itemId);
        $canReview = $userOrder !== false && $userOrder !== null;
        
        if ($canReview) {
            foreach ($reviews as $review) {
                if ($review['customer_id'] == $currentCustomerId) {
                    $userExistingReview = $review;
                    break;
                }
            }
        }
    }
}
?>

<style>
@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes slideInLeft {
    from {
        opacity: 0;
        transform: translateX(-30px);
    }
    to {
        opacity: 1;
        transform: translateX(0);
    }
}

@keyframes slideInRight {
    from {
        opacity: 0;
        transform: translateX(30px);
    }
    to {
        opacity: 1;
        transform: translateX(0);
    }
}

@keyframes pulse {
    0%, 100% {
        opacity: 1;
    }
    50% {
        opacity: 0.7;
    }
}

@keyframes shimmer {
    0% {
        background-position: -1000px 0;
    }
    100% {
        background-position: 1000px 0;
    }
}

@keyframes float {
    0%, 100% {
        transform: translateY(0px);
    }
    50% {
        transform: translateY(-10px);
    }
}

body {
    background: #000000;  
    color: #ffffff;  
    margin: 0;
    padding: 0;
    font-family: Arial, sans-serif;
}

body::after,
body::before {
    display: none;
}

.main-content {
    padding-top: 150px;
    margin-top: 20px;
    min-height: 100vh;
    position: relative;
    z-index: 1;
}

.main-content::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: url('../uploads/login-bg.jpeg') no-repeat center center;
    background-size: cover;
    z-index: 0;
}

.product-section {
    position: relative;
    z-index: 2;
    animation: fadeIn 0.6s ease;
}

.product-container {
    background: rgba(0, 0, 0, 0.85);  
    backdrop-filter: blur(10px);
    border-radius: 20px;
    padding: 40px;
    box-shadow: 0 20px 60px rgba(255, 255, 255, 0.05);
    border: 1px solid rgba(255, 255, 255, 0.1);
    margin-bottom: 40px;
    color: #ffffff;
    position: relative;
    overflow: hidden;
}

.product-container::before {
    content: '';
    position: absolute;
    top: -2px;
    left: -2px;
    right: -2px;
    bottom: -2px;
    background: linear-gradient(135deg, #667eea, #764ba2);
    border-radius: 20px;
    z-index: -1;
    opacity: 0;
    transition: opacity 0.4s ease;
}

.product-container:hover::before {
    opacity: 0.3;
}

.product-container h2,
.product-container h3,
.product-container h4,
.product-container h5,
.product-container p,
.product-container strong,
.product-container span,
.product-container li {
    color: #ffffff;
}

.image-slider {
    background: rgba(255, 255, 255, 0.05) !important;
    border: 2px solid rgba(255, 255, 255, 0.1) !important;
    border-radius: 15px !important;
    overflow: hidden;
    position: relative;
    animation: slideInLeft 0.8s ease;
}

.image-slider::before {
    content: '';
    position: absolute;
    top: -2px;
    left: -2px;
    right: -2px;
    bottom: -2px;
    background: linear-gradient(135deg, #667eea, #764ba2);
    border-radius: 15px;
    z-index: -1;
    opacity: 0;
    transition: opacity 0.4s ease;
}

.image-slider:hover::before {
    opacity: 0.5;
}

.slider-image {
    transition: transform 0.5s ease, opacity 0.5s ease !important;
}

.image-slider:hover .slider-image {
    transform: scale(1.05);
}

.slider-btn {
    background: rgba(102, 126, 234, 0.3) !important;
    color: #ffffff !important;
    transition: all 0.3s ease;
    backdrop-filter: blur(10px);
}

.slider-btn:hover {
    background: linear-gradient(135deg, #667eea, #764ba2) !important;
    transform: scale(1.1);
}

.image-counter {
    background: rgba(102, 126, 234, 0.4) !important;
    color: #ffffff !important;
    backdrop-filter: blur(10px);
}

.product-info-section {
    animation: slideInRight 0.8s ease;
}

.btn-primary {
    position: relative;
    z-index: 999 !important;
    pointer-events: auto !important;
    cursor: pointer !important;
}

.main-content::before {
    pointer-events: none !important;
}

.row {
    position: relative;
    z-index: 2;
}

.btn-success,
.btn-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
    color: #ffffff !important;
    border: none !important;
    padding: 12px 30px;
    border-radius: 10px;
    font-weight: 600;
    transition: all 0.3s ease;
    box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
    position: relative;
    overflow: hidden;
}

.btn-success::before,
.btn-primary::before {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    width: 0;
    height: 0;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.3);
    transform: translate(-50%, -50%);
    transition: width 0.6s, height 0.6s;
}

.btn-success:hover::before,
.btn-primary:hover::before {
    width: 300px;
    height: 300px;
}

.btn-success:hover,
.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 25px rgba(102, 126, 234, 0.6);
}

.stock-badge {
    display: inline-block;
    padding: 8px 20px;
    border-radius: 20px;
    font-weight: 600;
    font-size: 14px;
    animation: pulse 2s infinite;
}

.stock-badge.in-stock {
    background: linear-gradient(135deg, #4caf50, #45a049);
    color: white;
}

.stock-badge.low-stock {
    background: linear-gradient(135deg, #ff9800, #f57c00);
    color: white;
}

.stock-badge.out-stock {
    background: linear-gradient(135deg, #f44336, #d32f2f);
    color: white;
}

.quantity-selector {
    display: flex;
    align-items: center;
    gap: 15px;
    margin-top: 15px;
}

.qty-btn {
    background: rgba(102, 126, 234, 0.3);
    border: none;
    color: white;
    width: 35px;
    height: 35px;
    border-radius: 50%;
    cursor: pointer;
    transition: all 0.3s ease;
    font-size: 18px;
    font-weight: bold;
}

.qty-btn:hover {
    background: linear-gradient(135deg, #667eea, #764ba2);
    transform: scale(1.1);
}

.qty-input {
    width: 80px;
    text-align: center;
    background: rgba(255, 255, 255, 0.1);
    border: 2px solid rgba(102, 126, 234, 0.3);
    color: white;
    padding: 8px;
    border-radius: 10px;
    font-size: 16px;
    font-weight: 600;
}

.qty-input:focus {
    outline: none;
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.2);
}

.product-price {
    font-size: 2.5rem;
    font-weight: 800;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    margin: 20px 0;
    animation: float 3s ease-in-out infinite;
}

.rating-display {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 15px;
    background: rgba(255, 193, 7, 0.1);
    border-radius: 10px;
    border: 1px solid rgba(255, 193, 7, 0.3);
    margin-bottom: 20px;
}

.reviews-section {
    background: rgba(30, 30, 30, 0.9);
    backdrop-filter: blur(10px);
    border-radius: 20px;
    padding: 40px;
    box-shadow: 0 20px 60px rgba(255, 255, 255, 0.05);
    border: 1px solid rgba(255, 255, 255, 0.1);
    color: #ffffff;
    display: flex;
    flex-direction: column;
    animation: fadeIn 0.8s ease 0.2s both;
}

.review-item {
    background: #1a1a1a;
    border: 1px solid #333;
    border-radius: 12px;
    color: #ffffff;
    display: flex;
    flex-direction: column;
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
}

.review-item::before {
    content: '';
    position: absolute;
    top: -2px;
    left: -2px;
    right: -2px;
    bottom: -2px;
    background: linear-gradient(135deg, #667eea, #764ba2);
    border-radius: 12px;
    z-index: -1;
    opacity: 0;
    transition: opacity 0.3s ease;
}

.review-item:hover::before {
    opacity: 0.5;
}

.review-item .card-body {
    display: flex;
    flex-direction: column;
    flex-grow: 1;
}

.review-item:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);
}

#reviewsList {
    display: flex;
    flex-direction: column;
    align-items: stretch;
    justify-content: flex-end;
}

.review-item .card-body {
    display: flex;
    flex-direction: column;
    flex-grow: 1;
    justify-content: space-between;
}

.reviews-section .form-control {
    color: #ffffff;
    background: #000000;
    border: 2px solid rgba(102, 126, 234, 0.3);
    transition: all 0.3s ease;
}

.reviews-section .form-control:focus {
    color: #ffffff;
    background: #000000;
    border-color: #667eea;
    box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.2);
}

.star-rating,
.star-input .star {
    color: #ffc107;
    transition: all 0.2s ease;
}

.star-input .star:hover {
    transform: scale(1.2);
}

.alert {
    border-radius: 12px;
    border: none;
    backdrop-filter: blur(10px);
    color: #ffffff;
    background: rgba(102, 126, 234, 0.2);
    border: 1px solid rgba(102, 126, 234, 0.4);
}

.main-content,
.container,
.row {
    background: transparent !important;
}

#reviewFormContainer {
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    z-index: 9999;
    max-width: 600px;
    width: 90%;
    max-height: 80vh;
    overflow-y: auto;
    background: rgba(30, 30, 30, 0.98);
    border-radius: 20px;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.8);
    border: 2px solid rgba(102, 126, 234, 0.5);
    animation: fadeIn 0.3s ease;
}

#reviewFormContainer::before {
    content: '';
    position: fixed;
    top: 0;
    left: 0;
    width: 100vw;
    height: 100vh;
    background: rgba(0, 0, 0, 0.7);
    z-index: -1;
    backdrop-filter: blur(5px);
}

#reviewFormContainer .card-body {
    padding: 30px;
    color: #ffffff;
}

#reviewFormContainer .form-control {
    background: #000000;
    color: #ffffff;
    border: 2px solid rgba(102, 126, 234, 0.3);
}

#reviewFormContainer .form-control:focus {
    background: #000000;
    color: #ffffff;
    border-color: #667eea;
    box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.2);
}

.review-form-close {
    position: absolute;
    top: 15px;
    right: 15px;
    background: rgba(102, 126, 234, 0.3);
    border: none;
    color: #ffffff;
    width: 35px;
    height: 35px;
    border-radius: 50%;
    cursor: pointer;
    font-size: 20px;
    line-height: 1;
    transition: all 0.3s ease;
}

.review-form-close:hover {
    background: linear-gradient(135deg, #667eea, #764ba2);
    transform: rotate(90deg);
}

.verified-badge {
    background: linear-gradient(135deg, #4caf50, #45a049);
    color: white;
    padding: 4px 12px;
    border-radius: 15px;
    font-size: 12px;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 5px;
}

.verified-badge::before {
    content: '✓';
    font-weight: bold;
}

.thumbnail-grid {
    display: flex;
    gap: 10px;
    margin-top: 15px;
    flex-wrap: wrap;
}

.thumbnail {
    width: 80px;
    height: 80px;
    object-fit: cover;
    border-radius: 10px;
    cursor: pointer;
    border: 2px solid rgba(255, 255, 255, 0.2);
    transition: all 0.3s ease;
    opacity: 0.6;
}

.thumbnail:hover,
.thumbnail.active {
    border-color: #667eea;
    opacity: 1;
    transform: scale(1.05);
}

@media (max-width: 768px) {
    .product-container {
        padding: 20px;
    }
    
    .product-price {
        font-size: 2rem;
    }
    
    .quantity-selector {
        flex-direction: column;
        align-items: stretch;
    }
}

 

.main-content {
    padding-top: 80px;  
    margin-top: 0;  
    min-height: 100vh;
    position: relative;
    z-index: 1;
}

 
.main-content::before {
    content: '';
    position: fixed;  
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: url('../uploads/login-bg.jpeg') no-repeat center center;
    background-size: cover;
    z-index: -1;  
    opacity: 0.3;  
}

 
.product-container-wrapper {
    max-width: 1200px;
    margin: 0 auto;
    padding: 50px 20px 100px 20px;
    margin-top: 0;  
}

 
header,
.navbar,
.main-header {
    position: relative;
    z-index: 1000 !important;
}

 
.spacing-fix {
    margin-bottom: 40px;  
}

 
body {
    padding-top: 0;  
}

 
body.has-fixed-header {
    padding-top: 70px;  
}

body.has-fixed-header .main-content {
    padding-top: 30px;
}
</style>
<br><br>
<div class="main-content">
<div class="container" style="max-width: 1200px; margin: 0 auto; padding: 50px 20px 100px 20px; margin-top: 450px;">
    <div class="product-section">
    <div class="row" style="margin-bottom: 60px; align-items: flex-end;">
        <div class="col-md-6 pe-4 d-flex align-items-end">
           <div style="width: 100%;">
               <div class="image-slider position-relative" style="width:100%; min-height:400px; height:auto; overflow:hidden; border-radius:10px; background:transparent;">
                    <?php if (!empty($images)): ?>
                        <?php foreach ($images as $index => $img): ?>
                            <img src="<?php echo htmlspecialchars($img); ?>" 
                                 class="slider-image" 
                                 alt="Product Image <?php echo $index + 1; ?>"
                                 style="width:100%; height:100%; object-fit:contain; position:absolute; top:0; left:0; transition: opacity 0.5s ease; opacity:<?php echo $index === 0 ? 1 : 0; ?>;"
                                 onerror="this.style.display='none';">
                        <?php endforeach; ?>
                        
                        <?php if (count($images) > 1): ?>
                            <button class="slider-btn prev-btn" onclick="changeSlide(-1)" style="position:absolute; left:10px; top:50%; transform:translateY(-50%); background:rgba(0,0,0,0.5); color:white; border:none; border-radius:50%; width:40px; height:40px; cursor:pointer; font-size:18px; z-index:10;">
                                &lsaquo;
                            </button>
                            <button class="slider-btn next-btn" onclick="changeSlide(1)" style="position:absolute; right:10px; top:50%; transform:translateY(-50%); background:rgba(0,0,0,0.5); color:white; border:none; border-radius:50%; width:40px; height:40px; cursor:pointer; font-size:18px; z-index:10;">
                                &rsaquo;
                            </button>
                            
                            <div class="image-counter" style="position:absolute; bottom:10px; left:50%; transform:translateX(-50%); background:rgba(0,0,0,0.6); color:white; padding:5px 15px; border-radius:20px; font-size:14px; z-index:10;">
                                <span id="currentImageNum">1</span> / <?php echo count($images); ?>
                            </div>
                        <?php endif; ?>
                        
                        <div class="no-image-text" style="position:absolute; top:50%; left:50%; transform:translate(-50%, -50%); display:none; text-align:center; color:#999;">
                            <p>Image not available</p>
                        </div>
                    <?php else: ?>
                        <p style="position:absolute; top:50%; left:50%; transform:translate(-50%, -50%);">No images available</p>
                    <?php endif; ?>
                </div>
                
                <?php if (count($images) > 1): ?>
                <div class="thumbnail-grid">
                    <?php foreach ($images as $index => $img): ?>
                        <img src="<?php echo htmlspecialchars($img); ?>" 
                             class="thumbnail <?php echo $index === 0 ? 'active' : ''; ?>" 
                             onclick="goToSlide(<?php echo $index; ?>)"
                             alt="Thumbnail <?php echo $index + 1; ?>"
                             onerror="this.style.display='none';">
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
           </div>
        </div>

        <div class="col-md-6 ps-4 d-flex align-items-end">
            <div class="product-info-section" style="width: 100%;">
                <h2 style="font-size: 1.75rem; margin-bottom: 15px;"><?php echo htmlspecialchars($row['description']); ?></h2>
            
            <?php if ($totalReviews > 0): ?>
            <div class="rating-display">
                <div class="d-flex align-items-center">
                    <div class="star-rating" style="font-size: 18px; color: #ffc107;">
                        <?php for($i = 1; $i <= 5; $i++): ?>
                            <?php if ($i <= floor($avgRating)): ?>
                                &#9733;
                            <?php elseif ($i - $avgRating < 1 && $avgRating > floor($avgRating)): ?>
                                &#11089;
                            <?php else: ?>
                                &#9734;
                            <?php endif; ?>
                        <?php endfor; ?>
                    </div>
                    <span class="ms-2" style="font-size: 14px;">
                        <strong><?php echo $avgRating; ?></strong> out of 5 
                        (<?php echo $totalReviews; ?> <?php echo $totalReviews == 1 ? 'review' : 'reviews'; ?>)
                    </span>
                </div>
            </div>
            <?php endif; ?>

            <?php if (!empty($row['short_description'])): ?>
                <p style="margin-bottom: 10px; font-size: 14px;"><strong>Summary:</strong> <?php echo htmlspecialchars($row['short_description']); ?></p>
            <?php endif; ?>

            <?php if (!empty($row['specifications'])): ?>
                <p style="margin-bottom: 15px; font-size: 14px;"><strong>Specifications:</strong><br><?php echo nl2br(htmlspecialchars($row['specifications'])); ?></p>
            <?php endif; ?>

            <div class="product-price">&#8369;<?php echo number_format($row['sell_price'], 2); ?></div>

            <p style="margin-bottom: 15px; font-size: 14px;"><strong>Stock Status:</strong>
                <?php if ($inStock): ?>
                    <?php if ($stock <= 5): ?>
                        <span class="stock-badge low-stock">Low Stock: <?php echo $stock; ?> left</span>
                    <?php else: ?>
                        <span class="stock-badge in-stock" id="availableStock">In Stock: <?php echo $stock; ?> available</span>
                    <?php endif; ?>
                <?php else: ?>
                    <span class="stock-badge out-stock">OUT OF STOCK</span>
                <?php endif; ?>
            </p>

            <?php if ($inStock): ?>
            <form id="addToCartForm" class="mt-2" onsubmit="return false;">
                <input type="hidden" name="type" value="add">
                <input type="hidden" name="item_id" value="<?php echo $row['item_id']; ?>">
                <input type="hidden" name="item_name" value="<?php echo htmlspecialchars($row['description']); ?>">
                <input type="hidden" name="item_price" value="<?php echo $row['sell_price']; ?>">

                <div class="quantity-selector">
                    <label style="font-size: 14px; font-weight: 600;">Quantity:</label>
                    <button type="button" class="qty-btn" onclick="decreaseQty()">-</button>
                    <input type="number" name="item_qty" id="quantity" value="1" min="1" max="<?php echo $stock; ?>" class="qty-input">
                    <button type="button" class="qty-btn" onclick="increaseQty()">+</button>
                </div>
                <br>
                <button type="button" onclick="submitCart()" class="btn btn-success">Add to Cart</button>
            </form>
            <?php endif; ?>
            </div>
        </div>
    </div>
    </div>

    <?php if (function_exists('getProductReviews')): ?>
    <div class="row" style="margin-top: 60px; margin-bottom: 100px;">
        <div class="col-12 d-flex flex-column">
            <div class="mt-auto">
                <h3>Customer Reviews</h3>
                <hr>
            </div>
            
            <?php 
            $isUserLoggedIn = isset($_SESSION['customer_id']) || isset($_SESSION['user_id']);
            ?>
            
            <?php if ($isUserLoggedIn): ?>
                <?php if ($canReview): ?>
                    <div class="mt-auto">
                        <button class="btn btn-primary mb-4" onclick="toggleReviewForm()">
                            <?php echo $userExistingReview ? 'Edit Your Review' : 'Write a Review'; ?>
                        </button>
                    </div>
                    
                    <div id="reviewFormContainer" style="display:none; margin-bottom: 100px;" class="card mb-4">
                        <button class="review-form-close" onclick="toggleReviewForm()">&times;</button>
                        <div class="card-body" style="padding: 30px;">
                            <h5 style="margin-bottom: 25px;"><?php echo $userExistingReview ? 'Edit Your Review' : 'Write Your Review'; ?></h5>
                            <form id="reviewForm" onsubmit="return false;">
                                <input type="hidden" name="item_id" value="<?php echo $itemId; ?>">
                                <?php if ($userOrder && isset($userOrder['orderinfo_id'])): ?>
                                    <input type="hidden" name="orderinfo_id" value="<?php echo $userOrder['orderinfo_id']; ?>">
                                <?php endif; ?>
                                <?php if ($userExistingReview): ?>
                                    <input type="hidden" name="review_id" value="<?php echo $userExistingReview['review_id']; ?>">
                                <?php endif; ?>
                                
                                <div class="mb-4">
                                    <label class="form-label">Rating <span class="text-danger">*</span></label>
                                    <div class="star-input" id="starRating">
                                        <?php for($i = 1; $i <= 5; $i++): ?>
                                            <span class="star" data-rating="<?php echo $i; ?>" style="font-size:30px; cursor:pointer; color:#ddd;">&#9734;</span>
                                        <?php endfor; ?>
                                    </div>
                                    <input type="hidden" name="rating" id="ratingValue" value="<?php echo $userExistingReview ? $userExistingReview['rating'] : ''; ?>">
                                </div>
                                
                                <div class="mb-4">
                                    <label class="form-label">Review Title <span class="text-danger">*</span></label>
                                    <input type="text" name="review_title" class="form-control" maxlength="200" 
                                        value="<?php echo $userExistingReview ? htmlspecialchars($userExistingReview['review_title']) : ''; ?>">
                                </div>

                                <div class="mb-4">
                                    <label class="form-label">Your Review <span class="text-danger">*</span></label>
                                    <textarea name="review_text" class="form-control" rows="5"><?php echo $userExistingReview ? htmlspecialchars($userExistingReview['review_text']) : ''; ?></textarea>
                                    <small class="text-muted">Inappropriate words will be automatically masked.</small>
                                </div>
                                
                                <div style="margin-top: 30px; padding-bottom: 20px;">
                                    <button type="button" onclick="submitReview()" class="btn btn-success">Submit Review</button>
                                    <button type="button" class="btn btn-secondary" onclick="toggleReviewForm()">Cancel</button>
                                </div>
                            </form>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info">
                        You can only review products you have purchased and received.
                    </div>
                <?php endif; ?>
            <?php else: ?>
                <div class="alert alert-warning">
                    Please <a href="/lensify/e-commerce/user/login.php" style="color: #667eea; font-weight: 600;">login</a> to write a review.
                </div>
            <?php endif; ?>
            
            <?php if (empty($reviews)): ?>
                <p class="text-muted" style="margin-bottom: 100px;">No reviews yet. Be the first to review this product!</p>
            <?php else: ?>
                <div id="reviewsList" style="margin-bottom: 100px;">
                    <?php foreach ($reviews as $review): ?>
                        <div class="card mb-3 review-item">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div>
                                        <h5 class="card-title"><?php echo htmlspecialchars($review['review_title']); ?></h5>
                                        <div class="star-rating mb-2" style="color: #ffc107;">
                                            <?php for($i = 1; $i <= 5; $i++): ?>
                                                <?php echo $i <= $review['rating'] ? '&#9733;' : '&#9734;'; ?>
                                            <?php endfor; ?>
                                        </div>
                                    </div>
                                    <?php if (isset($_SESSION['customer_id']) && $_SESSION['customer_id'] == $review['customer_id']): ?>
                                        <button class="btn btn-sm btn-outline-danger align-self-start" onclick="deleteReview(<?php echo $review['review_id']; ?>)">Delete</button>
                                    <?php elseif (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $review['customer_id']): ?>
                                        <button class="btn btn-sm btn-outline-danger align-self-start" onclick="deleteReview(<?php echo $review['review_id']; ?>)">Delete</button>
                                    <?php endif; ?>
                                </div>
                                <p class="card-text flex-grow-1"><?php echo nl2br(htmlspecialchars($review['review_text'])); ?></p>
                                <div class="text-muted small mt-auto">
                                    <strong><?php echo htmlspecialchars($review['customer_name']); ?></strong>
                                    <?php if ($review['is_verified_purchase']): ?>
                                        <span class="verified-badge ms-2">Verified Purchase</span>
                                    <?php endif; ?>
                                    <br>
                                    <?php echo date('F j, Y', strtotime($review['created_at'])); ?>
                                    <?php if ($review['updated_at'] != $review['created_at']): ?>
                                        <em>(edited)</em>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>
</div>
</div>

<script type="text/javascript">
var currentSlide = 0;
var imgs = document.querySelectorAll('.slider-image');
var visibleImages = [];
for (var i = 0; i < imgs.length; i++) {
    if (imgs[i].style.display !== 'none') {
        visibleImages.push(imgs[i]);
    }
}

function showSlide(n) {
    if (visibleImages.length === 0) {
        var fallback = document.querySelector('.no-image-text');
        if (fallback) fallback.style.display = 'block';
        return;
    }
    
    for (var i = 0; i < visibleImages.length; i++) {
        visibleImages[i].style.opacity = 0;
    }
    
    if (n >= visibleImages.length) currentSlide = 0;
    if (n < 0) currentSlide = visibleImages.length - 1;
    
    visibleImages[currentSlide].style.opacity = 1;
    
    var counter = document.getElementById('currentImageNum');
    if (counter) counter.textContent = currentSlide + 1;
    
    var thumbnails = document.querySelectorAll('.thumbnail');
    thumbnails.forEach(function(thumb, index) {
        if (index === currentSlide) {
            thumb.classList.add('active');
        } else {
            thumb.classList.remove('active');
        }
    });
}

function changeSlide(direction) {
    currentSlide += direction;
    showSlide(currentSlide);
}

function goToSlide(index) {
    currentSlide = index;
    showSlide(currentSlide);
}

if (visibleImages.length > 0) {
    showSlide(0);
}

var sliderBtns = document.querySelectorAll('.slider-btn');
for (var i = 0; i < sliderBtns.length; i++) {
    sliderBtns[i].onmouseover = function() {
        this.style.background = 'rgba(0,0,0,0.8)';
    };
    sliderBtns[i].onmouseout = function() {
        this.style.background = 'rgba(0,0,0,0.5)';
    };
}

<?php if ($inStock): ?>
function increaseQty() {
    var qtyInput = document.getElementById("quantity");
    var maxStock = parseInt(qtyInput.max);
    var currentQty = parseInt(qtyInput.value);
    
    if (currentQty < maxStock) {
        qtyInput.value = currentQty + 1;
    }
}

function decreaseQty() {
    var qtyInput = document.getElementById("quantity");
    var currentQty = parseInt(qtyInput.value);
    
    if (currentQty > 1) {
        qtyInput.value = currentQty - 1;
    }
}

function submitCart() {
    var form = document.getElementById("addToCartForm");
    var qtyInput = document.getElementById("quantity");
    var qty = parseInt(qtyInput.value);
    var maxStock = <?php echo $stock; ?>;
    
    if (isNaN(qty) || qty < 1) {
        alert("Please enter a valid quantity (minimum 1).");
        return;
    }
    
    if (qty > maxStock) {
        alert("Quantity cannot exceed available stock (" + maxStock + ").");
        return;
    }
    
    var formData = new FormData(form);
    var xhr = new XMLHttpRequest();
    xhr.open("POST", "/lensify/e-commerce/cart/cart_update.php", true);
    xhr.onload = function() {
        if (xhr.status === 200) {
            try {
                var data = JSON.parse(xhr.responseText);
                if (data.success) {
                    alert("Item added to cart!");
                    var stockSpan = document.getElementById("availableStock");
                    if (stockSpan) {
                        if (data.newStock <= 5) {
                            stockSpan.className = 'stock-badge low-stock';
                            stockSpan.textContent = 'Low Stock: ' + data.newStock + ' left';
                        } else {
                            stockSpan.textContent = 'In Stock: ' + data.newStock + ' available';
                        }
                    }
                    qtyInput.max = data.newStock;
                    if (data.newStock == 0) {
                        var qtySelector = document.querySelector('.quantity-selector');
                        if (qtySelector) qtySelector.remove();
                        var submitBtn = document.querySelector("#addToCartForm button");
                        if (submitBtn) submitBtn.remove();
                        if (stockSpan) {
                            stockSpan.className = 'stock-badge out-stock';
                            stockSpan.textContent = 'OUT OF STOCK';
                        }
                    }
                } else {
                    alert(data.message || "Failed to add item.");
                }
            } catch(e) {
                alert("Error processing response.");
            }
        }
    };
    xhr.onerror = function() {
        alert("Error adding to cart.");
    };
    xhr.send(formData);
}
<?php endif; ?>

<?php if (function_exists('getProductReviews')): ?>

function toggleReviewForm() {
    var form = document.getElementById('reviewFormContainer');
    
    if (!form) {
        console.error('Review form container not found');
        return;
    }
    
    if (form.style.display === 'none' || form.style.display === '') {
        form.style.display = 'block';
        console.log('Review form opened');
    } else {
        form.style.display = 'none';
        console.log('Review form closed');
    }
}

document.addEventListener('DOMContentLoaded', function() {
    var stars = document.querySelectorAll('#starRating .star');
    var ratingInput = document.getElementById('ratingValue');
    
    if (!stars.length) {
        console.log('Star rating not available (user may not be able to review)');
        return;
    }
    
    var existingRating = ratingInput ? ratingInput.value : 0;
    if (existingRating > 0) {
        updateStars(existingRating);
    }
    
    stars.forEach(function(star) {
        star.addEventListener('click', function() {
            var rating = this.getAttribute('data-rating');
            ratingInput.value = rating;
            updateStars(rating);
            console.log('Rating selected:', rating);
        });
        
        star.addEventListener('mouseover', function() {
            var rating = this.getAttribute('data-rating');
            highlightStars(rating);
        });
    });
    
    var starRating = document.getElementById('starRating');
    if (starRating) {
        starRating.addEventListener('mouseout', function() {
            updateStars(ratingInput.value || 0);
        });
    }
});

function highlightStars(rating) {
    var stars = document.querySelectorAll('#starRating .star');
    stars.forEach(function(star, index) {
        if ((index + 1) <= rating) {
            star.style.color = '#ffc107';
            star.innerHTML = '&#9733;';
        } else {
            star.style.color = '#ddd';
            star.innerHTML = '&#9734;';
        }
    });
}

function updateStars(rating) {
    highlightStars(rating);
}

function submitReview() {
    var ratingInput = document.getElementById('ratingValue');
    var form = document.getElementById('reviewForm');
    
    if (!form) {
        alert('Review form not found');
        return;
    }
    
    if (!ratingInput || !ratingInput.value) {
        alert('Please select a rating.');
        return;
    }
    
    var reviewTitle = form.querySelector('[name="review_title"]');
    var reviewText = form.querySelector('[name="review_text"]');
    
    if (!reviewTitle || !reviewTitle.value.trim()) {
        alert('Please enter a review title.');
        return;
    }
    
    if (reviewTitle.value.trim().length > 200) {
        alert('Review title must be 200 characters or less.');
        return;
    }
    
    if (!reviewText || !reviewText.value.trim()) {
        alert('Please enter your review text.');
        return;
    }
    
    if (reviewText.value.trim().length < 10) {
        alert('Review must be at least 10 characters long.');
        return;
    }
    
    if (reviewText.value.trim().length > 2000) {
        alert('Review must be 2000 characters or less.');
        return;
    }
    
    console.log('Submitting review...');
    
    var formData = new FormData(form);
    var xhr = new XMLHttpRequest();
    xhr.open("POST", "review/submit_review.php", true);
    
    xhr.onload = function() {
        console.log('Response status:', xhr.status);
        console.log('Response text:', xhr.responseText);
        
        if (xhr.status === 200) {
            try {
                var data = JSON.parse(xhr.responseText);
                if (data.success) {
                    alert(data.message);
                    location.reload();
                } else {
                    alert(data.message || 'Error submitting review');
                }
            } catch(e) {
                console.error('Parse error:', e);
                console.log('Raw response:', xhr.responseText);
                alert('Error submitting review. Please try again.');
            }
        } else {
            alert('Server error. Please try again.');
        }
    };
    
    xhr.onerror = function() {
        console.error('XHR error');
        alert('Error submitting review. Please check your connection.');
    };
    
    xhr.send(formData);
}

function deleteReview(reviewId) {
    if (!confirm('Are you sure you want to delete your review?')) {
        return;
    }
    
    console.log('Deleting review:', reviewId);
    
    var xhr = new XMLHttpRequest();
    xhr.open("POST", "review/delete_review.php", true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    
    xhr.onload = function() {
        console.log('Delete response:', xhr.responseText);
        
        if (xhr.status === 200) {
            try {
                var data = JSON.parse(xhr.responseText);
                if (data.success) {
                    alert(data.message);
                    location.reload();
                } else {
                    alert(data.message || 'Error deleting review');
                }
            } catch(e) {
                console.error('Parse error:', e);
                alert('Error deleting review.');
            }
        } else {
            alert('Server error. Please try again.');
        }
    };
    
    xhr.onerror = function() {
        console.error('XHR error');
        alert('Error deleting review. Please check your connection.');
    };
    
    xhr.send('review_id=' + reviewId);
}
<?php endif; ?>
</script>
<br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br>
<?php include('./includes/footer.php'); ?>