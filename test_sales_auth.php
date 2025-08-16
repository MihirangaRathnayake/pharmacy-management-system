<?php
session_start();
require_once 'bootstrap.php';

echo "<h2>Sales Authentication Test</h2>";

// Check if user is logged in
if (isLoggedIn()) {
    $user = getCurrentUser();
    echo "<p>✅ User is logged in: " . htmlspecialchars($user['name']) . " (Role: " . htmlspecialchars($user['role']) . ")</p>";
    
    // Test API access
    echo "<h3>Testing API Access:</h3>";
    
    // Simulate API call
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
        
        echo "<p>✅ Database query successful. Found " . count($medicines) . " medicines.</p>";
        
        if (!empty($medicines)) {
            echo "<h4>Sample Medicine Data:</h4>";
            echo "<pre>" . json_encode($medicines[0], JSON_PRETTY_PRINT) . "</pre>";
        }
        
    } catch (Exception $e) {
        echo "<p>❌ Database error: " . $e->getMessage() . "</p>";
    }
    
} else {
    echo "<p>❌ User is NOT logged in</p>";
    echo "<p>Please log in first: <a href='auth/login.php'>Login</a></p>";
    
    // Quick login form
    echo "<h3>Quick Login for Testing:</h3>";
    echo '<form method="post" style="margin: 20px 0;">';
    echo '<button type="submit" name="quick_login" style="background: #4CAF50; color: white; padding: 10px 20px; border: none; border-radius: 4px;">Quick Login as Admin</button>';
    echo '</form>';
}

// Handle quick login
if (isset($_POST['quick_login'])) {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE role = 'admin' LIMIT 1");
    $stmt->execute();
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($admin) {
        $_SESSION['user_id'] = $admin['id'];
        $_SESSION['user_role'] = $admin['role'];
        $_SESSION['user_name'] = $admin['name'];
        echo "<script>window.location.reload();</script>";
    }
}

echo "<h3>Direct API Test:</h3>";
echo '<button onclick="testAPI()" style="background: #2196F3; color: white; padding: 10px 20px; border: none; border-radius: 4px; margin: 10px 0;">Test Search API</button>';
echo '<div id="apiResult" style="margin-top: 10px; padding: 10px; background: #f5f5f5; border-radius: 4px;"></div>';

?>

<script>
function testAPI() {
    const resultDiv = document.getElementById('apiResult');
    resultDiv.innerHTML = 'Testing API...';
    
    fetch('api/search_medicines.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ query: 'para' })
    })
    .then(response => {
        console.log('Response status:', response.status);
        return response.json();
    })
    .then(data => {
        console.log('API Response:', data);
        resultDiv.innerHTML = '<h4>API Response:</h4><pre>' + JSON.stringify(data, null, 2) + '</pre>';
    })
    .catch(error => {
        console.error('API Error:', error);
        resultDiv.innerHTML = '<h4>API Error:</h4><p style="color: red;">' + error.message + '</p>';
    });
}
</script>

<style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    pre { background: #f5f5f5; padding: 10px; border-radius: 4px; overflow-x: auto; }
</style>