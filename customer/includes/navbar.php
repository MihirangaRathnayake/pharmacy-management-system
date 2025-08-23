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
                        <span><?= htmlspecialchars($_SESSION['user_name']) ?></span>
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
                        <a href="../auth/logout.php">
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