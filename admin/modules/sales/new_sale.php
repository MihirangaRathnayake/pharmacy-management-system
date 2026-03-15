<?php
session_start();
require_once dirname(__DIR__, 2) . '/bootstrap.php';

requireLogin();

$user = getCurrentUser();

// Get customers for dropdown
$customersStmt = $pdo->query("SELECT * FROM customers WHERE status = 'active' ORDER BY name");
$customers = $customersStmt->fetchAll(PDO::FETCH_ASSOC);

// Get all sales for history table
try {
    $salesStmt = $pdo->query("
        SELECT s.*, 
               COALESCE(c.name, 'Walk-in Customer') as customer_name, 
               c.phone as customer_phone,
               u.name as cashier_name,
               (SELECT COUNT(*) FROM sale_items si WHERE si.sale_id = s.id) as item_count
        FROM sales s 
        LEFT JOIN customers c ON s.customer_id = c.id 
        LEFT JOIN users u ON s.user_id = u.id 
        ORDER BY s.sale_date DESC
    ");
    $allSales = $salesStmt->fetchAll(PDO::FETCH_ASSOC);

    $totalRevenue = $pdo->query("SELECT COALESCE(SUM(total_amount), 0) as total FROM sales WHERE status = 'completed'")->fetch()['total'];
    $todayRevenue = $pdo->query("SELECT COALESCE(SUM(total_amount), 0) as total FROM sales WHERE status = 'completed' AND DATE(sale_date) = CURDATE()")->fetch()['total'];
    $totalTransactions = count($allSales);
} catch (Exception $e) {
    $allSales = [];
    $totalRevenue = 0;
    $todayRevenue = 0;
    $totalTransactions = 0;
}
?>
<!DOCTYPE html>
<html lang="en" data-theme="<?php echo getThemeClass(); ?>">

<head>
    <title>New Sale - Pharmacy Management System</title>
    <?php include '../../includes/head.php'; ?>
    <?php
    if (function_exists('getThemeCSS')) echo getThemeCSS();
    if (function_exists('renderThemeScript')) renderThemeScript();
    ?>
</head>

<body class="pc-shell">
    <?php include '../../includes/navbar.php'; ?>

    <div class="pc-container">
        <div class="pc-page-header pc-animate">
            <div class="pc-breadcrumb">Home <i class="fas fa-chevron-right"></i> Sales <i class="fas fa-chevron-right"></i> New Sale</div>
            <div class="flex justify-between items-center mb-0 gap-4">
                <div>
                    <h1 class="pc-page-title">Point of Sale</h1>
                    <p class="pc-page-subtitle">Fast medicine lookup, cart, and checkout flow</p>
                </div>
                <a href="index.php" class="pc-btn pc-btn-muted">
                    <i class="fas fa-arrow-left"></i>
                    <span>Back to Sales</span>
                </a>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Sale Form -->
            <div class="lg:col-span-2">
                <div class="pc-card p-6">
                    <h2 class="text-xl font-semibold text-gray-800 mb-4">Sale Information</h2>

                    <form id="saleForm">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Invoice Number</label>
                                <input type="text" id="invoiceNumber" readonly
                                    class="pc-input bg-gray-50"
                                    value="INV-<?php echo date('Ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT); ?>">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Customer</label>
                                <select id="customerId" class="pc-select">
                                    <option value="">Walk-in Customer</option>
                                    <?php foreach ($customers as $customer): ?>
                                        <option value="<?php echo $customer['id']; ?>">
                                            <?php echo htmlspecialchars($customer['name']); ?> - <?php echo htmlspecialchars($customer['phone']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <!-- Medicine Search -->
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Add Medicine</label>
                            <div class="flex space-x-2">
                                <input type="text" id="medicineSearch" placeholder="Search medicine by name or barcode..."
                                    class="flex-1 pc-input">
                                <button type="button" onclick="searchMedicine()"
                                    class="pc-btn pc-btn-secondary">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                            <div id="medicineResults" class="mt-2 max-h-40 overflow-y-auto border border-gray-200 rounded-md hidden"></div>
                        </div>

                        <!-- Cart Items -->
                        <div class="mb-6">
                            <h3 class="text-lg font-medium text-gray-800 mb-3">Cart Items</h3>
                            <div class="overflow-x-auto">
                                <table class="pc-table min-w-full divide-y divide-gray-200" id="cartTable">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Medicine</th>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Price</th>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Qty</th>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Total</th>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody id="cartItems" class="bg-white divide-y divide-gray-200">
                                        <tr id="emptyCart">
                                            <td colspan="5" class="px-4 py-8 text-center text-gray-500">No items in cart</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Payment Details -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Payment Method</label>
                                <select id="paymentMethod" class="pc-select">
                                    <option value="cash">Cash</option>
                                    <option value="card">Card</option>
                                    <option value="upi">UPI</option>
                                    <option value="online">Online</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Notes</label>
                                <input type="text" id="notes" placeholder="Optional notes..."
                                    class="pc-input">
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Sale Summary -->
            <div class="lg:col-span-1">
                <div class="pc-card p-6 sticky top-24">
                    <h2 class="text-xl font-semibold text-gray-800 mb-4">Sale Summary</h2>

                    <div class="space-y-3 mb-6">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Subtotal:</span>
                            <span class="font-medium" id="subtotal">Rs 0.00</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Discount:</span>
                            <div class="flex items-center space-x-2">
                                <input type="number" id="discountAmount" value="0" min="0" step="0.01"
                                    class="pc-input w-20 text-sm"
                                    onchange="updateTotals()">
                                <span class="text-sm text-gray-500">Rs</span>
                            </div>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Tax (18%):</span>
                            <span class="font-medium" id="taxAmount">Rs 0.00</span>
                        </div>
                        <hr>
                        <div class="flex justify-between text-lg font-bold">
                            <span>Total:</span>
                            <span class="text-green-600" id="totalAmount">Rs 0.00</span>
                        </div>
                    </div>

                    <button type="button" onclick="processSale()"
                        class="w-full pc-btn pc-btn-primary py-3">
                        <i class="fas fa-shopping-cart mr-2"></i>
                        Complete Sale
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Sales History Section -->
    <div class="pc-container" style="padding-top: 0;">
        <!-- Sales Stats -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <div class="pc-card p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500">Today's Revenue</p>
                        <p class="text-xl font-bold text-green-600">Rs <?php echo number_format($todayRevenue, 2); ?></p>
                    </div>
                    <div class="bg-green-100 p-3 rounded-full"><i class="fas fa-calendar-day text-green-600"></i></div>
                </div>
            </div>
            <div class="pc-card p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500">Total Revenue</p>
                        <p class="text-xl font-bold text-blue-600">Rs <?php echo number_format($totalRevenue, 2); ?></p>
                    </div>
                    <div class="bg-blue-100 p-3 rounded-full"><i class="fas fa-chart-line text-blue-600"></i></div>
                </div>
            </div>
            <div class="pc-card p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500">Total Transactions</p>
                        <p class="text-xl font-bold text-purple-600"><?php echo $totalTransactions; ?></p>
                    </div>
                    <div class="bg-purple-100 p-3 rounded-full"><i class="fas fa-receipt text-purple-600"></i></div>
                </div>
            </div>
        </div>

        <!-- Sales History Table -->
        <div class="pc-card">
            <div class="p-6 border-b border-gray-200">
                <div class="flex flex-wrap items-center justify-between gap-4">
                    <h2 class="text-xl font-semibold text-gray-800"><i class="fas fa-history mr-2"></i>Sales History</h2>
                    <div class="flex flex-wrap items-center gap-3">
                        <div class="relative">
                            <input type="text" id="salesSearch" placeholder="Search invoice, customer..."
                                class="pc-input pl-9 text-sm" style="min-width:220px" onkeyup="filterSalesTable()">
                            <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
                        </div>
                        <select id="statusFilter" class="pc-select text-sm" onchange="filterSalesTable()">
                            <option value="">All Status</option>
                            <option value="completed">Completed</option>
                            <option value="cancelled">Cancelled</option>
                            <option value="returned">Returned</option>
                        </select>
                        <button onclick="downloadSalesCSV()" class="pc-btn pc-btn-secondary text-sm" title="Download CSV">
                            <i class="fas fa-download mr-1"></i> Download
                        </button>
                        <button onclick="printSalesTable()" class="pc-btn pc-btn-muted text-sm" title="Print Sales">
                            <i class="fas fa-print mr-1"></i> Print
                        </button>
                    </div>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200" id="salesHistoryTable">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase cursor-pointer" onclick="sortSalesTable(0)">Invoice <i class="fas fa-sort text-gray-300 ml-1"></i></th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase cursor-pointer" onclick="sortSalesTable(1)">Customer <i class="fas fa-sort text-gray-300 ml-1"></i></th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Items</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase cursor-pointer" onclick="sortSalesTable(3)">Amount <i class="fas fa-sort text-gray-300 ml-1"></i></th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Payment</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase cursor-pointer" onclick="sortSalesTable(6)">Date <i class="fas fa-sort text-gray-300 ml-1"></i></th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200" id="salesHistoryBody">
                        <?php if (empty($allSales)): ?>
                            <tr>
                                <td colspan="8" class="px-6 py-12 text-center text-gray-400">
                                    <i class="fas fa-receipt text-4xl mb-3 block"></i>
                                    <p class="font-medium">No sales recorded yet</p>
                                    <p class="text-sm">Complete a sale above to see it here</p>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($allSales as $sale): ?>
                                <tr class="hover:bg-gray-50 transition-colors sale-row"
                                    data-status="<?php echo htmlspecialchars($sale['status']); ?>">
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <span class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($sale['invoice_number']); ?></span>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($sale['customer_name']); ?></div>
                                        <?php if (!empty($sale['customer_phone'])): ?>
                                            <div class="text-xs text-gray-400"><?php echo htmlspecialchars($sale['customer_phone']); ?></div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <span class="bg-gray-100 text-gray-700 text-xs font-medium px-2 py-1 rounded-full"><?php echo $sale['item_count']; ?> items</span>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <span class="text-sm font-bold text-green-600">Rs <?php echo number_format($sale['total_amount'], 2); ?></span>
                                        <?php if ($sale['discount_amount'] > 0): ?>
                                            <div class="text-xs text-gray-400">Disc: Rs <?php echo number_format($sale['discount_amount'], 2); ?></div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <?php
                                        $pmColors = ['cash' => 'green', 'card' => 'blue', 'upi' => 'purple', 'online' => 'indigo'];
                                        $pmIcons = ['cash' => 'money-bill', 'card' => 'credit-card', 'upi' => 'mobile-alt', 'online' => 'globe'];
                                        $pmColor = $pmColors[$sale['payment_method']] ?? 'gray';
                                        $pmIcon = $pmIcons[$sale['payment_method']] ?? 'question';
                                        ?>
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-<?php echo $pmColor; ?>-100 text-<?php echo $pmColor; ?>-700">
                                            <i class="fas fa-<?php echo $pmIcon; ?> mr-1"></i><?php echo ucfirst($sale['payment_method']); ?>
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <?php
                                        $stColors = ['completed' => 'green', 'cancelled' => 'red', 'returned' => 'yellow'];
                                        $stColor = $stColors[$sale['status']] ?? 'gray';
                                        ?>
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-<?php echo $stColor; ?>-100 text-<?php echo $stColor; ?>-700">
                                            <?php echo ucfirst($sale['status']); ?>
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700">
                                        <?php echo date('M d, Y', strtotime($sale['sale_date'])); ?>
                                        <div class="text-xs text-gray-400"><?php echo date('g:i A', strtotime($sale['sale_date'])); ?></div>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <div class="flex items-center gap-2">
                                            <a href="invoice.php?id=<?php echo $sale['id']; ?>" class="text-green-600 hover:text-green-800" title="View Invoice"><i class="fas fa-file-invoice"></i></a>
                                            <a href="invoice.php?id=<?php echo $sale['id']; ?>&print=1" target="_blank" class="text-blue-600 hover:text-blue-800" title="Print Invoice"><i class="fas fa-print"></i></a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <?php if (!empty($allSales)): ?>
                <div class="p-4 border-t border-gray-200 flex items-center justify-between text-sm text-gray-500">
                    <span id="salesCount">Showing <?php echo count($allSales); ?> sales</span>
                    <span>Total: <strong class="text-green-600">Rs <?php echo number_format($totalRevenue, 2); ?></strong></span>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Print-only layout (hidden on screen) -->
    <div id="printArea" style="display:none;">
        <style>
            #printArea table {
                width: 100%;
                border-collapse: collapse;
                font-size: 12px;
            }

            #printArea th,
            #printArea td {
                border: 1px solid #ddd;
                padding: 6px 8px;
                text-align: left;
            }

            #printArea th {
                background: #f3f4f6;
                font-weight: 600;
            }

            #printArea h2 {
                margin-bottom: 4px;
            }

            #printArea .print-header {
                margin-bottom: 16px;
            }
        </style>
        <div class="print-header">
            <h2>Sales Report — <?php echo date('F d, Y'); ?></h2>
            <p>Generated by <?php echo htmlspecialchars($user['name']); ?></p>
        </div>
        <table>
            <thead>
                <tr>
                    <th>Invoice</th>
                    <th>Customer</th>
                    <th>Items</th>
                    <th>Amount</th>
                    <th>Payment</th>
                    <th>Status</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($allSales as $sale): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($sale['invoice_number']); ?></td>
                        <td><?php echo htmlspecialchars($sale['customer_name']); ?></td>
                        <td><?php echo $sale['item_count']; ?></td>
                        <td>Rs <?php echo number_format($sale['total_amount'], 2); ?></td>
                        <td><?php echo ucfirst($sale['payment_method']); ?></td>
                        <td><?php echo ucfirst($sale['status']); ?></td>
                        <td><?php echo date('Y-m-d H:i', strtotime($sale['sale_date'])); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <p style="margin-top:12px;font-size:11px;color:#666;">Total Revenue: Rs <?php echo number_format($totalRevenue, 2); ?> | Transactions: <?php echo $totalTransactions; ?></p>
    </div>

    <script src="../../assets/js/admin-icons-fix.js"></script>
    <script src="../../assets/js/sales.js?v=<?php echo time(); ?>"></script>
    <script>
        // Search & filter sales table
        function filterSalesTable() {
            var query = (document.getElementById('salesSearch').value || '').toLowerCase();
            var status = document.getElementById('statusFilter').value;
            var rows = document.querySelectorAll('#salesHistoryBody .sale-row');
            var visible = 0;
            rows.forEach(function(row) {
                var text = row.textContent.toLowerCase();
                var rowStatus = row.getAttribute('data-status');
                var matchSearch = !query || text.indexOf(query) !== -1;
                var matchStatus = !status || rowStatus === status;
                row.style.display = (matchSearch && matchStatus) ? '' : 'none';
                if (matchSearch && matchStatus) visible++;
            });
            var countEl = document.getElementById('salesCount');
            if (countEl) countEl.textContent = 'Showing ' + visible + ' sales';
        }

        // Sort table by column
        var sortDir = {};

        function sortSalesTable(colIndex) {
            var table = document.getElementById('salesHistoryTable');
            var tbody = document.getElementById('salesHistoryBody');
            var rows = Array.from(tbody.querySelectorAll('.sale-row'));
            if (!rows.length) return;

            sortDir[colIndex] = !sortDir[colIndex];
            var dir = sortDir[colIndex] ? 1 : -1;

            rows.sort(function(a, b) {
                var aText = a.cells[colIndex].textContent.trim();
                var bText = b.cells[colIndex].textContent.trim();
                // Try numeric sort for Amount column
                if (colIndex === 3) {
                    var aNum = parseFloat(aText.replace(/[^0-9.-]/g, '')) || 0;
                    var bNum = parseFloat(bText.replace(/[^0-9.-]/g, '')) || 0;
                    return (aNum - bNum) * dir;
                }
                return aText.localeCompare(bText) * dir;
            });

            rows.forEach(function(row) {
                tbody.appendChild(row);
            });
        }

        // Download CSV
        function downloadSalesCSV() {
            var rows = document.querySelectorAll('#salesHistoryBody .sale-row');
            if (!rows.length) {
                alert('No sales data to download');
                return;
            }

            var csv = 'Invoice,Customer,Items,Amount,Discount,Payment,Status,Date\n';
            rows.forEach(function(row) {
                if (row.style.display === 'none') return;
                var cells = row.querySelectorAll('td');
                var invoice = cells[0].textContent.trim();
                var customer = cells[1].textContent.trim().replace(/\n/g, ' ').replace(/\s+/g, ' ');
                var items = cells[2].textContent.trim();
                var amount = cells[3].textContent.trim().replace(/\n/g, ' ').replace(/\s+/g, ' ');
                var payment = cells[4].textContent.trim();
                var status = cells[5].textContent.trim();
                var date = cells[6].textContent.trim().replace(/\n/g, ' ').replace(/\s+/g, ' ');
                csv += '"' + invoice + '","' + customer + '","' + items + '","' + amount + '","' + payment + '","' + status + '","' + date + '"\n';
            });

            var blob = new Blob([csv], {
                type: 'text/csv;charset=utf-8;'
            });
            var link = document.createElement('a');
            link.href = URL.createObjectURL(blob);
            link.download = 'sales_report_' + new Date().toISOString().slice(0, 10) + '.csv';
            link.click();
            URL.revokeObjectURL(link.href);
        }

        // Print sales table
        function printSalesTable() {
            var printContent = document.getElementById('printArea').innerHTML;
            var win = window.open('', '_blank', 'width=900,height=700');
            win.document.write('<html><head><title>Sales Report</title></head><body>' + printContent + '</body></html>');
            win.document.close();
            win.focus();
            win.print();
            win.close();
        }
    </script>
</body>

</html>