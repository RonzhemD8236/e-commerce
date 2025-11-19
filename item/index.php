<?php
session_start();

// Authentication check
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    $_SESSION['auth_error'] = 'Please log in as admin to access this page.';
    header("Location: ../admin/login.php");
    exit();
}

include('../admin/header.php'); 
include('../includes/config.php');

// Check for search keyword and category filter
$keyword = isset($_GET['search']) ? trim($_GET['search']) : '';
$category_filter = isset($_GET['category']) ? trim($_GET['category']) : '';

// Build query conditions
$conditions = [];
$params = [];
$types = '';

if ($keyword) {
    $conditions[] = "(LOWER(i.description) LIKE LOWER(?) OR LOWER(i.short_description) LIKE LOWER(?) OR LOWER(i.specifications) LIKE LOWER(?) OR LOWER(i.category) LIKE LOWER(?))";
    $search_param = "%{$keyword}%";
    $params = array_merge($params, [$search_param, $search_param, $search_param, $search_param]);
    $types .= 'ssss';
}

if ($category_filter) {
    $conditions[] = "i.category = ?";
    $params[] = $category_filter;
    $types .= 's';
}

$where_clause = !empty($conditions) ? "WHERE " . implode(" AND ", $conditions) : "";

$stmt = $conn->prepare("SELECT i.*, s.quantity 
                        FROM item i 
                        LEFT JOIN stock s ON i.item_id = s.item_id 
                        $where_clause 
                        ORDER BY i.item_id DESC");

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();
$itemCount = $result->num_rows;
?>

<style>
/* Hero Banner */
.hero-banner {
    background: linear-gradient(135deg, rgba(0, 0, 0, 0.7) 0%, rgba(0, 0, 0, 0.85) 100%),
                url('https://images.unsplash.com/photo-1526406915894-7bcd65f60845?w=1200&h=400&fit=crop') center/cover;
    color: white;
    padding: 60px 40px;
    border-radius: 12px;
    margin-bottom: 30px;
    position: relative;
    overflow: hidden;
}

.hero-banner h1 {
    font-size: 2.5rem;
    font-weight: 700;
    margin-bottom: 15px;
}

.hero-banner p {
    font-size: 1.1rem;
    opacity: 0.95;
    max-width: 800px;
}

/* Search and Action Bar */
.search-action-bar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 25px;
    gap: 20px;
    flex-wrap: wrap;
}

.search-box {
    flex: 1;
    max-width: 400px;
    min-width: 250px;
}

.search-box input {
    width: 100%;
    padding: 10px 15px;
    border: 1px solid #ddd;
    border-radius: 6px;
    font-size: 0.95rem;
}

.search-box input:focus {
    outline: none;
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.btn-add-item {
    padding: 10px 20px;
    background-color: #000;
    color: white;
    border: none;
    border-radius: 6px;
    font-weight: 500;
    cursor: pointer;
    text-decoration: none;
    display: inline-block;
    transition: all 0.3s;
}

.btn-add-item:hover {
    background-color: #1f2937;
    color: white;
    transform: translateY(-2px);
}

/* Category Filter Tabs */
.category-tabs {
    display: flex;
    gap: 0;
    border-bottom: 2px solid #e5e7eb;
    margin-bottom: 25px;
    overflow-x: auto;
}

.category-tab {
    padding: 12px 24px;
    background: none;
    border: none;
    color: #6b7280;
    font-weight: 500;
    cursor: pointer;
    position: relative;
    text-decoration: none;
    transition: all 0.2s;
    white-space: nowrap;
}

.category-tab:hover {
    color: #667eea;
    background-color: #f9fafb;
}

.category-tab.active {
    color: #667eea;
}

.category-tab.active::after {
    content: '';
    position: absolute;
    bottom: -2px;
    left: 0;
    right: 0;
    height: 2px;
    background-color: #667eea;
}

/* Table Styling */
.products-table {
    background: white;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.products-table table {
    width: 100%;
    border-collapse: collapse;
}

.products-table thead {
    background-color: #f9fafb;
    border-bottom: 1px solid #e5e7eb;
}

.products-table th {
    padding: 16px;
    text-align: left;
    font-weight: 600;
    font-size: 0.875rem;
    color: #374151;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

.products-table td {
    padding: 16px;
    border-bottom: 1px solid #f3f4f6;
    vertical-align: middle;
}

.products-table tbody tr:hover {
    background-color: #f9fafb;
}

/* Stock Status */
.low-stock {
    background-color: #fef3c7 !important;
}

.out-of-stock {
    background-color: #fee2e2 !important;
}

.stock-badge {
    display: inline-block;
    padding: 4px 12px;
    border-radius: 12px;
    font-size: 0.875rem;
    font-weight: 500;
}

.stock-in {
    background-color: #d1fae5;
    color: #065f46;
}

.stock-low {
    background-color: #fef3c7;
    color: #92400e;
}

.stock-out {
    background-color: #fee2e2;
    color: #991b1b;
}

/* Category Badge */
.category-badge {
    display: inline-block;
    padding: 4px 12px;
    border-radius: 12px;
    font-size: 0.875rem;
    font-weight: 500;
    background-color: #dbeafe;
    color: #1e40af;
}

/* Product Images */
.product-images {
    display: flex;
    gap: 5px;
    flex-wrap: wrap;
}

.product-image {
    width: 60px;
    height: 60px;
    border-radius: 6px;
    object-fit: cover;
    cursor: pointer;
    transition: transform 0.3s;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.product-image:hover {
    transform: scale(1.1);
}

.image-count-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 60px;
    height: 60px;
    background-color: #e5e7eb;
    border-radius: 6px;
    font-size: 0.875rem;
    font-weight: 600;
    color: #4b5563;
}

/* Action Buttons */
.action-buttons {
    display: flex;
    gap: 8px;
}

.btn-view {
    padding: 6px 16px;
    background-color: #3b82f6;
    color: white;
    border: none;
    border-radius: 6px;
    font-size: 0.875rem;
    font-weight: 500;
    cursor: pointer;
    text-decoration: none;
    display: inline-block;
    transition: all 0.3s;
}

.btn-view:hover {
    background-color: #2563eb;
    color: white;
}

.btn-edit {
    padding: 6px 16px;
    background-color: #6b7280;
    color: white;
    border: none;
    border-radius: 6px;
    font-size: 0.875rem;
    font-weight: 500;
    cursor: pointer;
    text-decoration: none;
    display: inline-block;
    transition: all 0.3s;
}

.btn-edit:hover {
    background-color: #4b5563;
    color: white;
}

.btn-delete {
    padding: 6px 16px;
    background-color: #ef4444;
    color: white;
    border: none;
    border-radius: 6px;
    font-size: 0.875rem;
    font-weight: 500;
    cursor: pointer;
    text-decoration: none;
    display: inline-block;
    transition: all 0.3s;
}

.btn-delete:hover {
    background-color: #dc2626;
    color: white;
}

/* Modal Styles */
.modal {
    display: none;
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5);
    animation: fadeIn 0.3s;
}

.modal.show {
    display: flex;
    align-items: center;
    justify-content: center;
}

.modal-content {
    background-color: white;
    padding: 0;
    border-radius: 12px;
    width: 90%;
    max-width: 900px;
    max-height: 90vh;
    overflow-y: auto;
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
    animation: slideDown 0.3s;
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

@keyframes slideDown {
    from { transform: translateY(-50px); opacity: 0; }
    to { transform: translateY(0); opacity: 1; }
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px 30px;
    border-bottom: 1px solid #e5e7eb;
    background-color: #3b82f6;
    color: white;
    border-radius: 12px 12px 0 0;
}

.modal-header h2 {
    margin: 0;
    font-size: 1.5rem;
}

.close-modal {
    background: none;
    border: none;
    font-size: 1.5rem;
    cursor: pointer;
    color: white;
    padding: 0;
    width: 30px;
    height: 30px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.close-modal:hover {
    opacity: 0.8;
}

.modal-body {
    padding: 30px;
}

.spec-content {
    background-color: #f9fafb;
    padding: 20px;
    border-radius: 8px;
    white-space: pre-wrap;
    font-family: 'Courier New', monospace;
    font-size: 0.95rem;
    line-height: 1.6;
}

.image-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 15px;
}

.image-grid img {
    width: 100%;
    height: 200px;
    object-fit: cover;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

/* Empty State */
.empty-state {
    text-align: center;
    padding: 60px 20px;
    color: #6b7280;
}

.empty-state i {
    font-size: 4rem;
    color: #d1d5db;
    margin-bottom: 20px;
}

.empty-state h3 {
    font-size: 1.5rem;
    margin-bottom: 10px;
    color: #374151;
}

/* Alert Styles */
.alert {
    padding: 15px 20px;
    border-radius: 8px;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.alert-success {
    background-color: #d1fae5;
    color: #065f46;
    border-left: 4px solid #10b981;
}

.alert-danger {
    background-color: #fee2e2;
    color: #991b1b;
    border-left: 4px solid #ef4444;
}

.alert-dismissible {
    position: relative;
    padding-right: 45px;
}

.btn-close {
    position: absolute;
    right: 15px;
    top: 50%;
    transform: translateY(-50%);
    background: none;
    border: none;
    font-size: 1.2rem;
    cursor: pointer;
    opacity: 0.6;
}

.btn-close:hover {
    opacity: 1;
}
</style>

<div class="container-fluid px-4 py-4">
    <!-- Hero Banner -->
    <div class="hero-banner">
        <h1>Product Management</h1>
        <p>Manage and oversee product inventory, pricing, and specifications. Control stock levels and maintain product data securely for efficient business operations.</p>
    </div>

    <!-- Search and Add Item Bar -->
    <div class="search-action-bar">
        <div class="search-box">
            <input 
                type="text" 
                id="searchProduct" 
                placeholder="Search by name, description, specs..." 
                value="<?= htmlspecialchars($keyword) ?>"
            >
        </div>
        <div>
            <span style="display: inline-block; padding: 10px 20px; background: #f3f4f6; border-radius: 6px; font-weight: 600; margin-right: 10px;">
                Total Products: <?= $itemCount ?>
            </span>
            <a href="create.php" class="btn-add-item">Add New Product</a>
        </div>
    </div>

    <!-- Alert Messages -->
    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="alert alert-success alert-dismissible">
            <i class="fas fa-check-circle"></i>
            <span><?= htmlspecialchars($_SESSION['success_message']) ?></span>
            <button type="button" class="btn-close" onclick="this.parentElement.style.display='none'">&times;</button>
        </div>
        <?php unset($_SESSION['success_message']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="alert alert-danger alert-dismissible">
            <i class="fas fa-exclamation-circle"></i>
            <span><?= htmlspecialchars($_SESSION['error_message']) ?></span>
            <button type="button" class="btn-close" onclick="this.parentElement.style.display='none'">&times;</button>
        </div>
        <?php unset($_SESSION['error_message']); ?>
    <?php endif; ?>

    <!-- Category Filter Tabs -->
    <div class="category-tabs">
        <a href="?" class="category-tab <?= empty($category_filter) ? 'active' : '' ?>">
            All Products
        </a>
        <a href="?category=DSLR+Cameras" class="category-tab <?= $category_filter === 'DSLR Cameras' ? 'active' : '' ?>">
            DSLR Cameras
        </a>
        <a href="?category=Mirrorless+Cameras" class="category-tab <?= $category_filter === 'Mirrorless Cameras' ? 'active' : '' ?>">
            Mirrorless Cameras
        </a>
        <a href="?category=Action+Cameras" class="category-tab <?= $category_filter === 'Action Cameras' ? 'active' : '' ?>">
            Action Cameras
        </a>
        <a href="?category=Camera+Lenses" class="category-tab <?= $category_filter === 'Camera Lenses' ? 'active' : '' ?>">
            Camera Lenses
        </a>
        <a href="?category=Tripods+%26+Stabilizers" class="category-tab <?= $category_filter === 'Tripods & Stabilizers' ? 'active' : '' ?>">
            Tripods & Stabilizers
        </a>
        <a href="?category=Camera+Accessories" class="category-tab <?= $category_filter === 'Camera Accessories' ? 'active' : '' ?>">
            Camera Accessories
        </a>
    </div>

    <!-- Products Table -->
    <?php if ($result->num_rows > 0): ?>
    <div class="products-table">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Images</th>
                    <th>Product Name</th>
                    <th>Short Description</th>
                    <th>Category</th>
                    <th>Cost Price</th>
                    <th>Sell Price</th>
                    <th>Stock</th>
                    <th>Specs</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php while ($row = $result->fetch_assoc()): 
                $stock_class = '';
                if ($row['quantity'] == 0) {
                    $stock_class = 'out-of-stock';
                } elseif ($row['quantity'] <= 5) {
                    $stock_class = 'low-stock';
                }
            ?>
                <tr class="product-row <?= $stock_class ?>" data-name="<?= htmlspecialchars(strtolower($row['description'])) ?>" data-desc="<?= htmlspecialchars(strtolower($row['short_description'])) ?>">
                    <td><strong>#<?= htmlspecialchars($row['item_id']) ?></strong></td>
                    
                    <!-- Images -->
                    <td>
                        <div class="product-images">
                            <?php
                            $images = json_decode($row['image_path'], true);
                            $display_count = 0;
                            if (!empty($images) && is_array($images)) {
                                foreach ($images as $img) {
                                    if ($display_count >= 2) break;
                                    $fullPath = "../" . $img;
                                    if (!empty($img) && file_exists($fullPath)) {
                                        echo "<img src='../" . htmlspecialchars($img) . "' 
                                              class='product-image' 
                                              alt='Product image'
                                              onclick='openImageModal(" . json_encode($images) . ")'>";
                                        $display_count++;
                                    }
                                }
                                if (count($images) > 2) {
                                    echo "<span class='image-count-badge'>+" . (count($images) - 2) . "</span>";
                                }
                            } else {
                                echo "<img src='../uploads/default.png' class='product-image' alt='Default image'>";
                            }
                            ?>
                        </div>
                    </td>

                    <td><strong><?= htmlspecialchars($row['description']) ?></strong></td>
                    <td><?= htmlspecialchars(substr($row['short_description'], 0, 60)) . (strlen($row['short_description']) > 60 ? '...' : '') ?></td>
                    <td><span class="category-badge"><?= htmlspecialchars($row['category']) ?></span></td>
                    <td><strong style="color: #059669;">₱<?= number_format($row['cost_price'], 2) ?></strong></td>
                    <td><strong style="color: #3b82f6;">₱<?= number_format($row['sell_price'], 2) ?></strong></td>
                    
                    <!-- Stock Badge -->
                    <td>
                        <?php if ($row['quantity'] == 0): ?>
                            <span class="stock-badge stock-out">Out of Stock</span>
                        <?php elseif ($row['quantity'] <= 5): ?>
                            <span class="stock-badge stock-low"><?= htmlspecialchars($row['quantity']) ?> (Low)</span>
                        <?php else: ?>
                            <span class="stock-badge stock-in"><?= htmlspecialchars($row['quantity']) ?></span>
                        <?php endif; ?>
                    </td>

                    <!-- Specs Button -->
                    <td>
                        <button class="btn-view" onclick='openSpecModal(<?= json_encode($row["description"]) ?>, <?= json_encode($row["specifications"]) ?>)'>
                            View
                        </button>
                    </td>

                    <!-- Actions -->
                    <td>
                        <div class="action-buttons">
                            <a href="edit.php?id=<?= htmlspecialchars($row['item_id']) ?>" class="btn-edit" title="Edit">
                                <i class="fas fa-edit"></i>
                            </a>
                            <a href="delete.php?id=<?= htmlspecialchars($row['item_id']) ?>" 
                               class="btn-delete" 
                               onclick="return confirm('⚠️ Are you sure you want to delete this product?\n\nProduct: <?= htmlspecialchars($row['description']) ?>\n\nThis action cannot be undone!');" 
                               title="Delete">
                                <i class="fas fa-trash-alt"></i>
                            </a>
                        </div>
                    </td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    </div>
    <?php else: ?>
    <div class="empty-state">
        <i class="fas fa-inbox"></i>
        <h3>No Products Found</h3>
        <?php if ($keyword || $category_filter): ?>
            <p>No results match your search criteria.</p>
            <a href="index.php" class="btn-add-item" style="margin-top: 20px;">View All Products</a>
        <?php else: ?>
            <p>Start by adding your first product to the inventory.</p>
            <a href="create.php" class="btn-add-item" style="margin-top: 20px;">Add Product</a>
        <?php endif; ?>
    </div>
    <?php endif; ?>
</div>

<!-- Specifications Modal -->
<div id="specModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2 id="specModalTitle">Product Specifications</h2>
            <button class="close-modal" onclick="closeSpecModal()">&times;</button>
        </div>
        <div class="modal-body">
            <div class="spec-content" id="specModalContent"></div>
        </div>
    </div>
</div>

<!-- Images Modal -->
<div id="imageModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Product Images</h2>
            <button class="close-modal" onclick="closeImageModal()">&times;</button>
        </div>
        <div class="modal-body">
            <div class="image-grid" id="imageModalContent"></div>
        </div>
    </div>
</div>

<script>
// Specifications Modal
function openSpecModal(title, specs) {
    document.getElementById('specModalTitle').textContent = 'Specifications - ' + title;
    document.getElementById('specModalContent').textContent = specs;
    document.getElementById('specModal').classList.add('show');
}

function closeSpecModal() {
    document.getElementById('specModal').classList.remove('show');
}

// Images Modal
function openImageModal(images) {
    const container = document.getElementById('imageModalContent');
    container.innerHTML = '';
    
    images.forEach(img => {
        const imgElement = document.createElement('img');
        imgElement.src = '../' + img;
        imgElement.alt = 'Product image';
        container.appendChild(imgElement);
    });
    
    document.getElementById('imageModal').classList.add('show');
}

function closeImageModal() {
    document.getElementById('imageModal').classList.remove('show');
}

// Close modals when clicking outside
window.onclick = function(event) {
    const specModal = document.getElementById('specModal');
    const imageModal = document.getElementById('imageModal');
    
    if (event.target === specModal) {
        closeSpecModal();
    }
    if (event.target === imageModal) {
        closeImageModal();
    }
}

// Search functionality
document.getElementById('searchProduct').addEventListener('input', function() {
    const searchTerm = this.value.toLowerCase();
    document.querySelectorAll('.product-row').forEach(row => {
        const name = row.dataset.name;
        const desc = row.dataset.desc;
        
        if (name.includes(searchTerm) || desc.includes(searchTerm)) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
});

// Search on enter key
document.getElementById('searchProduct').addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        const searchTerm = this.value.trim();
        if (searchTerm) {
            window.location.href = '?search=' + encodeURIComponent(searchTerm);
        }
    }
});
</script>

<?php 
$stmt->close();
include('../includes/footer.php'); 
?>
