<?php
$mysqli = new mysqli('127.0.0.1', 'root', '', 'restaurant_order_db');
if ($mysqli->connect_error) {
    die('Connect error: ' . $mysqli->connect_error);
}
$res = $mysqli->query('DESCRIBE order_items');
if (!$res) {
    die('Query error: ' . $mysqli->error);
}
while ($row = $res->fetch_assoc()) {
    echo $row['Field'] . "\n";
}
?>
