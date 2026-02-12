<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'customer') {
    $_SESSION['auth_error'] = 'Please log in as admin to access this page.';
    header("Location: ./user/login.php");
    exit();
}
include('./includes/header.php');
include('./includes/config.php');
?>

<style>

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

.main-content {
    padding: 0 !important;
    margin: 0 !important;
    max-width: 100% !important;
    width: 100% !important;
}

.product-page-wrapper {
    background: transparent !important;
    width: 100%;
    padding: 0;
    margin: 0;
}

body {
    background: #000000;
    background-attachment: fixed;
    min-height: 100vh;
}

 
.hero-section {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 100px 20px;
    text-align: center;
    position: relative;
    overflow: hidden;
    width: 100%;
    margin: 0;
    border-bottom: 3px solid rgba(255, 255, 255, 0.1);
}

.hero-section::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: radial-gradient(circle at 30% 50%, rgba(157, 78, 221, 0.3) 0%, transparent 50%),
                radial-gradient(circle at 70% 50%, rgba(199, 125, 255, 0.3) 0%, transparent 50%);
    animation: pulse 8s ease-in-out infinite;
}

@keyframes pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.6; }
}

.hero-image {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    object-fit: cover;
    opacity: 0.15;
    z-index: 1;
    mix-blend-mode: overlay;
}

.hero-content {
    position: relative;
    z-index: 2;
    max-width: 900px;
    margin: 0 auto;
}

.hero-section h1 {
    font-size: 3.5em;
    margin-bottom: 25px;
    font-weight: 800;
    letter-spacing: -2px;
    text-shadow: 2px 4px 20px rgba(0, 0, 0, 0.3);
    background: linear-gradient(135deg, #ffffff 0%, #e0b3ff 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.hero-section p {
    font-size: 1.3em;
    opacity: 0.95;
    line-height: 1.8;
    text-shadow: 1px 2px 10px rgba(0, 0, 0, 0.2);
}

 
.search-section {
    width: 100%;
    background: rgba(10, 0, 21, 0.6);
    backdrop-filter: blur(20px);
    padding: 50px 20px;
    text-align: center;
    border-bottom: 1px solid rgba(157, 78, 221, 0.2);
}

.search-box {
    max-width: 700px;
    margin: 0 auto;
    position: relative;
}

.search-box input {
    width: 100%;
    padding: 20px 30px;
    border: 2px solid rgba(157, 78, 221, 0.4);
    border-radius: 50px;
    font-size: 17px;
    transition: all 0.3s;
    background: rgba(255, 255, 255, 0.05);
    color: white;
    box-shadow: 0 8px 32px rgba(107, 0, 179, 0.2);
}

.search-box input::placeholder {
    color: rgba(255, 255, 255, 0.5);
}

.search-box input:focus {
    outline: none;
    border-color: #9d4edd;
    box-shadow: 0 0 0 4px rgba(157, 78, 221, 0.2),
                0 8px 32px rgba(107, 0, 179, 0.4);
    background: rgba(255, 255, 255, 0.1);
}

 
.content-wrapper {
    max-width: 1400px;
    margin: 0 auto;
    padding: 50px 20px;
}

.filter-section {
    display: flex;
    gap: 35px;
    align-items: flex-start;
}

 
.filter-sidebar {
    background: linear-gradient(135deg, rgba(107, 0, 179, 0.15) 0%, rgba(45, 27, 78, 0.2) 100%);
    backdrop-filter: blur(20px);
    border: 1px solid rgba(157, 78, 221, 0.3);
    padding: 35px;
    border-radius: 20px;
    width: 320px;
    flex-shrink: 0;
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
    position: sticky;
    top: 100px;
}

.filter-sidebar h3 {
    margin-bottom: 30px;
    color: white;
    font-size: 1.5em;
    font-weight: 800;
    border-bottom: 3px solid #9d4edd;
    padding-bottom: 15px;
    text-transform: uppercase;
    letter-spacing: 1px;
}

 
.products-content {
    flex: 1;
    min-width: 0;
}

.filter-header {
    background: linear-gradient(135deg, rgba(107, 0, 179, 0.15) 0%, rgba(45, 27, 78, 0.2) 100%);
    backdrop-filter: blur(20px);
    border: 1px solid rgba(157, 78, 221, 0.3);
    padding: 30px;
    border-radius: 20px;
    margin-bottom: 35px;
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.results-count {
    color: white;
    font-weight: 700;
    font-size: 1.3em;
    text-shadow: 1px 2px 10px rgba(0, 0, 0, 0.3);
}

 
.price-filter h4 {
    margin-bottom: 25px;
    color: white;
    font-size: 1.1em;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 1.5px;
}

.price-slider-container {
    margin: 30px 0;
}

.price-slider {
    width: 100%;
    height: 10px;
    background: linear-gradient(to right, #2d1b4e, #9d4edd);
    border-radius: 10px;
    outline: none;
    -webkit-appearance: none;
    cursor: pointer;
    box-shadow: 0 4px 15px rgba(157, 78, 221, 0.3);
}

.price-slider::-webkit-slider-thumb {
    -webkit-appearance: none;
    appearance: none;
    width: 26px;
    height: 26px;
    background: linear-gradient(135deg, #9d4edd 0%, #c77dff 100%);
    border: 3px solid white;
    cursor: pointer;
    border-radius: 50%;
    box-shadow: 0 4px 12px rgba(157, 78, 221, 0.5);
    transition: all 0.2s;
}

.price-slider::-webkit-slider-thumb:hover {
    transform: scale(1.2);
    box-shadow: 0 6px 20px rgba(157, 78, 221, 0.7);
}

.price-slider::-moz-range-thumb {
    width: 26px;
    height: 26px;
    background: linear-gradient(135deg, #9d4edd 0%, #c77dff 100%);
    border: 3px solid white;
    cursor: pointer;
    border-radius: 50%;
    box-shadow: 0 4px 12px rgba(157, 78, 221, 0.5);
}

.price-values {
    display: flex;
    justify-content: space-between;
    margin-top: 15px;
    font-weight: 700;
    font-size: 1.05em;
    color: #c77dff;
    text-shadow: 1px 2px 8px rgba(0, 0, 0, 0.3);
}

.reset-btn {
    background: linear-gradient(135deg, #6b00b3 0%, #9d4edd 100%);
    color: white;
    border: 2px solid rgba(255, 255, 255, 0.2);
    padding: 16px 28px;
    border-radius: 50px;
    cursor: pointer;
    font-size: 16px;
    margin-top: 35px;
    transition: all 0.3s;
    width: 100%;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 1.5px;
    box-shadow: 0 6px 25px rgba(107, 0, 179, 0.4);
}

.reset-btn:hover {
    transform: translateY(-3px);
    box-shadow: 0 10px 35px rgba(107, 0, 179, 0.6);
    background: linear-gradient(135deg, #7d00cc 0%, #b35eff 100%);
}

 
.products-grid {
    display: grid !important;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)) !important;
    gap: 35px !important;
    padding: 0 !important;
    margin: 0 !important;
    list-style: none !important;
}

.product {
    width: 100% !important;
    background: linear-gradient(135deg, rgba(10, 0, 21, 0.8) 0%, rgba(45, 27, 78, 0.6) 100%) !important;
    backdrop-filter: blur(10px) !important;
    border: 1px solid rgba(157, 78, 221, 0.3) !important;
    border-radius: 25px !important;
    overflow: hidden !important;
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.4) !important;
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1) !important;
    display: flex !important;
    flex-direction: column !important;
    margin: 0 !important;
}

.product:hover {
    transform: translateY(-15px) scale(1.02) !important;
    box-shadow: 0 20px 50px rgba(157, 78, 221, 0.4) !important;
    border-color: rgba(199, 125, 255, 0.6) !important;
}

.product.out-of-stock {
    opacity: 0.5;
}

.product > a {
    text-decoration: none !important;
    color: inherit !important;
    display: flex !important;
    flex-direction: column !important;
    height: 100% !important;
}

 
.product-thumb {
    position: relative;
    width: 100% !important;
    height: 340px !important;
    overflow: hidden;
    background: linear-gradient(135deg, #1a0033 0%, #2d1b4e 100%);
    flex-shrink: 0;
}

.product-thumb img {
    width: 100% !important;
    height: 100% !important;
    object-fit: cover !important;
    position: absolute !important;
    top: 0;
    left: 0;
    transition: opacity 1s ease, transform 0.6s ease;
}

.product:hover .product-thumb img {
    transform: scale(1.15);
}

 
.stock-badge {
    position: absolute;
    top: 18px;
    right: 18px;
    background: linear-gradient(135deg, #00c853 0%, #64dd17 100%);
    backdrop-filter: blur(10px);
    color: white;
    padding: 10px 20px;
    border-radius: 30px;
    font-size: 13px;
    font-weight: 800;
    z-index: 10;
    text-transform: uppercase;
    letter-spacing: 1px;
    box-shadow: 0 6px 20px rgba(0, 200, 83, 0.4);
    border: 2px solid rgba(255, 255, 255, 0.3);
}

.stock-badge.out {
    background: linear-gradient(135deg, #d32f2f 0%, #f44336 100%);
    box-shadow: 0 6px 20px rgba(211, 47, 47, 0.4);
}

 
.product-info {
    padding: 28px !important;
    flex: 1;
    display: flex !important;
    flex-direction: column !important;
    gap: 15px;
    background: linear-gradient(180deg, rgba(10, 0, 21, 0.4) 0%, rgba(10, 0, 21, 0.8) 100%);
}

.product-name {
    font-size: 1.2em !important;
    font-weight: 700 !important;
    color: white !important;
    line-height: 1.5 !important;
    min-height: 60px;
    margin: 0 !important;
    text-shadow: 1px 2px 8px rgba(0, 0, 0, 0.3);
}

.product-price {
    font-size: 2em !important;
    font-weight: 900 !important;
    background: linear-gradient(135deg, #9d4edd 0%, #c77dff 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    margin-top: auto !important;
    margin-bottom: 0 !important;
    text-shadow: 2px 4px 15px rgba(157, 78, 221, 0.3);
}

.product-price.out-of-stock {
    background: linear-gradient(135deg, #d32f2f 0%, #f44336 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    font-size: 1.3em !important;
    font-weight: 800 !important;
}

 
.no-results {
    text-align: center;
    padding: 120px 40px;
    color: rgba(255, 255, 255, 0.7);
    display: none;
    background: linear-gradient(135deg, rgba(107, 0, 179, 0.15) 0%, rgba(45, 27, 78, 0.2) 100%);
    backdrop-filter: blur(20px);
    border: 1px solid rgba(157, 78, 221, 0.3);
    border-radius: 25px;
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
}

.no-results h3 {
    font-size: 2.5em;
    margin-bottom: 25px;
    color: white;
    font-weight: 800;
    text-shadow: 2px 4px 15px rgba(0, 0, 0, 0.3);
}

.no-results p {
    font-size: 1.3em;
    color: rgba(199, 125, 255, 0.8);
}

 
@media (max-width: 1200px) {
    .products-grid {
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)) !important;
        gap: 30px !important;
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
        font-size: 2.8em;
    }
    
    .products-grid {
        grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)) !important;
    }
}

@media (max-width: 768px) {
    .hero-section {
        padding: 70px 20px;
    }
    
    .hero-section h1 {
        font-size: 2.2em;
    }
    
    .hero-section p {
        font-size: 1.1em;
    }
    
    .content-wrapper {
        padding: 40px 15px;
    }
    
    .products-grid {
        grid-template-columns: repeat(auto-fill, minmax(240px, 1fr)) !important;
        gap: 25px !important;
    }
    
    .product-thumb {
        height: 300px !important;
    }
}

@media (max-width: 576px) {
    .hero-section {
        padding: 50px 15px;
    }
    
    .hero-section h1 {
        font-size: 1.9em;
    }
    
    .search-section {
        padding: 35px 15px;
    }
    
    .products-grid {
        grid-template-columns: 1fr !important;
        gap: 25px !important;
    }
    
    .product-thumb {
        height: 340px !important;
    }
}
</style>

<div class="product-page-wrapper">
     
    <div class="hero-section">
        <img src="uploads/banner.jpg" alt="" class="hero-image">
        <div class="hero-content">
            <h1>Discover Our Products</h1>
            <p>Explore our carefully curated collection of premium products. From the latest trends to timeless classics, find exactly what you're looking for.</p>
        </div>
    </div>

     
    <div class="search-section">
        <div class="search-box">
            <?php $searchValue = isset($_GET['search']) ? $_GET['search'] : ''; ?>
            <input type="text" id="searchInput" placeholder="🔍 Search products by name..." value="<?php echo htmlspecialchars($searchValue); ?>">
        </div>
    </div>

     
    <div class="content-wrapper">
        <div class="filter-section">
             
            <div class="filter-sidebar">
                <h3>Filters</h3>
                
                <div class="price-filter">
                    <h4>Price Range</h4>
                    <div class="price-slider-container">
                        <input type="range" id="minPrice" class="price-slider" min="0" max="500000" value="0" step="100">
                        <div class="price-values">
                            <span>Min: ₱<span id="minPriceValue">0</span></span>
                        </div>
                    </div>
                    <div class="price-slider-container">
                        <input type="range" id="maxPrice" class="price-slider" min="0" max="500000" value="500000" step="100">
                        <div class="price-values">
                            <span>Max: ₱<span id="maxPriceValue">500,000</span></span>
                        </div>
                    </div>
                    <button class="reset-btn" onclick="resetFilters()">Reset Filters</button>
                </div>
            </div>
            
             
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
                    $stmt = $conn->prepare($sql);
                    $stmt->execute();
                    $results = $stmt->get_result();

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
                        echo '</div>';

                        echo '<div class="product-info">';
                        echo '<div class="product-name">' . $item_name . '</div>';
                        echo '<div class="product-price ' . $stockClass . '">';
                        echo ($stock > 0) ? '₱' . $price : 'OUT OF STOCK';
                        echo '</div>';
                        echo '</div>';

                        echo '</a>';
                        echo '</div>';
                    }
                }
                ?>
                </div>

                <div class="no-results" id="noResults">
                    <h3>No products found</h3>
                    <p>Try adjusting your search or filter criteria</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
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

    if (visibleCount === 0) {
        productsGrid.style.display = 'none';
        noResults.style.display = 'block';
    } else {
        productsGrid.style.display = 'grid';
        noResults.style.display = 'none';
    }

    resultsCount.textContent = 'Showing ' + visibleCount + ' product' + (visibleCount !== 1 ? 's' : '');
}

function resetFilters() {
    searchInput.value = '';
    minPriceSlider.value = 0;
    maxPriceSlider.value = 500000;
    minPriceValue.textContent = '0';
    maxPriceValue.textContent = '500,000';
    filterProducts();
}

filterProducts();
</script>

<?php
include('./includes/footer.php');
?>