<?php
require_once __DIR__ . '/bootstrap.php';

echo "<!DOCTYPE html>
<html>
<head>
    <title>Complete Settings Test</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; }
        .success { color: green; }
        .error { color: red; }
        .info { color: blue; }
        .test-section { margin: 20px 0; padding: 20px; border: 1px solid #ddd; border-radius: 8px; }
    </style>
</head>
<body>
    <h1>🧪 Complete Settings System Test</h1>";

try {
    global $pdo;
    
    if (!$pdo) {
        echo "<p class='error'>❌ Database connection failed</p>";
        exit();
    }
    
    echo "<div class='test-section'>
        <h2>Database Tests</h2>";
    
    // Test user_preferences table
    $stmt = $pdo->query("SHOW TABLES LIKE 'user_preferences'");
    if ($stmt->rowCount() > 0) {
        echo "<p class='success'>✅ user_preferences table exists</p>";
        
        // Check all columns
        $stmt = $pdo->query("DESCRIBE user_preferences");
        $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        $expectedColumns = [
            'id', 'user_id', 'theme', 'language', 'timezone', 
            'notifications', 'email_notifications', 'low_stock_alerts',
            'expiry_alerts', 'sales_reports', 'new_orders', 
            'compact_view', 'show_tooltips', 'created_at', 'updated_at'
        ];
        
        foreach ($expectedColumns as $col) {
            if (in_array($col, $columns)) {
                echo "<p class='success'>✅ Column exists: $col</p>";
            } else {
                echo "<p class='error'>❌ Column missing: $col</p>";
            }
        }
    } else {
        echo "<p class='error'>❌ user_preferences table missing</p>";
    }
    
    // Test profile_image column in users table
    $stmt = $pdo->query("SHOW COLUMNS FROM users LIKE 'profile_image'");
    if ($stmt->rowCount() > 0) {
        echo "<p class='success'>✅ profile_image column exists in users table</p>";
    } else {
        echo "<p class='error'>❌ profile_image column missing in users table</p>";
    }
    
    echo "</div>";
    
    // Test file structure
    echo "<div class='test-section'>
        <h2>File Structure Tests</h2>";
    
    $files = [
        'modules/settings/index.php' => 'Main settings page',
        'modules/settings/settings.js' => 'JavaScript functionality',
        'modules/settings/settings.css' => 'Custom styling',
        'modules/settings/upload_profile_image.php' => 'Profile image upload',
        'uploads/profiles' => 'Profile images directory',
        'api/notifications.php' => 'Notifications API'
    ];
    
    foreach ($files as $file => $description) {
        if (file_exists(__DIR__ . '/' . $file) || is_dir(__DIR__ . '/' . $file)) {
            echo "<p class='success'>✅ $description: $file</p>";
        } else {
            echo "<p class='error'>❌ Missing: $file ($description)</p>";
        }
    }
    
    echo "</div>";
    
    // Test authentication
    echo "<div class='test-section'>
        <h2>Authentication Tests</h2>";
    
    if (function_exists('isLoggedIn')) {
        echo "<p class='success'>✅ isLoggedIn function exists</p>";
        
        if (isLoggedIn()) {
            echo "<p class='success'>✅ User is logged in</p>";
            $user = getCurrentUser();
            if ($user) {
                echo "<p class='success'>✅ User data retrieved: " . htmlspecialchars($user['name']) . "</p>";
            }
        } else {
            echo "<p class='info'>ℹ️ User not logged in - <a href='quick_login.php'>Click here to login</a></p>";
        }
    } else {
        echo "<p class='error'>❌ Authentication functions missing</p>";
    }
    
    echo "</div>";
    
    // Test image helper
    echo "<div class='test-section'>
        <h2>Image Helper Tests</h2>";
    
    if (function_exists('getProfileImageUrl')) {
        echo "<p class='success'>✅ getProfileImageUrl function exists</p>";
        $testUrl = getProfileImageUrl(1);
        if ($testUrl) {
            echo "<p class='success'>✅ Profile image URL generated: " . substr($testUrl, 0, 50) . "...</p>";
        }
    } else {
        echo "<p class='error'>❌ getProfileImageUrl function missing</p>";
    }
    
    echo "</div>";
    
    // Test links
    echo "<div class='test-section'>
        <h2>Access Links</h2>
        <p><a href='quick_login.php' target='_blank' style='color: blue;'>🔐 Quick Login (Auto-login as admin)</a></p>
        <p><a href='modules/settings/index.php' target='_blank' style='color: green;'>⚙️ Main Settings Page</a></p>
        <p><a href='simple_settings.php' target='_blank' style='color: orange;'>🔧 Simple Settings (Alternative)</a></p>
        <p><a href='debug_settings.php' target='_blank' style='color: purple;'>🐛 Debug Settings (No auth required)</a></p>
        <p><a href='admin_dashboard.php' target='_blank' style='color: teal;'>📊 Admin Dashboard</a></p>
    </div>";
    
    echo "<div class='test-section'>
        <h2>🎯 Quick Start Instructions</h2>
        <ol>
            <li><strong>Login:</strong> Click <a href='quick_login.php'>Quick Login</a> to automatically log in as admin</li>
            <li><strong>Access Settings:</strong> Go to <a href='modules/settings/index.php'>Settings Page</a></li>
            <li><strong>Test Features:</strong> Try changing theme, updating profile, changing password</li>
            <li><strong>Upload Profile Picture:</strong> Click on profile image to upload new picture</li>
            <li><strong>Configure Notifications:</strong> Set up your notification preferences</li>
        </ol>
    </div>";
    
} catch (Exception $e) {
    echo "<p class='error'>❌ Error: " . $e->getMessage() . "</p>";
}

echo "</body></html>";
?>