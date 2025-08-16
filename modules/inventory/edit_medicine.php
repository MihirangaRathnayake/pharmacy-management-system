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
$stmt = $pdo->prepare("SELECT * FROM medicines WHERE id = ?");
$stmt->execute([$id]);
$medicine = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$medicine) {
    header('Location: index.php');
    exit();
}

// Get categories and suppliers
$categoriesStmt = $pdo->query("SELECT * FROM categories WHERE status = 'active' ORDER BY name");
$categories = $categoriesStmt->fetchAll(PDO::FETCH_ASSOC);

$suppliersStmt = $pdo->query("SELECT * FROM suppliers WHERE status = 'active' ORDER BY name");
$suppliers = $suppliersStmt->fetchAll(PDO::FETCH_ASSOC);

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $name = trim($_POST['name']);
        $generic_name = trim($_POST['generic_name']);
        $category_id = $_POST['category_id'];
        $dosage = trim($_POST['dosage']);
        $barcode = trim($_POST['barcode']);
        $batch_number = trim($_POST['batch_number']);
        $manufacture_date = $_POST['manufacture_date'];
        $expiry_date = $_POST['expiry_date'];
        $purchase_price = floatval($_POST['purchase_price']);
        $selling_price = floatval($_POST['selling_price']);
        $stock_quantity = intval($_POST['stock_quantity']);
        $min_stock_level = intval($_POST['min_stock_level']);
        $unit = trim($_POST['unit']);
        $supplier_id = $_POST['supplier_id'] ?: null;
        $description = trim($_POST['description']);
        $status = $_POST['status'];

        // Validation
        if (empty($name) || empty($generic_name) || empty($category_id)) {
            throw new Exception('Name, generic name, and category are required.');
        }

        if ($purchase_price <= 0 || $selling_price <= 0) {
            throw new Exception('Purchase price and selling price must be greater than 0.');
        }

        if ($stock_quantity < 0 || $min_stock_level < 0) {
            throw new Exception('Stock quantities cannot be negative.');
        }

        // Check if barcode is unique (excluding current medicine)
        if (!empty($barcode)) {
            $barcodeStmt = $pdo->prepare("SELECT id FROM medicines WHERE barcode = ? AND id != ?");
            $barcodeStmt->execute([$barcode, $id]);
            if ($barcodeStmt->fetch()) {
                throw new Exception('Barcode already exists for another medicine.');
            }
        }

        // Update medicine
        $updateStmt = $pdo->prepare("
            UPDATE medicines SET 
                name = ?, generic_name = ?, category_id = ?, dosage = ?, 
                barcode = ?, batch_number = ?, manufacture_date = ?, expiry_date = ?,
                purchase_price = ?, selling_price = ?, stock_quantity = ?, min_stock_level = ?,
                unit = ?, supplier_id = ?, description = ?, status = ?, updated_at = NOW()
            WHERE id = ?
        ");
        
        $updateStmt->execute([
            $name, $generic_name, $category_id, $dosage,
            $barcode, $batch_number, $manufacture_date, $expiry_date,
            $purchase_price, $selling_price, $stock_quantity, $min_stock_level,
            $unit, $supplier_id, $description, $status, $id
        ]);

        $message = 'Medicine updated successfully!';
        
        // Refresh medicine data
        $stmt->execute([$id]);
        $medicine = $stmt->fetch(PDO::FETCH_ASSOC);
        
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

$user = getCurrentUser();
?>
<!DOCTYPE html>
<html lang="en" data-theme="<?php echo getThemeClass(); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Medicine - <?php echo htmlspecialchars($medicine['name'] ?? 'Unknown Medicine'); ?></title>
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
                <h1 class="text-3xl font-bold text-gray-800">Edit Medicine</h1>
                <p class="text-gray-600">Update medicine information</p>
            </div>
            <div class="flex space-x-3">
                <a href="view_medicine.php?id=<?php echo $medicine['id']; ?>" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg flex items-center space-x-2 transition duration-200">
                    <i class="fas fa-eye"></i>
                    <span>View Medicine</span>
                </a>
                <a href="index.php" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center space-x-2 transition duration-200">
                    <i class="fas fa-arrow-left"></i>
                    <span>Back to Inventory</span>
                </a>
            </div>
        </div>

        <!-- Messages -->
        <?php if ($message): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
                <i class="fas fa-check-circle mr-2"></i><?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
                <i class="fas fa-exclamation-circle mr-2"></i><?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <!-- Form -->
        <form method="POST" class="bg-white rounded-lg shadow-md p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Basic Information -->
                <div class="md:col-span-2">
                    <h2 class="text-xl font-semibold text-gray-800 mb-4">Basic Information</h2>
                </div>

                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Medicine Name *</label>
                    <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($medicine['name'] ?? ''); ?>" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500" required>
                </div>

                <div>
                    <label for="generic_name" class="block text-sm font-medium text-gray-700 mb-2">Generic Name *</label>
                    <input type="text" id="generic_name" name="generic_name" value="<?php echo htmlspecialchars($medicine['generic_name'] ?? ''); ?>" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500" required>
                </div>

                <div>
                    <label for="category_id" class="block text-sm font-medium text-gray-700 mb-2">Category *</label>
                    <select id="category_id" name="category_id" 
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500" required>
                        <option value="">Select Category</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?php echo $category['id']; ?>" <?php echo $medicine['category_id'] == $category['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($category['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label for="dosage" class="block text-sm font-medium text-gray-700 mb-2">Dosage</label>
                    <input type="text" id="dosage" name="dosage" value="<?php echo htmlspecialchars($medicine['dosage'] ?? ''); ?>" 
                           placeholder="e.g., 500mg, 10ml, 1 tablet"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500">
                </div>

                <div>
                    <label for="barcode" class="block text-sm font-medium text-gray-700 mb-2">Barcode</label>
                    <input type="text" id="barcode" name="barcode" value="<?php echo htmlspecialchars($medicine['barcode'] ?? ''); ?>" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500">
                </div>

                <div>
                    <label for="batch_number" class="block text-sm font-medium text-gray-700 mb-2">Batch Number</label>
                    <input type="text" id="batch_number" name="batch_number" value="<?php echo htmlspecialchars($medicine['batch_number'] ?? ''); ?>" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500">
                </div>

                <!-- Dates -->
                <div class="md:col-span-2">
                    <h2 class="text-xl font-semibold text-gray-800 mb-4 mt-6">Dates</h2>
                </div>

                <div>
                    <label for="manufacture_date" class="block text-sm font-medium text-gray-700 mb-2">Manufacturing Date</label>
                    <input type="date" id="manufacture_date" name="manufacture_date" value="<?php echo $medicine['manufacture_date'] ?? ''; ?>" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500">
                </div>

                <div>
                    <label for="expiry_date" class="block text-sm font-medium text-gray-700 mb-2">Expiry Date</label>
                    <input type="date" id="expiry_date" name="expiry_date" value="<?php echo $medicine['expiry_date'] ?? ''; ?>" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500">
                </div>

                <!-- Pricing -->
                <div class="md:col-span-2">
                    <h2 class="text-xl font-semibold text-gray-800 mb-4 mt-6">Pricing</h2>
                </div>

                <div>
                    <label for="purchase_price" class="block text-sm font-medium text-gray-700 mb-2">Purchase Price (Rs) *</label>
                    <input type="number" id="purchase_price" name="purchase_price" value="<?php echo $medicine['purchase_price']; ?>" 
                           step="0.01" min="0" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500" required>
                </div>

                <div>
                    <label for="selling_price" class="block text-sm font-medium text-gray-700 mb-2">Selling Price (Rs) *</label>
                    <input type="number" id="selling_price" name="selling_price" value="<?php echo $medicine['selling_price']; ?>" 
                           step="0.01" min="0" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500" required>
                </div>

                <!-- Stock -->
                <div class="md:col-span-2">
                    <h2 class="text-xl font-semibold text-gray-800 mb-4 mt-6">Stock Information</h2>
                </div>

                <div>
                    <label for="stock_quantity" class="block text-sm font-medium text-gray-700 mb-2">Current Stock *</label>
                    <input type="number" id="stock_quantity" name="stock_quantity" value="<?php echo $medicine['stock_quantity']; ?>" 
                           min="0" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500" required>
                </div>

                <div>
                    <label for="min_stock_level" class="block text-sm font-medium text-gray-700 mb-2">Minimum Stock Level *</label>
                    <input type="number" id="min_stock_level" name="min_stock_level" value="<?php echo $medicine['min_stock_level']; ?>" 
                           min="0" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500" required>
                </div>

                <div>
                    <label for="unit" class="block text-sm font-medium text-gray-700 mb-2">Unit</label>
                    <select id="unit" name="unit" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500">
                        <option value="piece" <?php echo $medicine['unit'] === 'piece' ? 'selected' : ''; ?>>Piece</option>
                        <option value="tablet" <?php echo $medicine['unit'] === 'tablet' ? 'selected' : ''; ?>>Tablet</option>
                        <option value="capsule" <?php echo $medicine['unit'] === 'capsule' ? 'selected' : ''; ?>>Capsule</option>
                        <option value="bottle" <?php echo $medicine['unit'] === 'bottle' ? 'selected' : ''; ?>>Bottle</option>
                        <option value="box" <?php echo $medicine['unit'] === 'box' ? 'selected' : ''; ?>>Box</option>
                        <option value="strip" <?php echo $medicine['unit'] === 'strip' ? 'selected' : ''; ?>>Strip</option>
                        <option value="vial" <?php echo $medicine['unit'] === 'vial' ? 'selected' : ''; ?>>Vial</option>
                        <option value="ml" <?php echo $medicine['unit'] === 'ml' ? 'selected' : ''; ?>>ML</option>
                        <option value="gram" <?php echo $medicine['unit'] === 'gram' ? 'selected' : ''; ?>>Gram</option>
                    </select>
                </div>

                <div>
                    <label for="supplier_id" class="block text-sm font-medium text-gray-700 mb-2">Supplier</label>
                    <select id="supplier_id" name="supplier_id" 
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500">
                        <option value="">Select Supplier</option>
                        <?php foreach ($suppliers as $supplier): ?>
                            <option value="<?php echo $supplier['id']; ?>" <?php echo $medicine['supplier_id'] == $supplier['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($supplier['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Additional Information -->
                <div class="md:col-span-2">
                    <h2 class="text-xl font-semibold text-gray-800 mb-4 mt-6">Additional Information</h2>
                </div>

                <div class="md:col-span-2">
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                    <textarea id="description" name="description" rows="3" 
                              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500"><?php echo htmlspecialchars($medicine['description'] ?? ''); ?></textarea>
                </div>

                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                    <select id="status" name="status" 
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500">
                        <option value="active" <?php echo $medicine['status'] === 'active' ? 'selected' : ''; ?>>Active</option>
                        <option value="inactive" <?php echo $medicine['status'] === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                        <option value="discontinued" <?php echo $medicine['status'] === 'discontinued' ? 'selected' : ''; ?>>Discontinued</option>
                    </select>
                </div>
            </div>

            <!-- Submit Buttons -->
            <div class="flex justify-end space-x-4 mt-8 pt-6 border-t border-gray-200">
                <a href="view_medicine.php?id=<?php echo $medicine['id']; ?>" 
                   class="px-6 py-2 border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50 transition duration-200">
                    Cancel
                </a>
                <button type="submit" 
                        class="px-6 py-2 bg-green-600 hover:bg-green-700 text-white rounded-md transition duration-200">
                    <i class="fas fa-save mr-2"></i>Update Medicine
                </button>
            </div>
        </form>
    </div>

    <script>
        // Calculate profit margin
        function updateProfitMargin() {
            const purchasePrice = parseFloat(document.getElementById('purchase_price').value) || 0;
            const sellingPrice = parseFloat(document.getElementById('selling_price').value) || 0;
            
            if (purchasePrice > 0) {
                const margin = ((sellingPrice - purchasePrice) / purchasePrice) * 100;
                console.log('Profit Margin:', margin.toFixed(1) + '%');
            }
        }

        document.getElementById('purchase_price').addEventListener('input', updateProfitMargin);
        document.getElementById('selling_price').addEventListener('input', updateProfitMargin);
    </script>
</body>
</html>