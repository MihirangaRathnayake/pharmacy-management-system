<?php
// Test the actual API endpoint
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

echo "<h2>Testing API Endpoint</h2>";

// Simulate the API request
$_SERVER['REQUEST_METHOD'] = 'POST';
$input = json_encode(['query' => 'para']);

// Capture the API output
ob_start();

// Simulate the input stream
$temp = tmpfile();
fwrite($temp, $input);
rewind($temp);

// Mock the php://input
$originalInput = 'php://input';

// Include the API file
try {
    // Set up the input for the API
    file_put_contents('php://temp', $input);
    
    // Capture output from the API
    include 'api/search_medicines.php';
    
} catch (Exception $e) {
    echo "Error including API: " . $e->getMessage();
}

$output = ob_get_clean();

echo "<p><strong>Input:</strong> " . htmlspecialchars($input) . "</p>";
echo "<p><strong>Raw Output:</strong></p>";
echo "<pre>" . htmlspecialchars($output) . "</pre>";

// Try to parse the JSON
$decoded = json_decode($output, true);
if ($decoded) {
    echo "<h3>Parsed Response:</h3>";
    echo "<pre>" . print_r($decoded, true) . "</pre>";
    
    if ($decoded['success'] && !empty($decoded['medicines'])) {
        echo "<p style='color: green;'>✅ API is working correctly!</p>";
    } else {
        echo "<p style='color: orange;'>⚠️ API returned success but no medicines found.</p>";
    }
} else {
    echo "<p style='color: red;'>❌ Invalid JSON response from API.</p>";
}

// Clean up
if (isset($temp)) {
    fclose($temp);
}
?>

<style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    pre { background: #f5f5f5; padding: 10px; border-radius: 4px; overflow-x: auto; }
</style>