<?php
require_once 'bootstrap.php';

echo "🎨 Testing Global Theme System\n\n";

// Test theme helper functions
if (function_exists('getUserTheme')) {
    echo "✅ getUserTheme function exists\n";
} else {
    echo "❌ getUserTheme function missing\n";
}

if (function_exists('getThemeClass')) {
    echo "✅ getThemeClass function exists\n";
} else {
    echo "❌ getThemeClass function missing\n";
}

if (function_exists('renderThemeScript')) {
    echo "✅ renderThemeScript function exists\n";
} else {
    echo "❌ renderThemeScript function missing\n";
}

// Test theme CSS file
if (file_exists('assets/css/theme.css')) {
    echo "✅ Theme CSS file exists\n";
    $cssSize = filesize('assets/css/theme.css');
    echo "📄 CSS file size: " . number_format($cssSize) . " bytes\n";
} else {
    echo "❌ Theme CSS file missing\n";
}

// Test API endpoint
if (file_exists('api/update_theme.php')) {
    echo "✅ Theme update API exists\n";
} else {
    echo "❌ Theme update API missing\n";
}

// Test if pages have been updated
$testPages = [
    'admin_dashboard.php',
    'modules/inventory/index.php',
    'modules/sales/new_sale.php'
];

echo "\n📄 Checking pages for theme support:\n";
foreach ($testPages as $page) {
    if (file_exists($page)) {
        $content = file_get_contents($page);
        if (strpos($content, 'data-theme=') !== false) {
            echo "✅ $page has theme support\n";
        } else {
            echo "❌ $page missing theme support\n";
        }
    } else {
        echo "❌ $page not found\n";
    }
}

echo "\n🎯 How the theme system works:\n";
echo "1. User changes theme in Settings page\n";
echo "2. Theme is saved to user_preferences table\n";
echo "3. All pages load user's theme preference\n";
echo "4. Theme toggle in navbar allows quick switching\n";
echo "5. Theme persists across all pages and sessions\n";

echo "\n🚀 To test:\n";
echo "1. Go to Settings and change theme to Dark\n";
echo "2. Navigate to Dashboard - should be dark\n";
echo "3. Go to Inventory - should be dark\n";
echo "4. Use navbar theme toggle - should switch immediately\n";
?>