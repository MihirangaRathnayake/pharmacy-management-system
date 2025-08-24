<?php
session_start();
require_once dirname(__DIR__, 2) . '/bootstrap.php';

requireLogin();

// Get filter parameters
$search = $_GET['search'] ?? '';
$page = max(1, intval($_GET['page'] ?? 1));
$limit = 20;
$offset = ($page - 1) * $limit;

// Build query
$whereConditions = ["c.status = 'active'"];
$params = [];

if ($search) {
    $whereConditions[] = "(c.name LIKE :search OR c.phone LIKE :search OR c.email LIKE :search OR c.customer_code LIKE :search)";
    $params['search'] = "%$search%";
}

$whereClause = implode(' AND ', $whereConditions);

// Get customers
$stmt = $pdo->prepare("
    SELECT c.*, 
           COUNT(DISTINCT s.id) as total_orders,
           COALESCE(SUM(s.total_amount), 0) as total_spent,
           MAX(s.sale_date) as last_order_date
    FROM customers c
    LEFT JOIN sales s ON c.id = s.customer_id AND s.status = 'completed'
    WHERE $whereClause
    GROUP BY c.id
    ORDER BY c.created_at DESC
    LIMIT :limit OFFSET :offset
");

foreach ($params as $key => $value) {
    $stmt->bindValue(":$key", $value);
}
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$customers = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get total count for pagination
$countStmt = $pdo->prepare("SELECT COUNT(*) FROM customers c WHERE $whereClause");
foreach ($params as $key => $value) {
    $countStmt->bindValue(":$key", $value);
}
$countStmt->execute();
$totalRecords = $countStmt->fetchColumn();
$totalPages = ceil($totalRecords / $limit);

$user = getCurrentUser();
?>
<!DOCTYPE html>
<html lang="en" data-theme="<?php echo getThemeClass(); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Management - Pharmacy Management System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- FontAwesome Icons - Multiple CDN fallbacks -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" integrity="sha512-iecdLmaskl7CVkqkXNQ/ZH/XLlvWZOJyj7Yy7tcenmpD1ypASozpmT/E0iPtmFIB46ZmdtAc9eNBvH0H/ZpiBw==" crossorigin="anonymous" referrerpolicy="no-referrer">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v6.4.0/css/all.css">
    <!-- FontAwesome 5 fallback -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" integrity="sha512-1ycn6IcaQQ40/MKBW2W4Rhis/DbILU74C1vSrLJxCq57o941Ym01SwNsOMqvEBFlcgUa6xLiPY/NS5R+E6ztJQ==" crossorigin="anonymous" referrerpolicy="no-referrer">
    
    <link rel="stylesheet" href="../../assets/css/admin-icons-fix.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <?php echo getThemeCSS(); ?>
    <?php 
    require_once '../../includes/action_buttons.php';
    echo renderActionButtonsCSS(); 
    ?>
    <style>
        body { font-family: 'Inter', sans-serif; }
        
        /* Customer card hover effects */
        .customer-card {
            transition: all 0.3s ease-in-out;
        }
        
        .customer-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
        }
    </style>
    <?php renderThemeScript(); ?>
</head>
<body class="bg-gray-50">
    <?php include '../../includes/navbar.php'; ?>
    
    <div class="container mx-auto px-4 py-8">
        <!-- Header -->
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-3xl font-bold text-gray-800">Customer Management</h1>
                <p class="text-gray-600">Manage your customer database</p>
            </div>
            <a href="add_customer.php" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg flex items-center space-x-2 transition duration-200">
                <i class="fas fa-user-plus"></i>
                <span>Add Customer</span>
            </a>
        </div>

        <!-- Search -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <form method="GET" class="flex space-x-4">
                <div class="flex-1">
                    <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" 
                           placeholder="Search customers by name, phone, email, or customer code..." 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500">
                </div>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-md transition duration-200">
                    <i class="fas fa-search mr-2"></i>Search
                </button>
            </form>
        </div>

        <!-- Customers Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach ($customers as $customer): ?>
                <div class="bg-white rounded-lg shadow-md p-6 customer-card">
                    <div class="flex items-center space-x-4 mb-4">
                        <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
                            <i class="fas fa-user text-green-600 text-xl"></i>
                        </div>
                        <div class="flex-1">
                            <h3 class="text-lg font-semibold text-gray-800"><?php echo htmlspecialchars($customer['name']); ?></h3>
                            <p class="text-sm text-gray-500"><?php echo htmlspecialchars($customer['customer_code']); ?></p>
                        </div>
                    </div>

                    <div class="space-y-2 mb-4">
                        <?php if ($customer['phone']): ?>
                            <div class="flex items-center text-sm text-gray-600">
                                <i class="fas fa-phone w-4 mr-2"></i>
                                <span><?php echo htmlspecialchars($customer['phone']); ?></span>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($customer['email']): ?>
                            <div class="flex items-center text-sm text-gray-600">
                                <i class="fas fa-envelope w-4 mr-2"></i>
                                <span><?php echo htmlspecialchars($customer['email']); ?></span>
                            </div>
                        <?php endif; ?>
                        
                        <div class="flex items-center text-sm text-gray-600">
                            <i class="fas fa-calendar w-4 mr-2"></i>
                            <span>Joined <?php echo date('M d, Y', strtotime($customer['created_at'])); ?></span>
                        </div>
                    </div>

                    <!-- Stats -->
                    <div class="grid grid-cols-2 gap-4 mb-4 p-3 bg-gray-50 rounded-lg">
                        <div class="text-center">
                            <p class="text-lg font-bold text-green-600"><?php echo $customer['total_orders']; ?></p>
                            <p class="text-xs text-gray-500">Orders</p>
                        </div>
                        <div class="text-center">
                            <p class="text-lg font-bold text-blue-600">Rs <?php echo number_format($customer['total_spent'], 0); ?></p>
                            <p class="text-xs text-gray-500">Total Spent</p>
                        </div>
                    </div>

                    <?php if ($customer['last_order_date']): ?>
                        <p class="text-xs text-gray-500 mb-4">
                            Last order: <?php echo date('M d, Y', strtotime($customer['last_order_date'])); ?>
                        </p>
                    <?php endif; ?>

                    <!-- Actions -->
                    <div class="flex space-x-1">
                        <a href="view_customer.php?id=<?php echo $customer['id']; ?>" 
                           class="action-btn flex-1 bg-blue-100 hover:bg-blue-200 text-blue-700 text-center py-2 px-3 rounded-md text-sm font-medium">
                            <i class="fas fa-eye mr-1"></i>View
                        </a>
                        <a href="edit_customer.php?id=<?php echo $customer['id']; ?>" 
                           class="action-btn flex-1 bg-green-100 hover:bg-green-200 text-green-700 text-center py-2 px-3 rounded-md text-sm font-medium">
                            <i class="fas fa-edit mr-1"></i>Edit
                        </a>
                        <button onclick="deleteCustomer(<?php echo $customer['id']; ?>, '<?php echo htmlspecialchars($customer['name'], ENT_QUOTES); ?>')" 
                                class="action-btn bg-red-100 hover:bg-red-200 text-red-700 py-2 px-3 rounded-md text-sm font-medium">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <?php if (empty($customers)): ?>
            <div class="text-center py-12">
                <i class="fas fa-users text-6xl text-gray-300 mb-4"></i>
                <h3 class="text-xl font-medium text-gray-500 mb-2">No customers found</h3>
                <p class="text-gray-400 mb-4">Start by adding your first customer</p>
                <a href="add_customer.php" class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded-lg inline-flex items-center space-x-2 transition duration-200">
                    <i class="fas fa-user-plus"></i>
                    <span>Add Customer</span>
                </a>
            </div>
        <?php endif; ?>

        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
            <div class="mt-8 flex justify-center">
                <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px">
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $i])); ?>" 
                           class="relative inline-flex items-center px-4 py-2 border text-sm font-medium 
                                  <?php echo $i === $page ? 'z-10 bg-green-50 border-green-500 text-green-600' : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50'; ?>">
                            <?php echo $i; ?>
                        </a>
                    <?php endfor; ?>
                </nav>
            </div>
        <?php endif; ?>
    </div>

    <?php echo renderDeleteModal('Customer'); ?>
    <?php echo renderToast(); ?>
    <?php echo renderDeleteScript('delete_customer.php', 'deleteCustomer'); ?>
</body>
</html>