<?php
require_once __DIR__ . '/bootstrap.php';

// Quick login for testing - logs in as admin
try {
    global $pdo;
    
    if (!$pdo) {
        die('Database connection failed');
    }
    
    // Get the first admin user
    $stmt = $pdo->prepare("SELECT * FROM users WHERE role = 'admin' LIMIT 1");
    $stmt->execute();
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($admin) {
        $_SESSION['user_id'] = $admin['id'];
        $_SESSION['user_role'] = $admin['role'];
        
        echo "<!DOCTYPE html>
        <html>
        <head>
            <title>Quick Login</title>
            <style>
                body { font-family: Arial, sans-serif; margin: 40px; text-align: center; }
                .success { color: green; font-size: 18px; margin: 20px 0; }
                .button { 
                    display: inline-block; 
                    padding: 12px 24px; 
                    background: #28a745; 
                    color: white; 
                    text-decoration: none; 
                    border-radius: 5px; 
                    margin: 10px;
                }
                .button:hover { background: #218838; }
            </style>
        </head>
        <body>
            <h1>🚀 Quick Login Successful!</h1>
            <p class='success'>✅ Logged in as: " . htmlspecialchars($admin['name']) . " (" . htmlspecialchars($admin['role']) . ")</p>
            
            <div>
                <a href='modules/settings/index.php' class='button'>🔧 Open Settings Page</a>
                <a href='admin_dashboard.php' class='button'>📊 Open Dashboard</a>
                <a href='debug_settings.php' class='button'>🐛 Debug Settings</a>
            </div>
            
            <p><small>Session ID: " . session_id() . "</small></p>
        </body>
        </html>";
    } else {
        echo "No admin user found. Please run the installation first.";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>