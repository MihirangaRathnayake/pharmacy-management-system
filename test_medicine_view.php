<?php
require_once 'bootstrap.php';

// Test database connection and medicine query
try {
    echo "<h2>Testing Medicine Database Query</h2>";
    
    // Test basic connection
    $testQuery = $pdo->query("SELECT COUNT(*) as count FROM medicines");
    $count = $testQuery->fetch()['count'];
    echo "<p>✅ Database connection successful. Found {$count} medicines.</p>";
    
    // Test medicine structure
    $structureQuery = $pdo->query("DESCRIBE medicines");
    $columns = $structureQuery->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h3>Medicine Table Structure:</h3>";
    echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
    foreach ($columns as $column) {
        echo "<tr>";
        echo "<td>{$column['Field']}</td>";
        echo "<td>{$column['Type']}</td>";
        echo "<td>{$column['Null']}</td>";
        echo "<td>{$column['Key']}</td>";
        echo "<td>{$column['Default']}</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Test sample medicine query
    if ($count > 0) {
        echo "<h3>Sample Medicine Data:</h3>";
        $sampleQuery = $pdo->query("
            SELECT m.*, c.name as category_name, s.name as supplier_name 
            FROM medicines m 
            LEFT JOIN categories c ON m.category_id = c.id 
            LEFT JOIN suppliers s ON m.supplier_id = s.id 
            LIMIT 1
        ");
        $sample = $sampleQuery->fetch(PDO::FETCH_ASSOC);
        
        if ($sample) {
            echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
            foreach ($sample as $key => $value) {
                echo "<tr><td><strong>{$key}</strong></td><td>" . htmlspecialchars($value ?? 'NULL') . "</td></tr>";
            }
            echo "</table>";
        }
    }
    
} catch (Exception $e) {
    echo "<p>❌ Error: " . $e->getMessage() . "</p>";
}
?>

<style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    table { width: 100%; max-width: 800px; }
    th, td { padding: 8px; text-align: left; border: 1px solid #ddd; }
    th { background-color: #f2f2f2; }
</style>