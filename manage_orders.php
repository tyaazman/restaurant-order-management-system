<?php
session_start();
require_once 'config/db.php';

// ==========================================
//  AJAX ENDPOINT — Update Order Status
// ==========================================
if (isset($_GET['ajax'])) {
    header('Content-Type: application/json');
    $action = $_GET['ajax'];

    if ($action === 'update_order' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $orderId = intval($_POST['order_id'] ?? 0);
        $status  = trim($_POST['status']   ?? '');
        $allowed = ['Pending', 'Preparing', 'Completed'];

        if (!$orderId || !in_array($status, $allowed)) {
            echo json_encode(['success'=>false,'error'=>'Invalid data.']); exit;
        }

        $userId = isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : null;
        $pdo->prepare("UPDATE orders SET order_status=?, user_id=? WHERE order_id=?")->execute([$status, $userId, $orderId]);
        
        if ($status === 'Completed') {
            $pdo->prepare("UPDATE order_items SET item_status = 'Completed' WHERE order_id = ?")->execute([$orderId]);
            $pdo->prepare("UPDATE payments SET payment_status = 'Completed' WHERE order_id = ?")->execute([$orderId]);
        }

        echo json_encode(['success'=>true, 'status'=>$status, 'order_id'=>$orderId]);
        exit;
    }

    echo json_encode(['success'=>false,'error'=>'Unknown action.']);
    exit;
}

// ==========================================
//  REGULAR PAGE LOAD
// ==========================================
$date         = $_GET['date']   ?? date('Y-m-d');
$statusFilter = $_GET['status'] ?? '';

$allowedStatuses = ['Pending', 'Preparing', 'Completed'];

/* ── Fetch orders for selected date ── */
$sql    = "SELECT * FROM orders WHERE DATE(created_at) = ?";
$params = [$date];
if ($statusFilter && in_array($statusFilter, $allowedStatuses)) {
    $sql    .= " AND order_status = ?";
    $params[] = $statusFilter;
}
// Sort by status: Pending -> Preparing -> Completed, then by date descending
$sql .= " ORDER BY 
            CASE order_status 
                WHEN 'Pending' THEN 1 
                WHEN 'Preparing' THEN 2 
                WHEN 'Completed' THEN 3 
                ELSE 4 
            END ASC, 
            created_at DESC";

$stmt   = $pdo->prepare($sql);
$stmt->execute($params);
$orders = $stmt->fetchAll();

/* ── Attach order items to each order ── */
foreach ($orders as &$order) {
    $s2 = $pdo->prepare(
        "SELECT oi.order_item_id, oi.quantity, (oi.unit_price * oi.quantity) AS subtotal,
                oi.customization_notes, m.item_name, m.price
         FROM order_items oi
         LEFT JOIN menu_items m ON oi.menu_item_id = m.menu_item_id
         WHERE oi.order_id = ?
         ORDER BY oi.order_item_id ASC"
    );
    $s2->execute([$order['order_id']]);
    $order['items'] = $s2->fetchAll();
}
unset($order);

/* ── Summary counts ── */
$countSql    = "SELECT order_status, COUNT(*) as cnt FROM orders WHERE DATE(created_at) = ? GROUP BY order_status";
$cStmt       = $pdo->prepare($countSql);
$cStmt->execute([$date]);
$countRows   = $cStmt->fetchAll();
$countByStatus = ['Pending'=>0,'Preparing'=>0,'Completed'=>0];
foreach ($countRows as $cr) { 
    if (isset($countByStatus[$cr['order_status']])) {
        $countByStatus[$cr['order_status']] = (int)$cr['cnt']; 
    }
}
$totalOrders = array_sum($countByStatus);

/* ── Status config (badge classes + labels) ── */
$STATUS_PHP = [
    'Pending'     => ['badge'=>'badge-pending',    'icon'=>'⚠'],
    'Preparing'   => ['badge'=>'badge-inprogress', 'icon'=>'🍳'],
    'Completed'   => ['badge'=>'badge-completed',  'icon'=>'✔'],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Manage restaurant orders — view and update order status">
    <title>Manage Orders — Restaurant ZZ</title>
    <link rel="stylesheet" href="css/admin.css">
    <style>
        /* ── Filter bar ── */
        .filter-bar { display:flex; gap:10px; flex-wrap:wrap; align-items:center; margin-bottom:18px; }
        .filter-bar select,
        .filter-bar input  { width:auto; margin:0; }

        /* ── Summary ribbon ── */
        .summary-ribbon { display:flex; gap:12px; flex-wrap:wrap; margin-bottom:22px; }
        .summary-pill {
            flex:1; min-width:120px; background:var(--white); border-radius:10px;
            padding:14px 18px; text-align:center;
            box-shadow:0 2px 10px rgba(0,0,0,0.07); border-top:4px solid var(--bg-cream);
            cursor:pointer; transition:transform 0.15s, box-shadow 0.15s;
        }
        .summary-pill:hover { transform:translateY(-2px); box-shadow:0 4px 16px rgba(0,0,0,0.12); }
        .summary-pill .pill-num  { font-size:1.8rem; font-weight:700; color:var(--text-brown); }
        .summary-pill .pill-lbl  { font-size:0.75rem; font-weight:600; color:#888; text-transform:uppercase; letter-spacing:0.05em; margin-top:2px; }
        .summary-pill.sp-pending     { border-top-color:#c89100; }
        .summary-pill.sp-inprogress  { border-top-color:var(--accent-orange); }
        .summary-pill.sp-completed   { border-top-color:#888; }

        /* ── Order card ── */
        .order-card {
            background: var(--white); border-radius: 12px; margin-bottom: 16px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08); overflow: hidden;
            border-left: 5px solid var(--bg-cream); transition:border-color 0.2s;
        }
        .order-card.status-Pending     { border-left-color: #c89100; }
        .order-card.status-Preparing   { border-left-color: var(--accent-orange); }
        .order-card.status-Completed   { border-left-color: #aaa; opacity: 0.75; }
        .order-card.status-Completed:hover { opacity: 1; }

        .order-header {
            display: flex; justify-content: space-between; align-items: center;
            padding: 14px 18px; cursor: pointer; user-select: none;
        }
        .order-header:hover { background: rgba(0,0,0,0.02); }
        .order-meta h4 { margin:0; font-size:0.97rem; color:var(--text-brown); }
        .order-meta p  { margin:3px 0 0; font-size:0.79rem; color:#888; }

        /* Badge */
        .badge-pending    { background:#fff3cd; color:#856404; border:1px solid #ffc107; }
        .badge-inprogress { background:#fff0e6; color:#7a3508; border:1px solid var(--accent-orange); }
        .badge-completed  { background:#e2e3e5; color:#383d41; border:1px solid #adb5bd; }
        .status-badge {
            display:inline-block; padding:4px 12px; border-radius:20px;
            font-size:0.78rem; font-weight:700; letter-spacing:0.03em;
        }

        /* Order body */
        .order-body { padding:0 18px 14px; }
        .order-body.collapsed { display:none; }

        /* Items table in card */
        .items-table { width:100%; border-collapse:collapse; font-size:0.85rem; margin-bottom:12px; }
        .items-table th { background:var(--bg-cream); color:var(--text-brown); padding:7px 10px; font-weight:600; text-align:left; border-bottom:2px solid #e0d5c5; }
        .items-table td { padding:7px 10px; border-bottom:1px solid #f0e8df; }
        .items-table tr:last-child td { border-bottom:none; }

        /* Order footer & action buttons */
        .order-footer { display:flex; gap:10px; align-items:center; justify-content:space-between; margin-top:10px; flex-wrap:wrap; background:#f9f6f2; padding:12px; border-radius:8px;}
        .order-total  { font-weight:700; color:var(--text-brown); font-size:1rem; }
        
        .action-btns { display:flex; gap:8px; }
        .btn-action { padding:6px 14px; font-size:0.82rem; font-weight:600; border-radius:6px; cursor:pointer; border:none; transition:opacity 0.2s; }
        .btn-action:hover { opacity:0.85; }
        .btn-preparing { background:var(--accent-orange); color:#fff; }
        .btn-completed { background:#2d7a4f; color:#fff; }

        /* No orders */
        .no-orders { text-align:center; padding:60px 20px; color:#aaa; }
        .no-orders .icon { font-size:3rem; margin-bottom:10px; }

        /* Type badge */
        .type-walkin  { background:var(--bg-cream); color:var(--text-brown); font-size:0.7rem; font-weight:700; padding:2px 8px; border-radius:10px; margin-left:8px; }
        .type-online  { background:#e8f0ff; color:#1a4e9e; font-size:0.7rem; font-weight:700; padding:2px 8px; border-radius:10px; margin-left:8px; }

        /* Toggle chevron */
        .toggle-icon { font-size:0.9rem; color:#bbb; transition:transform 0.2s; }
        .toggle-icon.open { transform:rotate(180deg); }

        /* Active filter pill */
        .active-filter { background:var(--accent-orange); color:#fff; padding:4px 12px; border-radius:12px; font-size:0.78rem; font-weight:600; }
    </style>
</head>
<body class="staff-portal">

    <div class="sidebar">
        <h2>Restaurant</h2>
        <a href="staff_dashboard.php">Dashboard</a>
        <a href="manage_orders.php" style="background-color: var(--accent-orange); color: white;">Manage Orders</a>
        <a href="manage_menu.php">Manage Menu</a>
        <a href="generate_qr.php">Customer QR Code</a>
        <a href="#" onclick="logoutStaff(); return false;" class="sidebar-logout">Logout</a>
    </div>

    <div class="main-content">
        <h1 style="border-bottom: 2px solid var(--text-brown); padding-bottom:10px;">
            Manage Orders
            <?php if ($statusFilter): ?>
                <span class="active-filter" style="font-size:0.8rem; vertical-align:middle;">
                    <?= htmlspecialchars($statusFilter) ?> ✕
                </span>
            <?php endif; ?>
        </h1>

        <!-- ── Summary Ribbon ── -->
        <div class="summary-ribbon">
            <div class="summary-pill sp-pending" onclick="filterStatus('Pending')" title="Filter: Pending">
                <div class="pill-num"><?= $countByStatus['Pending'] ?></div>
                <div class="pill-lbl">⚠ Pending</div>
            </div>
            <div class="summary-pill sp-inprogress" onclick="filterStatus('Preparing')" title="Filter: Preparing">
                <div class="pill-num"><?= $countByStatus['Preparing'] ?></div>
                <div class="pill-lbl">🍳 Preparing</div>
            </div>
            <div class="summary-pill sp-completed" onclick="filterStatus('Completed')" title="Filter: Completed">
                <div class="pill-num"><?= $countByStatus['Completed'] ?></div>
                <div class="pill-lbl">✔ Completed</div>
            </div>
            <div class="summary-pill" onclick="filterStatus('')" title="Show all">
                <div class="pill-num"><?= $totalOrders ?></div>
                <div class="pill-lbl">📋 All Orders</div>
            </div>
        </div>

        <!-- ── Filter Bar ── -->
        <div class="card" style="padding:14px 20px; margin-bottom:16px;">
            <div class="filter-bar">
                <strong>Date:</strong>
                <input type="date" id="dateFilter" value="<?= htmlspecialchars($date) ?>" onchange="filterDate()">

                <strong>Status:</strong>
                <select id="statusFilter" onchange="filterStatus(this.value)">
                    <option value="">All</option>
                    <option value="Pending"   <?= $statusFilter==='Pending'   ?'selected':'' ?>>Pending</option>
                    <option value="Preparing" <?= $statusFilter==='Preparing' ?'selected':'' ?>>Preparing</option>
                    <option value="Completed" <?= $statusFilter==='Completed' ?'selected':'' ?>>Completed</option>
                </select>

                <strong>Search:</strong>
                <input type="text" id="searchInput" placeholder="🔍 Search name, table…" oninput="searchCards()">

                <span style="color:#888;font-size:0.83rem;">
                    <?= count($orders) ?> order<?= count($orders)!==1?'s':'' ?> on <?= htmlspecialchars(date('d M Y', strtotime($date))) ?>
                </span>
            </div>
        </div>

        <!-- ── Orders Container ── -->
        <div id="ordersContainer">
<?php if (empty($orders)): ?>
            <div class="no-orders">
                <div class="icon">📋</div>
                <p>No orders found for <strong><?= htmlspecialchars(date('d M Y', strtotime($date))) ?></strong>
                <?php if ($statusFilter): ?>with status <strong><?= htmlspecialchars($statusFilter) ?></strong><?php endif; ?>.</p>
                <p style="font-size:0.83rem;color:#bbb;">Try changing the date or status filter.</p>
            </div>
<?php else: ?>
<?php foreach ($orders as $order):
    $oid        = $order['order_id'];
    $oStatus    = $order['order_status'] ?? 'Pending';
    $sc         = $STATUS_PHP[$oStatus] ?? $STATUS_PHP['Pending'];
    $statusKey  = str_replace(' ', '_', $oStatus);
    $oType      = $order['order_type'] ?? 'Walk-In';
    $typeClass  = strtolower(str_replace('-','', $oType))==='walkin' ? 'type-walkin' : 'type-online';
    $tableLabel = ($order['table_number'] ?? '') ? 'Table '.$order['table_number'] : 'Online';
    $oTime      = date('h:i A', strtotime($order['created_at']));
    $oTotal     = number_format((float)$order['total_amount'], 2);
?>
            <div class="order-card status-<?= htmlspecialchars($statusKey) ?>" id="card_<?= $oid ?>">

                <!-- Card Header (click to toggle) -->
                <div class="order-header" onclick="toggleCard(<?= $oid ?>)">
                    <div class="order-meta">
                        <h4>
                            Order #<?= $oid ?> — <?= htmlspecialchars($order['customer_name']) ?>
                            <span class="<?= $typeClass ?>"><?= htmlspecialchars($oType) ?></span>
                        </h4>
                        <p><?= $oTime ?> &nbsp;|&nbsp; <?= $tableLabel ?></p>
                    </div>
                    <div style="display:flex;align-items:center;gap:12px;">
                        <span class="status-badge <?= $sc['badge'] ?>" id="badge_<?= $oid ?>">
                            <?= $sc['icon'] ?> <?= htmlspecialchars($oStatus) ?>
                        </span>
                        <span class="toggle-icon open" id="chevron_<?= $oid ?>">▼</span>
                    </div>
                </div>

                <!-- Card Body (items list) -->
                <div class="order-body" id="body_<?= $oid ?>">
                    <table class="items-table">
                        <thead>
                            <tr>
                                <th>Item Name</th>
                                <th style="width:100px; text-align:center;">Qty</th>
                                <th style="width:120px; text-align:right;">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
<?php foreach ($order['items'] as $item):
    $iName = htmlspecialchars($item['item_name'] ?? '—');
    $remarkHtml = ($item['customization_notes'] ?? '') ? '<br><small style="color: #A85530; font-style: italic; font-weight: bold;">Remark: '.htmlspecialchars($item['customization_notes']).'</small>' : '';
?>
                            <tr>
                                <td><?= $iName ?><?= $remarkHtml ?></td>
                                <td style="text-align:center;">x<?= $item['quantity'] ?></td>
                                <td style="text-align:right;">RM <?= number_format((float)$item['subtotal'],2) ?></td>
                            </tr>
<?php endforeach; ?>
                        </tbody>
                    </table>

                    <div class="order-footer">
                        <div>
                            <span class="order-total">Total: RM <?= $oTotal ?></span><br>
                            <?php if ($order['shipping_address']): ?>
                                <span style="font-size:0.8rem;color:#888;">📍 <?= htmlspecialchars($order['shipping_address']) ?></span>
                            <?php endif; ?>
                        </div>
                        
                        <div class="action-btns" id="actions_<?= $oid ?>">
                            <?php if ($oStatus === 'Pending'): ?>
                                <button class="btn-action btn-preparing" onclick="updateOrderStatus(<?= $oid ?>, 'Preparing')">
                                    🍳 Start Preparing
                                </button>
                            <?php elseif ($oStatus === 'Preparing'): ?>
                                <button class="btn-action btn-completed" onclick="updateOrderStatus(<?= $oid ?>, 'Completed')">
                                    ✔ Mark Completed
                                </button>
                            <?php endif; ?>
                            <!-- No buttons if Completed -->
                        </div>
                    </div>
                </div>
            </div>
<?php endforeach; ?>
<?php endif; ?>
        </div><!-- /#ordersContainer -->
    </div><!-- /.main-content -->

    <div id="ros-toast" style="display:none;"></div>
    <script src="js/admin_validation.js?v=<?= time() ?>"></script>
    <script>
        requireAuth();

        // ── Toast ──
        function showToast(msg, isErr) {
            var t = document.getElementById('ros-toast');
            if (t) {
                t.style.display = 'block';
                t.style.background = isErr ? '#5E2A25' : 'var(--text-brown)';
                t.style.color = '#fff';
                t.innerText = msg;
                t.style.opacity = '1';
                clearTimeout(window._toastTimer);
                window._toastTimer = setTimeout(() => { t.style.opacity='0'; setTimeout(() => t.style.display='none', 350); }, 2800);
            }
        }

        // ── Date / Status filter (page reload) ──
        function filterDate() {
            var d = document.getElementById('dateFilter').value;
            var s = new URLSearchParams(window.location.search);
            s.set('date', d);
            s.delete('status');
            window.location.href = 'manage_orders.php?' + s.toString();
        }

        function filterStatus(val) {
            var s = new URLSearchParams(window.location.search);
            if (val) s.set('status', val); else s.delete('status');
            window.location.href = 'manage_orders.php?' + s.toString();
        }

        // ── Search (client-side card hide/show) ──
        function searchCards() {
            var q = document.getElementById('searchInput').value.toLowerCase();
            document.querySelectorAll('.order-card').forEach(function(card) {
                card.style.display = card.innerText.toLowerCase().includes(q) ? '' : 'none';
            });
        }

        // ── Collapse / Expand card ──
        function toggleCard(oid) {
            var body    = document.getElementById('body_'+oid);
            var chevron = document.getElementById('chevron_'+oid);
            var isOpen  = !body.classList.contains('collapsed');
            body.classList.toggle('collapsed', isOpen);
            chevron.classList.toggle('open', !isOpen);
        }

        // ── Update Order Status ──
        function updateOrderStatus(orderId, newStatus) {
            if (newStatus === 'Completed' && !confirm('Mark Order #'+orderId+' as Completed?')) return;

            var fd = new FormData();
            fd.append('order_id', orderId);
            fd.append('status', newStatus);

            fetch('manage_orders.php?ajax=update_order', { method:'POST', body:fd })
                .then(r => r.json())
                .then(function(res) {
                    if (!res.success) { showToast('⚠ '+(res.error||'Update failed.'), true); return; }
                    
                    showToast('✅ Order #'+orderId+' status updated to '+newStatus);
                    // Reload to reflect sorting changes automatically
                    setTimeout(() => window.location.reload(), 600);
                })
                .catch(() => showToast('⚠ Network error.', true));
        }
    </script>
</body>
</html>