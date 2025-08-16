-- Pharmacy Management System Database Schema

CREATE DATABASE IF NOT EXISTS pharmacy_management;
USE pharmacy_management;

-- Users table
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'pharmacist', 'customer') NOT NULL DEFAULT 'customer',
    phone VARCHAR(20),
    address TEXT,
    profile_image VARCHAR(255),
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Categories table
CREATE TABLE categories (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Suppliers table
CREATE TABLE suppliers (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    contact_person VARCHAR(100),
    email VARCHAR(100),
    phone VARCHAR(20),
    address TEXT,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Medicines table
CREATE TABLE medicines (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(200) NOT NULL,
    generic_name VARCHAR(200),
    category_id INT,
    supplier_id INT,
    batch_number VARCHAR(50),
    barcode VARCHAR(100),
    description TEXT,
    dosage VARCHAR(100),
    unit VARCHAR(50) DEFAULT 'piece',
    purchase_price DECIMAL(10,2) NOT NULL,
    selling_price DECIMAL(10,2) NOT NULL,
    stock_quantity INT DEFAULT 0,
    min_stock_level INT DEFAULT 10,
    max_stock_level INT DEFAULT 1000,
    expiry_date DATE,
    manufacture_date DATE,
    prescription_required BOOLEAN DEFAULT FALSE,
    image VARCHAR(255),
    status ENUM('active', 'inactive', 'discontinued') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id),
    FOREIGN KEY (supplier_id) REFERENCES suppliers(id),
    INDEX idx_name (name),
    INDEX idx_barcode (barcode),
    INDEX idx_expiry (expiry_date)
);

-- Customers table
CREATE TABLE customers (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    customer_code VARCHAR(20) UNIQUE,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100),
    phone VARCHAR(20),
    address TEXT,
    date_of_birth DATE,
    gender ENUM('male', 'female', 'other'),
    emergency_contact VARCHAR(100),
    allergies TEXT,
    medical_conditions TEXT,
    loyalty_points INT DEFAULT 0,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    INDEX idx_customer_code (customer_code),
    INDEX idx_phone (phone)
);

-- Sales table
CREATE TABLE sales (
    id INT PRIMARY KEY AUTO_INCREMENT,
    invoice_number VARCHAR(50) UNIQUE NOT NULL,
    customer_id INT,
    user_id INT NOT NULL,
    sale_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    subtotal DECIMAL(10,2) NOT NULL,
    tax_amount DECIMAL(10,2) DEFAULT 0,
    discount_amount DECIMAL(10,2) DEFAULT 0,
    total_amount DECIMAL(10,2) NOT NULL,
    payment_method ENUM('cash', 'card', 'upi', 'online') DEFAULT 'cash',
    payment_status ENUM('pending', 'paid', 'partial', 'refunded') DEFAULT 'paid',
    notes TEXT,
    status ENUM('completed', 'cancelled', 'returned') DEFAULT 'completed',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES customers(id),
    FOREIGN KEY (user_id) REFERENCES users(id),
    INDEX idx_invoice (invoice_number),
    INDEX idx_date (sale_date)
);

-- Sale items table
CREATE TABLE sale_items (
    id INT PRIMARY KEY AUTO_INCREMENT,
    sale_id INT NOT NULL,
    medicine_id INT NOT NULL,
    quantity INT NOT NULL,
    unit_price DECIMAL(10,2) NOT NULL,
    total_price DECIMAL(10,2) NOT NULL,
    batch_number VARCHAR(50),
    expiry_date DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (sale_id) REFERENCES sales(id) ON DELETE CASCADE,
    FOREIGN KEY (medicine_id) REFERENCES medicines(id)
);

-- Prescriptions table
CREATE TABLE prescriptions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    customer_id INT NOT NULL,
    doctor_name VARCHAR(100),
    prescription_date DATE,
    image_path VARCHAR(255),
    notes TEXT,
    status ENUM('pending', 'verified', 'processed', 'rejected') DEFAULT 'pending',
    verified_by INT,
    verified_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES customers(id),
    FOREIGN KEY (verified_by) REFERENCES users(id)
);

-- Prescription items table
CREATE TABLE prescription_items (
    id INT PRIMARY KEY AUTO_INCREMENT,
    prescription_id INT NOT NULL,
    medicine_id INT,
    medicine_name VARCHAR(200),
    dosage VARCHAR(100),
    quantity INT,
    instructions TEXT,
    status ENUM('pending', 'available', 'unavailable') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (prescription_id) REFERENCES prescriptions(id) ON DELETE CASCADE,
    FOREIGN KEY (medicine_id) REFERENCES medicines(id)
);

-- Stock movements table
CREATE TABLE stock_movements (
    id INT PRIMARY KEY AUTO_INCREMENT,
    medicine_id INT NOT NULL,
    movement_type ENUM('in', 'out', 'adjustment') NOT NULL,
    quantity INT NOT NULL,
    reference_type ENUM('purchase', 'sale', 'adjustment', 'return', 'expired') NOT NULL,
    reference_id INT,
    notes TEXT,
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (medicine_id) REFERENCES medicines(id),
    FOREIGN KEY (created_by) REFERENCES users(id)
);

-- Notifications table
CREATE TABLE notifications (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    title VARCHAR(200) NOT NULL,
    message TEXT NOT NULL,
    type ENUM('info', 'warning', 'error', 'success') DEFAULT 'info',
    is_read BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- System settings table
CREATE TABLE settings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    setting_key VARCHAR(100) UNIQUE NOT NULL,
    setting_value TEXT,
    description TEXT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insert default admin user
INSERT INTO users (name, email, password, role) VALUES 
('Admin User', 'admin@pharmacy.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin'),
('Pharmacist', 'pharmacist@pharmacy.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'pharmacist'),
('Customer', 'customer@pharmacy.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer');

-- Insert default categories
INSERT INTO categories (name, description) VALUES 
('Pain Relief', 'Medicines for pain management'),
('Antibiotics', 'Antibiotic medications'),
('Vitamins', 'Vitamin supplements'),
('Cold & Flu', 'Cold and flu medications'),
('Diabetes', 'Diabetes management medicines'),
('Heart', 'Cardiovascular medicines'),
('Skin Care', 'Dermatological products'),
('Digestive', 'Digestive system medicines');

-- Insert default suppliers
INSERT INTO suppliers (name, contact_person, email, phone, address) VALUES 
('MediCorp Ltd', 'John Smith', 'john@medicorp.com', '+1234567890', '123 Medical Street, City'),
('PharmaSupply Inc', 'Jane Doe', 'jane@pharmasupply.com', '+1234567891', '456 Supply Avenue, City'),
('HealthDistributors', 'Mike Johnson', 'mike@healthdist.com', '+1234567892', '789 Health Boulevard, City');

-- Insert sample medicines
INSERT INTO medicines (name, generic_name, category_id, supplier_id, batch_number, purchase_price, selling_price, stock_quantity, min_stock_level, expiry_date, prescription_required) VALUES 
('Paracetamol 500mg', 'Paracetamol', 1, 1, 'PAR001', 2.50, 5.00, 100, 20, '2025-12-31', FALSE),
('Amoxicillin 250mg', 'Amoxicillin', 2, 1, 'AMX001', 15.00, 25.00, 50, 10, '2025-06-30', TRUE),
('Vitamin C 1000mg', 'Ascorbic Acid', 3, 2, 'VTC001', 8.00, 15.00, 75, 15, '2026-03-31', FALSE),
('Cough Syrup', 'Dextromethorphan', 4, 2, 'CS001', 12.00, 20.00, 30, 10, '2025-09-30', FALSE),
('Metformin 500mg', 'Metformin HCl', 5, 3, 'MET001', 18.00, 30.00, 40, 15, '2025-11-30', TRUE);

-- User preferences table
CREATE TABLE user_preferences (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    theme ENUM('light', 'dark', 'auto') DEFAULT 'light',
    language VARCHAR(10) DEFAULT 'en',
    timezone VARCHAR(50) DEFAULT 'Asia/Kolkata',
    notifications BOOLEAN DEFAULT TRUE,
    email_notifications BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_preferences (user_id)
);

-- Insert default settings
INSERT INTO settings (setting_key, setting_value, description) VALUES 
('pharmacy_name', 'PharmaCare', 'Name of the pharmacy'),
('pharmacy_address', '123 Main Street, City, State 12345', 'Pharmacy address'),
('pharmacy_phone', '+1234567890', 'Pharmacy contact number'),
('pharmacy_email', 'info@pharmacare.com', 'Pharmacy email address'),
('tax_rate', '18', 'Default tax rate percentage'),
('currency_symbol', 'Rs', 'Currency symbol'),
('low_stock_threshold', '10', 'Default low stock alert threshold'),
('expiry_alert_days', '30', 'Days before expiry to show alert');