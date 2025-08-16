<?php
require_once __DIR__ . '/bootstrap.php';

try {
    global $pdo;
    
    if (!$pdo) {
        throw new Exception('Database connection not available');
    }
    
    echo "Starting database fixes...\n";
    
    // Check if profile_image column exists
    $stmt = $pdo->query("SHOW COLUMNS FROM users LIKE 'profile_image'");
    if ($stmt->rowCount() == 0) {
        echo "Adding profile_image column to users table...\n";
        $pdo->exec("ALTER TABLE users ADD COLUMN profile_image VARCHAR(255) AFTER address");
        echo "✅ Added profile_image column\n";
    } else {
        echo "✅ profile_image column already exists\n";
    }
    
    // Check if user_preferences table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'user_preferences'");
    if ($stmt->rowCount() == 0) {
        echo "Creating user_preferences table...\n";
        $pdo->exec("
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
            )
        ");
        echo "✅ Created user_preferences table\n";
    } else {
        echo "✅ user_preferences table already exists\n";
    }
    
    // Create uploads directories
    $dirs = [
        __DIR__ . '/uploads',
        __DIR__ . '/uploads/profiles',
        __DIR__ . '/uploads/prescriptions',
        __DIR__ . '/uploads/medicines'
    ];
    
    foreach ($dirs as $dir) {
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
            echo "✅ Created directory: " . basename($dir) . "\n";
        } else {
            echo "✅ Directory exists: " . basename($dir) . "\n";
        }
    }
    
    echo "\n🎉 Database setup completed successfully!\n";
    echo "You can now access the settings page at: modules/settings/index.php\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}