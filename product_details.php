<?php
session_start();
include('./includes/header.php');
include('./includes/config.php');

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
    WHERE i.item_id = $itemId
    LIMIT 1
";

$result = mysqli_query($conn, $sql);

if (!$result || mysqli_num_rows($result) == 0) {
    echo "<script>alert('Product not found.'); window.location.href='index.php';</script>";
    exit;
}

$row = mysqli_fetch_assoc($result);

// Handle images - Simple approach
$images = array();

if (!empty($row['image_path'])) {
    // Remove escaped slashes that might cause JSON decode issues
    $cleanPath = stripslashes($row['image_path']);
    
    // Try to decode as JSON
    $decoded = json_decode($cleanPath, true);
    
    if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
        $images = $decoded;
    } else {
        // Not JSON, treat as single path
        $images = array($cleanPath);
    }
}

// If no images found, use placeholder
if (empty($images)) {
    $images = array('https://via.placeholder.com/400x300?text=No+Image');
}

// Process each image to create full URLs
$processedImages = array();
foreach ($images as $img) {
    // Skip empty values
    if (empty(trim($img))) {
        continue;
    }
    
    // If already full URL, use as-is
    if (strpos($img, 'http://') === 0 || strpos($img, 'https://') === 0) {
        $processedImages[] = $img;
        continue;
    }
    
    // Remove any leading slashes
    $img = ltrim($img, '/');
    
    // Build full URL - uploads folder is inside e-commerce2 folder
    $scheme = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    $fullUrl = $scheme . '://' . $host . '/e-commerce/' . $img;
    
    $processedImages[] = $fullUrl;
}

// If no valid images after processing, use placeholder
if (empty($processedImages)) {
    $processedImages = array('https://via.placeholder.com/400x300?text=Image+Not+Found');
}

$images = $processedImages;

$stock = (int)$row['stock'];
$inStock = $stock > 0;

// Get reviews and ratings (only if review functions exist)
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
    
    // Get customer_id - check session or lookup from user_id
    $currentCustomerId = 0;
    if (isset($_SESSION['customer_id'])) {
        $currentCustomerId = $_SESSION['customer_id'];
    } elseif (isset($_SESSION['user_id']) && function_exists('getCustomerIdFromUserId')) {
        $currentCustomerId = getCustomerIdFromUserId($conn, $_SESSION['user_id']);
        if ($currentCustomerId > 0) {
            $_SESSION['customer_id'] = $currentCustomerId; // Store for future use
        }
    }
    
    if ($currentCustomerId > 0) {
        $userOrder = canCustomerReview($conn, $currentCustomerId, $itemId);
        $canReview = $userOrder !== false && $userOrder !== null;
        
        // Check if user already has a review
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
    background-image: url('uploads/homepage.jpg');
    background-size: cover;
    background-position: center;
    background-attachment: fixed;
    background-repeat: no-repeat;
    color: #f8f9fa; /* Default light text for body */
}

body::before {
    content: '';
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    z-index: -2;
}

body::after {
    content: '';
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    z-index: -1;
}

.main-content {
    padding-top: 100px;
    min-height: 100vh;
}

/* PRODUCT CONTAINER */
.product-container {
    background: rgba(0, 0, 0, 0.16);
    backdrop-filter: blur(20px);
    border-radius: 20px;
    padding: 40px;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
    border: 1px solid rgba(255, 255, 255, 0.3);
    margin-bottom: 40px;
    color: #f8f9fa; /* All text inside product container is light */
}

.product-container h2,
.product-container h3,
.product-container h4,
.product-container h5,
.product-container p,
.product-container strong,
.product-container span,
.product-container li {
    color: #f8f9fa;
}

/* IMAGE SLIDER */
.image-slider {
    background: #00000031 !important;
    border: 2px solid #00000031 !important;
    border-radius: 15px !important;
    overflow: hidden;
}

/* BUTTONS */
.slider-btn {
    background: linear-gradient(135deg, #1a1a1a 0%, #2d2d2d 100%) !important;
    transition: all 0.3s ease;
}

.slider-btn:hover {
    background: linear-gradient(135deg, #0d0d0d 0%, #1a1a1a 100%) !important;
    transform: scale(1.1);
}

.image-counter {
    background: linear-gradient(135deg, #1a1a1a 0%, #2d2d2d 100%) !important;
    color: #f8f9fa !important;
}

/* BUTTONS */
.btn-success,
.btn-primary {
    background: linear-gradient(135deg, #1a1a1a 0%, #2d2d2d 100%) !important;
    border: none !important;
    padding: 12px 30px;
    border-radius: 10px;
    font-weight: 600;
    transition: all 0.3s ease;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.4);
    color: #f8f9fa !important;
}

.btn-success:hover,
.btn-primary:hover {
    background: linear-gradient(135deg, #0d0d0d 0%, #1a1a1a 100%) !important;
    transform: translateY(-2px);
    box-shadow: 0 6px 25px rgba(0, 0, 0, 0.6);
}

/* REVIEWS SECTION */
.reviews-section {
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(20px);
    border-radius: 20px;
    padding: 40px;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
    border: 1px solid rgba(255, 255, 255, 0.3);
    color: #1a1a1a; /* Keep reviews readable on white background */
}

/* REVIEW ITEM */
.review-item {
    background: #f8f9fa;
    border: 1px solid #e0e0e0;
    border-radius: 12px;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    color: #1a1a1a; /* Dark text for reviews */
}

.review-item:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
}

/* FORM CONTROLS */
/* Dark text for inputs and textareas in reviews section */
.reviews-section .form-control {
    color: #1a1a1a;      /* Dark text for readability */
    background: #fff;     /* White background for inputs */
    border-color: #ced4da; /* Standard input border */
}

.reviews-section .form-control:focus {
    color: #1a1a1a;
    background: #fff;
    border-color: #ffc107; /* Highlight border */
    box-shadow: 0 0 0 4px rgba(255, 193, 7, 0.2);
}


/* STAR RATING */
.star-rating,
.star-input .star {
    color: #ffc107; /* Gold stars */
}

/* ALERTS */
.alert {
    border-radius: 12px;
    border: none;
    backdrop-filter: blur(10px);
    color: #1a1a1a;
}

</style>


<div class="main-content">
<div class="container" style="max-width: 1200px; margin: 0 auto; padding: 50px 20px 100px 20px;">
    <div class="row" style="margin-bottom: 60px;">
        <!-- Image slider -->
        <div class="col-md-6 pe-4">
            <div class="image-slider position-relative" style="width:100%; height:400px; overflow:hidden; border-radius:10px; border:1px solid #ddd; background:#f5f5f5;">
                <?php if (!empty($images)): ?>
                    <?php foreach ($images as $index => $img): ?>
                        <img src="<?php echo htmlspecialchars($img); ?>" 
                             class="slider-image" 
                             alt="Product Image <?php echo $index + 1; ?>"
                             style="width:100%; height:100%; object-fit:contain; position:absolute; top:0; left:0; transition: opacity 0.5s ease; opacity:<?php echo $index === 0 ? 1 : 0; ?>;"
                             onerror="console.error('Failed to load:', '<?php echo htmlspecialchars($img, ENT_QUOTES); ?>'); this.style.display='none';"
                             onload="console.log('Loaded:', '<?php echo htmlspecialchars($img, ENT_QUOTES); ?>');">
                    <?php endforeach; ?>
                    
                    <!-- Navigation Buttons -->
                    <?php if (count($images) > 1): ?>
                        <button class="slider-btn prev-btn" onclick="changeSlide(-1)" style="position:absolute; left:10px; top:50%; transform:translateY(-50%); background:rgba(0,0,0,0.5); color:white; border:none; border-radius:50%; width:40px; height:40px; cursor:pointer; font-size:18px; z-index:10;">
                            &lsaquo;
                        </button>
                        <button class="slider-btn next-btn" onclick="changeSlide(1)" style="position:absolute; right:10px; top:50%; transform:translateY(-50%); background:rgba(0,0,0,0.5); color:white; border:none; border-radius:50%; width:40px; height:40px; cursor:pointer; font-size:18px; z-index:10;">
                            &rsaquo;
                        </button>
                        
                        <!-- Image Counter -->
                        <div class="image-counter" style="position:absolute; bottom:10px; left:50%; transform:translateX(-50%); background:rgba(0,0,0,0.6); color:white; padding:5px 15px; border-radius:20px; font-size:14px; z-index:10;">
                            <span id="currentImageNum">1</span> / <?php echo count($images); ?>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Fallback text if all images fail -->
                    <div class="no-image-text" style="position:absolute; top:50%; left:50%; transform:translate(-50%, -50%); display:none; text-align:center; color:#999;">
                        <p>Image not available</p>
                    </div>
                <?php else: ?>
                    <p style="position:absolute; top:50%; left:50%; transform:translate(-50%, -50%);">No images available</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Product details -->
        <div class="col-md-6 ps-4 d-flex align-items-center">
            <div style="width: 100%;">
                <h2 style="font-size: 1.75rem; margin-bottom: 15px;"><?php echo htmlspecialchars($row['description']); ?></h2>
            
            <!-- Rating Display -->
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
                           value="1" min="1" max="<?php echo $stock; ?>"
                           class="form-control" style="width:100px; font-size: 14px;">
                </div>

                <button type="button" onclick="submitCart()" class="btn btn-success">Add to Cart</button>
            </form>
            <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Reviews Section -->
    <?php if (function_exists('getProductReviews')): ?>
    <div class="row" style="margin-top: 60px; margin-bottom: 100px;">
        <div class="col-12">
            <h3>Customer Reviews</h3>
            <hr>
            
            <!-- Write/Edit Review Button -->
            <?php 
            // Check if logged in using both possible session keys
            $isUserLoggedIn = isset($_SESSION['customer_id']) || isset($_SESSION['user_id']);
            $currentCustomerId = isset($_SESSION['customer_id']) ? $_SESSION['customer_id'] : (isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null);
            ?>
            
            <?php if ($isUserLoggedIn): ?>
                <?php if ($canReview): ?>
                    <button class="btn btn-primary mb-4" onclick="toggleReviewForm()">
                        <?php echo $userExistingReview ? 'Edit Your Review' : 'Write a Review'; ?>
                    </button>
                    
                    <!-- Review Form -->
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
                                    <input type="hidden" name="rating" id="ratingValue" value="<?php echo $userExistingReview ? $userExistingReview['rating'] : ''; ?>" required>
                                </div>
                                
                                <div class="mb-4">
                                    <label class="form-label">Review Title <span class="text-danger">*</span></label>
                                    <input type="text" name="review_title" class="form-control" maxlength="200" 
                                           value="<?php echo $userExistingReview ? htmlspecialchars($userExistingReview['review_title']) : ''; ?>" required>
                                </div>
                                
                                <div class="mb-4">
                                    <label class="form-label">Your Review <span class="text-danger">*</span></label>
                                    <textarea name="review_text" class="form-control" rows="5" required><?php echo $userExistingReview ? htmlspecialchars($userExistingReview['review_text']) : ''; ?></textarea>
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
            
            <!-- Display Reviews -->
            <?php if (empty($reviews)): ?>
                <p class="text-muted" style="margin-bottom: 100px;">No reviews yet. Be the first to review this product!</p>
            <?php else: ?>
                <div id="reviewsList" style="margin-bottom: 100px;">
                    <?php foreach ($reviews as $review): ?>
                        <div class="card mb-3 review-item">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <h5 class="card-title"><?php echo htmlspecialchars($review['review_title']); ?></h5>
                                        <div class="star-rating mb-2" style="color: #ffc107;">
                                            <?php for($i = 1; $i <= 5; $i++): ?>
                                                <?php echo $i <= $review['rating'] ? '&#9733;' : '&#9734;'; ?>
                                            <?php endfor; ?>
                                        </div>
                                    </div>
                                    <?php if (isset($_SESSION['customer_id']) && $_SESSION['customer_id'] == $review['customer_id']): ?>
                                        <button class="btn btn-sm btn-outline-danger" onclick="deleteReview(<?php echo $review['review_id']; ?>)">Delete</button>
                                    <?php elseif (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $review['customer_id']): ?>
                                        <button class="btn btn-sm btn-outline-danger" onclick="deleteReview(<?php echo $review['review_id']; ?>)">Delete</button>
                                    <?php endif; ?>
                                </div>
                                <p class="card-text"><?php echo nl2br(htmlspecialchars($review['review_text'])); ?></p>
                                <div class="text-muted small">
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
// Image slider with manual controls only (no auto-slide)
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
    
    // Hide all images
    for (var i = 0; i < visibleImages.length; i++) {
        visibleImages[i].style.opacity = 0;
    }
    
    // Wrap around
    if (n >= visibleImages.length) currentSlide = 0;
    if (n < 0) currentSlide = visibleImages.length - 1;
    
    // Show current image
    visibleImages[currentSlide].style.opacity = 1;
    
    // Update counter
    var counter = document.getElementById('currentImageNum');
    if (counter) counter.textContent = currentSlide + 1;
}

function changeSlide(direction) {
    currentSlide += direction;
    showSlide(currentSlide);
}

// Initialize slider
if (visibleImages.length > 0) {
    showSlide(0);
}

// Add hover effect to buttons
var sliderBtns = document.querySelectorAll('.slider-btn');
for (var i = 0; i < sliderBtns.length; i++) {
    sliderBtns[i].onmouseover = function() {
        this.style.background = 'rgba(0,0,0,0.8)';
    };
    sliderBtns[i].onmouseout = function() {
        this.style.background = 'rgba(0,0,0,0.5)';
    };
}

// Add to cart
<?php if ($inStock): ?>
function submitCart() {
    var form = document.getElementById("addToCartForm");
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
                    document.getElementById("quantity").max = data.newStock;
                    if (data.newStock == 0) {
                        var qtyInput = document.getElementById("quantity");
                        var submitBtn = document.querySelector("#addToCartForm button");
                        if (qtyInput) qtyInput.remove();
                        if (submitBtn) submitBtn.remove();
                        stockSpan.outerHTML = '<span style="color:red; font-size:24px; font-weight:bold;">OUT OF STOCK</span>';
                    }
                } else {
                    alert(data.message || "Failed to add item.");
                }
            } catch(e) {
                console.error(e);
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
// Review form toggle
function toggleReviewForm() {
    var form = document.getElementById('reviewFormContainer');
    
    if (form.style.display === 'none') {
        form.style.display = 'block';
        
        // Wait for display, then scroll to form
        setTimeout(function() {
            var formPosition = form.getBoundingClientRect().top + window.pageYOffset;
            window.scrollTo({
                top: formPosition - 100,
                behavior: 'smooth'
            });
        }, 100);
    } else {
        form.style.display = 'none';
        
        // Scroll back to reviews section
        var reviewsTitle = document.querySelector('h3');
        if (reviewsTitle && reviewsTitle.textContent.includes('Customer Reviews')) {
            var titlePosition = reviewsTitle.getBoundingClientRect().top + window.pageYOffset;
            window.scrollTo({
                top: titlePosition - 100,
                behavior: 'smooth'
            });
        }
    }
}


var stars = document.querySelectorAll('#starRating .star');
var ratingInput = document.getElementById('ratingValue');

// Set initial rating if editing
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
                console.error(e);
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

// Delete review
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
                console.error(e);
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

<?php include('./includes/footer.php'); ?>