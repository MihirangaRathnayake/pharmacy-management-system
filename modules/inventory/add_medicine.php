<?php
require_once dirname(dirname(__DIR__)) . '/bootstrap.php';

// Check authentication
if (!isLoggedIn()) {
    header('Location: ../../auth/login.php');
    exit();
}

$user = getCurrentUser();
$success = '';
$error = '';

// Handle form submission
if ($_POST) {
    try {
        $name = trim($_POST['name']);
        $generic_name = trim($_POST['generic_name']);
        $category_id = $_POST['category_id'];
        $supplier_id = $_POST['supplier_id'];
        $batch_number = trim($_POST['batch_number']);
        $barcode = trim($_POST['barcode']);
        $description = trim($_POST['description']);
        $dosage = trim($_POST['dosage']);
        $unit = $_POST['unit'];
        $purchase_price = floatval($_POST['purchase_price']);
        $selling_price = floatval($_POST['selling_price']);
        $stock_quantity = intval($_POST['stock_quantity']);
        $min_stock_level = intval($_POST['min_stock_level']);
        $max_stock_level = intval($_POST['max_stock_level']);
        $expiry_date = $_POST['expiry_date'];
        $manufacture_date = $_POST['manufacture_date'];
        $prescription_required = isset($_POST['prescription_required']) ? 1 : 0;

        // Validation
        if (empty($name) || empty($purchase_price) || empty($selling_price)) {
            throw new Exception('Please fill in all required fields');
        }

        if ($selling_price <= $purchase_price) {
            throw new Exception('Selling price must be greater than purchase price');
        }

        // Insert medicine
        $stmt = $pdo->prepare("
            INSERT INTO medicines (
                name, generic_name, category_id, supplier_id, batch_number, barcode,
                description, dosage, unit, purchase_price, selling_price, stock_quantity,
                min_stock_level, max_stock_level, expiry_date, manufacture_date, prescription_required
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        $stmt->execute([
            $name, $generic_name, $category_id, $supplier_id, $batch_number, $barcode,
            $description, $dosage, $unit, $purchase_price, $selling_price, $stock_quantity,
            $min_stock_level, $max_stock_level, $expiry_date, $manufacture_date, $prescription_required
        ]);

        $success = 'Medicine added successfully!';
        
        // Clear form data
        $_POST = [];
        
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

// Get categories and suppliers for dropdowns
$categories = $pdo->query("SELECT * FROM categories WHERE status = 'active' ORDER BY name")->fetchAll();
$suppliers = $pdo->query("SELECT * FROM suppliers WHERE status = 'active' ORDER BY name")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Medicine - Pharmacy Management</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        
        .form-group {
            position: relative;
            margin-bottom: 1.5rem;
        }
        
        .form-input {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            font-size: 16px;
            transition: all 0.3s ease;
            background: white;
        }
        
        .form-input:focus {
            outline: none;
            border-color: #10b981;
            box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.1);
            transform: translateY(-1px);
        }
        
        .form-label {
            position: absolute;
            left: 16px;
            top: 12px;
            background: white;
            padding: 0 8px;
            color: #6b7280;
            font-size: 16px;
            transition: all 0.3s ease;
            pointer-events: none;
        }
        
        .form-input:focus + .form-label,
        .form-input:not(:placeholder-shown) + .form-label {
            top: -8px;
            font-size: 12px;
            color: #10b981;
            font-weight: 500;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            transition: all 0.3s ease;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(16, 185, 129, 0.3);
        }
        
        .card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }
        
        .card:hover {
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
        }
        
        .alert {
            padding: 16px;
            border-radius: 8px;
            margin-bottom: 24px;
            animation: slideIn 0.3s ease;
        }
        
        @keyframes slideIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .progress-bar {
            height: 4px;
            background: linear-gradient(90deg, #10b981 0%, #059669 100%);
            border-radius: 2px;
            transition: width 0.3s ease;
        }
        
        .section-divider {
            border-left: 4px solid #10b981;
            padding-left: 16px;
            margin: 32px 0 24px 0;
        }
        
        .floating-label select {
            appearance: none;
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='m6 8 4 4 4-4'/%3e%3c/svg%3e");
            background-position: right 12px center;
            background-repeat: no-repeat;
            background-size: 16px;
            padding-right: 40px;
        }
    </style>
</head>
<body class="bg-gradient-to-br from-gray-50 to-blue-50 min-h-screen">
    <?php include '../../includes/navbar.php'; ?>
    
    <div class="container mx-auto px-4 py-8 max-w-4xl">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex items-center space-x-4 mb-4">
                <a href="../inventory/index.php" class="text-gray-600 hover:text-gray-800 transition-colors">
                    <i class="fas fa-arrow-left text-xl"></i>
                </a>
                <div>
                    <h1 class="text-3xl font-bold text-gray-800">Add New Medicine</h1>
                    <p class="text-gray-600">Add a new medicine to your inventory</p>
                </div>
            </div>
            
            <!-- Progress Bar -->
            <div class="bg-gray-200 rounded-full h-2 mb-6">
                <div class="progress-bar w-0" id="progressBar"></div>
            </div>
        </div>

        <!-- Alerts -->
        <?php if ($success): ?>
            <div class="alert bg-green-100 border border-green-400 text-green-700">
                <div class="flex items-center">
                    <i class="fas fa-check-circle mr-3 text-xl"></i>
                    <span><?php echo htmlspecialchars($success); ?></span>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert bg-red-100 border border-red-400 text-red-700">
                <div class="flex items-center">
                    <i class="fas fa-exclamation-circle mr-3 text-xl"></i>
                    <span><?php echo htmlspecialchars($error); ?></span>
                </div>
            </div>
        <?php endif; ?>

        <!-- Form -->
        <form method="POST" class="space-y-8" id="medicineForm">
            <!-- Basic Information -->
            <div class="card p-8">
                <div class="section-divider">
                    <h2 class="text-xl font-semibold text-gray-800">
                        <i class="fas fa-pills text-green-600 mr-2"></i>
                        Basic Information
                    </h2>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="form-group floating-label">
                        <input type="text" name="name" class="form-input" placeholder=" " required value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>">
                        <label class="form-label">Medicine Name *</label>
                    </div>
                    
                    <div class="form-group floating-label">
                        <input type="text" name="generic_name" class="form-input" placeholder=" " value="<?php echo htmlspecialchars($_POST['generic_name'] ?? ''); ?>">
                        <label class="form-label">Generic Name</label>
                    </div>
                    
                    <div class="form-group floating-label">
                        <select name="category_id" class="form-input" required>
                            <option value="">Select Category</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?php echo $category['id']; ?>" <?php echo ($_POST['category_id'] ?? '') == $category['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($category['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <label class="form-label">Category *</label>
                    </div>
                    
                    <div class="form-group floating-label">
                        <select name="supplier_id" class="form-input" required>
                            <option value="">Select Supplier</option>
                            <?php foreach ($suppliers as $supplier): ?>
                                <option value="<?php echo $supplier['id']; ?>" <?php echo ($_POST['supplier_id'] ?? '') == $supplier['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($supplier['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <label class="form-label">Supplier *</label>
                    </div>
                </div>
                
                <div class="form-group floating-label mt-6">
                    <textarea name="description" class="form-input" rows="3" placeholder=" "><?php echo htmlspecialchars($_POST['description'] ?? ''); ?></textarea>
                    <label class="form-label">Description</label>
                </div>
            </div>

            <!-- Product Details -->
            <div class="card p-8">
                <div class="section-divider">
                    <h2 class="text-xl font-semibold text-gray-800">
                        <i class="fas fa-info-circle text-blue-600 mr-2"></i>
                        Product Details
                    </h2>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="form-group floating-label">
                        <input type="text" name="batch_number" class="form-input" placeholder=" " value="<?php echo htmlspecialchars($_POST['batch_number'] ?? ''); ?>">
                        <label class="form-label">Batch Number</label>
                    </div>
                    
                    <div class="form-group floating-label">
                        <input type="text" name="barcode" class="form-input" placeholder=" " value="<?php echo htmlspecialchars($_POST['barcode'] ?? ''); ?>">
                        <label class="form-label">Barcode</label>
                    </div>
                    
                    <div class="form-group floating-label">
                        <input type="text" name="dosage" class="form-input" placeholder=" " value="<?php echo htmlspecialchars($_POST['dosage'] ?? ''); ?>">
                        <label class="form-label">Dosage (e.g., 500mg)</label>
                    </div>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                    <div class="form-group floating-label">
                        <select name="unit" class="form-input" required>
                            <option value="piece" <?php echo ($_POST['unit'] ?? 'piece') == 'piece' ? 'selected' : ''; ?>>Piece</option>
                            <option value="bottle" <?php echo ($_POST['unit'] ?? '') == 'bottle' ? 'selected' : ''; ?>>Bottle</option>
                            <option value="box" <?php echo ($_POST['unit'] ?? '') == 'box' ? 'selected' : ''; ?>>Box</option>
                            <option value="strip" <?php echo ($_POST['unit'] ?? '') == 'strip' ? 'selected' : ''; ?>>Strip</option>
                            <option value="vial" <?php echo ($_POST['unit'] ?? '') == 'vial' ? 'selected' : ''; ?>>Vial</option>
                        </select>
                        <label class="form-label">Unit *</label>
                    </div>
                    
                    <div class="flex items-center space-x-4 mt-4">
                        <label class="flex items-center space-x-2 cursor-pointer">
                            <input type="checkbox" name="prescription_required" class="w-5 h-5 text-green-600 rounded focus:ring-green-500" <?php echo isset($_POST['prescription_required']) ? 'checked' : ''; ?>>
                            <span class="text-gray-700 font-medium">Prescription Required</span>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Pricing & Stock -->
            <div class="card p-8">
                <div class="section-divider">
                    <h2 class="text-xl font-semibold text-gray-800">
                        <i class="fas fa-dollar-sign text-yellow-600 mr-2"></i>
                        Pricing & Stock
                    </h2>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="form-group floating-label">
                        <input type="number" name="purchase_price" class="form-input" step="0.01" placeholder=" " required value="<?php echo $_POST['purchase_price'] ?? ''; ?>">
                        <label class="form-label">Purchase Price (Rs) *</label>
                    </div>
                    
                    <div class="form-group floating-label">
                        <input type="number" name="selling_price" class="form-input" step="0.01" placeholder=" " required value="<?php echo $_POST['selling_price'] ?? ''; ?>">
                        <label class="form-label">Selling Price (Rs) *</label>
                    </div>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-6">
                    <div class="form-group floating-label">
                        <input type="number" name="stock_quantity" class="form-input" placeholder=" " required value="<?php echo $_POST['stock_quantity'] ?? '0'; ?>">
                        <label class="form-label">Stock Quantity *</label>
                    </div>
                    
                    <div class="form-group floating-label">
                        <input type="number" name="min_stock_level" class="form-input" placeholder=" " value="<?php echo $_POST['min_stock_level'] ?? '10'; ?>">
                        <label class="form-label">Min Stock Level</label>
                    </div>
                    
                    <div class="form-group floating-label">
                        <input type="number" name="max_stock_level" class="form-input" placeholder=" " value="<?php echo $_POST['max_stock_level'] ?? '1000'; ?>">
                        <label class="form-label">Max Stock Level</label>
                    </div>
                </div>
            </div>

            <!-- Dates -->
            <div class="card p-8">
                <div class="section-divider">
                    <h2 class="text-xl font-semibold text-gray-800">
                        <i class="fas fa-calendar text-purple-600 mr-2"></i>
                        Important Dates
                    </h2>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="form-group floating-label">
                        <input type="date" name="manufacture_date" class="form-input" placeholder=" " value="<?php echo $_POST['manufacture_date'] ?? ''; ?>">
                        <label class="form-label">Manufacture Date</label>
                    </div>
                    
                    <div class="form-group floating-label">
                        <input type="date" name="expiry_date" class="form-input" placeholder=" " value="<?php echo $_POST['expiry_date'] ?? ''; ?>">
                        <label class="form-label">Expiry Date</label>
                    </div>
                </div>
            </div>

            <!-- Submit Buttons -->
            <div class="flex flex-col sm:flex-row gap-4 justify-end">
                <a href="../inventory/index.php" class="px-8 py-3 bg-gray-500 hover:bg-gray-600 text-white font-medium rounded-lg transition-all duration-200 text-center">
                    <i class="fas fa-times mr-2"></i>Cancel
                </a>
                <button type="submit" class="px-8 py-3 btn-primary text-white font-medium rounded-lg">
                    <i class="fas fa-plus mr-2"></i>Add Medicine
                </button>
            </div>
        </form>
    </div>

    <script>
        // Form progress tracking
        const form = document.getElementById('medicineForm');
        const progressBar = document.getElementById('progressBar');
        const requiredFields = form.querySelectorAll('[required]');
        
        function updateProgress() {
            let filledFields = 0;
            requiredFields.forEach(field => {
                if (field.value.trim() !== '') {
                    filledFields++;
                }
            });
            
            const progress = (filledFields / requiredFields.length) * 100;
            progressBar.style.width = progress + '%';
        }
        
        // Update progress on input
        requiredFields.forEach(field => {
            field.addEventListener('input', updateProgress);
            field.addEventListener('change', updateProgress);
        });
        
        // Initial progress update
        updateProgress();
        
        // Price validation
        const purchasePrice = document.querySelector('[name="purchase_price"]');
        const sellingPrice = document.querySelector('[name="selling_price"]');
        
        function validatePrices() {
            if (purchasePrice.value && sellingPrice.value) {
                if (parseFloat(sellingPrice.value) <= parseFloat(purchasePrice.value)) {
                    sellingPrice.setCustomValidity('Selling price must be greater than purchase price');
                } else {
                    sellingPrice.setCustomValidity('');
                }
            }
        }
        
        purchasePrice.addEventListener('input', validatePrices);
        sellingPrice.addEventListener('input', validatePrices);
        
        // Auto-dismiss alerts
        setTimeout(() => {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                alert.style.opacity = '0';
                alert.style.transform = 'translateY(-10px)';
                setTimeout(() => alert.remove(), 300);
            });
        }, 5000);
        
        // Form animation on load
        document.addEventListener('DOMContentLoaded', () => {
            const cards = document.querySelectorAll('.card');
            cards.forEach((card, index) => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(20px)';
                setTimeout(() => {
                    card.style.transition = 'all 0.5s ease';
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }, index * 100);
            });
        });
    </script>
</body>
</html>