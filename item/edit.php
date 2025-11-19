<?php
session_start();

// Authentication check
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    $_SESSION['auth_error'] = 'Please log in as admin to access this page.';
    header("Location: ../admin/login.php");
    exit();
}

include('../includes/config.php');
include('../includes/header.php');
include('../admin/header.php');

// Check if item_id is provided
if (!isset($_GET['id'])) {
    echo "<script>
            alert('No item selected.');
            window.location.href = 'index.php';
          </script>";
    exit;
}

$item_id = intval($_GET['id']);

// Fetch item details using prepared statement
$stmt = $conn->prepare("SELECT item.*, stock.quantity 
                        FROM item 
                        LEFT JOIN stock USING (item_id) 
                        WHERE item.item_id = ?");
$stmt->bind_param("i", $item_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    $stmt->close();
    echo "<script>
            alert('Item not found.');
            window.location.href = 'index.php';
          </script>";
    exit;
}

$row = $result->fetch_assoc();
$stmt->close();

// Use session values if available (after validation errors)
$desc       = $_SESSION['desc']        ?? $row['description'];
$short_desc = $_SESSION['short_desc']  ?? $row['short_description'];
$specs      = $_SESSION['specs']       ?? $row['specifications'];
$cost       = $_SESSION['cost']        ?? $row['cost_price'];
$sell       = $_SESSION['sell']        ?? $row['sell_price'];
$qty        = $_SESSION['qty']         ?? $row['quantity'];
$category   = $_SESSION['category']    ?? $row['category'];

// Capture errors if any
$descError       = $_SESSION['descError']       ?? '';
$shortDescError  = $_SESSION['shortDescError']  ?? '';
$specsError      = $_SESSION['specsError']      ?? '';
$costError       = $_SESSION['costError']       ?? '';
$sellError       = $_SESSION['sellError']       ?? '';
$qtyError        = $_SESSION['qtyError']        ?? '';
$categoryError   = $_SESSION['categoryError']   ?? '';
$imageError      = $_SESSION['imageError']      ?? '';

// Clear session errors
unset($_SESSION['descError'], $_SESSION['shortDescError'], $_SESSION['specsError'], $_SESSION['costError'], $_SESSION['sellError'], $_SESSION['qtyError'], $_SESSION['categoryError'], $_SESSION['imageError']);
unset($_SESSION['desc'], $_SESSION['short_desc'], $_SESSION['specs'], $_SESSION['cost'], $_SESSION['sell'], $_SESSION['qty'], $_SESSION['category']);
?>

<div class="container mt-4">
    <form action="update.php" method="POST" enctype="multipart/form-data" style="max-width: 800px;">
        <input type="hidden" name="item_id" value="<?= htmlspecialchars($row['item_id']) ?>">

        <!-- Item Name / Description -->
        <div class="mb-3">
            <label class="form-label">Item Name / Description <span class="text-danger">*</span></label>
            <?php if($descError): ?><small class="text-danger ms-2"><?= htmlspecialchars($descError) ?></small><?php endif; ?>
            <input type="text" name="description" class="form-control" value="<?= htmlspecialchars($desc) ?>" required>
        </div>

        <!-- Short Description -->
        <div class="mb-3">
            <label class="form-label">Short Description <span class="text-danger">*</span></label>
            <?php if($shortDescError): ?><small class="text-danger ms-2"><?= htmlspecialchars($shortDescError) ?></small><?php endif; ?>
            <textarea name="short_description" class="form-control" rows="2" required><?= htmlspecialchars($short_desc) ?></textarea>
        </div>

        <!-- Specifications -->
        <div class="mb-3">
            <label class="form-label">Specifications <span class="text-danger">*</span></label>
            <?php if($specsError): ?><small class="text-danger ms-2"><?= htmlspecialchars($specsError) ?></small><?php endif; ?>
            <textarea name="specifications" class="form-control" rows="4" required><?= htmlspecialchars($specs) ?></textarea>
        </div>

        <!-- Category -->
        <div class="mb-3">
            <label class="form-label">Category <span class="text-danger">*</span></label>
            <?php if($categoryError): ?><small class="text-danger ms-2"><?= htmlspecialchars($categoryError) ?></small><?php endif; ?>
            <select name="category" class="form-control" required>
                <option value="">-- Select Category --</option>
                <option value="DSLR Cameras" <?php if($category=="DSLR Cameras") echo "selected"; ?>>DSLR Cameras</option>
                <option value="Mirrorless Cameras" <?php if($category=="Mirrorless Cameras") echo "selected"; ?>>Mirrorless Cameras</option>
                <option value="Action Cameras" <?php if($category=="Action Cameras") echo "selected"; ?>>Action Cameras</option>
                <option value="Camera Lenses" <?php if($category=="Camera Lenses") echo "selected"; ?>>Camera Lenses</option>
                <option value="Tripods & Stabilizers" <?php if($category=="Tripods & Stabilizers") echo "selected"; ?>>Tripods & Stabilizers</option>
                <option value="Camera Accessories" <?php if($category=="Camera Accessories") echo "selected"; ?>>Camera Accessories</option>
            </select>
        </div>

        <!-- Cost -->
        <div class="mb-3">
            <label class="form-label">Cost Price <span class="text-danger">*</span></label>
            <?php if($costError): ?><small class="text-danger ms-2"><?= htmlspecialchars($costError) ?></small><?php endif; ?>
            <input type="number" step="0.01" name="cost_price" class="form-control" value="<?= htmlspecialchars($cost) ?>" required>
        </div>

        <!-- Sell -->
        <div class="mb-3">
            <label class="form-label">Sell Price <span class="text-danger">*</span></label>
            <?php if($sellError): ?><small class="text-danger ms-2"><?= htmlspecialchars($sellError) ?></small><?php endif; ?>
            <input type="number" step="0.01" name="sell_price" class="form-control" value="<?= htmlspecialchars($sell) ?>" required>
        </div>

        <!-- Quantity -->
        <div class="mb-3">
            <label class="form-label">Quantity <span class="text-danger">*</span></label>
            <?php if($qtyError): ?><small class="text-danger ms-2"><?= htmlspecialchars($qtyError) ?></small><?php endif; ?>
            <input type="number" name="quantity" class="form-control" value="<?= htmlspecialchars($qty) ?>" required>
        </div>

        <!-- Multiple Images -->
        <div class="mb-3">
            <label class="form-label">Product Images</label>
            <?php if($imageError): ?><small class="text-danger ms-2"><?= htmlspecialchars($imageError) ?></small><?php endif; ?>
            
            <div class="mb-2" id="imageContainer">
                <?php
                $images = json_decode($row['image_path'], true) ?: [];
                foreach ($images as $index => $imgPath):
                ?>
                    <div class="d-inline-block position-relative me-2 mb-2 image-wrapper">
                        <img src="../<?= htmlspecialchars($imgPath) ?>" width="120" height="120" class="rounded shadow-sm">
                        <span class="remove-image position-absolute top-0 end-0 m-0 p-1 bg-danger text-white rounded-circle" style="cursor:pointer;" data-index="<?= htmlspecialchars($index) ?>">&times;</span>
                        <input type="hidden" name="keep_images[]" value="<?= htmlspecialchars($imgPath) ?>">
                    </div>
                <?php endforeach; ?>
            </div>

            <input type="file" name="image_path[]" class="form-control" multiple accept="image/jpeg,image/jpg,image/png,image/gif">
            <small class="text-muted">Click × to remove an image, or upload new images to add.</small>
        </div>

        <div class="text-left">
            <button type="submit" class="btn btn-success">Update Item</button>
            <a href="index.php" class="btn btn-secondary">Cancel</a>
        </div>

    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.remove-image').forEach(function(btn) {
        btn.addEventListener('click', function() {
            const wrapper = btn.closest('.image-wrapper');
            // Remove corresponding hidden input
            wrapper.querySelector('input[name="keep_images[]"]').remove();
            // Remove the image from DOM
            wrapper.remove();
        });
    });
});
</script>

<br>
<?php include('../includes/footer.php'); ?>