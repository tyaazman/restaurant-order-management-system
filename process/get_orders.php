<?php
require_once __DIR__ . '/../config/db.php';
header('Content-Type: application/json');

$date = $_GET['date'] ?? date('Y-m-d');

$stmt = mysqli_prepare($conn, "SELECT order_id, customer_name, order_type, table_number, total_amount, order_status, created_at FROM orders WHERE DATE(created_at) = ?");
if (!$stmt) {
    echo json_encode([]);
    exit();
}

mysqli_stmt_bind_param($stmt, 's', $date);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);

$orders = [];
while ($row = mysqli_fetch_assoc($res)) {
    $order_id = (int)$row['order_id'];
    
    // Format customer name matching the style in mock data
    $type = $row['order_type'];
    $cust = $row['customer_name'];
    if ($type === 'walkin-dinein') {
        $customer = "Walk-in (Table " . $row['table_number'] . ")";
    } else if ($type === 'walkin-takeaway') {
        $customer = "Takeaway (" . $cust . ")";
    } else {
        $customer = "Online (" . $cust . ")";
    }

    // Format time
    $time = date('h:i A', strtotime($row['created_at']));

    // Query items
    $items = [];
    $item_stmt = mysqli_prepare($conn, "SELECT oi.order_item_id, m.item_name, oi.quantity, oi.customization_notes FROM order_items oi JOIN menu_items m ON oi.menu_item_id = m.menu_item_id WHERE oi.order_id = ?");
    if ($item_stmt) {
        mysqli_stmt_bind_param($item_stmt, 'i', $order_id);
        mysqli_stmt_execute($item_stmt);
        $item_res = mysqli_stmt_get_result($item_stmt);
        while ($item_row = mysqli_fetch_assoc($item_res)) {
            $items[] = [
                'id' => $item_row['order_item_id'],
                'name' => $item_row['item_name'],
                'qty' => (int)$item_row['quantity'],
                'remark' => $item_row['customization_notes'],
                'status' => strtolower($row['order_status']) // map overall order status to item status
            ];
        }
        mysqli_stmt_close($item_stmt);
    }

    $orders[] = [
        'id' => $order_id,
        'customer' => $customer,
        'time' => $time,
        'items' => $items
    ];
}

mysqli_stmt_close($stmt);
echo json_encode($orders);
?>
