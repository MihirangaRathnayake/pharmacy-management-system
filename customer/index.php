<?php
// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Initialize variables
$featured_medicines = [];
$categories = [];
$pdo = null;

// Try to connect to database
try {
    require_once '../config/database.php';
    
    if ($pdo) {
        // Get featured medicines
        $stmt = $pdo->prepare("
            SELECT m.*, c.name as category_name 
            FROM medicines m 
            LEFT JOIN categories c ON m.category_id = c.id 
            WHERE m.status = 'active' AND m.stock_quantity > 0 
            ORDER BY m.created_at DESC 
            LIMIT 8
        ");
        $stmt->execute();
        $featured_medicines = $stmt->fetchAll();

        // Get categories
        $stmt = $pdo->prepare("SELECT * FROM categories WHERE status = 'active' ORDER BY name");
        $stmt->execute();
        $categories = $stmt->fetchAll();
    }
} catch (Exception $e) {
    // Handle database errors gracefully
    error_log("Database error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PharmaCare - Your Trusted Online Pharmacy</title>
    
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/amazon-ember-font@latest/amazonember.css" rel="stylesheet">
    <!-- FontAwesome Icons - Multiple CDN fallbacks -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" integrity="sha512-iecdLmaskl7CVkqkXNQ/ZH/XLlvWZOJyj7Yy7tcenmpD1ypASozpmT/E0iPtmFIB46ZmdtAc9eNBvH0H/ZpiBw==" crossorigin="anonymous" referrerpolicy="no-referrer">
    <!-- Backup FontAwesome CDNs -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v6.4.0/css/all.css">
    <!-- FontAwesome 5 fallback -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" integrity="sha512-1ycn6IcaQQ40/MKBW2W4Rhis/DbILU74C1vSrLJxCq57o941Ym01SwNsOMqvEBFlcgUa6xLiPY/NS5R+E6ztJQ==" crossorigin="anonymous" referrerpolicy="no-referrer">
    
    <link rel="stylesheet" href="assets/css/icons-fix.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/theme.css">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar">
        <div class="nav-container">
            <div class="nav-brand">
                <a href="index.php">
                    <i class="fas fa-plus-circle"></i>
                    <span class="brand-text">PharmaCare</span>
                </a>
            </div>
            
            <div class="nav-menu" id="navMenu">
                <a href="index.php" class="nav-link">
                    <i class="fas fa-home"></i>
                    <span>Home</span>
                </a>
                <a href="products.php" class="nav-link">
                    <i class="fas fa-pills"></i>
                    <span>Products</span>
                </a>
                <a href="prescription.php" class="nav-link">
                    <i class="fas fa-prescription"></i>
                    <span>Prescription</span>
                </a>
                <a href="contact.php" class="nav-link">
                    <i class="fas fa-phone"></i>
                    <span>Contact</span>
                </a>
            </div>

            <div class="nav-actions">
                <div class="search-container">
                    <input type="text" class="search-input" placeholder="Search medicines..." id="searchInput">
                    <button class="search-btn">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
                
                <div class="cart-container">
                    <button class="cart-btn" id="cartBtn">
                        <i class="fas fa-shopping-cart"></i>
                        <span class="cart-count" id="cartCount">0</span>
                    </button>
                </div>

                <?php if (isset($_SESSION['user_id'])): ?>
                    <div class="user-menu">
                        <button class="user-btn" id="userBtn">
                            <i class="fas fa-user"></i>
                            <span><?= htmlspecialchars($_SESSION['user_name'] ?? 'User') ?></span>
                        </button>
                        <div class="user-dropdown" id="userDropdown">
                            <a href="profile.php">
                                <i class="fas fa-user-circle"></i>
                                Profile
                            </a>
                            <a href="orders.php">
                                <i class="fas fa-box"></i>
                                Orders
                            </a>
                            <a href="logout.php">
                                <i class="fas fa-sign-out-alt"></i>
                                Logout
                            </a>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="auth-buttons">
                        <a href="login.php" class="btn btn-outline">
                            <i class="fas fa-sign-in-alt"></i>
                            Login
                        </a>
                        <a href="register.php" class="btn btn-primary">
                            <i class="fas fa-user-plus"></i>
                            Register
                        </a>
                    </div>
                <?php endif; ?>
            </div>

            <div class="nav-toggle" id="navToggle">
                <span></span>
                <span></span>
                <span></span>
            </div>
        </div>

        <!-- Mobile Search -->
        <div class="mobile-search">
            <div class="search-container">
                <input type="text" class="search-input" placeholder="Search medicines...">
                <button class="search-btn">
                    <i class="fas fa-search"></i>
                </button>
            </div>
        </div>
    </nav>

    <!-- Cart Sidebar -->
    <div class="cart-sidebar" id="cartSidebar">
        <div class="cart-header">
            <h3>Shopping Cart</h3>
            <button class="close-cart" id="closeCart">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="cart-items" id="cartItems">
            <!-- Cart items will be loaded here -->
        </div>
        <div class="cart-footer">
            <div class="cart-total">
                <span>Total: Rs <span id="cartTotal">0.00</span></span>
            </div>
            <button class="btn btn-success checkout-btn" id="checkoutBtn">
                <i class="fas fa-credit-card"></i>
                Checkout
            </button>
        </div>
    </div>

    <div class="cart-overlay" id="cartOverlay"></div>

    <!-- Hero Section -->
    <section class="hero">
        <div class="container">
            <div class="hero-content">
                <div class="hero-text">
                    <h1 class="hero-title">
                        <span class="gradient-text">Your Health,</span><br>
                        <span class="gradient-text">Our Priority</span>
                    </h1>
                    <p class="hero-subtitle">
                        Get authentic medicines delivered to your doorstep with our trusted pharmacy service
                    </p>
                    <div class="hero-buttons">
                        <a href="products.php" class="btn btn-primary">
                            <i class="fas fa-pills"></i>
                            Shop Medicines
                        </a>
                        <a href="prescription.php" class="btn btn-secondary">
                            <i class="fas fa-prescription"></i>
                            Upload Prescription
                        </a>
                    </div>
                </div>
                <div class="hero-image">
                    <div class="floating-card">
                        <i class="fas fa-heartbeat"></i>
                        <span>24/7 Service</span>
                    </div>
                    <div class="floating-card">
                        <i class="fas fa-shield-alt"></i>
                        <span>Authentic Medicines</span>
                    </div>
                    <div class="floating-card">
                        <i class="fas fa-truck"></i>
                        <span>Fast Delivery</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Categories Section -->
    <section class="categories">
        <div class="container">
            <h2 class="section-title gradient-text">Shop by Category</h2>
            <div class="categories-grid">
                <?php if (!empty($categories)): ?>
                    <?php foreach ($categories as $category): ?>
                    <div class="category-card glass-card">
                        <div class="category-icon">
                            <i class="fas fa-<?= getCategoryIcon($category['name']) ?>"></i>
                        </div>
                        <h3><?= htmlspecialchars($category['name']) ?></h3>
                        <p><?= htmlspecialchars($category['description'] ?? '') ?></p>
                        <a href="products.php?category=<?= $category['id'] ?>" class="btn btn-outline">
                            Browse <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <!-- Default categories if database is not available -->
                    <div class="category-card glass-card">
                        <div class="category-icon">
                            <i class="fas fa-band-aid"></i>
                        </div>
                        <h3>Pain Relief</h3>
                        <p>Medicines for pain management</p>
                        <a href="products.php" class="btn btn-outline">
                            Browse <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                    <div class="category-card glass-card">
                        <div class="category-icon">
                            <i class="fas fa-syringe"></i>
                        </div>
                        <h3>Antibiotics</h3>
                        <p>Antibiotic medications</p>
                        <a href="products.php" class="btn btn-outline">
                            Browse <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                    <div class="category-card glass-card">
                        <div class="category-icon">
                            <i class="fas fa-leaf"></i>
                        </div>
                        <h3>Vitamins</h3>
                        <p>Vitamin supplements</p>
                        <a href="products.php" class="btn btn-outline">
                            Browse <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                    <div class="category-card glass-card">
                        <div class="category-icon">
                            <i class="fas fa-thermometer-half"></i>
                        </div>
                        <h3>Cold & Flu</h3>
                        <p>Cold and flu medications</p>
                        <a href="products.php" class="btn btn-outline">
                            Browse <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Featured Products -->
    <section class="featured-products">
        <div class="container">
            <h2 class="section-title gradient-text">Featured Medicines</h2>
            <div class="products-grid">
                <?php if (!empty($featured_medicines)): ?>
                    <?php foreach ($featured_medicines as $medicine): ?>
                    <div class="product-card glass-card" data-id="<?= $medicine['id'] ?>">
                        <div class="product-image">
                            <?php if (!empty($medicine['image'])): ?>
                                <img src="../uploads/medicines/<?= $medicine['image'] ?>" alt="<?= htmlspecialchars($medicine['name']) ?>">
                            <?php else: ?>
                                <div class="placeholder-image">
                                    <i class="fas fa-pills"></i>
                                </div>
                            <?php endif; ?>
                            <?php if (!empty($medicine['prescription_required'])): ?>
                                <span class="prescription-badge">
                                    <i class="fas fa-prescription"></i> Rx
                                </span>
                            <?php endif; ?>
                        </div>
                        <div class="product-info">
                            <h3 class="product-name"><?= htmlspecialchars($medicine['name']) ?></h3>
                            <p class="product-generic"><?= htmlspecialchars($medicine['generic_name'] ?? '') ?></p>
                            <p class="product-category"><?= htmlspecialchars($medicine['category_name'] ?? '') ?></p>
                            <div class="product-price">
                                <span class="currency">Rs</span>
                                <span class="amount"><?= number_format($medicine['selling_price'] ?? 0, 2) ?></span>
                            </div>
                            <div class="product-actions">
                                <button class="btn btn-success add-to-cart" data-id="<?= $medicine['id'] ?>">
                                    <i class="fas fa-cart-plus"></i>
                                    Add to Cart
                                </button>
                                <button class="btn btn-outline view-details" data-id="<?= $medicine['id'] ?>">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <!-- Demo products if database is not available -->
                    <div class="product-card glass-card" data-id="1">
                        <div class="product-image">
                            <div class="placeholder-image">
                                <i class="fas fa-pills"></i>
                            </div>
                        </div>
                        <div class="product-info">
                            <h3 class="product-name">Paracetamol 500mg</h3>
                            <p class="product-generic">Paracetamol</p>
                            <p class="product-category">Pain Relief</p>
                            <div class="product-price">
                                <span class="currency">Rs</span>
                                <span class="amount">5.00</span>
                            </div>
                            <div class="product-actions">
                                <button class="btn btn-success add-to-cart" data-id="1">
                                    <i class="fas fa-cart-plus"></i>
                                    Add to Cart
                                </button>
                                <button class="btn btn-outline view-details" data-id="1">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="product-card glass-card" data-id="2">
                        <div class="product-image">
                            <div class="placeholder-image">
                                <i class="fas fa-pills"></i>
                            </div>
                            <span class="prescription-badge">
                                <i class="fas fa-prescription"></i> Rx
                            </span>
                        </div>
                        <div class="product-info">
                            <h3 class="product-name">Amoxicillin 250mg</h3>
                            <p class="product-generic">Amoxicillin</p>
                            <p class="product-category">Antibiotics</p>
                            <div class="product-price">
                                <span class="currency">Rs</span>
                                <span class="amount">25.00</span>
                            </div>
                            <div class="product-actions">
                                <button class="btn btn-success add-to-cart" data-id="2">
                                    <i class="fas fa-cart-plus"></i>
                                    Add to Cart
                                </button>
                                <button class="btn btn-outline view-details" data-id="2">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="product-card glass-card" data-id="3">
                        <div class="product-image">
                            <div class="placeholder-image">
                                <i class="fas fa-pills"></i>
                            </div>
                        </div>
                        <div class="product-info">
                            <h3 class="product-name">Vitamin C 1000mg</h3>
                            <p class="product-generic">Ascorbic Acid</p>
                            <p class="product-category">Vitamins</p>
                            <div class="product-price">
                                <span class="currency">Rs</span>
                                <span class="amount">15.00</span>
                            </div>
                            <div class="product-actions">
                                <button class="btn btn-success add-to-cart" data-id="3">
                                    <i class="fas fa-cart-plus"></i>
                                    Add to Cart
                                </button>
                                <button class="btn btn-outline view-details" data-id="3">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="product-card glass-card" data-id="4">
                        <div class="product-image">
                            <div class="placeholder-image">
                                <i class="fas fa-pills"></i>
                            </div>
                        </div>
                        <div class="product-info">
                            <h3 class="product-name">Cough Syrup</h3>
                            <p class="product-generic">Dextromethorphan</p>
                            <p class="product-category">Cold & Flu</p>
                            <div class="product-price">
                                <span class="currency">Rs</span>
                                <span class="amount">20.00</span>
                            </div>
                            <div class="product-actions">
                                <button class="btn btn-success add-to-cart" data-id="4">
                                    <i class="fas fa-cart-plus"></i>
                                    Add to Cart
                                </button>
                                <button class="btn btn-outline view-details" data-id="4">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
            <div class="text-center">
                <a href="products.php" class="btn btn-primary">
                    View All Products <i class="fas fa-arrow-right"></i>
                </a>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features">
        <div class="container">
            <div class="features-grid">
                <div class="feature-card glass-card">
                    <div class="feature-icon">
                        <i class="fas fa-shipping-fast"></i>
                    </div>
                    <h3>Fast Delivery</h3>
                    <p>Get your medicines delivered within 24 hours</p>
                </div>
                <div class="feature-card glass-card">
                    <div class="feature-icon">
                        <i class="fas fa-certificate"></i>
                    </div>
                    <h3>Authentic Products</h3>
                    <p>100% genuine medicines from licensed suppliers</p>
                </div>
                <div class="feature-card glass-card">
                    <div class="feature-icon">
                        <i class="fas fa-user-md"></i>
                    </div>
                    <h3>Expert Consultation</h3>
                    <p>Get advice from qualified pharmacists</p>
                </div>
                <div class="feature-card glass-card">
                    <div class="feature-icon">
                        <i class="fas fa-lock"></i>
                    </div>
                    <h3>Secure Payment</h3>
                    <p>Safe and secure payment options</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="footer-content">
            <div class="footer-section">
                <div class="footer-brand">
                    <i class="fas fa-plus-circle"></i>
                    <span>PharmaCare</span>
                </div>
                <p>Your trusted online pharmacy providing authentic medicines with fast delivery and expert consultation.</p>
                <div class="social-links">
                    <a href="#" class="social-link">
                        <i class="fab fa-facebook-f"></i>
                    </a>
                    <a href="#" class="social-link">
                        <i class="fab fa-twitter"></i>
                    </a>
                    <a href="#" class="social-link">
                        <i class="fab fa-instagram"></i>
                    </a>
                    <a href="#" class="social-link">
                        <i class="fab fa-linkedin-in"></i>
                    </a>
                </div>
            </div>

            <div class="footer-section">
                <h3>Quick Links</h3>
                <ul>
                    <li><a href="index.php">Home</a></li>
                    <li><a href="products.php">Products</a></li>
                    <li><a href="prescription.php">Upload Prescription</a></li>
                    <li><a href="contact.php">Contact Us</a></li>
                </ul>
            </div>

            <div class="footer-section">
                <h3>Categories</h3>
                <ul>
                    <li><a href="products.php?category=1">Pain Relief</a></li>
                    <li><a href="products.php?category=2">Antibiotics</a></li>
                    <li><a href="products.php?category=3">Vitamins</a></li>
                    <li><a href="products.php?category=4">Cold & Flu</a></li>
                </ul>
            </div>

            <div class="footer-section">
                <h3>Contact Info</h3>
                <div class="contact-info">
                    <div class="contact-item">
                        <i class="fas fa-map-marker-alt"></i>
                        <span>123 Main Street, City, State 12345</span>
                    </div>
                    <div class="contact-item">
                        <i class="fas fa-phone"></i>
                        <span>+1 (234) 567-8900</span>
                    </div>
                    <div class="contact-item">
                        <i class="fas fa-envelope"></i>
                        <span>info@pharmacare.com</span>
                    </div>
                    <div class="contact-item">
                        <i class="fas fa-clock"></i>
                        <span>24/7 Service Available</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="footer-bottom">
            <div class="footer-bottom-content">
                <p>&copy; 2024 PharmaCare. All rights reserved.</p>
                <div class="footer-links">
                    <a href="privacy.php">Privacy Policy</a>
                    <a href="terms.php">Terms of Service</a>
                    <a href="refund.php">Refund Policy</a>
                </div>
            </div>
        </div>
    </footer>

    <script src="assets/js/icons-fix.js"></script>
    <script src="assets/js/icons.js"></script>
    <script src="assets/js/main.js"></script>
</body>
</html>

<?php
function getCategoryIcon($categoryName) {
    $icons = [
        'Pain Relief' => 'band-aid',
        'Antibiotics' => 'syringe',
        'Vitamins' => 'leaf',
        'Cold & Flu' => 'thermometer-half',
        'Diabetes' => 'heartbeat',
        'Heart' => 'heart',
        'Skin Care' => 'spa',
        'Digestive' => 'stomach'
    ];
    return $icons[$categoryName] ?? 'pills';
}
?>