<?php
require_once 'bootstrap.php';

echo "Testing correct URL structure...\n\n";

// Test URL helper functions
echo "Base URL: " . getBaseUrl() . "\n";
echo "Settings URL: " . moduleUrl('settings') . "\n";
echo "Dashboard URL: " . url('admin_dashboard.php') . "\n";

// Test if settings file exists
$settingsPath = 'modules/settings/index.php';
if (file_exists($settingsPath)) {
    echo "✅ Settings file exists at: $settingsPath\n";
} else {
    echo "❌ Settings file missing at: $settingsPath\n";
}

// Test server info
echo "\nServer Info:\n";
echo "HTTP_HOST: " . $_SERVER['HTTP_HOST'] . "\n";
echo "SCRIPT_NAME: " . $_SERVER['SCRIPT_NAME'] . "\n";
echo "REQUEST_URI: " . ($_SERVER['REQUEST_URI'] ?? 'Not set') . "\n";

echo "\n🎯 Based on your setup, the correct URLs should be:\n";
echo "Settings: " . moduleUrl('settings') . "\n";
echo "Dashboard: " . url('admin_dashboard.php') . "\n";
?>