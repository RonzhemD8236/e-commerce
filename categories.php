<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'customer') {
    $_SESSION['auth_error'] = 'Please log in as admin to access this page.';
    header("Location: ./user/login.php");
    exit();
}
include('./includes/header.php');
include('./includes/config.php');

if (!isset($conn) || $conn->connect_error) {
    die("<div style='background: #ffebee; color: #c62828; padding: 20px; margin: 20px; border-radius: 5px; font-family: Arial;'>
         <strong>Database Connection Failed</strong>
         <br><br>Please check:
         <ul>
             <li>Is XAMPP running?</li>
             <li>Is MySQL service started?</li>
             <li>Does the database 'cammerce_db' exist?</li>
         </ul>
         </div>");
}

$selectedCategory = isset($_GET['category']) ? trim($_GET['category']) : '';

$categories = [
    'DSLR Cameras' => ['icon' => 'fa-camera', 'desc' => 'Professional DSLR cameras for stunning photography'],
    'Mirrorless Cameras' => ['icon' => 'fa-camera-retro', 'desc' => 'Compact mirrorless systems with cutting-edge tech'],
    'Action Cameras' => ['icon' => 'fa-video', 'desc' => 'Rugged cameras for adventure and action shots'],
    'Camera Lenses' => ['icon' => 'fa-circle-dot', 'desc' => 'Premium lenses for every shooting style'],
    'Tripods & Stabilizers' => ['icon' => 'fa-dharmachakra', 'desc' => 'Stable support for smooth, professional shots'],
    'Camera Accessories' => ['icon' => 'fa-toolbox', 'desc' => 'Essential gear to complete your setup']
];

// Get product counts for each category
$categoryCounts = array();
foreach (array_keys($categories) as $catName) {
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM item WHERE category = ?");
    $stmt->bind_param("s", $catName);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $categoryCounts[$catName] = $row['count'];
    $stmt->close();
}

$products = array();
$error_message = '';

if ($selectedCategory) {
    try {
        $sql = "SELECT i.*, IFNULL(s.quantity, 0) AS stock 
                FROM item i
                LEFT JOIN stock s ON i.item_id = s.item_id
                WHERE i.category = ?
                ORDER BY i.item_id DESC";
        
        $stmt = $conn->prepare($sql);
        
        if ($stmt === false) {
            throw new Exception("Prepare statement failed: " . $conn->error);
        }
        
        if (!$stmt->bind_param("s", $selectedCategory)) {
            throw new Exception("Bind param failed: " . $stmt->error);
        }
        
        if (!$stmt->execute()) {
            throw new Exception("Execute failed: " . $stmt->error);
        }
        
        $result = $stmt->get_result();
        
        if ($result === false) {
            throw new Exception("Get result failed: " . $stmt->error);
        }
        
        while ($row = $result->fetch_assoc()) {
            $products[] = $row;
        }
        
        $stmt->close();
        
    } catch (Exception $e) {
        error_log("Database error in categories.php: " . $e->getMessage());
        $error_message = $e->getMessage();
    }
}
?>

<style>
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes float {
    0%, 100% {
        transform: translateY(0px);
    }
    50% {
        transform: translateY(-20px);
    }
}

@keyframes pulse {
    0%, 100% {
        opacity: 1;
    }
    50% {
        opacity: 0.5;
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

.categories-page-wrapper {
    width: 100%;
    background-image: url('uploads/checkout-bg.jpg');
    background-size: cover;
    background-position: center;
    background-attachment: fixed;
    background-repeat: no-repeat;
    min-height: auto;
    padding-top: 40px;
    padding-bottom: 60px;
    position: relative;
}

.categories-page-wrapper::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.6);
    z-index: 0;
}

.categories-page-wrapper::after {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: 
        radial-gradient(circle at 20% 50%, rgba(102, 126, 234, 0.15) 0%, transparent 50%),
        radial-gradient(circle at 80% 80%, rgba(118, 75, 162, 0.15) 0%, transparent 50%);
    z-index: 0;
    pointer-events: none;
}

.categories-page-wrapper > * {
    position: relative;
    z-index: 1;
}

.categories-hero {
    background: linear-gradient(135deg, #1a0033 0%, #4a0080 100%);
    color: white;
    padding: 60px 20px;
    text-align: center;
    margin-bottom: 50px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.1);
    position: relative;
    overflow: hidden;
}

.categories-hero::before {
    content: '';
    position: absolute;
    top: -50%;
    right: -50%;
    width: 200%;
    height: 200%;
    background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
    animation: float 6s ease-in-out infinite;
}

.categories-hero h1 {
    font-size: 3em;
    font-weight: 800;
    margin-bottom: 15px;
    letter-spacing: -1px;
    position: relative;
    z-index: 1;
}

.categories-hero p {
    font-size: 1.3em;
    opacity: 0.95;
    max-width: 600px;
    margin: 0 auto;
    position: relative;
    z-index: 1;
}

.categories-container {
    max-width: 1400px;
    margin: 0 auto;
    padding: 0 15px;
}

.categories-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
    gap: 35px;
    margin-top: 30px;
    padding-bottom: 60px;
}

.category-card {
    background: rgba(255, 255, 255, 0.21);
    padding: 50px 30px;
    border-radius: 20px;
    text-align: center;
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    box-shadow: 0 4px 15px rgba(0,0,0,0.3);
    border: 2px solid rgba(255, 255, 255, 0.2);
    text-decoration: none;
    display: block;
    position: relative;
    overflow: hidden;
    opacity: 0;
    animation: fadeInUp 0.6s ease forwards;
}

.category-card:nth-child(1) { animation-delay: 0.1s; }
.category-card:nth-child(2) { animation-delay: 0.2s; }
.category-card:nth-child(3) { animation-delay: 0.3s; }
.category-card:nth-child(4) { animation-delay: 0.4s; }
.category-card:nth-child(5) { animation-delay: 0.5s; }
.category-card:nth-child(6) { animation-delay: 0.6s; }

.category-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    transition: left 0.4s ease;
    z-index: 0;
}

.category-card::after {
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

.category-card:hover::before {
    left: 0;
}

.category-card:hover::after {
    opacity: 1;
}

.category-card:hover {
    transform: translateY(-12px) scale(1.02);
    box-shadow: 0 20px 40px rgba(102, 126, 234, 0.4);
    border-color: transparent;
}

.category-card > * {
    position: relative;
    z-index: 1;
}

.category-icon {
    font-size: 4em;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    margin-bottom: 25px;
    transition: all 0.4s ease;
    display: inline-block;
}

.category-card:hover .category-icon {
    background: white;
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    transform: scale(1.15) rotateY(360deg);
}

.category-name {
    font-size: 1.5em;
    font-weight: 700;
    color: #e9e9e9ff;
    margin: 0 0 10px 0;
    transition: color 0.4s ease;
}

.category-description {
    font-size: 0.9em;
    color: #d0d0d0;
    margin: 10px 0 15px 0;
    opacity: 0.9;
    transition: all 0.4s ease;
    line-height: 1.5;
}

.category-count {
    display: inline-block;
    background: rgba(255, 255, 255, 0.2);
    color: white;
    padding: 6px 16px;
    border-radius: 20px;
    font-size: 0.85em;
    font-weight: 600;
    margin-top: 10px;
    transition: all 0.4s ease;
}

.category-card:hover .category-count {
    background: rgba(255, 255, 255, 0.3);
    transform: translateY(-3px);
}

.category-card:hover .category-name {
    color: white;
}

.category-card:hover .category-description {
    color: white;
    opacity: 1;
}

.view-arrow {
    opacity: 0;
    transform: translateX(-10px);
    transition: all 0.4s ease;
    color: white;
    font-weight: 600;
    margin-top: 10px;
    font-size: 0.95em;
}

.category-card:hover .view-arrow {
    opacity: 1;
    transform: translateX(0);
}

.products-view {
    margin-top: 0;
    padding-top: 60px;
    padding-bottom: 100px;
}

.back-button {
    display: inline-flex;
    align-items: center;
    gap: 12px;
    background: rgba(255, 255, 255, 0.21);
    color: #e9e9e9ff;
    padding: 18px 35px;
    border-radius: 20px;
    text-decoration: none;
    font-weight: 700;
    font-size: 1.05em;
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    box-shadow: 0 4px 15px rgba(0,0,0,0.3);
    border: 2px solid rgba(255, 255, 255, 0.2);
    margin-bottom: 30px;
    position: relative;
    overflow: hidden;
}

.back-button::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    transition: left 0.4s ease;
    z-index: 0;
}

.back-button:hover::before {
    left: 0;
}

.back-button i,
.back-button span {
    position: relative;
    z-index: 1;
}

.back-button:hover {
    transform: translateY(-8px);
    box-shadow: 0 20px 40px rgba(102, 126, 234, 0.4);
    border-color: #764ba2;
    color: white;
}

.back-button i {
    font-size: 1.2em;
    transition: transform 0.3s ease;
}

.back-button:hover i {
    transform: translateX(-5px);
}

.category-title {
    background: rgba(0, 0, 0, 0.7);
    padding: 40px;
    border-radius: 20px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.3);
    margin-bottom: 30px;
    text-align: center;
    border: 2px solid rgba(102, 126, 234, 0.3);
}

.category-title h1 {
    font-size: 2.5em;
    font-weight: 700;
    color: #ffffff;
    margin-bottom: 15px;
    text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
}

.product-count {
    color: #ffffff;
    font-size: 1.2em;
    font-weight: 600;
    opacity: 0.9;
}

.products-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 30px;
    padding-bottom: 40px;
}

.product-card {
    background: rgba(255, 255, 255, 0.95);
    border-radius: 20px;
    overflow: hidden;
    box-shadow: 0 4px 15px rgba(0,0,0,0.3);
    border: 2px solid rgba(255, 255, 255, 0.2);
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    text-decoration: none;
    display: flex;
    flex-direction: column;
    height: 100%;
    opacity: 0;
    animation: fadeInUp 0.6s ease forwards;
}

.product-card:nth-child(1) { animation-delay: 0.1s; }
.product-card:nth-child(2) { animation-delay: 0.15s; }
.product-card:nth-child(3) { animation-delay: 0.2s; }
.product-card:nth-child(4) { animation-delay: 0.25s; }
.product-card:nth-child(5) { animation-delay: 0.3s; }
.product-card:nth-child(6) { animation-delay: 0.35s; }

.product-card::before {
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

.product-card:hover::before {
    opacity: 1;
}

.product-card:hover {
    transform: translateY(-12px);
    box-shadow: 0 20px 40px rgba(102, 126, 234, 0.4);
    border-color: transparent;
}

.product-image {
    width: 100%;
    height: 250px;
    object-fit: cover;
    transition: transform 0.5s ease;
}

.product-card:hover .product-image {
    transform: scale(1.1);
}

.product-image-container {
    overflow: hidden;
    background: linear-gradient(135deg, #f5f5f5 0%, #e0e0e0 100%);
    height: 250px;
    flex-shrink: 0;
    position: relative;
}

.product-info {
    padding: 20px;
    display: flex;
    flex-direction: column;
    flex-grow: 1;
}

.product-title {
    font-size: 1.05em;
    font-weight: 700;
    color: #1a0033;
    margin-bottom: 10px;
    height: 48px;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    line-height: 1.4;
}

.product-description {
    font-size: 0.88em;
    color: #666;
    margin-bottom: 12px;
    height: 38px;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    line-height: 1.4;
}

.product-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: auto;
    margin-bottom: 12px;
}

.product-price {
    font-size: 1.4em;
    font-weight: 800;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.product-stock {
    font-size: 0.85em;
    color: #666;
}

.product-stock.out {
    color: #f44336;
    font-weight: 700;
}

.view-details-btn {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 10px 20px;
    border-radius: 10px;
    text-align: center;
    font-weight: 700;
    font-size: 0.95em;
    display: block;
    transition: all 0.3s;
    margin-top: auto;
    position: relative;
    overflow: hidden;
}

.view-details-btn::before {
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

.product-card:hover .view-details-btn::before {
    width: 300px;
    height: 300px;
}

.product-card:hover .view-details-btn {
    transform: translateY(-3px);
    box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
}

.no-products {
    background: rgba(255, 255, 255, 0.21);
    padding: 80px 40px;
    border-radius: 20px;
    text-align: center;
    box-shadow: 0 4px 15px rgba(0,0,0,0.3);
    border: 2px solid rgba(255, 255, 255, 0.2);
}

.no-products i {
    font-size: 4em;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    margin-bottom: 20px;
    display: inline-block;
}

.no-products h3 {
    font-size: 1.8em;
    color: #e9e9e9ff;
    margin-bottom: 15px;
    text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
}

.no-products p {
    color: #e9e9e9ff;
    font-size: 1.1em;
    opacity: 0.9;
}

.footer {
    margin-top: 0 !important;
}

/* Loading skeleton */
.skeleton {
    background: linear-gradient(90deg, 
        rgba(255, 255, 255, 0.1) 25%, 
        rgba(255, 255, 255, 0.2) 50%, 
        rgba(255, 255, 255, 0.1) 75%);
    background-size: 1000px 100%;
    animation: shimmer 2s infinite;
}

@media (max-width: 992px) {
    .categories-grid {
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 25px;
        margin-top: 30px;
        padding-bottom: 100px;
        margin-bottom: 0;
    }
    
    .categories-hero h1 {
        font-size: 2.5em;
    }
}

@media (max-width: 768px) {
    .categories-page-wrapper {
        padding-top: 20px;
        padding-bottom: 0;
    }
    
    .categories-hero {
        padding: 40px 20px;
        margin-bottom: 30px;
    }
    
    .categories-hero h1 {
        font-size: 2em;
    }
    
    .categories-hero p {
        font-size: 1.1em;
    }
    
    .categories-grid {
        grid-template-columns: 1fr;
        gap: 20px;
    }
    
    .products-grid {
        grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
        gap: 20px;
    }
    
    .product-image-container {
        height: 220px;
    }
    
    .product-image {
        height: 220px;
    }

    .category-title {
        padding: 30px 20px;
    }

    .category-title h1 {
        font-size: 2em;
    }
}

@media (max-width: 576px) {
    .categories-hero h1 {
        font-size: 1.75em;
    }
    
    .category-card {
        padding: 40px 20px;
    }
    
    .products-grid {
        grid-template-columns: 1fr;
    }

    .back-button {
        padding: 15px 25px;
        font-size: 1em;
    }

    .category-title h1 {
        font-size: 1.75em;
    }

    .product-count {
        font-size: 1em;
    }
}
</style>

<div class="categories-page-wrapper">
    <?php if ($error_message): ?>
        <div style='background: #ffebee; color: #c62828; padding: 20px; margin: 20px; border-radius: 5px; font-family: Arial;'>
            <strong>Database Error:</strong> <?php echo htmlspecialchars($error_message); ?>
            <br><br>
            <strong>Possible solutions:</strong>
            <ul>
                <li>Check if the 'item' and 'stock' tables exist in your database</li>
                <li>Verify the table column names match (item_id, category, quantity, etc.)</li>
                <li>Make sure XAMPP MySQL is running</li>
            </ul>
        </div>
    <?php endif; ?>
    
    <?php if (!$selectedCategory): ?>
        <div class="categories-hero">
            <h1>Browse by Category</h1>
            <p>Choose a category to explore our premium products</p>
        </div>
        
        <div class="categories-container">
            <div class="categories-grid">
                <?php foreach ($categories as $catName => $catData): ?>
                    <a href="?category=<?php echo urlencode($catName); ?>" class="category-card">
                        <i class="fas <?php echo $catData['icon']; ?> category-icon"></i>
                        <h4 class="category-name"><?php echo htmlspecialchars($catName); ?></h4>
                        <p class="category-description"><?php echo htmlspecialchars($catData['desc']); ?></p>
                        <span class="category-count">
                            <?php echo $categoryCounts[$catName]; ?> 
                            Product<?php echo $categoryCounts[$catName] !== 1 ? 's' : ''; ?>
                        </span>
                        <div class="view-arrow">View Products →</div>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
        
    <?php else: ?>
        <div class="categories-container products-view">
            <a href="/lensify/e-commerce/categories.php" class="back-button">
                <i class="fas fa-arrow-left"></i>
                <span>Back to Categories</span>
            </a>
            
            <div class="category-title">
                <h1><?php echo htmlspecialchars($selectedCategory); ?></h1>
                <p class="product-count">
                    <?php if (empty($products)): ?>
                        No products found
                    <?php else: ?>
                        <?php echo count($products); ?> product<?php echo count($products) !== 1 ? 's' : ''; ?> available
                    <?php endif; ?>
                </p>
            </div>
            
            <?php if (empty($products)): ?>
                <div class="no-products">
                    <i class="fas fa-box-open"></i>
                    <h3>No Products Found</h3>
                    <p>We don't have any products in this category yet. Check back soon!</p>
                </div>
            <?php else: ?>
                <div class="products-grid">
                    <?php foreach ($products as $product): ?>
                        <?php
                        $images = array();
                        if (!empty($product['image_path'])) {
                            $cleanPath = stripslashes($product['image_path']);
                            $decoded = json_decode($cleanPath, true);
                            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                                $images = $decoded;
                            } else {
                                $images = array($cleanPath);
                            }
                        }
                        
                        $firstImage = !empty($images) ? $images[0] : 'uploads/default.png';
                        
                        $stock = (int)$product['stock'];
                        $inStock = $stock > 0;
                        ?>
                        
                        <a href="/lensify/e-commerce/product_details.php?id=<?php echo $product['item_id']; ?>" class="product-card">
                            <div class="product-image-container">
                                <img src="<?php echo htmlspecialchars($firstImage); ?>" 
                                     class="product-image" 
                                     alt="<?php echo htmlspecialchars($product['description']); ?>"
                                     onerror="this.src='/lensify/e-commerce/uploads/default.png'">
                            </div>
                            
                            <div class="product-info">
                                <h6 class="product-title">
                                    <?php echo htmlspecialchars($product['description']); ?>
                                </h6>
                                
                                <?php if (!empty($product['short_description'])): ?>
                                    <p class="product-description">
                                        <?php echo htmlspecialchars($product['short_description']); ?>
                                    </p>
                                <?php endif; ?>
                                
                                <div class="product-footer">
                                    <span class="product-price">₱<?php echo number_format($product['sell_price'], 2); ?></span>
                                    <?php if ($inStock): ?>
                                        <small class="product-stock">Stock: <?php echo $stock; ?></small>
                                    <?php else: ?>
                                        <small class="product-stock out">Out of Stock</small>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="view-details-btn">
                                    View Details
                                </div>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<?php include('./includes/footer.php'); ?>