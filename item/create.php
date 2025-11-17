<?php
session_start();
include('../includes/header.php');
include('../includes/config.php');

?>

<body>
    <div class="container">
        <form method="POST" action="store.php" enctype="multipart/form-data">
            <div class="form-group">

                <!-- Category Dropdown -->
                <label for="category">
                    Item Category <span class="text-danger">*</span>
                    <?php if (isset($_SESSION['categoryError'])): ?>
                        <small class="text-danger ms-2">
                            <?php 
                                echo $_SESSION['categoryError']; 
                                unset($_SESSION['categoryError']); 
                            ?>
                        </small>
                    <?php endif; ?>
                </label>

                <select class="form-control" id="category" name="category">
                    <option value="">-- Select Category --</option>
                <option value="DSLR Cameras"        <?php if(isset($_SESSION['category']) && $_SESSION['category']=="DSLR Cameras") echo "selected"; ?>>DSLR Cameras</option>
                <option value="Mirrorless Cameras"  <?php if(isset($_SESSION['category']) && $_SESSION['category']=="Mirrorless Cameras") echo "selected"; ?>>Mirrorless Cameras</option>
                <option value="Action Cameras"      <?php if(isset($_SESSION['category']) && $_SESSION['category']=="Action Cameras") echo "selected"; ?>>Action Cameras</option>
                <option value="Camera Lenses"       <?php if(isset($_SESSION['category']) && $_SESSION['category']=="Camera Lenses") echo "selected"; ?>>Camera Lenses</option>
                <option value="Tripods & Stabilizers" <?php if(isset($_SESSION['category']) && $_SESSION['category']=="Tripods & Stabilizers") echo "selected"; ?>>Tripods & Stabilizers</option>
                <option value="Camera Accessories"  <?php if(isset($_SESSION['category']) && $_SESSION['category']=="Camera Accessories") echo "selected"; ?>>Camera Accessories</option>

                </select>
                <br>

                <!-- Item Name -->
                <label for="name">
                    Item Name <span class="text-danger">*</span>
                    <?php if (isset($_SESSION['descError'])): ?>
                        <small class="text-danger ms-2">
                            <?php 
                                echo $_SESSION['descError']; 
                                unset($_SESSION['descError']); 
                            ?>
                        </small>
                    <?php endif; ?>
                </label>
                <input type="text"
                       class="form-control"
                       id="name"
                       placeholder="Enter item name"
                       name="description"
                       value="<?php if (isset($_SESSION['desc'])) echo $_SESSION['desc']; ?>" />

                <!-- Short Description -->
                <label for="short_description">
                    Short Description <span class="text-danger">*</span>
                    <?php if (isset($_SESSION['shortDescError'])): ?>
                        <small class="text-danger ms-2">
                            <?php 
                                echo $_SESSION['shortDescError']; 
                                unset($_SESSION['shortDescError']); 
                            ?>
                        </small>
                    <?php endif; ?>
                </label>
                <input type="text"
                       class="form-control"
                       id="short_description"
                       placeholder="Ex: Best-selling camera lens..."
                       name="short_description"
                       value="<?php if (isset($_SESSION['short_desc'])) echo $_SESSION['short_desc']; ?>" />

                <!-- Specifications -->
                <label for="specifications">
                    Specifications <span class="text-danger">*</span>
                    <?php if (isset($_SESSION['specsError'])): ?>
                        <small class="text-danger ms-2">
                            <?php 
                                echo $_SESSION['specsError']; 
                                unset($_SESSION['specsError']); 
                            ?>
                        </small>
                    <?php endif; ?>
                </label>
                <textarea class="form-control"
                          id="specifications"
                          placeholder="Enter item specifications..."
                          name="specifications"
                          rows="4"><?php 
                          if (isset($_SESSION['specs'])) echo $_SESSION['specs']; 
                          ?></textarea>

                <!-- Cost Price -->
                <label for="cost">
                    Cost Price <span class="text-danger">*</span>
                    <?php if (isset($_SESSION['costError'])): ?>
                        <small class="text-danger ms-2">
                            <?php 
                                echo $_SESSION['costError']; 
                                unset($_SESSION['costError']); 
                            ?>
                        </small>
                    <?php endif; ?>
                </label>
                <input type="text"
                       class="form-control"
                       id="cost"
                       placeholder="Enter item cost price"
                       name="cost_price"
                       value="<?php if (isset($_SESSION['cost'])) echo $_SESSION['cost']; ?>" />

                <!-- Sell Price -->
                <label for="sell">
                    Sell Price <span class="text-danger">*</span>
                    <?php if (isset($_SESSION['sellError'])): ?>
                        <small class="text-danger ms-2">
                            <?php 
                                echo $_SESSION['sellError']; 
                                unset($_SESSION['sellError']); 
                            ?>
                        </small>
                    <?php endif; ?>
                </label>
                <input type="text"
                       class="form-control"
                       id="sell"
                       placeholder="Enter sell price"
                       name="sell_price"
                       value="<?php if (isset($_SESSION['sell'])) echo $_SESSION['sell']; ?>" />

                <!-- Quantity -->
                <label for="qty">
                    Quantity <span class="text-danger">*</span>
                    <?php if (isset($_SESSION['qtyError'])): ?>
                        <small class="text-danger ms-2">
                            <?php 
                                echo $_SESSION['qtyError']; 
                                unset($_SESSION['qtyError']); 
                            ?>
                        </small>
                    <?php endif; ?>
                </label>
                <input type="number"
                       class="form-control"
                       id="qty"
                       placeholder="1"
                       name="quantity"
                       value="<?php if (isset($_SESSION['qty'])) echo $_SESSION['qty']; ?>" />

                <!-- Image -->
        <label for="image_path">
            Product Images <span class="text-danger">*</span>
            <?php if (isset($_SESSION['imageError'])): ?>
                <small class="text-danger ms-2">
                    <?php 
                        echo $_SESSION['imageError']; 
                        unset($_SESSION['imageError']); 
                    ?>
                </small>
            <?php endif; ?>
        </label>
        <input class="form-control" type="file" name="image_path[]" multiple /><br />


            </div>

            <button type="submit" class="btn btn-primary" name="submit" value="submit">Submit</button>
            <a href="index.php" role="button" class="btn btn-secondary">Cancel</a>
        </form>
    </div>

<?php
include('../includes/footer.php');
?>
