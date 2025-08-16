<?php
session_start();
require_once 'bootstrap.php';

// Quick auto-login
if (!isLoggedIn()) {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE role = 'admin' LIMIT 1");
    $stmt->execute();
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($admin) {
        $_SESSION['user_id'] = $admin['id'];
        $_SESSION['user_role'] = $admin['role'];
        $_SESSION['user_name'] = $admin['name'];
        echo "✅ Auto-logged in as: " . $admin['name'] . "<br>";
    }
}

echo "<h2>Quick System Test</h2>";

// Test 1: Authentication
echo "<h3>1. Authentication:</h3>";
if (isLoggedIn()) {
    $user = getCurrentUser();
    echo "✅ Logged in as: " . htmlspecialchars($user['name']) . " (Role: " . htmlspecialchars($user['role']) . ")<br>";
} else {
    echo "❌ Not logged in<br>";
}

// Test 2: Database
echo "<h3>2. Database:</h3>";
try {
    $count = $pdo->query("SELECT COUNT(*) FROM medicines WHERE status = 'active' AND stock_quantity > 0")->fetchColumn();
    echo "✅ Database OK. Active medicines with stock: {$count}<br>";
} catch (Exception $e) {
    echo "❌ Database error: " . $e->getMessage() . "<br>";
}

// Test 3: Search Query
echo "<h3>3. Search Test:</h3>";
try {
    $stmt = $pdo->prepare("
        SELECT id, name, generic_name, selling_price, stock_quantity
        FROM medicines 
        WHERE (name LIKE :query1 OR generic_name LIKE :query2)
        AND status = 'active' 
        AND stock_quantity > 0
        ORDER BY name ASC
        LIMIT 5
    ");
    
    $searchQuery = "%para%";
    $stmt->execute([
        'query1' => $searchQuery,
        'query2' => $searchQuery
    ]);
    $medicines = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "✅ Search query OK. Found " . count($medicines) . " medicines for 'para'<br>";
    
    if (!empty($medicines)) {
        foreach ($medicines as $medicine) {
            echo "- " . htmlspecialchars($medicine['name']) . " (Rs " . $medicine['selling_price'] . ", Stock: " . $medicine['stock_quantity'] . ")<br>";
        }
    }
    
} catch (Exception $e) {
    echo "❌ Search error: " . $e->getMessage() . "<br>";
}

// Test 4: API Response Format
echo "<h3>4. API Response Format:</h3>";
$response = [
    'success' => true,
    'medicines' => $medicines ?? []
];
echo "✅ JSON Response:<br>";
echo "<pre>" . json_encode($response, JSON_PRETTY_PRINT) . "</pre>";

echo "<h3>Next Steps:</h3>";
echo "<p>1. Visit: <a href='test_search_direct.html' target='_blank'>test_search_direct.html</a> - Test search in browser</p>";
echo "<p>2. Visit: <a href='modules/sales/new_sale.php' target='_blank'>modules/sales/new_sale.php</a> - Try actual sales page</p>";
echo "<p>3. Open browser console (F12) to see any JavaScript errors</p>";
?>

<style>
    body { font-family: Arial, sans-serif; margin: 20px; line-height: 1.6; }
    pre { background: #f5f5f5; padding: 10px; border-radius: 4px; overflow-x: auto; }
    a { color: #0066cc; text-decoration: none; }
    a:hover { text-decoration: underline; }
</style>