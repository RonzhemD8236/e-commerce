<?php
// delete_review.php - Handle review deletion

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');

// Check if user is logged in - check both user_id and customer_id
$isLoggedIn = isset($_SESSION['user_id']) || isset($_SESSION['customer_id']);

if (!$isLoggedIn) {
    echo json_encode(array('success' => false, 'message' => 'You must be logged in to delete a review.'));
    exit;
}

// Get customer ID from session (try both possible session keys)
$customerId = isset($_SESSION['customer_id']) ? intval($_SESSION['customer_id']) : (isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : 0);

if ($customerId <= 0) {
    echo json_encode(array('success' => false, 'message' => 'Invalid customer session. Please login again.'));
    exit;
}

include('../includes/config.php');
include('./review_functions.php');

// Check if POST request
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