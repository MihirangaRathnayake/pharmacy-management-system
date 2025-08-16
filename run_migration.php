<?php
require_once __DIR__ . '/bootstrap.php';

try {
    global $pdo;
    
    if (!$pdo) {
        throw new Exception('Database connection not available');
    }
    
    // Read and execute the migration
    $migrationFile = __DIR__ . '/database/migrations/add_user_preferences.sql';
    
    if (!file_exists($migrationFile)) {
        throw new Exception('Migration file not found');
    }
    
    $sql = file_get_contents($migrationFile);
    
    // Split by semicolon and execute each statement
    $statements = array_filter(array_map('trim', explode(';', $sql)));
    
    foreach ($statements as $statement) {
        if (!empty($statement) && !str_starts_with($statement, '--')) {
            $pdo->exec($statement);
            echo "Executed: " . substr($statement, 0, 50) . "...\n";
        }
    }
    
    echo "Migration completed successfully!\n";
    
} catch (Exception $e) {
    echo "Migration failed: " . $e->getMessage() . "\n";
}