<?php
session_start();
include('./includes/header.php');
include('./includes/config.php');

// Debug background image path
echo "<!-- DEBUG: Checking background image path -->";
$bgPath = __DIR__ . '/uploads/login-bg.jpeg';
echo "<!-- Full path: $bgPath -->";
echo "<!-- File exists: " . (file_exists($bgPath) ? 'YES' : 'NO') . " -->";
echo "<!-- Current directory: " . __DIR__ . " -->";

// List all files in uploads directory
if (is_dir(__DIR__ . '/uploads')) {
    $files = scandir(__DIR__ . '/uploads');
    echo "<!-- Files in uploads: " . implode(', ', $files) . " -->";
}

// Determine the correct base URL
$scheme = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'];
$baseUrl = $scheme . '://' . $host . '/lensify/e-commerce/';
echo "<!-- Base URL: $baseUrl -->";

// Include review functions
if (file_exists('./review/review_functions.php')) {
    include('./review/review_functions.php');
}

// Check if product ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "<script>alert('No product selected.'); window.location.href='index.php';</script>";
    exit;
}

$itemId = intval($_GET['id']);

// Fetch product with stock
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

// Handle images
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

// Process each image to create full URLs
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

// Get reviews and ratings
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
body {
    background: #000000; /* solid black background */
    color: #ffffff; /* default text color white */
    margin: 0;
    body {
    padding-top: 160px; /* adjust to match your header's actual height */
    font-family: Arial, sans-serif;
}

/* Optional overlay removed since background is already black */
body::after,
body::before {
    display: none;
}
/* Make sure your header has the right z-index */
header, .navbar {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    z-index: 1000;
    background: #000; /* or whatever your header background is */
}

/* Ensure product section maintains its position when review form opens */
.product-section {
    position: relative;
    z-index: 2;
}

.product-container {
    background: rgba(0, 0, 0, 0.85); /* dark container */
    backdrop-filter: blur(10px);
    border-radius: 20px;
    padding: 40px;
    box-shadow: 0 20px 60px rgba(255, 255, 255, 0.05);
    border: 1px solid rgba(255, 255, 255, 0.1);
    margin-bottom: 40px;
    color: #ffffff; /* white text */
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
}

.slider-btn {
    background: rgba(255, 255, 255, 0.1) !important;
    color: #ffffff !important;
    transition: all 0.3s ease;
}

.slider-btn:hover {
    background: rgba(255, 255, 255, 0.25) !important;
}

.image-counter {
    background: rgba(255, 255, 255, 0.15) !important;
    color: #ffffff !important;
}

.btn-success,
.btn-primary {
    background: #222222 !important;
    color: #ffffff !important;
    border: none !important;
    padding: 12px 30px;
    border-radius: 10px;
    font-weight: 600;
    transition: all 0.3s ease;
    box-shadow: 0 4px 15px rgba(255, 255, 255, 0.1);
}

.btn-success:hover,
.btn-primary:hover {
    background: #444444 !important;
    transform: translateY(-2px);
    box-shadow: 0 6px 25px rgba(255, 255, 255, 0.15);
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
}

.review-item {
    background: #1a1a1a;
    border: 1px solid #333;
    border-radius: 12px;
    color: #ffffff;
    display: flex;
    flex-direction: column;
}

.review-item .card-body {
    display: flex;
    flex-direction: column;
    flex-grow: 1;
}

.review-item:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 20px rgba(255, 255, 255, 0.05);
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
    border-color: #555;
}

.reviews-section .form-control:focus {
    color: #ffffff;
    background: #000000;
    border-color: #ffc107;
    box-shadow: 0 0 0 4px rgba(255, 193, 7, 0.2);
}

.star-rating,
.star-input .star {
    color: #ffc107;
}

.alert {
    border-radius: 12px;
    border: none;
    backdrop-filter: blur(10px);
    color: #ffffff;
    background: rgba(50, 50, 50, 0.9);
}

.main-content,
.container,
.row {
    background: transparent !important;
}

.main-content {
    padding-top: 120px; /* Space for fixed header */
    margin-top: 0;
    min-height: 100vh;
    position: relative;
    z-index: 1;
}

.main-content {
    padding-top: 180px !important; /* increase until nothing overlaps */
}
.product-row {
    display: flex;
    gap: 40px;
    margin-bottom: 60px;
    align-items: stretch;
}

.product-image-col {
    flex: 0 0 50%;
    max-width: 50%;
}

.product-details-col {
    flex: 0 0 50%;
    max-width: 50%;
    display: flex;
    flex-direction: column;
    justify-content: flex-start;
}

.image-slider {
    width: 100%;
    min-height: 400px;
    height: 500px;
    position: relative;
    overflow: hidden;
    border-radius: 10px;
    background: rgba(255, 255, 255, 0.05) !important;
    border: 2px solid rgba(255, 255, 255, 0.1) !important;
}

.slider-image {
    width: 100%;
    height: 100%;
    object-fit: contain;
    position: absolute;
    top: 0;
    left: 0;
    transition: opacity 0.5s ease;
}

@media (max-width: 768px) {
    .product-row {
        flex-direction: column;
    }
    
    .product-image-col,
    .product-details-col {
        flex: 0 0 100%;
        max-width: 100%;
    }
}
</style>

<div class="main-content">
<div class="container" style="max-width: 1200px; margin: 1200 auto; padding: 50px 20px 100px 20px; margin-top: 400px;">
    <!-- Product Section -->
    <div class="product-row">
        <!-- Image Slider Column -->
        <div class="product-image-col">
           <div class="image-slider">
                <?php if (!empty($images)): ?>
                    <?php foreach ($images as $index => $img): ?>
                        <img src="<?php echo htmlspecialchars($img); ?>" 
                             class="slider-image" 
                             alt="Product Image <?php echo $index + 1; ?>"
                             style="opacity:<?php echo $index === 0 ? 1 : 0; ?>;"
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
        </div>

        <!-- Product Details Column -->
        <div class="product-details-col">
            <h2 style="font-size: 1.75rem; margin-bottom: 15px;"><?php echo htmlspecialchars($row['description']); ?></h2>
            
            <?php if ($totalReviews > 0): ?>
            <div class="mb-2">
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

            <h4 class="text-success" style="margin-bottom: 15px; font-size: 1.5rem;">&#8369;<?php echo number_format($row['sell_price'], 2); ?></h4>

            <p style="margin-bottom: 15px; font-size: 14px;"><strong>Available Stock:</strong>
                <?php if ($inStock): ?>
                    <span id="availableStock"><?php echo $stock; ?></span>
                <?php else: ?>
                    <span style="color:red; font-size:24px; font-weight:bold;">OUT OF STOCK</span>
                <?php endif; ?>
            </p>

            <?php if ($inStock): ?>
            <form id="addToCartForm" class="mt-2" onsubmit="return false;">
                <input type="hidden" name="type" value="add">
                <input type="hidden" name="item_id" value="<?php echo $row['item_id']; ?>">
                <input type="hidden" name="item_name" value="<?php echo htmlspecialchars($row['description']); ?>">
                <input type="hidden" name="item_price" value="<?php echo $row['sell_price']; ?>">

                <div class="mb-2">
                    <label style="font-size: 14px;">Quantity:</label>
                    <input type="number" name="item_qty" id="quantity"
                           value="1" class="form-control" style="width:100px; font-size: 14px;">
                </div>

                <button type="button" onclick="submitCart()" class="btn btn-success">Add to Cart</button>
            </form>
            <?php endif; ?>
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
                    Please <a href="/lensify/e-commerce/user/login.php">login</a> to write a review.
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
                                        <span class="badge bg-success ms-2">Verified Purchase</span>
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
}

function changeSlide(direction) {
    currentSlide += direction;
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
                    stockSpan.textContent = data.newStock;
                    qtyInput.max = data.newStock;
                    if (data.newStock == 0) {
                        if (qtyInput) qtyInput.remove();
                        var submitBtn = document.querySelector("#addToCartForm button");
                        if (submitBtn) submitBtn.remove();
                        stockSpan.outerHTML = '<span style="color:red; font-size:24px; font-weight:bold;">OUT OF STOCK</span>';
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
    var currentScrollPosition = window.pageYOffset || document.documentElement.scrollTop;
    
    if (form.style.display === 'none') {
        form.style.display = 'block';
        // Maintain scroll position to preserve padding between header and product
        window.scrollTo(0, currentScrollPosition);
    } else {
        form.style.display = 'none';
        // Maintain scroll position when closing
        window.scrollTo(0, currentScrollPosition);
    }
}

var stars = document.querySelectorAll('#starRating .star');
var ratingInput = document.getElementById('ratingValue');

<?php if ($userExistingReview): ?>
updateStars(<?php echo $userExistingReview['rating']; ?>);
<?php endif; ?>

for (var i = 0; i < stars.length; i++) {
    stars[i].onclick = function() {
        var rating = this.getAttribute('data-rating');
        ratingInput.value = rating;
        updateStars(rating);
    };
    
    stars[i].onmouseover = function() {
        var rating = this.getAttribute('data-rating');
        highlightStars(rating);
    };
}

document.getElementById('starRating').onmouseout = function() {
    updateStars(ratingInput.value);
};

function highlightStars(rating) {
    var stars = document.querySelectorAll('#starRating .star');
    for (var i = 0; i < stars.length; i++) {
        var index = i + 1;
        if (index <= rating) {
            stars[i].style.color = '#ffc107';
            stars[i].innerHTML = '&#9733;';
        } else {
            stars[i].style.color = '#ddd';
            stars[i].innerHTML = '&#9734;';
        }
    }
}

function updateStars(rating) {
    highlightStars(rating);
}

<?php if ($canReview): ?>
function submitReview() {
    if (!ratingInput.value) {
        alert('Please select a rating.');
        return;
    }
    
    var form = document.getElementById('reviewForm');
    var reviewTitle = form.querySelector('[name="review_title"]').value.trim();
    var reviewText = form.querySelector('[name="review_text"]').value.trim();
    
    if (!reviewTitle) {
        alert('Please enter a review title.');
        return;
    }
    
    if (reviewTitle.length > 200) {
        alert('Review title must be 200 characters or less.');
        return;
    }
    
    if (!reviewText) {
        alert('Please enter your review text.');
        return;
    }
    
    if (reviewText.length < 10) {
        alert('Review must be at least 10 characters long.');
        return;
    }
    
    if (reviewText.length > 2000) {
        alert('Review must be 2000 characters or less.');
        return;
    }
    
    var formData = new FormData(form);
    var xhr = new XMLHttpRequest();
    xhr.open("POST", "review/submit_review.php", true);
    xhr.onload = function() {
        if (xhr.status === 200) {
            try {
                var data = JSON.parse(xhr.responseText);
                if (data.success) {
                    alert(data.message);
                    location.reload();
                } else {
                    alert(data.message);
                }
            } catch(e) {
                alert('Error submitting review. Please try again.');
            }
        }
    };
    xhr.onerror = function() {
        alert('Error submitting review. Please try again.');
    };
    xhr.send(formData);
}
<?php endif; ?>

function deleteReview(reviewId) {
    if (!confirm('Are you sure you want to delete your review?')) return;
    
    var xhr = new XMLHttpRequest();
    xhr.open("POST", "review/delete_review.php", true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.onload = function() {
        if (xhr.status === 200) {
            try {
                var data = JSON.parse(xhr.responseText);
                if (data.success) {
                    alert(data.message);
                    location.reload();
                } else {
                    alert(data.message);
                }
            } catch(e) {
                alert('Error deleting review.');
            }
        }
    };
    xhr.onerror = function() {
        alert('Error deleting review.');
    };
    xhr.send('review_id=' + reviewId);
}
<?php endif; ?>
</script>
<br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br>
<?php include('./includes/footer.php'); ?>