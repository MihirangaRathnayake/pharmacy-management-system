<?php
require_once dirname(__DIR__, 2) . '/bootstrap.php';

requireLogin();

$error = '';
$success = '';

if ($_POST) {
    try {
        // Get form data
        $name = trim($_POST['name']);
        $email = trim($_POST['email']);
        $phone = trim($_POST['phone']);
        $address = trim($_POST['address']);
        $dateOfBirth = $_POST['date_of_birth'] ?: null;
        $gender = $_POST['gender'] ?: null;
        $emergencyContact = trim($_POST['emergency_contact']);
        $allergies = trim($_POST['allergies']);
        $medicalConditions = trim($_POST['medical_conditions']);
        
        // Validation
        if (empty($name)) {
            throw new Exception('Customer name is required');
        }
        
        if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception('Please enter a valid email address');
        }
        
        if (empty($phone)) {
            throw new Exception('Phone number is required');
        }
        
        // Check if phone already exists
        $stmt = $pdo->prepare("SELECT id FROM customers WHERE phone = ? AND status = 'active'");
        $stmt->execute([$phone]);
        if ($stmt->fetch()) {
            throw new Exception('A customer with this phone number already exists');
        }
        
        // Check if email already exists (if provided)
        if (!empty($email)) {
            $stmt = $pdo->prepare("SELECT id FROM customers WHERE email = ? AND status = 'active'");
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                throw new Exception('A customer with this email address already exists');
            }
        }
        
        $pdo->beginTransaction();
        
        // Generate customer code
        $customerCode = generateCustomerCode();
        
        // Insert customer
        $stmt = $pdo->prepare("
            INSERT INTO customers (
                customer_code, name, email, phone, address, date_of_birth, 
                gender, emergency_contact, allergies, medical_conditions, status
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'active')
        ");
        
        $stmt->execute([
            $customerCode, $name, $email, $phone, $address, $dateOfBirth,
            $gender, $emergencyContact, $allergies, $medicalConditions
        ]);
        
        $customerId = $pdo->lastInsertId();
        
        // Create user account if email is provided
        if (!empty($email)) {
            $defaultPassword = 'customer123'; // In real app, generate random password and send via email
            $hashedPassword = password_hash($defaultPassword, PASSWORD_DEFAULT);
            
            $stmt = $pdo->prepare("
                INSERT INTO users (name, email, password, phone, role, status) 
                VALUES (?, ?, ?, ?, 'customer', 'active')
            ");
            $stmt->execute([$name, $email, $hashedPassword, $phone]);
            
            $userId = $pdo->lastInsertId();
            
            // Link customer to user
            $stmt = $pdo->prepare("UPDATE customers SET user_id = ? WHERE id = ?");
            $stmt->execute([$userId, $customerId]);
        }
        
        $pdo->commit();
        
        $success = "Customer added successfully! Customer Code: $customerCode";
        
        // Clear form data
        $_POST = [];
        
    } catch (Exception $e) {
        $pdo->rollBack();
        $error = $e->getMessage();
    }
}

$user = getCurrentUser();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Customer - Pharmacy Management System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        .form-section {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            border: 1px solid #e5e7eb;
            margin-bottom: 24px;
            overflow: hidden;
        }
        .section-header {
            background: linear-gradient(135deg, #10b981, #059669);
            color: white;
            padding: 16px 24px;
            border-bottom: 1px solid #e5e7eb;
        }
        .section-content {
            padding: 24px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-label {
            display: block;
            font-size: 14px;
            font-weight: 500;
            color: #374151;
            margin-bottom: 6px;
        }
        .form-input {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            font-size: 14px;
            transition: all 0.2s ease;
            background: #fafafa;
        }
        .form-input:focus {
            outline: none;
            border-color: #10b981;
            background: white;
            box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.1);
        }
        .form-textarea {
            min-height: 100px;
            resize: vertical;
        }
        .icon-input {
            position: relative;
        }
        .icon-input i {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: #9ca3af;
            z-index: 10;
        }
        .icon-input input,
        .icon-input select,
        .icon-input textarea {
            padding-left: 48px;
        }
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 24px;
            border-radius: 8px;
            font-weight: 500;
            font-size: 14px;
            transition: all 0.2s ease;
            cursor: pointer;
            border: none;
            text-decoration: none;
        }
        .btn-primary {
            background: linear-gradient(135deg, #10b981, #059669);
            color: white;
        }
        .btn-primary:hover {
            background: linear-gradient(135deg, #059669, #047857);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.4);
        }
        .btn-secondary {
            background: #f3f4f6;
            color: #374151;
            border: 2px solid #e5e7eb;
        }
        .btn-secondary:hover {
            background: #e5e7eb;
            border-color: #d1d5db;
        }
        .alert {
            padding: 16px 20px;
            border-radius: 8px;
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .alert-success {
            background: #ecfdf5;
            color: #065f46;
            border: 1px solid #a7f3d0;
        }
        .alert-error {
            background: #fef2f2;
            color: #991b1b;
            border: 1px solid #fca5a5;
        }
        .customer-avatar {
            width: 120px;
            height: 120px;
            background: linear-gradient(135deg, #10b981, #059669);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            color: white;
            font-size: 48px;
            box-shadow: 0 8px 25px rgba(16, 185, 129, 0.3);
        }
        .grid-2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        .grid-3 {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 20px;
        }
        @media (max-width: 768px) {
            .grid-2, .grid-3 {
                grid-template-columns: 1fr;
            }
        }
        .required {
            color: #ef4444;
        }
        .help-text {
            font-size: 12px;
            color: #6b7280;
            margin-top: 4px;
        }
    </style>
</head>
<body class="bg-gray-50">
    <?php include '../../includes/navbar.php'; ?>
    
    <div class="container mx-auto px-4 py-8">
        <!-- Header -->
        <div class="flex justify-between items-center mb-8">
            <div>
                <h1 class="text-3xl font-bold text-gray-800 flex items-center gap-3">
                    <div class="w-12 h-12 bg-green-500 rounded-full flex items-center justify-center">
                        <i class="fas fa-user-plus text-white text-xl"></i>
                    </div>
                    Add New Customer
                </h1>
                <p class="text-gray-600 mt-2">Create a new customer profile with complete information</p>
            </div>
            <a href="index.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i>
                Back to Customers
            </a>
        </div>

        <!-- Messages -->
        <?php if ($success): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle text-xl"></i>
                <div>
                    <strong>Success!</strong> <?php echo htmlspecialchars($success); ?>
                    <div class="help-text mt-1">The customer has been added to the database and can now make purchases.</div>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle text-xl"></i>
                <div>
                    <strong>Error!</strong> <?php echo htmlspecialchars($error); ?>
                </div>
            </div>
        <?php endif; ?>

        <form method="POST" class="max-w-4xl mx-auto">
            <!-- Customer Avatar -->
            <div class="form-section">
                <div class="section-content text-center">
                    <div class="customer-avatar">
                        <i class="fas fa-user"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-800 mb-2">Customer Profile</h3>
                    <p class="text-gray-600 text-sm">Fill in the customer information below</p>
                </div>
            </div>

            <!-- Basic Information -->
            <div class="form-section">
                <div class="section-header">
                    <h2 class="text-lg font-semibold flex items-center gap-2">
                        <i class="fas fa-id-card"></i>
                        Basic Information
                    </h2>
                </div>
                <div class="section-content">
                    <div class="grid-2">
                        <div class="form-group">
                            <label class="form-label">
                                <i class="fas fa-user text-green-500 mr-2"></i>
                                Full Name <span class="required">*</span>
                            </label>
                            <div class="icon-input">
                                <i class="fas fa-user"></i>
                                <input type="text" name="name" class="form-input" 
                                       value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>" 
                                       placeholder="Enter customer's full name" required>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">
                                <i class="fas fa-phone text-blue-500 mr-2"></i>
                                Phone Number <span class="required">*</span>
                            </label>
                            <div class="icon-input">
                                <i class="fas fa-phone"></i>
                                <input type="tel" name="phone" class="form-input" 
                                       value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>" 
                                       placeholder="Enter phone number" required>
                            </div>
                            <div class="help-text">Primary contact number for the customer</div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">
                            <i class="fas fa-envelope text-purple-500 mr-2"></i>
                            Email Address
                        </label>
                        <div class="icon-input">
                            <i class="fas fa-envelope"></i>
                            <input type="email" name="email" class="form-input" 
                                   value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" 
                                   placeholder="Enter email address (optional)">
                        </div>
                        <div class="help-text">If provided, a user account will be created for online access</div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">
                            <i class="fas fa-map-marker-alt text-red-500 mr-2"></i>
                            Address
                        </label>
                        <div class="icon-input">
                            <i class="fas fa-map-marker-alt"></i>
                            <textarea name="address" class="form-input form-textarea" 
                                      placeholder="Enter complete address"><?php echo htmlspecialchars($_POST['address'] ?? ''); ?></textarea>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Personal Details -->
            <div class="form-section">
                <div class="section-header">
                    <h2 class="text-lg font-semibold flex items-center gap-2">
                        <i class="fas fa-user-circle"></i>
                        Personal Details
                    </h2>
                </div>
                <div class="section-content">
                    <div class="grid-3">
                        <div class="form-group">
                            <label class="form-label">
                                <i class="fas fa-birthday-cake text-pink-500 mr-2"></i>
                                Date of Birth
                            </label>
                            <div class="icon-input">
                                <i class="fas fa-calendar"></i>
                                <input type="date" name="date_of_birth" class="form-input" 
                                       value="<?php echo htmlspecialchars($_POST['date_of_birth'] ?? ''); ?>">
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">
                                <i class="fas fa-venus-mars text-indigo-500 mr-2"></i>
                                Gender
                            </label>
                            <div class="icon-input">
                                <i class="fas fa-venus-mars"></i>
                                <select name="gender" class="form-input">
                                    <option value="">Select Gender</option>
                                    <option value="male" <?php echo ($_POST['gender'] ?? '') === 'male' ? 'selected' : ''; ?>>Male</option>
                                    <option value="female" <?php echo ($_POST['gender'] ?? '') === 'female' ? 'selected' : ''; ?>>Female</option>
                                    <option value="other" <?php echo ($_POST['gender'] ?? '') === 'other' ? 'selected' : ''; ?>>Other</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">
                                <i class="fas fa-phone-alt text-orange-500 mr-2"></i>
                                Emergency Contact
                            </label>
                            <div class="icon-input">
                                <i class="fas fa-phone-alt"></i>
                                <input type="tel" name="emergency_contact" class="form-input" 
                                       value="<?php echo htmlspecialchars($_POST['emergency_contact'] ?? ''); ?>" 
                                       placeholder="Emergency contact number">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Medical Information -->
            <div class="form-section">
                <div class="section-header">
                    <h2 class="text-lg font-semibold flex items-center gap-2">
                        <i class="fas fa-heartbeat"></i>
                        Medical Information
                    </h2>
                </div>
                <div class="section-content">
                    <div class="grid-2">
                        <div class="form-group">
                            <label class="form-label">
                                <i class="fas fa-exclamation-triangle text-yellow-500 mr-2"></i>
                                Allergies
                            </label>
                            <div class="icon-input">
                                <i class="fas fa-exclamation-triangle"></i>
                                <textarea name="allergies" class="form-input form-textarea" 
                                          placeholder="List any known allergies (e.g., Penicillin, Aspirin, etc.)"><?php echo htmlspecialchars($_POST['allergies'] ?? ''); ?></textarea>
                            </div>
                            <div class="help-text">Important for prescription safety</div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">
                                <i class="fas fa-notes-medical text-teal-500 mr-2"></i>
                                Medical Conditions
                            </label>
                            <div class="icon-input">
                                <i class="fas fa-notes-medical"></i>
                                <textarea name="medical_conditions" class="form-input form-textarea" 
                                          placeholder="List any chronic conditions (e.g., Diabetes, Hypertension, etc.)"><?php echo htmlspecialchars($_POST['medical_conditions'] ?? ''); ?></textarea>
                            </div>
                            <div class="help-text">Helps in providing better healthcare advice</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="form-section">
                <div class="section-content">
                    <div class="flex justify-between items-center">
                        <div class="text-sm text-gray-600">
                            <i class="fas fa-info-circle mr-2"></i>
                            Fields marked with <span class="required">*</span> are required
                        </div>
                        <div class="flex gap-4">
                            <button type="button" onclick="resetForm()" class="btn btn-secondary">
                                <i class="fas fa-undo"></i>
                                Reset Form
                            </button>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i>
                                Save Customer
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <script>
        function resetForm() {
            if (confirm('Are you sure you want to reset the form? All entered data will be lost.')) {
                document.querySelector('form').reset();
            }
        }

        // Auto-format phone number
        document.querySelector('input[name="phone"]').addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length >= 10) {
                value = value.substring(0, 10);
                e.target.value = value.replace(/(\d{3})(\d{3})(\d{4})/, '($1) $2-$3');
            }
        });

        // Auto-format emergency contact
        document.querySelector('input[name="emergency_contact"]').addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length >= 10) {
                value = value.substring(0, 10);
                e.target.value = value.replace(/(\d{3})(\d{3})(\d{4})/, '($1) $2-$3');
            }
        });

        // Form validation
        document.querySelector('form').addEventListener('submit', function(e) {
            const name = document.querySelector('input[name="name"]').value.trim();
            const phone = document.querySelector('input[name="phone"]').value.trim();
            
            if (!name) {
                e.preventDefault();
                alert('Please enter the customer name');
                document.querySelector('input[name="name"]').focus();
                return;
            }
            
            if (!phone) {
                e.preventDefault();
                alert('Please enter the phone number');
                document.querySelector('input[name="phone"]').focus();
                return;
            }
            
            // Show loading state
            const submitBtn = document.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
            submitBtn.disabled = true;
            
            // Re-enable after 5 seconds (in case of error)
            setTimeout(() => {
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            }, 5000);
        });

        // Auto-capitalize name
        document.querySelector('input[name="name"]').addEventListener('input', function(e) {
            e.target.value = e.target.value.replace(/\b\w/g, l => l.toUpperCase());
        });
    </script>
</body>
</html>