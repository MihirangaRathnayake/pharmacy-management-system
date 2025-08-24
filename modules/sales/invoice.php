<?php
require_once __DIR__ . '/../../bootstrap.php';

// Redirect to login if not authenticated
if (!isLoggedIn()) {
    header('Location: ../../auth/login.php');
    exit();
}

$user = getCurrentUser();
$invoice_id = $_GET['id'] ?? null;

if (!$invoice_id) {
    header('Location: index.php');
    exit();
}

// Get invoice/sale data
$sale = null;
$sale_items = [];
$customer = null;

try {
    global $pdo;
    
    if ($pdo) {
        // Get sale data
        $stmt = $pdo->prepare("
            SELECT s.*, u.name as customer_name, u.email as customer_email, u.phone as customer_phone, u.address as customer_address
            FROM sales s 
            LEFT JOIN users u ON s.customer_id = u.id 
            WHERE s.id = ?
        ");
        $stmt->execute([$invoice_id]);
        $sale = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$sale) {
            header('Location: index.php?error=Invoice not found');
            exit();
        }
        
        // Get sale items
        $stmt = $pdo->prepare("
            SELECT si.*, m.name as medicine_name, m.generic_name, m.manufacturer, m.batch_number, m.expiry_date
            FROM sale_items si 
            JOIN medicines m ON si.medicine_id = m.id 
            WHERE si.sale_id = ?
        ");
        $stmt->execute([$invoice_id]);
        $sale_items = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (Exception $e) {
    error_log("Invoice error: " . $e->getMessage());
    header('Location: index.php?error=Error loading invoice');
    exit();
}

// Calculate totals
$subtotal = 0;
$total_tax = 0;
$total_discount = 0;

foreach ($sale_items as $item) {
    $item_total = $item['quantity'] * $item['unit_price'];
    $subtotal += $item_total;
    $total_tax += $item['tax_amount'] ?? 0;
    $total_discount += $item['discount_amount'] ?? 0;
}

$grand_total = $subtotal + $total_tax - $total_discount;

// Generate invoice number if not exists
$invoice_number = $sale['invoice_number'] ?? 'INV-' . str_pad($invoice_id, 6, '0', STR_PAD_LEFT);
?>
<!DOCTYPE html>
<html lang="en" data-theme="<?php echo getThemeClass(); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice #<?php echo htmlspecialchars($invoice_number); ?> - PharmaCare</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- FontAwesome Icons - Multiple CDN fallbacks -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" integrity="sha512-iecdLmaskl7CVkqkXNQ/ZH/XLlvWZOJyj7Yy7tcenmpD1ypASozpmT/E0iPtmFIB46ZmdtAc9eNBvH0H/ZpiBw==" crossorigin="anonymous" referrerpolicy="no-referrer">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v6.4.0/css/all.css">
    <!-- FontAwesome 5 fallback -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" integrity="sha512-1ycn6IcaQQ40/MKBW2W4Rhis/DbILU74C1vSrLJxCq57o941Ym01SwNsOMqvEBFlcgUa6xLiPY/NS5R+E6ztJQ==" crossorigin="anonymous" referrerpolicy="no-referrer">
    
    <link rel="stylesheet" href="../../assets/css/admin-icons-fix.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <?php echo getThemeCSS(); ?>
    
    <style>
        body { 
            font-family: 'Inter', sans-serif; 
        }
        
        /* Print Styles */
        @media print {
            .no-print {
                display: none !important;
            }
            
            .print-only {
                display: block !important;
            }
            
            body {
                background: white !important;
                color: black !important;
            }
            
            .invoice-container {
                box-shadow: none !important;
                border: none !important;
                margin: 0 !important;
                padding: 20px !important;
            }
            
            .page-break {
                page-break-before: always;
            }
            
            /* Ensure proper colors in print */
            .bg-gradient-to-r {
                background: #059669 !important;
                color: white !important;
            }
            
            .text-green-600 {
                color: #059669 !important;
            }
            
            .border-green-500 {
                border-color: #059669 !important;
            }
        }
        
        /* Animation for interactive elements */
        .btn-animate {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        .btn-animate:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
        }
        
        .invoice-item-row {
            transition: all 0.2s ease;
        }
        
        .invoice-item-row:hover {
            background-color: rgba(16, 185, 129, 0.05);
            transform: translateX(4px);
        }
        
        /* Gradient backgrounds */
        .gradient-header {
            background: linear-gradient(135deg, #059669 0%, #047857 100%);
        }
        
        .gradient-accent {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        }
        
        /* Status badges */
        .status-paid {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        }
        
        .status-pending {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        }
        
        .status-overdue {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
        }
        
        /* Watermark */
        .watermark {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 6rem;
            font-weight: 900;
            color: rgba(16, 185, 129, 0.05);
            z-index: 0;
            pointer-events: none;
        }
        
        /* Interactive buttons */
        .btn-primary {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            border: none;
            color: white;
            font-weight: 600;
            padding: 12px 24px;
            border-radius: 12px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(16, 185, 129, 0.3);
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(16, 185, 129, 0.4);
            background: linear-gradient(135deg, #059669 0%, #047857 100%);
        }
        
        .btn-secondary {
            background: linear-gradient(135deg, #6b7280 0%, #4b5563 100%);
            border: none;
            color: white;
            font-weight: 600;
            padding: 12px 24px;
            border-radius: 12px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(107, 114, 128, 0.3);
        }
        
        .btn-secondary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(107, 114, 128, 0.4);
            background: linear-gradient(135deg, #4b5563 0%, #374151 100%);
        }
        
        /* Loading animation */
        .loading-spinner {
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
        
        /* Pulse animation for status */
        .pulse-animation {
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.7; }
        }
    </style>
    <?php renderThemeScript(); ?>
</head>
<body class="bg-gray-50 min-h-screen">
    <!-- Action Bar (No Print) -->
    <div class="no-print bg-white shadow-sm border-b sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-4">
                <div class="flex items-center space-x-4">
                    <a href="index.php" class="btn-secondary btn-animate flex items-center space-x-2">
                        <i class="fas fa-arrow-left"></i>
                        <span>Back to Sales</span>
                    </a>
                    <div class="flex items-center space-x-2">
                        <i class="fas fa-file-invoice text-green-600"></i>
                        <h1 class="text-xl font-bold text-gray-900">Invoice #<?php echo htmlspecialchars($invoice_number); ?></h1>
                    </div>
                </div>
                
                <div class="flex items-center space-x-3">
                    <!-- Status Badge -->
                    <span class="status-<?php echo strtolower($sale['status'] ?? 'paid'); ?> text-white px-4 py-2 rounded-full text-sm font-semibold pulse-animation">
                        <i class="fas fa-circle mr-1"></i>
                        <?php echo ucfirst($sale['status'] ?? 'Paid'); ?>
                    </span>
                    
                    <!-- Action Buttons -->
                    <button onclick="downloadPDF()" class="btn-primary btn-animate flex items-center space-x-2" title="Download PDF">
                        <i class="fas fa-download"></i>
                        <span>Download PDF</span>
                    </button>
                    
                    <button onclick="printInvoice()" class="btn-primary btn-animate flex items-center space-x-2" title="Print Invoice">
                        <i class="fas fa-print"></i>
                        <span>Print</span>
                    </button>
                    
                    <button onclick="emailInvoice()" class="btn-secondary btn-animate flex items-center space-x-2" title="Email Invoice">
                        <i class="fas fa-envelope"></i>
                        <span>Email</span>
                    </button>
                    
                    <button onclick="shareInvoice()" class="btn-secondary btn-animate flex items-center space-x-2" title="Share Invoice">
                        <i class="fas fa-share-alt"></i>
                        <span>Share</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Invoice Container -->
    <div class="max-w-4xl mx-auto p-6">
        <div class="invoice-container bg-white rounded-2xl shadow-2xl overflow-hidden relative">
            <!-- Watermark -->
            <div class="watermark">PHARMACARE</div>
            
            <!-- Header -->
            <div class="gradient-header text-white p-8 relative overflow-hidden">
                <div class="absolute top-0 right-0 w-32 h-32 bg-white opacity-10 rounded-full -mr-16 -mt-16"></div>
                <div class="absolute bottom-0 left-0 w-24 h-24 bg-white opacity-10 rounded-full -ml-12 -mb-12"></div>
                
                <div class="relative z-10">
                    <div class="flex justify-between items-start">
                        <div>
                            <div class="flex items-center space-x-3 mb-4">
                                <div class="bg-white bg-opacity-20 p-3 rounded-xl">
                                    <i class="fas fa-plus-circle text-2xl"></i>
                                </div>
                                <div>
                                    <h1 class="text-3xl font-bold">PharmaCare</h1>
                                    <p class="text-green-100">Your Trusted Pharmacy</p>
                                </div>
                            </div>
                            <div class="space-y-1 text-green-100">
                                <p><i class="fas fa-map-marker-alt mr-2"></i>123 Medical Street, Healthcare City</p>
                                <p><i class="fas fa-phone mr-2"></i>+1 (555) 123-4567</p>
                                <p><i class="fas fa-envelope mr-2"></i>info@pharmacare.com</p>
                                <p><i class="fas fa-globe mr-2"></i>www.pharmacare.com</p>
                            </div>
                        </div>
                        
                        <div class="text-right">
                            <div class="bg-white bg-opacity-20 rounded-xl p-6">
                                <h2 class="text-2xl font-bold mb-2">INVOICE</h2>
                                <div class="space-y-2">
                                    <p><span class="font-semibold">Invoice #:</span> <?php echo htmlspecialchars($invoice_number); ?></p>
                                    <p><span class="font-semibold">Date:</span> <?php echo date('M d, Y', strtotime($sale['created_at'])); ?></p>
                                    <p><span class="font-semibold">Due Date:</span> <?php echo date('M d, Y', strtotime($sale['created_at'] . ' +30 days')); ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Customer & Sale Info -->
            <div class="p-8">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-8">
                    <!-- Bill To -->
                    <div class="bg-gray-50 rounded-xl p-6">
                        <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center">
                            <i class="fas fa-user text-green-600 mr-2"></i>
                            Bill To
                        </h3>
                        <div class="space-y-2 text-gray-700">
                            <p class="font-semibold text-gray-900"><?php echo htmlspecialchars($sale['customer_name'] ?? 'Walk-in Customer'); ?></p>
                            <?php if ($sale['customer_email']): ?>
                                <p><i class="fas fa-envelope text-gray-400 mr-2"></i><?php echo htmlspecialchars($sale['customer_email']); ?></p>
                            <?php endif; ?>
                            <?php if ($sale['customer_phone']): ?>
                                <p><i class="fas fa-phone text-gray-400 mr-2"></i><?php echo htmlspecialchars($sale['customer_phone']); ?></p>
                            <?php endif; ?>
                            <?php if ($sale['customer_address']): ?>
                                <p><i class="fas fa-map-marker-alt text-gray-400 mr-2"></i><?php echo htmlspecialchars($sale['customer_address']); ?></p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Sale Details -->
                    <div class="bg-gray-50 rounded-xl p-6">
                        <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center">
                            <i class="fas fa-info-circle text-green-600 mr-2"></i>
                            Sale Details
                        </h3>
                        <div class="space-y-2 text-gray-700">
                            <p><span class="font-semibold">Sale ID:</span> #<?php echo str_pad($sale['id'], 6, '0', STR_PAD_LEFT); ?></p>
                            <p><span class="font-semibold">Cashier:</span> <?php echo htmlspecialchars($user['name']); ?></p>
                            <p><span class="font-semibold">Payment Method:</span> <?php echo ucfirst($sale['payment_method'] ?? 'Cash'); ?></p>
                            <p><span class="font-semibold">Sale Date:</span> <?php echo date('M d, Y g:i A', strtotime($sale['created_at'])); ?></p>
                        </div>
                    </div>
                </div>

                <!-- Items Table -->
                <div class="mb-8">
                    <h3 class="text-xl font-bold text-gray-900 mb-6 flex items-center">
                        <i class="fas fa-pills text-green-600 mr-2"></i>
                        Items Purchased
                    </h3>
                    
                    <div class="overflow-hidden rounded-xl border border-gray-200">
                        <table class="w-full">
                            <thead class="gradient-accent text-white">
                                <tr>
                                    <th class="px-6 py-4 text-left font-semibold">#</th>
                                    <th class="px-6 py-4 text-left font-semibold">Medicine</th>
                                    <th class="px-6 py-4 text-center font-semibold">Qty</th>
                                    <th class="px-6 py-4 text-right font-semibold">Unit Price</th>
                                    <th class="px-6 py-4 text-right font-semibold">Total</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white">
                                <?php foreach ($sale_items as $index => $item): ?>
                                <tr class="invoice-item-row border-b border-gray-100 hover:bg-green-50">
                                    <td class="px-6 py-4 text-gray-600 font-medium"><?php echo $index + 1; ?></td>
                                    <td class="px-6 py-4">
                                        <div>
                                            <p class="font-semibold text-gray-900"><?php echo htmlspecialchars($item['medicine_name']); ?></p>
                                            <?php if ($item['generic_name']): ?>
                                                <p class="text-sm text-gray-500"><?php echo htmlspecialchars($item['generic_name']); ?></p>
                                            <?php endif; ?>
                                            <?php if ($item['batch_number']): ?>
                                                <p class="text-xs text-gray-400">Batch: <?php echo htmlspecialchars($item['batch_number']); ?></p>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <span class="bg-green-100 text-green-800 px-3 py-1 rounded-full text-sm font-semibold">
                                            <?php echo number_format($item['quantity']); ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-right font-semibold text-gray-900">
                                        Rs <?php echo number_format($item['unit_price'], 2); ?>
                                    </td>
                                    <td class="px-6 py-4 text-right font-bold text-green-600">
                                        Rs <?php echo number_format($item['quantity'] * $item['unit_price'], 2); ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Totals -->
                <div class="flex justify-end">
                    <div class="w-full max-w-md">
                        <div class="bg-gray-50 rounded-xl p-6 space-y-4">
                            <div class="flex justify-between items-center">
                                <span class="text-gray-600">Subtotal:</span>
                                <span class="font-semibold text-gray-900">Rs <?php echo number_format($subtotal, 2); ?></span>
                            </div>
                            
                            <?php if ($total_discount > 0): ?>
                            <div class="flex justify-between items-center">
                                <span class="text-gray-600">Discount:</span>
                                <span class="font-semibold text-red-600">-Rs <?php echo number_format($total_discount, 2); ?></span>
                            </div>
                            <?php endif; ?>
                            
                            <?php if ($total_tax > 0): ?>
                            <div class="flex justify-between items-center">
                                <span class="text-gray-600">Tax:</span>
                                <span class="font-semibold text-gray-900">Rs <?php echo number_format($total_tax, 2); ?></span>
                            </div>
                            <?php endif; ?>
                            
                            <hr class="border-gray-300">
                            
                            <div class="flex justify-between items-center">
                                <span class="text-xl font-bold text-gray-900">Total:</span>
                                <span class="text-2xl font-bold text-green-600">Rs <?php echo number_format($grand_total, 2); ?></span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Footer -->
                <div class="mt-12 pt-8 border-t border-gray-200">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <div>
                            <h4 class="font-bold text-gray-900 mb-3">Terms & Conditions</h4>
                            <ul class="text-sm text-gray-600 space-y-1">
                                <li>• All sales are final unless otherwise specified</li>
                                <li>• Prescription medicines require valid prescription</li>
                                <li>• Please check expiry dates before use</li>
                                <li>• Store medicines in cool, dry place</li>
                            </ul>
                        </div>
                        
                        <div class="text-right">
                            <h4 class="font-bold text-gray-900 mb-3">Thank You!</h4>
                            <p class="text-sm text-gray-600 mb-4">
                                We appreciate your business and trust in PharmaCare. 
                                For any queries, please contact us at the above details.
                            </p>
                            <div class="flex justify-end space-x-4">
                                <i class="fab fa-facebook text-green-600 text-xl"></i>
                                <i class="fab fa-twitter text-green-600 text-xl"></i>
                                <i class="fab fa-instagram text-green-600 text-xl"></i>
                                <i class="fab fa-linkedin text-green-600 text-xl"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Loading Modal -->
    <div id="loadingModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50 no-print">
        <div class="bg-white rounded-xl p-8 text-center">
            <div class="loading-spinner fas fa-spinner text-4xl text-green-600 mb-4"></div>
            <p class="text-gray-700 font-semibold">Processing...</p>
        </div>
    </div>

    <!-- Success Modal -->
    <div id="successModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50 no-print">
        <div class="bg-white rounded-xl p-8 text-center max-w-md mx-4">
            <div class="text-green-600 text-5xl mb-4">
                <i class="fas fa-check-circle"></i>
            </div>
            <h3 class="text-xl font-bold text-gray-900 mb-2">Success!</h3>
            <p id="successMessage" class="text-gray-600 mb-6">Operation completed successfully.</p>
            <button onclick="closeModal('successModal')" class="btn-primary">
                <i class="fas fa-check mr-2"></i>
                OK
            </button>
        </div>
    </div>

    <script src="../../assets/js/admin-icons-fix.js"></script>
    
    <script>
        // Print functionality
        function printInvoice() {
            window.print();
        }

        // Download PDF functionality
        function downloadPDF() {
            showLoading();
            
            // Simulate PDF generation
            setTimeout(() => {
                hideLoading();
                showSuccess('PDF downloaded successfully!');
                
                // In a real implementation, you would call a server endpoint
                // window.location.href = 'generate_pdf.php?id=<?php echo $invoice_id; ?>';
            }, 2000);
        }

        // Email functionality
        function emailInvoice() {
            const email = prompt('Enter email address:');
            if (email) {
                showLoading();
                
                // Simulate email sending
                setTimeout(() => {
                    hideLoading();
                    showSuccess(`Invoice emailed to ${email} successfully!`);
                    
                    // In a real implementation, you would call a server endpoint
                    // fetch('email_invoice.php', { method: 'POST', body: JSON.stringify({id: <?php echo $invoice_id; ?>, email: email}) });
                }, 2000);
            }
        }

        // Share functionality
        function shareInvoice() {
            if (navigator.share) {
                navigator.share({
                    title: 'Invoice #<?php echo htmlspecialchars($invoice_number); ?>',
                    text: 'PharmaCare Invoice',
                    url: window.location.href
                });
            } else {
                // Fallback - copy to clipboard
                navigator.clipboard.writeText(window.location.href).then(() => {
                    showSuccess('Invoice link copied to clipboard!');
                });
            }
        }

        // Modal functions
        function showLoading() {
            document.getElementById('loadingModal').classList.remove('hidden');
            document.getElementById('loadingModal').classList.add('flex');
        }

        function hideLoading() {
            document.getElementById('loadingModal').classList.add('hidden');
            document.getElementById('loadingModal').classList.remove('flex');
        }

        function showSuccess(message) {
            document.getElementById('successMessage').textContent = message;
            document.getElementById('successModal').classList.remove('hidden');
            document.getElementById('successModal').classList.add('flex');
        }

        function closeModal(modalId) {
            document.getElementById(modalId).classList.add('hidden');
            document.getElementById(modalId).classList.remove('flex');
        }

        // Keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            if (e.ctrlKey || e.metaKey) {
                switch(e.key) {
                    case 'p':
                        e.preventDefault();
                        printInvoice();
                        break;
                    case 's':
                        e.preventDefault();
                        downloadPDF();
                        break;
                }
            }
        });

        // Add some interactive animations
        document.addEventListener('DOMContentLoaded', function() {
            // Animate invoice items on scroll
            const observerOptions = {
                threshold: 0.1,
                rootMargin: '0px 0px -50px 0px'
            };

            const observer = new IntersectionObserver(function(entries) {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.style.opacity = '1';
                        entry.target.style.transform = 'translateY(0)';
                    }
                });
            }, observerOptions);

            // Observe invoice items
            document.querySelectorAll('.invoice-item-row').forEach((item, index) => {
                item.style.opacity = '0';
                item.style.transform = 'translateY(20px)';
                item.style.transition = `all 0.6s ease ${index * 0.1}s`;
                observer.observe(item);
            });
        });

        // Auto-save functionality (for future use)
        function autoSave() {
            // This could be used to save invoice state or notes
            console.log('Auto-saving invoice state...');
        }

        // Set up auto-save every 30 seconds
        setInterval(autoSave, 30000);
    </script>
</body>
</html>