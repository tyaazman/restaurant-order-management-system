<?php
$mysqli = new mysqli('127.0.0.1','root','', 'restaurant_order_db');
if($mysqli->connect_error){die('Connect error');}
$res = $mysqli->query('SHOW CREATE TABLE menu_items');
$row = $res->fetch_assoc();
print_r($row);
?>
