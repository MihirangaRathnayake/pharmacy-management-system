<?php
session_start();
require_once dirname(__DIR__, 2) . '/bootstrap.php';

requireLogin();

// Get filter parameters
$search = $_GET['search'] ?? '';
$status_filter = $_GET['status'] ?? 'active';
$page = max(1, intval($_GET['page'] ?? 1));
$limit = 20;
$offset = ($page - 1) * $limit;

// Build query
$whereConditions = [];
$params = [];

if ($status_filter && $status_filter !== 'all') {
    $whereConditions[] = "s.status = :status";
    $params['status'] = $status_filter;
}

if ($search) {
    $whereConditions[] = "(s.name LIKE :search OR s.contact_person LIKE :search OR s.email LIKE :search OR s.phone LIKE :search)";
    $params['search'] = "%$search%";
}

$whereClause = $whereConditions ? 'WHERE ' . implode(' AND ', $whereConditions) : '';

// Get suppliers
$stmt = $pdo->prepare("
    SELECT s.*, 
           COUNT(DISTINCT m.id) as total_medicines,
           COALESCE(SUM(m.stock_quantity), 0) as total_stock
    FROM suppliers s
    LEFT JOIN medicines m ON s.id = m.supplier_id AND m.status = 'active'
    $whereClause
    GROUP BY s.id
    ORDER BY s.created_at DESC
    LIMIT :limit OFFSET :offset
");

foreach ($params as $key => $value) {
    $stmt->bindValue(":$key", $value);
}
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$suppliers = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get total count
$countStmt = $pdo->prepare("SELECT COUNT(*) FROM suppliers s $whereClause");
foreach ($params as $key => $value) {
    $countStmt->bindValue(":$key", $value);
}
$countStmt->execute();
$totalRecords = $countStmt->fetchColumn();
$totalPages = ceil($totalRecords / $limit);

// Get stats
$statsStmt = $pdo->query("
    SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active,
        SUM(CASE WHEN status = 'inactive' THEN 1 ELSE 0 END) as inactive
    FROM suppliers
");
$stats = $statsStmt->fetch(PDO::FETCH_ASSOC);

$user = getCurrentUser();
?>
<!DOCTYPE html>
<html lang="en" data-theme="<?php echo getThemeClass(); ?>">

<head>
    <title>Supplier Management - Pharmacy Management System</title>
    <?php include '../../includes/head.php'; ?>
    <?php require_once '../../includes/action_buttons.php';
    echo renderActionButtonsCSS(); ?>
</head>

<body class="pc-shell">
    <?php include '../../includes/navbar.php'; ?>

    <div class="pc-container">
        <div class="pc-page-header pc-animate">
            <div class="pc-breadcrumb">Home <i class="fas fa-chevron-right"></i> Suppliers</div>
            <div class="flex justify-between items-center gap-4">
                <div>
                    <h1 class="pc-page-title">Supplier Management</h1>
                    <p class="pc-page-subtitle">Manage suppliers, track medicine sourcing, and contact details</p>
                </div>
                <a href="add_supplier.php" class="pc-btn pc-btn-primary">
                    <i class="fas fa-plus"></i>
                    <span>Add Supplier</span>
                </a>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <div class="pc-card p-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-truck text-blue-600"></i>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-gray-800"><?php echo $stats['total']; ?></p>
                        <p class="text-xs text-gray-500">Total Suppliers</p>
                    </div>
                </div>
            </div>
            <div class="pc-card p-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-check-circle text-green-600"></i>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-green-600"><?php echo $stats['active']; ?></p>
                        <p class="text-xs text-gray-500">Active Suppliers</p>
                    </div>
                </div>
            </div>
            <div class="pc-card p-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-red-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-times-circle text-red-600"></i>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-red-600"><?php echo $stats['inactive']; ?></p>
                        <p class="text-xs text-gray-500">Inactive Suppliers</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Search & Filter -->
        <div class="pc-toolbar mb-6">
            <form method="GET" class="flex flex-wrap gap-3">
                <div class="flex-1 min-w-[200px]">
                    <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>"
                        placeholder="Search suppliers by name, contact person, email, or phone..."
                        class="pc-input">
                </div>
                <select name="status" class="pc-select w-auto" onchange="this.form.submit()">
                    <option value="active" <?php echo $status_filter === 'active' ? 'selected' : ''; ?>>Active</option>
                    <option value="inactive" <?php echo $status_filter === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                    <option value="all" <?php echo $status_filter === 'all' ? 'selected' : ''; ?>>All</option>
                </select>
                <button type="submit" class="pc-btn pc-btn-secondary">
                    <i class="fas fa-search mr-2"></i>Search
                </button>
            </form>
        </div>

        <!-- Suppliers Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach ($suppliers as $supplier): ?>
                <div class="pc-card p-6">
                    <div class="flex items-center space-x-4 mb-4">
                        <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                            <i class="fas fa-truck text-blue-600 text-xl"></i>
                        </div>
                        <div class="flex-1">
                            <h3 class="text-lg font-semibold text-gray-800"><?php echo htmlspecialchars($supplier['name']); ?></h3>
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium 
                                <?php echo $supplier['status'] === 'active' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'; ?>">
                                <?php echo ucfirst($supplier['status']); ?>
                            </span>
                        </div>
                    </div>

                    <div class="space-y-2 mb-4">
                        <?php if ($supplier['contact_person']): ?>
                            <div class="flex items-center text-sm text-gray-600">
                                <i class="fas fa-user w-4 mr-2"></i>
                                <span><?php echo htmlspecialchars($supplier['contact_person']); ?></span>
                            </div>
                        <?php endif; ?>

                        <?php if ($supplier['phone']): ?>
                            <div class="flex items-center text-sm text-gray-600">
                                <i class="fas fa-phone w-4 mr-2"></i>
                                <span><?php echo htmlspecialchars($supplier['phone']); ?></span>
                            </div>
                        <?php endif; ?>

                        <?php if ($supplier['email']): ?>
                            <div class="flex items-center text-sm text-gray-600">
                                <i class="fas fa-envelope w-4 mr-2"></i>
                                <span><?php echo htmlspecialchars($supplier['email']); ?></span>
                            </div>
                        <?php endif; ?>

                        <?php if ($supplier['address']): ?>
                            <div class="flex items-center text-sm text-gray-600">
                                <i class="fas fa-map-marker-alt w-4 mr-2"></i>
                                <span><?php echo htmlspecialchars($supplier['address']); ?></span>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Stats -->
                    <div class="grid grid-cols-2 gap-4 mb-4 p-3 bg-gray-50 rounded-lg">
                        <div class="text-center">
                            <p class="text-lg font-bold text-blue-600"><?php echo $supplier['total_medicines']; ?></p>
                            <p class="text-xs text-gray-500">Medicines</p>
                        </div>
                        <div class="text-center">
                            <p class="text-lg font-bold text-green-600"><?php echo number_format($supplier['total_stock']); ?></p>
                            <p class="text-xs text-gray-500">Total Stock</p>
                        </div>
                    </div>

                    <p class="text-xs text-gray-400 mb-4">
                        Added: <?php echo date('M d, Y', strtotime($supplier['created_at'])); ?>
                    </p>

                    <!-- Actions -->
                    <div class="flex space-x-1">
                        <a href="view_supplier.php?id=<?php echo $supplier['id']; ?>"
                            class="action-btn flex-1 bg-blue-100 hover:bg-blue-200 text-blue-700 text-center py-2 px-3 rounded-md text-sm font-medium">
                            <i class="fas fa-eye mr-1"></i>View
                        </a>
                        <a href="edit_supplier.php?id=<?php echo $supplier['id']; ?>"
                            class="action-btn flex-1 bg-green-100 hover:bg-green-200 text-green-700 text-center py-2 px-3 rounded-md text-sm font-medium">
                            <i class="fas fa-edit mr-1"></i>Edit
                        </a>
                        <button onclick="deleteSupplier(<?php echo $supplier['id']; ?>, '<?php echo htmlspecialchars($supplier['name'], ENT_QUOTES); ?>')"
                            class="action-btn bg-red-100 hover:bg-red-200 text-red-700 py-2 px-3 rounded-md text-sm font-medium">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <?php if (empty($suppliers)): ?>
            <div class="text-center py-12">
                <i class="fas fa-truck text-6xl text-gray-300 mb-4"></i>
                <h3 class="text-xl font-medium text-gray-500 mb-2">No suppliers found</h3>
                <p class="text-gray-400 mb-4">Start by adding your first supplier</p>
                <a href="add_supplier.php" class="pc-btn pc-btn-primary">
                    <i class="fas fa-plus"></i>
                    <span>Add Supplier</span>
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

    <?php echo renderDeleteModal('Supplier'); ?>
    <?php echo renderToast(); ?>
    <?php echo renderDeleteScript('delete_supplier.php', 'deleteSupplier'); ?>

    <script src="../../assets/js/icon-fix.js"></script>
</body>

</html>