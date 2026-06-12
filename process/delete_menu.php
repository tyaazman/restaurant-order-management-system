<?php
require_once __DIR__ . '/../config/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET' && $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../manage_menu.php');
    exit();
}

$item_id = (int) ($_GET['id'] ?? $_POST['item_id'] ?? 0);

if ($item_id <= 0) {
    echo "Error deleting menu item: invalid item id.";
    exit();
}

$stmt = mysqli_prepare($conn, "DELETE FROM menu_items WHERE item_id = ?");
if (!$stmt) {
    die("Error deleting menu item: " . mysqli_error($conn));
}

mysqli_stmt_bind_param($stmt, 'i', $item_id);

if (mysqli_stmt_execute($stmt)) {
    mysqli_stmt_close($stmt);
    header('Location: ../manage_menu.php');
    exit();
}

echo "Error deleting menu item: " . mysqli_stmt_error($stmt);
mysqli_stmt_close($stmt);
?>