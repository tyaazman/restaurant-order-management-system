<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Dashboard — Restaurant</title>
    <link rel="stylesheet" href="css/admin.css">
    <style>
        /* ── Stat Grid (5 boxes) ── */
        .grid {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 16px;
            margin-bottom: 30px;
        }
        .stat-box { text-align: center; padding: 24px 12px; }
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
        /* Colour-coded stat numbers */
        .stat-box.box-pending    p { color: #5E2A25; }
        .stat-box.box-inprogress p { color: #a85530; }
        .stat-box.box-ready      p { color: #c47a2b; }
        .stat-box.box-completed  p { color: #2d7a4f; }

        /* ── Date Filter ── */
        .date-filter { margin-bottom: 20px; display: flex; align-items: center; gap: 10px; }

        /* ── Status cell container ── */
        .status-cell { display: flex; align-items: center; }

        /* ════ STATUS BARS ════ */
        /* Shared bar style */
        .status-bar {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 6px 14px 6px 10px;
            border-radius: 6px;
            font-weight: 700;
            font-size: 0.82rem;
            text-decoration: none;
            border-left: 4px solid;
            transition: filter 0.2s, transform 0.15s;
            cursor: pointer;
            white-space: nowrap;
        }
        .status-bar:hover { filter: brightness(0.93); transform: translateX(2px); }

        /* Pending bar */
        .status-bar.bar-pending {
            background: rgba(94, 42, 37, 0.09);
            color: #5E2A25;
            border-color: #5E2A25;
        }
        /* Pulse dot for pending */
        .status-bar.bar-pending::before {
            content: '';
            width: 8px; height: 8px;
            border-radius: 50%;
            background: #5E2A25;
            animation: pulseDot 1.4s ease-in-out infinite;
            flex-shrink: 0;
        }

        /* In Progress bar */
        .status-bar.bar-inprogress {
            background: rgba(168, 85, 48, 0.09);
            color: #a85530;
            border-color: #a85530;
        }
        /* Spinning cog for in-progress */
        .status-bar.bar-inprogress::before {
            content: '';
            width: 8px; height: 8px;
            border-radius: 50%;
            background: #a85530;
            animation: pulseDot 0.9s ease-in-out infinite;
            flex-shrink: 0;
        }

        @keyframes pulseDot {
            0%, 100% { opacity: 1; transform: scale(1);    }
            50%       { opacity: 0.4; transform: scale(1.5); }
        }

        /* Ready → action button */
        .btn-ready {
            background: linear-gradient(135deg, #e67e22, var(--accent-orange));
            color: #fff;
            border: none;
            padding: 6px 15px;
            border-radius: 20px;
            font-size: 0.82rem;
            font-weight: 600;
            cursor: pointer;
            font-family: 'Poppins', sans-serif;
            box-shadow: 0 2px 8px rgba(168,85,48,0.35);
            transition: transform 0.15s, box-shadow 0.15s;
            white-space: nowrap;
        }
        .btn-ready:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 14px rgba(168,85,48,0.5);
            opacity: 1;
        }

        /* Completed → plain text */
        .text-completed { color: #2d7a4f; font-weight: 700; font-size: 0.88rem; }

        /* Empty row */
        .no-orders { text-align:center; padding:40px; color:#aaa; }
    </style>
</head>
<body>

    <div class="sidebar">
        <h2>Restaurant</h2>
        <a href="staff_dashboard.php" style="background-color: var(--accent-orange); color: white;">Dashboard</a>
        <a href="manage_orders.php">Manage Orders</a>
        <a href="manage_menu.php">Manage Menu</a>
        <a href="login.php" style="margin-top: 50px; color: var(--bg-cream);">Logout</a>
    </div>

    <div class="main-content">
        <h1 style="border-bottom: 2px solid var(--text-brown); padding-bottom: 10px;">Staff Dashboard</h1>

        <!-- Date Filter -->
        <div class="date-filter">
            <label for="dashboardDate"><strong>Viewing Orders For:</strong></label>
            <input type="date" id="dashboardDate" style="width: auto; margin: 0;" value="2026-06-12"
                   onchange="loadDashboard(this.value)">
        </div>

        <!-- ── 5 Stat Boxes ── -->
        <div class="grid">
            <div class="card stat-box">
                <h3>Total Orders</h3>
                <p id="statTotal">—</p>
            </div>
            <div class="card stat-box box-pending">
                <h3>⚠ Pending</h3>
                <p id="statPending">—</p>
            </div>
            <div class="card stat-box box-inprogress">
                <h3>🍳 In Progress</h3>
                <p id="statInProgress">—</p>
            </div>
            <div class="card stat-box box-ready">
                <h3>✅ Ready</h3>
                <p id="statReady">—</p>
            </div>
            <div class="card stat-box box-completed">
                <h3>✔ Completed</h3>
                <p id="statCompleted">—</p>
            </div>
        </div>

        <!-- Orders Table -->
        <div class="card">
            <h3 style="margin-top:0;" id="tableTitle">Today's Order Overview</h3>
            <table>
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Customer</th>
                        <th>Items Summary</th>
                        <th>Overall Status</th>
                    </tr>
                </thead>
                <tbody id="ordersBody">
                    <!-- Dynamically rendered -->
                </tbody>
            </table>
        </div>
    </div>

    <script src="js/data.js"></script>
    <script>
        const TODAY = '2026-06-12';
        let allOrders = {};

        function loadDashboard(date) {
            allOrders = ROS.getOrders();
            const orders = allOrders[date] || [];
            const isToday = (date === TODAY);

            // Update title
            document.getElementById('tableTitle').innerText =
                isToday ? "Today's Order Overview" : "Orders for " + date;

            // ── Compute stats (each status in its own box) ──
            let total = orders.length, pending = 0, inprog = 0, ready = 0, completed = 0;
            orders.forEach(o => {
                const s = ROS.getOverallStatus(o.items);
                if      (s === 'pending')    pending++;
                else if (s === 'inprogress') inprog++;
                else if (s === 'ready')      ready++;
                else if (s === 'completed')  completed++;
            });

            document.getElementById('statTotal').innerText      = total;
            document.getElementById('statPending').innerText    = pending;
            document.getElementById('statInProgress').innerText = inprog;
            document.getElementById('statReady').innerText      = ready;
            document.getElementById('statCompleted').innerText  = completed;

            // ── Render rows ──
            const tbody = document.getElementById('ordersBody');
            if (orders.length === 0) {
                tbody.innerHTML = '<tr><td colspan="4" class="no-orders">No orders recorded for this date.</td></tr>';
                return;
            }

            tbody.innerHTML = orders.map(order => {
                const status  = ROS.getOverallStatus(order.items);
                const summary = order.items.map(i => i.qty + 'x ' + i.name).join(', ');

                let statusCell;

                if (isToday) {
                    if (status === 'pending') {
                        // Pending → bar indicator with link to manage_orders
                        statusCell = `
                            <a href="manage_orders.php?date=${date}&order=${order.id}"
                               class="status-bar bar-pending"
                               title="Click to view and update this order">
                                Pending Items
                            </a>`;
                    } else if (status === 'inprogress') {
                        // In Progress → bar indicator with link to manage_orders
                        statusCell = `
                            <a href="manage_orders.php?date=${date}&order=${order.id}"
                               class="status-bar bar-inprogress"
                               title="Click to view and update this order">
                                In Progress
                            </a>`;
                    } else if (status === 'ready') {
                        // Ready → action button
                        statusCell = `
                            <button class="btn-ready" id="readyBtn_${order.id}"
                                    onclick="markCompleted(${order.id}, '${date}')">
                                ✅ Ready — Mark Collected
                            </button>`;
                    } else {
                        // Completed → display only
                        statusCell = `<span class="text-completed">✔ Completed</span>`;
                    }
                } else {
                    // Past dates — all display only
                    statusCell = `<span class="text-completed">✔ Completed</span>`;
                }

                return `
                    <tr id="dashRow_${order.id}">
                        <td><strong>#${order.id}</strong></td>
                        <td>${order.customer}</td>
                        <td style="font-size:0.86rem; color:#555;">${summary}</td>
                        <td><div class="status-cell">${statusCell}</div></td>
                    </tr>`;
            }).join('');
        }

        function markCompleted(orderId, date) {
            allOrders = ROS.getOrders();
            const order = (allOrders[date] || []).find(o => o.id === orderId);
            if (!order) return;
            order.items.forEach(i => i.status = 'completed');
            ROS.saveOrders(allOrders);
            loadDashboard(date);
            ROS.showToast('✅ Order #' + orderId + ' marked as Completed!');
        }

        // ── Init ──
        loadDashboard(document.getElementById('dashboardDate').value);
    </script>
</body>
</html>