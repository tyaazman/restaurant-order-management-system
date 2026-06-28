<?php
session_start();
require_once 'config/db.php';

// ── Fetch date (default today) ──────────────────────────────────
$date    = $_GET['date'] ?? date('Y-m-d');
$isToday = ($date === date('Y-m-d'));

// ── Stats (counts per status for that date) ─────────────────────
$sStat = $pdo->prepare(
    "SELECT order_status, COUNT(*) AS cnt FROM orders WHERE DATE(order_date) = ? GROUP BY order_status"
);
$sStat->execute([$date]);
$stats = ['Pending'=>0,'In Progress'=>0,'Ready'=>0,'Completed'=>0];
foreach ($sStat->fetchAll() as $r) {
    if (isset($stats[$r['order_status']])) $stats[$r['order_status']] = (int)$r['cnt'];
}
$totalOrders = array_sum($stats);

// ── Orders summary list ─────────────────────────────────────────
$sOrd = $pdo->prepare(
    "SELECT o.order_id, o.customer_name, o.order_type, o.table_no,
            o.order_status, o.total_amount,
            GROUP_CONCAT(CONCAT(oi.quantity,'x ',m.item_name) ORDER BY oi.order_item_id SEPARATOR ', ') AS items_summary
     FROM orders o
     LEFT JOIN order_items oi ON o.order_id = oi.order_id
     LEFT JOIN menu_items  m  ON oi.item_id  = m.item_id
     WHERE DATE(o.order_date) = ?
     GROUP BY o.order_id
     ORDER BY o.order_date DESC"
);
$sOrd->execute([$date]);
$orders = $sOrd->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Staff dashboard — real-time overview of today's orders">
    <title>Staff Dashboard — Restaurant ZZ</title>
    <link rel="stylesheet" href="css/admin.css">
    <style>
        /* ── Stat Grid ── */
        .grid {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 16px;
            margin-bottom: 30px;
        }
        .stat-box { text-align: center; padding: 24px 12px; cursor:pointer; transition:transform 0.15s,box-shadow 0.15s; }
        .stat-box:hover { transform:translateY(-2px); box-shadow:0 6px 20px rgba(0,0,0,0.10); }
        .stat-box h3 {
            margin: 0;
            color: var(--text-brown);
            font-size: 0.78rem;
            text-transform: uppercase;
            letter-spacing: 0.07em;
        }
        .stat-box p {
            font-size: 2.2em;
            margin: 8px 0 0 0;
            font-weight: bold;
            color: var(--accent-orange);
        }
        .stat-box.box-pending    p { color: #5E2A25; }
        .stat-box.box-inprogress p { color: #a85530; }
        .stat-box.box-ready      p { color: #c47a2b; }
        .stat-box.box-completed  p { color: #2d7a4f; }

        /* ── Date Filter ── */
        .date-filter { margin-bottom: 20px; display: flex; align-items: center; gap: 10px; }

        /* ── Status cells ── */
        .status-cell { display: flex; align-items: center; }

        .status-bar {
            display: inline-flex; align-items: center; gap: 8px;
            padding: 6px 14px 6px 10px; border-radius: 6px;
            font-weight: 700; font-size: 0.82rem;
            text-decoration: none; border-left: 4px solid;
            transition: filter 0.2s, transform 0.15s; cursor: pointer; white-space: nowrap;
        }
        .status-bar:hover { filter: brightness(0.93); transform: translateX(2px); }

        .status-bar.bar-pending    { background:rgba(94,42,37,0.09);  color:#5E2A25; border-color:#5E2A25; }
        .status-bar.bar-inprogress { background:rgba(168,85,48,0.09); color:#a85530; border-color:#a85530; }

        .status-bar.bar-pending::before,
        .status-bar.bar-inprogress::before {
            content:''; width:8px; height:8px; border-radius:50%;
            background:currentColor; flex-shrink:0;
            animation:pulseDot 1.2s ease-in-out infinite;
        }
        @keyframes pulseDot {
            0%,100% { opacity:1; transform:scale(1); }
            50%      { opacity:0.4; transform:scale(1.5); }
        }

        .btn-ready {
            background: linear-gradient(135deg,#e67e22,var(--accent-orange));
            color:#fff; border:none; padding:6px 15px; border-radius:20px;
            font-size:0.82rem; font-weight:600; cursor:pointer;
            font-family:'Poppins',sans-serif;
            box-shadow:0 2px 8px rgba(168,85,48,0.35);
            transition:transform 0.15s,box-shadow 0.15s;
        }
        .btn-ready:hover { transform:translateY(-1px); box-shadow:0 4px 14px rgba(168,85,48,0.5); opacity:1; }

        .text-completed { color:#2d7a4f; font-weight:700; font-size:0.88rem; }
        .text-pending   { color:#856404; font-weight:700; font-size:0.88rem; }
        .no-orders { text-align:center; padding:40px; color:#aaa; }

        /* type badges */
        .badge-walkin { background:var(--bg-cream); color:var(--text-brown); font-size:0.7rem; font-weight:700; padding:2px 8px; border-radius:10px; margin-left:6px; }
        .badge-online { background:#e8f0ff; color:#1a4e9e; font-size:0.7rem; font-weight:700; padding:2px 8px; border-radius:10px; margin-left:6px; }
    </style>
</head>
<body>

    <div class="sidebar">
        <h2>Restaurant</h2>
        <a href="staff_dashboard.php" style="background-color: var(--accent-orange); color: white;">Dashboard</a>
        <a href="manage_orders.php">Manage Orders</a>
        <a href="manage_menu.php">Manage Menu</a>
        <a href="#" onclick="logoutStaff(); return false;" class="sidebar-logout">Logout</a>
    </div>

    <div class="main-content">
        <h1 style="border-bottom: 2px solid var(--text-brown); padding-bottom: 10px;">Staff Dashboard</h1>

        <!-- ── Date Filter ── -->
        <div class="date-filter">
            <label for="dashboardDate"><strong>Viewing Orders For:</strong></label>
            <input type="date" id="dashboardDate"
                   value="<?= htmlspecialchars($date) ?>"
                   style="width: auto; margin: 0;"
                   onchange="window.location.href='staff_dashboard.php?date='+this.value">
        </div>

        <!-- ── 5 Stat Boxes ── -->
        <div class="grid">
            <div class="card stat-box" onclick="window.location.href='manage_orders.php?date=<?= $date ?>'">
                <h3>Total Orders</h3>
                <p><?= $totalOrders ?></p>
            </div>
            <div class="card stat-box box-pending" onclick="window.location.href='manage_orders.php?date=<?= $date ?>&status=Pending'">
                <h3>⚠ Pending</h3>
                <p><?= $stats['Pending'] ?></p>
            </div>
            <div class="card stat-box box-inprogress" onclick="window.location.href='manage_orders.php?date=<?= $date ?>&status=In+Progress'">
                <h3>🍳 In Progress</h3>
                <p><?= $stats['In Progress'] ?></p>
            </div>
            <div class="card stat-box box-ready" onclick="window.location.href='manage_orders.php?date=<?= $date ?>&status=Ready'">
                <h3>✅ Ready</h3>
                <p><?= $stats['Ready'] ?></p>
            </div>
            <div class="card stat-box box-completed" onclick="window.location.href='manage_orders.php?date=<?= $date ?>&status=Completed'">
                <h3>✔ Completed</h3>
                <p><?= $stats['Completed'] ?></p>
            </div>
        </div>

        <!-- ── Orders Table ── -->
        <div class="card">
            <h3 style="margin-top:0;">
                <?= $isToday ? "Today's Order Overview" : "Orders for ".date('d M Y', strtotime($date)) ?>
                <span style="font-size:0.78rem;font-weight:400;color:#aaa;margin-left:10px;">
                    Click a stat box above to filter by status in Manage Orders
                </span>
            </h3>
            <table>
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Customer</th>
                        <th>Type</th>
                        <th>Items Summary</th>
                        <th>Total (RM)</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
<?php if (empty($orders)): ?>
                    <tr>
                        <td colspan="6" class="no-orders">
                            📋 No orders recorded for
                            <strong><?= htmlspecialchars(date('d M Y', strtotime($date))) ?></strong>.
                        </td>
                    </tr>
<?php else: ?>
<?php foreach ($orders as $o):
    $oid    = $o['order_id'];
    $status = $o['order_status'] ?? 'Pending';
    $oType  = $o['order_type']   ?? 'Walk-In';
    $typeClass  = (strtolower(str_replace(['-',' '],'',$oType))==='walkin') ? 'badge-walkin' : 'badge-online';
    $tableInfo  = ($o['table_no'] ?? '') ? 'Table '.$o['table_no'] : 'Online';
?>
                    <tr id="dashRow_<?= $oid ?>">
                        <td><strong>#<?= $oid ?></strong></td>
                        <td>
                            <?= htmlspecialchars($o['customer_name']) ?>
                        </td>
                        <td>
                            <span class="<?= $typeClass ?>"><?= htmlspecialchars($oType) ?></span><br>
                            <span style="font-size:0.78rem;color:#888;"><?= htmlspecialchars($tableInfo) ?></span>
                        </td>
                        <td style="font-size:0.84rem;color:#555;max-width:260px;">
                            <?= htmlspecialchars($o['items_summary'] ?? '—') ?>
                        </td>
                        <td style="font-weight:700;color:var(--text-brown);">
                            RM <?= number_format((float)$o['total_amount'], 2) ?>
                        </td>
                        <td>
                            <div class="status-cell">
<?php if ($isToday): ?>
<?php   if ($status === 'Pending'): ?>
                                <a href="manage_orders.php?date=<?= $date ?>" class="status-bar bar-pending">Pending Items</a>
<?php   elseif ($status === 'In Progress'): ?>
                                <a href="manage_orders.php?date=<?= $date ?>" class="status-bar bar-inprogress">In Progress</a>
<?php   elseif ($status === 'Ready'): ?>
                                <button class="btn-ready" onclick="markCompleted(<?= $oid ?>)">✅ Ready — Mark Collected</button>
<?php   else: ?>
                                <span class="text-completed">✔ Completed</span>
<?php   endif; ?>
<?php else: ?>
                                <span class="text-<?= strtolower(str_replace(' ','',  $status)) ?>">
                                    <?= htmlspecialchars($status) ?>
                                </span>
<?php endif; ?>
                            </div>
                        </td>
                    </tr>
<?php endforeach; ?>
<?php endif; ?>
                </tbody>
            </table>
        </div>
    </div><!-- /.main-content -->

    <script src="js/admin_validation.js"></script>
    <script>
        requireAuth();

        function showToast(msg) {
            var t = document.getElementById('ros-toast');
            if (!t) {
                t = document.createElement('div');
                t.id = 'ros-toast';
                Object.assign(t.style, {
                    position:'fixed', bottom:'28px', right:'28px',
                    background:'var(--text-brown)', color:'#fff',
                    padding:'12px 22px', borderRadius:'8px',
                    fontFamily:"'Poppins',sans-serif", fontWeight:'600', fontSize:'0.9rem',
                    boxShadow:'0 4px 16px rgba(0,0,0,0.22)', zIndex:'9999', transition:'opacity 0.35s'
                });
                document.body.appendChild(t);
            }
            t.innerText = msg; t.style.opacity = '1';
            clearTimeout(window._toastTimer);
            window._toastTimer = setTimeout(() => t.style.opacity='0', 2800);
        }

        // Mark Ready order as Completed via AJAX → manage_orders endpoint
        function markCompleted(orderId) {
            var fd = new FormData();
            fd.append('order_id', orderId);
            fetch('manage_orders.php?ajax=complete_order', { method:'POST', body:fd })
                .then(r => r.json())
                .then(function(res) {
                    if (res.success) {
                        showToast('✔ Order #'+orderId+' marked as Completed!');
                        setTimeout(() => window.location.reload(), 1000);
                    }
                })
                .catch(() => showToast('⚠ Network error.'));
        }
    </script>
</body>
</html>