<?php
session_start();
require_once dirname(__DIR__, 2) . '/bootstrap.php';

requireLogin();

$user = getCurrentUser();
$message = '';
$messageType = '';

if ($_POST) {
    try {
        $customerName = trim($_POST['customer_name']);
        $customerPhone = trim($_POST['customer_phone']);
        $customerEmail = trim($_POST['customer_email']);
        $doctorName = trim($_POST['doctor_name']);
        $prescriptionDate = $_POST['prescription_date'];
        $notes = trim($_POST['notes']);
        
        // Handle file upload
        $uploadDir = '../../uploads/prescriptions/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        $imagePath = '';
        if (isset($_FILES['prescription_image']) && $_FILES['prescription_image']['error'] === UPLOAD_ERR_OK) {
            $fileExtension = strtolower(pathinfo($_FILES['prescription_image']['name'], PATHINFO_EXTENSION));
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'pdf'];
            
            if (!in_array($fileExtension, $allowedExtensions)) {
                throw new Exception('Invalid file type. Only JPG, PNG, and PDF files are allowed.');
            }
            
            $fileName = 'prescription_' . time() . '_' . rand(1000, 9999) . '.' . $fileExtension;
            $imagePath = $uploadDir . $fileName;
            
            if (!move_uploaded_file($_FILES['prescription_image']['tmp_name'], $imagePath)) {
                throw new Exception('Failed to upload prescription image.');
            }
            
            $imagePath = 'uploads/prescriptions/' . $fileName; // Store relative path
        }
        
        $pdo->beginTransaction();
        
        // Check if customer exists or create new one
        $stmt = $pdo->prepare("SELECT id FROM customers WHERE phone = ? AND status = 'active'");
        $stmt->execute([$customerPhone]);
        $customer = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$customer) {
            // Create new customer
            $stmt = $pdo->prepare("
                INSERT INTO customers (name, phone, email, customer_code, status)
                VALUES (?, ?, ?, ?, 'active')
            ");
            $customerCode = 'CUST' . date('Ymd') . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT);
            $stmt->execute([$customerName, $customerPhone, $customerEmail, $customerCode]);
            $customerId = $pdo->lastInsertId();
        } else {
            $customerId = $customer['id'];
        }
        
        // Insert prescription
        $stmt = $pdo->prepare("
            INSERT INTO prescriptions (customer_id, doctor_name, prescription_date, image_path, notes, status)
            VALUES (?, ?, ?, ?, ?, 'pending')
        ");
        
        $stmt->execute([$customerId, $doctorName, $prescriptionDate, $imagePath, $notes]);
        
        $pdo->commit();
        
        $message = 'Prescription uploaded successfully! Our pharmacist will verify it shortly.';
        $messageType = 'success';
        
    } catch (Exception $e) {
        $pdo->rollBack();
        $message = 'Error: ' . $e->getMessage();
        $messageType = 'error';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Prescription - Pharmacy Management System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        .drop-zone {
            border: 2px dashed #d1d5db;
            transition: all 0.3s ease;
        }
        .drop-zone.dragover {
            border-color: #10b981;
            background-color: #f0fdf4;
        }
    </style>
</head>
<body class="bg-gray-50">
    <?php include '../../includes/navbar.php'; ?>
    
    <div class="container mx-auto px-4 py-8">
        <!-- Header -->
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-3xl font-bold text-gray-800">Upload Prescription</h1>
                <p class="text-gray-600">Upload your prescription for medicine ordering</p>
            </div>
            <a href="index.php" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg flex items-center space-x-2 transition duration-200">
                <i class="fas fa-arrow-left"></i>
                <span>Back to Prescriptions</span>
            </a>
        </div>

        <!-- Message -->
        <?php if ($message): ?>
            <div class="mb-6 p-4 rounded-lg <?php echo $messageType === 'success' ? 'bg-green-100 border border-green-400 text-green-700' : 'bg-red-100 border border-red-400 text-red-700'; ?>">
                <i class="fas <?php echo $messageType === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'; ?> mr-2"></i>
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <div class="max-w-2xl mx-auto">
            <div class="bg-white rounded-lg shadow-md p-8">
                <form method="POST" enctype="multipart/form-data" class="space-y-6">
                    <!-- Customer Information -->
                    <div>
                        <h2 class="text-xl font-semibold text-gray-800 mb-4">Customer Information</h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    <i class="fas fa-user mr-1"></i>Full Name *
                                </label>
                                <input type="text" name="customer_name" required
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500"
                                       placeholder="Enter your full name">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    <i class="fas fa-phone mr-1"></i>Phone Number *
                                </label>
                                <input type="tel" name="customer_phone" required
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500"
                                       placeholder="Enter your phone number">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    <i class="fas fa-envelope mr-1"></i>Email Address
                                </label>
                                <input type="email" name="customer_email"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500"
                                       placeholder="Enter your email address">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    <i class="fas fa-user-md mr-1"></i>Doctor Name
                                </label>
                                <input type="text" name="doctor_name"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500"
                                       placeholder="Enter doctor's name">
                            </div>
                        </div>
                    </div>

                    <!-- Prescription Details -->
                    <div>
                        <h2 class="text-xl font-semibold text-gray-800 mb-4">Prescription Details</h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    <i class="fas fa-calendar mr-1"></i>Prescription Date
                                </label>
                                <input type="date" name="prescription_date" value="<?php echo date('Y-m-d'); ?>"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    <i class="fas fa-sticky-note mr-1"></i>Additional Notes
                                </label>
                                <input type="text" name="notes"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500"
                                       placeholder="Any special instructions">
                            </div>
                        </div>
                    </div>

                    <!-- File Upload -->
                    <div>
                        <h2 class="text-xl font-semibold text-gray-800 mb-4">Upload Prescription Image</h2>
                        <div class="drop-zone rounded-lg p-8 text-center" id="dropZone">
                            <input type="file" name="prescription_image" id="prescriptionFile" 
                                   accept="image/*,.pdf" class="hidden" onchange="handleFileSelect(this)">
                            <div id="uploadContent">
                                <i class="fas fa-cloud-upload-alt text-4xl text-gray-400 mb-4"></i>
                                <p class="text-lg font-medium text-gray-700 mb-2">Drop your prescription here</p>
                                <p class="text-sm text-gray-500 mb-4">or click to browse files</p>
                                <button type="button" onclick="document.getElementById('prescriptionFile').click()" 
                                        class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded-lg transition duration-200">
                                    <i class="fas fa-folder-open mr-2"></i>Choose File
                                </button>
                                <p class="text-xs text-gray-400 mt-2">Supported formats: JPG, PNG, PDF (Max 5MB)</p>
                            </div>
                            <div id="filePreview" class="hidden">
                                <div class="flex items-center justify-center space-x-4">
                                    <i class="fas fa-file-image text-3xl text-green-600"></i>
                                    <div>
                                        <p class="font-medium text-gray-800" id="fileName"></p>
                                        <p class="text-sm text-gray-500" id="fileSize"></p>
                                    </div>
                                    <button type="button" onclick="removeFile()" class="text-red-600 hover:text-red-800">
                                        <i class="fas fa-times-circle text-xl"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="flex justify-end space-x-4">
                        <button type="button" onclick="window.history.back()" 
                                class="px-6 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition duration-200">
                            Cancel
                        </button>
                        <button type="submit" 
                                class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded-lg flex items-center space-x-2 transition duration-200">
                            <i class="fas fa-upload"></i>
                            <span>Upload Prescription</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Drag and drop functionality
        const dropZone = document.getElementById('dropZone');
        const fileInput = document.getElementById('prescriptionFile');

        dropZone.addEventListener('click', () => fileInput.click());

        dropZone.addEventListener('dragover', (e) => {
            e.preventDefault();
            dropZone.classList.add('dragover');
        });

        dropZone.addEventListener('dragleave', () => {
            dropZone.classList.remove('dragover');
        });

        dropZone.addEventListener('drop', (e) => {
            e.preventDefault();
            dropZone.classList.remove('dragover');
            
            const files = e.dataTransfer.files;
            if (files.length > 0) {
                fileInput.files = files;
                handleFileSelect(fileInput);
            }
        });

        function handleFileSelect(input) {
            const file = input.files[0];
            if (!file) return;

            // Validate file size (5MB limit)
            if (file.size > 5 * 1024 * 1024) {
                alert('File size must be less than 5MB');
                input.value = '';
                return;
            }

            // Show file preview
            document.getElementById('uploadContent').classList.add('hidden');
            document.getElementById('filePreview').classList.remove('hidden');
            document.getElementById('fileName').textContent = file.name;
            document.getElementById('fileSize').textContent = formatFileSize(file.size);
        }

        function removeFile() {
            document.getElementById('prescriptionFile').value = '';
            document.getElementById('uploadContent').classList.remove('hidden');
            document.getElementById('filePreview').classList.add('hidden');
        }

        function formatFileSize(bytes) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        }
    </script>
</body>
</html>