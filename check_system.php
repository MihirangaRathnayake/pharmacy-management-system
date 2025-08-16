<?php
// System Check for Pharmacy Management System

echo "<h1>🏥 Pharmacy Management System - System Check</h1>";

// Check PHP version
echo "<h2>📋 System Requirements</h2>";
echo "<div class='check-item'>";
echo "<strong>PHP Version:</strong> " . PHP_VERSION;
if (version_compare(PHP_VERSION, '7.4.0', '>=')) {
    echo " <span class='success'>✅ OK</span>";
} else {
    echo " <span class='error'>❌ Requires PHP 7.4+</span>";
}
echo "</div>";

// Check required extensions
$required_extensions = ['pdo', 'pdo_mysql', 'gd', 'fileinfo', 'json'];
echo "<h3>Required PHP Extensions:</h3>";
foreach ($required_extensions as $ext) {
    echo "<div class='check-item'>";
    echo "<strong>$ext:</strong> ";
    if (extension_loaded($ext)) {
        echo "<span class='success'>✅ Loaded</span>";
    } else {
        echo "<span class='error'>❌ Missing</span>";
    }
    echo "</div>";
}

// Check database connection
echo "<h2>🗄️ Database Connection</h2>";
try {
    require_once 'config/database.php';
    echo "<div class='check-item'>";
    echo "<strong>Database Connection:</strong> <span class='success'>✅ Connected</span>";
    echo "</div>";
    
    // Check if tables exist
    $tables = ['users', 'medicines', 'customers', 'sales', 'prescriptions'];
    echo "<h3>Database Tables:</h3>";
    foreach ($tables as $table) {
        echo "<div class='check-item'>";
        echo "<strong>$table:</strong> ";
        try {
            $stmt = $pdo->query("SELECT COUNT(*) FROM $table");
            $count = $stmt->fetchColumn();
            echo "<span class='success'>✅ Exists ($count records)</span>";
        } catch (Exception $e) {
            echo "<span class='error'>❌ Missing</span>";
        }
        echo "</div>";
    }
    
} catch (Exception $e) {
    echo "<div class='check-item'>";
    echo "<strong>Database Connection:</strong> <span class='error'>❌ Failed</span>";
    echo "<br><small>Error: " . $e->getMessage() . "</small>";
    echo "</div>";
    
    echo "<div class='alert alert-error'>";
    echo "<h3>🔧 Database Setup Required</h3>";
    echo "<p>Run the database setup first:</p>";
    echo "<a href='setup_database.php' class='btn'>Setup Database</a>";
    echo "</div>";
}

// Check file permissions
echo "<h2>📁 File Permissions</h2>";
$directories = ['uploads', 'uploads/prescriptions'];
foreach ($directories as $dir) {
    echo "<div class='check-item'>";
    echo "<strong>$dir/:</strong> ";
    if (is_dir($dir)) {
        if (is_writable($dir)) {
            echo "<span class='success'>✅ Writable</span>";
        } else {
            echo "<span class='warning'>⚠️ Not writable</span>";
        }
    } else {
        echo "<span class='error'>❌ Missing</span>";
        // Try to create directory
        if (mkdir($dir, 0755, true)) {
            echo " <span class='success'>✅ Created</span>";
        }
    }
    echo "</div>";
}

// Check Apache modules
echo "<h2>🌐 Web Server</h2>";
echo "<div class='check-item'>";
echo "<strong>Web Server:</strong> " . $_SERVER['SERVER_SOFTWARE'];
echo "</div>";

if (function_exists('apache_get_modules')) {
    $modules = apache_get_modules();
    $required_modules = ['mod_rewrite'];
    foreach ($required_modules as $module) {
        echo "<div class='check-item'>";
        echo "<strong>$module:</strong> ";
        if (in_array($module, $modules)) {
            echo "<span class='success'>✅ Enabled</span>";
        } else {
            echo "<span class='warning'>⚠️ Not detected</span>";
        }
        echo "</div>";
    }
}

// Quick links
echo "<h2>🚀 Quick Access</h2>";
echo "<div class='links'>";
echo "<a href='setup_database.php' class='btn btn-primary'>Setup Database</a>";
echo "<a href='index.php' class='btn btn-secondary'>Admin Dashboard</a>";
echo "<a href='customer/index.html' class='btn btn-secondary'>Customer Website</a>";
echo "</div>";

// System info
echo "<h2>ℹ️ System Information</h2>";
echo "<div class='info-grid'>";
echo "<div><strong>Document Root:</strong> " . $_SERVER['DOCUMENT_ROOT'] . "</div>";
echo "<div><strong>Current Path:</strong> " . __DIR__ . "</div>";
echo "<div><strong>PHP Memory Limit:</strong> " . ini_get('memory_limit') . "</div>";
echo "<div><strong>Max Upload Size:</strong> " . ini_get('upload_max_filesize') . "</div>";
echo "<div><strong>Max Post Size:</strong> " . ini_get('post_max_size') . "</div>";
echo "</div>";

?>

<style>
body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    max-width: 1000px;
    margin: 0 auto;
    padding: 20px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    min-height: 100vh;
}

h1 {
    color: white;
    text-align: center;
    margin-bottom: 30px;
    text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
}

h2 {
    color: #333;
    background: white;
    padding: 15px;
    border-radius: 8px;
    margin: 20px 0 10px 0;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

h3 {
    color: #555;
    margin: 15px 0 10px 0;
}

.check-item {
    background: white;
    padding: 12px 15px;
    margin: 5px 0;
    border-radius: 5px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.success {
    color: #28a745;
    font-weight: bold;
}

.error {
    color: #dc3545;
    font-weight: bold;
}

.warning {
    color: #ffc107;
    font-weight: bold;
}

.alert {
    padding: 20px;
    border-radius: 8px;
    margin: 20px 0;
}

.alert-error {
    background: #f8d7da;
    border: 1px solid #f5c6cb;
    color: #721c24;
}

.btn {
    display: inline-block;
    padding: 12px 24px;
    margin: 10px 5px;
    text-decoration: none;
    border-radius: 5px;
    font-weight: bold;
    transition: all 0.3s ease;
}

.btn-primary {
    background: #28a745;
    color: white;
}

.btn-secondary {
    background: #6c757d;
    color: white;
}

.btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.2);
}

.links {
    text-align: center;
    background: white;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.info-grid {
    background: white;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 15px;
}

.info-grid div {
    padding: 10px;
    background: #f8f9fa;
    border-radius: 4px;
}

@media (max-width: 768px) {
    .info-grid {
        grid-template-columns: 1fr;
    }
    
    .check-item {
        flex-direction: column;
        align-items: flex-start;
        gap: 5px;
    }
}
</style>