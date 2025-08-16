<?php
// Debug the settings page
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "Starting debug...\n";

try {
    // Test if we can include bootstrap
    require_once __DIR__ . '/bootstrap.php';
    echo "✅ Bootstrap loaded\n";
    
    // Test authentication functions
    if (function_exists('isLoggedIn')) {
        echo "✅ Auth functions available\n";
        
        if (isLoggedIn()) {
            echo "✅ User is logged in\n";
            $user = getCurrentUser();
            if ($user) {
                echo "✅ User data: " . $user['name'] . "\n";
            }
        } else {
            echo "❌ User not logged in\n";
        }
    } else {
        echo "❌ Auth functions missing\n";
    }
    
    // Test database
    global $pdo;
    if ($pdo) {
        echo "✅ Database connected\n";
    } else {
        echo "❌ Database not connected\n";
    }
    
    // Test if we can start output buffering and include the settings page
    ob_start();
    
    // Simulate being logged in for testing
    if (!isLoggedIn()) {
        $_SESSION['user_id'] = 1; // Force login for testing
    }
    
    include 'modules/settings/index.php';
    $output = ob_get_clean();
    
    echo "Settings page output length: " . strlen($output) . "\n";
    
    if (strlen($output) < 500) {
        echo "Full output:\n" . $output . "\n";
    } else {
        echo "First 500 chars:\n" . substr($output, 0, 500) . "\n";
        echo "Last 200 chars:\n" . substr($output, -200) . "\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
?>