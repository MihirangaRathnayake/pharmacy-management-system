<?php
require_once dirname(dirname(__DIR__)) . '/bootstrap.php';

// Check authentication
if (!isLoggedIn()) {
    header('Location: ../../auth/login.php');
    exit();
}

$user = getCurrentUser();

// Get analytics data
try {
    // Sales Analytics
    $todaySales = $pdo->query("SELECT COALESCE(SUM(total_amount), 0) as total FROM sales WHERE DATE(sale_date) = CURDATE()")->fetch()['total'];
    $monthSales = $pdo->query("SELECT COALESCE(SUM(total_amount), 0) as total FROM sales WHERE MONTH(sale_date) = MONTH(CURDATE()) AND YEAR(sale_date) = YEAR(CURDATE())")->fetch()['total'];
    $yearSales = $pdo->query("SELECT COALESCE(SUM(total_amount), 0) as total FROM sales WHERE YEAR(sale_date) = YEAR(CURDATE())")->fetch()['total'];
    
    // Customer Analytics
    $totalCustomers = $pdo->query("SELECT COUNT(*) as count FROM customers WHERE status = 'active'")->fetch()['count'];
    $newCustomersThisMonth = $pdo->query("SELECT COUNT(*) as count FROM customers WHERE MONTH(created_at) = MONTH(CURDATE()) AND YEAR(created_at) = YEAR(CURDATE())")->fetch()['count'];
    
    // Inventory Analytics
    $totalMedicines = $pdo->query("SELECT COUNT(*) as count FROM medicines WHERE status = 'active'")->fetch()['count'];
    $lowStockItems = $pdo->query("SELECT COUNT(*) as count FROM medicines WHERE stock_quantity <= min_stock_level AND status = 'active'")->fetch()['count'];
    $expiringSoon = $pdo->query("SELECT COUNT(*) as count FROM medicines WHERE expiry_date <= DATE_ADD(CURDATE(), INTERVAL 30 DAY) AND expiry_date > CURDATE() AND status = 'active'")->fetch()['count'];
    
    // Monthly Sales Data for Chart
    $monthlySalesData = $pdo->query("
        SELECT 
            MONTH(sale_date) as month,
            MONTHNAME(sale_date) as month_name,
            SUM(total_amount) as total
        FROM sales 
        WHERE YEAR(sale_date) = YEAR(CURDATE())
        GROUP BY MONTH(sale_date), MONTHNAME(sale_date)
        ORDER BY MONTH(sale_date)
    ")->fetchAll();
    
    // Top Selling Medicines
    $topMedicines = $pdo->query("
        SELECT 
            m.name,
            SUM(si.quantity) as total_sold,
            SUM(si.total_price) as revenue
        FROM sale_items si
        JOIN medicines m ON si.medicine_id = m.id
        JOIN sales s ON si.sale_id = s.id
        WHERE MONTH(s.sale_date) = MONTH(CURDATE()) AND YEAR(s.sale_date) = YEAR(CURDATE())
        GROUP BY m.id, m.name
        ORDER BY total_sold DESC
        LIMIT 10
    ")->fetchAll();
    
    // Category-wise Sales
    $categorySales = $pdo->query("
        SELECT 
            c.name as category,
            SUM(si.total_price) as revenue,
            COUNT(si.id) as items_sold
        FROM sale_items si
        JOIN medicines m ON si.medicine_id = m.id
        JOIN categories c ON m.category_id = c.id
        JOIN sales s ON si.sale_id = s.id
        WHERE MONTH(s.sale_date) = MONTH(CURDATE()) AND YEAR(s.sale_date) = YEAR(CURDATE())
        GROUP BY c.id, c.name
        ORDER BY revenue DESC
    ")->fetchAll();
    
    // Daily Sales for Current Month
    $dailySales = $pdo->query("
        SELECT 
            DAY(sale_date) as day,
            SUM(total_amount) as total
        FROM sales 
        WHERE MONTH(sale_date) = MONTH(CURDATE()) AND YEAR(sale_date) = YEAR(CURDATE())
        GROUP BY DAY(sale_date)
        ORDER BY DAY(sale_date)
    ")->fetchAll();
    
    // Payment Method Distribution
    $paymentMethods = $pdo->query("
        SELECT 
            payment_method,
            COUNT(*) as count,
            SUM(total_amount) as total
        FROM sales 
        WHERE MONTH(sale_date) = MONTH(CURDATE()) AND YEAR(sale_date) = YEAR(CURDATE())
        GROUP BY payment_method
    ")->fetchAll();
    
} catch (Exception $e) {
    $error = $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en" data-theme="<?php echo getThemeClass(); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Analytics & Reports - Pharmacy Management</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <?php echo getThemeCSS(); ?>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/date-fns@2.29.3/index.min.js"></script>
    <style>
        body { font-family: 'Inter', sans-serif; }
        
        /* Ensure theme variables are available */
        :root {
            --bg-primary: #ffffff;
            --bg-secondary: #f9fafb;
            --bg-tertiary: #f3f4f6;
            --text-primary: #111827;
            --text-secondary: #374151;
            --text-muted: #6b7280;
            --border-color: #e5e7eb;
            --shadow: rgba(0, 0, 0, 0.1);
        }
        
        [data-theme="dark"] {
            --bg-primary: #111827;
            --bg-secondary: #1f2937;
            --bg-tertiary: #374151;
            --text-primary: #f9fafb;
            --text-secondary: #d1d5db;
            --text-muted: #9ca3af;
            --border-color: #374151;
            --shadow: rgba(0, 0, 0, 0.3);
        }
        
        .stat-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 12px;
            padding: 20px;
            color: white;
            position: relative;
            overflow: hidden;
            transition: all 0.3s ease;
        }
        
        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 100px;
            height: 100px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            transform: translate(30px, -30px);
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
        }
        
        .chart-container {
            background: var(--bg-primary);
            border-radius: 12px;
            padding: 16px;
            box-shadow: 0 2px 4px -1px var(--shadow);
            border: 1px solid var(--border-color);
            transition: all 0.3s ease;
        }
        
        .chart-container:hover {
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
        }
        
        .gradient-bg-1 { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
        .gradient-bg-2 { background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); }
        .gradient-bg-3 { background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); }
        .gradient-bg-4 { background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%); }
        .gradient-bg-5 { background: linear-gradient(135deg, #fa709a 0%, #fee140 100%); }
        .gradient-bg-6 { background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%); }
        
        .animate-counter {
            animation: countUp 2s ease-out;
        }
        
        @keyframes countUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .filter-tabs {
            display: flex;
            background: var(--bg-tertiary);
            border-radius: 12px;
            padding: 4px;
            margin-bottom: 24px;
        }
        
        .filter-tab {
            flex: 1;
            padding: 12px 24px;
            text-align: center;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
            font-weight: 500;
        }
        
        .filter-tab.active {
            background: var(--bg-primary);
            color: #667eea;
            box-shadow: 0 2px 4px var(--shadow);
        }
        
        .metric-item {
            display: flex;
            align-items: center;
            padding: 16px;
            background: var(--bg-tertiary);
            border-radius: 12px;
            margin-bottom: 12px;
            transition: all 0.3s ease;
        }
        
        .metric-item:hover {
            background: var(--bg-secondary);
            transform: translateX(4px);
        }
        
        .loading-spinner {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid #f3f3f3;
            border-top: 3px solid #667eea;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
    <?php renderThemeScript(); ?>
</head>
<body class="bg-gray-50 min-h-screen">
    <?php include '../../includes/navbar.php'; ?>
    
    <div class="container mx-auto px-4 py-8">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h1 class="text-3xl font-bold text-gray-800 mb-2">
                        <i class="fas fa-chart-line text-blue-600 mr-3"></i>
                        Analytics & Reports
                    </h1>
                    <p class="text-gray-600">Comprehensive insights into your pharmacy operations</p>
                </div>
                
                <div class="flex space-x-4">
                    <button onclick="exportReport()" class="px-6 py-3 bg-green-600 hover:bg-green-700 text-white rounded-lg font-medium transition-colors">
                        <i class="fas fa-download mr-2"></i>Export Report
                    </button>
                    <button onclick="refreshData()" class="px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium transition-colors">
                        <i class="fas fa-sync-alt mr-2"></i>Refresh
                    </button>
                </div>
            </div>
            
            <!-- Time Filter Tabs -->
            <div class="filter-tabs">
                <div class="filter-tab active" onclick="changeTimeFilter('today')">Today</div>
                <div class="filter-tab" onclick="changeTimeFilter('week')">This Week</div>
                <div class="filter-tab" onclick="changeTimeFilter('month')">This Month</div>
                <div class="filter-tab" onclick="changeTimeFilter('year')">This Year</div>
            </div>
        </div>

        <!-- Key Metrics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="stat-card gradient-bg-1">
                <div class="flex items-center justify-between relative z-10">
                    <div>
                        <p class="text-white/80 text-sm font-medium">Today's Sales</p>
                        <p class="text-3xl font-bold animate-counter">Rs <?php echo number_format($todaySales, 2); ?></p>
                        <p class="text-white/60 text-xs mt-1">
                            <i class="fas fa-arrow-up mr-1"></i>+12% from yesterday
                        </p>
                    </div>
                    <div class="text-4xl text-white/30">
                        <i class="fas fa-rupee-sign"></i>
                    </div>
                </div>
            </div>
            
            <div class="stat-card gradient-bg-2">
                <div class="flex items-center justify-between relative z-10">
                    <div>
                        <p class="text-white/80 text-sm font-medium">Total Customers</p>
                        <p class="text-3xl font-bold animate-counter"><?php echo number_format($totalCustomers); ?></p>
                        <p class="text-white/60 text-xs mt-1">
                            <i class="fas fa-arrow-up mr-1"></i>+<?php echo $newCustomersThisMonth; ?> this month
                        </p>
                    </div>
                    <div class="text-4xl text-white/30">
                        <i class="fas fa-users"></i>
                    </div>
                </div>
            </div>
            
            <div class="stat-card gradient-bg-3">
                <div class="flex items-center justify-between relative z-10">
                    <div>
                        <p class="text-white/80 text-sm font-medium">Total Medicines</p>
                        <p class="text-3xl font-bold animate-counter"><?php echo number_format($totalMedicines); ?></p>
                        <p class="text-white/60 text-xs mt-1">
                            <i class="fas fa-exclamation-triangle mr-1"></i><?php echo $lowStockItems; ?> low stock
                        </p>
                    </div>
                    <div class="text-4xl text-white/30">
                        <i class="fas fa-pills"></i>
                    </div>
                </div>
            </div>
            
            <div class="stat-card gradient-bg-4">
                <div class="flex items-center justify-between relative z-10">
                    <div>
                        <p class="text-white/80 text-sm font-medium">Monthly Revenue</p>
                        <p class="text-3xl font-bold animate-counter">Rs <?php echo number_format($monthSales, 2); ?></p>
                        <p class="text-white/60 text-xs mt-1">
                            <i class="fas fa-calendar mr-1"></i><?php echo date('F Y'); ?>
                        </p>
                    </div>
                    <div class="text-4xl text-white/30">
                        <i class="fas fa-chart-line"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts Grid - Compact Layout -->
        <div class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-4 gap-4 mb-6">
            <!-- Monthly Sales Chart -->
            <div class="chart-container xl:col-span-2">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-lg font-semibold text-gray-800">
                        <i class="fas fa-chart-bar text-blue-600 mr-2"></i>
                        Monthly Sales
                    </h3>
                    <div class="loading-spinner" id="salesChartLoader"></div>
                </div>
                <div style="height: 200px;">
                    <canvas id="monthlySalesChart"></canvas>
                </div>
            </div>
            
            <!-- Category Distribution -->
            <div class="chart-container">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-lg font-semibold text-gray-800">
                        <i class="fas fa-chart-pie text-green-600 mr-2"></i>
                        Categories
                    </h3>
                    <div class="loading-spinner" id="categoryChartLoader"></div>
                </div>
                <div style="height: 200px;">
                    <canvas id="categoryChart"></canvas>
                </div>
            </div>
            
            <!-- Payment Methods -->
            <div class="chart-container">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-lg font-semibold text-gray-800">
                        <i class="fas fa-credit-card text-orange-600 mr-2"></i>
                        Payments
                    </h3>
                    <div class="loading-spinner" id="paymentChartLoader"></div>
                </div>
                <div style="height: 200px;">
                    <canvas id="paymentChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Daily Sales - Full Width Compact -->
        <div class="chart-container mb-6">
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-lg font-semibold text-gray-800">
                    <i class="fas fa-chart-area text-purple-600 mr-2"></i>
                    Daily Sales Trend (Current Month)
                </h3>
                <div class="loading-spinner" id="dailyChartLoader"></div>
            </div>
            <div style="height: 180px;">
                <canvas id="dailySalesChart"></canvas>
            </div>
        </div>

        <!-- Compact Data Section -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-6">
            <!-- Top Selling Medicines - Compact -->
            <div class="chart-container lg:col-span-2">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">
                    <i class="fas fa-trophy text-yellow-600 mr-2"></i>
                    Top Selling Medicines
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-2 max-h-64 overflow-y-auto">
                    <?php foreach (array_slice($topMedicines, 0, 6) as $index => $medicine): ?>
                        <div class="flex items-center justify-between bg-gray-50 rounded-lg p-3 hover:bg-gray-100 transition-colors">
                            <div class="flex items-center">
                                <div class="w-6 h-6 bg-gradient-to-r from-blue-500 to-purple-600 rounded-full flex items-center justify-center text-white font-bold text-xs mr-2">
                                    <?php echo $index + 1; ?>
                                </div>
                                <div>
                                    <p class="font-medium text-gray-800 text-sm"><?php echo htmlspecialchars(substr($medicine['name'], 0, 20)) . (strlen($medicine['name']) > 20 ? '...' : ''); ?></p>
                                    <p class="text-xs text-gray-600"><?php echo $medicine['total_sold']; ?> units</p>
                                </div>
                            </div>
                            <p class="font-bold text-green-600 text-sm">Rs <?php echo number_format($medicine['revenue'], 0); ?></p>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <!-- Inventory Alerts - Compact -->
            <div class="chart-container">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">
                    <i class="fas fa-exclamation-triangle text-red-600 mr-2"></i>
                    Alerts
                </h3>
                
                <div class="space-y-3">
                    <div class="bg-red-50 border border-red-200 rounded-lg p-3">
                        <div class="flex items-center">
                            <i class="fas fa-exclamation-circle text-red-600 mr-2 text-sm"></i>
                            <div>
                                <p class="font-medium text-red-800 text-sm">Low Stock</p>
                                <p class="text-xs text-red-600"><?php echo $lowStockItems; ?> items</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-3">
                        <div class="flex items-center">
                            <i class="fas fa-clock text-yellow-600 mr-2 text-sm"></i>
                            <div>
                                <p class="font-medium text-yellow-800 text-sm">Expiring</p>
                                <p class="text-xs text-yellow-600"><?php echo $expiringSoon; ?> items</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-green-50 border border-green-200 rounded-lg p-3">
                        <div class="flex items-center">
                            <i class="fas fa-check-circle text-green-600 mr-2 text-sm"></i>
                            <div>
                                <p class="font-medium text-green-800 text-sm">Healthy</p>
                                <p class="text-xs text-green-600"><?php echo $totalMedicines - $lowStockItems; ?> items</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Additional Metrics - Compact -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="stat-card gradient-bg-5" style="padding: 16px;">
                <div class="flex items-center justify-between relative z-10">
                    <div>
                        <p class="text-white/80 text-xs font-medium">Avg. Order Value</p>
                        <p class="text-xl font-bold">Rs <?php echo number_format($monthSales / max($totalCustomers, 1), 0); ?></p>
                    </div>
                    <i class="fas fa-shopping-cart text-2xl text-white/30"></i>
                </div>
            </div>
            
            <div class="stat-card gradient-bg-6" style="padding: 16px;">
                <div class="flex items-center justify-between relative z-10">
                    <div>
                        <p class="text-white/80 text-xs font-medium">Customer Growth</p>
                        <p class="text-xl font-bold">+<?php echo number_format(($newCustomersThisMonth / max($totalCustomers - $newCustomersThisMonth, 1)) * 100, 1); ?>%</p>
                    </div>
                    <i class="fas fa-user-plus text-2xl text-white/30"></i>
                </div>
            </div>
            
            <div class="stat-card gradient-bg-1" style="padding: 16px;">
                <div class="flex items-center justify-between relative z-10">
                    <div>
                        <p class="text-white/80 text-xs font-medium">Yearly Revenue</p>
                        <p class="text-xl font-bold">Rs <?php echo number_format($yearSales, 0); ?></p>
                    </div>
                    <i class="fas fa-calendar-alt text-2xl text-white/30"></i>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Chart configurations and data
        const chartColors = {
            primary: '#667eea',
            secondary: '#764ba2',
            success: '#10b981',
            warning: '#f59e0b',
            danger: '#ef4444',
            info: '#3b82f6'
        };

        // Monthly Sales Chart
        const monthlySalesData = <?php echo json_encode($monthlySalesData); ?>;
        const monthlySalesChart = new Chart(document.getElementById('monthlySalesChart'), {
            type: 'line',
            data: {
                labels: monthlySalesData.map(item => (item.month_name || 'N/A').substring(0, 3)),
                datasets: [{
                    label: 'Sales (Rs)',
                    data: monthlySalesData.map(item => item.total || 0),
                    borderColor: chartColors.primary,
                    backgroundColor: chartColors.primary + '20',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: chartColors.primary,
                    pointBorderColor: '#fff',
                    pointBorderWidth: 1,
                    pointRadius: 3
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: '#f1f5f9'
                        },
                        ticks: {
                            font: {
                                size: 10
                            },
                            callback: function(value) {
                                return 'Rs ' + (value/1000).toFixed(0) + 'K';
                            }
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            font: {
                                size: 10
                            }
                        }
                    }
                }
            }
        });

        // Category Sales Pie Chart
        const categorySalesData = <?php echo json_encode($categorySales); ?>;
        const categoryChart = new Chart(document.getElementById('categoryChart'), {
            type: 'doughnut',
            data: {
                labels: categorySalesData.map(item => (item.category || 'Unknown').substring(0, 8)),
                datasets: [{
                    data: categorySalesData.map(item => item.revenue || 0),
                    backgroundColor: [
                        '#667eea', '#764ba2', '#10b981', '#f59e0b', '#ef4444', 
                        '#3b82f6', '#8b5cf6', '#06b6d4', '#84cc16', '#f97316'
                    ],
                    borderWidth: 0,
                    hoverOffset: 5
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 8,
                            usePointStyle: true,
                            font: {
                                size: 10
                            }
                        }
                    }
                }
            }
        });

        // Daily Sales Chart
        const dailySalesData = <?php echo json_encode($dailySales); ?>;
        const dailySalesChart = new Chart(document.getElementById('dailySalesChart'), {
            type: 'bar',
            data: {
                labels: dailySalesData.map(item => item.day || 0),
                datasets: [{
                    label: 'Daily Sales',
                    data: dailySalesData.map(item => item.total || 0),
                    backgroundColor: chartColors.success + '80',
                    borderColor: chartColors.success,
                    borderWidth: 1,
                    borderRadius: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: '#f1f5f9'
                        },
                        ticks: {
                            font: {
                                size: 10
                            }
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            font: {
                                size: 10
                            }
                        }
                    }
                }
            }
        });

        // Payment Methods Chart
        const paymentMethodsData = <?php echo json_encode($paymentMethods); ?>;
        const paymentChart = new Chart(document.getElementById('paymentChart'), {
            type: 'doughnut',
            data: {
                labels: paymentMethodsData.map(item => (item.payment_method?.toUpperCase() || 'Unknown').substring(0, 4)),
                datasets: [{
                    data: paymentMethodsData.map(item => item.total || 0),
                    backgroundColor: [
                        chartColors.primary,
                        chartColors.success,
                        chartColors.warning,
                        chartColors.danger
                    ],
                    borderWidth: 0,
                    hoverOffset: 5
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 8,
                            usePointStyle: true,
                            font: {
                                size: 10
                            }
                        }
                    }
                }
            }
        });

        // Hide loading spinners
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(() => {
                document.querySelectorAll('.loading-spinner').forEach(spinner => {
                    spinner.style.display = 'none';
                });
            }, 1000);
        });

        // Filter functions
        function changeTimeFilter(period) {
            // Update active tab
            document.querySelectorAll('.filter-tab').forEach(tab => {
                tab.classList.remove('active');
            });
            event.target.classList.add('active');
            
            // Here you would typically reload data based on the selected period
            console.log('Filter changed to:', period);
        }

        function refreshData() {
            location.reload();
        }

        function exportReport() {
            // Create a simple report export
            const reportData = {
                generated: new Date().toISOString(),
                todaySales: <?php echo $todaySales; ?>,
                monthSales: <?php echo $monthSales; ?>,
                totalCustomers: <?php echo $totalCustomers; ?>,
                totalMedicines: <?php echo $totalMedicines; ?>,
                lowStockItems: <?php echo $lowStockItems; ?>
            };
            
            const dataStr = JSON.stringify(reportData, null, 2);
            const dataBlob = new Blob([dataStr], {type: 'application/json'});
            const url = URL.createObjectURL(dataBlob);
            const link = document.createElement('a');
            link.href = url;
            link.download = 'pharmacy-report-' + new Date().toISOString().split('T')[0] + '.json';
            link.click();
        }

        // Animate counters on scroll
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('animate-counter');
                }
            });
        }, observerOptions);

        document.querySelectorAll('.stat-card').forEach(card => {
            observer.observe(card);
        });
    </script>
</body>
</html>