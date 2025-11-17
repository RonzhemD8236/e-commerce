<?php
// review_functions.php - Functions for handling product reviews

/**
 * Get customer_id from user_id if needed
 */
function getCustomerIdFromUserId($conn, $userId) {
    $userId = intval($userId);
    
    $sql = "SELECT customer_id FROM customer WHERE user_id = $userId LIMIT 1";
    $result = mysqli_query($conn, $sql);
    
    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        return intval($row['customer_id']);
    }
    
    return 0;
}

/**
 * Get all reviews for a specific product
 */
function getProductReviews($conn, $itemId) {
    $itemId = intval($itemId);
    
    $sql = "SELECT 
                r.review_id,
                r.customer_id,
                r.rating,
                r.review_title,
                r.review_text,
                r.is_verified_purchase,
                r.created_at,
                r.updated_at,
                CONCAT(c.fname, ' ', c.lname) AS customer_name
            FROM reviews r
            INNER JOIN customer c ON r.customer_id = c.customer_id
            WHERE r.item_id = $itemId
            AND r.is_approved = 1
            ORDER BY r.created_at DESC";
    
    $result = mysqli_query($conn, $sql);
    
    if (!$result) {
        return array();
    }
    
    $reviews = array();
    while ($row = mysqli_fetch_assoc($result)) {
        $reviews[] = $row;
    }
    
    return $reviews;
}

/**
 * Get average rating and total review count for a product
 */
function getAverageRating($conn, $itemId) {
    $itemId = intval($itemId);
    
    $sql = "SELECT 
                AVG(rating) AS avg_rating,
                COUNT(*) AS total_reviews
            FROM reviews
            WHERE item_id = $itemId
            AND is_approved = 1";
    
    $result = mysqli_query($conn, $sql);
    
    if (!$result) {
        return array('avg_rating' => 0, 'total_reviews' => 0);
    }
    
    $row = mysqli_fetch_assoc($result);
    return array(
        'avg_rating' => $row['avg_rating'] ? floatval($row['avg_rating']) : 0,
        'total_reviews' => intval($row['total_reviews'])
    );
}

/**
 * Check if a customer can review a product (must have purchased and received it)
 */
function canCustomerReview($conn, $customerId, $itemId) {
    $customerId = intval($customerId);
    $itemId = intval($itemId);
    
    // Check if customer has a completed/delivered order with this item
    // Adjusted to match your actual database structure
    $sql = "SELECT oi.orderinfo_id, oi.date_placed
            FROM orderinfo oi
            INNER JOIN orderline ol ON oi.orderinfo_id = ol.orderinfo_id
            WHERE oi.customer_id = $customerId
            AND ol.item_id = $itemId
            AND (oi.status = 'Delivered' OR oi.status = 'Completed' OR oi.status = 'delivered' OR oi.status = 'completed')
            ORDER BY oi.date_placed DESC
            LIMIT 1";
    
    $result = mysqli_query($conn, $sql);
    
    if (!$result || mysqli_num_rows($result) == 0) {
        return false;
    }
    
    return mysqli_fetch_assoc($result);
}

/**
 * Filter inappropriate words from text
 */
function filterInappropriateWords($text) {
    // List of words to filter (add more as needed)
    $badWords = array(
        'damn', 'hell', 'crap', 'stupid', 'idiot', 'dumb',
        // Add more words to filter
    );
    
    foreach ($badWords as $word) {
        $pattern = '/\b' . preg_quote($word, '/') . '\b/i';
        $replacement = str_repeat('*', strlen($word));
        $text = preg_replace($pattern, $replacement, $text);
    }
    
    return $text;
}

/**
 * Validate review data
 */
function validateReviewData($data) {
    $errors = array();
    
    // Check rating
    if (!isset($data['rating']) || empty($data['rating'])) {
        $errors[] = "Rating is required.";
    } elseif ($data['rating'] < 1 || $data['rating'] > 5) {
        $errors[] = "Rating must be between 1 and 5.";
    }
    
    // Check review title
    if (!isset($data['review_title']) || empty(trim($data['review_title']))) {
        $errors[] = "Review title is required.";
    } elseif (strlen($data['review_title']) > 200) {
        $errors[] = "Review title must be 200 characters or less.";
    }
    
    // Check review text
    if (!isset($data['review_text']) || empty(trim($data['review_text']))) {
        $errors[] = "Review text is required.";
    } elseif (strlen($data['review_text']) < 10) {
        $errors[] = "Review must be at least 10 characters long.";
    } elseif (strlen($data['review_text']) > 2000) {
        $errors[] = "Review must be 2000 characters or less.";
    }
    
    return $errors;
}

/**
 * Insert a new review
 */
function insertReview($conn, $customerId, $itemId, $orderinfoId, $rating, $reviewTitle, $reviewText) {
    $customerId = intval($customerId);
    $itemId = intval($itemId);
    $orderinfoId = intval($orderinfoId);
    $rating = intval($rating);
    $reviewTitle = mysqli_real_escape_string($conn, trim($reviewTitle));
    $reviewText = mysqli_real_escape_string($conn, trim($reviewText));
    
    // Filter inappropriate words
    $reviewTitle = filterInappropriateWords($reviewTitle);
    $reviewText = filterInappropriateWords($reviewText);
    
    $sql = "INSERT INTO reviews 
            (customer_id, item_id, orderinfo_id, rating, review_title, review_text, is_verified_purchase, is_approved, created_at, updated_at)
            VALUES 
            ($customerId, $itemId, $orderinfoId, $rating, '$reviewTitle', '$reviewText', 1, 1, NOW(), NOW())";
    
    return mysqli_query($conn, $sql);
}

/**
 * Update an existing review
 */
function updateReview($conn, $reviewId, $customerId, $rating, $reviewTitle, $reviewText) {
    $reviewId = intval($reviewId);
    $customerId = intval($customerId);
    $rating = intval($rating);
    $reviewTitle = mysqli_real_escape_string($conn, trim($reviewTitle));
    $reviewText = mysqli_real_escape_string($conn, trim($reviewText));
    
    // Filter inappropriate words
    $reviewTitle = filterInappropriateWords($reviewTitle);
    $reviewText = filterInappropriateWords($reviewText);
    
    $sql = "UPDATE reviews 
            SET rating = $rating,
                review_title = '$reviewTitle',
                review_text = '$reviewText',
                updated_at = NOW()
            WHERE review_id = $reviewId
            AND customer_id = $customerId";
    
    return mysqli_query($conn, $sql);
}

/**
 * Delete a review
 */
function deleteReview($conn, $reviewId, $customerId) {
    $reviewId = intval($reviewId);
    $customerId = intval($customerId);
    
    $sql = "DELETE FROM reviews 
            WHERE review_id = $reviewId
            AND customer_id = $customerId";
    
    return mysqli_query($conn, $sql);
}

/**
 * Check if review belongs to customer
 */
function isReviewOwner($conn, $reviewId, $customerId) {
    $reviewId = intval($reviewId);
    $customerId = intval($customerId);
    
    $sql = "SELECT review_id FROM reviews 
            WHERE review_id = $reviewId 
            AND customer_id = $customerId";
    
    $result = mysqli_query($conn, $sql);
    
    return $result && mysqli_num_rows($result) > 0;
}
?>