<?php
// Test the search API directly
session_start();
require_once 'bootstrap.php';

// Simulate being logged in for testing
if (!isLoggedIn()) {
    // Quick login as admin for testing
    $stmt = $pdo->prepare("SELECT * FROM users WHERE role = 'admin' LIMIT 1");
    $stmt->execute();
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($admin) {
        $_SESSION['user_id'] = $admin['id'];
        $_SESSION['user_role'] = $admin['role'];
        $_SESSION['user_name'] = $admin['name'];
    }
}

// Simulate the API call
$_SERVER['REQUEST_METHOD'] = 'POST';
$input = json_encode(['query' => 'para']);

// Capture output
ob_start();

// Include the API file
include 'api/search_medicines.php';

$output = ob_get_clean();

echo "<h2>API Test Results</h2>";
echo "<p><strong>Input:</strong> " . htmlspecialchars($input) . "</p>";
echo "<p><strong>Output:</strong></p>";
echo "<pre>" . htmlspecialchars($output) . "</pre>";

// Try to decode the JSON
$decoded = json_decode($output, true);
if ($decoded) {
    echo "<h3>Decoded Response:</h3>";
    echo "<pre>" . print_r($decoded, true) . "</pre>";
    
    if ($decoded['success'] && !empty($decoded['medicines'])) {
        echo "<p>✅ API is working! Found " . count($decoded['medicines']) . " medicines.</p>";
    } else {
        echo "<p>⚠️ API returned success but no medicines found.</p>";
    }
} else {
    echo "<p>❌ Invalid JSON response.</p>";
}
?>

<style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    pre { background: #f5f5f5; padding: 10px; border-radius: 4px; overflow-x: auto; }
</style>