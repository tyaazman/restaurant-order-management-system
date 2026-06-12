CREATE DATABASE IF NOT EXISTS restaurant_order_db;
USE restaurant_order_db;

CREATE TABLE IF NOT EXISTS users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role VARCHAR(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS menu_items (
    item_id INT AUTO_INCREMENT PRIMARY KEY,
    item_name VARCHAR(100) NOT NULL,
    category VARCHAR(50) NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    image VARCHAR(255),
    status VARCHAR(20) DEFAULT 'Available'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS orders (
    order_id INT AUTO_INCREMENT PRIMARY KEY,
    customer_name VARCHAR(100) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    order_type VARCHAR(20) DEFAULT 'Walk-In',
    table_no VARCHAR(20),
    address VARCHAR(255),
    total_amount DECIMAL(10,2) NOT NULL,
    order_status VARCHAR(50) DEFAULT 'Pending',
    order_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS order_items (
    order_item_id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    item_id INT NOT NULL,
    quantity INT NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL,
    CONSTRAINT fk_order_items_order
        FOREIGN KEY (order_id) REFERENCES orders(order_id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_order_items_menu
        FOREIGN KEY (item_id) REFERENCES menu_items(item_id)
        ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS payments (
    payment_id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    receipt_image VARCHAR(255),
    payment_status VARCHAR(50) DEFAULT 'Pending',
    CONSTRAINT fk_payments_order
        FOREIGN KEY (order_id) REFERENCES orders(order_id)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO users (username, password, role)
SELECT 'admin', '123456', 'admin'
WHERE NOT EXISTS (
    SELECT 1 FROM users WHERE username = 'admin'
);

DELETE FROM menu_items;

INSERT INTO menu_items (item_name, category, price, image, status)
VALUES
('Sup Gearbox Kambing', 'Signature', 19.00, 'sup_gearbox.png', 'Available'),
('Sup Kambing', 'Signature', 20.00, 'sup_kambing.png', 'Available'),
('Sup Daging', 'Signature', 8.00, 'sup_daging.png', 'Available'),
('Mee Rebus Daging', 'Signature', 9.50, 'mee_rebus.png', 'Available'),
('Lontong Kuah', 'Sarapan', 7.50, 'lontong.png', 'Available'),
('Nasi Lemak Basmathi Ayam', 'Sarapan', 9.00, 'nasi_lemak.png', 'Available'),
('Roti Canai Kosong', 'Roti Canai', 1.50, 'roti_canai.png', 'Available'),
('Chicken Chop', 'Western Food', 18.50, 'chicken_chop.png', 'Available'),
('Crispy Chicken Burger', 'Western Food', 7.50, 'crispy_burger.png', 'Available'),
('Nasi Goreng Kampung', 'Goreng-Goreng', 8.00, 'nasi_goreng.png', 'Available'),
('Mee Goreng', 'Goreng-Goreng', 7.50, 'mee_goreng.png', 'Available'),
('Teh Tarik Cold', 'Drinks', 3.00, 'teh_tarik.png', 'Available');
