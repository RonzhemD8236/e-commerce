<?php
$db_host = "localhost";
$db_username = "root";
$db_passwd = "";
$db_name = "cammerce_db";
$db_port = 3306;

$conn = new mysqli($db_host, $db_username, $db_passwd, $db_name, $db_port);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
