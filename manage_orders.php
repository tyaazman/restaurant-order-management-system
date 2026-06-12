<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Orders — Restaurant</title>
    <link rel="stylesheet" href="style.css">
    <style>
        /* ── Page header ── */
        .page-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 2px solid var(--text-brown);
            padding-bottom: 12px;
            margin-bottom: 24px;
            flex-wrap: wrap;
            gap: 10px;
        }
        .page-header h1 { margin: 0; }
        .page-header .order-count {
            font-size: 0.92rem;
            color: var(--accent-orange);
            font-weight: 600;
        }

        /* ── Filter bar ── */
        .filter-bar {
            display: flex;
            gap: 12px;
            align-items: center;
            flex-wrap: wrap;
            margin-bottom: 22px;
        }
        .filter-bar label { font-weight: 600; white-space: nowrap; }
        .filter-bar input,
        .filter-bar select { margin: 0; width: auto; min-width: 160px; }

        /* ── Back link ── */
        .back-link {
            display: inline-block;
            margin-bottom: 14px;
            font-size: 0.88rem;
            color: var(--text-brown);
            text-decoration: none;
            font-weight: 600;
            opacity: 0.7;
            transition: opacity 0.2s;
        }
        .back-link:hover { opacity: 1; }

        /* ── Order Cards ── */
        .order-card {
            background: var(--white);
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.07);
            margin-bottom: 20px;
            overflow: hidden;
            transition: box-shadow 0.2s;
        }
        .order-card:hover { box-shadow: 0 6px 22px rgba(0,0,0,0.12); }

        /* Card header */
        .order-card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 14px 20px;
            background: var(--text-brown);
            color: var(--bg-cream);
            flex-wrap: wrap;
            gap: 8px;
        }
        .order-card-header .meta { display: flex; align-items: center; gap: 16px; flex-wrap: wrap; }
        .order-card-header h3   { margin: 0; font-size: 1rem; color: var(--bg-cream); }
        .order-card-header .time { font-size: 0.8rem; opacity: 0.7; }
        .order-card-header .actions { display: flex; align-items: center; gap: 10px; }

        /* Overall badge */
        .overall-badge {
            padding: 4px 14px;
            border-radius: 20px;
            font-size: 0.78rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            white-space: nowrap;
        }
        .badge-pending    { background: #5E2A25; color: #ffeaea; }
        .badge-inprogress { background: #7a4d00; color: #fff3cd; }
        .badge-ready      { background: #a85530; color: #fff;    }
        .badge-completed  { background: #2d5a3d; color: #d4edda; }

        /* Collapse toggle */
        .toggle-btn {
            background: none;
            border: 1px solid rgba(225,211,169,0.5);
            color: var(--bg-cream);
            padding: 4px 12px;
            border-radius: 6px;
            font-size: 0.78rem;
            cursor: pointer;
            font-family: 'Poppins', sans-serif;
            white-space: nowrap;
        }
        .toggle-btn:hover { background: rgba(255,255,255,0.1); opacity: 1; }

        /* Card body */
        .order-card-body { padding: 0 20px 16px 20px; }

        /* Bulk bar */
        .bulk-bar {
            display: flex;
            gap: 10px;
            align-items: center;
            padding: 12px 0;
            border-bottom: 1px solid #eee;
            margin-bottom: 4px;
            flex-wrap: wrap;
        }
        .bulk-bar label  { font-weight: 600; font-size: 0.87rem; color: var(--text-brown); }
        .bulk-bar select { width: auto; min-width: 175px; margin: 0; }

        .btn-apply {
            background: var(--accent-orange);
            color: #fff;
            border: none;
            padding: 7px 16px;
            border-radius: 6px;
            font-family: 'Poppins', sans-serif;
            font-weight: 600;
            font-size: 0.83rem;
            cursor: pointer;
            transition: opacity 0.2s, transform 0.15s;
        }
        .btn-apply:hover { opacity: 0.88; transform: translateY(-1px); }

        .select-all-label { margin-left: auto; cursor: pointer; font-size: 0.85rem; display: flex; align-items: center; gap: 6px; }

        /* Items table */
        .item-table { margin-top: 0; }
        .item-table thead th { background: #f5f0eb; color: var(--text-brown); font-size: 0.83rem; }
        .item-table tbody tr:last-child td { border-bottom: none; }
        .item-table td { vertical-align: middle; font-size: 0.88rem; }

        /* Status pills */
        .pill {
            display: inline-block;
            padding: 3px 12px;
            border-radius: 20px;
            font-size: 0.76rem;
            font-weight: 700;
        }
        .pill-pending    { background: #fde8e8; color: #5E2A25; }
        .pill-inprogress { background: #fff3cd; color: #7a4d00; }
        .pill-ready      { background: #ffe0cc; color: #a85530; }
        .pill-completed  { background: #d4edda; color: #2d5a3d; }

        /* Per-item dropdown */
        .item-sel { width: auto; min-width: 130px; margin: 0; font-size: 0.82rem; padding: 5px 8px; }

        /* Highlight animation (from dashboard link) */
        @keyframes pulseHighlight {
            0%, 100% { box-shadow: 0 4px 12px rgba(0,0,0,0.07); }
            40%       { box-shadow: 0 0 0 4px var(--accent-orange), 0 6px 24px rgba(168,85,48,0.45); }
        }
        .order-card.highlighted { animation: pulseHighlight 1s ease-in-out 3; }

        /* Wrapper transition */
        .order-items-wrapper { overflow: hidden; }

        /* Empty state */
        .no-orders { text-align: center; padding: 50px; color: #aaa; font-size: 1rem; }
    </style>
</head>
<body class="staff-portal">

    <div class="sidebar">
        <h2>Restaurant</h2>
        <a href="staff_dashboard.php">Dashboard</a>
        <a href="manage_orders.php" style="background-color: var(--accent-orange); color: white;">Manage Orders</a>
        <a href="manage_menu.php">Manage Menu</a>
        <a href="login.php" style="margin-top: 50px; color: var(--bg-cream);">Logout</a>
    </div>

    <div class="main-content">

        <!-- Back link (shown only when coming from a deep link) -->
        <a href="staff_dashboard.php" class="back-link" id="backLink" style="display:none;">
            ← Back to Dashboard
        </a>

        <div class="page-header">
            <h1>Manage Orders</h1>
            <span class="order-count" id="orderCount"></span>
        </div>

        <!-- Filter Bar -->
        <div class="filter-bar">
            <label>Date:</label>
            <input type="date" id="dateFilter" value="2026-06-12" style="width:auto;margin:0;"
                   onchange="loadOrders()">

            <label>Status:</label>
            <select id="statusFilter" onchange="applyVisibilityFilter()">
                <option value="">All Statuses</option>
                <option value="pending">Pending</option>
                <option value="inprogress">In Progress</option>
                <option value="ready">Ready</option>
                <option value="completed">Completed</option>
            </select>

            <input type="text" id="searchInput" placeholder="🔍 Search order / customer…"
                   style="min-width:200px;" oninput="applyVisibilityFilter()">
        </div>

        <!-- Order Cards Container -->
        <div id="ordersContainer">
            <!-- Dynamically rendered -->
        </div>

        <p class="no-orders" id="noResults" style="display:none;">No orders match your filter.</p>

    </div>

    <script src="js/data.js"></script>
    <script>
        // ── State ────────────────────────────────────────
        let allOrders = {};
        let currentDate = '2026-06-12';

        // ── Load & Render Orders for a Date ──────────────
        function loadOrders() {
            allOrders  = ROS.getOrders();
            currentDate = document.getElementById('dateFilter').value;
            const orders = allOrders[currentDate] || [];

            const container = document.getElementById('ordersContainer');

            if (orders.length === 0) {
                container.innerHTML = '';
                document.getElementById('noResults').style.display = 'block';
                document.getElementById('orderCount').innerText = '0 orders';
                return;
            }

            document.getElementById('noResults').style.display = 'none';

            container.innerHTML = orders.map(order => {
                const status  = ROS.getOverallStatus(order.items);
                const cfg     = ROS.STATUS[status];
                const isCompleted = (status === 'completed');

                // Build item rows
                const itemRows = order.items.map(item => {
                    const sCfg = ROS.STATUS[item.status];
                    const domId = `pill_${order.id}_${item.id}`;
                    const selId = `sel_${order.id}_${item.id}`;

                    if (isCompleted) {
                        // Read-only for completed orders
                        return `
                        <tr>
                            <td>—</td>
                            <td>${item.name}</td>
                            <td>${item.qty}</td>
                            <td><span class="pill ${sCfg.pillClass}" id="${domId}">${sCfg.pillLabel}</span></td>
                            <td style="color:#aaa; font-size:0.8rem;">—</td>
                        </tr>`;
                    }

                    return `
                    <tr data-item-id="${item.id}">
                        <td style="text-align:center;">
                            <input type="checkbox" class="item-check" data-order="${order.id}">
                        </td>
                        <td>${item.name}</td>
                        <td>${item.qty}</td>
                        <td><span class="pill ${sCfg.pillClass}" id="${domId}">${sCfg.pillLabel}</span></td>
                        <td>
                            <select class="item-sel" id="${selId}"
                                    onchange="updateItem('${item.id}', ${order.id}, this.value)">
                                <option value="pending"    ${item.status==='pending'    ?'selected':''}>Pending</option>
                                <option value="inprogress" ${item.status==='inprogress' ?'selected':''}>In Progress</option>
                                <option value="ready"      ${item.status==='ready'      ?'selected':''}>Ready</option>
                                <option value="completed"  ${item.status==='completed'  ?'selected':''}>Completed</option>
                            </select>
                        </td>
                    </tr>`;
                }).join('');

                // Bulk bar (hidden for completed orders)
                const bulkBar = isCompleted ? '' : `
                <div class="bulk-bar">
                    <label>Update Selected:</label>
                    <select class="bulkSel" id="bulk_${order.id}">
                        <option value="">Select Status…</option>
                        <option value="inprogress">In Progress</option>
                        <option value="ready">Ready</option>
                        <option value="completed">Completed</option>
                    </select>
                    <button class="btn-apply" onclick="applyBulk(${order.id})">Apply to Selected</button>
                    <label class="select-all-label">
                        <input type="checkbox" id="selAll_${order.id}"
                               onchange="toggleSelectAll(${order.id})"> Select All
                    </label>
                </div>`;

                const checkboxHeader = isCompleted
                    ? '<th style="width:44px;">—</th>'
                    : '<th style="width:44px; text-align:center;"></th>';

                const quickHeader = isCompleted
                    ? ''
                    : '<th style="width:160px;">Quick Update</th>';

                return `
                <div class="order-card" id="order_${order.id}"
                     data-status="${status}"
                     data-customer="${order.customer.toLowerCase()}"
                     data-orderid="${order.id}">

                    <div class="order-card-header">
                        <div class="meta">
                            <h3>#${order.id}</h3>
                            <span>${order.customer}</span>
                            <span class="time">🕐 ${order.time}</span>
                        </div>
                        <div class="actions">
                            <span class="overall-badge ${cfg.badgeClass}" id="badge_${order.id}">
                                ${cfg.badgeLabel}
                            </span>
                            <button class="toggle-btn" id="toggleBtn_${order.id}"
                                    onclick="toggleCard(${order.id})">▼ Hide</button>
                        </div>
                    </div>

                    <div class="order-items-wrapper" id="wrapper_${order.id}">
                        <div class="order-card-body">
                            ${bulkBar}
                            <table class="item-table">
                                <thead>
                                    <tr>
                                        ${checkboxHeader}
                                        <th>Menu Item</th>
                                        <th style="width:60px;">Qty</th>
                                        <th style="width:120px;">Status</th>
                                        ${quickHeader}
                                    </tr>
                                </thead>
                                <tbody id="items_${order.id}">
                                    ${itemRows}
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>`;
            }).join('');

            applyVisibilityFilter();
        }

        // ── Update single item status ─────────────────────
        function updateItem(itemId, orderId, newStatus) {
            const order = (allOrders[currentDate] || []).find(o => o.id === orderId);
            if (!order) return;
            const item  = order.items.find(i => i.id === itemId);
            if (!item) return;
            item.status = newStatus;
            ROS.saveOrders(allOrders);
            refreshBadge(orderId);
        }

        // ── Re-compute and update order badge ────────────
        function refreshBadge(orderId) {
            const order   = (allOrders[currentDate] || []).find(o => o.id === orderId);
            if (!order) return;
            const status  = ROS.getOverallStatus(order.items);
            const cfg     = ROS.STATUS[status];
            const badge   = document.getElementById('badge_' + orderId);
            const card    = document.getElementById('order_' + orderId);
            if (badge) { badge.className = 'overall-badge ' + cfg.badgeClass; badge.innerText = cfg.badgeLabel; }
            if (card)  { card.dataset.status = status; }

            // Also refresh pills (in case we came from bulk)
            const selects = document.querySelectorAll(`#items_${orderId} .item-sel`);
            selects.forEach(sel => {
                const row    = sel.closest('tr');
                const iId    = row ? row.dataset.itemId : null;
                if (!iId) return;
                const it     = order.items.find(x => x.id === iId);
                if (!it) return;
                const pill   = document.getElementById(`pill_${orderId}_${iId}`);
                const sCfg   = ROS.STATUS[it.status];
                if (pill) { pill.className = 'pill ' + sCfg.pillClass; pill.innerText = sCfg.pillLabel; }
            });
        }

        // ── Bulk apply ───────────────────────────────────
        function applyBulk(orderId) {
            const newStatus = document.getElementById('bulk_' + orderId).value;
            if (!newStatus) { ROS.showToast('Please select a status.'); return; }

            const checked = document.querySelectorAll(
                `#items_${orderId} .item-check:checked`
            );
            if (checked.length === 0) { ROS.showToast('No items selected.'); return; }

            const order = (allOrders[currentDate] || []).find(o => o.id === orderId);
            if (!order) return;

            checked.forEach(chk => {
                const row    = chk.closest('tr');
                const iId    = row.dataset.itemId;
                const item   = order.items.find(i => i.id === iId);
                if (item) item.status = newStatus;
                const sel    = document.getElementById(`sel_${orderId}_${iId}`);
                if (sel) sel.value = newStatus;
                const pill   = document.getElementById(`pill_${orderId}_${iId}`);
                const sCfg   = ROS.STATUS[newStatus];
                if (pill) { pill.className = 'pill ' + sCfg.pillClass; pill.innerText = sCfg.pillLabel; }
                chk.checked = false;
            });

            ROS.saveOrders(allOrders);
            refreshBadge(orderId);

            const saEl = document.getElementById('selAll_' + orderId);
            if (saEl) saEl.checked = false;
            ROS.showToast(`Updated ${checked.length} item(s) → ${ROS.STATUS[newStatus].pillLabel}`);
        }

        // ── Select-all ───────────────────────────────────
        function toggleSelectAll(orderId) {
            const master = document.getElementById('selAll_' + orderId);
            document.querySelectorAll(`#items_${orderId} .item-check`).forEach(c => {
                c.checked = master.checked;
            });
        }

        // ── Collapse / Expand ─────────────────────────────
        function toggleCard(orderId) {
            const w   = document.getElementById('wrapper_' + orderId);
            const btn = document.getElementById('toggleBtn_' + orderId);
            if (!w) return;
            if (w.style.display === 'none') { w.style.display = ''; btn.innerText = '▼ Hide'; }
            else                            { w.style.display = 'none'; btn.innerText = '▶ Show'; }
        }

        // ── Search / Status Filter ────────────────────────
        function applyVisibilityFilter() {
            const search    = document.getElementById('searchInput').value.toLowerCase();
            const statusF   = document.getElementById('statusFilter').value;
            const cards     = document.querySelectorAll('.order-card');
            let visible     = 0;

            cards.forEach(card => {
                const cs = card.dataset.status   || '';
                const cc = card.dataset.customer || '';
                const ci = card.dataset.orderid  || '';

                const matchStatus = !statusF || cs === statusF;
                const matchSearch = !search  || cc.includes(search) || ci.includes(search);

                if (matchStatus && matchSearch) {
                    card.style.display = '';
                    visible++;
                } else {
                    card.style.display = 'none';
                }
            });

            document.getElementById('noResults').style.display = visible === 0 ? 'block' : 'none';
            document.getElementById('orderCount').innerText =
                visible + ' order' + (visible !== 1 ? 's' : '') + ' shown';
        }

        // ── Highlight a specific order (from URL param) ──
        function highlightOrder(orderId) {
            const card = document.getElementById('order_' + orderId);
            if (!card) return;
            // Scroll into view
            setTimeout(() => {
                card.scrollIntoView({ behavior: 'smooth', block: 'start' });
                card.classList.add('highlighted');
                setTimeout(() => card.classList.remove('highlighted'), 3500);
            }, 200);
        }

        // ─────────────────────────────────────────────────
        //  INIT
        // ─────────────────────────────────────────────────
        (function init() {
            const paramDate  = ROS.getParam('date');
            const paramOrder = ROS.getParam('order');

            // Set date filter from URL param
            if (paramDate) {
                document.getElementById('dateFilter').value = paramDate;
            }

            // Show back link if navigated from dashboard
            if (paramOrder) {
                document.getElementById('backLink').style.display = 'inline-block';
            }

            loadOrders();

            // Highlight & scroll to specific order
            if (paramOrder) {
                highlightOrder(parseInt(paramOrder));
            }
        })();
    </script>

</body>
</html>