# Laundry.App
App Laundry ber sistem CRUD antara user dan amin

# code SQL 
CREATE DATABASE IF NOT EXISTS laundry_db;
USE laundry_db;

-- Table: users
CREATE TABLE users (
    id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE,
    email VARCHAR(100),
    password VARCHAR(255),
    role ENUM('admin', 'user') DEFAULT 'user',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    remember_token VARCHAR(64),
    token_expiry DATETIME
);

-- Table: user_profiles
CREATE TABLE user_profiles (
    id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    user_id INT(11) NOT NULL,
    full_name VARCHAR(100),
    phone VARCHAR(20),
    address TEXT,
    photo VARCHAR(255),
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Table: orders
CREATE TABLE orders (
    id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    user_id INT(11),
    order_code VARCHAR(100),
    customer_name VARCHAR(100),
    phone VARCHAR(20),
    address TEXT,
    services TEXT,
    status ENUM('pending','washing','drying','ironing','packing','delivered','done') DEFAULT 'pending',
    payment_method VARCHAR(50),
    total INT(11),
    paid ENUM('yes','no') DEFAULT 'no',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Table: order_items
CREATE TABLE order_items (
    id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    order_id INT(11) NOT NULL,
    item_name VARCHAR(100) NOT NULL,
    quantity INT(11) NOT NULL,
    unit_price INT(11) NOT NULL,
    subtotal INT(11) NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id)
);

-- Table: order_status
CREATE TABLE order_status (
    id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    order_id INT(11),
    proses ENUM('Order','Cuci','Kering','Setrika','Packing','Antar','Selesai'),
    waktu DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id)
);

-- Table: password_resets
CREATE TABLE password_resets (
    id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL,
    token VARCHAR(255) NOT NULL,
    expires_at DATETIME NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);
