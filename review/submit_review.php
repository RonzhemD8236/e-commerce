<?php
// submit_review.php - Handle review submission and updates

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');

// Check if user is logged in - check both user_id and customer_id
$isLoggedIn = isset($_SESSION['user_id']) || isset($_SESSION['customer_id']);

if (!$isLoggedIn) {
    echo json_encode(array('success' => false, 'message' => 'You must be logged in to submit a review.'));
    exit;
}

include('../includes/config.php');
include('./review_functions.php');

// Get customer ID - check session or lookup from user_id
if (isset($_SESSION['customer_id'])) {
    $customerId = intval($_SESSION['customer_id']);
} elseif (isset($_SESSION['user_id'])) {
    $customerId = getCustomerIdFromUserId($conn, $_SESSION['user_id']);
    if ($customerId > 0) {
        $_SESSION['customer_id'] = $customerId;
    }
} else {
    $customerId = 0;
}

if ($customerId <= 0) {
    echo json_encode(array('success' => false, 'message' => 'Invalid customer session. Please login again.'));
    exit;
}

// Check if POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(array('success' => false, 'message' => 'Invalid request method.'));
    exit;
}

$itemId = isset($_POST['item_id']) ? intval($_POST['item_id']) : 0;
$orderinfoId = isset($_POST['orderinfo_id']) ? intval($_POST['orderinfo_id']) : 0;
$rating = isset($_POST['rating']) ? intval($_POST['rating']) : 0;
$reviewTitle = isset($_POST['review_title']) ? trim($_POST['review_title']) : '';
$reviewText = isset($_POST['review_text']) ? trim($_POST['review_text']) : '';
$reviewId = isset($_POST['review_id']) ? intval($_POST['review_id']) : 0;

// Basic validation
if ($itemId <= 0) {
    echo json_encode(array('success' => false, 'message' => 'Invalid product ID.'));
    exit;
}

// Validate data
$errors = validateReviewData(array(
    'rating' => $rating,
    'review_title' => $reviewTitle,
    'review_text' => $reviewText
));

if (!empty($errors)) {
    echo json_encode(array('success' => false, 'message' => implode(' ', $errors)));
    exit;
}

// Check if item exists
$checkItem = mysqli_query($conn, "SELECT item_id FROM item WHERE item_id = $itemId");
if (!$checkItem || mysqli_num_rows($checkItem) == 0) {
    echo json_encode(array('success' => false, 'message' => 'Product not found.'));
    exit;
}

// Check if customer can review this product
$userOrder = canCustomerReview($conn, $customerId, $itemId);
if (!$userOrder) {
    echo json_encode(array('success' => false, 'message' => 'You can only review products you have purchased and received. Your order status must be Delivered or Completed.'));
    exit;
}

// If orderinfo_id wasn't provided in POST, get it from the user's order
if ($orderinfoId <= 0 && isset($userOrder['orderinfo_id'])) {
    $orderinfoId = intval($userOrder['orderinfo_id']);
}

// Check if this is an update or new review
if ($reviewId > 0) {
    // Verify the review belongs to this customer
    if (!isReviewOwner($conn, $reviewId, $customerId)) {
        echo json_encode(array('success' => false, 'message' => 'You can only edit your own reviews.'));
        exit;
    }
    
    $updateResult = updateReview($conn, $reviewId, $customerId, $rating, $reviewTitle, $reviewText);
    
    if ($updateResult) {
        echo json_encode(array('success' => true, 'message' => 'Review updated successfully!'));
    } else {
        echo json_encode(array('success' => false, 'message' => 'Failed to update review. Please try again.'));
    }
} else {
    // Check if customer already reviewed this product
    $checkExisting = mysqli_query($conn, 
        "SELECT review_id FROM reviews WHERE customer_id = $customerId AND item_id = $itemId"
    );
    
    if ($checkExisting && mysqli_num_rows($checkExisting) > 0) {
        echo json_encode(array('success' => false, 'message' => 'You have already reviewed this product. You can edit your existing review.'));
        exit;
    }
    
    $insertResult = insertReview($conn, $customerId, $itemId, $orderinfoId, $rating, $reviewTitle, $reviewText);
    
    if ($insertResult) {
        echo json_encode(array('success' => true, 'message' => 'Review submitted successfully! Thank you for your feedback.'));
    } else {
        echo json_encode(array('success' => false, 'message' => 'Failed to submit review. Please try again.'));
    }
}

mysqli_close($conn);
?>