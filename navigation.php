<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Navigation - Pharmacy Management System</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            margin: 0;
            padding: 20px;
            min-height: 100vh;
        }
        .container {
            max-width: 1000px;
            margin: 0 auto;
            background: white;
            border-radius: 15px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.3);
            overflow: hidden;
        }
        .header {
            background: linear-gradient(135deg, #28a745, #20c997);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 2.5rem;
        }
        .content {
            padding: 30px;
        }
        .nav-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .nav-card {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            text-align: center;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            border: 2px solid transparent;
        }
        .nav-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.15);
            border-color: #28a745;
        }
        .nav-icon {
            font-size: 2.5rem;
            margin-bottom: 15px;
            color: #28a745;
        }
        .nav-card h3 {
            color: #333;
            margin-bottom: 10px;
            font-size: 1.3rem;
        }
        .nav-card p {
            color: #666;
            margin-bottom: 20px;
            font-size: 0.9rem;
        }
        .btn {
            display: inline-block;
            padding: 10px 20px;
            background: #28a745;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
            transition: all 0.3s ease;
            margin: 5px;
        }
        .btn:hover {
            background: #218838;
            transform: translateY(-2px);
        }
        .btn-secondary {
            background: #6c757d;
        }
        .btn-secondary:hover {
            background: #545b62;
        }
        .section {
            margin: 30px 0;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 10px;
        }
        .section h2 {
            color: #333;
            margin-bottom: 15px;
        }
        .url-list {
            background: white;
            padding: 15px;
            border-radius: 5px;
            font-family: monospace;
            font-size: 0.9rem;
        }
        .url-list a {
            display: block;
            color: #007bff;
            text-decoration: none;
            padding: 5px 0;
            border-bottom: 1px solid #eee;
        }
        .url-list a:hover {
            background: #f8f9fa;
            padding-left: 10px;
        }
    </style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🧭 Navigation Center</h1>
            <p>All links to access your Pharmacy Management System</p>
        </div>
        
        <div class="content">
            <!-- Main Modules -->
            <div class="nav-grid">
                <div class="nav-card">
                    <div class="nav-icon"><i class="fas fa-home"></i></div>
                    <h3>Dashboard</h3>
                    <p>Main admin dashboard with overview and quick stats</p>
                    <a href="index.php" class="btn">Open Dashboard</a>
                    <a href="dashboard_simple.php" class="btn btn-secondary">Simple Dashboard</a>
                </div>
                
                <div class="nav-card">
                    <div class="nav-icon"><i class="fas fa-boxes"></i></div>
                    <h3>Inventory</h3>
                    <p>Manage medicines, stock levels, and suppliers</p>
                    <a href="modules/inventory/index.php" class="btn">Manage Inventory</a>
                </div>
                
                <div class="nav-card">
                    <div class="nav-icon"><i class="fas fa-cash-register"></i></div>
                    <h3>Sales</h3>
                    <p>Process sales, generate invoices, and manage transactions</p>
                    <a href="modules/sales/new_sale.php" class="btn">New Sale</a>
                </div>
                
                <div class="nav-card">
                    <div class="nav-icon"><i class="fas fa-users"></i></div>
                    <h3>Customers</h3>
                    <p>Manage customer records and purchase history</p>
                    <a href="modules/customers/index.php" class="btn">View Customers</a>
                </div>
                
                <div class="nav-card">
                    <div class="nav-icon"><i class="fas fa-chart-bar"></i></div>
                    <h3>Reports</h3>
                    <p>Generate sales reports and business analytics</p>
                    <a href="modules/reports/index.php" class="btn">View Reports</a>
                </div>
                
                <div class="nav-card">
                    <div class="nav-icon"><i class="fas fa-shopping-cart"></i></div>
                    <h3>Customer Website</h3>
                    <p>Customer-facing online pharmacy store</p>
                    <a href="customer/index.html" class="btn">Visit Store</a>
                </div>
            </div>
            
            <!-- Authentication -->
            <div class="section">
                <h2>🔐 Authentication</h2>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 10px;">
                    <a href="auth/login.php" class="btn">Login</a>
                    <a href="auth/register.php" class="btn">Register</a>
                    <a href="auth/forgot_password.php" class="btn">Reset Password</a>
                    <a href="auth/logout.php" class="btn btn-secondary">Logout</a>
                </div>
            </div>
            
            <!-- Tools & Utilities -->
            <div class="section">
                <h2>🛠️ Tools & Utilities</h2>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 10px;">
                    <a href="install.php" class="btn btn-secondary">Install Database</a>
                    <a href="reset_admin.php" class="btn btn-secondary">Reset Passwords</a>
                    <a href="debug_login.php" class="btn btn-secondary">Debug Login</a>
                    <a href="test_connection.php" class="btn btn-secondary">Test System</a>
                    <a href="test_dashboard.php" class="btn btn-secondary">Test Dashboard</a>
                </div>
            </div>
            
            <!-- Direct URLs -->
            <div class="section">
                <h2>📋 Direct URLs (Copy & Paste)</h2>
                <div class="url-list">
                    <a href="http://localhost/pharmacy-management-system/index.php" target="_blank">
                        http://localhost/pharmacy-management-system/index.php
                    </a>
                    <a href="http://localhost/pharmacy-management-system/modules/inventory/index.php" target="_blank">
                        http://localhost/pharmacy-management-system/modules/inventory/index.php
                    </a>
                    <a href="http://localhost/pharmacy-management-system/modules/sales/new_sale.php" target="_blank">
                        http://localhost/pharmacy-management-system/modules/sales/new_sale.php
                    </a>
                    <a href="http://localhost/pharmacy-management-system/modules/customers/index.php" target="_blank">
                        http://localhost/pharmacy-management-system/modules/customers/index.php
                    </a>
                    <a href="http://localhost/pharmacy-management-system/modules/customers/add_customer.php" target="_blank">
                        http://localhost/pharmacy-management-system/modules/customers/add_customer.php
                    </a>
                    <a href="http://localhost/pharmacy-management-system/modules/reports/index.php" target="_blank">
                        http://localhost/pharmacy-management-system/modules/reports/index.php
                    </a>
                    <a href="http://localhost/pharmacy-management-system/customer/index.html" target="_blank">
                        http://localhost/pharmacy-management-system/customer/index.html
                    </a>
                </div>
            </div>
            
            <!-- Status -->
            <div class="section">
                <h2>📊 System Status</h2>
                <p>Current URL: <code><?php echo $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']; ?></code></p>
                <p>Document Root: <code><?php echo $_SERVER['DOCUMENT_ROOT']; ?></code></p>
                <p>Project Path: <code><?php echo __DIR__; ?></code></p>
            </div>
        </div>
    </div>
</body>
</html>