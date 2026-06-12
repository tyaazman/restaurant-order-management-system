<?php
require_once __DIR__ . '/../config/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../manage_orders.php');
    exit();
}

$customer_name = trim($_POST['customer_name'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$order_type = trim($_POST['order_type'] ?? 'Walk-In');
$table_no = trim($_POST['table_no'] ?? '');
$address = trim($_POST['address'] ?? '');
$total_amount = (float) ($_POST['total_amount'] ?? 0);

if ($customer_name === '' || $phone === '' || $total_amount <= 0) {
    echo "Error adding order: missing required fields.";
    exit();
}

$stmt = mysqli_prepare(
    $conn,
    "INSERT INTO orders (customer_name, phone, order_type, table_no, address, total_amount, order_status) VALUES (?, ?, ?, ?, ?, ?, 'Pending')"
);
if (!$stmt) {
    die("Error adding order: " . mysqli_error($conn));
}

mysqli_stmt_bind_param($stmt, 'sssssd', $customer_name, $phone, $order_type, $table_no, $address, $total_amount);

if (!mysqli_stmt_execute($stmt)) {
    die("Error adding order: " . mysqli_stmt_error($stmt));
}

$order_id = mysqli_insert_id($conn);

if (isset($_FILES['receipt_image']) && $_FILES['receipt_image']['error'] === UPLOAD_ERR_OK && $_FILES['receipt_image']['name'] !== '') {
    $receipt_name = time() . '_' . basename($_FILES['receipt_image']['name']);
    $receipt_tmp = $_FILES['receipt_image']['tmp_name'];
    $upload_dir = __DIR__ . '/../assets/receipts/';
    $upload_path = $upload_dir . $receipt_name;

    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    if (move_uploaded_file($receipt_tmp, $upload_path)) {
        $payment_stmt = mysqli_prepare(
            $conn,
            "INSERT INTO payments (order_id, receipt_image, payment_status) VALUES (?, ?, 'Pending')"
        );

        if ($payment_stmt) {
            mysqli_stmt_bind_param($payment_stmt, 'is', $order_id, $receipt_name);
            mysqli_stmt_execute($payment_stmt);
            mysqli_stmt_close($payment_stmt);
        }
    }
}

mysqli_stmt_close($stmt);

header('Location: ../confirmation.php?order_id=' . $order_id);
exit();
?>