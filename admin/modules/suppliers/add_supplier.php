<?php
require_once dirname(__DIR__, 2) . '/bootstrap.php';

requireLogin();

$error = '';
$success = '';

if ($_POST) {
    try {
        $name = trim($_POST['name']);
        $contact_person = trim($_POST['contact_person']);
        $email = trim($_POST['email']);
        $phone = trim($_POST['phone']);
        $address = trim($_POST['address']);

        // Validation
        if (empty($name)) {
            throw new Exception('Supplier name is required');
        }

        if (empty($phone)) {
            throw new Exception('Phone number is required');
        }

        if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception('Please enter a valid email address');
        }

        // Check if supplier name already exists
        $stmt = $pdo->prepare("SELECT id FROM suppliers WHERE name = ? AND status = 'active'");
        $stmt->execute([$name]);
        if ($stmt->fetch()) {
            throw new Exception('A supplier with this name already exists');
        }

        // Check if email already exists (if provided)
        if (!empty($email)) {
            $stmt = $pdo->prepare("SELECT id FROM suppliers WHERE email = ? AND status = 'active'");
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                throw new Exception('A supplier with this email already exists');
            }
        }

        // Insert supplier
        $stmt = $pdo->prepare("
            INSERT INTO suppliers (name, contact_person, email, phone, address, status)
            VALUES (?, ?, ?, ?, ?, 'active')
        ");
        $stmt->execute([$name, $contact_person, $email, $phone, $address]);

        $success = 'Supplier added successfully!';
        $_POST = [];
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

$user = getCurrentUser();
?>
<!DOCTYPE html>
<html lang="en" data-theme="<?php echo getThemeClass(); ?>">

<head>
    <title>Add Supplier - Pharmacy Management System</title>
    <?php include '../../includes/head.php'; ?>
    <style>
        .form-section {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            border: 1px solid #e5e7eb;
            margin-bottom: 24px;
            overflow: hidden;
        }

        .section-header {
            background: linear-gradient(135deg, #3b82f6, #2563eb);
            color: white;
            padding: 16px 24px;
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
            border-color: #3b82f6;
            background: white;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
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
        .icon-input textarea {
            padding-left: 48px;
        }
    </style>
</head>

<body class="pc-shell">
    <?php include '../../includes/navbar.php'; ?>

    <div class="pc-container">
        <div class="pc-page-header pc-animate">
            <div class="pc-breadcrumb">Home <i class="fas fa-chevron-right"></i> <a href="index.php" class="text-blue-600 hover:underline">Suppliers</a> <i class="fas fa-chevron-right"></i> Add Supplier</div>
            <div class="flex justify-between items-center gap-4">
                <div>
                    <h1 class="pc-page-title">Add New Supplier</h1>
                    <p class="pc-page-subtitle">Register a new medicine supplier</p>
                </div>
                <a href="index.php" class="pc-btn pc-btn-muted">
                    <i class="fas fa-arrow-left"></i>
                    <span>Back to Suppliers</span>
                </a>
            </div>
        </div>

        <?php if ($success): ?>
            <div class="mb-6 p-4 bg-green-50 border border-green-200 text-green-700 rounded-lg flex items-center gap-3">
                <i class="fas fa-check-circle text-green-500 text-xl"></i>
                <div>
                    <p class="font-medium"><?php echo htmlspecialchars($success); ?></p>
                    <a href="index.php" class="text-sm text-green-600 hover:underline">View all suppliers</a>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="mb-6 p-4 bg-red-50 border border-red-200 text-red-700 rounded-lg flex items-center gap-3">
                <i class="fas fa-exclamation-circle text-red-500 text-xl"></i>
                <p class="font-medium"><?php echo htmlspecialchars($error); ?></p>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-section">
                <div class="section-header">
                    <h2 class="text-lg font-semibold flex items-center gap-2">
                        <i class="fas fa-truck"></i> Supplier Information
                    </h2>
                </div>
                <div class="section-content">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="form-group">
                            <label class="form-label">Supplier Name <span class="text-red-500">*</span></label>
                            <div class="icon-input">
                                <i class="fas fa-building"></i>
                                <input type="text" name="name" class="form-input" placeholder="Enter supplier name"
                                    value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Contact Person</label>
                            <div class="icon-input">
                                <i class="fas fa-user"></i>
                                <input type="text" name="contact_person" class="form-input" placeholder="Enter contact person name"
                                    value="<?php echo htmlspecialchars($_POST['contact_person'] ?? ''); ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Email Address</label>
                            <div class="icon-input">
                                <i class="fas fa-envelope"></i>
                                <input type="email" name="email" class="form-input" placeholder="Enter email address"
                                    value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Phone Number <span class="text-red-500">*</span></label>
                            <div class="icon-input">
                                <i class="fas fa-phone"></i>
                                <input type="text" name="phone" class="form-input" placeholder="Enter phone number"
                                    value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>" required>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Address</label>
                        <div class="icon-input">
                            <i class="fas fa-map-marker-alt" style="top: 24px; transform: none;"></i>
                            <textarea name="address" class="form-input form-textarea" placeholder="Enter full address"><?php echo htmlspecialchars($_POST['address'] ?? ''); ?></textarea>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex justify-end gap-3">
                <a href="index.php" class="pc-btn pc-btn-muted px-6 py-3">Cancel</a>
                <button type="submit" class="pc-btn pc-btn-primary px-6 py-3">
                    <i class="fas fa-plus mr-2"></i>Add Supplier
                </button>
            </div>
        </form>
    </div>

    <script src="../../assets/js/icon-fix.js"></script>
</body>

</html>