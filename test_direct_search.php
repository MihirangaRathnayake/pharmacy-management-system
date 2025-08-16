<?php
require_once 'bootstrap.php';

// Test direct search without API
try {
    echo "<h2>Direct Medicine Search Test</h2>";
    
    // Check if user is logged in
    if (!isLoggedIn()) {
        echo "<p>❌ Not logged in. Please log in first.</p>";
        exit();
    }
    
    echo "<p>✅ User is logged in.</p>";
    
    // Test basic query
    $query = "para";
    echo "<p>Testing search for: '{$query}'</p>";
    
    $stmt = $pdo->prepare("
        SELECT id, name, generic_name, selling_price, stock_quantity, barcode, status
        FROM medicines 
        WHERE (name LIKE :query OR generic_name LIKE :query OR barcode LIKE :query)
        AND status = 'active' 
        AND stock_quantity > 0
        ORDER BY name ASC
        LIMIT 10
    ");
    
    $searchQuery = "%$query%";
    $stmt->execute(['query' => $searchQuery]);
    $medicines = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<p>Found " . count($medicines) . " medicines</p>";
    
    if (!empty($medicines)) {
        echo "<table border='1' style='border-collapse: collapse; width: 100%; margin: 10px 0;'>";
        echo "<tr><th>ID</th><th>Name</th><th>Generic</th><th>Price</th><th>Stock</th><th>Status</th></tr>";
        foreach ($medicines as $medicine) {
            echo "<tr>";
            echo "<td>{$medicine['id']}</td>";
            echo "<td>" . htmlspecialchars($medicine['name']) . "</td>";
            echo "<td>" . htmlspecialchars($medicine['generic_name'] ?? 'N/A') . "</td>";
            echo "<td>Rs " . number_format($medicine['selling_price'], 2) . "</td>";
            echo "<td>{$medicine['stock_quantity']}</td>";
            echo "<td>{$medicine['status']}</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        // Test JSON output
        echo "<h3>JSON Response:</h3>";
        echo "<pre>" . json_encode([
            'success' => true,
            'medicines' => $medicines
        ], JSON_PRETTY_PRINT) . "</pre>";
    }
    
    // Test all medicines
    echo "<h3>All Active Medicines with Stock:</h3>";
    $allStmt = $pdo->query("SELECT id, name, generic_name, selling_price, stock_quantity, status FROM medicines WHERE status = 'active' AND stock_quantity > 0 ORDER BY name");
    $allMedicines = $allStmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<p>Total active medicines with stock: " . count($allMedicines) . "</p>";
    
    if (!empty($allMedicines)) {
        echo "<table border='1' style='border-collapse: collapse; width: 100%; margin: 10px 0;'>";
        echo "<tr><th>ID</th><th>Name</th><th>Generic</th><th>Price</th><th>Stock</th></tr>";
        foreach ($allMedicines as $medicine) {
            echo "<tr>";
            echo "<td>{$medicine['id']}</td>";
            echo "<td>" . htmlspecialchars($medicine['name']) . "</td>";
            echo "<td>" . htmlspecialchars($medicine['generic_name'] ?? 'N/A') . "</td>";
            echo "<td>Rs " . number_format($medicine['selling_price'], 2) . "</td>";
            echo "<td>{$medicine['stock_quantity']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
} catch (Exception $e) {
    echo "<p>❌ Error: " . $e->getMessage() . "</p>";
    echo "<p>Stack trace: " . $e->getTraceAsString() . "</p>";
}
?>

<style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    table { width: 100%; border-collapse: collapse; margin: 10px 0; }
    th, td { padding: 8px; text-align: left; border: 1px solid #ddd; }
    th { background-color: #f2f2f2; }
    pre { background: #f5f5f5; padding: 10px; border-radius: 4px; overflow-x: auto; }
</style>