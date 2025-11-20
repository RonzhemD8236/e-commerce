<?php
session_start();

 
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    $_SESSION['auth_error'] = 'Please log in as admin to access this page.';
    header("Location: ../admin/login.php");
    exit();
}

include('../admin/header.php');
include('../includes/config.php');
?>

<style>
 
.add-item-wrapper {
    width: 100%;
    display: flex;
    justify-content: center;
    padding: 40px 20px;
    background-color: #f4f6f8;
}

 
.add-item-container {
    max-width: 1000px;
    width: 100%;
    margin: 0 auto;
}

 
.add-item-card {
    background: #ffffff;
    border-radius: 12px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.08);
    overflow: hidden;
    transition: all 0.3s ease;
}

 
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

 
.card-body-custom {
    padding: 25px 25px;
}

 
.form-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 20px 20px;
}

.form-group-custom {
    margin-bottom: 15px;
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

<div class="add-item-wrapper">
    <div class="add-item-container">
        <div class="add-item-card">
            <div class="card-header-custom">
                <h2>Add New Item</h2>
                <p>Fill out the form to add a new product</p>
            </div>
            <div class="card-body-custom">
                <form method="POST" action="store.php" enctype="multipart/form-data">

                     
                    <div class="form-group-custom">
                        <label class="form-label-custom" for="description">
                            Item Name / Description <span class="text-danger">*</span>
                            <?php if(isset($_SESSION['descError'])): ?>
                                <span class="text-danger"><?= htmlspecialchars($_SESSION['descError']); unset($_SESSION['descError']); ?></span>
                            <?php endif; ?>
                        </label>
                        <input type="text" class="form-control-custom" id="description" name="description" placeholder="Enter item name" value="<?= isset($_SESSION['desc'])?htmlspecialchars($_SESSION['desc']):'' ?>">
                    </div>

                    <div class="form-group-custom">
                        <label class="form-label-custom" for="short_description">
                            Short Description <span class="text-danger">*</span>
                            <?php if(isset($_SESSION['shortDescError'])): ?>
                                <span class="text-danger"><?= htmlspecialchars($_SESSION['shortDescError']); unset($_SESSION['shortDescError']); ?></span>
                            <?php endif; ?>
                        </label>
                        <textarea class="form-control-custom" id="short_description" name="short_description" rows="2" placeholder="Ex: Best-selling camera lens..."><?= isset($_SESSION['short_desc'])?htmlspecialchars($_SESSION['short_desc']):'' ?></textarea>
                    </div>

                    <div class="form-group-custom">
                        <label class="form-label-custom" for="specifications">
                            Specifications <span class="text-danger">*</span>
                            <?php if(isset($_SESSION['specsError'])): ?>
                                <span class="text-danger"><?= htmlspecialchars($_SESSION['specsError']); unset($_SESSION['specsError']); ?></span>
                            <?php endif; ?>
                        </label>
                        <textarea class="form-control-custom" id="specifications" name="specifications" rows="4" placeholder="Enter item specifications..."><?= isset($_SESSION['specs'])?htmlspecialchars($_SESSION['specs']):'' ?></textarea>
                    </div>

                     
                    <div class="form-grid">
                        <div class="form-group-custom">
                            <label class="form-label-custom" for="cost_price">
                                Cost Price <span class="text-danger">*</span>
                                <?php if(isset($_SESSION['costError'])): ?>
                                    <span class="text-danger"><?= htmlspecialchars($_SESSION['costError']); unset($_SESSION['costError']); ?></span>
                                <?php endif; ?>
                            </label>
                            <input type="text" class="form-control-custom" id="cost_price" name="cost_price" placeholder="Enter cost price" value="<?= isset($_SESSION['cost'])?htmlspecialchars($_SESSION['cost']):'' ?>">
                        </div>

                        <div class="form-group-custom">
                            <label class="form-label-custom" for="sell_price">
                                Sell Price <span class="text-danger">*</span>
                                <?php if(isset($_SESSION['sellError'])): ?>
                                    <span class="text-danger"><?= htmlspecialchars($_SESSION['sellError']); unset($_SESSION['sellError']); ?></span>
                                <?php endif; ?>
                            </label>
                            <input type="text" class="form-control-custom" id="sell_price" name="sell_price" placeholder="Enter sell price" value="<?= isset($_SESSION['sell'])?htmlspecialchars($_SESSION['sell']):'' ?>">
                        </div>

                        <div class="form-group-custom">
                            <label class="form-label-custom" for="category">
                                Category <span class="text-danger">*</span>
                                <?php if(isset($_SESSION['categoryError'])): ?>
                                    <span class="text-danger"><?= htmlspecialchars($_SESSION['categoryError']); unset($_SESSION['categoryError']); ?></span>
                                <?php endif; ?>
                            </label>
                            <select class="form-select-custom" id="category" name="category">
                                <option value="">-- Select Category --</option>
                                <?php
                                $categories = ["DSLR Cameras","Mirrorless Cameras","Action Cameras","Camera Lenses","Tripods & Stabilizers","Camera Accessories"];
                                foreach($categories as $cat):
                                ?>
                                    <option value="<?= $cat ?>" <?= isset($_SESSION['category']) && $_SESSION['category']==$cat?'selected':'' ?>><?= $cat ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group-custom">
                            <label class="form-label-custom" for="quantity">
                                Quantity <span class="text-danger">*</span>
                                <?php if(isset($_SESSION['qtyError'])): ?>
                                    <span class="text-danger"><?= htmlspecialchars($_SESSION['qtyError']); unset($_SESSION['qtyError']); ?></span>
                                <?php endif; ?>
                            </label>
                            <input type="text" class="form-control-custom" id="quantity" name="quantity" placeholder="1" value="<?= isset($_SESSION['qty'])?htmlspecialchars($_SESSION['qty']):'' ?>">
                        </div>
                    </div>

                     
                    <div class="form-group-custom mt-3">
                        <label class="form-label-custom" for="image_path">
                            Product Images <span class="text-danger">*</span>
                            <?php if(isset($_SESSION['imageError'])): ?>
                                <span class="text-danger"><?= htmlspecialchars($_SESSION['imageError']); unset($_SESSION['imageError']); ?></span>
                            <?php endif; ?>
                        </label>
                        <input type="file" class="form-control-custom" name="image_path[]" multiple>
                        <small class="text-muted">Upload one or more product images</small>
                    </div>

                     
                    <div class="button-group mt-4">
                        <button type="submit" class="btn-custom btn-primary-custom" name="submit">Submit</button>
                        <a href="index.php" class="btn-custom btn-secondary-custom">Cancel</a>
                    </div>

                </form>
            </div>
        </div>
    </div>
</div>

<?php
include('../includes/footer.php');
?>
