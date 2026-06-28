# Staff Panel (Member 2) — Logic & Integration Guide

This document explains how the **Staff Dashboard, Manage Orders, and Manage Menu** pages integrate with the existing database and frontend. 

**Important Note for the Team:** 
The Staff Panel (Member 2's part) is built **entirely around Member 1's existing database structure and order flow**. We are strictly reading from and writing to the exact tables that Member 1/3 have already designed (`menu_items`, `orders`, `order_items`). We do not require any changes to the existing customer frontend or database schema, except for one minor addition to track per-item status.

---

## 🗄️ How We Connect to the Existing Database (For Member 3)

We use the existing `restaurant_order_db`. The connection logic for the staff panel is in `config/db.php` (using PDO). 

Here is how the Staff Panel interacts with the tables you've already created:

### 1. `menu_items` Table
- **What we do:** `manage_menu.php` reads all items from this table to display to the staff. When staff adds, edits, or deletes a food item, we run standard `INSERT`, `UPDATE`, or `DELETE` queries directly on this table.
- **Your part:** Continue inserting new menu items here. The Customer Frontend (Member 1) should simply read from this table to display the menu to walk-in/online customers. 

### 2. `orders` Table
- **What we do:** `staff_dashboard.php` and `manage_orders.php` read from this table to see incoming orders. We group them by `order_status` (Pending, Preparing, Completed) and display them to the staff. 
- **Your part:** When a customer places an order on Member 1's frontend, simply `INSERT` a new row into this table with `order_status = 'Pending'`. The Staff Dashboard will automatically pick it up.

### 3. `order_items` Table
- **What we do:** We read this table to show the staff exactly what food is in each order. 
- **Your part:** When a customer places an order, just `INSERT` the items into this table as usual.

---

## ⚙️ The Overall Flow

1. **Customer Orders (Member 1's Flow):** Customer scans QR / goes online -> browses `menu_items` -> submits order -> Member 1's code inserts to `orders` and `order_items`.
2. **Staff Dashboard (Member 2's Flow):** Staff logs in (`login.php`) -> sees dashboard. Dashboard does a `SELECT COUNT(*)` on the `orders` table to show how many orders are Pending, In Progress, etc., for today.
3. **Kitchen Updates (Member 2's Flow):** Kitchen staff uses Manage Orders to update the overall order status (Pending -> Preparing -> Completed). This directly updates `orders.order_status`.
4. **Menu Management (Member 2's Flow):** Admin uses Manage Menu to change prices or add new foods. This directly updates `menu_items`, instantly reflecting on Member 1's Customer Frontend.

---

## 📌 Files Included in Member 2's Push
- **`login.php`**: Secure login page (credentials: `admin` / `admin123`).
- **`staff_dashboard.php`**: Real-time PHP dashboard reading from the `orders` table.
- **`manage_orders.php`**: Order management system reading from `orders` and `order_items`.
- **`manage_menu.php`**: Menu CRUD system reading/writing to `menu_items`.
- **`config/db.php`**: Shared PDO database connection logic.
- **`database/seed_menu.sql`**: Contains sample orders to help test the system before production. **For the menu data, we will ignore this file and strictly use whatever Member 1 has already populated in the database.**
- **CSS & JS**: `css/admin.css` and `js/admin_validation.js`.
