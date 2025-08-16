<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Debug Login</title>
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
        pre {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            overflow-x: auto;
        }
    </style>
</head>
<body>
    <h1>🔍 Login Debug Tool</h1>
    
    <?php
    try {
        // Connect to database
        $pdo = new PDO("mysql:host=localhost;dbname=pharmacy_management;charset=utf8mb4", "root", "");
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        echo '<div class="success">✅ Database connection successful</div>';
        
        // Check if users table exists
        $stmt = $pdo->query("SHOW TABLES LIKE 'users'");
        if ($stmt->rowCount() > 0) {
            echo '<div class="success">✅ Users table exists</div>';
            
            // Get all users
            $stmt = $pdo->query("SELECT id, name, email, role, status, created_at FROM users");
            $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo '<div class="info"><strong>Found ' . count($users) . ' users in database:</strong></div>';
            
            if (count($users) > 0) {
                echo '<pre>';
                foreach ($users as $user) {
                    echo "ID: {$user['id']}\n";
                    echo "Name: {$user['name']}\n";
                    echo "Email: {$user['email']}\n";
                    echo "Role: {$user['role']}\n";
                    echo "Status: {$user['status']}\n";
                    echo "Created: {$user['created_at']}\n";
                    echo "---\n";
                }
                echo '</pre>';
                
                // Test password verification for admin user
                $stmt = $pdo->prepare("SELECT * FROM users WHERE email = 'admin@pharmacy.com'");
                $stmt->execute();
                $admin = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($admin) {
                    echo '<div class="info"><strong>Testing admin password:</strong></div>';
                    
                    // Test the password
                    $testPassword = 'admin123';
                    $isValid = password_verify($testPassword, $admin['password']);
                    
                    if ($isValid) {
                        echo '<div class="success">✅ Password "admin123" is CORRECT for admin@pharmacy.com</div>';
                    } else {
                        echo '<div class="error">❌ Password "admin123" is INCORRECT for admin@pharmacy.com</div>';
                        echo '<div class="info">Stored password hash: ' . substr($admin['password'], 0, 50) . '...</div>';
                        
                        // Show what the correct hash should be
                        $correctHash = password_hash('admin123', PASSWORD_DEFAULT);
                        echo '<div class="info">A correct hash would look like: ' . substr($correctHash, 0, 50) . '...</div>';
                    }
                } else {
                    echo '<div class="error">❌ Admin user not found in database</div>';
                }
                
            } else {
                echo '<div class="error">❌ No users found in database</div>';
            }
            
        } else {
            echo '<div class="error">❌ Users table does not exist</div>';
        }
        
        // Test login function
        if (isset($_POST['test_login'])) {
            session_start();
            
            echo '<div class="info"><strong>Testing login function...</strong></div>';
            
            // Include the auth functions
            require_once 'includes/auth.php';
            
            $email = 'admin@pharmacy.com';
            $password = 'admin123';
            
            $loginResult = login($email, $password);
            
            if ($loginResult) {
                echo '<div class="success">✅ Login function returned TRUE - Login should work!</div>';
                echo '<div class="info">Session user_id: ' . ($_SESSION['user_id'] ?? 'Not set') . '</div>';
                echo '<div class="info">Session user_role: ' . ($_SESSION['user_role'] ?? 'Not set') . '</div>';
            } else {
                echo '<div class="error">❌ Login function returned FALSE - Login failed</div>';
            }
        }
        
    } catch (Exception $e) {
        echo '<div class="error">❌ Database Error: ' . $e->getMessage() . '</div>';
    }
    ?>
    
    <div style="text-align: center; margin: 30px 0;">
        <form method="POST" style="display: inline;">
            <button type="submit" name="test_login" class="btn">🧪 Test Login Function</button>
        </form>
        <a href="reset_admin.php" class="btn">🔄 Reset Passwords</a>
        <a href="auth/login.php" class="btn">🚀 Try Login</a>
    </div>
    
    <div class="info">
        <strong>💡 If login still doesn't work:</strong>
        <ol>
            <li>Click "Reset Passwords" to recreate all users with correct password hashes</li>
            <li>Try logging in again with: admin@pharmacy.com / admin123</li>
            <li>If it still fails, there might be a session issue - try clearing browser cache</li>
        </ol>
    </div>
</body>
</html>