<?php
// Set default timezone
date_default_timezone_set('Asia/Kuala_Lumpur');

require_once __DIR__ . '/../config/db.php';
header('Content-Type: application/json');

$category = $_GET['category'] ?? '';

if (!$category) {
    echo json_encode(['success' => false, 'error' => 'Category name is required.']);
    exit();
}

try {
    $stmt = $pdo->prepare("SELECT menu_item_id, item_name, price, category_name FROM menu_items WHERE category_name = ? AND is_available = 1 ORDER BY menu_item_id ASC");
    $stmt->execute([$category]);
    $items = $stmt->fetchAll();

    // Fetch options
    $optStmt = $pdo->query("SELECT option_id, menu_item_id, option_group, option_name, additional_price FROM menu_item_options ORDER BY option_id ASC");
    $options = $optStmt->fetchAll();

    // Group options by menu_item_id
    $optionsByItem = [];
    foreach ($options as $opt) {
        $optionsByItem[$opt['menu_item_id']][] = $opt;
    }

    // Attach options to items
    foreach ($items as &$item) {
        $item['options'] = $optionsByItem[$item['menu_item_id']] ?? [];
    }
    unset($item);

    echo json_encode(['success' => true, 'items' => $items]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
