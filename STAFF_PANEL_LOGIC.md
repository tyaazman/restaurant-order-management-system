# Staff Panel (Member 2) — Logic & Integration Guide

This document explains the logic for the Staff Dashboard, Manage Orders, and Manage Menu pages, specifically for **Member 3 (Database/SQL)** and **Member 1 (Customer/Menu Frontend)** to understand how the modules connect.

## 🗄️ Database Schema & Connection (For Member 3)

We are using the `restaurant_order_db` database. The connection file is located at `config/db.php` and connects via PDO (root/no password for local testing).

The staff panel relies on the following tables and columns:

### 1. `menu_items` Table
- **Columns used:** `item_id`, `item_name`, `category`, `price`
- **Logic:** `manage_menu.php` fetches all items from this table and displays them grouped by `category`. When staff adds, edits, or deletes an item, an AJAX request is sent to `manage_menu.php` to instantly run `INSERT`, `UPDATE`, or `DELETE` on this table.
- **For Member 1:** The customer frontend should query `SELECT * FROM menu_items WHERE status = 'Available'` (if you add a status column) to display the menu to walk-in/online customers.

### 2. `orders` Table
- **Columns used:** `order_id`, `customer_name`, `order_type` (Walk-In/Online), `table_no`, `total_amount`, `order_status`, `order_date`.
- **Logic:** `manage_orders.php` and `staff_dashboard.php` query this table (filtered by `DATE(order_date)`) to display the orders. 
- **For Member 1:** When a customer places an order, your frontend must `INSERT` a new row into `orders` with the initial `order_status = 'Pending'`.

### 3. `order_items` Table
- **Columns used:** `order_item_id`, `order_id` (FK), `item_id` (FK), `quantity`, `subtotal`, `status` (Added `status VARCHAR(20) DEFAULT 'Pending'`).
- **Logic:** `manage_orders.php` allows staff to update the status of *individual items* (e.g., from Pending to In Progress to Ready). When an item's status updates, PHP automatically recalculates the overall `order_status` in the `orders` table. 
- **For Member 1:** When a customer places an order, your frontend must `INSERT` the corresponding items into `order_items` for the `order_id` generated above.

---

## ⚙️ Module Flow

1. **Customer Orders (Member 1):** Customer scans QR / goes online -> browses `menu_items` -> submits order -> inserts to `orders` and `order_items`.
2. **Staff Dashboard (Member 2):** Staff logs in (`login.php` using session auth) -> sees dashboard. Dashboard does a `SELECT COUNT(*)` on `orders` to show how many orders are Pending, In Progress, etc., for today.
3. **Manage Orders (Member 2):** Staff updates the item statuses (Cooking/Ready) -> AJAX updates `order_items.status` -> updates `orders.order_status`.
4. **Manage Menu (Member 2):** Staff adds new foods/prices -> AJAX updates `menu_items` -> Customers instantly see the updated menu.

---

## 📌 Files Included in Member 2's Push
- **`login.php`**: Secure login page (credentials: `admin` / `admin123`).
- **`staff_dashboard.php`**: Real-time PHP dashboard pulling from MySQL.
- **`manage_orders.php`**: Order management with item-level status updates.
- **`manage_menu.php`**: Menu CRUD system.
- **`config/db.php`**: Shared PDO database connection logic.
- **`database/seed_menu.sql`**: Test data containing all 163 menu items and sample orders to help test the system before production.
- **CSS & JS**: `css/admin.css` and `js/admin_validation.js` for UI styling and validation.
