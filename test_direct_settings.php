<?php
// Test direct access to settings page
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "Testing direct access to settings page...\n\n";

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

try {
    require_once 'bootstrap.php';
    
    // Auto-login for testing
    global $pdo;
    if ($pdo) {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE role = 'admin' LIMIT 1");
        $stmt->execute();
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($admin) {
            $_SESSION['user_id'] = $admin['id'];
            $_SESSION['user_role'] = $admin['role'];
            echo "✅ Auto-logged in as: " . $admin['name'] . "\n";
        }
    }
    
    // Test if we can access the settings page
    ob_start();
    $_SERVER['REQUEST_METHOD'] = 'GET'; // Simulate GET request
    include 'modules/settings/index.php';
    $output = ob_get_clean();
    
    echo "Settings page output length: " . strlen($output) . " characters\n";
    
    if (strlen($output) > 1000) {
        echo "✅ Settings page loaded successfully!\n";
        echo "✅ Page contains HTML content\n";
        
        // Check for key elements
        if (strpos($output, 'Settings') !== false) {
            echo "✅ Page title found\n";
        }
        if (strpos($output, 'Profile') !== false) {
            echo "✅ Profile section found\n";
        }
        if (strpos($output, 'Security') !== false) {
            echo "✅ Security section found\n";
        }
        if (strpos($output, 'Preferences') !== false) {
            echo "✅ Preferences section found\n";
        }
    } else {
        echo "❌ Settings page output too short\n";
        echo "Output: " . substr($output, 0, 500) . "\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}

echo "\n🎯 The settings page should now work at:\n";
echo "http://localhost/modules/settings/index.php\n";
?>