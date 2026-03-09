<?php
require_once dirname(__DIR__, 2) . '/bootstrap.php';
requireLogin();

$user = getCurrentUser();

// Get sales data
$sales = [];
$totalSales = 0;
$todaySales = 0;

try {
    if ($pdo) {
        // Get all sales with customer info
        $stmt = $pdo->query("
            SELECT s.*, c.name as customer_name, c.phone as customer_phone,
                   u.name as cashier_name
            FROM sales s 
            LEFT JOIN customers c ON s.customer_id = c.id 
            LEFT JOIN users u ON s.user_id = u.id 
            ORDER BY s.created_at DESC 
            LIMIT 50
        ");
        $sales = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Get total sales
        $stmt = $pdo->query("SELECT SUM(total_amount) as total FROM sales WHERE status = 'completed'");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $totalSales = $result['total'] ?? 0;

        // Get today's sales
        $stmt = $pdo->query("SELECT SUM(total_amount) as total FROM sales WHERE DATE(created_at) = CURDATE() AND status = 'completed'");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $todaySales = $result['total'] ?? 0;
    }
} catch (Exception $e) {
    error_log("Sales error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <title>Sales Management - Pharmacy Management System</title>
    <?php include '../../includes/head.php'; ?>
</head>

<body class="pc-shell">
    <?php include '../../includes/navbar.php'; ?>

    <div class="container mx-auto px-4 py-8">
        <!-- Header -->
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-3xl font-bold text-gray-800">Sales Management</h1>
                <p class="text-gray-600">Manage sales transactions and generate invoices</p>
            </div>
            <a href="new_sale.php" class="bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-lg flex items-center space-x-2 transition duration-200">
                <i class="fas fa-plus"></i>
                <span>New Sale</span>
            </a>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-green-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Today's Sales</p>
                        <p class="text-2xl font-bold text-gray-900">Rs <?php echo number_format($todaySales, 2); ?></p>
                    </div>
                    <div class="bg-green-100 p-3 rounded-full">
                        <i class="fas fa-calendar-day text-green-600"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-blue-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Total Sales</p>
                        <p class="text-2xl font-bold text-gray-900">Rs <?php echo number_format($totalSales, 2); ?></p>
                    </div>
                    <div class="bg-blue-100 p-3 rounded-full">
                        <i class="fas fa-chart-line text-blue-600"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-purple-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Total Transactions</p>
                        <p class="text-2xl font-bold text-gray-900"><?php echo count($sales); ?></p>
                    </div>
                    <div class="bg-purple-100 p-3 rounded-full">
                        <i class="fas fa-receipt text-purple-600"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Error Message -->
        <?php if (isset($_GET['error'])): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
                <i class="fas fa-exclamation-circle mr-2"></i>
                <?php echo htmlspecialchars($_GET['error']); ?>
            </div>
        <?php endif; ?>

        <!-- Success Message -->
        <?php if (isset($_GET['success'])): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
                <i class="fas fa-check-circle mr-2"></i>
                <?php echo htmlspecialchars($_GET['success']); ?>
            </div>
        <?php endif; ?>

        <!-- Sales Table -->
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-xl font-semibold text-gray-800">Recent Sales</h2>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Invoice</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cashier</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Payment</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php if (empty($sales)): ?>
                            <tr>
                                <td colspan="8" class="px-6 py-12 text-center text-gray-500">
                                    <i class="fas fa-receipt text-4xl mb-4 text-gray-300"></i>
                                    <p class="text-lg font-medium">No sales found</p>
                                    <p class="text-sm">Create your first sale to get started</p>
                                    <a href="new_sale.php" class="mt-4 inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition duration-200">
                                        <i class="fas fa-plus mr-2"></i>
                                        Create New Sale
                                    </a>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($sales as $sale): ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($sale['invoice_number']); ?></div>
                                        <div class="text-sm text-gray-500">#<?php echo str_pad($sale['id'], 6, '0', STR_PAD_LEFT); ?></div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">
                                            <?php echo htmlspecialchars($sale['customer_name'] ?? 'Walk-in Customer'); ?>
                                        </div>
                                        <?php if ($sale['customer_phone']): ?>
                                            <div class="text-sm text-gray-500"><?php echo htmlspecialchars($sale['customer_phone']); ?></div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900"><?php echo htmlspecialchars($sale['cashier_name']); ?></div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-bold text-green-600">Rs <?php echo number_format($sale['total_amount'], 2); ?></div>
                                        <?php if ($sale['discount_amount'] > 0): ?>
                                            <div class="text-xs text-gray-500">Discount: Rs <?php echo number_format($sale['discount_amount'], 2); ?></div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                            <?php
                                            switch ($sale['payment_method']) {
                                                case 'cash':
                                                    echo 'bg-green-100 text-green-800';
                                                    break;
                                                case 'card':
                                                    echo 'bg-blue-100 text-blue-800';
                                                    break;
                                                case 'upi':
                                                    echo 'bg-purple-100 text-purple-800';
                                                    break;
                                                default:
                                                    echo 'bg-gray-100 text-gray-800';
                                            }
                                            ?>">
                                            <i class="fas fa-<?php
                                                                switch ($sale['payment_method']) {
                                                                    case 'cash':
                                                                        echo 'money-bill';
                                                                        break;
                                                                    case 'card':
                                                                        echo 'credit-card';
                                                                        break;
                                                                    case 'upi':
                                                                        echo 'mobile-alt';
                                                                        break;
                                                                    default:
                                                                        echo 'question';
                                                                }
                                                                ?> mr-1"></i>
                                            <?php echo ucfirst($sale['payment_method']); ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                            <?php
                                            switch ($sale['status']) {
                                                case 'completed':
                                                    echo 'bg-green-100 text-green-800';
                                                    break;
                                                case 'pending':
                                                    echo 'bg-yellow-100 text-yellow-800';
                                                    break;
                                                case 'cancelled':
                                                    echo 'bg-red-100 text-red-800';
                                                    break;
                                                default:
                                                    echo 'bg-gray-100 text-gray-800';
                                            }
                                            ?>">
                                            <i class="fas fa-circle mr-1"></i>
                                            <?php echo ucfirst($sale['status']); ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <?php echo date('M d, Y', strtotime($sale['created_at'])); ?>
                                        <div class="text-xs text-gray-500"><?php echo date('g:i A', strtotime($sale['created_at'])); ?></div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                                        <a href="invoice.php?id=<?php echo $sale['id']; ?>"
                                            class="text-green-600 hover:text-green-900 transition duration-200"
                                            title="View Invoice">
                                            <i class="fas fa-file-invoice"></i>
                                        </a>
                                        <a href="invoice.php?id=<?php echo $sale['id']; ?>&print=1"
                                            class="text-blue-600 hover:text-blue-900 transition duration-200"
                                            title="Print Invoice" target="_blank">
                                            <i class="fas fa-print"></i>
                                        </a>
                                        <?php if ($sale['status'] !== 'cancelled'): ?>
                                            <button onclick="cancelSale(<?php echo $sale['id']; ?>)"
                                                class="text-red-600 hover:text-red-900 transition duration-200"
                                                title="Cancel Sale">
                                                <i class="fas fa-times-circle"></i>
                                            </button>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        function cancelSale(saleId) {
            if (confirm('Are you sure you want to cancel this sale? This action cannot be undone.')) {
                fetch('cancel_sale.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            sale_id: saleId
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            location.reload();
                        } else {
                            alert('Error cancelling sale: ' + data.message);
                        }
                    })
                    .catch(error => {
                        alert('Error cancelling sale: ' + error.message);
                    });
            }
        }
    </script>
</body>

</html>