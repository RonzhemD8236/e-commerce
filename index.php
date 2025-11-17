<?php
session_start();
include('./includes/header.php');
include('./includes/config.php');
?>

<style>
/* ========================================
   RESET & BASE STYLES
   ======================================== */


* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}



/* Override Bootstrap container */
.main-content {
    padding: 0 !important;
    margin: 0 !important;
    max-width: 100% !important;
    width: 100% !important;
}

/* Page Wrapper - Full Width */
.product-page-wrapper {
    background: transparent !important;
    width: 100%;
    padding: 0;
    margin: 0;
}

/* ========================================
   HERO SECTION - FULL WIDTH
   ======================================== */

body {
    background: url("uploads/homepage.jpg") no-repeat center center fixed;
    background-size: cover;
}


.hero-section {
    background: linear-gradient(135deg, #1a0033 0%, #4a0080 100%);
    color: white;
    padding: 80px 20px;
    text-align: center;
    position: relative;
    overflow: hidden;
    width: 100%;
    margin: 0;
}

.hero-image {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    object-fit: cover;
    opacity: 0.2;
    z-index: 1;
}

.hero-content {
    position: relative;
    z-index: 2;
    max-width: 800px;
    margin: 0 auto;
}

.hero-section h1 {
    font-size: 3em;
    margin-bottom: 20px;
    font-weight: 700;
    letter-spacing: -1px;
}

.hero-section p {
    font-size: 1.2em;
    opacity: 0.95;
    line-height: 1.7;
}

/* ========================================
   SEARCH SECTION
   ======================================== */
.search-section {
    width: 100%;
    background: transparent;
    padding: 40px 20px;
    text-align: center;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
}

.search-box {
    max-width: 700px;
    margin: 0 auto;
}

.search-box input {
    width: 100%;
    padding: 18px 30px;
    border: 2px solid #e0e0e0;
    border-radius: 50px;
    font-size: 17px;
    transition: all 0.3s;
    background: #fafafa;
}

.search-box input:focus {
    outline: none;
    border-color: #4a0080;
    box-shadow: 0 0 0 4px rgba(74, 0, 128, 0.1);
    background: white;
}

/* ========================================
   MAIN CONTENT AREA - CONSTRAINED WIDTH
   ======================================== */
.content-wrapper {
    max-width: 1400px;
    margin: 0 auto;
    padding: 40px 20px;
}

/* Filter Section - Side by Side */
.filter-section {
    display: flex;
    gap: 30px;
    align-items: flex-start;
}

.filter-sidebar {
    background: white;
    padding: 30px;
    border-radius: 16px;
    width: 300px;
    flex-shrink: 0;
    box-shadow: 0 4px 15px rgba(0,0,0,0.08);
    position: sticky;
    top: 100px;
}

.filter-sidebar h3 {
    margin-bottom: 25px;
    color: #1a0033;
    font-size: 1.4em;
    font-weight: 700;
    border-bottom: 3px solid #4a0080;
    padding-bottom: 15px;
}

/* Products Content */
.products-content {
    flex: 1;
    min-width: 0;
}

.filter-header {
    background: white;
    padding: 25px 30px;
    border-radius: 16px;
    margin-bottom: 30px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.08);
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.results-count {
    color: #333;
    font-weight: 700;
    font-size: 1.2em;
}

.filter-sidebar,
.filter-header {
    background: rgba(255, 255, 255, 0.8) !important; /* 80% transparency */
    backdrop-filter: blur(10px) !important; /* glass effect */
    -webkit-backdrop-filter: blur(10px);
    border: 1px solid rgba(255,255,255,0.4);
}


/* ========================================
   PRICE FILTER
   ======================================== */
.price-filter h4 {
    margin-bottom: 20px;
    color: #1a0033;
    font-size: 1.05em;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 1px;
}

.price-slider-container {
    margin: 25px 0;
}

.price-slider {
    width: 100%;
    height: 8px;
    background: linear-gradient(to right, #e0e0e0, #4a0080);
    border-radius: 10px;
    outline: none;
    -webkit-appearance: none;
    cursor: pointer;
}

.price-slider::-webkit-slider-thumb {
    -webkit-appearance: none;
    appearance: none;
    width: 24px;
    height: 24px;
    background: white;
    border: 3px solid #4a0080;
    cursor: pointer;
    border-radius: 50%;
    box-shadow: 0 3px 8px rgba(0,0,0,0.2);
    transition: all 0.2s;
}

.price-slider::-webkit-slider-thumb:hover {
    transform: scale(1.15);
    box-shadow: 0 5px 15px rgba(74, 0, 128, 0.4);
}

.price-slider::-moz-range-thumb {
    width: 24px;
    height: 24px;
    background: white;
    border: 3px solid #4a0080;
    cursor: pointer;
    border-radius: 50%;
    border: none;
    box-shadow: 0 3px 8px rgba(0,0,0,0.2);
}

.price-values {
    display: flex;
    justify-content: space-between;
    margin-top: 12px;
    font-weight: 700;
    font-size: 1em;
    color: #4a0080;
}

.reset-btn {
    background: linear-gradient(135deg, #1a0033 0%, #4a0080 100%);
    color: white;
    border: none;
    padding: 15px 25px;
    border-radius: 12px;
    cursor: pointer;
    font-size: 16px;
    margin-top: 30px;
    transition: all 0.3s;
    width: 100%;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 1px;
}

.reset-btn:hover {
    transform: translateY(-3px);
    box-shadow: 0 6px 20px rgba(74, 0, 128, 0.4);
}

/* ========================================
   PRODUCTS GRID - MODERN LAYOUT
   ======================================== */
.products-grid {
    display: grid !important;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)) !important;
    gap: 30px !important;
    padding: 0 !important;
    margin: 0 !important;
    list-style: none !important;
}

/* Product Card - Modern Design */
.product {
    width: 100% !important;
    background: white !important;
    border-radius: 20px !important;
    overflow: hidden !important;
    box-shadow: 0 4px 15px rgba(0,0,0,0.08) !important;
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1) !important;
    display: flex !important;
    flex-direction: column !important;
    border: none !important;
    margin: 0 !important;
}

.product:hover {
    transform: translateY(-12px) !important;
    box-shadow: 0 20px 40px rgba(0,0,0,0.15) !important;
}

.product.out-of-stock {
    opacity: 0.6;
}

.product > a {
    text-decoration: none !important;
    color: inherit !important;
    display: flex !important;
    flex-direction: column !important;
    height: 100% !important;
}

/* Product Thumbnail - Improved */
.product-thumb {
    position: relative;
    width: 100% !important;
    height: 320px !important;
    overflow: hidden;
    background: linear-gradient(135deg, #f5f5f5 0%, #e0e0e0 100%);
    flex-shrink: 0;
}

.product-thumb img {
    width: 100% !important;
    height: 100% !important;
    object-fit: cover !important;
    position: absolute !important;
    top: 0;
    left: 0;
    transition: opacity 1s ease, transform 0.5s ease;
}

.product:hover .product-thumb img {
    transform: scale(1.1);
}

/* Stock Badge - Modern */
.stock-badge {
    position: absolute;
    top: 16px;
    right: 16px;
    background: rgba(76, 175, 80, 0.95);
    backdrop-filter: blur(10px);
    color: white;
    padding: 8px 18px;
    border-radius: 25px;
    font-size: 13px;
    font-weight: 700;
    z-index: 10;
    text-transform: uppercase;
    letter-spacing: 0.8px;
    box-shadow: 0 4px 12px rgba(76, 175, 80, 0.3);
}

.stock-badge.out {
    background: rgba(244, 67, 54, 0.95);
    box-shadow: 0 4px 12px rgba(244, 67, 54, 0.3);
}

/* Product Info - Improved Spacing */
.product-info {
    padding: 25px !important;
    flex: 1;
    display: flex !important;
    flex-direction: column !important;
    gap: 12px;
}

.product-name {
    font-size: 1.15em !important;
    font-weight: 700 !important;
    color: #1a0033 !important;
    line-height: 1.5 !important;
    min-height: 55px;
    margin: 0 !important;
}

.product-price {
    font-size: 1.8em !important;
    font-weight: 800 !important;
    color: #4a0080 !important;
    margin-top: auto !important;
    margin-bottom: 0 !important;
}

.product-price.out-of-stock {
    color: #f44336 !important;
    font-size: 1.2em !important;
    font-weight: 700 !important;
}

/* No Results */
.no-results {
    text-align: center;
    padding: 100px 40px;
    color: #666;
    display: none;
    background: white;
    border-radius: 20px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.08);
}

.no-results h3 {
    font-size: 2em;
    margin-bottom: 20px;
    color: #1a0033;
    font-weight: 700;
}

.no-results p {
    font-size: 1.2em;
}

/* ========================================
   RESPONSIVE DESIGN
   ======================================== */
@media (max-width: 1200px) {
    .products-grid {
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)) !important;
        gap: 25px !important;
    }
}

@media (max-width: 992px) {
    .filter-section {
        flex-direction: column;
    }
    
    .filter-sidebar {
        width: 100%;
        position: static;
    }
    
    .hero-section h1 {
        font-size: 2.5em;
    }
    
    .products-grid {
        grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)) !important;
    }
}

@media (max-width: 768px) {
    .hero-section {
        padding: 60px 20px;
    }
    
    .hero-section h1 {
        font-size: 2em;
    }
    
    .hero-section p {
        font-size: 1em;
    }
    
    .content-wrapper {
        padding: 30px 15px;
    }
    
    .products-grid {
        grid-template-columns: repeat(auto-fill, minmax(240px, 1fr)) !important;
        gap: 20px !important;
    }
    
    .product-thumb {
        height: 280px !important;
    }
}

@media (max-width: 576px) {
    .hero-section {
        padding: 40px 15px;
    }
    
    .hero-section h1 {
        font-size: 1.75em;
    }
    
    .search-section {
        padding: 30px 15px;
    }
    
    .products-grid {
        grid-template-columns: 1fr !important;
        gap: 20px !important;
    }
    
    .product-thumb {
        height: 320px !important;
    }
}
</style>

<div class="product-page-wrapper">
    <!-- Hero Section -->
    <div class="hero-section">
        <img src="uploads/banner.jpg" alt="Banner" class="hero-image">
            <h1>Discover Our Products</h1>
            <p>Explore our carefully curated collection of premium products. From the latest trends to timeless classics, find exactly what you're looking for.</p>
    </div>

    <!-- Search Section -->
    <div class="search-section">
        <div class="search-box">
            <?php $searchValue = isset($_GET['search']) ? $_GET['search'] : ''; ?>
        <input type="text" id="searchInput" placeholder="🔍 Search products by name..." value="<?php echo htmlspecialchars($searchValue); ?>">
    
        </div>
    </div>

    <!-- Main Content -->
    <div class="content-wrapper">
        <div class="filter-section">
            <!-- Left Sidebar - Filters -->
            <div class="filter-sidebar">
                <h3>Filters</h3>
                
                <div class="price-filter">
                    <h4>Price Range</h4>
                    <div class="price-slider-container">
                        <input type="range" id="minPrice" class="price-slider" min="0" max="50000" value="0" step="100">
                        <div class="price-values">
                            <span>Min: ₱<span id="minPriceValue">0</span></span>
                        </div>
                    </div>
                    <div class="price-slider-container">
                        <input type="range" id="maxPrice" class="price-slider" min="0" max="50000" value="50000" step="100">
                        <div class="price-values">
                            <span>Max: ₱<span id="maxPriceValue">50,000</span></span>
                        </div>
                    </div>
                    <button class="reset-btn" onclick="resetFilters()">Reset Filters</button>
                </div>
            </div>
            
            <!-- Right Side - Products -->
            <div class="products-content">
                <div class="filter-header">
                    <div class="results-count" id="resultsCount">Loading products...</div>
                </div>
                
                <div class="products-grid" id="productsGrid">
                <?php
                $sql = "SELECT i.item_id AS itemId, i.description AS item_name, i.image_path, i.sell_price, s.quantity AS stock
                        FROM item i
                        INNER JOIN stock s USING(item_id)
                        ORDER BY i.item_id ASC";
                $results = mysqli_query($conn, $sql);

                if ($results) {
                    while ($row = mysqli_fetch_assoc($results)) {
                        $item_name = htmlspecialchars($row['item_name'] ?? 'Unnamed Product');

                        $price = number_format($row['sell_price'], 2);
                        $itemId = $row['itemId'];
                        $stock = (int)$row['stock'];
                        $raw_price = $row['sell_price'];

                        $images = json_decode($row['image_path'], true);
                        if (!is_array($images)) $images = ['uploads/default.png'];

                        $processedImages = [];
                        foreach ($images as $img) {
                            if (file_exists($img)) $processedImages[] = $img;
                        }
                        if (empty($processedImages)) $processedImages = ['uploads/default.png'];

                        $stockClass = ($stock > 0) ? '' : 'out-of-stock';
                        $stockBadge = ($stock > 0) ? 'In Stock' : 'Out of Stock';
                        $stockBadgeClass = ($stock > 0) ? '' : 'out';

                        echo '<div class="product ' . $stockClass . '" data-price="' . $raw_price . '">';
                        echo '<a href="/lensify/e-commerce/product_details.php?id=' . $itemId . '">';
                        echo '<div class="product-thumb">';
                        echo '<span class="stock-badge ' . $stockBadgeClass . '">' . $stockBadge . '</span>';
                        foreach ($processedImages as $index => $img) {
                            $cacheBuster = file_exists($img) ? filemtime($img) : time();
                            echo '<img src="' . $img . '?v=' . $cacheBuster . '" class="rotating-image" style="opacity:' . ($index === 0 ? 1 : 0) . ';">';
                        }
                        echo '</div>'; // end product-thumb

                        echo '<div class="product-info">';
                        echo '<div class="product-name">' . $item_name . '</div>';
                        echo '<div class="product-price ' . $stockClass . '">';
                        echo ($stock > 0) ? '₱' . $price : 'OUT OF STOCK';
                        echo '</div>';
                        echo '</div>'; // end product-info

                        echo '</a>';
                        echo '</div>'; // end product
                    } // end while
                } // end if
                ?>
                </div> <!-- end products-grid -->

                
                <div class="no-results" id="noResults">
                    <h3>No products found</h3>
                    <p>Try adjusting your search or filter criteria</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
// Rotating image script
var productThumbs = document.querySelectorAll('.product-thumb');
for (var i = 0; i < productThumbs.length; i++) {
    (function(container) {
        var imgs = container.querySelectorAll('img.rotating-image');
        var current = 0;
        if(imgs.length > 1){
            setInterval(function() {
                imgs[current].style.opacity = 0;
                current = (current + 1) % imgs.length;
                imgs[current].style.opacity = 1;
            }, 5000);
        }
    })(productThumbs[i]);
}

// Filter functionality
var searchInput = document.getElementById('searchInput');
var minPriceSlider = document.getElementById('minPrice');
var maxPriceSlider = document.getElementById('maxPrice');
var minPriceValue = document.getElementById('minPriceValue');
var maxPriceValue = document.getElementById('maxPriceValue');
var productsGrid = document.getElementById('productsGrid');
var noResults = document.getElementById('noResults');
var resultsCount = document.getElementById('resultsCount');

minPriceSlider.addEventListener('input', function() {
    minPriceValue.textContent = parseInt(this.value).toLocaleString();
    if (parseInt(this.value) > parseInt(maxPriceSlider.value)) {
        this.value = maxPriceSlider.value;
        minPriceValue.textContent = parseInt(this.value).toLocaleString();
    }
    filterProducts();
});

maxPriceSlider.addEventListener('input', function() {
    maxPriceValue.textContent = parseInt(this.value).toLocaleString();
    if (parseInt(this.value) < parseInt(minPriceSlider.value)) {
        this.value = minPriceSlider.value;
        maxPriceValue.textContent = parseInt(this.value).toLocaleString();
    }
    filterProducts();
});

searchInput.addEventListener('input', filterProducts);

function filterProducts() {
    var searchTerm = searchInput.value.toLowerCase();
    var minPrice = parseInt(minPriceSlider.value);
    var maxPrice = parseInt(maxPriceSlider.value);
    var products = document.querySelectorAll('.product');
    var visibleCount = 0;

    products.forEach(function(product) {
        var productName = product.querySelector('.product-name').textContent.toLowerCase();
        var productPrice = parseFloat(product.getAttribute('data-price'));

        var matchesSearch = productName.includes(searchTerm);
        var matchesPrice = productPrice >= minPrice && productPrice <= maxPrice;

        if (matchesSearch && matchesPrice) {
            product.style.display = 'flex';
            product.style.setProperty('display', 'flex', 'important');
            visibleCount++;
        } else {
            product.style.display = 'none';
            product.style.setProperty('display', 'none', 'important');
        }
    });

    // Show/Hide product grid & no-results message
    if (visibleCount === 0) {
        productsGrid.style.display = 'none';
        noResults.style.display = 'block';
    } else {
        productsGrid.style.display = 'grid';
        noResults.style.display = 'none';
    }

    // Update results text
    resultsCount.textContent = 'Showing ' + visibleCount + ' product' + (visibleCount !== 1 ? 's' : '');
}

function resetFilters() {
    searchInput.value = '';
    minPriceSlider.value = 0;
    maxPriceSlider.value = 50000;
    minPriceValue.textContent = '0';
    maxPriceValue.textContent = '50,000';
    filterProducts();
}

// MUST be kept — initializes filter on page load
filterProducts();
</script>


<?php
include('./includes/footer.php'); ?>
