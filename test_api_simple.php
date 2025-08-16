<?php
// Simple API test
session_start();
require_once 'bootstrap.php';

// Auto-login for testing
if (!isLoggedIn()) {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE role = 'admin' LIMIT 1");
    $stmt->execute();
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($admin) {
        $_SESSION['user_id'] = $admin['id'];
        $_SESSION['user_role'] = $admin['role'];
        $_SESSION['user_name'] = $admin['name'];
    }
}

// Test the search API logic directly
$query = 'para';

try {
    $stmt = $pdo->prepare("
        SELECT id, name, generic_name, selling_price, stock_quantity, barcode
        FROM medicines 
        WHERE (name LIKE :query1 OR generic_name LIKE :query2 OR barcode LIKE :query3)
        AND status = 'active' 
        AND stock_quantity > 0
        ORDER BY name ASC
        LIMIT 10
    ");
    
    $searchQuery = "%$query%";
    $stmt->execute([
        'query1' => $searchQuery,
        'query2' => $searchQuery,
        'query3' => $searchQuery
    ]);
    $medicines = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $response = [
        'success' => true,
        'medicines' => $medicines
    ];
    
    echo "<h2>API Test Results</h2>";
    echo "<p><strong>Query:</strong> '$query'</p>";
    echo "<p><strong>Found:</strong> " . count($medicines) . " medicines</p>";
    echo "<h3>JSON Response:</h3>";
    echo "<pre>" . json_encode($response, JSON_PRETTY_PRINT) . "</pre>";
    
    if (!empty($medicines)) {
        echo "<h3>Medicine Details:</h3>";
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>ID</th><th>Name</th><th>Generic</th><th>Price</th><th>Stock</th></tr>";
        foreach ($medicines as $medicine) {
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
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}
?>

<style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    table { width: 100%; border-collapse: collapse; margin: 10px 0; }
    th, td { padding: 8px; text-align: left; border: 1px solid #ddd; }
    th { background-color: #f2f2f2; }
    pre { background: #f5f5f5; padding: 10px; border-radius: 4px; overflow-x: auto; }
</style>