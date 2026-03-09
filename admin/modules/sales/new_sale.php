<?php
session_start();
require_once dirname(__DIR__, 2) . '/bootstrap.php';

requireLogin();

$user = getCurrentUser();

// Get customers for dropdown
$customersStmt = $pdo->query("SELECT * FROM customers WHERE status = 'active' ORDER BY name");
$customers = $customersStmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en" data-theme="<?php echo getThemeClass(); ?>">
<head>
    <title>New Sale - Pharmacy Management System</title>
    <?php include '../../includes/head.php'; ?>
    <link rel="stylesheet" href="../../assets/css/admin-icons-fix.css">
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

    <script src="../../assets/js/admin-icons-fix.js"></script>
    <script src="../../assets/js/sales.js"></script>
</body>
</html>
