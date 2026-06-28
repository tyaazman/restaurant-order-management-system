<?php
session_start();

// ==========================================
//  AJAX ENDPOINT  (handles add / edit / delete via fetch)
// ==========================================
if (isset($_GET['ajax'])) {
    header('Content-Type: application/json');
    require_once 'config/db.php';

    $action = $_GET['ajax'];

    /* ── ADD ── */
    if ($action === 'add') {
        $name     = trim($_POST['name']     ?? '');
        $price    = floatval($_POST['price'] ?? 0);
        $category = trim($_POST['category'] ?? '');

        if (!$name)     { echo json_encode(['success'=>false,'error'=>'Food name is required.']);    exit; }
        if ($price <= 0){ echo json_encode(['success'=>false,'error'=>'Enter a valid price (> 0).']); exit; }
        if (!$category) { echo json_encode(['success'=>false,'error'=>'Please select a category.']); exit; }

        $stmt = $pdo->prepare("INSERT INTO menu_items (item_name, price, category_name) VALUES (?, ?, ?)");
        $stmt->execute([$name, $price, $category]);
        $id = (int)$pdo->lastInsertId();

        // Process options if any
        $options = json_decode($_POST['options_json'] ?? '[]', true);
        if (is_array($options)) {
            foreach ($options as $opt) {
                $optGroup = trim($opt['group'] ?? '');
                $optName  = trim($opt['name'] ?? '');
                $optPrice = floatval($opt['price'] ?? 0);
                if ($optGroup !== '' && $optName !== '') {
                    $stmtOpt = $pdo->prepare("INSERT INTO menu_item_options (menu_item_id, option_group, option_name, additional_price) VALUES (?, ?, ?, ?)");
                    $stmtOpt->execute([$id, $optGroup, $optName, $optPrice]);
                }
            }
        }

        // Fetch inserted options to return in response
        $stmtFetchOpt = $pdo->prepare("SELECT option_id, menu_item_id, option_group, option_name, additional_price FROM menu_item_options WHERE menu_item_id = ? ORDER BY option_id ASC");
        $stmtFetchOpt->execute([$id]);
        $addedOpts = $stmtFetchOpt->fetchAll();

        echo json_encode(['success'=>true, 'id'=>$id, 'name'=>$name, 'price'=>$price, 'category'=>$category, 'options'=>$addedOpts]);
        exit;
    }

    /* ── EDIT ── */
    if ($action === 'edit') {
        $id       = intval($_POST['id']      ?? 0);
        $name     = trim($_POST['name']      ?? '');
        $price    = floatval($_POST['price'] ?? 0);
        $category = trim($_POST['category']  ?? '');

        if (!$id || !$name || $price <= 0 || !$category) {
            echo json_encode(['success'=>false,'error'=>'Invalid data. Check all fields.']); exit;
        }

        $stmt = $pdo->prepare("UPDATE menu_items SET item_name=?, price=?, category_name=? WHERE menu_item_id=?");
        $stmt->execute([$name, $price, $category, $id]);

        echo json_encode(['success'=>true, 'name'=>$name, 'price'=>(float)$price, 'category'=>$category]);
        exit;
    }

    /* ── DELETE ── */
    if ($action === 'delete') {
        $id = intval($_POST['id'] ?? 0);
        if (!$id) { echo json_encode(['success'=>false,'error'=>'Invalid item ID.']); exit; }

        $stmt = $pdo->prepare("DELETE FROM menu_items WHERE menu_item_id=?");
        $stmt->execute([$id]);

        echo json_encode(['success'=>true]);
        exit;
    }

    echo json_encode(['success'=>false,'error'=>'Unknown action.']);
    exit;
}

// ==========================================
//  REGULAR PAGE LOAD
// ==========================================
require_once 'config/db.php';

/* Category display order (matches database category_name values) */
$CATEGORIES = [
    ['id'=>'Signature Sup',   'label'=>'Signature Sup',   'section'=>'SIGNATURE'],
    ['id'=>'Mee Rebus ZZ',    'label'=>'Mee Rebus ZZ',    'section'=>'SIGNATURE'],
    ['id'=>'Sarapan',         'label'=>'Sarapan',         'section'=>'SARAPAN'],
    ['id'=>'Roti Canai',      'label'=>'Roti Canai',      'section'=>'ROTI CANAI'],
    ['id'=>'Set Tengah Hari', 'label'=>'Set Nasi & Lauk', 'section'=>'SET TENGAH HARI'],
    ['id'=>'Menu Ikan',       'label'=>'Menu Ikan',       'section'=>'MENU IKAN'],
    ['id'=>'Ala Carte Menu',  'label'=>'Ala Carte Menu',  'section'=>'ALA CARTE'],
    ['id'=>'Western Food',    'label'=>'Western Food',    'section'=>'WESTERN'],
    ['id'=>'Goreng-Goreng',   'label'=>'Goreng-Goreng',   'section'=>'GORENG-GORENG'],
    ['id'=>'Drinks',          'label'=>'Drinks',          'section'=>'DRINKS'],
];

/* Fetch all menu items and options */
try {
    $stmt      = $pdo->query("SELECT menu_item_id AS item_id, item_name, price, category_name AS category FROM menu_items ORDER BY menu_item_id ASC");
    $menuItems = $stmt->fetchAll();

    $stmtOpt   = $pdo->query("SELECT option_id, menu_item_id, option_group, option_name, additional_price FROM menu_item_options ORDER BY option_id ASC");
    $allOptions = $stmtOpt->fetchAll();
} catch (PDOException $e) {
    $menuItems = [];
    $allOptions = [];
}

/* Group options by menu_item_id */
$optionsByItem = [];
foreach ($allOptions as $opt) {
    $optionsByItem[$opt['menu_item_id']][] = $opt;
}

/* Group by category */
$grouped = [];
foreach ($menuItems as $item) {
    $grouped[$item['category']][] = $item;
}
$totalItems = count($menuItems);

/* All unique categories found in DB (for "Other" items not in CATEGORIES list) */
$knownCatIds = array_column($CATEGORIES, 'id');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Manage restaurant food menu — add, edit and delete items">
    <title>Manage Menu — Restaurant ZZ</title>
    <link rel="stylesheet" href="css/admin.css">
    <style>
        /* ── Modal ── */
        .modal-overlay {
            display: none; position: fixed; inset: 0;
            background: rgba(0,0,0,0.55); z-index: 1000;
            align-items: center; justify-content: center;
            backdrop-filter: blur(3px);
        }
        .modal-overlay.active { display: flex; }
        .modal-box {
            background: var(--white); border-radius: 12px;
            padding: 30px 35px; width: 460px; max-width: 95vw;
            box-shadow: 0 20px 60px rgba(0,0,0,0.25);
            animation: modalIn 0.22s ease; position: relative;
        }
        @keyframes modalIn {
            from { transform: translateY(-18px); opacity: 0; }
            to   { transform: translateY(0);     opacity: 1; }
        }
        .modal-box h3 {
            margin: 0 0 18px; color: var(--text-brown);
            font-size: 1.1rem; border-bottom: 2px solid var(--bg-cream); padding-bottom: 12px;
        }
        .modal-close {
            position: absolute; top: 16px; right: 18px;
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
            background: var(--bg-cream); padding: 6px 16px;
            font-size: 0.7rem; font-weight: 700; text-transform: uppercase;
            letter-spacing: 0.1em; color: var(--accent-orange);
            border-top: 2px solid var(--text-brown);
        }
        .cat-section-header:first-child { border-top: none; }
        .cat-group-header { background: #f5f0eb; }
        .cat-group-header td {
            padding: 8px 12px; font-weight: 700; font-size: 0.85rem;
            color: var(--text-brown); border-bottom: 1px solid #e0d5c5; letter-spacing: 0.03em;
        }
        .cat-group-icon { margin-right: 6px; }

        /* Action buttons */
        .btn-edit   { padding: 4px 11px; background-color: var(--text-brown); font-size: 0.8rem; }
        .btn-delete { padding: 4px 11px; background-color: var(--danger-red); font-size: 0.8rem; margin-left: 4px; }

        /* Filter bar */
        .filter-bar { display: flex; gap: 10px; align-items: center; flex-wrap: wrap; margin-bottom: 16px; }
        .filter-bar select { width: auto; margin: 0; }
        .filter-bar input  { width: auto; min-width: 180px; margin: 0; }

        /* Item count badge */
        .item-count { font-size: 0.82rem; color: var(--accent-orange); font-weight: 600; margin-left: 8px; }

        /* Price cell */
        td.price-cell { font-weight: 600; color: var(--text-brown); }

        /* Field error message */
        .field-err { color: var(--danger-red); font-size: 0.77rem; font-weight: 600; display: block; margin-top: 2px; }

        /* Fade-in row */
        @keyframes rowIn {
            from { opacity: 0; transform: translateX(-8px); }
            to   { opacity: 1; transform: translateX(0); }
        }
        .row-new { animation: rowIn 0.3s ease; }
    </style>
</head>
<body class="staff-portal">

    <div class="sidebar">
        <h2>Restaurant</h2>
        <a href="staff_dashboard.php">Dashboard</a>
        <a href="manage_orders.php">Manage Orders</a>
        <a href="manage_menu.php" style="background-color: var(--accent-orange); color: white;">Manage Menu</a>
        <a href="#" onclick="logoutStaff(); return false;" class="sidebar-logout">Logout</a>
    </div>

    <div class="main-content">
        <h1 style="border-bottom: 2px solid var(--text-brown); padding-bottom: 10px;">
            Manage Menu <span class="item-count" id="itemCount">(<?= $totalItems ?> items)</span>
        </h1>

        <!-- ── Add New Item ── -->
        <div class="card" style="margin-bottom: 26px;">
            <h3 style="margin-top: 0;">➕ Add New Food Item</h3>
            <div id="addError" style="display:none; color:var(--danger-red); font-size:0.83rem;
                 font-weight:600; background:#fde8e8; border-left:4px solid var(--danger-red);
                 border-radius:6px; padding:8px 12px; margin-bottom:12px;"></div>
            <form id="addForm" onsubmit="return false;">
                <div style="flex-grow:2; min-width: 250px;">
                    <label for="food_name">Food Name</label>
                    <input type="text" id="food_name" placeholder="e.g. Roti Sardine" autocomplete="off">
                </div>
                <div style="min-width: 100px;">
                    <label for="price">Price (RM)</label>
                    <input type="number" id="price" placeholder="e.g. 6.00" step="0.10" min="0.10">
                </div>
                <div style="min-width: 200px;">
                    <label for="category">Category</label>
                    <select id="category" style="margin:8px 0 15px;">
                        <option value="">-- Select Category --</option>
                        <?php foreach ($CATEGORIES as $cat): ?>
                        <option value="<?= htmlspecialchars($cat['id']) ?>"><?= htmlspecialchars($cat['section'].' › '.$cat['label']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <!-- Dynamic Options Section -->
                <div style="width: 100%; margin-top: 15px; border-top: 1px dashed #e0d5c5; padding-top: 15px;">
                    <h4 style="margin: 0 0 10px; color: var(--accent-orange); font-size: 0.9rem;">⚙️ Customize Options / Add-Ons (Optional)</h4>
                    <div id="optionsContainer">
                        <!-- Option rows will be appended here dynamically -->
                    </div>
                    <button type="button" class="btn-action" style="background:#5E2A25; color:#fff; font-size:0.75rem; padding:6px 14px; margin-top: 5px; border-radius:6px;" onclick="addOptionRow()">
                        ➕ Add Option Row
                    </button>
                </div>

                <div style="width: 100%; text-align: right; margin-top: 15px;">
                    <button type="button" id="addBtn" onclick="addItem()">
                        Add to Menu
                    </button>
                </div>
            </form>
        </div>

        <!-- ── Filter Bar ── -->
        <div class="card" style="padding: 14px 20px; margin-bottom: 6px;">
            <div class="filter-bar">
                <strong>Filter:</strong>
                <select id="catFilter" onchange="applyFilter()">
                    <option value="">All Categories</option>
                    <?php foreach ($CATEGORIES as $cat): ?>
                        <?php if (!empty($grouped[$cat['id']])): ?>
                        <option value="<?= htmlspecialchars($cat['id']) ?>">
                            <?= htmlspecialchars($cat['section'].' › '.$cat['label']) ?>
                        </option>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </select>
                <input type="text" id="searchFilter" placeholder="🔍 Search item name…" oninput="applyFilter()">
                <span class="item-count" id="filterCount"></span>
            </div>
        </div>

        <!-- ── Menu Table ── -->
        <div class="card" style="padding:0; overflow:hidden;">
            <table id="menuTable" style="margin-top:0;">
                <thead>
                    <tr>
                        <th style="width:55px;">ID</th>
                        <th>Food Name</th>
                        <th style="width:145px;">Category</th>
                        <th style="width:120px;">Price (RM)</th>
                        <th style="width:165px;">Actions</th>
                    </tr>
                </thead>
                <tbody id="menuBody">
<?php
$lastSection = null;
foreach ($CATEGORIES as $cat):
    $items = $grouped[$cat['id']] ?? [];
    if (empty($items)) continue;

    /* Section divider */
    if ($cat['section'] !== $lastSection):
        echo '<tr class="cat-section-header" data-section="'.htmlspecialchars($cat['section']).'"><td colspan="5">'.htmlspecialchars($cat['section']).'</td></tr>';
        $lastSection = $cat['section'];
    endif;

    /* Category sub-header */
    echo '<tr class="cat-group-header" id="cathead-'.htmlspecialchars($cat['id']).'" data-category="'.htmlspecialchars($cat['id']).'">';
    echo '<td colspan="5"><span class="cat-group-icon">▸</span>'.htmlspecialchars($cat['label'])
       . ' <span style="font-weight:400;font-size:0.78rem;color:#888;margin-left:8px;">('. count($items) .' items)</span></td>';
    echo '</tr>';

    /* Item rows */
    foreach ($items as $item):
        $catEsc  = htmlspecialchars($item['category']);
        $nameEsc = htmlspecialchars($item['item_name']);
        $nameJS  = json_encode($item['item_name']);
        $catJS   = json_encode($item['category']);
        $price   = number_format((float)$item['price'], 2);
        $id      = (int)$item['item_id'];
        $optionsEsc = htmlspecialchars(json_encode($optionsByItem[$id] ?? []));
        echo "<tr id=\"row_{$id}\" class=\"item-row\" data-id=\"{$id}\" data-category=\"{$catEsc}\" data-name=\"".strtolower($item['item_name'])."\">";
        echo "<td style=\"color:#aaa;font-size:0.8rem;\">#{$id}</td>";
        echo "<td>";
        echo "<strong>{$nameEsc}</strong>";
        if (!empty($optionsByItem[$id])) {
            echo '<div style="margin-top: 5px; display: flex; flex-wrap: wrap; gap: 6px;">';
            foreach ($optionsByItem[$id] as $opt) {
                $optGroup = htmlspecialchars($opt['option_group']);
                $optName  = htmlspecialchars($opt['option_name']);
                $optPrice = floatval($opt['additional_price']);
                $priceStr = $optPrice > 0 ? " (+RM " . number_format($optPrice, 2) . ")" : "";
                echo "<span style=\"font-size: 0.72rem; background: #fdf5e6; color: #b85c38; border: 1px solid #ebd5c8; border-radius: 4px; padding: 1px 6px; font-weight: 500;\">";
                echo "{$optGroup}: {$optName}{$priceStr}";
                echo "</span>";
            }
            echo '</div>';
        }
        echo "</td>";
        echo "<td style=\"font-size:0.8rem;color:#888;\">{$catEsc}</td>";
        echo "<td class=\"price-cell\" id=\"price_{$id}\">RM {$price}</td>";
        echo "<td>";
        echo "<button class=\"btn-edit\" onclick='openEditModal({$id},{$nameJS},{$item['price']},{$catJS},{$optionsEsc})'>✏️ Edit</button>";
        echo "<button class=\"btn-delete\" onclick=\"deleteItem({$id},{$nameJS})\">🗑</button>";
        echo "</td>";
        echo "</tr>";
    endforeach;
endforeach;
?>
                </tbody>
            </table>
            <p id="noItems" style="display:none; text-align:center; padding:40px; color:#aaa;">
                No items match your filter.
            </p>
        </div><!-- /.card -->
    </div><!-- /.main-content -->

    <!-- ══ Edit Modal ══ -->
    <div class="modal-overlay" id="editModal" onclick="closeModalOnOverlay(event)">
        <div class="modal-box">
            <button class="modal-close" onclick="closeEditModal()">✕</button>
            <h3>✏️ Edit Food Item <span id="modalItemId" style="color:var(--accent-orange);"></span></h3>

            <div id="editError" style="display:none; color:var(--danger-red); font-size:0.83rem;
                 font-weight:600; background:#fde8e8; border-left:4px solid var(--danger-red);
                 border-radius:6px; padding:8px 12px; margin-bottom:12px;"></div>

            <input type="hidden" id="modal_edit_id">

            <label for="modal_food_name">Food Name</label>
            <input type="text" id="modal_food_name" placeholder="e.g. Roti Kosong">

            <label for="modal_price">Price (RM)</label>
            <input type="number" id="modal_price" placeholder="e.g. 1.50" step="0.10" min="0.10">

            <label for="modal_category">Category</label>
            <select id="modal_category">
                <option value="">-- Select Category --</option>
                <?php foreach ($CATEGORIES as $cat): ?>
                <option value="<?= htmlspecialchars($cat['id']) ?>"><?= htmlspecialchars($cat['section'].' › '.$cat['label']) ?></option>
                <?php endforeach; ?>
            </select>

            <!-- Dynamic Edit Options Section -->
            <div style="margin-top: 15px; border-top: 1px dashed #e0d5c5; padding-top: 15px;">
                <h4 style="margin: 0 0 10px; color: var(--accent-orange); font-size: 0.9rem;">⚙️ Customize Options / Add-Ons (Optional)</h4>
                <div id="editOptionsContainer" style="max-height: 160px; overflow-y: auto; padding-right: 5px;">
                    <!-- Option rows will be loaded here dynamically -->
                </div>
                <button type="button" class="btn-action" style="background:#5E2A25; color:#fff; font-size:0.75rem; padding:6px 14px; margin-top: 5px; border-radius:6px;" onclick="addEditOptionRow()">
                    ➕ Add Option Row
                </button>
            </div>

            <div class="modal-footer">
                <button class="btn-cancel" onclick="closeEditModal()">Cancel</button>
                <button id="saveBtn" onclick="saveEdit()">💾 Save Changes</button>
            </div>
        </div>
    </div>

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

        // ── Categories list ──
        const CATEGORIES = <?= json_encode($CATEGORIES) ?>;

        // ── Filter / Search ──
        function applyFilter() {
            var catF   = document.getElementById('catFilter').value;
            var search = document.getElementById('searchFilter').value.toLowerCase().trim();
            var rows   = document.querySelectorAll('#menuBody tr.item-row');
            var visible = 0;

            rows.forEach(function(row) {
                var cat  = row.dataset.category || '';
                var name = row.dataset.name     || '';
                var ok   = (!catF || cat === catF) && (!search || name.includes(search));
                row.style.display = ok ? '' : 'none';
                if (ok) visible++;
            });

            // Show / hide category group headers
            document.querySelectorAll('#menuBody tr.cat-group-header').forEach(function(hdr) {
                var cat = hdr.dataset.category;
                var has = Array.from(document.querySelectorAll('#menuBody tr.item-row[data-category="'+cat+'"]'))
                              .some(r => r.style.display !== 'none');
                hdr.style.display = has ? '' : 'none';
            });

            // Show / hide section headers
            document.querySelectorAll('#menuBody tr.cat-section-header').forEach(function(sh) {
                var sec = sh.dataset.section;
                var has = Array.from(document.querySelectorAll('#menuBody tr.cat-group-header')).some(function(hdr){
                    return hdr.dataset.section === sec && hdr.style.display !== 'none';
                });
                sh.style.display = has ? '' : 'none';
            });

            document.getElementById('noItems').style.display   = visible===0 ? 'block' : 'none';
            document.getElementById('filterCount').innerText   = visible===0 ? '' : '('+visible+' shown)';
            document.getElementById('itemCount').innerText     = '('+visible+' item'+(visible!==1?'s':'')+')';
        }

        // Rebuild section data attrs from cat-group-header
        document.querySelectorAll('#menuBody tr.cat-group-header').forEach(function(hdr){
            var catId = hdr.dataset.category;
            var sec   = CATEGORIES.find(c=>c.id===catId);
            if (sec) hdr.dataset.section = sec.section;
        });

        // ── AJAX helpers ──
        function postAjax(action, data, onSuccess, onError) {
            var fd = new FormData();
            Object.keys(data).forEach(k => fd.append(k, data[k]));
            fetch('manage_menu.php?ajax='+action, { method:'POST', body: fd })
                .then(r => r.json())
                .then(res => { if (res.success) onSuccess(res); else onError(res.error||'Unknown error'); })
                .catch(() => onError('Network error. Please try again.'));
        }

        // ── ADD OPTION ROW ──
        function addOptionRow() {
            var container = document.getElementById('optionsContainer');
            var div = document.createElement('div');
            div.className = 'option-row';
            div.style.cssText = 'display: flex; gap: 10px; align-items: center; margin-bottom: 8px;';
            div.innerHTML = 
                '<input type="text" class="opt-group" placeholder="Group (e.g. Add Ons)" style="flex: 1; margin: 0; padding: 6px 10px; font-size: 0.82rem;">' +
                '<input type="text" class="opt-name" placeholder="Name (e.g. Extra Cheese)" style="flex: 1.5; margin: 0; padding: 6px 10px; font-size: 0.82rem;">' +
                '<input type="number" class="opt-price" placeholder="Price (e.g. 1.50)" step="0.05" min="0.00" value="0.00" style="width: 100px; margin: 0; padding: 6px 10px; font-size: 0.82rem;">' +
                '<button type="button" style="background:#5E2A25; color:#fff; border:none; padding: 8px 12px; border-radius: 4px; cursor:pointer;" onclick="this.parentElement.remove()">🗑</button>';
            container.appendChild(div);
            div.querySelector('.opt-group').focus();
        }

        // ── ADD ──
        function addItem() {
            var name     = document.getElementById('food_name').value.trim();
            var price    = parseFloat(document.getElementById('price').value);
            var category = document.getElementById('category').value;
            var errEl    = document.getElementById('addError');

            errEl.style.display = 'none';
            document.getElementById('food_name').style.borderColor = '';
            document.getElementById('price').style.borderColor = '';

            // Client-side validation
            var errors = [];
            if (!name)          { errors.push('Food name is required.'); document.getElementById('food_name').style.borderColor = '#5E2A25'; }
            if (!price||price<=0){ errors.push('Enter a valid price.');  document.getElementById('price').style.borderColor = '#5E2A25'; }
            if (!category)       { errors.push('Select a category.'); }

            // Collect options
            var options = [];
            var optRows = document.querySelectorAll('.option-row');
            optRows.forEach(function(row) {
                var g = row.querySelector('.opt-group').value.trim();
                var n = row.querySelector('.opt-name').value.trim();
                var p = parseFloat(row.querySelector('.opt-price').value) || 0;
                if (g || n) {
                    if (!g || !n) {
                        errors.push('Option group and name are both required for each row.');
                    } else {
                        options.push({ group: g, name: n, price: p });
                    }
                }
            });

            if (errors.length) { errEl.innerText = '⚠ ' + errors[0]; errEl.style.display='block'; return; }

            var btn = document.getElementById('addBtn');
            btn.disabled = true; btn.innerText = 'Adding…';

            postAjax('add', {
                name: name,
                price: price.toFixed(2),
                category: category,
                options_json: JSON.stringify(options)
            }, function(res){
                // Insert new row into the table
                insertNewRow(res.id, res.name, res.price, res.category, res.options);

                // Clear form
                document.getElementById('food_name').value = '';
                document.getElementById('price').value     = '';
                document.getElementById('optionsContainer').innerHTML = '';
                showToast('✅ "'+res.name+'" added to menu!');
                btn.disabled = false; btn.innerText = 'Add to Menu';

                applyFilter();
            }, function(err){
                errEl.innerText = '⚠ '+err; errEl.style.display='block';
                btn.disabled = false; btn.innerText = 'Add to Menu';
            });
        }

        function insertNewRow(id, name, price, category, options) {
            var tbody = document.getElementById('menuBody');
            var catId = category;

            // Find the category group header to insert after
            var groupHdr = document.getElementById('cathead-'+catId);

            // Build new row HTML
            var nameJS = JSON.stringify(name);
            var catJS  = JSON.stringify(category);
            var priceF = parseFloat(price).toFixed(2);

            var tr = document.createElement('tr');
            tr.id = 'row_'+id;
            tr.className = 'item-row row-new';
            tr.dataset.id       = id;
            tr.dataset.category = catId;
            tr.dataset.name     = name.toLowerCase();

            var optionsHtml = '';
            if (options && options.length > 0) {
                optionsHtml = '<div style="margin-top: 5px; display: flex; flex-wrap: wrap; gap: 6px;">';
                options.forEach(function(opt) {
                    var priceStr = opt.additional_price > 0 ? " (+RM " + parseFloat(opt.additional_price).toFixed(2) + ")" : "";
                    optionsHtml += '<span style="font-size: 0.72rem; background: #fdf5e6; color: #b85c38; border: 1px solid #ebd5c8; border-radius: 4px; padding: 1px 6px; font-weight: 500;">' +
                        escHtml(opt.option_group) + ': ' + escHtml(opt.option_name) + priceStr +
                        '</span>';
                });
                optionsHtml += '</div>';
            }

            tr.innerHTML =
                '<td style="color:#aaa;font-size:0.8rem;">#'+id+'</td>' +
                '<td><strong>'+escHtml(name)+'</strong>' + optionsHtml + '</td>' +
                '<td style="font-size:0.8rem;color:#888;">'+escHtml(category)+'</td>' +
                '<td class="price-cell" id="price_'+id+'">RM '+priceF+'</td>' +
                '<td>' +
                  '<button class="btn-edit" onclick=\'openEditModal('+id+','+nameJS+','+price+','+catJS+','+JSON.stringify(options)+')\'>✏️ Edit</button>' +
                  '<button class="btn-delete" onclick="deleteItem('+id+','+nameJS+')">🗑</button>' +
                '</td>';

            if (groupHdr) {
                // Insert after the group header (and any existing items in that group)
                var next = groupHdr.nextSibling;
                while (next && next.classList && (next.classList.contains('item-row')) && next.dataset.category === catId) {
                    next = next.nextSibling;
                }
                tbody.insertBefore(tr, next);

                // Update item count in the group header
                var span = groupHdr.querySelector('span');
                if (span) {
                    var count = tbody.querySelectorAll('tr.item-row[data-category="'+catId+'"]').length;
                    span.innerText = '('+count+' items)';
                }
            } else {
                // Category not in table yet — just append
                tbody.appendChild(tr);
            }

            // Update total count
            var total = tbody.querySelectorAll('tr.item-row').length;
            document.getElementById('itemCount').innerText = '('+total+' items)';
        }

        // ── EDIT OPTION ROW ──
        function addEditOptionRow(group = '', name = '', price = '0.00') {
            var container = document.getElementById('editOptionsContainer');
            var div = document.createElement('div');
            div.className = 'edit-option-row';
            div.style.cssText = 'display: flex; gap: 10px; align-items: center; margin-bottom: 8px;';
            div.innerHTML = 
                '<input type="text" class="edit-opt-group" placeholder="Group (e.g. Add Ons)" value="' + escHtml(group) + '" style="flex: 1; margin: 0; padding: 6px 10px; font-size: 0.82rem;">' +
                '<input type="text" class="edit-opt-name" placeholder="Name (e.g. Extra Cheese)" value="' + escHtml(name) + '" style="flex: 1.5; margin: 0; padding: 6px 10px; font-size: 0.82rem;">' +
                '<input type="number" class="edit-opt-price" placeholder="Price (e.g. 1.50)" step="0.05" min="0.00" value="' + parseFloat(price).toFixed(2) + '" style="width: 100px; margin: 0; padding: 6px 10px; font-size: 0.82rem;">' +
                '<button type="button" style="background:#5E2A25; color:#fff; border:none; padding: 8px 12px; border-radius: 4px; cursor:pointer;" onclick="this.parentElement.remove()">🗑</button>';
            container.appendChild(div);
        }

        // ── EDIT MODAL ──
        function openEditModal(id, name, price, category, options) {
            document.getElementById('modal_edit_id').value    = id;
            document.getElementById('modal_food_name').value  = name;
            document.getElementById('modal_price').value      = parseFloat(price).toFixed(2);
            document.getElementById('modal_category').value   = category;
            document.getElementById('modalItemId').innerText  = '(ID: '+id+')';
            document.getElementById('editError').style.display = 'none';

            // Populate options
            var container = document.getElementById('editOptionsContainer');
            container.innerHTML = '';
            if (options && options.length > 0) {
                options.forEach(function(opt) {
                    addEditOptionRow(opt.option_group, opt.option_name, opt.additional_price);
                });
            }

            document.getElementById('editModal').classList.add('active');
            document.getElementById('modal_food_name').focus();
        }

        function closeEditModal() {
            document.getElementById('editModal').classList.remove('active');
        }

        function closeModalOnOverlay(e) {
            if (e.target === document.getElementById('editModal')) closeEditModal();
        }

        function saveEdit() {
            var id       = parseInt(document.getElementById('modal_edit_id').value);
            var name     = document.getElementById('modal_food_name').value.trim();
            var price    = parseFloat(document.getElementById('modal_price').value);
            var category = document.getElementById('modal_category').value;
            var errEl    = document.getElementById('editError');

            errEl.style.display = 'none';

            var errors = [];
            if (!name)          { errors.push('Food name is required.'); }
            if (!price||price<=0){ errors.push('Enter a valid price.'); }
            if (!category)       { errors.push('Select a category.'); }

            // Collect edit options
            var options = [];
            var optRows = document.querySelectorAll('.edit-option-row');
            optRows.forEach(function(row) {
                var g = row.querySelector('.edit-opt-group').value.trim();
                var n = row.querySelector('.edit-opt-name').value.trim();
                var p = parseFloat(row.querySelector('.edit-opt-price').value) || 0;
                if (g || n) {
                    if (!g || !n) {
                        errors.push('Option group and name are both required for each row.');
                    } else {
                        options.push({ group: g, name: n, price: p });
                    }
                }
            });

            if (errors.length)  { errEl.innerText = '⚠ '+errors[0]; errEl.style.display='block'; return; }

            var btn = document.getElementById('saveBtn');
            btn.disabled = true; btn.innerText = 'Saving…';

            postAjax('edit', {
                id: id,
                name: name,
                price: price.toFixed(2),
                category: category,
                options_json: JSON.stringify(options)
            }, function(res){
                // Update row in table
                var row = document.getElementById('row_'+id);
                if (row) {
                    var nameJS = JSON.stringify(res.name);
                    var catJS  = JSON.stringify(res.category);
                    var priceF = parseFloat(res.price).toFixed(2);

                    var optionsHtml = '';
                    if (res.options && res.options.length > 0) {
                        optionsHtml = '<div style="margin-top: 5px; display: flex; flex-wrap: wrap; gap: 6px;">';
                        res.options.forEach(function(opt) {
                            var priceStr = opt.additional_price > 0 ? " (+RM " + parseFloat(opt.additional_price).toFixed(2) + ")" : "";
                            optionsHtml += '<span style="font-size: 0.72rem; background: #fdf5e6; color: #b85c38; border: 1px solid #ebd5c8; border-radius: 4px; padding: 1px 6px; font-weight: 500;">' +
                                escHtml(opt.option_group) + ': ' + escHtml(opt.option_name) + priceStr +
                                '</span>';
                        });
                        optionsHtml += '</div>';
                    }

                    row.cells[1].innerHTML = '<strong>' + escHtml(res.name) + '</strong>' + optionsHtml;
                    row.cells[2].innerText = res.category;
                    row.cells[3].innerText = 'RM '+priceF;
                    row.cells[3].id        = 'price_'+id;
                    row.dataset.name       = res.name.toLowerCase();
                    row.dataset.category   = res.category;

                    var optsJS = JSON.stringify(res.options);
                    row.cells[4].innerHTML =
                        '<button class="btn-edit" onclick=\'openEditModal('+id+','+nameJS+','+res.price+','+catJS+','+optsJS+')\'>✏️ Edit</button>' +
                        '<button class="btn-delete" onclick="deleteItem('+id+','+nameJS+')">🗑</button>';
                }
                closeEditModal();
                showToast('✅ "'+res.name+'" updated!');
                btn.disabled = false; btn.innerText = '💾 Save Changes';
                applyFilter();
            }, function(err){
                errEl.innerText = '⚠ '+err; errEl.style.display='block';
                btn.disabled = false; btn.innerText = '💾 Save Changes';
            });
        }

        // ── DELETE ──
        function deleteItem(id, name) {
            if (!confirm('Delete "'+name+'" from the menu?\nThis cannot be undone.')) return;

            postAjax('delete', {id:id}, function(){
                var row = document.getElementById('row_'+id);
                if (row) {
                    var cat = row.dataset.category;
                    row.remove();

                    // Update category item count
                    var groupHdr = document.getElementById('cathead-'+cat);
                    if (groupHdr) {
                        var remaining = document.querySelectorAll('#menuBody tr.item-row[data-category="'+cat+'"]').length;
                        if (remaining === 0) {
                            groupHdr.style.display = 'none';
                        } else {
                            var sp = groupHdr.querySelector('span');
                            if (sp) sp.innerText = '('+remaining+' items)';
                        }
                    }
                }
                var total = document.querySelectorAll('#menuBody tr.item-row').length;
                document.getElementById('itemCount').innerText = '('+total+' item'+(total!==1?'s':'')+')';
                showToast('🗑 "'+name+'" removed from menu.');
                applyFilter();
            }, function(err){
                showToast('⚠ '+err, true);
            });
        }

        // ── Escape HTML helper ──
        function escHtml(s) {
            return s.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
        }

        // ── Enter key in Add form ──
        document.getElementById('addForm').addEventListener('keydown', function(e) {
            if (e.key === 'Enter') addItem();
        });
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') closeEditModal();
        });

        // ── Init ──
        applyFilter();
    </script>
</body>
</html>