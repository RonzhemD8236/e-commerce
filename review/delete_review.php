<?php
// delete_review.php - Handle review deletion

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');

// ✅ AUTHENTICATION CHECK - Must be logged in
if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    echo json_encode(array(
        'success' => false, 
        'message' => 'You must be logged in to delete a review.',
        'redirect' => '/lensify/e-commerce/user/login.php'
    ));
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
    echo json_encode(array(
        'success' => false, 
        'message' => 'Invalid customer session. Please login again.',
        'redirect' => '/lensify/e-commerce/user/login.php'
    ));
    exit;
}

// ✅ Check if POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(array('success' => false, 'message' => 'Invalid request method.'));
    exit;
}

$reviewId = isset($_POST['review_id']) ? intval($_POST['review_id']) : 0;

if ($reviewId <= 0) {
    echo json_encode(array('success' => false, 'message' => 'Invalid review ID.'));
    exit;
}

// Verify the review belongs to this customer
if (!isReviewOwner($conn, $reviewId, $customerId)) {
    echo json_encode(array('success' => false, 'message' => 'You can only delete your own reviews.'));
    exit;
}

// Delete the review
if (deleteReview($conn, $reviewId, $customerId)) {
    echo json_encode(array('success' => true, 'message' => 'Review deleted successfully.'));
} else {
    echo json_encode(array('success' => false, 'message' => 'Failed to delete review. Please try again.'));
}

mysqli_close($conn);
?>