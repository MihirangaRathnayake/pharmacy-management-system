<?php
require_once 'bootstrap.php';

// Test medicine search functionality
try {
    echo "<h2>Testing Medicine Search</h2>";
    
    // Test if we have any medicines
    $countQuery = $pdo->query("SELECT COUNT(*) as count FROM medicines WHERE status = 'active' AND stock_quantity > 0");
    $count = $countQuery->fetch()['count'];
    echo "<p>✅ Found {$count} active medicines with stock.</p>";
    
    if ($count > 0) {
        // Test search query
        $searchQuery = "a"; // Search for medicines containing 'a'
        $stmt = $pdo->prepare("
            SELECT id, name, generic_name, selling_price, stock_quantity, barcode
            FROM medicines 
            WHERE (name LIKE :query OR generic_name LIKE :query OR barcode LIKE :query)
            AND status = 'active' 
            AND stock_quantity > 0
            ORDER BY name ASC
            LIMIT 10
        ");
        
        $searchParam = "%$searchQuery%";
        $stmt->execute(['query' => $searchParam]);
        $medicines = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<h3>Search Results for '{$searchQuery}':</h3>";
        if (!empty($medicines)) {
            echo "<table border='1' style='border-collapse: collapse; margin: 10px 0; width: 100%;'>";
            echo "<tr><th>ID</th><th>Name</th><th>Generic Name</th><th>Price</th><th>Stock</th><th>Barcode</th></tr>";
            foreach ($medicines as $medicine) {
                echo "<tr>";
                echo "<td>{$medicine['id']}</td>";
                echo "<td>" . htmlspecialchars($medicine['name']) . "</td>";
                echo "<td>" . htmlspecialchars($medicine['generic_name'] ?? 'N/A') . "</td>";
                echo "<td>Rs " . number_format($medicine['selling_price'], 2) . "</td>";
                echo "<td>{$medicine['stock_quantity']}</td>";
                echo "<td>" . htmlspecialchars($medicine['barcode'] ?? 'N/A') . "</td>";
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "<p>❌ No medicines found for search query.</p>";
        }
        
        // Test the API directly
        echo "<h3>Testing API Response:</h3>";
        $testData = ['query' => $searchQuery];
        
        // Simulate the API call
        $_POST = [];
        $input = json_encode($testData);
        
        // Capture the API output
        ob_start();
        
        // Simulate the search API logic
        if (strlen($searchQuery) >= 2) {
            $stmt = $pdo->prepare("
                SELECT id, name, generic_name, selling_price, stock_quantity, barcode
                FROM medicines 
                WHERE (name LIKE :query OR generic_name LIKE :query OR barcode LIKE :query)
                AND status = 'active' 
                AND stock_quantity > 0
                ORDER BY name ASC
                LIMIT 10
            ");
            
            $searchParam = "%$searchQuery%";
            $stmt->execute(['query' => $searchParam]);
            $medicines = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $response = [
                'success' => true,
                'medicines' => $medicines
            ];
            
            echo "<pre>" . json_encode($response, JSON_PRETTY_PRINT) . "</pre>";
        }
        
        ob_end_flush();
    }
    
} catch (Exception $e) {
    echo "<p>❌ Error: " . $e->getMessage() . "</p>";
}
?>

<style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    table { width: 100%; max-width: 1000px; }
    th, td { padding: 8px; text-align: left; border: 1px solid #ddd; }
    th { background-color: #f2f2f2; }
    pre { background: #f5f5f5; padding: 10px; border-radius: 4px; overflow-x: auto; }
</style>