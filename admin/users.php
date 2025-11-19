<?php
session_start();
include("../includes/config.php");
include("../includes/header.php");

// ✅ PREPARED STATEMENT - No user input, but good practice
$sql = "SELECT * FROM users u ORDER BY total DESC";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$itemCount = mysqli_num_rows($result);

// Don't forget to close the statement when done
mysqli_stmt_close($stmt);
?>