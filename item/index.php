<?php
session_start();
include('../admin/header.php'); 
include('../includes/config.php');

// Check for search keyword
$keyword = isset($_GET['search']) ? strtolower(trim($_GET['search'])) : '';

// Query items
if ($keyword) {
    $sql = "SELECT * FROM item LEFT JOIN stock USING (item_id)
            WHERE LOWER(description) LIKE '%{$keyword}%' 
               OR LOWER(short_description) LIKE '%{$keyword}%'
               OR LOWER(specifications) LIKE '%{$keyword}%'
               OR LOWER(category) LIKE '%{$keyword}%'";
} else {
    $sql = "SELECT * FROM item LEFT JOIN stock USING (item_id)";
}

$result = mysqli_query($conn, $sql);
$itemCount = mysqli_num_rows($result);
?>

<body>
    <br>
    <a href="create.php" class="btn btn-primary btn-lg ms-5">Add Item</a>

    <form method="GET" class="d-flex justify-content-center mt-3 mb-3">
        <input type="text" name="search" class="form-control w-50" placeholder="Search items..." value="<?= htmlspecialchars($keyword) ?>">
        <button type="submit" class="btn btn-secondary ms-2">Search</button>
    </form>

    <h2 class="text-center">Number of items: <?= $itemCount ?></h2>
    <br>

    <table class="table table-striped table-bordered text-center align-middle">
        <thead class="table-light">
            <tr>
                <th>ID</th>
                <th>Images</th>
                <th>Description</th>
                <th>Short Description</th>
                <th>Specifications</th>
                <th>Category</th>
                <th>Selling Price</th>
                <th>Cost Price</th>
                <th>Quantity</th>
                <th>Actions</th>
            </tr>
        </thead>

        <tbody>
            <?php while ($row = mysqli_fetch_assoc($result)): ?>
                <tr>
                    <td><?= $row['item_id'] ?></td>

                    <!-- Images -->
                    <td>
                        <?php
                        $images = json_decode($row['image_path'], true); // Decode JSON array
                        if (!empty($images) && is_array($images)) {
                            foreach ($images as $img) {
                                $fullPath = "../" . $img;
                                if (!empty($img) && file_exists($fullPath)) {
                                    echo "<img src='../{$img}' width='80' height='80' class='rounded shadow-sm me-1 mb-1'>";
                                }
                            }
                        } else {
                            echo "<img src='../uploads/default.png' width='80' height='80' class='rounded shadow-sm'>";
                        }
                        ?>
                    </td>

                    <td><?= htmlspecialchars($row['description']) ?></td>
                    <td><?= htmlspecialchars($row['short_description']) ?></td>

                    <!-- Specifications Modal Trigger -->
                    <td>
                        <button class="btn btn-info btn-sm" data-bs-toggle="modal" data-bs-target="#specModal<?= $row['item_id'] ?>">
                            View
                        </button>

                        <!-- Modal -->
                        <div class="modal fade" id="specModal<?= $row['item_id'] ?>" tabindex="-1">
                            <div class="modal-dialog modal-lg">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Specifications</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <pre style="white-space: pre-wrap; font-size: 16px;">
<?= htmlspecialchars($row['specifications']) ?>
                                        </pre>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </td>

                    <td><?= htmlspecialchars($row['category']) ?></td>
                    <td><?= number_format($row['sell_price'], 2) ?></td>
                    <td><?= number_format($row['cost_price'], 2) ?></td>
                    <td><?= $row['quantity'] ?></td>

                    <td>
                        <a href="edit.php?id=<?= $row['item_id'] ?>">
                            <i class="fa-regular fa-pen-to-square" style="color: blue"></i>
                        </a>
                        &nbsp;
                        <a href="delete.php?id=<?= $row['item_id'] ?>" onclick="return confirm('Are you sure you want to delete this item?');">
                            <i class="fa-solid fa-trash" style="color: red"></i>
                        </a>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</body>

<?php include('../includes/footer.php'); ?>
