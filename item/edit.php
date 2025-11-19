<?php
session_start();

// Authentication check
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    $_SESSION['auth_error'] = 'Please log in as admin to access this page.';
    header("Location: ../admin/login.php");
    exit();
}

include('../includes/config.php');
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

<style>
/* Wrapper to center card */
.edit-item-wrapper {
    width: 100%;
    display: flex;
    justify-content: center;
    padding: 40px 20px;
    background-color: #f4f6f8;
}

/* Card container */
.edit-item-container {
    max-width: 1000px;
    width: 100%;
    margin: 0 auto;
}

/* Card styling */
.edit-item-card {
    background: #ffffff;
    border-radius: 12px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.08);
    overflow: hidden;
    transition: all 0.3s ease;
}

/* Card header */
.card-header-custom {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    padding: 2rem;
    border-radius: 10px 10px 0 0;
    color: white;
    margin-bottom: 0;
}

.card-header-custom h2 {
    margin: 0;
    font-size: 28px;
    font-weight: 600;
}

.card-header-custom p {
    margin-top: 5px;
    font-size: 14px;
    opacity: 0.9;
}

/* Card body */
.card-body-custom {
    padding: 25px 25px;
}

/* Form grid */
.form-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 20px 20px;
}

.form-group-custom {
    margin-bottom: 0;
}

.form-label-custom {
    display: block;
    font-weight: 500;
    color: #374151;
    margin-bottom: 5px;
    font-size: 14px;
}

.form-control-custom,
.form-select-custom,
textarea.form-control-custom {
    width: 100%;
    padding: 10px 15px;
    border: 1px solid #d1d5db;
    border-radius: 6px;
    font-size: 15px;
    background-color: #f9fafb;
    transition: all 0.2s ease;
}

.form-control-custom:focus,
.form-select-custom:focus,
textarea.form-control-custom:focus {
    outline: none;
    border-color: #7f00ff;
    background-color: #ffffff;
    box-shadow: 0 0 0 2px rgba(127,0,255,0.15);
}

.text-danger {
    font-size: 12px;
    margin-left: 5px;
}

/* Image previews */
.image-wrapper {
    display: inline-block;
    position: relative;
}

.remove-image {
    cursor: pointer;
}

/* Buttons */
.button-group {
    display: flex;
    gap: 12px;
    margin-top: 30px;
    flex-wrap: wrap;
}

.btn-custom {
    flex: 1;
    padding: 12px 20px;
    border: none;
    border-radius: 6px;
    font-size: 15px;
    font-weight: 500;
    cursor: pointer;
    text-align: center;
    transition: all 0.2s ease;
}

.btn-primary-custom {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: #ffffff;
}

.btn-primary-custom:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(127,0,255,0.25);
}

.btn-secondary-custom {
    background-color: #e5e7eb;
    color: #374151;
}

.btn-secondary-custom:hover {
    background-color: #d1d5db;
    transform: translateY(-1px);
}

/* Responsive */
@media (max-width: 768px) {
    .form-grid {
        grid-template-columns: 1fr;
    }

    .button-group {
        flex-direction: column;
    }

    .btn-custom {
        width: 100%;
    }
}
</style>

<div class="edit-item-wrapper">
    <div class="edit-item-container">
        <div class="edit-item-card">
            <div class="card-header-custom">
                <h2>Edit Item</h2>
                <p>Update product details and images</p>
            </div>
            <div class="card-body-custom">
                <form id="editItemForm" action="update.php" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="item_id" value="<?= htmlspecialchars($row['item_id']) ?>">

                    <!-- Single-column fields -->
                    <div class="form-group-custom mb-3">
                        <label class="form-label-custom">Item Name / Description <span class="text-danger">*</span></label>
                        <input type="text" name="description" class="form-control-custom" value="<?= htmlspecialchars($desc) ?>">
                        <div class="text-danger" id="descWarning"></div>
                    </div>

                    <div class="form-group-custom mb-3">
                        <label class="form-label-custom">Short Description <span class="text-danger">*</span></label>
                        <textarea name="short_description" class="form-control-custom" rows="2"><?= htmlspecialchars($short_desc) ?></textarea>
                        <div class="text-danger" id="shortDescWarning"></div>
                    </div>

                    <div class="form-group-custom mb-3">
                        <label class="form-label-custom">Specifications <span class="text-danger">*</span></label>
                        <textarea name="specifications" class="form-control-custom" rows="4"><?= htmlspecialchars($specs) ?></textarea>
                        <div class="text-danger" id="specsWarning"></div>
                    </div>

                    <!-- Two-column fields -->
                    <div class="form-grid">
                        <div class="form-group-custom">
                            <label class="form-label-custom">Cost Price <span class="text-danger">*</span></label>
                            <input type="text" name="cost_price" class="form-control-custom" value="<?= htmlspecialchars($cost) ?>">
                            <div class="text-danger" id="costWarning"></div>
                        </div>

                        <div class="form-group-custom">
                            <label class="form-label-custom">Sell Price <span class="text-danger">*</span></label>
                            <input type="text" name="sell_price" class="form-control-custom" value="<?= htmlspecialchars($sell) ?>">
                            <div class="text-danger" id="sellWarning"></div>
                        </div>

                        <div class="form-group-custom">
                            <label class="form-label-custom">Category <span class="text-danger">*</span></label>
                            <select name="category" class="form-select-custom">
                                <option value="">-- Select Category --</option>
                                <?php
                                $categories = ["DSLR Cameras","Mirrorless Cameras","Action Cameras","Camera Lenses","Tripods & Stabilizers","Camera Accessories"];
                                foreach($categories as $cat):
                                ?>
                                    <option value="<?= $cat ?>" <?= $category==$cat?'selected':'' ?>><?= $cat ?></option>
                                <?php endforeach; ?>
                            </select>
                            <div class="text-danger" id="categoryWarning"></div>
                        </div>

                        <div class="form-group-custom">
                            <label class="form-label-custom">Quantity <span class="text-danger">*</span></label>
                            <input type="text" name="quantity" class="form-control-custom" value="<?= htmlspecialchars($qty) ?>">
                            <div class="text-danger" id="qtyWarning"></div>
                        </div>
                    </div>

                    <!-- Product Images -->
                    <!-- Product Images -->
<div class="form-group-custom mt-3">
    <label class="form-label-custom">Product Images</label>
    <div class="mb-2" id="imageContainer">
        <?php
        $images = json_decode($row['image_path'], true) ?: [];
        foreach ($images as $index => $imgPath):
        ?>
            <div style="display:inline-block; position:relative; margin-right:10px; margin-bottom:10px;">
                <img src="../<?= htmlspecialchars($imgPath) ?>" width="120" height="120" style="border-radius:4px; border:1px solid #ccc;">
                <span class="remove-image" data-index="<?= $index ?>" 
                      style="position:absolute; top:2px; right:2px; cursor:pointer; background:red; color:white; font-weight:bold; border-radius:50%; width:18px; height:18px; display:flex; align-items:center; justify-content:center; font-size:14px;">×</span>
                <input type="hidden" name="keep_images[]" value="<?= htmlspecialchars($imgPath) ?>">
            </div>
        <?php endforeach; ?>
    </div>
    <input type="file" name="image_path[]" class="form-control-custom" multiple>
    <small class="text-muted">Click × to remove an image, or upload a new image to add.</small>
</div>

                                
                    <!-- Buttons -->
                    <div class="button-group mt-4">
                        <button type="submit" class="btn-custom btn-primary-custom">Update</button>
                        <a href="index.php" class="btn-custom btn-secondary-custom">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
// Remove image functionality
document.addEventListener('DOMContentLoaded', function() {
    var removeButtons = document.getElementsByClassName('remove-image');
    for(var i=0; i<removeButtons.length; i++) {
        removeButtons[i].onclick = function() {
            var wrapper = this.parentNode;
            var hiddenInput = wrapper.getElementsByTagName('input')[0];
            hiddenInput.parentNode.removeChild(hiddenInput);
            wrapper.parentNode.removeChild(wrapper);
        }
    }

    // Inline validation
    document.getElementById('editItemForm').onsubmit = function() {
        var valid = true;

        // Clear all warnings first
        ['desc','shortDesc','specs','cost','sell','category','qty'].forEach(function(id){
            document.getElementById(id+'Warning').innerHTML = '';
        });

        // Fields
        var desc = this.elements['description'];
        if(!desc.value){ document.getElementById('descWarning').innerHTML = 'This field is required'; valid=false; }

        var shortDesc = this.elements['short_description'];
        if(!shortDesc.value){ document.getElementById('shortDescWarning').innerHTML = 'This field is required'; valid=false; }

        var specs = this.elements['specifications'];
        if(!specs.value){ document.getElementById('specsWarning').innerHTML = 'This field is required'; valid=false; }

        var cost = this.elements['cost_price'];
        if(!cost.value || isNaN(cost.value)){ document.getElementById('costWarning').innerHTML = 'Required and must be numeric'; valid=false; }

        var sell = this.elements['sell_price'];
        if(!sell.value || isNaN(sell.value)){ document.getElementById('sellWarning').innerHTML = 'Required and must be numeric'; valid=false; }

        var category = this.elements['category'];
        if(!category.value){ document.getElementById('categoryWarning').innerHTML = 'This field is required'; valid=false; }

        var qty = this.elements['quantity'];
        if(!qty.value || isNaN(qty.value)){ document.getElementById('qtyWarning').innerHTML = 'This field is required and must be numeric'; valid=false; }

        return valid;
    }
});
</script>



<?php include('../includes/footer.php'); ?>
