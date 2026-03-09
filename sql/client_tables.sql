-- Additional tables for client website functionality
-- Run this after the main schema.sql

USE pharmacy_management;

-- Client orders table (separate from admin sales table)
CREATE TABLE IF NOT EXISTS client_orders (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    total DECIMAL(10,2) NOT NULL,
    status ENUM('PENDING','PAID','SHIPPED','CANCELLED') NOT NULL DEFAULT 'PENDING',
    address TEXT NOT NULL,
    phone VARCHAR(20) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_status (status),
    INDEX idx_created_at (created_at)
);

-- Client order items table
CREATE TABLE IF NOT EXISTS client_order_items (
    id INT PRIMARY KEY AUTO_INCREMENT,
    order_id INT NOT NULL,
    medicine_id INT NOT NULL,
    qty INT NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES client_orders(id) ON DELETE CASCADE,
    FOREIGN KEY (medicine_id) REFERENCES medicines(id) ON DELETE CASCADE,
    INDEX idx_order_id (order_id),
    INDEX idx_medicine_id (medicine_id)
);

-- Add slug column to categories for URL-friendly names
ALTER TABLE categories ADD COLUMN IF NOT EXISTS slug VARCHAR(100);
UPDATE categories SET slug = LOWER(REPLACE(name, ' ', '-')) WHERE slug IS NULL;
UPDATE categories SET slug = 'cold-flu' WHERE name = 'Cold & Flu';
UPDATE categories SET slug = 'pain-relief' WHERE name = 'Pain Relief';

-- Sample client orders for demonstration
INSERT IGNORE INTO client_orders (user_id, total, status, address, phone, created_at) VALUES
(3, 45.00, 'SHIPPED', '123 Main St\nAnytown, ST 12345', '+1-555-0123', DATE_SUB(NOW(), INTERVAL 3 DAY)),
(3, 25.00, 'PENDING', '123 Main St\nAnytown, ST 12345', '+1-555-0123', DATE_SUB(NOW(), INTERVAL 1 DAY));

-- Sample client order items
INSERT IGNORE INTO client_order_items (order_id, medicine_id, qty, price) VALUES
(1, 1, 2, 5.00),
(1, 3, 1, 15.00),
(2, 1, 1, 5.00),
(2, 4, 1, 20.00);