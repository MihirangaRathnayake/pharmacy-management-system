<?php
require_once 'bootstrap.php';
require_once 'includes/action_buttons.php';

// Demo data
$medicines = [
    ['id' => 1, 'name' => 'Amoxicillin 250mg', 'category' => 'Antibiotics', 'stock' => 50, 'price' => 25.00, 'status' => 'Active'],
    ['id' => 2, 'name' => 'Paracetamol 500mg', 'category' => 'Pain Relief', 'stock' => 5, 'price' => 15.00, 'status' => 'Active'],
    ['id' => 3, 'name' => 'Aspirin 100mg', 'category' => 'Cardiovascular', 'stock' => 0, 'price' => 12.00, 'status' => 'Discontinued'],
];

$customers = [
    ['id' => 1, 'name' => 'John Doe', 'phone' => '+1234567890', 'email' => 'john@example.com', 'orders' => 5],
    ['id' => 2, 'name' => 'Jane Smith', 'phone' => '+1234567891', 'email' => 'jane@example.com', 'orders' => 12],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Action Buttons Demo - Pharmacy Management System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <?php echo renderActionButtonsCSS(); ?>
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-gray-50 min-h-screen">
    <div class="container mx-auto px-4 py-8">
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-800 mb-2">Action Buttons Demo</h1>
            <p class="text-gray-600">Interactive examples of enhanced CRUD action buttons</p>
        </div>

        <!-- Medicine Inventory Example -->
        <div class="bg-white rounded-lg shadow-md mb-8">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-xl font-semibold text-gray-800">Medicine Inventory Actions</h2>
                <p class="text-sm text-gray-600">Standard view, edit, delete actions with status-based styling</p>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Medicine</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Category</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Stock</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Price</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <?php foreach ($medicines as $medicine): ?>
                            <tr class="table-row">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900"><?php echo $medicine['name']; ?></div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo $medicine['category']; ?></td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="text-sm font-medium <?php echo $medicine['stock'] <= 5 ? 'text-red-600' : 'text-gray-900'; ?>">
                                        <?php echo $medicine['stock']; ?>
                                        <?php if ($medicine['stock'] <= 5): ?>
                                            <i class="fas fa-exclamation-triangle text-red-500 ml-1 alert-icon"></i>
                                        <?php endif; ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Rs <?php echo number_format($medicine['price'], 2); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full status-badge
                                        <?php echo $medicine['status'] === 'Active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
                                        <?php echo $medicine['status']; ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <?php echo renderActionButtons([
                                        'id' => $medicine['id'],
                                        'name' => $medicine['name'],
                                        'module' => 'inventory',
                                        'actions' => ['view', 'edit', 'delete']
                                    ]); ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Customer Management Example -->
        <div class="bg-white rounded-lg shadow-md mb-8">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-xl font-semibold text-gray-800">Customer Management Actions</h2>
                <p class="text-sm text-gray-600">Actions with custom buttons for additional functionality</p>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Customer</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Contact</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Orders</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <?php foreach ($customers as $customer): ?>
                            <tr class="table-row">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900"><?php echo $customer['name']; ?></div>
                                    <div class="text-sm text-gray-500"><?php echo $customer['email']; ?></div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo $customer['phone']; ?></td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="text-sm font-medium text-blue-600"><?php echo $customer['orders']; ?> orders</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <?php echo renderActionButtons([
                                        'id' => $customer['id'],
                                        'name' => $customer['name'],
                                        'module' => 'customers',
                                        'actions' => ['view', 'edit'],
                                        'custom_actions' => [
                                            [
                                                'label' => 'Orders',
                                                'url' => 'modules/sales/customer_orders.php?customer_id={id}',
                                                'class' => 'bg-purple-100 hover:bg-purple-200 text-purple-700',
                                                'icon' => 'fas fa-shopping-cart'
                                            ],
                                            [
                                                'label' => 'Message',
                                                'url' => 'modules/communication/send_message.php?customer_id={id}',
                                                'class' => 'bg-yellow-100 hover:bg-yellow-200 text-yellow-700',
                                                'icon' => 'fas fa-envelope'
                                            ]
                                        ]
                                    ]); ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Button Variations -->
        <div class="bg-white rounded-lg shadow-md mb-8">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-xl font-semibold text-gray-800">Button Variations</h2>
                <p class="text-sm text-gray-600">Different button styles and configurations</p>
            </div>
            <div class="p-6 space-y-6">
                <!-- Standard Actions -->
                <div>
                    <h3 class="text-lg font-medium text-gray-800 mb-3">Standard Actions</h3>
                    <div class="flex space-x-2">
                        <a href="#" class="action-btn inline-flex items-center px-3 py-2 bg-blue-100 hover:bg-blue-200 text-blue-700 text-sm font-medium rounded-md">
                            <i class="fas fa-eye mr-2"></i>View
                        </a>
                        <a href="#" class="action-btn inline-flex items-center px-3 py-2 bg-green-100 hover:bg-green-200 text-green-700 text-sm font-medium rounded-md">
                            <i class="fas fa-edit mr-2"></i>Edit
                        </a>
                        <button class="action-btn inline-flex items-center px-3 py-2 bg-red-100 hover:bg-red-200 text-red-700 text-sm font-medium rounded-md">
                            <i class="fas fa-trash mr-2"></i>Delete
                        </button>
                    </div>
                </div>

                <!-- Custom Actions -->
                <div>
                    <h3 class="text-lg font-medium text-gray-800 mb-3">Custom Actions</h3>
                    <div class="flex space-x-2">
                        <button class="action-btn inline-flex items-center px-3 py-2 bg-purple-100 hover:bg-purple-200 text-purple-700 text-sm font-medium rounded-md">
                            <i class="fas fa-download mr-2"></i>Export
                        </button>
                        <button class="action-btn inline-flex items-center px-3 py-2 bg-orange-100 hover:bg-orange-200 text-orange-700 text-sm font-medium rounded-md">
                            <i class="fas fa-print mr-2"></i>Print
                        </button>
                        <button class="action-btn inline-flex items-center px-3 py-2 bg-indigo-100 hover:bg-indigo-200 text-indigo-700 text-sm font-medium rounded-md">
                            <i class="fas fa-share mr-2"></i>Share
                        </button>
                        <button class="action-btn inline-flex items-center px-3 py-2 bg-pink-100 hover:bg-pink-200 text-pink-700 text-sm font-medium rounded-md">
                            <i class="fas fa-heart mr-2"></i>Favorite
                        </button>
                    </div>
                </div>

                <!-- Status Actions -->
                <div>
                    <h3 class="text-lg font-medium text-gray-800 mb-3">Status Actions</h3>
                    <div class="flex space-x-2">
                        <button class="action-btn inline-flex items-center px-3 py-2 bg-green-100 hover:bg-green-200 text-green-700 text-sm font-medium rounded-md">
                            <i class="fas fa-check mr-2"></i>Approve
                        </button>
                        <button class="action-btn inline-flex items-center px-3 py-2 bg-yellow-100 hover:bg-yellow-200 text-yellow-700 text-sm font-medium rounded-md">
                            <i class="fas fa-pause mr-2"></i>Suspend
                        </button>
                        <button class="action-btn inline-flex items-center px-3 py-2 bg-red-100 hover:bg-red-200 text-red-700 text-sm font-medium rounded-md">
                            <i class="fas fa-ban mr-2"></i>Reject
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Interactive Demo -->
        <div class="bg-white rounded-lg shadow-md">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-xl font-semibold text-gray-800">Interactive Demo</h2>
                <p class="text-sm text-gray-600">Click the delete button to see the enhanced modal in action</p>
            </div>
            <div class="p-6">
                <div class="bg-gray-50 rounded-lg p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-medium text-gray-800">Sample Medicine Record</h3>
                            <p class="text-sm text-gray-600">Amoxicillin 250mg - Antibiotics</p>
                        </div>
                        <div class="flex space-x-2">
                            <button onclick="showToast('View action clicked!', 'success')" 
                                    class="action-btn inline-flex items-center px-3 py-2 bg-blue-100 hover:bg-blue-200 text-blue-700 text-sm font-medium rounded-md">
                                <i class="fas fa-eye mr-2"></i>View
                            </button>
                            <button onclick="showToast('Edit action clicked!', 'success')" 
                                    class="action-btn inline-flex items-center px-3 py-2 bg-green-100 hover:bg-green-200 text-green-700 text-sm font-medium rounded-md">
                                <i class="fas fa-edit mr-2"></i>Edit
                            </button>
                            <button onclick="deleteMedicine(999, 'Sample Medicine')" 
                                    class="action-btn inline-flex items-center px-3 py-2 bg-red-100 hover:bg-red-200 text-red-700 text-sm font-medium rounded-md">
                                <i class="fas fa-trash mr-2"></i>Delete
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php echo renderDeleteModal('Medicine'); ?>
    <?php echo renderToast(); ?>
    
    <script>
        let currentRecordId = null;

        function deleteMedicine(id, name) {
            currentRecordId = id;
            document.getElementById('recordName').textContent = name;
            document.getElementById('deleteModal').classList.remove('hidden');
            document.getElementById('deleteModal').classList.add('flex');
        }

        function closeDeleteModal() {
            document.getElementById('deleteModal').classList.add('hidden');
            document.getElementById('deleteModal').classList.remove('flex');
            currentRecordId = null;
        }

        function confirmDelete() {
            if (!currentRecordId) return;

            // Show loading state
            const deleteBtn = document.querySelector('#deleteModal button[onclick="confirmDelete()"]');
            const originalText = deleteBtn.innerHTML;
            deleteBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i>Deleting...';
            deleteBtn.disabled = true;

            // Simulate API call
            setTimeout(() => {
                closeDeleteModal();
                showToast('Demo: Medicine would be deleted successfully!', 'success');
                
                // Reset button state
                deleteBtn.innerHTML = originalText;
                deleteBtn.disabled = false;
            }, 2000);
        }

        function showToast(message, type) {
            const toast = document.getElementById('toast');
            const toastIcon = document.getElementById('toastIcon');
            const toastMessage = document.getElementById('toastMessage');

            // Set icon and colors based on type
            if (type === 'success') {
                toastIcon.innerHTML = '<i class="fas fa-check-circle text-green-600 text-lg"></i>';
                toastMessage.className = 'text-sm font-medium text-green-800';
            } else {
                toastIcon.innerHTML = '<i class="fas fa-exclamation-circle text-red-600 text-lg"></i>';
                toastMessage.className = 'text-sm font-medium text-red-800';
            }

            toastMessage.textContent = message;
            toast.classList.remove('hidden');

            // Auto hide after 5 seconds
            setTimeout(hideToast, 5000);
        }

        function hideToast() {
            document.getElementById('toast').classList.add('hidden');
        }

        // Close modal when clicking outside
        document.getElementById('deleteModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeDeleteModal();
            }
        });

        // Close modal with Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeDeleteModal();
            }
        });
    </script>
</body>
</html>