<?php
/**
 * Database Connection — Restaurant Order System
 * Database: restaurant_order_db (XAMPP MySQL, root / no password)
 */

$host    = 'localhost';
$dbname  = 'restaurant_order_db';
$dbuser  = 'root';
$dbpass  = '';
$charset = 'utf8mb4';

// Initialize mysqli connection
$conn = mysqli_connect($host, $dbuser, $dbpass, $dbname);
if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}
mysqli_set_charset($conn, $charset);

// Initialize PDO connection
$dsn = "mysql:host={$host};dbname={$dbname};charset={$charset}";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $dbuser, $dbpass, $options);
} catch (PDOException $e) {
    $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH'])
           || isset($_GET['ajax'])
           || (isset($_SERVER['CONTENT_TYPE']) && strpos($_SERVER['CONTENT_TYPE'], 'json') !== false);

    if ($isAjax) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => 'Database connection failed.']);
    } else {
        echo '<div style="font-family:\'Poppins\',sans-serif;padding:40px;color:#5E2A25;'
           . 'background:#fde8e8;border-radius:8px;margin:40px auto;max-width:600px;">'
           . '<h2>⚠ Database Error</h2>'
           . '<p>Could not connect to <strong>restaurant_order_db</strong>.</p>'
           . '<ul><li>Make sure XAMPP MySQL is running.</li>'
           . '<li>Check phpMyAdmin that the database exists.</li></ul>'
           . '<pre style="background:#fff;padding:10px;border-radius:4px;font-size:0.8rem;">'
           . htmlspecialchars($e->getMessage()) . '</pre></div>';
    }
    exit;
}
?>
