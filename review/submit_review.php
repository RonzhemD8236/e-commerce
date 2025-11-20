<?php
 

 
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');

 
if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    echo json_encode(array(
        'success' => false, 
        'message' => 'You must be logged in to submit a review.',
        'redirect' => '/lensify/e-commerce/user/login.php'
    ));
    exit;
}

include('../includes/config.php');
include('./review_functions.php');

 
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
    echo json_encode(array(
        'success' => false, 
        'message' => 'Invalid customer session. Please login again.',
        'redirect' => '/lensify/e-commerce/user/login.php'
    ));
    exit;
}

 
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

 
if ($itemId <= 0) {
    echo json_encode(array('success' => false, 'message' => 'Invalid product ID.'));
    exit;
}

 
$errors = validateReviewData(array(
    'rating' => $rating,
    'review_title' => $reviewTitle,
    'review_text' => $reviewText
));

if (!empty($errors)) {
    echo json_encode(array('success' => false, 'message' => implode(' ', $errors)));
    exit;
}

 
$stmt = mysqli_prepare($conn, "SELECT item_id FROM item WHERE item_id = ?");
mysqli_stmt_bind_param($stmt, "i", $itemId);
mysqli_stmt_execute($stmt);
$checkItem = mysqli_stmt_get_result($stmt);

if (!$checkItem || mysqli_num_rows($checkItem) == 0) {
    mysqli_stmt_close($stmt);
    echo json_encode(array('success' => false, 'message' => 'Product not found.'));
    exit;
}
mysqli_stmt_close($stmt);

 
$userOrder = canCustomerReview($conn, $customerId, $itemId);
if (!$userOrder) {
    echo json_encode(array('success' => false, 'message' => 'You can only review products you have purchased and received. Your order status must be Delivered or Completed.'));
    exit;
}

 
if ($orderinfoId <= 0 && isset($userOrder['orderinfo_id'])) {
    $orderinfoId = intval($userOrder['orderinfo_id']);
}

 
if (!empty($reviewId)) {
    
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
    
    $stmt = mysqli_prepare($conn, "SELECT review_id FROM reviews WHERE customer_id = ? AND item_id = ?");
    mysqli_stmt_bind_param($stmt, "ii", $customerId, $itemId);
    mysqli_stmt_execute($stmt);
    $checkExisting = mysqli_stmt_get_result($stmt);
    
    if ($checkExisting && mysqli_num_rows($checkExisting) > 0) {
        mysqli_stmt_close($stmt);
        echo json_encode(array('success' => false, 'message' => 'You have already reviewed this product. You can edit your existing review.'));
        exit;
    }
    mysqli_stmt_close($stmt);
    
    $insertResult = insertReview($conn, $customerId, $itemId, $orderinfoId, $rating, $reviewTitle, $reviewText);
    
    if ($insertResult) {
        echo json_encode(array('success' => true, 'message' => 'Review submitted successfully! Thank you for your feedback.'));
    } else {
        echo json_encode(array('success' => false, 'message' => 'Failed to submit review. Please try again.'));
    }
}

mysqli_close($conn);
?>