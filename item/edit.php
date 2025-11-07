<?php
session_start();
include('../includes/config.php');
include('../includes/header.php');
include('../includes/adminHeader.php');

// Check if item_id is provided
if (!isset($_GET['id'])) {
    echo "<script>
            alert('No item selected.');
            window.location.href = 'index.php';
          </script>";
    exit;
}

$item_id = intval($_GET['id']);

// Fetch item details
$sql = "SELECT item.*, stock.quantity 
        FROM item 
        LEFT JOIN stock USING (item_id) 
        WHERE item.item_id = $item_id";
$result = mysqli_query($conn, $sql);

if (mysqli_num_rows($result) == 0) {
    echo "<script>
            alert('Item not found.');
            window.location.href = 'index.php';
          </script>";
    exit;
}

$row = mysqli_fetch_assoc($result);
?>

<div class="container mt-4">
   <form action="update.php" method="POST" enctype="multipart/form-data" style="max-width: 800px;">
        <input type="hidden" name="item_id" value="<?= $row['item_id'] ?>">

        <div class="mb-3">
            <label class="form-label">Description</label>
            <input type="text" name="desc" class="form-control" value="<?= htmlspecialchars($row['description']) ?>" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Cost Price</label>
            <input type="number" name="cost" class="form-control" value="<?= $row['cost_price'] ?>" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Sell Price</label>
            <input type="number" name="sell" class="form-control" value="<?= $row['sell_price'] ?>" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Quantity</label>
            <input type="number" name="qty" class="form-control" value="<?= $row['quantity'] ?>" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Upload image</label>
            <input type="file" name="image" class="form-control"><br>
            <img src="../<?= htmlspecialchars($row['image_path']) ?>" width="150" height="150" class="rounded mb-2" alt="Item Image"><br>
            <small class="text-muted">Leave blank to keep the same image</small>
        </div>

        <div class="text-left">
            <button type="submit" class="btn btn-success">Update Item</button>
            <a href="index.php" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>
<br>

<?php include('../includes/footer.php'); ?>
