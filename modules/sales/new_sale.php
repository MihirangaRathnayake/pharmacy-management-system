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
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Sale - Pharmacy Management System</title>
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
    <?php include '../../includes/navbar.php'; ?>
    
    <div class="container mx-auto px-4 py-8">
        <!-- Header -->
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-3xl font-bold text-gray-800">New Sale</h1>
                <p class="text-gray-600">Create a new sale transaction</p>
            </div>
            <a href="index.php" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg flex items-center space-x-2 transition duration-200">
                <i class="fas fa-arrow-left"></i>
                <span>Back to Sales</span>
            </a>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Sale Form -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-xl font-semibold text-gray-800 mb-4">Sale Information</h2>
                    
                    <form id="saleForm">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Invoice Number</label>
                                <input type="text" id="invoiceNumber" readonly 
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-50"
                                       value="INV-<?php echo date('Ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT); ?>">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Customer</label>
                                <select id="customerId" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500">
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
                                       class="flex-1 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500">
                                <button type="button" onclick="searchMedicine()" 
                                        class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md transition duration-200">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                            <div id="medicineResults" class="mt-2 max-h-40 overflow-y-auto border border-gray-200 rounded-md hidden"></div>
                        </div>

                        <!-- Cart Items -->
                        <div class="mb-6">
                            <h3 class="text-lg font-medium text-gray-800 mb-3">Cart Items</h3>
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200" id="cartTable">
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
                                <select id="paymentMethod" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500">
                                    <option value="cash">Cash</option>
                                    <option value="card">Card</option>
                                    <option value="upi">UPI</option>
                                    <option value="online">Online</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Notes</label>
                                <input type="text" id="notes" placeholder="Optional notes..."
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500">
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Sale Summary -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-lg shadow-md p-6 sticky top-24">
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
                                       class="w-20 px-2 py-1 border border-gray-300 rounded text-sm"
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
                            class="w-full bg-green-600 hover:bg-green-700 text-white font-medium py-3 px-4 rounded-lg transition duration-200 flex items-center justify-center">
                        <i class="fas fa-shopping-cart mr-2"></i>
                        Complete Sale
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="../../assets/js/sales.js"></script>
</body>
</html>