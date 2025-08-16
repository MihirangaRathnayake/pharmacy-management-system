<?php
require_once __DIR__ . '/bootstrap.php';

// Redirect to login if not authenticated
if (!isLoggedIn()) {
    header('Location: auth/login.php');
    exit();
}

// Check user role and redirect accordingly
$user = getCurrentUser();
if ($user['role'] === 'customer') {
    header('Location: customer/index.html');
    exit();
} elseif ($user['role'] === 'pharmacist') {
    header('Location: pharmacist/index.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en" data-theme="<?php echo getThemeClass(); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Pharmacy Management System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <?php echo getThemeCSS(); ?>
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
    <?php renderThemeScript(); ?>
</head>
<body class="bg-gray-50">
    <?php include 'includes/navbar.php'; ?>
    
    <div class="container mx-auto px-4 py-8">
        <!-- Dashboard Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-800 mb-2">Admin Dashboard</h1>
            <p class="text-gray-600">Welcome back, <?php echo htmlspecialchars($user['name']); ?>!</p>
        </div>

        <!-- Quick Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-green-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Today's Sales</p>
                        <p class="text-2xl font-bold text-gray-900" id="todaySales">Rs 0</p>
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
                        <p class="text-2xl font-bold text-gray-900" id="lowStockCount">0</p>
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
                        <p class="text-2xl font-bold text-gray-900" id="pendingOrders">0</p>
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
                        <p class="text-2xl font-bold text-gray-900" id="totalCustomers">0</p>
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
                <div id="recentSales" class="space-y-3">
                    <p class="text-gray-500 text-center py-4">Loading...</p>
                </div>
                <a href="modules/sales/index.php" class="text-green-600 hover:text-green-700 text-sm font-medium mt-4 inline-block">View All Sales →</a>
            </div>

            <!-- Stock Alerts -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-xl font-semibold text-gray-800 mb-4">Stock Alerts</h2>
                <div id="stockAlerts" class="space-y-3">
                    <p class="text-gray-500 text-center py-4">Loading...</p>
                </div>
                <a href="modules/inventory/index.php" class="text-blue-600 hover:text-blue-700 text-sm font-medium mt-4 inline-block">View Inventory →</a>
            </div>
        </div>
    </div>

    <script src="assets/js/dashboard.js"></script>
</body>
</html>