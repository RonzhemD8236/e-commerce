<?php
 

/**
 * Get customer_id from user_id if needed
 */
function getCustomerIdFromUserId($conn, $userId) {
    $userId = intval($userId);
    
    $stmt = mysqli_prepare($conn, "SELECT customer_id FROM customer WHERE user_id = ? LIMIT 1");
    mysqli_stmt_bind_param($stmt, "i", $userId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);
        return intval($row['customer_id']);
    }
    
    mysqli_stmt_close($stmt);
    return 0;
}

function getProductReviews($conn, $itemId) {
    $itemId = intval($itemId);
    
    $stmt = mysqli_prepare($conn, "SELECT 
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
            WHERE r.item_id = ?
            AND r.is_approved = 1
            ORDER BY r.created_at DESC");
    
    mysqli_stmt_bind_param($stmt, "i", $itemId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if (!$result) {
        mysqli_stmt_close($stmt);
        return array();
    }
    
    $reviews = array();
    while ($row = mysqli_fetch_assoc($result)) {
        $reviews[] = $row;
    }
    
    mysqli_stmt_close($stmt);
    return $reviews;
}

function getAverageRating($conn, $itemId) {
    $itemId = intval($itemId);
    
    $stmt = mysqli_prepare($conn, "SELECT 
                AVG(rating) AS avg_rating,
                COUNT(*) AS total_reviews
            FROM reviews
            WHERE item_id = ?
            AND is_approved = 1");
    
    mysqli_stmt_bind_param($stmt, "i", $itemId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if (!$result) {
        mysqli_stmt_close($stmt);
        return array('avg_rating' => 0, 'total_reviews' => 0);
    }
    
    $row = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
    
    return array(
        'avg_rating' => $row['avg_rating'] ? floatval($row['avg_rating']) : 0,
        'total_reviews' => intval($row['total_reviews'])
    );
}

function canCustomerReview($conn, $customerId, $itemId) {
    $customerId = intval($customerId);
    $itemId = intval($itemId);
    
    $stmt = mysqli_prepare($conn, "SELECT oi.orderinfo_id, oi.date_placed
            FROM orderinfo oi
            INNER JOIN orderline ol ON oi.orderinfo_id = ol.orderinfo_id
            WHERE oi.customer_id = ?
            AND ol.item_id = ?
            AND (oi.status = 'Delivered' OR oi.status = 'Completed' OR oi.status = 'delivered' OR oi.status = 'completed')
            ORDER BY oi.date_placed DESC
            LIMIT 1");
    
    mysqli_stmt_bind_param($stmt, "ii", $customerId, $itemId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if (!$result || mysqli_num_rows($result) == 0) {
        mysqli_stmt_close($stmt);
        return false;
    }
    
    $row = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
    return $row;
}

function filterInappropriateWords($text) {
    $badWords = array(
        
        'fuck', 'shit', 'bitch', 'asshole', 'bastard', 'damn', 'hell', 
        'crap', 'piss', 'dick', 'cock', 'pussy', 'cunt', 'slut', 'whore',
        'fag', 'nigger', 'retard', 'retarded', 'motherfucker', 'bullshit',
        
        
        'stupid', 'idiot', 'dumb', 'moron', 'imbecile', 'loser', 'trash',
        
        
        'putang ina', 'putangina', 'tangina', 'tngina', 'puta', 'gago', 
        'gaga', 'tarantado', 'tanga', 'bobo', 'ulol', 'inutil', 'punyeta',
        'leche', 'peste', 'hinayupak', 'hayop', 'animal', 'kupal', 
        'kantot', 'kantutan', 'tamod', 'tite', 'bilat', 'puke', 'bayag',
        'burat', 'jakol', 'pokpok', 'siraulo', 'tarantadong', 
        
        
        'walang kwenta', 'walang hiya', 'walanghiya', 'galing mo', 
        'bastos', 'malandi', 'bruha', 'tarantada',
        
        
        'yawa', 'atay', 'pisti', 'buang', 'buanga', 'bilat', 
        'bilata', 'puta', 'putang', 'lintian',
        
        
        'f*ck', 'sh*t', 'b*tch', 'a$$hole', 'fvck', 'fck', 'shyt',
        'p*ta', 'g*go', 'tang*na', 'b*bo', 't*nga', 'p*k*',
        
        
        'wtf', 'stfu', 'lmao', 'lol jk die', 'kys', 'ky$ ', 'di ka maganda',
        'pangit', 'mukhang', 'ampangit'
    );
    
    foreach ($badWords as $word) {
        $pattern = createFlexiblePattern($word);
        $replacement = str_repeat('*', strlen($word));
        $text = preg_replace($pattern, $replacement, $text);
    }
    
    return $text;
}

function createFlexiblePattern($word) {
    $word = preg_quote($word, '/');
    
    $word = str_replace(array('a', 'e', 'i', 'o', 's', 't'), 
                       array('[a@4]', '[e3]', '[i1!]', '[o0]', '[s$5]', '[t7]'), 
                       $word);
    
    $word = str_replace('\ ', '\s*', $word);
    
    return '/\b' . $word . '\b/i';
}

function validateReviewData($data) {
    $errors = array();
    
    if (!isset($data['rating']) || empty($data['rating'])) {
        $errors[] = "Rating is required.";
    } elseif ($data['rating'] < 1 || $data['rating'] > 5) {
        $errors[] = "Rating must be between 1 and 5.";
    }
    
    if (!isset($data['review_title']) || empty(trim($data['review_title']))) {
        $errors[] = "Review title is required.";
    } elseif (strlen($data['review_title']) > 200) {
        $errors[] = "Review title must be 200 characters or less.";
    }
    
    if (!isset($data['review_text']) || empty(trim($data['review_text']))) {
        $errors[] = "Review text is required.";
    } elseif (strlen($data['review_text']) < 10) {
        $errors[] = "Review must be at least 10 characters long.";
    } elseif (strlen($data['review_text']) > 2000) {
        $errors[] = "Review must be 2000 characters or less.";
    }
    
    return $errors;
}

function insertReview($conn, $customerId, $itemId, $orderinfoId, $rating, $reviewTitle, $reviewText) {
    $customerId = intval($customerId);
    $itemId = intval($itemId);
    $orderinfoId = intval($orderinfoId);
    $rating = intval($rating);
    
    
    $reviewTitle = filterInappropriateWords(trim($reviewTitle));
    $reviewText = filterInappropriateWords(trim($reviewText));
    
    $stmt = mysqli_prepare($conn, "INSERT INTO reviews 
            (customer_id, item_id, orderinfo_id, rating, review_title, review_text, is_verified_purchase, is_approved, created_at, updated_at)
            VALUES 
            (?, ?, ?, ?, ?, ?, 1, 1, NOW(), NOW())");
    
    mysqli_stmt_bind_param($stmt, "iiiiss", $customerId, $itemId, $orderinfoId, $rating, $reviewTitle, $reviewText);
    $result = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    
    return $result;
}

function updateReview($conn, $reviewId, $customerId, $rating, $reviewTitle, $reviewText) {
    $reviewId = intval($reviewId);
    $customerId = intval($customerId);
    $rating = intval($rating);
    
    $reviewTitle = filterInappropriateWords(trim($reviewTitle));
    $reviewText = filterInappropriateWords(trim($reviewText));
    
    $stmt = mysqli_prepare($conn, "UPDATE reviews 
            SET rating = ?,
                review_title = ?,
                review_text = ?,
                updated_at = NOW()
            WHERE review_id = ?
            AND customer_id = ?");
    
    mysqli_stmt_bind_param($stmt, "issii", $rating, $reviewTitle, $reviewText, $reviewId, $customerId);
    $result = mysqli_stmt_execute($stmt);
    $affected = mysqli_stmt_affected_rows($stmt);
    mysqli_stmt_close($stmt);
    
    return $result && $affected > 0;
}

/**
 * Delete a review
 */
/**
 * Delete a review
 */
function deleteReview($conn, $reviewId, $customerId) {
    $reviewId = intval($reviewId);
    $customerId = intval($customerId);
    
    
    $checkStmt = mysqli_prepare($conn, "SELECT review_id FROM reviews WHERE review_id = ? AND customer_id = ?");
    mysqli_stmt_bind_param($checkStmt, "ii", $reviewId, $customerId);
    mysqli_stmt_execute($checkStmt);
    $checkResult = mysqli_stmt_get_result($checkStmt);
    
    if (!$checkResult || mysqli_num_rows($checkResult) == 0) {
        mysqli_stmt_close($checkStmt);
        error_log("Review not found: review_id=$reviewId, customer_id=$customerId");
        return false;
    }
    mysqli_stmt_close($checkStmt);
    
    
    $stmt = mysqli_prepare($conn, "DELETE FROM reviews WHERE review_id = ? AND customer_id = ?");
    
    if (!$stmt) {
        error_log("Failed to prepare delete statement: " . mysqli_error($conn));
        return false;
    }
    
    mysqli_stmt_bind_param($stmt, "ii", $reviewId, $customerId);
    $result = mysqli_stmt_execute($stmt);
    
    if (!$result) {
        error_log("Failed to execute delete: " . mysqli_stmt_error($stmt));
        mysqli_stmt_close($stmt);
        return false;
    }
    
    $affected = mysqli_stmt_affected_rows($stmt);
    mysqli_stmt_close($stmt);
    
    error_log("Delete review result: affected_rows=$affected");
    return $result && $affected > 0;
}

/**
 * Check if review belongs to customer
 */
function isReviewOwner($conn, $reviewId, $customerId) {
    $reviewId = intval($reviewId);
    $customerId = intval($customerId);
    
    $stmt = mysqli_prepare($conn, "SELECT review_id FROM reviews 
            WHERE review_id = ? 
            AND customer_id = ?");
    
    mysqli_stmt_bind_param($stmt, "ii", $reviewId, $customerId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    $exists = $result && mysqli_num_rows($result) > 0;
    mysqli_stmt_close($stmt);
    
    return $exists;
}
?>