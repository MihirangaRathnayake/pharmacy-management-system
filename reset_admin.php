<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Admin Password</title>
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
            max-width: 600px;
            width: 100%;
        }
        h1 {
            color: #333;
            text-align: center;
            margin-bottom: 30px;
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
        .btn-secondary {
            background: #6c757d;
        }
        .btn-secondary:hover {
            background: #545b62;
        }
        .credentials {
            background: #e3f2fd;
            border: 1px solid #2196f3;
            padding: 20px;
            border-radius: 5px;
            margin: 20px 0;
        }
        .cred-item {
            background: white;
            padding: 10px;
            margin: 5px 0;
            border-radius: 3px;
            font-family: monospace;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔧 Reset Admin Password</h1>
        
        <?php
        if ($_POST && isset($_POST['reset'])) {
            try {
                // Connect to database
                $pdo = new PDO("mysql:host=localhost;dbname=pharmacy_management;charset=utf8mb4", "root", "");
                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                
                echo '<div class="info">Resetting admin credentials...</div>';
                
                // Hash the passwords properly
                $adminPassword = password_hash('admin123', PASSWORD_DEFAULT);
                $pharmacistPassword = password_hash('pharma123', PASSWORD_DEFAULT);
                $customerPassword = password_hash('customer123', PASSWORD_DEFAULT);
                
                // Delete existing users first
                $pdo->exec("DELETE FROM users WHERE email IN ('admin@pharmacy.com', 'pharmacist@pharmacy.com', 'customer@pharmacy.com')");
                echo '<div class="info">Cleared existing users...</div>';
                
                // Insert new users with proper password hashes
                $stmt = $pdo->prepare("
                    INSERT INTO users (name, email, password, role, status) VALUES 
                    (?, ?, ?, ?, 'active')
                ");
                
                // Insert admin
                $stmt->execute(['Admin User', 'admin@pharmacy.com', $adminPassword, 'admin']);
                echo '<div class="success">✅ Admin user created</div>';
                
                // Insert pharmacist
                $stmt->execute(['Pharmacist', 'pharmacist@pharmacy.com', $pharmacistPassword, 'pharmacist']);
                echo '<div class="success">✅ Pharmacist user created</div>';
                
                // Insert customer
                $stmt->execute(['Customer', 'customer@pharmacy.com', $customerPassword, 'customer']);
                echo '<div class="success">✅ Customer user created</div>';
                
                // Verify the users were created
                $stmt = $pdo->query("SELECT name, email, role FROM users WHERE email IN ('admin@pharmacy.com', 'pharmacist@pharmacy.com', 'customer@pharmacy.com')");
                $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                echo '<div class="success">✅ Password reset completed successfully!</div>';
                
                echo '<div class="credentials">';
                echo '<h3>🔐 Updated Login Credentials</h3>';
                foreach ($users as $user) {
                    echo '<div class="cred-item">';
                    echo '<strong>' . ucfirst($user['role']) . ':</strong> ' . $user['email'] . ' / ';
                    switch ($user['role']) {
                        case 'admin':
                            echo 'admin123';
                            break;
                        case 'pharmacist':
                            echo 'pharma123';
                            break;
                        case 'customer':
                            echo 'customer123';
                            break;
                    }
                    echo '</div>';
                }
                echo '</div>';
                
                echo '<div class="info">You can now login with these credentials!</div>';
                echo '<a href="auth/login.php" class="btn">🚀 Try Login Now</a>';
                echo '<a href="index.php" class="btn btn-secondary">👨‍💼 Admin Dashboard</a>';
                
            } catch (Exception $e) {
                echo '<div class="error">❌ Error: ' . $e->getMessage() . '</div>';
                echo '<div class="info">Make sure the database is installed first.</div>';
                echo '<a href="install.php" class="btn">🔧 Install Database</a>';
            }
        } else {
            // Show the form
            ?>
            <div class="info">
                <h3>⚠️ Login Issues?</h3>
                <p>If you're having trouble logging in with the default credentials, this tool will reset the admin password and recreate all default users.</p>
            </div>
            
            <form method="POST">
                <div style="text-align: center; margin: 30px 0;">
                    <button type="submit" name="reset" class="btn">🔄 Reset All User Passwords</button>
                </div>
            </form>
            
            <div class="credentials">
                <h3>🔐 Default Credentials (After Reset)</h3>
                <div class="cred-item"><strong>Admin:</strong> admin@pharmacy.com / admin123</div>
                <div class="cred-item"><strong>Pharmacist:</strong> pharmacist@pharmacy.com / pharma123</div>
                <div class="cred-item"><strong>Customer:</strong> customer@pharmacy.com / customer123</div>
            </div>
            
            <div style="text-align: center; margin-top: 30px;">
                <a href="test_connection.php" class="btn btn-secondary">🔍 Test System</a>
                <a href="install.php" class="btn btn-secondary">🔧 Reinstall Database</a>
            </div>
            <?php
        }
        ?>
    </div>
</body>
</html>