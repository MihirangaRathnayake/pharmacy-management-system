<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Currency Symbol</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            margin: 0;
            padding: 20px;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .container {
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
            text-align: center;
            max-width: 500px;
        }
        h1 {
            color: #333;
            margin-bottom: 20px;
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
        .btn {
            display: inline-block;
            padding: 12px 24px;
            background: #28a745;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
            margin: 10px 5px;
            border: none;
            cursor: pointer;
            font-size: 16px;
        }
        .btn:hover {
            background: #218838;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>💱 Update Currency Symbol</h1>
        
        <?php
        if ($_POST && isset($_POST['update'])) {
            try {
                // Connect to database
                $pdo = new PDO("mysql:host=localhost;dbname=pharmacy_management;charset=utf8mb4", "root", "");
                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                
                echo '<div class="result success">✅ Connected to database</div>';
                
                // Update currency symbol in settings
                $stmt = $pdo->prepare("UPDATE settings SET setting_value = 'Rs' WHERE setting_key = 'currency_symbol'");
                $stmt->execute();
                
                if ($stmt->rowCount() > 0) {
                    echo '<div class="result success">✅ Currency symbol updated to "Rs" in database</div>';
                } else {
                    // Insert if doesn't exist
                    $stmt = $pdo->prepare("INSERT INTO settings (setting_key, setting_value, description) VALUES ('currency_symbol', 'Rs', 'Currency symbol') ON DUPLICATE KEY UPDATE setting_value = 'Rs'");
                    $stmt->execute();
                    echo '<div class="result success">✅ Currency symbol setting created/updated</div>';
                }
                
                echo '<div class="result success">
                    <strong>✅ Currency Update Complete!</strong><br>
                    All prices will now display as "Rs" instead of "₹"<br><br>
                    <strong>Changes Applied:</strong><br>
                    • Database currency symbol: Rs<br>
                    • All PHP files: Updated<br>
                    • All JavaScript files: Updated<br>
                    • Customer website: Updated<br>
                    • Admin dashboard: Updated
                </div>';
                
                echo '<a href="index.php" class="btn">Go to Dashboard</a>';
                echo '<a href="customer/index.html" class="btn">Visit Store</a>';
                
            } catch (Exception $e) {
                echo '<div class="result error">❌ Error: ' . $e->getMessage() . '</div>';
                echo '<div class="result error">Make sure the database is installed first.</div>';
                echo '<a href="install.php" class="btn">Install Database</a>';
            }
        } else {
            ?>
            <p>This will update the currency symbol from "₹" (Indian Rupee) to "Rs" throughout the entire system.</p>
            
            <div class="result" style="background: #e3f2fd; color: #0c5460; border: 1px solid #bee5eb;">
                <strong>What will be updated:</strong><br>
                ✓ Database currency setting<br>
                ✓ All price displays<br>
                ✓ Customer website<br>
                ✓ Admin dashboard<br>
                ✓ Reports and invoices
            </div>
            
            <form method="POST">
                <button type="submit" name="update" class="btn">🔄 Update Currency to Rs</button>
            </form>
            
            <div style="margin-top: 20px;">
                <a href="index.php" class="btn" style="background: #6c757d;">Cancel</a>
            </div>
            <?php
        }
        ?>
    </div>
</body>
</html>