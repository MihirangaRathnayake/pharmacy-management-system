<?php
// Test the settings URL
echo "Testing settings page at modules/settings/index.php\n\n";

// Check if files exist
$files = [
    'modules/settings/index.php',
    'includes/image_helper.php',
    'bootstrap.php'
];

foreach ($files as $file) {
    if (file_exists($file)) {
        echo "✅ $file exists\n";
    } else {
        echo "❌ $file missing\n";
    }
}

// Test if we can include the bootstrap
try {
    require_once 'bootstrap.php';
    echo "✅ Bootstrap loaded successfully\n";
    
    // Test database connection
    global $pdo;
    if ($pdo) {
        echo "✅ Database connected\n";
    } else {
        echo "❌ Database not connected\n";
    }
    
    // Test auth functions
    if (function_exists('isLoggedIn')) {
        echo "✅ Auth functions available\n";
    } else {
        echo "❌ Auth functions missing\n";
    }
    
    // Test image helper
    if (function_exists('getProfileImageUrl')) {
        echo "✅ Image helper functions available\n";
    } else {
        echo "❌ Image helper functions missing\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error loading bootstrap: " . $e->getMessage() . "\n";
}

echo "\n🎯 To access settings:\n";
echo "1. Make sure you're logged in\n";
echo "2. Go to: http://localhost/modules/settings/index.php\n";
echo "3. Or click Settings from the user dropdown in the dashboard\n";
?>