<?php
// CREATE VIEW orderdetails AS 
// SELECT o.orderinfo_id, c.lname, c.fname, c.addressline, c.town, c.zipcode, c.phone,  
// i.sell_price, ol.quantity, i.description, o.status 
// FROM customer c 
// INNER JOIN orderinfo o USING(customer_id) 
// INNER JOIN orderline ol USING (orderinfo_id) 
// INNER JOIN item i USING(item_id);

session_start();
include('../includes/header.php');
include('../includes/config.php');

// ✅ Fix: Use 'orderinfo_id' instead of 'id', with safe fallback
$orderId = isset($_GET['orderinfo_id']) ? (int)$_GET['orderinfo_id'] : 0;
if ($orderId === 0) {
    die("Error: Invalid or missing orderinfo_id.");
}

$_SESSION['orderId'] = $orderId;

// ✅ Query for customer/order info
$sql = "SELECT 
            c.lname, c.fname, c.addressline, c.town, c.zipcode, c.phone, 
            o.orderinfo_id, o.status 
        FROM customer c
        INNER JOIN orderinfo o USING(customer_id)
        WHERE o.orderinfo_id = $orderId
        LIMIT 1";

$result = mysqli_query($conn, $sql);
$customer = mysqli_fetch_assoc($result);

// ✅ Query for order items
$sql = "SELECT 
            i.description, 
            ol.quantity, 
            i.sell_price
        FROM orderline ol
        INNER JOIN item i USING(item_id)
        WHERE ol.orderinfo_id = $orderId";

$items = mysqli_query($conn, $sql);
?>

<h2><?= $customer['orderinfo_id'] ?> </h2>
<h3><?php echo "{$customer['lname']} {$customer['fname']}" ?></h3>
<p><?php echo "{$customer['addressline']} {$customer['town']} {$customer['zipcode']} {$customer['phone']}" ?></p>

<table class="table table-striped table-bordered">
    <thead>
        <th>item name</th>
        <th>quantity</th>
        <th>price</th>
        <th>total</th>
    </thead>

    <?php
    $grandTotal = 0;
    while ($row = mysqli_fetch_assoc($items)) {
        $total = $row['sell_price'] * $row['quantity'];
        $grandTotal += $total;
        echo "<tr>";
        echo "<td>{$row['description']}</td>";
        echo "<td>{$row['quantity']}</td>";
        echo "<td>{$row['sell_price']}</td>";
        echo "<td>{$total}</td>";
        echo "</tr>";
    }
    ?>
</table>

<h4><?= $grandTotal ?></h4>

<form action="updateOrder.php" method="POST">
    <select class="form-select form-control" aria-label="Default select example" name="status">
        <option selected>Open this select menu</option>
        <option value="Processing">processing</option>
        <option value="Delivered">delivered</option>
        <option value="Canceled">canceled</option>
    </select>
    <button type="submit" class="btn btn-primary">update order</button>
</form>

<?php
include('../includes/footer.php');
?>
