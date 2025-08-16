<?php
require_once __DIR__ . '/bootstrap.php';

// Simple test to check if settings page works
echo "<!DOCTYPE html>
<html>
<head>
    <title>Settings Test</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; }
        .success { color: green; }
        .error { color: red; }
        .info { color: blue; }
    </style>
</head>
<body>
    <h1>Settings Page Test</h1>";

try {
    global $pdo;
    
    if (!$pdo) {
        echo "<p class='error'>❌ Database connection failed</p>";
        exit();
    }
    
    echo "<p class='success'>✅ Database connection successful</p>";
    
    // Check if user_preferences table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'user_preferences'");
    if ($stmt->rowCount() > 0) {
        echo "<p class='success'>✅ user_preferences table exists</p>";
    } else {
        echo "<p class='error'>❌ user_preferences table missing</p>";
    }
    
    // Check if profile_image column exists in users table
    $stmt = $pdo->query("SHOW COLUMNS FROM users LIKE 'profile_image'");
    if ($stmt->rowCount() > 0) {
        echo "<p class='success'>✅ profile_image column exists in users table</p>";
    } else {
        echo "<p class='error'>❌ profile_image column missing in users table</p>";
    }
    
    // Check if uploads directory exists
    if (is_dir(__DIR__ . '/uploads/profiles')) {
        echo "<p class='success'>✅ Profile uploads directory exists</p>";
    } else {
        echo "<p class='info'>ℹ️ Profile uploads directory will be created automatically</p>";
    }
    
    // Check if settings files exist
    $files = [
        'modules/settings/index.php',
        'modules/settings/settings.js',
        'modules/settings/settings.css',
        'modules/settings/upload_profile_image.php'
    ];
    
    foreach ($files as $file) {
        if (file_exists(__DIR__ . '/' . $file)) {
            echo "<p class='success'>✅ $file exists</p>";
        } else {
            echo "<p class='error'>❌ $file missing</p>";
        }
    }
    
    echo "<h2>Test Results</h2>";
    echo "<p><a href='modules/settings/index.php' target='_blank'>🔗 Open Settings Page</a></p>";
    echo "<p><a href='admin_dashboard.php' target='_blank'>🔗 Open Admin Dashboard</a></p>";
    
} catch (Exception $e) {
    echo "<p class='error'>❌ Error: " . $e->getMessage() . "</p>";
}

echo "</body></html>";
?>