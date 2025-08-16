<?php
require_once 'bootstrap.php';

try {
    global $pdo;
    
    echo "Updating user_preferences table...\n";
    
    $columns = [
        'low_stock_alerts BOOLEAN DEFAULT TRUE',
        'expiry_alerts BOOLEAN DEFAULT TRUE', 
        'sales_reports BOOLEAN DEFAULT TRUE',
        'new_orders BOOLEAN DEFAULT TRUE',
        'compact_view BOOLEAN DEFAULT FALSE',
        'show_tooltips BOOLEAN DEFAULT TRUE'
    ];
    
    foreach ($columns as $column) {
        try {
            $columnName = explode(' ', $column)[0];
            
            // Check if column exists
            $stmt = $pdo->query("SHOW COLUMNS FROM user_preferences LIKE '$columnName'");
            if ($stmt->rowCount() == 0) {
                $pdo->exec("ALTER TABLE user_preferences ADD COLUMN $column");
                echo "✅ Added column: $columnName\n";
            } else {
                echo "✅ Column already exists: $columnName\n";
            }
        } catch (Exception $e) {
            echo "❌ Error adding $columnName: " . $e->getMessage() . "\n";
        }
    }
    
    echo "\n🎉 Database update completed!\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>