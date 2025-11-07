<?php
session_start();
include('../includes/header.php');
include('../includes/config.php');

// Check for search keyword
if (isset($_GET['search'])) {
    $keyword = strtolower(trim($_GET['search']));
} else {
    $keyword = '';
}

// Query items
if ($keyword) {
    $sql = "SELECT * FROM item LEFT JOIN stock USING (item_id) WHERE description LIKE '%{$keyword}%'";
} else {
    $sql = "SELECT * FROM item LEFT JOIN stock USING (item_id)";
}
$result = mysqli_query($conn, $sql);
$itemCount = mysqli_num_rows($result);
?>

<body>
    <br>
    <a href="create.php" class="btn btn-primary btn-lg ms-5" role="button" aria-disabled="true">Add Item</a>
    <h2 class="text-center">Number of items: <?= $itemCount ?></h2>
    <br>

    <table class="table table-striped table-bordered text-center align-middle">
        <thead class="table-light">
            <tr>
                <th>ID</th>
                <th>Image</th>
                <th>Description</th>
                <th>Selling Price</th>
                <th>Cost Price</th>
                <th>Quantity</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php
            while ($row = mysqli_fetch_assoc($result)) {
                echo "<tr>";

                // ✅ Check image path and display properly
                echo "<td>{$row['item_id']}</td>";
                $imagePath = $row['image_path'];

                if (!empty($imagePath)) {
                    // Handle both absolute and relative paths
                    if (file_exists("../" . $imagePath)) {
                        echo "<td><img src='../{$imagePath}' width='150' height='150' class='rounded shadow-sm' /></td>";
                    } elseif (file_exists($imagePath)) {
                        echo "<td><img src='{$imagePath}' width='150' height='150' class='rounded shadow-sm' /></td>";
                    } else {
                        echo "<td><img src='../uploads/default.png' width='150' height='150' class='rounded shadow-sm' /></td>";
                    }
                } else {
                    echo "<td><img src='../uploads/default.png' width='150' height='150' class='rounded shadow-sm' /></td>";
                }

                echo "<td>{$row['description']}</td>";
                echo "<td>{$row['sell_price']}</td>";
                echo "<td>{$row['cost_price']}</td>";
                echo "<td>{$row['quantity']}</td>";

                // Action buttons
                echo "<td>
                        <a href='edit.php?id={$row['item_id']}'>
                            <i class='fa-regular fa-pen-to-square' style='color: blue'></i>
                        </a>
                        &nbsp;
                        <a href='delete.php?id={$row['item_id']}' 
                        onclick=\"return confirm('Are you sure you want to delete this item?');\">
                            <i class='fa-solid fa-trash' style='color: red'></i>
                        </a>
                    </td>";

                echo "</tr>";
            }
            ?>
        </tbody>
    </table>
</body>

<?php include('../includes/footer.php'); ?>
