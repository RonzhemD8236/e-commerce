<?php
session_start();
include('../includes/header.php');
include('../includes/config.php');

// ✅ Reset session values when opening page normally (not coming from store.php with errors)
if (!isset($_SERVER['HTTP_REFERER']) || !str_contains($_SERVER['HTTP_REFERER'], 'store.php')) {
    unset($_SESSION['desc'], $_SESSION['cost'], $_SESSION['sell'], $_SESSION['qty']);
    unset($_SESSION['descError'], $_SESSION['costError'], $_SESSION['sellError'], $_SESSION['qtyError'], $_SESSION['imageError']);
}
?>

<body>
    <div class="container">
        <form method="POST" action="store.php" enctype="multipart/form-data">
            <div class="form-group">

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
                    Product Image <span class="text-danger">*</span>
                    <?php if (isset($_SESSION['imageError'])): ?>
                        <small class="text-danger ms-2">
                            <?php 
                            echo $_SESSION['imageError']; 
                            unset($_SESSION['imageError']); 
                            ?>
                        </small>
                    <?php endif; ?>
                </label>
                <input class="form-control" type="file" name="image_path" /><br />

            </div>

            <button type="submit" class="btn btn-primary" name="submit" value="submit">Submit</button>
            <a href="index.php" role="button" class="btn btn-secondary">Cancel</a>
        </form>
    </div>

<?php
include('../includes/footer.php');
?>
