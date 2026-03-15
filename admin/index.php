<?php
require_once __DIR__ . '/bootstrap.php';

// Check if database is set up
if (!$pdo) {
    header('Location: auth/login.php?message=database_error');
    exit();
}

// Create users table if needed
createUsersTable();

// Check if admin user exists
if (!hasAdminUser()) {
    header('Location: auth/login.php?message=no_admin');
    exit();
}

// Check if user is logged in
if (!isLoggedIn()) {
    header('Location: auth/login.php');
    exit();
}

// Get current user for admin dashboard
$user = getCurrentUser();

// Additional security check
if (!$user) {
    logoutUser();
    header('Location: auth/login.php?message=access_denied');
    exit();
}

// Fetch dashboard statistics from database
try {
    // Today's Sales
    $todaySales = $pdo->query("SELECT COALESCE(SUM(total_amount), 0) as total FROM sales WHERE DATE(sale_date) = CURDATE()")->fetch()['total'];

    // Low Stock Items
    $lowStockCount = $pdo->query("SELECT COUNT(*) as count FROM medicines WHERE stock_quantity <= min_stock_level AND status = 'active'")->fetch()['count'];

    // Pending Orders (prescriptions pending)
    $pendingOrders = $pdo->query("SELECT COUNT(*) as count FROM prescriptions WHERE status = 'pending'")->fetch()['count'];

    // Total Customers
    $totalCustomers = $pdo->query("SELECT COUNT(*) as count FROM customers WHERE status = 'active'")->fetch()['count'];

    // Recent Sales (last 5)
    $recentSales = $pdo->query("
        SELECT s.invoice_number, s.total_amount, s.sale_date, s.payment_method,
               COALESCE(c.name, 'Walk-in Customer') as customer_name
        FROM sales s
        LEFT JOIN customers c ON s.customer_id = c.id
        ORDER BY s.sale_date DESC
        LIMIT 5
    ")->fetchAll();

    // Stock Alerts (low stock medicines)
    $stockAlerts = $pdo->query("
        SELECT name, stock_quantity, min_stock_level
        FROM medicines
        WHERE stock_quantity <= min_stock_level AND status = 'active'
        ORDER BY stock_quantity ASC
        LIMIT 5
    ")->fetchAll();
} catch (Exception $e) {
    $todaySales = 0;
    $lowStockCount = 0;
    $pendingOrders = 0;
    $totalCustomers = 0;
    $recentSales = [];
    $stockAlerts = [];
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <title>Pharmacy Management System - Dashboard</title>
    <?php include 'includes/head.php'; ?>
    <link rel="stylesheet" href="assets/css/admin-icons-fix.css">
    <style>
        /* Minimal Light Background */
        body.pc-shell {
            position: relative;
            background: #fafafa;
            overflow-x: hidden;
        }

        .dashboard-animated-bg {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, #f0fdf4 0%, #e0f2fe 50%, #f5f3ff 100%);
            background-size: 200% 200%;
            animation: gradientShift 25s ease infinite;
            z-index: -2;
        }

        @keyframes gradientShift {

            0%,
            100% {
                background-position: 0% 50%;
            }

            50% {
                background-position: 100% 50%;
            }
        }

        /* Subtle Particles */
        .particle {
            position: absolute;
            background: rgba(16, 185, 129, 0.04);
            border-radius: 50%;
            pointer-events: none;
        }

        /* Enhanced Navbar Glassmorphism */
        nav {
            background: rgba(255, 255, 255, 0.95) !important;
            backdrop-filter: blur(16px) !important;
            border-bottom: 1px solid rgba(16, 185, 129, 0.12) !important;
            box-shadow: 0 2px 20px rgba(0, 0, 0, 0.04) !important;
            z-index: 1000 !important;
        }

        nav .pc-btn {
            border-radius: 0.75rem !important;
            transition: all 0.3s ease;
        }

        nav .pc-btn:hover {
            background: rgba(16, 185, 129, 0.08) !important;
            transform: translateY(-1px);
        }

        /* Theme & Notification Buttons */
        nav .pc-btn {
            color: #334155 !important;
        }

        nav .pc-btn i {
            color: #64748b !important;
        }

        nav .pc-btn:hover i {
            color: #10b981 !important;
        }

        /* Navbar Links Enhancement */
        nav .md\:flex.items-center.gap-2 a.pc-btn {
            color: #475569 !important;
            font-weight: 500;
        }

        nav .md\:flex.items-center.gap-2 a.pc-btn span {
            color: #475569 !important;
        }

        nav .md\:flex.items-center.gap-2 a.pc-btn:hover {
            color: #10b981 !important;
        }

        nav .md\:flex.items-center.gap-2 a.pc-btn:hover span {
            color: #10b981 !important;
        }

        nav .md\:flex.items-center.gap-2 a.pc-btn.ring-2 {
            background: rgba(16, 185, 129, 0.1) !important;
            color: #10b981 !important;
        }

        nav .md\:flex.items-center.gap-2 a.pc-btn.ring-2 span {
            color: #10b981 !important;
        }

        nav .pc-card {
            background: rgba(255, 255, 255, 0.98) !important;
            backdrop-filter: blur(12px);
            border: 1px solid rgba(16, 185, 129, 0.08) !important;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.12) !important;
            z-index: 1001 !important;
        }

        /* Notification Panel Text Colors */
        #notificationsDropdown .text-sm {
            color: #1e293b !important;
        }

        #notificationsDropdown .font-semibold {
            color: #0f172a !important;
        }

        #notificationsList>div {
            background: rgba(249, 250, 251, 0.8) !important;
            border-color: rgba(16, 185, 129, 0.15) !important;
            color: #334155 !important;
            transition: all 0.2s ease;
        }

        #notificationsList>div:hover {
            background: rgba(16, 185, 129, 0.05) !important;
            border-color: rgba(16, 185, 129, 0.25) !important;
            transform: translateX(4px);
        }

        /* User Profile Button Enhancement */
        nav .flex.items-center.gap-2>button {
            background: rgba(255, 255, 255, 0.9) !important;
            border-color: rgba(16, 185, 129, 0.15) !important;
            transition: all 0.3s ease;
        }

        nav .flex.items-center.gap-2>button:hover {
            background: rgba(16, 185, 129, 0.05) !important;
            border-color: rgba(16, 185, 129, 0.25) !important;
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.1) !important;
        }

        /* All possible selectors for user name text */
        nav .flex.items-center.gap-2>button span,
        nav .flex.items-center.gap-2>button span.hidden.lg\:inline,
        nav button[onclick="toggleUserMenu()"] span,
        nav button[onclick="toggleUserMenu()"] span.text-sm,
        nav .relative button span {
            color: #1e293b !important;
            font-weight: 500 !important;
        }

        /* Override dark mode text color */
        nav .flex.items-center.gap-2>button.text-slate-700,
        nav .flex.items-center.gap-2>button.dark\:text-slate-100 {
            color: #1e293b !important;
        }

        /* Catch-all for any text in profile button */
        nav .relative:last-child button,
        nav .relative:last-child button * {
            color: #1e293b !important;
        }

        nav .relative:last-child button .fa-chevron-down {
            color: #64748b !important;
        }

        nav .flex.items-center.gap-2>button .fa-chevron-down {
            color: #64748b !important;
        }

        /* Profile Image Enhancement */
        nav img[alt="Profile"] {
            border: 2px solid rgba(16, 185, 129, 0.2);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
        }

        nav .flex.items-center.gap-2>button:hover img[alt="Profile"] {
            border-color: rgba(16, 185, 129, 0.4);
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.15);
        }

        /* Navbar Logo Enhancement */
        nav .h-9.w-9 {
            transition: all 0.3s ease;
        }

        nav a:hover .h-9.w-9 {
            transform: scale(1.05);
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.2);
        }

        /* New Gampaha Pharmacy Brand Text */
        nav .font-bold.leading-tight {
            color: #0f172a !important;
        }

        nav .text-xs.text-slate-500 {
            color: #64748b !important;
        }

        /* Mobile Menu Button */
        nav .md\:hidden {
            color: #334155 !important;
        }

        /* User Dropdown Text Colors */
        #userDropdown a {
            color: #334155 !important;
        }

        #userDropdown a:hover {
            background: rgba(16, 185, 129, 0.08) !important;
        }

        #userDropdown a.text-red-600 {
            color: #dc2626 !important;
        }

        #userDropdown a.text-red-600:hover {
            background: rgba(239, 68, 68, 0.08) !important;
        }

        /* Dropdown Animation */
        #notificationsDropdown,
        #userDropdown {
            animation: dropdownSlideIn 0.2s ease-out;
            transform-origin: top;
        }

        @keyframes dropdownSlideIn {
            from {
                opacity: 0;
                transform: translateY(-10px) scale(0.95);
            }

            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        /* Badge Enhancement */
        .pc-badge {
            font-size: 0.65rem;
            padding: 0.15rem 0.4rem;
            border-radius: 0.5rem;
            font-weight: 700;
        }

        .pc-badge-danger {
            background: linear-gradient(135deg, #ef4444, #dc2626);
            color: white;
            box-shadow: 0 2px 8px rgba(239, 68, 68, 0.3);
            animation: badgePulse 2s ease-in-out infinite;
        }

        @keyframes badgePulse {

            0%,
            100% {
                transform: scale(1);
                box-shadow: 0 2px 8px rgba(239, 68, 68, 0.3);
            }

            50% {
                transform: scale(1.05);
                box-shadow: 0 4px 12px rgba(239, 68, 68, 0.5);
            }
        }

        /* Container Spacing */
        .pc-container {
            padding-top: 2rem;
            position: relative;
            z-index: 1;
        }

        /* Enhanced Stat Card with Glassmorphism */
        .pc-stat-card {
            background: rgba(255, 255, 255, 0.95) !important;
            backdrop-filter: blur(12px);
            border: 1px solid rgba(16, 185, 129, 0.1) !important;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.04) !important;
            border-radius: 1.5rem !important;
            padding: 1.75rem !important;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .pc-stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: linear-gradient(90deg, #10b981, #3b82f6);
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .pc-stat-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 12px 40px rgba(0, 0, 0, 0.08) !important;
        }

        .pc-stat-card:hover::before {
            opacity: 1;
        }

        .pc-stat-card .icon {
            width: 4rem;
            height: 4rem;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 1rem;
            background: linear-gradient(135deg, rgba(16, 185, 129, 0.1), rgba(59, 130, 246, 0.1));
            font-size: 1.75rem;
        }

        /* Enhanced Quick Actions Card */
        .pc-card {
            background: rgba(255, 255, 255, 0.95) !important;
            backdrop-filter: blur(12px);
            border: 1px solid rgba(16, 185, 129, 0.08) !important;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.04) !important;
            border-radius: 1.5rem !important;
            transition: all 0.3s ease;
        }

        .pc-card:hover {
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.08) !important;
        }

        /* Action Button Enhancements */
        .pc-btn {
            border-radius: 1rem !important;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .pc-btn::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.3);
            transform: translate(-50%, -50%);
            transition: width 0.5s, height 0.5s;
        }

        .pc-btn:active::before {
            width: 300px;
            height: 300px;
        }

        .pc-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.1);
        }

        /* Page Header Enhancement */
        .pc-page-header {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            border-radius: 1.5rem;
            padding: 1.75rem;
            margin-bottom: 2rem;
            border: 1px solid rgba(16, 185, 129, 0.08);
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.03);
        }

        /* Breadcrumb Enhancement */
        .pc-breadcrumb {
            color: #64748b;
            font-size: 0.875rem;
            font-weight: 500;
            margin-bottom: 1rem;
        }

        /* Fade In Animation */
        .fade-in-up {
            animation: fadeInUp 0.6s ease-out forwards;
            opacity: 0;
        }

        @keyframes fadeInUp {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Empty State Enhancement */
        .pc-empty-state {
            padding: 2rem;
            text-align: center;
            color: #94a3b8;
        }

        .pc-empty-icon {
            font-size: 2.5rem;
            margin-bottom: 0.75rem;
            opacity: 0.5;
        }

        /* Trend Indicators */
        .pc-trend-up {
            color: #10b981 !important;
            font-size: 0.75rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 0.25rem;
            margin-top: 0.5rem;
        }

        .pc-trend-down {
            color: #f59e0b !important;
            font-size: 0.75rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 0.25rem;
            margin-top: 0.5rem;
        }
    </style>
</head>

<body class="pc-shell">
    <!-- Minimal Animated Background -->
    <div class="dashboard-animated-bg"></div>

    <!-- Subtle Particles Container -->
    <div id="particles-container" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; z-index: -1; pointer-events: none;"></div>
    <?php include 'includes/navbar.php'; ?>

    <div class="pc-container">
        <div class="pc-page-header pc-animate">
            <div class="pc-breadcrumb">Home <i class="fas fa-chevron-right"></i> Dashboard</div>
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div>
                    <h1 class="pc-page-title"><i class="fas fa-shield-heart mr-2 text-emerald-600"></i>Operations Dashboard</h1>
                    <p class="pc-page-subtitle">Welcome back, <?php echo htmlspecialchars($user['name']); ?>. Here's today's pharmacy snapshot.</p>
                </div>
                <a href="modules/sales/new_sale.php" class="pc-btn pc-btn-primary"><i class="fas fa-plus"></i>New Sale</a>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="pc-stat-card">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Today's Sales</p>
                        <p class="text-2xl font-bold text-gray-900" id="todaySales">Rs <?php echo number_format($todaySales, 2); ?></p>
                        <p class="pc-trend-up"><i class="fas fa-arrow-trend-up"></i> +6.1% vs yesterday</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-rupee-sign text-green-600"></i>
                    </div>
                </div>
            </div>

            <div class="pc-stat-card">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Low Stock Items</p>
                        <p class="text-2xl font-bold text-gray-900" id="lowStockCount"><?php echo $lowStockCount; ?></p>
                        <p class="pc-trend-down"><i class="fas fa-circle-exclamation"></i> monitor urgently</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-exclamation-triangle text-blue-600"></i>
                    </div>
                </div>
            </div>

            <div class="pc-stat-card">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Pending Orders</p>
                        <p class="text-2xl font-bold text-gray-900" id="pendingOrders"><?php echo $pendingOrders; ?></p>
                        <p class="text-xs text-slate-500">Workflow queue</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-clock text-yellow-600"></i>
                    </div>
                </div>
            </div>

            <div class="pc-stat-card">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Total Customers</p>
                        <p class="text-2xl font-bold text-gray-900" id="totalCustomers"><?php echo $totalCustomers; ?></p>
                        <p class="pc-trend-up"><i class="fas fa-user-plus"></i> healthy growth</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-users text-purple-600"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="pc-card p-6 mb-8 pc-animate">
            <h2 class="text-xl font-semibold text-gray-800 mb-4">
                <i class="fas fa-bolt mr-2"></i>Quick Actions
            </h2>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <a href="modules/inventory/add_medicine.php" class="pc-btn pc-btn-primary p-4 text-center flex-col !gap-2">
                    <i class="fas fa-plus-circle text-2xl mb-2"></i>
                    <p class="font-medium">Add Medicine</p>
                </a>
                <a href="modules/sales/new_sale.php" class="pc-btn pc-btn-secondary p-4 text-center flex-col !gap-2">
                    <i class="fas fa-shopping-cart text-2xl mb-2"></i>
                    <p class="font-medium">New Sale</p>
                </a>
                <a href="modules/prescriptions/upload.php" class="pc-btn pc-btn-muted p-4 text-center flex-col !gap-2">
                    <i class="fas fa-upload text-2xl mb-2"></i>
                    <p class="font-medium">Upload Prescription</p>
                </a>
                <a href="modules/customers/add_customer.php" class="pc-btn pc-btn-muted p-4 text-center flex-col !gap-2">
                    <i class="fas fa-user-plus text-2xl mb-2"></i>
                    <p class="font-medium">Add Customer</p>
                </a>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="pc-card p-6">
                <h2 class="text-xl font-semibold text-gray-800 mb-4">
                    <i class="fas fa-shopping-cart mr-2"></i>Recent Sales
                </h2>
                <div id="recentSales" class="space-y-3"></div>
                <a href="modules/sales/index.php" class="text-green-600 hover:text-green-700 text-sm font-medium mt-4 inline-block">
                    <i class="fas fa-arrow-right mr-1"></i>View All Sales
                </a>
            </div>

            <div class="pc-card p-6">
                <h2 class="text-xl font-semibold text-gray-800 mb-4">
                    <i class="fas fa-exclamation-triangle mr-2"></i>Stock Alerts
                </h2>
                <div id="stockAlerts" class="space-y-3"></div>
                <a href="modules/inventory/index.php" class="text-blue-600 hover:text-blue-700 text-sm font-medium mt-4 inline-block">
                    <i class="fas fa-arrow-right mr-1"></i>View Inventory
                </a>
            </div>
        </div>
    </div>

    <script src="assets/js/admin-icons-fix.js"></script>
    <script>
        // Dashboard data from database
        window.dashboardData = {
            recentSales: <?php echo json_encode($recentSales); ?>,
            stockAlerts: <?php echo json_encode($stockAlerts); ?>
        };
    </script>
    <script src="assets/js/dashboard.js"></script>

    <script>
        // Dashboard Animations Controller
        const DashboardAnimations = {
            init() {
                if (typeof anime === 'undefined') {
                    console.warn('Anime.js not loaded. Animations will not run.');
                    return;
                }

                this.createParticles();
                this.animateStatCards();
                this.animateQuickActions();
                this.animateSections();
                this.setupCardHovers();
            },

            // Create subtle floating particles
            createParticles() {
                const container = document.getElementById('particles-container');
                if (!container) return;

                const particleCount = 35;

                for (let i = 0; i < particleCount; i++) {
                    const particle = document.createElement('div');
                    particle.className = 'particle';

                    const size = Math.random() * 70 + 25;
                    const startX = Math.random() * window.innerWidth;
                    const startY = Math.random() * window.innerHeight;
                    const duration = Math.random() * 30 + 20;
                    const delay = Math.random() * 5;

                    particle.style.width = `${size}px`;
                    particle.style.height = `${size}px`;
                    particle.style.left = `${startX}px`;
                    particle.style.top = `${startY}px`;

                    container.appendChild(particle);

                    anime({
                        targets: particle,
                        translateY: [{
                                value: -100,
                                duration: duration * 500
                            },
                            {
                                value: 100,
                                duration: duration * 500
                            }
                        ],
                        translateX: [{
                                value: -50,
                                duration: duration * 250
                            },
                            {
                                value: 50,
                                duration: duration * 500
                            },
                            {
                                value: -50,
                                duration: duration * 250
                            }
                        ],
                        opacity: [{
                                value: 0.6,
                                duration: duration * 250
                            },
                            {
                                value: 0.1,
                                duration: duration * 500
                            },
                            {
                                value: 0.6,
                                duration: duration * 250
                            }
                        ],
                        easing: 'easeInOutSine',
                        loop: true,
                        delay: delay * 1000
                    });
                }
            },

            // Animate stat cards with stagger
            animateStatCards() {
                const statCards = document.querySelectorAll('.pc-stat-card');

                anime({
                    targets: statCards,
                    translateY: [50, 0],
                    opacity: [0, 1],
                    duration: 800,
                    delay: anime.stagger(100, {
                        start: 300
                    }),
                    easing: 'easeOutQuad'
                });

                // Animate the numbers inside stat cards
                statCards.forEach((card, index) => {
                    const number = card.querySelector('.text-2xl');
                    if (number) {
                        anime({
                            targets: number,
                            scale: [0.8, 1],
                            opacity: [0, 1],
                            duration: 600,
                            delay: 500 + (index * 100),
                            easing: 'easeOutBack'
                        });
                    }
                });
            },

            // Animate quick actions section
            animateQuickActions() {
                const quickActionsSection = document.querySelector('.pc-card');

                if (quickActionsSection) {
                    const observer = new IntersectionObserver((entries) => {
                        entries.forEach(entry => {
                            if (entry.isIntersecting && !entry.target.classList.contains('animated')) {
                                entry.target.classList.add('animated');

                                const buttons = entry.target.querySelectorAll('.pc-btn');
                                anime({
                                    targets: buttons,
                                    scale: [0.9, 1],
                                    opacity: [0, 1],
                                    duration: 600,
                                    delay: anime.stagger(80, {
                                        start: 200
                                    }),
                                    easing: 'easeOutBack'
                                });
                            }
                        });
                    }, {
                        threshold: 0.2
                    });

                    observer.observe(quickActionsSection);
                }
            },

            // Animate sections on scroll
            animateSections() {
                const sections = document.querySelectorAll('.grid.grid-cols-1.lg\\:grid-cols-2');

                sections.forEach(section => {
                    const observer = new IntersectionObserver((entries) => {
                        entries.forEach(entry => {
                            if (entry.isIntersecting && !entry.target.classList.contains('animated')) {
                                entry.target.classList.add('animated');

                                const cards = entry.target.querySelectorAll('.pc-card');
                                anime({
                                    targets: cards,
                                    translateY: [40, 0],
                                    opacity: [0, 1],
                                    duration: 800,
                                    delay: anime.stagger(150),
                                    easing: 'easeOutQuad'
                                });
                            }
                        });
                    }, {
                        threshold: 0.1
                    });

                    observer.observe(section);
                });
            },

            // Setup card hover animations
            setupCardHovers() {
                const cards = document.querySelectorAll('.pc-stat-card, .pc-card .pc-btn');

                cards.forEach(card => {
                    card.addEventListener('mouseenter', function() {
                        anime({
                            targets: this,
                            scale: 1.02,
                            duration: 300,
                            easing: 'easeOutQuad'
                        });
                    });

                    card.addEventListener('mouseleave', function() {
                        anime({
                            targets: this,
                            scale: 1,
                            duration: 300,
                            easing: 'easeOutQuad'
                        });
                    });
                });

                // Animate stat card icons on hover
                const statCards = document.querySelectorAll('.pc-stat-card');
                statCards.forEach(card => {
                    const icon = card.querySelector('.icon');
                    if (icon) {
                        card.addEventListener('mouseenter', function() {
                            anime({
                                targets: icon,
                                rotate: [0, 360],
                                scale: [1, 1.1],
                                duration: 600,
                                easing: 'easeOutBack'
                            });
                        });
                    }
                });
            }
        };

        // Initialize animations when DOM is ready
        document.addEventListener('DOMContentLoaded', function() {
            DashboardAnimations.init();

            // Animate navbar
            if (typeof anime !== 'undefined') {
                const navbar = document.querySelector('nav');
                if (navbar) {
                    anime({
                        targets: navbar,
                        translateY: [-30, 0],
                        opacity: [0, 1],
                        duration: 600,
                        easing: 'easeOutQuad'
                    });

                    // Animate navbar logo
                    const navLogo = navbar.querySelector('.h-9.w-9');
                    if (navLogo) {
                        anime({
                            targets: navLogo,
                            rotate: [0, 360],
                            scale: [0.8, 1],
                            duration: 800,
                            delay: 400,
                            easing: 'easeOutBack'
                        });
                    }

                    // Animate navbar links
                    const navLinks = navbar.querySelectorAll('.md\\:flex .pc-btn');
                    if (navLinks.length > 0) {
                        anime({
                            targets: navLinks,
                            opacity: [0, 1],
                            translateY: [-10, 0],
                            duration: 500,
                            delay: anime.stagger(50, {
                                start: 500
                            }),
                            easing: 'easeOutQuad'
                        });
                    }

                    // Animate user profile button
                    const userButton = navbar.querySelector('.relative:last-child button, button[onclick="toggleUserMenu()"]');
                    if (userButton) {
                        anime({
                            targets: userButton,
                            opacity: [0, 1],
                            scale: [0.9, 1],
                            duration: 500,
                            delay: 800,
                            easing: 'easeOutBack'
                        });
                    }
                }

                // Animate page header
                const pageHeader = document.querySelector('.pc-page-header');
                if (pageHeader) {
                    anime({
                        targets: pageHeader,
                        translateY: [-20, 0],
                        opacity: [0, 1],
                        duration: 600,
                        delay: 200,
                        easing: 'easeOutQuad'
                    });
                }

                // Animate breadcrumb
                const breadcrumb = document.querySelector('.pc-breadcrumb');
                if (breadcrumb) {
                    anime({
                        targets: breadcrumb,
                        translateX: [-20, 0],
                        opacity: [0, 1],
                        duration: 500,
                        delay: 200,
                        easing: 'easeOutQuad'
                    });
                }

                // Animate page title
                const pageTitle = document.querySelector('.pc-page-title');
                if (pageTitle) {
                    anime({
                        targets: pageTitle,
                        translateY: [10, 0],
                        opacity: [0, 1],
                        duration: 600,
                        delay: 300,
                        easing: 'easeOutQuad'
                    });
                }
            }
        });

        // Enhanced dropdown toggle animations
        window.toggleNotifications = function() {
            const dropdown = document.getElementById('notificationsDropdown');
            const isHidden = dropdown.classList.contains('hidden');

            if (isHidden) {
                dropdown.classList.remove('hidden');
                if (typeof anime !== 'undefined') {
                    anime({
                        targets: dropdown,
                        opacity: [0, 1],
                        translateY: [-10, 0],
                        scale: [0.95, 1],
                        duration: 300,
                        easing: 'easeOutBack'
                    });
                }
            } else {
                if (typeof anime !== 'undefined') {
                    anime({
                        targets: dropdown,
                        opacity: [1, 0],
                        translateY: [0, -10],
                        scale: [1, 0.95],
                        duration: 200,
                        easing: 'easeInQuad',
                        complete: () => dropdown.classList.add('hidden')
                    });
                } else {
                    dropdown.classList.add('hidden');
                }
            }
        };

        window.toggleUserMenu = function() {
            const dropdown = document.getElementById('userDropdown');
            const isHidden = dropdown.classList.contains('hidden');
            const profileImg = document.querySelector('nav img[alt="Profile"]');

            if (isHidden) {
                dropdown.classList.remove('hidden');

                // Animate profile image
                if (typeof anime !== 'undefined' && profileImg) {
                    anime({
                        targets: profileImg,
                        scale: [1, 1.1, 1],
                        rotate: [0, 5, -5, 0],
                        duration: 400,
                        easing: 'easeOutElastic(1, .5)'
                    });
                }

                if (typeof anime !== 'undefined') {
                    anime({
                        targets: dropdown,
                        opacity: [0, 1],
                        translateY: [-10, 0],
                        scale: [0.95, 1],
                        duration: 300,
                        easing: 'easeOutBack'
                    });
                }
            } else {
                if (typeof anime !== 'undefined') {
                    anime({
                        targets: dropdown,
                        opacity: [1, 0],
                        translateY: [0, -10],
                        scale: [1, 0.95],
                        duration: 200,
                        easing: 'easeInQuad',
                        complete: () => dropdown.classList.add('hidden')
                    });
                } else {
                    dropdown.classList.add('hidden');
                }
            }
        };
    </script>
</body>

</html>