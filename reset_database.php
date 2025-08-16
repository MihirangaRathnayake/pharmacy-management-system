<?php
/**
 * Database Reset Script
 * Use this to completely reset the database and fix tablespace issues
 */

try {
    // Database connection settings
    $host = 'localhost';
    $username = 'root';
    $password = '';
    $database = 'pharmacy_management';
    
    // Connect to MySQL server
    $pdo = new PDO("mysql:host=$host", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<h2>Resetting Database...</h2>";
    
    // Drop database completely
    $pdo->exec("DROP DATABASE IF EXISTS $database");
    echo "<p>✅ Database dropped successfully</p>";
    
    // Recreate database
    $pdo->exec("CREATE DATABASE $database CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "<p>✅ Database recreated successfully</p>";
    
    echo "<p><strong>Database reset complete!</strong></p>";
    echo "<p><a href='install.php?step=install'>Continue with Installation</a></p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
    echo "<p>Make sure MySQL is running in XAMPP</p>";
}
?>