<?php
require_once 'bootstrap.php';

echo "<h2>Medicine Database Check</h2>";

try {
    // Check total medicines
    $totalStmt = $pdo->query("SELECT COUNT(*) as count FROM medicines");
    $total = $totalStmt->fetch()['count'];
    echo "<p>Total medicines in database: {$total}</p>";
    
    if ($total == 0) {
        echo "<p>❌ No medicines found. Let's insert some sample data.</p>";
        
        // Insert sample medicines
        $sampleMedicines = [
            ['Paracetamol 500mg', 'Paracetamol', 1, 1, 'PAR001', 2.50, 5.00, 100, 20, '2025-12-31', 0],
            ['Amoxicillin 250mg', 'Amoxicillin', 2, 1, 'AMX001', 15.00, 25.00, 50, 10, '2025-06-30', 1],
            ['Vitamin C 1000mg', 'Ascorbic Acid', 3, 2, 'VTC001', 8.00, 15.00, 75, 15, '2026-03-31', 0],
            ['Cough Syrup', 'Dextromethorphan', 4, 2, 'CS001', 12.00, 20.00, 30, 10, '2025-09-30', 0],
            ['Metformin 500mg', 'Metformin HCl', 5, 3, 'MET001', 18.00, 30.00, 40, 15, '2025-11-30', 1]
        ];
        
        $insertStmt = $pdo->prepare("
            INSERT INTO medicines (name, generic_name, category_id, supplier_id, batch_number, purchase_price, selling_price, stock_quantity, min_stock_level, expiry_date, prescription_required) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        foreach ($sampleMedicines as $medicine) {
            $insertStmt->execute($medicine);
        }
        
        echo "<p>✅ Inserted " . count($sampleMedicines) . " sample medicines.</p>";
        
        // Recheck count
        $total = $pdo->query("SELECT COUNT(*) as count FROM medicines")->fetch()['count'];
        echo "<p>New total: {$total}</p>";
    }
    
    // Show all medicines
    $stmt = $pdo->query("
        SELECT m.*, c.name as category_name, s.name as supplier_name 
        FROM medicines m 
        LEFT JOIN categories c ON m.category_id = c.id 
        LEFT JOIN suppliers s ON m.supplier_id = s.id 
        ORDER BY m.name
    ");
    $medicines = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h3>All Medicines:</h3>";
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>ID</th><th>Name</th><th>Generic</th><th>Category</th><th>Price</th><th>Stock</th><th>Status</th></tr>";
    
    foreach ($medicines as $medicine) {
        $rowClass = $medicine['stock_quantity'] > 0 && $medicine['status'] === 'active' ? 'style="background-color: #e8f5e8;"' : '';
        echo "<tr {$rowClass}>";
        echo "<td>{$medicine['id']}</td>";
        echo "<td>" . htmlspecialchars($medicine['name']) . "</td>";
        echo "<td>" . htmlspecialchars($medicine['generic_name'] ?? 'N/A') . "</td>";
        echo "<td>" . htmlspecialchars($medicine['category_name'] ?? 'N/A') . "</td>";
        echo "<td>Rs " . number_format($medicine['selling_price'], 2) . "</td>";
        echo "<td>{$medicine['stock_quantity']}</td>";
        echo "<td>{$medicine['status']}</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Check active medicines with stock
    $activeStmt = $pdo->query("SELECT COUNT(*) as count FROM medicines WHERE status = 'active' AND stock_quantity > 0");
    $activeCount = $activeStmt->fetch()['count'];
    echo "<p><strong>Active medicines with stock: {$activeCount}</strong></p>";
    
    // Test search query
    echo "<h3>Test Search Query:</h3>";
    $searchQuery = "para";
    $searchStmt = $pdo->prepare("
        SELECT id, name, generic_name, selling_price, stock_quantity, barcode
        FROM medicines 
        WHERE (name LIKE :query1 OR generic_name LIKE :query2 OR barcode LIKE :query3)
        AND status = 'active' 
        AND stock_quantity > 0
        ORDER BY name ASC
        LIMIT 10
    ");
    
    $searchParam = "%$searchQuery%";
    $searchStmt->execute([
        'query1' => $searchParam,
        'query2' => $searchParam,
        'query3' => $searchParam
    ]);
    $searchResults = $searchStmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<p>Search for '{$searchQuery}' found " . count($searchResults) . " results:</p>";
    
    if (!empty($searchResults)) {
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>ID</th><th>Name</th><th>Generic</th><th>Price</th><th>Stock</th></tr>";
        foreach ($searchResults as $result) {
            echo "<tr>";
            echo "<td>{$result['id']}</td>";
            echo "<td>" . htmlspecialchars($result['name']) . "</td>";
            echo "<td>" . htmlspecialchars($result['generic_name'] ?? 'N/A') . "</td>";
            echo "<td>Rs " . number_format($result['selling_price'], 2) . "</td>";
            echo "<td>{$result['stock_quantity']}</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        // Show JSON response
        echo "<h4>JSON Response:</h4>";
        echo "<pre>" . json_encode([
            'success' => true,
            'medicines' => $searchResults
        ], JSON_PRETTY_PRINT) . "</pre>";
    }
    
} catch (Exception $e) {
    echo "<p>❌ Error: " . $e->getMessage() . "</p>";
}
?>

<style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    table { width: 100%; border-collapse: collapse; margin: 10px 0; }
    th, td { padding: 8px; text-align: left; border: 1px solid #ddd; }
    th { background-color: #f2f2f2; }
    pre { background: #f5f5f5; padding: 10px; border-radius: 4px; overflow-x: auto; }
</style>