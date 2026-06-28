# Instructions for Member 3 (Database & SQL)

Hi Member 3! Member 2 here. I've finished the Staff Panel (Dashboard, Manage Orders, Manage Menu). 

Since my part relies heavily on the database, I built my code to exactly match the database structure we agreed on. Here are the exact instructions for setting up the database so that my Staff Panel and Member 1's Customer Frontend can connect perfectly.

## 1. Database Setup
Please ensure the database is named exactly:
**`restaurant_order_db`**

I have created a connection file at `config/db.php` that both my part and Member 1's part can use to connect to the database easily.

## 2. Required Tables & Schema
Here is the exact schema my Staff Panel is reading from/writing to. Please ensure these tables and columns exist:

### `menu_items`
*(I use this table in Manage Menu to add/edit/delete foods. Member 1 will read from this to show the menu to customers).*
- `item_id` (INT, Primary Key, Auto Increment)
- `item_name` (VARCHAR)
- `category` (VARCHAR)
- `price` (DECIMAL 10,2)
- `image` (VARCHAR, optional)
- `status` (VARCHAR, default 'Available')

### `orders`
*(I use this table in the Dashboard and Manage Orders to see incoming orders from Member 1).*
- `order_id` (INT, Primary Key, Auto Increment)
- `customer_name` (VARCHAR)
- `phone` (VARCHAR, optional)
- `order_type` (VARCHAR - e.g., 'Walk-In' or 'Online')
- `table_no` (VARCHAR, optional)
- `total_amount` (DECIMAL 10,2)
- `order_status` (VARCHAR, default 'Pending')
- `order_date` (TIMESTAMP or DATETIME)

### `order_items`
*(I use this table so the kitchen staff can see exactly what food to cook for each order).*
- `order_item_id` (INT, Primary Key, Auto Increment)
- `order_id` (INT, Foreign Key linking to `orders`)
- `item_id` (INT, Foreign Key linking to `menu_items`)
- `quantity` (INT)
- `subtotal` (DECIMAL 10,2)
- **`status` (VARCHAR, default 'Pending')** 👈 *Note: I added this column so kitchen staff can mark individual foods as "Cooking" or "Ready"!*

## 3. Seed Data (Important!)
I have written an SQL file that automatically creates all **163 menu items** (Sup Gearbox, Roti Canai, Tomyam, etc.) and adds **7 sample orders** so we can test the system right away without having to type everything manually.

**Your Action Item:**
1. Open phpMyAdmin.
2. Select `restaurant_order_db`.
3. Go to the "Import" tab.
4. Upload the file located at: `database/seed_menu.sql` (I just pushed this to GitHub).
5. Click Import.

Once you do that, my Staff Dashboard will instantly light up with real data, and Member 1 can start pulling real menu items for the customer side!
