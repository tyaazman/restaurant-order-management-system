<?php
session_start();
require_once __DIR__ . '/config/db.php';

// Auth guard - ensure only staff/admin can access
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Automatically resolve server's local network IP on Windows XAMPP
$local_ip = gethostbyname(gethostname());

// Default parameters
$table_no = isset($_GET['table_no']) && $_GET['table_no'] !== '' ? intval($_GET['table_no']) : '';
$url = "http://{$local_ip}/Assignment2%20-%20BITM/index.html";
if ($table_no !== '') {
    $url .= "?table={$table_no}";
}

// Generate the QR Code image URL using a free secure public API
$qr_api_url = "https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=" . urlencode($url);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer QR Generator — Restaurant ZZ</title>
    <link rel="stylesheet" href="css/admin.css">
    <style>
        .qr-card {
            max-width: 450px;
            margin: 0 auto;
            text-align: center;
            background: #fff;
            padding: 40px;
            border-radius: 16px;
            box-shadow: 0 8px 30px rgba(0,0,0,0.06);
            border-top: 8px solid var(--accent-orange);
        }
        .qr-image-wrapper {
            margin: 25px auto;
            padding: 15px;
            border: 2px dashed #D2B48C;
            border-radius: 12px;
            display: inline-block;
            background: #fff;
        }
        .qr-image-wrapper img {
            display: block;
            max-width: 100%;
        }
        .qr-url-display {
            font-family: monospace;
            background: #f7f3eb;
            padding: 8px 12px;
            border-radius: 6px;
            font-size: 0.85rem;
            color: #555;
            word-break: break-all;
            margin: 15px 0;
            border: 1px solid #e1d3a9;
        }
        .print-btn {
            background-color: var(--danger-red);
            color: white;
            padding: 12px 24px;
            border-radius: 30px;
            font-weight: 600;
            font-size: 0.95rem;
            transition: all 0.2s;
            margin-top: 15px;
            border: none;
            cursor: pointer;
        }
        .print-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(94, 42, 37, 0.3);
        }
        
        /* Print styling rules */
        @media print {
            body * {
                visibility: hidden;
            }
            .qr-print-section, .qr-print-section * {
                visibility: visible;
            }
            .qr-print-section {
                position: absolute;
                left: 50%;
                top: 50%;
                transform: translate(-50%, -50%);
                width: 100%;
                margin: 0;
                padding: 0;
                box-shadow: none;
                border: none;
            }
            .sidebar, .print-btn, form, h1, hr, select, button {
                display: none !important;
            }
            .main-content {
                margin: 0 !important;
                padding: 0 !important;
            }
        }
    </style>
</head>
<body>

    <div class="sidebar">
        <h2>Restaurant</h2>
        <a href="staff_dashboard.php">Dashboard</a>
        <a href="manage_orders.php">Manage Orders</a>
        <a href="manage_menu.php">Manage Menu</a>
        <a href="generate_qr.php" style="background-color: var(--accent-orange); color: white;">Customer QR Code</a>
        <a href="#" onclick="logoutStaff(); return false;" class="sidebar-logout">Logout</a>
    </div>

    <div class="main-content">
        <h1 style="border-bottom: 2px solid var(--text-brown); padding-bottom: 10px; margin-bottom: 30px;">Customer QR Code Generator</h1>

        <div style="max-width: 600px; margin-bottom: 25px; background: var(--white); padding: 20px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.03);">
            <form method="GET" action="generate_qr.php" style="display: flex; align-items: flex-end; gap: 15px; flex-wrap: wrap;">
                <div style="flex: 1; min-width: 200px;">
                    <label style="font-weight: 600; font-size: 0.88rem; color: var(--text-brown); display: block; margin-bottom: 6px;">Assign to Table (Optional):</label>
                    <select name="table_no" style="margin: 0;" onchange="this.form.submit()">
                        <option value="">-- General Restaurant QR --</option>
                        <?php for($i=1; $i<=20; $i++): ?>
                            <option value="<?= $i ?>" <?= $table_no === $i ? 'selected' : '' ?>>Table <?= $i ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
                <button type="submit" style="height: 42px; margin-bottom: 15px; background-color: var(--accent-orange); color: var(--white); border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer; font-family: 'Poppins', sans-serif; font-weight: 600;">Generate</button>
            </form>
        </div>

        <div class="qr-card qr-print-section">
            <h2 style="color: var(--text-brown); margin: 0; font-size: 1.6rem; font-weight: 600;">SUP TULANG ZZ</h2>
            <p style="color: #666; font-size: 0.9rem; margin: 5px 0 0 0;">SCAN TO ORDER ONLINE</p>
            
            <?php if ($table_no !== ''): ?>
                <div style="display: inline-block; background: var(--accent-orange); color: white; padding: 4px 18px; border-radius: 20px; font-weight: 700; font-size: 1.1rem; margin-top: 15px;">
                    TABLE <?= $table_no ?>
                </div>
            <?php endif; ?>

            <div class="qr-image-wrapper">
                <img src="<?= htmlspecialchars($qr_api_url) ?>" alt="QR Code Link" width="220" height="220">
            </div>

            <p style="font-size: 0.82rem; color: #777; margin: 0;">Scan this with your mobile phone camera to view the menu and start ordering.</p>
            
            <div class="qr-url-display">
                <?= htmlspecialchars($url) ?>
            </div>

            <button onclick="window.print()" class="print-btn">🖨️ Print QR Label</button>
        </div>
    </div>

    <script src="js/admin_validation.js"></script>
    <script>
        // Guard check: Ensure staff session is valid
        requireAuth();
    </script>
</body>
</html>
