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
- `order_status` (VARCHAR, default 'Pending') — *Statuses used: Pending, Preparing, Completed*
- `order_date` (TIMESTAMP or DATETIME)

### `order_items`
*(I use this table so the kitchen staff can see exactly what food to cook for each order).*
- `order_item_id` (INT, Primary Key, Auto Increment)
- `order_id` (INT, Foreign Key linking to `orders`)
- `item_id` (INT, Foreign Key linking to `menu_items`)
- `quantity` (INT)
- `subtotal` (DECIMAL 10,2)

## 3. Data & Testing
Since **Member 1 has already handled the menu data**, please follow Member 1's data for the menu items. You do not need to use my seed data for the menu. 

My Staff Panel will simply read whatever menu items Member 1 has already stored in the `menu_items` table. 

*(Note: I did include a file `database/seed_menu.sql` in my push which has 7 dummy orders in it. If you need some fake orders to test if the dashboard is working, you can use that, but ignore the menu items part of it!)*
