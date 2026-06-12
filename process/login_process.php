<?php
session_start();
require_once __DIR__ . '/../config/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../login.php');
    exit();
}

$username = trim($_POST['username'] ?? '');
$password = trim($_POST['password'] ?? '');

if ($username === '' || $password === '') {
    echo "<script>alert('Username and password are required'); window.location.href='../login.php';</script>";
    exit();
}

$stmt = mysqli_prepare($conn, "SELECT user_id, username, password, role FROM users WHERE username = ? AND password = ? LIMIT 1");
if (!$stmt) {
    die("Login query failed: " . mysqli_error($conn));
}

mysqli_stmt_bind_param($stmt, 'ss', $username, $password);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user = $result ? mysqli_fetch_assoc($result) : null;

if ($user) {
    $_SESSION['user_id'] = $user['user_id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['role'] = $user['role'];

    header('Location: ../staff_dashboard.php');
    exit();
}

echo "<script>alert('Invalid username or password'); window.location.href='../login.php';</script>";
exit();
?>