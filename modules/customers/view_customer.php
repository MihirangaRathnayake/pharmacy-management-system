<?php
require_once dirname(__DIR__, 2) . '/bootstrap.php';

requireLogin();

$customerId = $_GET['id'] ?? null;

if (!$customerId) {
    header('Location: index.php');
    exit();
}

try {
    // Get customer details
    $stmt = $pdo->prepare("
        SELECT c.*, u.email as user_email, u.status as user_status
        FROM customers c
        LEFT JOIN users u ON c.user_id = u.id
        WHERE c.id = ?
    ");
    $stmt->execute([$customerId]);
    $customer = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$customer) {
        header('Location: index.php');
        exit();
    }
    
    // Get customer's purchase history
    $stmt = $pdo->prepare("
        SELECT s.*, COUNT(si.id) as item_count
        FROM sales s
        LEFT JOIN sale_items si ON s.id = si.sale_id
        WHERE s.customer_id = ?
        GROUP BY s.id
        ORDER BY s.sale_date DESC
        LIMIT 10
    ");
    $stmt->execute([$customerId]);
    $purchases = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get customer's prescriptions
    $stmt = $pdo->prepare("
        SELECT p.*, u.name as verified_by_name
        FROM prescriptions p
        LEFT JOIN users u ON p.verified_by = u.id
        WHERE p.customer_id = ?
        ORDER BY p.created_at DESC
        LIMIT 5
    ");
    $stmt->execute([$customerId]);
    $prescriptions = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Calculate customer stats
    $stmt = $pdo->prepare("
        SELECT 
            COUNT(*) as total_orders,
            COALESCE(SUM(total_amount), 0) as total_spent,
            MAX(sale_date) as last_order_date,
            AVG(total_amount) as avg_order_value
        FROM sales 
        WHERE customer_id = ? AND status = 'completed'
    ");
    $stmt->execute([$customerId]);
    $stats = $stmt->fetch(PDO::FETCH_ASSOC);
    
} catch (Exception $e) {
    $error = $e->getMessage();
}

$user = getCurrentUser();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Customer - <?php echo htmlspecialchars($customer['name']); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        .info-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            border: 1px solid #e5e7eb;
            overflow: hidden;
        }
        .stat-card {
            background: linear-gradient(135deg, #10b981, #059669);
            color: white;
            border-radius: 12px;
            padding: 24px;
            text-align: center;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }
        .customer-avatar {
            width: 120px;
            height: 120px;
            background: linear-gradient(135deg, #10b981, #059669);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 48px;
            box-shadow: 0 8px 25px rgba(16, 185, 129, 0.3);
            margin: 0 auto;
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
                    <div class="w-12 h-12 bg-blue-500 rounded-full flex items-center justify-center">
                        <i class="fas fa-user text-white text-xl"></i>
                    </div>
                    Customer Profile
                </h1>
                <p class="text-gray-600 mt-2">Detailed information and purchase history</p>
            </div>
            <div class="flex gap-3">
                <a href="edit_customer.php?id=<?php echo $customer['id']; ?>" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg flex items-center space-x-2 transition duration-200">
                    <i class="fas fa-edit"></i>
                    <span>Edit Customer</span>
                </a>
                <a href="index.php" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg flex items-center space-x-2 transition duration-200">
                    <i class="fas fa-arrow-left"></i>
                    <span>Back to List</span>
                </a>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Customer Information -->
            <div class="lg:col-span-1">
                <div class="info-card p-6 mb-6">
                    <div class="text-center mb-6">
                        <div class="customer-avatar mb-4">
                            <i class="fas fa-user"></i>
                        </div>
                        <h2 class="text-2xl font-bold text-gray-800"><?php echo htmlspecialchars($customer['name']); ?></h2>
                        <p class="text-gray-600"><?php echo htmlspecialchars($customer['customer_code']); ?></p>
                        <span class="inline-block mt-2 px-3 py-1 bg-green-100 text-green-800 text-sm font-medium rounded-full">
                            <?php echo ucfirst($customer['status']); ?>
                        </span>
                    </div>
                    
                    <div class="space-y-4">
                        <?php if ($customer['phone']): ?>
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                                <i class="fas fa-phone text-blue-600"></i>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Phone</p>
                                <p class="font-medium"><?php echo htmlspecialchars($customer['phone']); ?></p>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <?php if ($customer['email']): ?>
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 bg-purple-100 rounded-full flex items-center justify-center">
                                <i class="fas fa-envelope text-purple-600"></i>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Email</p>
                                <p class="font-medium"><?php echo htmlspecialchars($customer['email']); ?></p>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <?php if ($customer['date_of_birth']): ?>
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 bg-pink-100 rounded-full flex items-center justify-center">
                                <i class="fas fa-birthday-cake text-pink-600"></i>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Date of Birth</p>
                                <p class="font-medium"><?php echo date('M d, Y', strtotime($customer['date_of_birth'])); ?></p>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <?php if ($customer['gender']): ?>
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 bg-indigo-100 rounded-full flex items-center justify-center">
                                <i class="fas fa-venus-mars text-indigo-600"></i>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Gender</p>
                                <p class="font-medium"><?php echo ucfirst($customer['gender']); ?></p>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center">
                                <i class="fas fa-calendar text-green-600"></i>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Member Since</p>
                                <p class="font-medium"><?php echo date('M d, Y', strtotime($customer['created_at'])); ?></p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Customer Stats -->
                <div class="grid grid-cols-2 gap-4 mb-6">
                    <div class="stat-card">
                        <div class="text-3xl font-bold"><?php echo $stats['total_orders']; ?></div>
                        <div class="text-sm opacity-90">Total Orders</div>
                    </div>
                    <div class="stat-card">
                        <div class="text-3xl font-bold">Rs <?php echo number_format($stats['total_spent'], 0); ?></div>
                        <div class="text-sm opacity-90">Total Spent</div>
                    </div>
                </div>
            </div>

            <!-- Details and History -->
            <div class="lg:col-span-2">
                <!-- Additional Information -->
                <div class="info-card mb-6">
                    <div class="p-6 border-b">
                        <h3 class="text-lg font-semibold text-gray-800 flex items-center gap-2">
                            <i class="fas fa-info-circle text-blue-500"></i>
                            Additional Information
                        </h3>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <?php if ($customer['address']): ?>
                            <div>
                                <h4 class="font-medium text-gray-800 mb-2 flex items-center gap-2">
                                    <i class="fas fa-map-marker-alt text-red-500"></i>
                                    Address
                                </h4>
                                <p class="text-gray-600"><?php echo nl2br(htmlspecialchars($customer['address'])); ?></p>
                            </div>
                            <?php endif; ?>
                            
                            <?php if ($customer['emergency_contact']): ?>
                            <div>
                                <h4 class="font-medium text-gray-800 mb-2 flex items-center gap-2">
                                    <i class="fas fa-phone-alt text-orange-500"></i>
                                    Emergency Contact
                                </h4>
                                <p class="text-gray-600"><?php echo htmlspecialchars($customer['emergency_contact']); ?></p>
                            </div>
                            <?php endif; ?>
                            
                            <?php if ($customer['allergies']): ?>
                            <div>
                                <h4 class="font-medium text-gray-800 mb-2 flex items-center gap-2">
                                    <i class="fas fa-exclamation-triangle text-yellow-500"></i>
                                    Allergies
                                </h4>
                                <p class="text-gray-600"><?php echo nl2br(htmlspecialchars($customer['allergies'])); ?></p>
                            </div>
                            <?php endif; ?>
                            
                            <?php if ($customer['medical_conditions']): ?>
                            <div>
                                <h4 class="font-medium text-gray-800 mb-2 flex items-center gap-2">
                                    <i class="fas fa-notes-medical text-teal-500"></i>
                                    Medical Conditions
                                </h4>
                                <p class="text-gray-600"><?php echo nl2br(htmlspecialchars($customer['medical_conditions'])); ?></p>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Purchase History -->
                <div class="info-card mb-6">
                    <div class="p-6 border-b">
                        <h3 class="text-lg font-semibold text-gray-800 flex items-center gap-2">
                            <i class="fas fa-shopping-bag text-green-500"></i>
                            Recent Purchases
                        </h3>
                    </div>
                    <div class="p-6">
                        <?php if (empty($purchases)): ?>
                            <div class="text-center py-8 text-gray-500">
                                <i class="fas fa-shopping-bag text-4xl mb-4"></i>
                                <p>No purchases yet</p>
                            </div>
                        <?php else: ?>
                            <div class="space-y-4">
                                <?php foreach ($purchases as $purchase): ?>
                                <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                                    <div>
                                        <p class="font-medium text-gray-800">#<?php echo htmlspecialchars($purchase['invoice_number']); ?></p>
                                        <p class="text-sm text-gray-600"><?php echo $purchase['item_count']; ?> items</p>
                                        <p class="text-xs text-gray-500"><?php echo date('M d, Y g:i A', strtotime($purchase['sale_date'])); ?></p>
                                    </div>
                                    <div class="text-right">
                                        <p class="font-bold text-green-600">Rs <?php echo number_format($purchase['total_amount'], 2); ?></p>
                                        <span class="inline-block px-2 py-1 bg-green-100 text-green-800 text-xs rounded-full">
                                            <?php echo ucfirst($purchase['status']); ?>
                                        </span>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Prescriptions -->
                <?php if (!empty($prescriptions)): ?>
                <div class="info-card">
                    <div class="p-6 border-b">
                        <h3 class="text-lg font-semibold text-gray-800 flex items-center gap-2">
                            <i class="fas fa-file-medical text-blue-500"></i>
                            Recent Prescriptions
                        </h3>
                    </div>
                    <div class="p-6">
                        <div class="space-y-4">
                            <?php foreach ($prescriptions as $prescription): ?>
                            <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                                <div>
                                    <p class="font-medium text-gray-800">Dr. <?php echo htmlspecialchars($prescription['doctor_name'] ?: 'Unknown'); ?></p>
                                    <p class="text-sm text-gray-600"><?php echo date('M d, Y', strtotime($prescription['created_at'])); ?></p>
                                </div>
                                <div class="text-right">
                                    <span class="inline-block px-2 py-1 text-xs rounded-full
                                        <?php 
                                        switch($prescription['status']) {
                                            case 'pending': echo 'bg-yellow-100 text-yellow-800'; break;
                                            case 'verified': echo 'bg-green-100 text-green-800'; break;
                                            case 'processed': echo 'bg-blue-100 text-blue-800'; break;
                                            case 'rejected': echo 'bg-red-100 text-red-800'; break;
                                            default: echo 'bg-gray-100 text-gray-800';
                                        }
                                        ?>">
                                        <?php echo ucfirst($prescription['status']); ?>
                                    </span>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>