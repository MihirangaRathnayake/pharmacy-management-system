-- Pharmacy Client Website Database Schema
-- MySQL 8.x compatible

SET FOREIGN_KEY_CHECKS = 0;

-- Drop tables if they exist
DROP TABLE IF EXISTS `order_items`;
DROP TABLE IF EXISTS `orders`;
DROP TABLE IF EXISTS `products`;
DROP TABLE IF EXISTS `categories`;
DROP TABLE IF EXISTS `users`;

SET FOREIGN_KEY_CHECKS = 1;

-- Users table
CREATE TABLE `users` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `name` varchar(100) NOT NULL,
    `email` varchar(100) NOT NULL UNIQUE,
    `password_hash` varchar(255) NOT NULL,
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Categories table
CREATE TABLE `categories` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `name` varchar(100) NOT NULL,
    `slug` varchar(100) NOT NULL UNIQUE,
    PRIMARY KEY (`id`),
    KEY `idx_slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Products table
CREATE TABLE `products` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `category_id` int(11) NOT NULL,
    `name` varchar(200) NOT NULL,
    `slug` varchar(200) NOT NULL UNIQUE,
    `sku` varchar(50) NOT NULL UNIQUE,
    `price` decimal(10,2) NOT NULL,
    `rx_required` boolean NOT NULL DEFAULT FALSE,
    `stock` int(11) NOT NULL DEFAULT 0,
    `image_url` varchar(500) DEFAULT NULL,
    `short_desc` text,
    `description` text,
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_category_id` (`category_id`),
    KEY `idx_slug` (`slug`),
    KEY `idx_sku` (`sku`),
    KEY `idx_stock` (`stock`),
    KEY `idx_rx_required` (`rx_required`),
    CONSTRAINT `fk_products_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Orders table
CREATE TABLE `orders` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `user_id` int(11) NOT NULL,
    `total` decimal(10,2) NOT NULL,
    `status` enum('PENDING','PAID','SHIPPED','CANCELLED') NOT NULL DEFAULT 'PENDING',
    `address` text NOT NULL,
    `phone` varchar(20) NOT NULL,
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_user_id` (`user_id`),
    KEY `idx_status` (`status`),
    KEY `idx_created_at` (`created_at`),
    CONSTRAINT `fk_orders_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Order items table
CREATE TABLE `order_items` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `order_id` int(11) NOT NULL,
    `product_id` int(11) NOT NULL,
    `qty` int(11) NOT NULL,
    `price` decimal(10,2) NOT NULL,
    PRIMARY KEY (`id`),
    KEY `idx_order_id` (`order_id`),
    KEY `idx_product_id` (`product_id`),
    CONSTRAINT `fk_order_items_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_order_items_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert sample data

-- Sample users
INSERT INTO `users` (`name`, `email`, `password_hash`, `created_at`) VALUES
('John Doe', 'john@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NOW()),
('Jane Smith', 'jane@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NOW()),
('Mike Johnson', 'mike@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NOW());

-- Sample categories
INSERT INTO `categories` (`name`, `slug`) VALUES
('Pain Relief', 'pain-relief'),
('Cold & Flu', 'cold-flu'),
('Vitamins & Supplements', 'vitamins-supplements');

-- Sample products
INSERT INTO `products` (`category_id`, `name`, `slug`, `sku`, `price`, `rx_required`, `stock`, `image_url`, `short_desc`, `description`) VALUES
-- Pain Relief
(1, 'Ibuprofen 200mg Tablets', 'ibuprofen-200mg-tablets', 'IBU200-100', 12.99, FALSE, 50, NULL, 'Fast-acting pain relief for headaches, muscle aches, and fever', 'Ibuprofen is a nonsteroidal anti-inflammatory drug (NSAID) used to reduce fever and treat pain or inflammation caused by many conditions such as headache, toothache, back pain, arthritis, menstrual cramps, or minor injury. This medication works by blocking your body''s production of certain natural substances that cause inflammation.'),

(1, 'Acetaminophen 500mg Tablets', 'acetaminophen-500mg-tablets', 'ACE500-100', 9.99, FALSE, 75, NULL, 'Gentle pain relief and fever reducer', 'Acetaminophen is used to treat mild to moderate pain and to reduce fever. It may also be used to treat pain from many conditions such as headache, muscle aches, arthritis, backache, toothaches, colds, and fevers. This medication does not usually cause stomach upset.'),

(1, 'Aspirin 325mg Tablets', 'aspirin-325mg-tablets', 'ASP325-100', 8.99, FALSE, 60, NULL, 'Classic pain reliever with anti-inflammatory properties', 'Aspirin is used to reduce fever and relieve mild to moderate pain from conditions such as muscle aches, toothaches, common cold, and headaches. It may also be used to reduce pain and swelling in conditions such as arthritis.'),

(1, 'Prescription Pain Relief', 'prescription-pain-relief', 'PPR-001', 45.99, TRUE, 25, NULL, 'Strong prescription pain medication', 'Prescription-strength pain relief medication for severe pain management. Requires valid prescription from licensed healthcare provider. Please consult with your doctor before use.'),

-- Cold & Flu
(2, 'Cold & Flu Relief Capsules', 'cold-flu-relief-capsules', 'CFR-CAP-50', 15.99, FALSE, 40, NULL, 'Multi-symptom cold and flu relief', 'Provides temporary relief of common cold and flu symptoms including nasal congestion, runny nose, sneezing, minor aches and pains, headache, fever, and sore throat pain.'),

(2, 'Cough Syrup 8oz', 'cough-syrup-8oz', 'CS-8OZ-001', 11.99, FALSE, 30, NULL, 'Effective cough suppressant and expectorant', 'Temporarily relieves cough due to minor throat and bronchial irritation as may occur with a cold. Helps loosen phlegm and thin bronchial secretions to make coughs more productive.'),

(2, 'Throat Lozenges', 'throat-lozenges', 'TL-MEN-20', 6.99, FALSE, 80, NULL, 'Soothing throat lozenges with menthol', 'Temporarily relieves minor discomfort and protection of irritated areas in sore mouth and sore throat. Contains menthol for cooling relief.'),

(2, 'Nasal Decongestant Spray', 'nasal-decongestant-spray', 'NDS-001', 13.99, FALSE, 35, NULL, 'Fast-acting nasal congestion relief', 'Provides temporary relief of nasal congestion due to common cold, hay fever, or other upper respiratory allergies. Fast-acting formula provides relief in minutes.'),

-- Vitamins & Supplements
(3, 'Multivitamin Daily Tablets', 'multivitamin-daily-tablets', 'MV-DAILY-100', 19.99, FALSE, 90, NULL, 'Complete daily multivitamin for adults', 'Comprehensive multivitamin and mineral supplement designed to support overall health and wellness. Contains essential vitamins and minerals including Vitamin A, C, D, E, B-complex, calcium, iron, and zinc.'),

(3, 'Vitamin C 1000mg Tablets', 'vitamin-c-1000mg-tablets', 'VC1000-60', 14.99, FALSE, 70, NULL, 'High-potency Vitamin C for immune support', 'High-potency Vitamin C supplement to support immune system health. Vitamin C is an antioxidant that supports the immune system and helps with the absorption of iron.'),

(3, 'Omega-3 Fish Oil Capsules', 'omega-3-fish-oil-capsules', 'O3-FO-120', 24.99, FALSE, 45, NULL, 'Premium omega-3 fatty acids for heart health', 'High-quality fish oil supplement providing EPA and DHA omega-3 fatty acids. Supports heart health, brain function, and overall wellness. Molecularly distilled for purity.'),

(3, 'Calcium + Vitamin D Tablets', 'calcium-vitamin-d-tablets', 'CAL-VD-100', 16.99, FALSE, 55, NULL, 'Bone health support with calcium and vitamin D', 'Combines calcium carbonate with Vitamin D3 to support bone health and calcium absorption. Essential for maintaining strong bones and teeth throughout life.');

-- Sample orders (for demonstration)
INSERT INTO `orders` (`user_id`, `total`, `status`, `address`, `phone`, `created_at`) VALUES
(1, 28.98, 'SHIPPED', '123 Main St\nAnytown, ST 12345', '+1-555-0123', DATE_SUB(NOW(), INTERVAL 3 DAY)),
(2, 45.97, 'PENDING', '456 Oak Ave\nSomewhere, ST 67890', '+1-555-0456', DATE_SUB(NOW(), INTERVAL 1 DAY)),
(1, 19.99, 'PAID', '123 Main St\nAnytown, ST 12345', '+1-555-0123', DATE_SUB(NOW(), INTERVAL 7 DAY));

-- Sample order items
INSERT INTO `order_items` (`order_id`, `product_id`, `qty`, `price`) VALUES
-- Order 1
(1, 1, 2, 12.99),
(1, 5, 1, 15.99),

-- Order 2  
(2, 9, 1, 19.99),
(2, 11, 1, 24.99),

-- Order 3
(3, 9, 1, 19.99);

-- Create indexes for better performance
CREATE INDEX idx_products_name ON products(name);
CREATE INDEX idx_products_price ON products(price);
CREATE INDEX idx_orders_total ON orders(total);
CREATE INDEX idx_users_created_at ON users(created_at);

-- Update stock after sample orders
UPDATE products SET stock = stock - 2 WHERE id = 1;
UPDATE products SET stock = stock - 1 WHERE id = 5;
UPDATE products SET stock = stock - 2 WHERE id = 9;
UPDATE products SET stock = stock - 1 WHERE id = 11;