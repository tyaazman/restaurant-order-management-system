<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Menu — Restaurant</title>
    <link rel="stylesheet" href="style.css">
    <style>
        /* ── Modal ── */
        .modal-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.55);
            z-index: 1000;
            align-items: center;
            justify-content: center;
            backdrop-filter: blur(3px);
        }
        .modal-overlay.active { display: flex; }
        .modal-box {
            background: var(--white);
            border-radius: 12px;
            padding: 30px 35px;
            width: 460px;
            max-width: 95vw;
            box-shadow: 0 20px 60px rgba(0,0,0,0.25);
            animation: modalIn 0.25s ease;
            position: relative;
        }
        @keyframes modalIn {
            from { transform: translateY(-18px); opacity: 0; }
            to   { transform: translateY(0);     opacity: 1; }
        }
        .modal-box h3 {
            margin: 0 0 18px 0;
            color: var(--text-brown);
            font-size: 1.1rem;
            border-bottom: 2px solid var(--bg-cream);
            padding-bottom: 12px;
        }
        .modal-close {
            position: absolute; top:16px; right:18px;
            background: none; border: none;
            font-size: 1.4rem; color: #bbb; cursor: pointer; padding: 0; line-height: 1;
        }
        .modal-close:hover { color: var(--danger-red); opacity: 1; }
        .modal-footer { display: flex; gap: 10px; justify-content: flex-end; margin-top: 20px; }
        .btn-cancel { background-color: #ccc; color: var(--text-brown); }
        .btn-cancel:hover { background-color: #bbb; opacity: 1; }

        /* ── Add form ── */
        #addForm { display: flex; gap: 14px; align-items: flex-end; flex-wrap: wrap; }
        #addForm > div { flex-grow: 1; min-width: 140px; }

        /* ── Category section headers ── */
        .cat-section-header {
            background: var(--bg-cream);
            padding: 6px 16px;
            font-size: 0.7rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            color: var(--accent-orange);
            border-top: 2px solid var(--text-brown);
        }
        .cat-section-header:first-child { border-top: none; }

        .cat-group-header {
            background: #f5f0eb;
        }
        .cat-group-header td {
            padding: 8px 12px;
            font-weight: 700;
            font-size: 0.85rem;
            color: var(--text-brown);
            border-bottom: 1px solid #e0d5c5;
            letter-spacing: 0.03em;
        }
        .cat-group-icon { margin-right: 6px; }

        /* Action buttons */
        .btn-edit   { padding: 4px 11px; background-color: var(--text-brown); font-size: 0.8rem; }
        .btn-delete { padding: 4px 11px; background-color: var(--danger-red); font-size: 0.8rem; margin-left:4px; }

        /* Filter bar */
        .filter-bar { display:flex; gap:10px; align-items:center; flex-wrap:wrap; margin-bottom:16px; }
        .filter-bar select { width:auto; margin:0; }
        .filter-bar input  { width:auto; min-width:180px; margin:0; }

        /* Item count badge */
        .item-count { font-size:0.82rem; color:var(--accent-orange); font-weight:600; margin-left:8px; }

        /* Price cell */
        td.price-cell { font-weight:600; color:var(--text-brown); }
    </style>
</head>
<body class="staff-portal">

    <div class="sidebar">
        <h2>Restaurant</h2>
        <a href="staff_dashboard.php">Dashboard</a>
        <a href="manage_orders.php">Manage Orders</a>
        <a href="manage_menu.php" style="background-color: var(--accent-orange); color: white;">Manage Menu</a>
        <a href="login.php" style="margin-top: 50px; color: var(--bg-cream);">Logout</a>
    </div>

    <div class="main-content">
        <h1>Manage Menu</h1>

        <!-- ── Add New Item ── -->
        <div class="card" style="margin-bottom: 26px;">
            <h3 style="margin-top: 0;">➕ Add New Food Item</h3>
            <form id="addForm" onsubmit="return false;">
                <div style="flex-grow: 2;">
                    <label for="food_name">Food Name</label>
                    <input type="text" id="food_name" placeholder="e.g. Roti Sardine">
                </div>
                <div>
                    <label for="price">Price (RM)</label>
                    <input type="text" id="price" placeholder="e.g. 6.00" style="min-width:100px;">
                </div>
                <div>
                    <label for="category">Category</label>
                    <select id="category" style="margin:8px 0 15px;">
                        <!-- Populated by JS -->
                    </select>
                </div>
                <button type="button" style="margin-bottom:15px;" onclick="addItem()">Add to Menu</button>
            </form>
        </div>

        <!-- ── Filter Bar ── -->
        <div class="card" style="padding: 14px 20px; margin-bottom: 6px;">
            <div class="filter-bar">
                <strong>Filter:</strong>
                <select id="catFilter" onchange="renderMenu()">
                    <option value="">All Categories</option>
                </select>
                <input type="text" id="searchFilter" placeholder="🔍 Search item name…" oninput="renderMenu()">
                <span class="item-count" id="itemCount"></span>
            </div>
        </div>

        <!-- ── Menu Table ── -->
        <div class="card" style="padding:0; overflow:hidden;">
            <table id="menuTable" style="margin-top:0;">
                <thead>
                    <tr>
                        <th style="width:50px;">ID</th>
                        <th>Food Name</th>
                        <th style="width:130px;">Category</th>
                        <th style="width:120px;">Price (RM)</th>
                        <th style="width:160px;">Actions</th>
                    </tr>
                </thead>
                <tbody id="menuBody">
                    <!-- Rendered by JS -->
                </tbody>
            </table>
        </div>
    </div>

    <!-- ════ EDIT MODAL ════ -->
    <div class="modal-overlay" id="editModal" onclick="closeModalOnOverlay(event)">
        <div class="modal-box">
            <button class="modal-close" onclick="closeEditModal()">✕</button>
            <h3>✏️ Edit Food Item <span id="modalItemId" style="color:var(--accent-orange);"></span></h3>

            <input type="hidden" id="modal_edit_id">

            <label for="modal_food_name">Food Name</label>
            <input type="text" id="modal_food_name" placeholder="e.g. Roti Kosong">

            <label for="modal_price">Price (RM)</label>
            <input type="text" id="modal_price" placeholder="e.g. 1.50">

            <label for="modal_category">Category</label>
            <select id="modal_category">
                <!-- Populated by JS -->
            </select>

            <div class="modal-footer">
                <button class="btn-cancel" onclick="closeEditModal()">Cancel</button>
                <button onclick="saveEdit()">💾 Save Changes</button>
            </div>
        </div>
    </div>

    <script src="js/data.js"></script>
    <script>
        let menu = ROS.getMenu();

        // ── Build category options ──────────────────
        const catIds = ROS.CATEGORIES.map(c => c.id);
        function buildCatOptions(selectId) {
            const el = document.getElementById(selectId);
            if (!el) return;
            el.innerHTML = ROS.CATEGORIES.map(c =>
                `<option value="${c.id}">${c.section} › ${c.label}</option>`
            ).join('');
        }
        buildCatOptions('category');
        buildCatOptions('modal_category');

        // Populate filter dropdown
        (function buildFilter() {
            const f = document.getElementById('catFilter');
            ROS.CATEGORIES.forEach(c => {
                const o = document.createElement('option');
                o.value = c.id; o.innerText = c.section + ' › ' + c.label;
                f.appendChild(o);
            });
        })();

        // ── Render Table ────────────────────────────
        function renderMenu() {
            const catF    = document.getElementById('catFilter').value;
            const search  = document.getElementById('searchFilter').value.toLowerCase();

            let filtered = menu;
            if (catF)   filtered = filtered.filter(i => i.category === catF);
            if (search) filtered = filtered.filter(i => i.name.toLowerCase().includes(search));

            document.getElementById('itemCount').innerText =
                '(' + filtered.length + ' item' + (filtered.length !== 1 ? 's' : '') + ')';

            const tbody = document.getElementById('menuBody');

            if (filtered.length === 0) {
                tbody.innerHTML = '<tr><td colspan="5" style="text-align:center;color:#aaa;padding:30px;">No items match your filter.</td></tr>';
                return;
            }

            // Group by category (maintaining CATEGORIES order)
            const groups = {};
            ROS.CATEGORIES.forEach(c => { groups[c.id] = []; });
            filtered.forEach(item => {
                if (!groups[item.category]) groups[item.category] = [];
                groups[item.category].push(item);
            });

            let html = '';
            let lastSection = null;

            ROS.CATEGORIES.forEach(cat => {
                const items = groups[cat.id];
                if (!items || items.length === 0) return;

                // Section header (e.g. "SIGNATURE")
                if (cat.section !== lastSection) {
                    html += `<tr><td colspan="5" class="cat-section-header">${cat.section}</td></tr>`;
                    lastSection = cat.section;
                }

                // Category sub-header (e.g. "Sup ZZ")
                html += `<tr class="cat-group-header">
                    <td colspan="5">
                        <span class="cat-group-icon">▸</span>${cat.label}
                        <span style="font-weight:400; font-size:0.78rem; color:#888; margin-left:8px;">(${items.length} items)</span>
                    </td>
                </tr>`;

                // Item rows
                items.forEach(item => {
                    html += `
                    <tr id="row_${item.id}">
                        <td style="color:#aaa; font-size:0.8rem;">#${item.id}</td>
                        <td>${item.name}</td>
                        <td style="font-size:0.8rem; color:#888;">${item.category}</td>
                        <td class="price-cell">RM ${item.price.toFixed(2)}</td>
                        <td>
                            <button class="btn-edit"   onclick="openEditModal(${item.id})">✏️ Edit</button>
                            <button class="btn-delete" onclick="deleteItem(${item.id})">🗑</button>
                        </td>
                    </tr>`;
                });
            });

            tbody.innerHTML = html;
        }

        // ── Add ─────────────────────────────────────
        function addItem() {
            const name     = document.getElementById('food_name').value.trim();
            const price    = parseFloat(document.getElementById('price').value);
            const category = document.getElementById('category').value;

            if (!name)            { ROS.showToast('⚠ Please enter a food name.'); return; }
            if (isNaN(price) || price <= 0) { ROS.showToast('⚠ Please enter a valid price.'); return; }

            const maxId = menu.reduce((m, i) => Math.max(m, i.id), 0);
            menu.push({ id: maxId + 1, name, price, category });
            ROS.saveMenu(menu);
            renderMenu();
            document.getElementById('food_name').value = '';
            document.getElementById('price').value    = '';
            ROS.showToast('✅ "' + name + '" added to menu!');
        }

        // ── Edit Modal ───────────────────────────────
        function openEditModal(id) {
            const item = menu.find(i => i.id === id);
            if (!item) return;
            document.getElementById('modal_food_name').value = item.name;
            document.getElementById('modal_price').value     = item.price.toFixed(2);
            document.getElementById('modal_category').value  = item.category || 'Lain-Lain';
            document.getElementById('modal_edit_id').value   = id;
            document.getElementById('modalItemId').innerText = '(ID: ' + id + ')';
            document.getElementById('editModal').classList.add('active');
        }

        function closeEditModal() {
            document.getElementById('editModal').classList.remove('active');
        }

        function closeModalOnOverlay(e) {
            if (e.target === document.getElementById('editModal')) closeEditModal();
        }

        function saveEdit() {
            const id       = parseInt(document.getElementById('modal_edit_id').value);
            const name     = document.getElementById('modal_food_name').value.trim();
            const price    = parseFloat(document.getElementById('modal_price').value);
            const category = document.getElementById('modal_category').value;

            if (!name)            { ROS.showToast('⚠ Please enter a food name.'); return; }
            if (isNaN(price) || price <= 0) { ROS.showToast('⚠ Please enter a valid price.'); return; }

            const item = menu.find(i => i.id === id);
            if (item) { item.name = name; item.price = price; item.category = category; }
            ROS.saveMenu(menu);
            renderMenu();
            closeEditModal();
            ROS.showToast('✅ "' + name + '" updated!');
        }

        // ── Delete ───────────────────────────────────
        function deleteItem(id) {
            const item = menu.find(i => i.id === id);
            if (!item) return;
            if (!confirm('Delete "' + item.name + '" from the menu?')) return;
            menu = menu.filter(i => i.id !== id);
            ROS.saveMenu(menu);
            renderMenu();
            ROS.showToast('🗑 "' + item.name + '" removed.');
        }

        // Enter key on add form
        document.getElementById('addForm').addEventListener('keydown', e => {
            if (e.key === 'Enter') addItem();
        });

        // ── Init ──────────────────────────────────────
        renderMenu();
    </script>
</body>
</html>