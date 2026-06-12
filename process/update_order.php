<?php
require_once __DIR__ . '/../config/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../manage_orders.php');
    exit();
}

$order_id = (int) ($_POST['order_id'] ?? 0);
$order_status = trim($_POST['order_status'] ?? '');

if ($order_id <= 0 || $order_status === '') {
    echo "Error updating order: missing required fields.";
    exit();
}

$stmt = mysqli_prepare($conn, "UPDATE orders SET order_status = ? WHERE order_id = ?");
if (!$stmt) {
    die("Error updating order: " . mysqli_error($conn));
}

mysqli_stmt_bind_param($stmt, 'si', $order_status, $order_id);

if (mysqli_stmt_execute($stmt)) {
    mysqli_stmt_close($stmt);
    header('Location: ../manage_orders.php');
    exit();
}

echo "Error updating order: " . mysqli_stmt_error($stmt);
mysqli_stmt_close($stmt);
?>