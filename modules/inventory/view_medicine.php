<?php
session_start();
require_once dirname(__DIR__, 2) . '/bootstrap.php';

requireLogin();

$id = $_GET['id'] ?? null;
if (!$id) {
    header('Location: index.php');
    exit();
}

// Get medicine details
$stmt = $pdo->prepare("
    SELECT m.*, c.name as category_name, s.name as supplier_name, s.contact_person, s.phone
    FROM medicines m
    LEFT JOIN categories c ON m.category_id = c.id
    LEFT JOIN suppliers s ON m.supplier_id = s.id
    WHERE m.id = ?
");
$stmt->execute([$id]);
$medicine = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$medicine) {
    header('Location: index.php');
    exit();
}

// Get recent sales for this medicine
$salesStmt = $pdo->prepare("
    SELECT s.sale_date, si.quantity, si.unit_price, si.total_price, c.name as customer_name
    FROM sale_items si
    JOIN sales s ON si.sale_id = s.id
    LEFT JOIN customers c ON s.customer_id = c.id
    WHERE si.medicine_id = ?
    ORDER BY s.sale_date DESC
    LIMIT 10
");
$salesStmt->execute([$id]);
$recentSales = $salesStmt->fetchAll(PDO::FETCH_ASSOC);

$user = getCurrentUser();
?>
<!DOCTYPE html>
<html lang="en" data-theme="<?php echo getThemeClass(); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Medicine - <?php echo htmlspecialchars($medicine['name'] ?? 'Unknown Medicine'); ?></title>
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
                <h1 class="text-3xl font-bold text-gray-800"><?php echo htmlspecialchars($medicine['name'] ?? 'Unknown Medicine'); ?></h1>
                <p class="text-gray-600"><?php echo htmlspecialchars($medicine['generic_name'] ?? 'N/A'); ?></p>
            </div>
            <div class="flex space-x-3">
                <a href="index.php" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg flex items-center space-x-2 transition duration-200">
                    <i class="fas fa-arrow-left"></i>
                    <span>Back to Inventory</span>
                </a>
                <a href="edit_medicine.php?id=<?php echo $medicine['id']; ?>" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center space-x-2 transition duration-200">
                    <i class="fas fa-edit"></i>
                    <span>Edit Medicine</span>
                </a>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Medicine Details -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                    <h2 class="text-xl font-semibold text-gray-800 mb-4">Medicine Information</h2>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Medicine Name</label>
                            <p class="text-gray-900 font-medium"><?php echo htmlspecialchars($medicine['name'] ?? 'Unknown Medicine'); ?></p>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Generic Name</label>
                            <p class="text-gray-900"><?php echo htmlspecialchars($medicine['generic_name'] ?? 'N/A'); ?></p>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Category</label>
                            <p class="text-gray-900"><?php echo htmlspecialchars($medicine['category_name'] ?? 'N/A'); ?></p>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Barcode</label>
                            <p class="text-gray-900 font-mono"><?php echo htmlspecialchars($medicine['barcode'] ?? 'N/A'); ?></p>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Batch Number</label>
                            <p class="text-gray-900"><?php echo htmlspecialchars($medicine['batch_number'] ?? 'N/A'); ?></p>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Dosage</label>
                            <p class="text-gray-900"><?php echo htmlspecialchars($medicine['dosage'] ?? 'N/A'); ?></p>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Manufacturing Date</label>
                            <p class="text-gray-900">
                                <?php 
                                if (!empty($medicine['manufacture_date'])) {
                                    echo date('d/m/Y', strtotime($medicine['manufacture_date']));
                                } else {
                                    echo 'N/A';
                                }
                                ?>
                            </p>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Expiry Date</label>
                            <p class="text-gray-900 <?php echo (!empty($medicine['expiry_date']) && strtotime($medicine['expiry_date']) <= strtotime('+30 days')) ? 'text-red-600 font-semibold' : ''; ?>">
                                <?php 
                                if (!empty($medicine['expiry_date'])) {
                                    echo date('d/m/Y', strtotime($medicine['expiry_date']));
                                    if (strtotime($medicine['expiry_date']) <= strtotime('+30 days')) {
                                        echo ' <i class="fas fa-exclamation-triangle ml-2"></i>';
                                    }
                                } else {
                                    echo 'N/A';
                                }
                                ?>
                            </p>
                        </div>
                    </div>
                    
                    <?php if ($medicine['description']): ?>
                        <div class="mt-6">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                            <p class="text-gray-900"><?php echo nl2br(htmlspecialchars($medicine['description'] ?? '')); ?></p>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Recent Sales -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-xl font-semibold text-gray-800 mb-4">Recent Sales</h2>
                    
                    <?php if (empty($recentSales)): ?>
                        <p class="text-gray-500 text-center py-8">No recent sales found for this medicine.</p>
                    <?php else: ?>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Customer</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Quantity</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Unit Price</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    <?php foreach ($recentSales as $sale): ?>
                                        <tr>
                                            <td class="px-4 py-3 text-sm text-gray-900"><?php echo date('d/m/Y', strtotime($sale['sale_date'])); ?></td>
                                            <td class="px-4 py-3 text-sm text-gray-900"><?php echo htmlspecialchars($sale['customer_name'] ?: 'Walk-in Customer'); ?></td>
                                            <td class="px-4 py-3 text-sm text-gray-900"><?php echo $sale['quantity']; ?></td>
                                            <td class="px-4 py-3 text-sm text-gray-900">Rs <?php echo number_format($sale['unit_price'], 2); ?></td>
                                            <td class="px-4 py-3 text-sm text-gray-900 font-medium">Rs <?php echo number_format($sale['total_price'], 2); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- Stock Information -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Stock Information</h3>
                    
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Current Stock</label>
                            <p class="text-2xl font-bold <?php echo $medicine['stock_quantity'] <= $medicine['min_stock_level'] ? 'text-red-600' : 'text-green-600'; ?>">
                                <?php echo $medicine['stock_quantity']; ?>
                                <?php if ($medicine['stock_quantity'] <= $medicine['min_stock_level']): ?>
                                    <i class="fas fa-exclamation-triangle text-red-500 ml-2"></i>
                                <?php endif; ?>
                            </p>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Minimum Stock Level</label>
                            <p class="text-gray-900"><?php echo $medicine['min_stock_level']; ?></p>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Unit</label>
                            <p class="text-gray-900"><?php echo htmlspecialchars($medicine['unit'] ?? 'N/A'); ?></p>
                        </div>
                    </div>
                </div>

                <!-- Pricing Information -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Pricing</h3>
                    
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Purchase Price</label>
                            <p class="text-lg font-semibold text-gray-900">Rs <?php echo number_format($medicine['purchase_price'], 2); ?></p>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Selling Price</label>
                            <p class="text-lg font-semibold text-green-600">Rs <?php echo number_format($medicine['selling_price'], 2); ?></p>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Profit Margin</label>
                            <?php 
                            $margin = (($medicine['selling_price'] - $medicine['purchase_price']) / $medicine['purchase_price']) * 100;
                            ?>
                            <p class="text-lg font-semibold text-blue-600"><?php echo number_format($margin, 1); ?>%</p>
                        </div>
                    </div>
                </div>

                <!-- Supplier Information -->
                <?php if ($medicine['supplier_name']): ?>
                    <div class="bg-white rounded-lg shadow-md p-6">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4">Supplier</h3>
                        
                        <div class="space-y-3">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Company</label>
                                <p class="text-gray-900"><?php echo htmlspecialchars($medicine['supplier_name'] ?? 'N/A'); ?></p>
                            </div>
                            
                            <?php if ($medicine['contact_person']): ?>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Contact Person</label>
                                    <p class="text-gray-900"><?php echo htmlspecialchars($medicine['contact_person'] ?? 'N/A'); ?></p>
                                </div>
                            <?php endif; ?>
                            
                            <?php if ($medicine['phone']): ?>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
                                    <p class="text-gray-900"><?php echo htmlspecialchars($medicine['phone'] ?? 'N/A'); ?></p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Status -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Status</h3>
                    
                    <div class="space-y-3">
                        <div>
                            <span class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full 
                                <?php echo $medicine['status'] === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
                                <?php echo ucfirst($medicine['status']); ?>
                            </span>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Created</label>
                            <p class="text-sm text-gray-600"><?php echo date('d/m/Y H:i', strtotime($medicine['created_at'])); ?></p>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Last Updated</label>
                            <p class="text-sm text-gray-600"><?php echo date('d/m/Y H:i', strtotime($medicine['updated_at'])); ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>