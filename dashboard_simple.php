<?php
require_once __DIR__ . '/bootstrap.php';

// Redirect to login if not authenticated
if (!isLoggedIn()) {
    header('Location: auth/login.php');
    exit();
}

$user = getCurrentUser();

// Get dashboard data directly
try {
    // Today's sales
    $stmt = $pdo->prepare("
        SELECT COALESCE(SUM(total_amount), 0) as today_sales 
        FROM sales 
        WHERE DATE(sale_date) = CURDATE() AND status = 'completed'
    ");
    $stmt->execute();
    $todaySales = $stmt->fetchColumn();

    // Low stock count
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as low_stock_count 
        FROM medicines 
        WHERE stock_quantity <= min_stock_level AND status = 'active'
    ");
    $stmt->execute();
    $lowStockCount = $stmt->fetchColumn();

    // Pending orders (prescriptions)
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as pending_orders 
        FROM prescriptions 
        WHERE status = 'pending'
    ");
    $stmt->execute();
    $pendingOrders = $stmt->fetchColumn();

    // Total customers
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as total_customers 
        FROM customers 
        WHERE status = 'active'
    ");
    $stmt->execute();
    $totalCustomers = $stmt->fetchColumn();

    // Recent sales
    $stmt = $pdo->prepare("
        SELECT s.invoice_number, s.total_amount, s.sale_date, c.name as customer_name
        FROM sales s
        LEFT JOIN customers c ON s.customer_id = c.id
        WHERE s.status = 'completed'
        ORDER BY s.sale_date DESC
        LIMIT 5
    ");
    $stmt->execute();
    $recentSales = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Stock alerts
    $stmt = $pdo->prepare("
        SELECT name, stock_quantity, min_stock_level, 'low_stock' as type
        FROM medicines 
        WHERE stock_quantity <= min_stock_level AND status = 'active'
        ORDER BY stock_quantity ASC
        LIMIT 5
    ");
    $stmt->execute();
    $stockAlerts = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    $error = $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pharmacy Management System - Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-gray-50">
    <?php include 'includes/navbar.php'; ?>
    
    <div class="container mx-auto px-4 py-8">
        <!-- Dashboard Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-800 mb-2">Dashboard</h1>
            <p class="text-gray-600">Welcome back, <?php echo htmlspecialchars($user['name']); ?>!</p>
        </div>

        <?php if (isset($error)): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
                <strong>Error:</strong> <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <!-- Quick Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-green-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Today's Sales</p>
                        <p class="text-2xl font-bold text-gray-900">Rs <?php echo number_format($todaySales ?? 0, 2); ?></p>
                    </div>
                    <div class="bg-green-100 p-3 rounded-full">
                        <i class="fas fa-rupee-sign text-green-600"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-blue-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Low Stock Items</p>
                        <p class="text-2xl font-bold text-gray-900"><?php echo $lowStockCount ?? 0; ?></p>
                    </div>
                    <div class="bg-blue-100 p-3 rounded-full">
                        <i class="fas fa-exclamation-triangle text-blue-600"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-yellow-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Pending Orders</p>
                        <p class="text-2xl font-bold text-gray-900"><?php echo $pendingOrders ?? 0; ?></p>
                    </div>
                    <div class="bg-yellow-100 p-3 rounded-full">
                        <i class="fas fa-clock text-yellow-600"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-purple-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Total Customers</p>
                        <p class="text-2xl font-bold text-gray-900"><?php echo $totalCustomers ?? 0; ?></p>
                    </div>
                    <div class="bg-purple-100 p-3 rounded-full">
                        <i class="fas fa-users text-purple-600"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-8">
            <h2 class="text-xl font-semibold text-gray-800 mb-4">Quick Actions</h2>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <a href="modules/inventory/add_medicine.php" class="bg-green-500 hover:bg-green-600 text-white p-4 rounded-lg text-center transition duration-200">
                    <i class="fas fa-plus-circle text-2xl mb-2"></i>
                    <p class="font-medium">Add Medicine</p>
                </a>
                <a href="modules/sales/new_sale.php" class="bg-blue-500 hover:bg-blue-600 text-white p-4 rounded-lg text-center transition duration-200">
                    <i class="fas fa-shopping-cart text-2xl mb-2"></i>
                    <p class="font-medium">New Sale</p>
                </a>
                <a href="modules/prescriptions/upload.php" class="bg-purple-500 hover:bg-purple-600 text-white p-4 rounded-lg text-center transition duration-200">
                    <i class="fas fa-upload text-2xl mb-2"></i>
                    <p class="font-medium">Upload Prescription</p>
                </a>
                <a href="modules/customers/add_customer.php" class="bg-orange-500 hover:bg-orange-600 text-white p-4 rounded-lg text-center transition duration-200">
                    <i class="fas fa-user-plus text-2xl mb-2"></i>
                    <p class="font-medium">Add Customer</p>
                </a>
            </div>
        </div>

        <!-- Recent Activity & Alerts -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Recent Sales -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-xl font-semibold text-gray-800 mb-4">Recent Sales</h2>
                <div class="space-y-3">
                    <?php if (!empty($recentSales)): ?>
                        <?php foreach ($recentSales as $sale): ?>
                            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                <div>
                                    <p class="font-medium text-gray-800">#<?php echo htmlspecialchars($sale['invoice_number']); ?></p>
                                    <p class="text-sm text-gray-600"><?php echo htmlspecialchars($sale['customer_name'] ?: 'Walk-in Customer'); ?></p>
                                </div>
                                <div class="text-right">
                                    <p class="font-medium text-green-600">Rs <?php echo number_format($sale['total_amount'], 2); ?></p>
                                    <p class="text-xs text-gray-500"><?php echo date('M d, Y g:i A', strtotime($sale['sale_date'])); ?></p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-gray-500 text-center py-4">No recent sales</p>
                    <?php endif; ?>
                </div>
                <a href="modules/sales/index.php" class="text-green-600 hover:text-green-700 text-sm font-medium mt-4 inline-block">View All Sales →</a>
            </div>

            <!-- Stock Alerts -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-xl font-semibold text-gray-800 mb-4">Stock Alerts</h2>
                <div class="space-y-3">
                    <?php if (!empty($stockAlerts)): ?>
                        <?php foreach ($stockAlerts as $alert): ?>
                            <div class="flex items-center justify-between p-3 bg-yellow-50 rounded-lg border-l-4 border-yellow-400">
                                <div>
                                    <p class="font-medium text-gray-800"><?php echo htmlspecialchars($alert['name']); ?></p>
                                    <p class="text-sm text-gray-600">Low Stock</p>
                                </div>
                                <div class="text-right">
                                    <p class="font-medium text-yellow-600"><?php echo $alert['stock_quantity']; ?> left</p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-gray-500 text-center py-4">No stock alerts</p>
                    <?php endif; ?>
                </div>
                <a href="modules/inventory/index.php" class="text-blue-600 hover:text-blue-700 text-sm font-medium mt-4 inline-block">View Inventory →</a>
            </div>
        </div>
    </div>
</body>
</html>