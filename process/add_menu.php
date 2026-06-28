<?php
require_once __DIR__ . '/../config/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../manage_menu.php');
    exit();
}

$item_name = trim($_POST['item_name'] ?? '');
$category = trim($_POST['category'] ?? '');
$price = (float) ($_POST['price'] ?? 0);
$status = trim($_POST['status'] ?? 'Available');
$image_name = null;

if ($item_name === '' || $category === '' || $price <= 0) {
    echo "Error adding menu item: missing required fields.";
    exit();
}

if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK && $_FILES['image']['name'] !== '') {
    $image_name = time() . '_' . basename($_FILES['image']['name']);
    $image_tmp = $_FILES['image']['tmp_name'];
    $upload_dir = __DIR__ . '/../assets/images/';
    $upload_path = $upload_dir . $image_name;

    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    move_uploaded_file($image_tmp, $upload_path);
}

$is_available = (strcasecmp($status, 'Available') === 0) ? 1 : 0;

$stmt = mysqli_prepare($conn, "INSERT INTO menu_items (item_name, category_name, price, is_available) VALUES (?, ?, ?, ?)");
if (!$stmt) {
    die("Error adding menu item: " . mysqli_error($conn));
}

mysqli_stmt_bind_param($stmt, 'ssdi', $item_name, $category, $price, $is_available);

if (mysqli_stmt_execute($stmt)) {
    mysqli_stmt_close($stmt);
    header('Location: ../manage_menu.php');
    exit();
}

echo "Error adding menu item: " . mysqli_stmt_error($stmt);
mysqli_stmt_close($stmt);
?>