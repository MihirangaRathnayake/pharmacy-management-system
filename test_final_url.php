<?php
echo "🎯 Settings URL Test\n\n";

// Check if settings file exists
if (file_exists('modules/settings/index.php')) {
    echo "✅ Settings file exists\n";
} else {
    echo "❌ Settings file missing\n";
}

// Check file size
$fileSize = filesize('modules/settings/index.php');
echo "📄 File size: " . number_format($fileSize) . " bytes\n";

if ($fileSize > 10000) {
    echo "✅ File has content\n";
} else {
    echo "❌ File too small\n";
}

echo "\n🌐 Based on your dashboard URL structure:\n";
echo "Dashboard: http://localhost/pharmacy-management-system/index.php\n";
echo "Settings:  http://localhost/pharmacy-management-system/modules/settings/index.php\n";

echo "\n📋 How to access:\n";
echo "1. Make sure you're logged in to your dashboard\n";
echo "2. Click on your user dropdown (top right)\n";
echo "3. Click 'Settings'\n";
echo "4. Or go directly to the URL above\n";

echo "\n🔧 If still getting 404, try:\n";
echo "- Check if your web server is running\n";
echo "- Verify the pharmacy-management-system folder exists in your web root\n";
echo "- Make sure you're logged in first\n";
?>