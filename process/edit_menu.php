<?php
require_once __DIR__ . '/../config/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../manage_menu.php');
    exit();
}

$menu_item_id = (int) ($_POST['menu_item_id'] ?? 0);
$item_name = trim($_POST['item_name'] ?? '');
$category = trim($_POST['category'] ?? '');
$price = (float) ($_POST['price'] ?? 0);

if ($menu_item_id <= 0 || $item_name === '' || $category === '' || $price <= 0) {
    echo "Error updating menu item: missing required fields.";
    exit();
}

$stmt = mysqli_prepare($conn, "UPDATE menu_items SET item_name = ?, category_name = ?, price = ? WHERE menu_item_id = ?");
if (!$stmt) {
    die("Error updating menu item: " . mysqli_error($conn));
}

mysqli_stmt_bind_param($stmt, 'ssdi', $item_name, $category, $price, $menu_item_id);

if (mysqli_stmt_execute($stmt)) {
    mysqli_stmt_close($stmt);
    header('Location: ../manage_menu.php');
    exit();
}

echo "Error updating menu item: " . mysqli_stmt_error($stmt);
mysqli_stmt_close($stmt);
?>
