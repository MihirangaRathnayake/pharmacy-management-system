<?php
require_once dirname(__DIR__, 2) . '/bootstrap.php';

requireLogin();

$supplierId = $_GET['id'] ?? null;

if (!$supplierId) {
    header('Location: index.php');
    exit();
}

try {
    // Get supplier details
    $stmt = $pdo->prepare("SELECT * FROM suppliers WHERE id = ?");
    $stmt->execute([$supplierId]);
    $supplier = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$supplier) {
        header('Location: index.php');
        exit();
    }

    // Get medicines supplied by this supplier
    $stmt = $pdo->prepare("
        SELECT m.*, c.name as category_name
        FROM medicines m
        LEFT JOIN categories c ON m.category_id = c.id
        WHERE m.supplier_id = ? AND m.status = 'active'
        ORDER BY m.name ASC
    ");
    $stmt->execute([$supplierId]);
    $medicines = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Calculate supplier stats
    $statsStmt = $pdo->prepare("
        SELECT 
            COUNT(*) as total_medicines,
            COALESCE(SUM(stock_quantity), 0) as total_stock,
            COALESCE(SUM(stock_quantity * purchase_price), 0) as total_purchase_value,
            COALESCE(SUM(stock_quantity * selling_price), 0) as total_selling_value,
            SUM(CASE WHEN stock_quantity <= min_stock_level THEN 1 ELSE 0 END) as low_stock_count,
            SUM(CASE WHEN expiry_date IS NOT NULL AND expiry_date < CURDATE() THEN 1 ELSE 0 END) as expired_count
        FROM medicines 
        WHERE supplier_id = ? AND status = 'active'
    ");
    $statsStmt->execute([$supplierId]);
    $stats = $statsStmt->fetch(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $error = $e->getMessage();
}

$user = getCurrentUser();
?>
<!DOCTYPE html>
<html lang="en" data-theme="<?php echo getThemeClass(); ?>">

<head>
    <title>View Supplier - <?php echo htmlspecialchars($supplier['name']); ?></title>
    <?php include '../../includes/head.php'; ?>
    <style>
        .info-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            border: 1px solid #e5e7eb;
            overflow: hidden;
        }

        .stat-card {
            padding: 16px;
            border-radius: 10px;
            text-align: center;
        }
    </style>
</head>

<body class="pc-shell">
    <?php include '../../includes/navbar.php'; ?>

    <div class="pc-container">
        <div class="pc-page-header pc-animate">
            <div class="pc-breadcrumb">Home <i class="fas fa-chevron-right"></i> <a href="index.php" class="text-blue-600 hover:underline">Suppliers</a> <i class="fas fa-chevron-right"></i> <?php echo htmlspecialchars($supplier['name']); ?></div>
            <div class="flex justify-between items-center gap-4">
                <div>
                    <h1 class="pc-page-title"><?php echo htmlspecialchars($supplier['name']); ?></h1>
                    <p class="pc-page-subtitle">Supplier details and supplied medicines</p>
                </div>
                <div class="flex gap-2">
                    <a href="edit_supplier.php?id=<?php echo $supplier['id']; ?>" class="pc-btn pc-btn-secondary">
                        <i class="fas fa-edit"></i>
                        <span>Edit</span>
                    </a>
                    <a href="index.php" class="pc-btn pc-btn-muted">
                        <i class="fas fa-arrow-left"></i>
                        <span>Back</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- Stats -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
            <div class="stat-card bg-blue-50 border border-blue-200">
                <p class="text-2xl font-bold text-blue-700"><?php echo $stats['total_medicines']; ?></p>
                <p class="text-xs text-blue-500">Medicines</p>
            </div>
            <div class="stat-card bg-green-50 border border-green-200">
                <p class="text-2xl font-bold text-green-700"><?php echo number_format($stats['total_stock']); ?></p>
                <p class="text-xs text-green-500">Total Stock</p>
            </div>
            <div class="stat-card bg-amber-50 border border-amber-200">
                <p class="text-2xl font-bold text-amber-700"><?php echo $stats['low_stock_count']; ?></p>
                <p class="text-xs text-amber-500">Low Stock</p>
            </div>
            <div class="stat-card bg-red-50 border border-red-200">
                <p class="text-2xl font-bold text-red-700"><?php echo $stats['expired_count']; ?></p>
                <p class="text-xs text-red-500">Expired</p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Supplier Info -->
            <div class="info-card p-6">
                <div class="flex items-center gap-4 mb-6">
                    <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-truck text-blue-600 text-2xl"></i>
                    </div>
                    <div>
                        <h2 class="text-xl font-bold text-gray-800"><?php echo htmlspecialchars($supplier['name']); ?></h2>
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium 
                            <?php echo $supplier['status'] === 'active' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'; ?>">
                            <?php echo ucfirst($supplier['status']); ?>
                        </span>
                    </div>
                </div>

                <div class="space-y-4">
                    <?php if ($supplier['contact_person']): ?>
                        <div class="flex items-start gap-3">
                            <i class="fas fa-user text-gray-400 mt-1 w-5"></i>
                            <div>
                                <p class="text-xs text-gray-400">Contact Person</p>
                                <p class="text-sm font-medium text-gray-700"><?php echo htmlspecialchars($supplier['contact_person']); ?></p>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if ($supplier['email']): ?>
                        <div class="flex items-start gap-3">
                            <i class="fas fa-envelope text-gray-400 mt-1 w-5"></i>
                            <div>
                                <p class="text-xs text-gray-400">Email</p>
                                <p class="text-sm font-medium text-gray-700"><?php echo htmlspecialchars($supplier['email']); ?></p>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if ($supplier['phone']): ?>
                        <div class="flex items-start gap-3">
                            <i class="fas fa-phone text-gray-400 mt-1 w-5"></i>
                            <div>
                                <p class="text-xs text-gray-400">Phone</p>
                                <p class="text-sm font-medium text-gray-700"><?php echo htmlspecialchars($supplier['phone']); ?></p>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if ($supplier['address']): ?>
                        <div class="flex items-start gap-3">
                            <i class="fas fa-map-marker-alt text-gray-400 mt-1 w-5"></i>
                            <div>
                                <p class="text-xs text-gray-400">Address</p>
                                <p class="text-sm font-medium text-gray-700"><?php echo htmlspecialchars($supplier['address']); ?></p>
                            </div>
                        </div>
                    <?php endif; ?>

                    <div class="flex items-start gap-3">
                        <i class="fas fa-calendar text-gray-400 mt-1 w-5"></i>
                        <div>
                            <p class="text-xs text-gray-400">Added On</p>
                            <p class="text-sm font-medium text-gray-700"><?php echo date('M d, Y', strtotime($supplier['created_at'])); ?></p>
                        </div>
                    </div>
                </div>

                <div class="mt-6 p-4 bg-gray-50 rounded-lg">
                    <p class="text-xs text-gray-500 mb-1">Purchase Value (Stock)</p>
                    <p class="text-lg font-bold text-gray-800">Rs <?php echo number_format($stats['total_purchase_value'], 2); ?></p>
                    <p class="text-xs text-gray-500 mt-2 mb-1">Selling Value (Stock)</p>
                    <p class="text-lg font-bold text-green-600">Rs <?php echo number_format($stats['total_selling_value'], 2); ?></p>
                </div>
            </div>

            <!-- Medicines List -->
            <div class="lg:col-span-2">
                <div class="info-card">
                    <div class="p-4 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-800">
                            <i class="fas fa-pills mr-2 text-blue-500"></i>Supplied Medicines (<?php echo count($medicines); ?>)
                        </h3>
                    </div>

                    <?php if (empty($medicines)): ?>
                        <div class="p-8 text-center">
                            <i class="fas fa-pills text-4xl text-gray-300 mb-3"></i>
                            <p class="text-gray-500">No medicines from this supplier yet</p>
                        </div>
                    <?php else: ?>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Medicine</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Category</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Stock</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Price</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Expiry</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <?php foreach ($medicines as $med): ?>
                                        <?php
                                        $isLowStock = $med['stock_quantity'] <= $med['min_stock_level'];
                                        $isExpired = $med['expiry_date'] && strtotime($med['expiry_date']) < time();
                                        ?>
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-4 py-3">
                                                <p class="text-sm font-medium text-gray-800"><?php echo htmlspecialchars($med['name']); ?></p>
                                                <?php if ($med['generic_name']): ?>
                                                    <p class="text-xs text-gray-400"><?php echo htmlspecialchars($med['generic_name']); ?></p>
                                                <?php endif; ?>
                                            </td>
                                            <td class="px-4 py-3 text-sm text-gray-600"><?php echo htmlspecialchars($med['category_name'] ?? 'N/A'); ?></td>
                                            <td class="px-4 py-3">
                                                <span class="text-sm font-medium <?php echo $isLowStock ? 'text-red-600' : 'text-gray-800'; ?>">
                                                    <?php echo $med['stock_quantity']; ?>
                                                </span>
                                                <?php if ($isLowStock): ?>
                                                    <span class="text-xs text-red-500 block">Low stock</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="px-4 py-3 text-sm text-gray-600">Rs <?php echo number_format($med['selling_price'], 2); ?></td>
                                            <td class="px-4 py-3">
                                                <span class="text-sm <?php echo $isExpired ? 'text-red-600 font-medium' : 'text-gray-600'; ?>">
                                                    <?php echo $med['expiry_date'] ? date('M Y', strtotime($med['expiry_date'])) : 'N/A'; ?>
                                                </span>
                                                <?php if ($isExpired): ?>
                                                    <span class="text-xs text-red-500 block">Expired</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script src="../../assets/js/icon-fix.js"></script>
</body>

</html>