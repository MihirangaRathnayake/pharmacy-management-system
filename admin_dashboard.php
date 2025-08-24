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
    <!-- FontAwesome Icons - Multiple CDN fallbacks -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" integrity="sha512-iecdLmaskl7CVkqkXNQ/ZH/XLlvWZOJyj7Yy7tcenmpD1ypASozpmT/E0iPtmFIB46ZmdtAc9eNBvH0H/ZpiBw==" crossorigin="anonymous" referrerpolicy="no-referrer">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v6.4.0/css/all.css">
    <!-- FontAwesome 5 fallback -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" integrity="sha512-1ycn6IcaQQ40/MKBW2W4Rhis/DbILU74C1vSrLJxCq57o941Ym01SwNsOMqvEBFlcgUa6xLiPY/NS5R+E6ztJQ==" crossorigin="anonymous" referrerpolicy="no-referrer">
    
    <link rel="stylesheet" href="assets/css/admin-icons-fix.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <?php echo getThemeCSS(); ?>
    <style>
        body { font-family: 'Inter', sans-serif; }
        
        /* Modal animations */
        .modal-enter {
            animation: modalEnter 0.3s ease-out;
        }
        
        @keyframes modalEnter {
            from {
                opacity: 0;
                transform: scale(0.9);
            }
            to {
                opacity: 1;
                transform: scale(1);
            }
        }
    </style>
    <?php renderThemeScript(); ?>
</head>
<body class="bg-gray-50">
    <?php include 'includes/navbar.php'; ?>
    
    <div class="container mx-auto px-4 py-8">
        <!-- Dashboard Header -->
        <div class="mb-8">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold text-gray-800 mb-2">Admin Dashboard</h1>
                    <p class="text-gray-600">Welcome back, <?php echo htmlspecialchars($user['name']); ?>!</p>
                </div>
                <div class="flex items-center space-x-4">
                    <div class="text-right">
                        <p class="text-sm text-gray-500">Logged in as</p>
                        <p class="font-medium text-gray-800"><?php echo htmlspecialchars($user['name']); ?></p>
                        <p class="text-xs text-gray-500"><?php echo ucfirst($user['role']); ?></p>
                    </div>
                    <div class="flex flex-col space-y-2">
                        <a href="modules/settings/index.php" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-lg text-sm font-medium transition duration-200 flex items-center">
                            <i class="fas fa-cog mr-2"></i>
                            Settings
                        </a>
                        <button onclick="confirmLogout()" class="bg-red-100 hover:bg-red-200 text-red-700 px-4 py-2 rounded-lg text-sm font-medium transition duration-200 flex items-center">
                            <i class="fas fa-sign-out-alt mr-2"></i>
                            Logout
                        </button>
                    </div>
                </div>
            </div>
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

    <!-- Logout Confirmation Modal -->
    <div id="logoutModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden items-center justify-center z-50">
        <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4 modal-enter">
            <div class="flex items-center mb-4">
                <div class="flex-shrink-0">
                    <i class="fas fa-sign-out-alt text-red-600 text-2xl"></i>
                </div>
                <div class="ml-3">
                    <h3 class="text-lg font-medium text-gray-900">Confirm Logout</h3>
                </div>
            </div>
            <div class="mb-4">
                <p class="text-sm text-gray-500">
                    Are you sure you want to logout? You will need to login again to access the system.
                </p>
            </div>
            <div class="flex justify-end space-x-3">
                <button onclick="closeLogoutModal()" 
                        class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-md transition-colors duration-200">
                    Cancel
                </button>
                <button onclick="performLogout()" 
                        class="px-4 py-2 text-sm font-medium text-white bg-red-600 hover:bg-red-700 rounded-md transition-colors duration-200">
                    <i class="fas fa-sign-out-alt mr-1"></i>
                    Logout
                </button>
            </div>
        </div>
    </div>

    <script src="assets/js/admin-icons-fix.js"></script>
    <script src="assets/js/dashboard.js"></script>
    
    <script>
        function confirmLogout() {
            document.getElementById('logoutModal').classList.remove('hidden');
            document.getElementById('logoutModal').classList.add('flex');
        }
        
        function closeLogoutModal() {
            document.getElementById('logoutModal').classList.add('hidden');
            document.getElementById('logoutModal').classList.remove('flex');
        }
        
        function performLogout() {
            // Show loading state
            const logoutBtn = document.querySelector('#logoutModal button[onclick="performLogout()"]');
            const originalText = logoutBtn.innerHTML;
            logoutBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i>Logging out...';
            logoutBtn.disabled = true;
            
            // Add a small delay for better UX
            setTimeout(() => {
                window.location.href = 'auth/logout.php';
            }, 500);
        }
        
        // Close modal when clicking outside
        document.getElementById('logoutModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeLogoutModal();
            }
        });
        
        // Close modal with Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeLogoutModal();
            }
        });
        
        // Add logout confirmation to navbar logout link as well
        document.addEventListener('DOMContentLoaded', function() {
            const navLogoutLink = document.querySelector('a[href*="auth/logout.php"]');
            if (navLogoutLink) {
                navLogoutLink.addEventListener('click', function(e) {
                    e.preventDefault();
                    confirmLogout();
                });
            }
        });
    </script>
</body>
</html>