<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connection Test</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .result {
            padding: 15px;
            margin: 10px 0;
            border-radius: 5px;
        }
        .success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .info {
            background: #d1ecf1;
            color: #0c5460;
            border: 1px solid #bee5eb;
        }
        h1 {
            color: #333;
            text-align: center;
        }
        .btn {
            display: inline-block;
            padding: 10px 20px;
            background: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin: 5px;
        }
    </style>
</head>
<body>
    <h1>🔧 Connection Test</h1>
    
    <?php
    echo '<div class="info">Testing system components...</div>';
    
    // Test 1: PHP Version
    echo '<div class="result ' . (version_compare(PHP_VERSION, '7.4.0', '>=') ? 'success' : 'error') . '">';
    echo '<strong>PHP Version:</strong> ' . PHP_VERSION;
    echo version_compare(PHP_VERSION, '7.4.0', '>=') ? ' ✅' : ' ❌ (Requires 7.4+)';
    echo '</div>';
    
    // Test 2: Required Extensions
    $required_extensions = ['pdo', 'pdo_mysql', 'fileinfo'];
    $optional_extensions = ['gd'];
    
    foreach ($required_extensions as $ext) {
        echo '<div class="result ' . (extension_loaded($ext) ? 'success' : 'error') . '">';
        echo '<strong>' . $ext . ' (Required):</strong> ' . (extension_loaded($ext) ? 'Loaded ✅' : 'Missing ❌');
        echo '</div>';
    }
    
    foreach ($optional_extensions as $ext) {
        echo '<div class="result ' . (extension_loaded($ext) ? 'success' : 'info') . '">';
        echo '<strong>' . $ext . ' (Optional):</strong> ' . (extension_loaded($ext) ? 'Loaded ✅' : 'Missing ⚠️ (Image processing disabled)');
        echo '</div>';
    }
    
    // Test 3: Database Connection
    try {
        $pdo = new PDO("mysql:host=localhost", "root", "");
        echo '<div class="result success"><strong>MySQL Connection:</strong> Success ✅</div>';
        
        // Test if database exists
        $stmt = $pdo->query("SHOW DATABASES LIKE 'pharmacy_management'");
        if ($stmt->rowCount() > 0) {
            echo '<div class="result success"><strong>Database:</strong> pharmacy_management exists ✅</div>';
            
            // Connect to the database
            $pdo = new PDO("mysql:host=localhost;dbname=pharmacy_management", "root", "");
            
            // Check tables
            $tables = ['users', 'medicines', 'customers', 'sales'];
            foreach ($tables as $table) {
                try {
                    $stmt = $pdo->query("SELECT COUNT(*) FROM $table");
                    $count = $stmt->fetchColumn();
                    echo '<div class="result success"><strong>Table ' . $table . ':</strong> ' . $count . ' records ✅</div>';
                } catch (Exception $e) {
                    echo '<div class="result error"><strong>Table ' . $table . ':</strong> Missing ❌</div>';
                }
            }
        } else {
            echo '<div class="result error"><strong>Database:</strong> pharmacy_management not found ❌</div>';
        }
        
    } catch (Exception $e) {
        echo '<div class="result error"><strong>MySQL Connection:</strong> Failed ❌<br>';
        echo 'Error: ' . $e->getMessage() . '</div>';
    }
    
    // Test 4: File Permissions
    $dirs = ['uploads', 'uploads/prescriptions'];
    foreach ($dirs as $dir) {
        if (is_dir($dir)) {
            echo '<div class="result ' . (is_writable($dir) ? 'success' : 'error') . '">';
            echo '<strong>Directory ' . $dir . ':</strong> ' . (is_writable($dir) ? 'Writable ✅' : 'Not writable ❌');
            echo '</div>';
        } else {
            echo '<div class="result error"><strong>Directory ' . $dir . ':</strong> Missing ❌</div>';
        }
    }
    ?>
    
    <div style="text-align: center; margin-top: 30px;">
        <a href="install.php" class="btn">🚀 Install Database</a>
        <a href="start.php" class="btn">🏠 Home</a>
        <a href="index.php" class="btn">👨‍💼 Admin</a>
        <a href="customer/index.html" class="btn">🛒 Customer</a>
    </div>
</body>
</html>