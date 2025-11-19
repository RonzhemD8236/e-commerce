<?php
session_start();
include("../includes/config.php");
include("../includes/header.php");

$sql = "SELECT * FROM users u ORDER BY total DESC";
$stmt = $conn->prepare($sql);
$stmt->execute();
$result = $stmt->get_result();
$itemCount = $result->num_rows;