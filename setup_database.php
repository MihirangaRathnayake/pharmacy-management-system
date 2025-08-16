<?php
// Database setup script for Pharmacy Management System

$host = 'localhost';
$username = 'root';
$password = '';
$database = 'pharmacy_management';

try {
    // Connect to MySQL server (without database)
    $pdo = new PDO("mysql:host=$host", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<h2>Setting up Pharmacy Management System Database...</h2>";
    
    // Create database
    $pdo->exec("CREATE DATABASE IF NOT EXISTS $database");
    echo "<p>✅ Database '$database' created successfully!</p>";
    
    // Connect to the new database
    $pdo = new PDO("mysql:host=$host;dbname=$database", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Read and execute SQL schema
    $sqlFile = 'database/schema.sql';
    if (file_exists($sqlFile)) {
        $sql = file_get_contents($sqlFile);
        
        // Split SQL into individual statements
        $statements = array_filter(array_map('trim', explode(';', $sql)));
        
        foreach ($statements as $statement) {
            if (!empty($statement)) {
                $pdo->exec($statement);
            }
        }
        
        echo "<p>✅ Database schema created successfully!</p>";
        echo "<p>✅ Sample data inserted successfully!</p>";
        
        // Verify tables were created
        $stmt = $pdo->query("SHOW TABLES");
        $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        echo "<h3>Created Tables:</h3>";
        echo "<ul>";
        foreach ($tables as $table) {
            echo "<li>$table</li>";
        }
        echo "</ul>";
        
        echo "<h3>Default Login Credentials:</h3>";
        echo "<div style='background: #f0f8ff; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
        echo "<strong>Admin:</strong> admin@pharmacy.com / admin123<br>";
        echo "<strong>Pharmacist:</strong> pharmacist@pharmacy.com / pharma123<br>";
        echo "<strong>Customer:</strong> customer@pharmacy.com / customer123";
        echo "</div>";
        
        echo "<h3>Next Steps:</h3>";
        echo "<ol>";
        echo "<li>Visit <a href='index.php' target='_blank'>Admin Dashboard</a></li>";
        echo "<li>Visit <a href='customer/index.html' target='_blank'>Customer Website</a></li>";
        echo "<li>Login with the credentials above</li>";
        echo "</ol>";
        
    } else {
        echo "<p>❌ Error: schema.sql file not found!</p>";
    }
    
} catch (PDOException $e) {
    echo "<p>❌ Database Error: " . $e->getMessage() . "</p>";
    echo "<p>Please make sure MySQL is running in XAMPP.</p>";
}
?>

<style>
body {
    font-family: Arial, sans-serif;
    max-width: 800px;
    margin: 50px auto;
    padding: 20px;
    background: #f5f5f5;
}
h2 {
    color: #28a745;
    border-bottom: 2px solid #28a745;
    padding-bottom: 10px;
}
p {
    background: white;
    padding: 10px;
    border-radius: 5px;
    margin: 10px 0;
}
ul, ol {
    background: white;
    padding: 15px;
    border-radius: 5px;
}
a {
    color: #28a745;
    text-decoration: none;
    font-weight: bold;
}
a:hover {
    text-decoration: underline;
}
</style>