<?php
require_once dirname(__DIR__) . '/bootstrap.php';

// Redirect to login if not authenticated
if (!isLoggedIn()) {
    header('Location: ../auth/login.php');
    exit();
}

// Check if user is pharmacist
$user = getCurrentUser();
if ($user['role'] !== 'pharmacist') {
    header('Location: ../index.php');
    exit();
}

// Get pharmacist-specific data
try {
    // Today's sales
    $stmt = $pdo->prepare("
        SELECT COALESCE(SUM(total_amount), 0) as today_sales 
        FROM sales 
        WHERE DATE(sale_date) = CURDATE() AND status = 'completed'
    ");
    $stmt->execute();
    $todaySales = $stmt->fetchColumn();

    // Pending prescriptions
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as pending_prescriptions 
        FROM prescriptions 
        WHERE status = 'pending'
    ");
    $stmt->execute();
    $pendingPrescriptions = $stmt->fetchColumn();

    // Low stock medicines
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as low_stock_count 
        FROM medicines 
        WHERE stock_quantity <= min_stock_level AND status = 'active'
    ");
    $stmt->execute();
    $lowStockCount = $stmt->fetchColumn();

    // Recent prescriptions
    $stmt = $pdo->prepare("
        SELECT p.*, c.name as customer_name
        FROM prescriptions p
        LEFT JOIN customers c ON p.customer_id = c.id
        WHERE p.status = 'pending'
        ORDER BY p.created_at DESC
        LIMIT 5
    ");
    $stmt->execute();
    $recentPrescriptions = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    $error = $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pharmacist Dashboard - PharmaCare</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- FontAwesome Icons - Multiple CDN fallbacks -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" integrity="sha512-iecdLmaskl7CVkqkXNQ/ZH/XLlvWZOJyj7Yy7tcenmpD1ypASozpmT/E0iPtmFIB46ZmdtAc9eNBvH0H/ZpiBw==" crossorigin="anonymous" referrerpolicy="no-referrer">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v6.4.0/css/all.css">
    <!-- FontAwesome 5 fallback -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" integrity="sha512-1ycn6IcaQQ40/MKBW2W4Rhis/DbILU74C1vSrLJxCq57o941Ym01SwNsOMqvEBFlcgUa6xLiPY/NS5R+E6ztJQ==" crossorigin="anonymous" referrerpolicy="no-referrer">
    
    <link rel="stylesheet" href="../assets/css/admin-icons-fix.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Navigation -->
    <nav class="bg-white shadow-lg sticky top-0 z-50">
        <div class="container mx-auto px-4">
            <div class="flex justify-between items-center py-4">
                <div class="flex items-center space-x-2">
                    <i class="fas fa-pills text-green-600 text-2xl"></i>
                    <span class="text-xl font-bold text-gray-800">PharmaCare</span>
                    <span class="text-sm bg-blue-100 text-blue-800 px-2 py-1 rounded-full">Pharmacist</span>
                </div>
                
                <div class="flex items-center space-x-4">
                    <span class="text-gray-700">Welcome, <?php echo htmlspecialchars($user['name']); ?></span>
                    <a href="../auth/logout.php" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg transition duration-200">
                        <i class="fas fa-sign-out-alt mr-2"></i>Logout
                    </a>
                </div>
            </div>
        </div>
    </nav>
    
    <div class="container mx-auto px-4 py-8">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-800 mb-2">Pharmacist Dashboard</h1>
            <p class="text-gray-600">Manage prescriptions, sales, and inventory</p>
        </div>

        <!-- Quick Stats -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
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
                        <p class="text-sm font-medium text-gray-600">Pending Prescriptions</p>
                        <p class="text-2xl font-bold text-gray-900"><?php echo $pendingPrescriptions ?? 0; ?></p>
                    </div>
                    <div class="bg-blue-100 p-3 rounded-full">
                        <i class="fas fa-file-medical text-blue-600"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-yellow-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Low Stock Items</p>
                        <p class="text-2xl font-bold text-gray-900"><?php echo $lowStockCount ?? 0; ?></p>
                    </div>
                    <div class="bg-yellow-100 p-3 rounded-full">
                        <i class="fas fa-exclamation-triangle text-yellow-600"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-8">
            <h2 class="text-xl font-semibold text-gray-800 mb-4">Quick Actions</h2>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <a href="../modules/sales/new_sale.php" class="bg-green-500 hover:bg-green-600 text-white p-4 rounded-lg text-center transition duration-200">
                    <i class="fas fa-shopping-cart text-2xl mb-2"></i>
                    <p class="font-medium">New Sale</p>
                </a>
                <a href="../modules/prescriptions/index.php" class="bg-blue-500 hover:bg-blue-600 text-white p-4 rounded-lg text-center transition duration-200">
                    <i class="fas fa-file-medical text-2xl mb-2"></i>
                    <p class="font-medium">Verify Prescriptions</p>
                </a>
                <a href="../modules/inventory/index.php" class="bg-purple-500 hover:bg-purple-600 text-white p-4 rounded-lg text-center transition duration-200">
                    <i class="fas fa-boxes text-2xl mb-2"></i>
                    <p class="font-medium">Check Inventory</p>
                </a>
                <a href="../modules/customers/index.php" class="bg-orange-500 hover:bg-orange-600 text-white p-4 rounded-lg text-center transition duration-200">
                    <i class="fas fa-users text-2xl mb-2"></i>
                    <p class="font-medium">Customer Records</p>
                </a>
            </div>
        </div>

        <!-- Pending Prescriptions -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold text-gray-800 mb-4">Pending Prescriptions</h2>
            <div class="space-y-3">
                <?php if (!empty($recentPrescriptions)): ?>
                    <?php foreach ($recentPrescriptions as $prescription): ?>
                        <div class="flex items-center justify-between p-4 bg-yellow-50 rounded-lg border-l-4 border-yellow-400">
                            <div>
                                <p class="font-medium text-gray-800"><?php echo htmlspecialchars($prescription['customer_name']); ?></p>
                                <p class="text-sm text-gray-600">Dr. <?php echo htmlspecialchars($prescription['doctor_name'] ?: 'Unknown'); ?></p>
                                <p class="text-xs text-gray-500"><?php echo date('M d, Y g:i A', strtotime($prescription['created_at'])); ?></p>
                            </div>
                            <div class="flex space-x-2">
                                <a href="../modules/prescriptions/verify.php?id=<?php echo $prescription['id']; ?>" 
                                   class="bg-green-600 hover:bg-green-700 text-white px-3 py-1 rounded text-sm transition duration-200">
                                    <i class="fas fa-check mr-1"></i>Verify
                                </a>
                                <a href="../modules/prescriptions/view.php?id=<?php echo $prescription['id']; ?>" 
                                   class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded text-sm transition duration-200">
                                    <i class="fas fa-eye mr-1"></i>View
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="text-gray-500 text-center py-8">No pending prescriptions</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <script src="../assets/js/admin-icons-fix.js"></script>
</body>
</html>