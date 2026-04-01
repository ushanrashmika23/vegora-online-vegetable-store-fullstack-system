-- database.sql
-- Run this script to generate the database and users table

CREATE DATABASE IF NOT EXISTS vegi_db;
USE vegi_db;

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('customer', 'admin') DEFAULT 'customer',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert a default admin account (password: admin123)
-- Note: Replace with password_hash('admin123', PASSWORD_DEFAULT) output in actual production.
-- Using manual insert for scaffolding purposes.
INSERT IGNORE INTO users (name, email, password, role) VALUES 
('Super Admin', 'admin@vegora.com', '$2y$10$wN1I41Ehm3q2c5cIq78fA.qY.c88vT5y.2M2c99T33l4xQjJp6T/y', 'admin');

CREATE TABLE IF NOT EXISTS product_categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    discounted_price DECIMAL(10, 2) DEFAULT NULL,
    image VARCHAR(255) NOT NULL,
    stock INT NOT NULL DEFAULT 0,
    stock_limit INT NOT NULL DEFAULT 20,
    category_id INT NOT NULL,
    description TEXT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES product_categories(id) ON DELETE RESTRICT
);

INSERT IGNORE INTO product_categories (id, name) VALUES
(1, 'Organic'),
(2, 'Root Vegetables'),
(3, 'Greens'),
(4, 'Onions & Garlic');

CREATE TABLE IF NOT EXISTS cart (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    total DECIMAL(10, 2) NOT NULL,
    status VARCHAR(50) DEFAULT 'Pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS product_reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    user_id INT NOT NULL,
    rating TINYINT NOT NULL,
    review_text TEXT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uniq_product_user_review (product_id, user_id),
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CHECK (rating BETWEEN 1 AND 5)
);

-- Insert some mock data
INSERT IGNORE INTO products (id, name, price, discounted_price, image, stock, stock_limit, category_id, description) VALUES
(1, 'Fresh Tomatoes', 3.99, NULL, 'https://images.unsplash.com/photo-1592924357228-91a4daadcfea?auto=format&fit=crop&q=80&w=400', 50, 15, 1, NULL),
(2, 'Organic Carrots', 2.49, 1.99, 'https://images.unsplash.com/photo-1598170845058-32b9d6a5da37?auto=format&fit=crop&q=80&w=400', 100, 25, 2, NULL),
(3, 'Fresh Broccoli', 4.99, NULL, 'https://images.unsplash.com/photo-1459411621453-7b03977f4bfc?auto=format&fit=crop&q=80&w=400', 30, 12, 3, NULL),
(4, 'Bell Peppers', 5.99, 4.79, 'https://images.unsplash.com/photo-1610832958506-aa56368176cf?auto=format&fit=crop&q=80&w=400', 40, 10, 1, NULL),
(5, 'Red Onions', 1.99, NULL, 'https://images.unsplash.com/photo-1615486511484-93e50b10a406?auto=format&fit=crop&q=80&w=400', 80, 20, 4, NULL),
(6, 'Organic Potatoes', 2.99, NULL, 'https://images.unsplash.com/photo-1518977676601-b53f82aba655?auto=format&fit=crop&q=80&w=400', 150, 30, 2, NULL);
