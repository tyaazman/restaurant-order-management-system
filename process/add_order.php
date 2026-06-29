<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Ensure this points to your exact db.php file location
require_once __DIR__ . '/../config/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../manage_orders.php');
    exit();
}

// 1. Sanitize and prepare form data
$customer_name = trim($_POST['customer_name'] ?? '');

$customer_phone = trim($_POST['phone'] ?? '');
if ($customer_phone === '') $customer_phone = null;

$customer_email = trim($_POST['email'] ?? '');
if ($customer_email === '') $customer_email = null;

$order_type = trim($_POST['order_type'] ?? 'Walk-In');

$table_number = trim($_POST['table_no'] ?? '');
$table_number = ($table_number === '') ? null : (int)$table_number;

$shipping_address = trim($_POST['address'] ?? '');
if ($shipping_address === '') $shipping_address = null;

$payment_method = trim($_POST['payment_method'] ?? 'Cash');
$total_amount = (float) ($_POST['total_amount'] ?? 0);

if ($customer_name === '' || $total_amount <= 0) {
    die("Error adding order: missing required fields. Please ensure your cart is not empty and your name is filled out.");
}

try {
    // 2. Insert into `orders` table using PDO
    $stmt = $pdo->prepare("INSERT INTO orders (order_type, customer_name, customer_email, customer_phone, table_number, shipping_address, payment_method, total_amount, order_status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'Pending')");
    $stmt->execute([$order_type, $customer_name, $customer_email, $customer_phone, $table_number, $shipping_address, $payment_method, $total_amount]);
    
    // Grab the ID of the order we just created
    $order_id = $pdo->lastInsertId();

    // 3. Insert into `order_items` table
    $cart_items_json = $_POST['cart_items'] ?? '[]';
    $cart_items = json_decode($cart_items_json, true);

    if (is_array($cart_items)) {
        foreach ($cart_items as $item) {
            $item_name = trim($item['name'] ?? '');
            $base_name = trim($item['base_name'] ?? '');
            $item_price_str = trim($item['price'] ?? '0');
            $price = (float) str_ireplace('RM ', '', $item_price_str);

            $item_id = 0;

            // 1. First try matching the base name directly if present
            if ($base_name !== '') {
                $stmtBase = $pdo->prepare("SELECT menu_item_id FROM menu_items WHERE item_name = ? LIMIT 1");
                $stmtBase->execute([$base_name]);
                if ($row = $stmtBase->fetch(PDO::FETCH_ASSOC)) {
                    $item_id = (int)$row['menu_item_id'];
                }
            }

            // 2. Fallback to parsing display item_name
            if ($item_id === 0 && $item_name !== '') {
                // Remove parentheses
                $clean_display = str_replace(array('(', ')'), '', $item_name);
                // Split by '+' to extract the core item name
                $parts = explode('+', $clean_display);
                $core_name = trim($parts[0]);

                $stmtFuzzy = $pdo->prepare("SELECT menu_item_id FROM menu_items WHERE item_name = ? OR ? LIKE CONCAT('%', item_name, '%') LIMIT 1");
                $stmtFuzzy->execute([$core_name, $core_name]);
                if ($row = $stmtFuzzy->fetch(PDO::FETCH_ASSOC)) {
                    $item_id = (int)$row['menu_item_id'];
                }
            }

            // 3. Absolute fallback to first item in database
            if ($item_id === 0) {
                $fallback_stmt = $pdo->query("SELECT menu_item_id FROM menu_items LIMIT 1");
                $fallback_row = $fallback_stmt->fetch(PDO::FETCH_ASSOC);
                $item_id = $fallback_row ? (int)$fallback_row['menu_item_id'] : 1;
            }

            $customization_notes = trim($item['customization_notes'] ?? '');
            if ($customization_notes === '') {
                $customization_notes = null;
            }

            // Insert the item into the order
            $insert_item_stmt = $pdo->prepare("INSERT INTO order_items (order_id, menu_item_id, quantity, unit_price, customization_notes) VALUES (?, ?, 1, ?, ?)");
            $insert_item_stmt->execute([$order_id, $item_id, $price, $customization_notes]);
        }
    }

    // 4. Handle Receipt Upload & `payments` table
    if (isset($_FILES['receipt_image']) && $_FILES['receipt_image']['error'] === UPLOAD_ERR_OK && $_FILES['receipt_image']['name'] !== '') {
        $receipt_name = time() . '_' . basename($_FILES['receipt_image']['name']);
        $receipt_tmp = $_FILES['receipt_image']['tmp_name'];
        $upload_dir = __DIR__ . '/../assets/receipts/';
        $upload_path = $upload_dir . $receipt_name;

        // Create the directory if it doesn't exist
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        // Move the file and insert payment record
        if (move_uploaded_file($receipt_tmp, $upload_path)) {
            $payment_stmt = $pdo->prepare("INSERT INTO payments (order_id, payment_method, amount_paid, receipt_image, payment_status) VALUES (?, ?, ?, ?, 'Pending')");
            $payment_stmt->execute([$order_id, $payment_method, $total_amount, $receipt_name]);
        }
    } else {
        // If they pay by cash (no receipt image), we still need to log the payment record!
        $payment_stmt = $pdo->prepare("INSERT INTO payments (order_id, payment_method, amount_paid, payment_status) VALUES (?, ?, ?, 'Pending')");
        $payment_stmt->execute([$order_id, $payment_method, $total_amount]);
    }

    // 5. Success! Redirect to confirmation page
    header('Location: ../confirmation.php?order_id=' . $order_id);
    exit();

} catch (PDOException $e) {
    // If anything fails in the database, it prints exactly what went wrong
    die("Database Error: " . $e->getMessage());
}
?>