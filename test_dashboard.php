<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Test</title>
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
    <h1>🔧 Dashboard Test</h1>
    
    <?php
    echo '<div class="info">Testing dashboard components...</div>';
    
    // Test 1: Check if user is logged in
    session_start();
    if (isset($_SESSION['user_id'])) {
        echo '<div class="result success">✅ User is logged in (ID: ' . $_SESSION['user_id'] . ')</div>';
    } else {
        echo '<div class="result error">❌ User is not logged in</div>';
        echo '<div class="info">Please login first: <a href="auth/login.php">Login</a></div>';
    }
    
    // Test 2: Check database connection
    try {
        require_once 'bootstrap.php';
        echo '<div class="result success">✅ Bootstrap loaded successfully</div>';
        
        if ($pdo) {
            echo '<div class="result success">✅ Database connection successful</div>';
            
            // Test API endpoints
            $apiTests = [
                'dashboard_stats.php' => 'Dashboard Statistics',
                'recent_sales.php' => 'Recent Sales',
                'stock_alerts.php' => 'Stock Alerts'
            ];
            
            foreach ($apiTests as $endpoint => $description) {
                $url = "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['REQUEST_URI']) . "/api/$endpoint";
                
                $context = stream_context_create([
                    'http' => [
                        'method' => 'GET',
                        'header' => 'Cookie: ' . $_SERVER['HTTP_COOKIE'] ?? ''
                    ]
                ]);
                
                $response = @file_get_contents($url, false, $context);
                
                if ($response) {
                    $data = json_decode($response, true);
                    if ($data && isset($data['success']) && $data['success']) {
                        echo '<div class="result success">✅ ' . $description . ' API working</div>';
                        if ($endpoint === 'dashboard_stats.php') {
                            echo '<pre>' . json_encode($data, JSON_PRETTY_PRINT) . '</pre>';
                        }
                    } else {
                        echo '<div class="result error">❌ ' . $description . ' API failed</div>';
                        echo '<pre>' . htmlspecialchars($response) . '</pre>';
                    }
                } else {
                    echo '<div class="result error">❌ ' . $description . ' API not accessible</div>';
                }
            }
            
        } else {
            echo '<div class="result error">❌ Database connection failed</div>';
        }
        
    } catch (Exception $e) {
        echo '<div class="result error">❌ Bootstrap error: ' . $e->getMessage() . '</div>';
    }
    
    // Test 3: Check if dashboard files exist
    $files = [
        'index.php' => 'Main Dashboard',
        'assets/js/dashboard.js' => 'Dashboard JavaScript',
        'includes/navbar.php' => 'Navigation Bar'
    ];
    
    foreach ($files as $file => $description) {
        if (file_exists($file)) {
            echo '<div class="result success">✅ ' . $description . ' exists</div>';
        } else {
            echo '<div class="result error">❌ ' . $description . ' missing</div>';
        }
    }
    ?>
    
    <div style="text-align: center; margin-top: 30px;">
        <a href="index.php" class="btn">🏠 Go to Dashboard</a>
        <a href="auth/login.php" class="btn">🔑 Login</a>
        <a href="navigation.php" class="btn">🧭 Navigation</a>
    </div>
    
    <div class="info">
        <strong>💡 If dashboard still shows basic view:</strong>
        <ol>
            <li>Make sure you're logged in as admin/pharmacist</li>
            <li>Check browser console for JavaScript errors (F12)</li>
            <li>Clear browser cache and refresh</li>
            <li>Check if all API endpoints are working above</li>
        </ol>
    </div>
</body>
</html>