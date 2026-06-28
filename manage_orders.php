<?php
session_start();
require_once 'config/db.php';

// ══════════════════════════════════════════════════════════
//  AJAX ENDPOINT — Update individual item status
// ══════════════════════════════════════════════════════════
if (isset($_GET['ajax'])) {
    header('Content-Type: application/json');
    $action = $_GET['ajax'];

    /* ── Update order_item status ── */
    if ($action === 'update_item' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $itemId = intval($_POST['item_id'] ?? 0);
        $status = trim($_POST['status']   ?? '');
        $allowed = ['Pending','In Progress','Ready','Completed'];

        if (!$itemId || !in_array($status, $allowed)) {
            echo json_encode(['success'=>false,'error'=>'Invalid data.']); exit;
        }

        $pdo->prepare("UPDATE order_items SET status=? WHERE order_item_id=?")->execute([$status, $itemId]);

        // Recompute overall order status from its items
        $row    = $pdo->prepare("SELECT order_id FROM order_items WHERE order_item_id=?");
        $row->execute([$itemId]);
        $orderId = $row->fetchColumn();

        $overall = computeOverall($pdo, $orderId);
        $pdo->prepare("UPDATE orders SET order_status=? WHERE order_id=?")->execute([$overall, $orderId]);

        echo json_encode(['success'=>true, 'overall_status'=>$overall, 'order_id'=>$orderId]);
        exit;
    }

    /* ── Mark entire order as Completed ── */
    if ($action === 'complete_order' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $orderId = intval($_POST['order_id'] ?? 0);
        if (!$orderId) { echo json_encode(['success'=>false]); exit; }

        $pdo->prepare("UPDATE order_items SET status='Completed' WHERE order_id=?")->execute([$orderId]);
        $pdo->prepare("UPDATE orders SET order_status='Completed' WHERE order_id=?")->execute([$orderId]);

        echo json_encode(['success'=>true]);
        exit;
    }

    echo json_encode(['success'=>false,'error'=>'Unknown action.']);
    exit;
}

// ══════════════════════════════════════════════════════════
//  HELPERS
// ══════════════════════════════════════════════════════════
function computeOverall(PDO $pdo, int $orderId): string {
    $s = $pdo->prepare("SELECT status FROM order_items WHERE order_id=?");
    $s->execute([$orderId]);
    $statuses = $s->fetchAll(PDO::FETCH_COLUMN);
    if (empty($statuses)) return 'Pending';
    $unique = array_unique($statuses);
    if ($unique === ['Completed']) return 'Completed';
    if (in_array('Ready', $statuses)) return 'Ready';
    if (in_array('In Progress', $statuses)) return 'In Progress';
    return 'Pending';
}

// ══════════════════════════════════════════════════════════
//  REGULAR PAGE LOAD
// ══════════════════════════════════════════════════════════
$date         = $_GET['date']   ?? date('Y-m-d');
$statusFilter = $_GET['status'] ?? '';

$allowedStatuses = ['Pending','In Progress','Ready','Completed'];

/* ── Fetch orders for selected date ── */
$sql    = "SELECT * FROM orders WHERE DATE(order_date) = ?";
$params = [$date];
if ($statusFilter && in_array($statusFilter, $allowedStatuses)) {
    $sql    .= " AND order_status = ?";
    $params[] = $statusFilter;
}
$sql .= " ORDER BY order_date DESC";

$stmt   = $pdo->prepare($sql);
$stmt->execute($params);
$orders = $stmt->fetchAll();

/* ── Attach order items to each order ── */
foreach ($orders as &$order) {
    $s2 = $pdo->prepare(
        "SELECT oi.order_item_id, oi.quantity, oi.subtotal, oi.status,
                m.item_name, m.price
         FROM order_items oi
         LEFT JOIN menu_items m ON oi.item_id = m.item_id
         WHERE oi.order_id = ?
         ORDER BY oi.order_item_id ASC"
    );
    $s2->execute([$order['order_id']]);
    $order['items'] = $s2->fetchAll();
}
unset($order);

/* ── Summary counts ── */
$countSql    = "SELECT order_status, COUNT(*) as cnt FROM orders WHERE DATE(order_date) = ? GROUP BY order_status";
$cStmt       = $pdo->prepare($countSql);
$cStmt->execute([$date]);
$countRows   = $cStmt->fetchAll();
$countByStatus = ['Pending'=>0,'In Progress'=>0,'Ready'=>0,'Completed'=>0];
foreach ($countRows as $cr) { $countByStatus[$cr['order_status']] = (int)$cr['cnt']; }
$totalOrders = array_sum($countByStatus);

/* ── Status config (badge classes + labels) ── */
$STATUS_PHP = [
    'Pending'     => ['badge'=>'badge-pending',    'pill'=>'pill-pending',    'icon'=>'⚠'],
    'In Progress' => ['badge'=>'badge-inprogress',  'pill'=>'pill-inprogress',  'icon'=>'🍳'],
    'Ready'       => ['badge'=>'badge-ready',       'pill'=>'pill-ready',       'icon'=>'✅'],
    'Completed'   => ['badge'=>'badge-completed',   'pill'=>'pill-completed',   'icon'=>'✔'],
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
        .summary-pill.sp-ready       { border-top-color:#2d7a4f; }
        .summary-pill.sp-completed   { border-top-color:#888; }

        /* ── Order card ── */
        .order-card {
            background: var(--white); border-radius: 12px; margin-bottom: 16px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08); overflow: hidden;
            border-left: 5px solid var(--bg-cream); transition:border-color 0.2s;
        }
        .order-card.status-Pending     { border-left-color: #c89100; }
        .order-card.status-In_Progress { border-left-color: var(--accent-orange); }
        .order-card.status-Ready       { border-left-color: #2d7a4f; }
        .order-card.status-Completed   { border-left-color: #aaa; }

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
        .badge-ready      { background:#d4edda; color:#155724; border:1px solid #28a745; }
        .badge-completed  { background:#e2e3e5; color:#383d41; border:1px solid #adb5bd; }
        .status-badge {
            display:inline-block; padding:4px 12px; border-radius:20px;
            font-size:0.78rem; font-weight:700; letter-spacing:0.03em;
        }

        /* Item pill status */
        .pill-pending    { background:#c89100; }
        .pill-inprogress { background:var(--accent-orange); }
        .pill-ready      { background:#2d7a4f; }
        .pill-completed  { background:#888; }
        .item-status-pill {
            display:inline-block; padding:2px 9px; border-radius:12px;
            font-size:0.72rem; font-weight:700; color:#fff; margin-left:6px;
        }

        /* Order body */
        .order-body { padding:0 18px 14px; }
        .order-body.collapsed { display:none; }

        /* Items table in card */
        .items-table { width:100%; border-collapse:collapse; font-size:0.85rem; margin-bottom:12px; }
        .items-table th { background:var(--bg-cream); color:var(--text-brown); padding:7px 10px; font-weight:600; text-align:left; }
        .items-table td { padding:7px 10px; border-bottom:1px solid #f0e8df; }
        .items-table tr:last-child td { border-bottom:none; }

        /* Status select */
        .status-select { width:auto; margin:0; padding:4px 8px; font-size:0.8rem; border-radius:6px; border:1px solid #ddd; }

        /* Order footer */
        .order-footer { display:flex; gap:10px; align-items:center; margin-top:10px; flex-wrap:wrap; }
        .order-total  { font-weight:700; color:var(--text-brown); font-size:0.9rem; }
        .btn-complete { padding:6px 16px; font-size:0.82rem; background:var(--text-brown); }
        .btn-complete:hover { opacity:0.85; }

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
<body>

    <div class="sidebar">
        <h2>Restaurant</h2>
        <a href="staff_dashboard.php">Dashboard</a>
        <a href="manage_orders.php" style="background-color: var(--accent-orange); color: white;">Manage Orders</a>
        <a href="manage_menu.php">Manage Menu</a>
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
            <div class="summary-pill sp-inprogress" onclick="filterStatus('In Progress')" title="Filter: In Progress">
                <div class="pill-num"><?= $countByStatus['In Progress'] ?></div>
                <div class="pill-lbl">🍳 In Progress</div>
            </div>
            <div class="summary-pill sp-ready" onclick="filterStatus('Ready')" title="Filter: Ready">
                <div class="pill-num"><?= $countByStatus['Ready'] ?></div>
                <div class="pill-lbl">✅ Ready</div>
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
                    <option value="Pending"     <?= $statusFilter==='Pending'     ?'selected':'' ?>>Pending</option>
                    <option value="In Progress" <?= $statusFilter==='In Progress' ?'selected':'' ?>>In Progress</option>
                    <option value="Ready"       <?= $statusFilter==='Ready'       ?'selected':'' ?>>Ready</option>
                    <option value="Completed"   <?= $statusFilter==='Completed'   ?'selected':'' ?>>Completed</option>
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
    $tableLabel = ($order['table_no'] ?? '') ? 'Table '.$order['table_no'] : 'Online';
    $oTime      = date('h:i A', strtotime($order['order_date']));
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
                        <p><?= $oTime ?> &nbsp;|&nbsp; <?= $tableLabel ?> &nbsp;|&nbsp; RM <?= $oTotal ?></p>
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
                                <th>Item</th>
                                <th style="width:70px;">Qty</th>
                                <th style="width:100px;">Subtotal</th>
                                <th style="width:180px;">Item Status</th>
                            </tr>
                        </thead>
                        <tbody>
<?php foreach ($order['items'] as $item):
    $iid    = $item['order_item_id'];
    $iName  = htmlspecialchars($item['item_name'] ?? '—');
    $iStatus = $item['status'] ?? 'Pending';
    $iSc    = $STATUS_PHP[$iStatus] ?? $STATUS_PHP['Pending'];
?>
                            <tr id="irow_<?= $iid ?>">
                                <td><?= $iName ?></td>
                                <td>x<?= $item['quantity'] ?></td>
                                <td>RM <?= number_format((float)$item['subtotal'],2) ?></td>
                                <td>
                                    <select class="status-select" id="isel_<?= $iid ?>"
                                            onchange="updateItemStatus(<?= $iid ?>, <?= $oid ?>, this.value)">
                                        <option value="Pending"     <?= $iStatus==='Pending'     ?'selected':'' ?>>⚠ Pending</option>
                                        <option value="In Progress" <?= $iStatus==='In Progress' ?'selected':'' ?>>🍳 In Progress</option>
                                        <option value="Ready"       <?= $iStatus==='Ready'       ?'selected':'' ?>>✅ Ready</option>
                                        <option value="Completed"   <?= $iStatus==='Completed'   ?'selected':'' ?>>✔ Completed</option>
                                    </select>
                                </td>
                            </tr>
<?php endforeach; ?>
                        </tbody>
                    </table>

                    <div class="order-footer">
                        <span class="order-total">Total: RM <?= $oTotal ?></span>
                        <?php if ($order['address']): ?>
                            <span style="font-size:0.8rem;color:#888;">📍 <?= htmlspecialchars($order['address']) ?></span>
                        <?php endif; ?>
                        <?php if ($oStatus !== 'Completed'): ?>
                        <button class="btn-complete" id="completebtn_<?= $oid ?>"
                                onclick="markCompleted(<?= $oid ?>)">
                            ✔ Mark All Completed
                        </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
<?php endforeach; ?>
<?php endif; ?>
        </div><!-- /#ordersContainer -->
    </div><!-- /.main-content -->

    <script src="js/admin_validation.js"></script>
    <script>
        requireAuth();

        // ── Status config ──────────────────────────────────────
        const STATUS_CONFIG = {
            'Pending':     { badge:'badge-pending',    icon:'⚠' },
            'In Progress': { badge:'badge-inprogress', icon:'🍳' },
            'Ready':       { badge:'badge-ready',      icon:'✅' },
            'Completed':   { badge:'badge-completed',  icon:'✔' },
        };

        // ── Toast ──────────────────────────────────────────────
        function showToast(msg, isErr) {
            var t = document.getElementById('ros-toast');
            if (!t) {
                t = document.createElement('div');
                t.id = 'ros-toast';
                Object.assign(t.style, {
                    position:'fixed', bottom:'28px', right:'28px',
                    padding:'12px 22px', borderRadius:'8px',
                    fontFamily:"'Poppins',sans-serif", fontWeight:'600', fontSize:'0.9rem',
                    boxShadow:'0 4px 16px rgba(0,0,0,0.22)', zIndex:'9999', transition:'opacity 0.35s'
                });
                document.body.appendChild(t);
            }
            t.style.background = isErr ? '#5E2A25' : 'var(--text-brown)';
            t.style.color = '#fff';
            t.innerText = msg;
            t.style.opacity = '1';
            clearTimeout(window._toastTimer);
            window._toastTimer = setTimeout(() => t.style.opacity='0', 2800);
        }

        // ── Date / Status filter (page reload) ────────────────
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

        // ── Search (client-side card hide/show) ───────────────
        function searchCards() {
            var q = document.getElementById('searchInput').value.toLowerCase();
            document.querySelectorAll('.order-card').forEach(function(card) {
                card.style.display = card.innerText.toLowerCase().includes(q) ? '' : 'none';
            });
        }

        // ── Collapse / Expand card ────────────────────────────
        function toggleCard(oid) {
            var body    = document.getElementById('body_'+oid);
            var chevron = document.getElementById('chevron_'+oid);
            var isOpen  = !body.classList.contains('collapsed');
            body.classList.toggle('collapsed', isOpen);
            chevron.classList.toggle('open', !isOpen);
        }

        // ── Update individual item status ─────────────────────
        function updateItemStatus(itemId, orderId, newStatus) {
            var fd = new FormData();
            fd.append('item_id', itemId);
            fd.append('status', newStatus);

            fetch('manage_orders.php?ajax=update_item', { method:'POST', body:fd })
                .then(r => r.json())
                .then(function(res) {
                    if (!res.success) { showToast('⚠ '+(res.error||'Update failed.'), true); return; }

                    // Update the overall order badge
                    var badge = document.getElementById('badge_'+orderId);
                    var card  = document.getElementById('card_'+orderId);
                    var sc    = STATUS_CONFIG[res.overall_status] || STATUS_CONFIG['Pending'];

                    if (badge) {
                        badge.className = 'status-badge '+sc.badge;
                        badge.innerText = sc.icon+' '+res.overall_status;
                    }

                    // Update card border class
                    if (card) {
                        card.className = card.className.replace(/status-\S+/, 'status-'+res.overall_status.replace(' ','_'));
                    }

                    // Show/hide "Mark Completed" button
                    var cbtn = document.getElementById('completebtn_'+orderId);
                    if (cbtn) cbtn.style.display = res.overall_status === 'Completed' ? 'none' : '';

                    showToast('✅ Status updated: '+newStatus);
                })
                .catch(() => showToast('⚠ Network error.', true));
        }

        // ── Mark entire order as Completed ────────────────────
        function markCompleted(orderId) {
            if (!confirm('Mark ALL items in Order #'+orderId+' as Completed?')) return;

            var fd = new FormData();
            fd.append('order_id', orderId);

            fetch('manage_orders.php?ajax=complete_order', { method:'POST', body:fd })
                .then(r => r.json())
                .then(function(res) {
                    if (!res.success) { showToast('⚠ Failed.', true); return; }

                    // Update all selects in this card
                    var card = document.getElementById('card_'+orderId);
                    if (card) {
                        card.querySelectorAll('.status-select').forEach(s => s.value = 'Completed');
                        var badge = document.getElementById('badge_'+orderId);
                        if (badge) {
                            badge.className = 'status-badge badge-completed';
                            badge.innerText = '✔ Completed';
                        }
                        card.className = card.className.replace(/status-\S+/, 'status-Completed');
                    }

                    var cbtn = document.getElementById('completebtn_'+orderId);
                    if (cbtn) cbtn.style.display = 'none';

                    showToast('✔ Order #'+orderId+' marked as Completed!');
                })
                .catch(() => showToast('⚠ Network error.', true));
        }
    </script>
</body>
</html>