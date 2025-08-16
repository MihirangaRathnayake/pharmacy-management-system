<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Install Pharmacy Management System</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            margin: 0;
            padding: 20px;
            min-height: 100vh;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
            overflow: hidden;
        }
        .header {
            background: #28a745;
            color: white;
            padding: 30px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 2.5rem;
        }
        .content {
            padding: 30px;
        }
        .step {
            background: #f8f9fa;
            border-left: 4px solid #28a745;
            padding: 20px;
            margin: 20px 0;
            border-radius: 5px;
        }
        .success {
            background: #d4edda;
            border-color: #28a745;
            color: #155724;
        }
        .error {
            background: #f8d7da;
            border-color: #dc3545;
            color: #721c24;
        }
        .warning {
            background: #fff3cd;
            border-color: #ffc107;
            color: #856404;
        }
        .btn {
            display: inline-block;
            padding: 12px 30px;
            background: #28a745;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
            margin: 10px 5px;
            border: none;
            cursor: pointer;
            font-size: 16px;
        }
        .btn:hover {
            background: #218838;
        }
        .btn-secondary {
            background: #6c757d;
        }
        .btn-secondary:hover {
            background: #545b62;
        }
        .credentials {
            background: #e3f2fd;
            border: 1px solid #2196f3;
            padding: 20px;
            border-radius: 5px;
            margin: 20px 0;
        }
        .credentials h3 {
            color: #1976d2;
            margin-top: 0;
        }
        .cred-item {
            background: white;
            padding: 10px;
            margin: 5px 0;
            border-radius: 3px;
            font-family: monospace;
        }
        .progress {
            background: #e9ecef;
            border-radius: 10px;
            height: 20px;
            margin: 20px 0;
            overflow: hidden;
        }
        .progress-bar {
            background: #28a745;
            height: 100%;
            transition: width 0.3s ease;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🏥 Pharmacy Management System</h1>
            <p>Installation & Setup</p>
        </div>
        
        <div class="content">
            <?php
            $step = $_GET['step'] ?? 'check';
            
            if ($step === 'check') {
                echo '<h2>System Requirements Check</h2>';
                
                $required_checks = [
                    'PHP Version >= 7.4' => version_compare(PHP_VERSION, '7.4.0', '>='),
                    'PDO Extension' => extension_loaded('pdo'),
                    'PDO MySQL Extension' => extension_loaded('pdo_mysql'),
                    'JSON Extension' => extension_loaded('json')
                ];
                
                $optional_checks = [
                    'GD Extension (for image processing)' => extension_loaded('gd'),
                    'FileInfo Extension (for file validation)' => extension_loaded('fileinfo')
                ];
                
                $allPassed = true;
                
                echo '<h3>Required Components:</h3>';
                foreach ($required_checks as $check => $passed) {
                    $class = $passed ? 'success' : 'error';
                    $icon = $passed ? '✅' : '❌';
                    echo "<div class='step $class'>$icon $check</div>";
                    if (!$passed) $allPassed = false;
                }
                
                echo '<h3>Optional Components:</h3>';
                foreach ($optional_checks as $check => $passed) {
                    $class = $passed ? 'success' : 'warning';
                    $icon = $passed ? '✅' : '⚠️';
                    echo "<div class='step $class'>$icon $check</div>";
                }
                
                if ($allPassed) {
                    echo '<div class="step success">✅ All requirements met! Ready to install.</div>';
                    echo '<a href="?step=install" class="btn">Continue Installation</a>';
                } else {
                    echo '<div class="step error">❌ Please fix the requirements above before continuing.</div>';
                }
            }
            
            elseif ($step === 'install') {
                echo '<h2>Database Installation</h2>';
                echo '<div class="progress"><div class="progress-bar" style="width: 50%"></div></div>';
                
                try {
                    // Database connection settings
                    $host = 'localhost';
                    $username = 'root';
                    $password = '';
                    $database = 'pharmacy_management';
                    
                    // Connect to MySQL server
                    $pdo = new PDO("mysql:host=$host", $username, $password);
                    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                    
                    echo '<div class="step success">✅ Connected to MySQL server</div>';
                    
                    // Drop and recreate database to avoid tablespace issues
                    $pdo->exec("DROP DATABASE IF EXISTS $database");
                    $pdo->exec("CREATE DATABASE $database CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
                    echo '<div class="step success">✅ Database created: ' . $database . '</div>';
                    
                    // Connect to the database
                    $pdo = new PDO("mysql:host=$host;dbname=$database;charset=utf8mb4", $username, $password);
                    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                    
                    // Create tables
                    $sql = "
                    -- Users table
                    CREATE TABLE IF NOT EXISTS users (
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
                    CREATE TABLE IF NOT EXISTS categories (
                        id INT PRIMARY KEY AUTO_INCREMENT,
                        name VARCHAR(100) NOT NULL,
                        description TEXT,
                        status ENUM('active', 'inactive') DEFAULT 'active',
                        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                    );

                    -- Suppliers table
                    CREATE TABLE IF NOT EXISTS suppliers (
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
                    CREATE TABLE IF NOT EXISTS medicines (
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
                    CREATE TABLE IF NOT EXISTS customers (
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
                    CREATE TABLE IF NOT EXISTS sales (
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
                    CREATE TABLE IF NOT EXISTS sale_items (
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
                    CREATE TABLE IF NOT EXISTS prescriptions (
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

                    -- Stock movements table
                    CREATE TABLE IF NOT EXISTS stock_movements (
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
                    CREATE TABLE IF NOT EXISTS notifications (
                        id INT PRIMARY KEY AUTO_INCREMENT,
                        user_id INT,
                        title VARCHAR(200) NOT NULL,
                        message TEXT NOT NULL,
                        type ENUM('info', 'warning', 'error', 'success') DEFAULT 'info',
                        is_read BOOLEAN DEFAULT FALSE,
                        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                        FOREIGN KEY (user_id) REFERENCES users(id)
                    );

                    -- Settings table
                    CREATE TABLE IF NOT EXISTS settings (
                        id INT PRIMARY KEY AUTO_INCREMENT,
                        setting_key VARCHAR(100) UNIQUE NOT NULL,
                        setting_value TEXT,
                        description TEXT,
                        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                    );
                    ";
                    
                    $pdo->exec($sql);
                    echo '<div class="step success">✅ Database tables created successfully</div>';
                    
                    // Insert sample data
                    $sampleData = "
                    ";
                    
                    $pdo->exec($sql);
                    echo '<div class="step success">✅ Database tables created successfully</div>';
                    
                    // Insert users with properly hashed passwords
                    echo '<div class="step success">✅ Creating default users...</div>';
                    
                    // Hash passwords properly
                    $adminPassword = password_hash('admin123', PASSWORD_DEFAULT);
                    $pharmacistPassword = password_hash('pharma123', PASSWORD_DEFAULT);
                    $customerPassword = password_hash('customer123', PASSWORD_DEFAULT);
                    
                    // Insert users
                    $stmt = $pdo->prepare("
                        INSERT IGNORE INTO users (name, email, password, role, status) VALUES 
                        (?, ?, ?, ?, 'active')
                    ");
                    
                    $stmt->execute(['Admin User', 'admin@pharmacy.com', $adminPassword, 'admin']);
                    $stmt->execute(['Pharmacist', 'pharmacist@pharmacy.com', $pharmacistPassword, 'pharmacist']);
                    $stmt->execute(['Customer', 'customer@pharmacy.com', $customerPassword, 'customer']);
                    
                    echo '<div class="step success">✅ Default users created with proper password hashes</div>';
                    
                    // Continue with other sample data
                    $sampleData = "

                    -- Insert categories
                    INSERT IGNORE INTO categories (name, description) VALUES 
                    ('Pain Relief', 'Medicines for pain management'),
                    ('Antibiotics', 'Antibiotic medications'),
                    ('Vitamins', 'Vitamin supplements'),
                    ('Cold & Flu', 'Cold and flu medications'),
                    ('Diabetes', 'Diabetes management medicines'),
                    ('Heart', 'Cardiovascular medicines'),
                    ('Skin Care', 'Dermatological products'),
                    ('Digestive', 'Digestive system medicines');

                    -- Insert suppliers
                    INSERT IGNORE INTO suppliers (name, contact_person, email, phone, address) VALUES 
                    ('MediCorp Ltd', 'John Smith', 'john@medicorp.com', '+1234567890', '123 Medical Street, City'),
                    ('PharmaSupply Inc', 'Jane Doe', 'jane@pharmasupply.com', '+1234567891', '456 Supply Avenue, City'),
                    ('HealthDistributors', 'Mike Johnson', 'mike@healthdist.com', '+1234567892', '789 Health Boulevard, City');

                    -- Insert sample medicines
                    INSERT IGNORE INTO medicines (name, generic_name, category_id, supplier_id, batch_number, purchase_price, selling_price, stock_quantity, min_stock_level, expiry_date, prescription_required) VALUES 
                    ('Paracetamol 500mg', 'Paracetamol', 1, 1, 'PAR001', 2.50, 5.00, 100, 20, '2025-12-31', FALSE),
                    ('Amoxicillin 250mg', 'Amoxicillin', 2, 1, 'AMX001', 15.00, 25.00, 50, 10, '2025-06-30', TRUE),
                    ('Vitamin C 1000mg', 'Ascorbic Acid', 3, 2, 'VTC001', 8.00, 15.00, 75, 15, '2026-03-31', FALSE),
                    ('Cough Syrup', 'Dextromethorphan', 4, 2, 'CS001', 12.00, 20.00, 30, 10, '2025-09-30', FALSE),
                    ('Metformin 500mg', 'Metformin HCl', 5, 3, 'MET001', 18.00, 30.00, 40, 15, '2025-11-30', TRUE);

                    -- Insert settings
                    INSERT IGNORE INTO settings (setting_key, setting_value, description) VALUES 
                    ('pharmacy_name', 'PharmaCare', 'Name of the pharmacy'),
                    ('pharmacy_address', '123 Main Street, City, State 12345', 'Pharmacy address'),
                    ('pharmacy_phone', '+1234567890', 'Pharmacy contact number'),
                    ('pharmacy_email', 'info@pharmacare.com', 'Pharmacy email address'),
                    ('tax_rate', '18', 'Default tax rate percentage'),
                    ('currency_symbol', 'Rs', 'Currency symbol'),
                    ('low_stock_threshold', '10', 'Default low stock alert threshold'),
                    ('expiry_alert_days', '30', 'Days before expiry to show alert');
                    ";
                    
                    $pdo->exec($sampleData);
                    echo '<div class="step success">✅ Sample data inserted successfully</div>';
                    
                    // Create upload directories
                    $uploadDirs = ['uploads', 'uploads/prescriptions'];
                    foreach ($uploadDirs as $dir) {
                        if (!is_dir($dir)) {
                            mkdir($dir, 0755, true);
                            echo '<div class="step success">✅ Created directory: ' . $dir . '</div>';
                        }
                    }
                    
                    echo '<div class="progress"><div class="progress-bar" style="width: 100%"></div></div>';
                    echo '<a href="?step=complete" class="btn">Complete Installation</a>';
                    
                } catch (Exception $e) {
                    echo '<div class="step error">❌ Installation failed: ' . $e->getMessage() . '</div>';
                    echo '<div class="step warning">⚠️ Make sure MySQL is running in XAMPP</div>';
                }
            }
            
            elseif ($step === 'complete') {
                echo '<h2>🎉 Installation Complete!</h2>';
                echo '<div class="step success">✅ Pharmacy Management System has been installed successfully!</div>';
                
                echo '<div class="credentials">';
                echo '<h3>🔐 Default Login Credentials</h3>';
                echo '<div class="cred-item"><strong>Admin:</strong> admin@pharmacy.com / admin123</div>';
                echo '<div class="cred-item"><strong>Pharmacist:</strong> pharmacist@pharmacy.com / pharma123</div>';
                echo '<div class="cred-item"><strong>Customer:</strong> customer@pharmacy.com / customer123</div>';
                echo '</div>';
                
                echo '<h3>🚀 Quick Links</h3>';
                echo '<a href="index.php" class="btn">Admin Dashboard</a>';
                echo '<a href="customer/index.html" class="btn btn-secondary">Customer Website</a>';
                
                echo '<div class="step warning">';
                echo '<strong>⚠️ Security Note:</strong> Please change the default passwords after logging in!';
                echo '</div>';
            }
            ?>
        </div>
    </div>
</body>
</html>