<?php
require('../includes/config.php');

if (isset($_GET['id'])) {
    $item_id = (int) $_GET['id'];
    $sql = "DELETE FROM item WHERE item_id = $item_id LIMIT 1";
    mysqli_query($conn, $sql);
}
header("Location: index.php?msg=deleted");
exit();
