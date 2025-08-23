<?php
// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Initialize variables
$products = [];
$categories = [];
$pdo = null;

// Try to connect to database
try {
    require_once '../config/database.php';
    
    if ($pdo) {
        // Database connection successful - use real data
        // ... existing database code ...
    }
} catch (Exception $e) {
    // Handle database errors gracefully
    error_log("Database error: " . $e->getMessage());
}

// Get search and filter parameters
$search = $_GET['search'] ?? '';
$category = $_GET['category'] ?? '';
$sort = $_GET['sort'] ?? 'name';
$page = max(1, intval($_GET['page'] ?? 1));
$limit = 12;
$offset = ($page - 1) * $limit;

// Build query
$where_conditions = ["m.status = 'active'", "m.stock_quantity > 0"];
$params = [];

if ($search) {
    $where_conditions[] = "(m.name LIKE ? OR m.generic_name LIKE ? OR m.description LIKE ?)";
    $search_param = "%$search%";
    $params = array_merge($params, [$search_param, $search_param, $search_param]);
}

if ($category) {
    $where_conditions[] = "m.category_id = ?";
    $params[] = $category;
}

$where_clause = implode(' AND ', $where_conditions);

// Order by clause
$order_options = [
    'name' => 'm.name ASC',
    'price_low' => 'm.selling_price ASC',
    'price_high' => 'm.selling_price DESC',
    'newest' => 'm.created_at DESC'
];
$order_clause = $order_options[$sort] ?? 'm.name ASC';

// Get total count
$count_sql = "SELECT COUNT(*) FROM medicines m LEFT JOIN categories c ON m.category_id = c.id WHERE $where_clause";
$count_stmt = $pdo->prepare($count_sql);
$count_stmt->execute($params);
$total_products = $count_stmt->fetchColumn();
$total_pages = ceil($total_products / $limit);

// Get products
$sql = "
    SELECT m.*, c.name as category_name 
    FROM medicines m 
    LEFT JOIN categories c ON m.category_id = c.id 
    WHERE $where_clause 
    ORDER BY $order_clause 
    LIMIT $limit OFFSET $offset
";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();

// Get categories for filter
$categories_stmt = $pdo->prepare("SELECT * FROM categories WHERE status = 'active' ORDER BY name");
$categories_stmt->execute();
$categories = $categories_stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products - PharmaCare</title>
    
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/amazon-ember-font@latest/amazonember.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/theme.css">
    <link rel="stylesheet" href="assets/css/products.css">
</head>
<body>
    <?php include 'includes/navbar.php'; ?>

    <main class="products-page">
        <!-- Page Header -->
        <section class="page-header">
            <div class="container">
                <div class="header-content">
                    <h1 class="gradient-text">Our Products</h1>
                    <p>Discover our wide range of authentic medicines and healthcare products</p>
                </div>
                
                <!-- Advanced Search -->
                <div class="search-section">
                    <form class="search-form" method="GET">
                        <div class="search-group">
                            <div class="search-input-wrapper">
                                <input type="text" name="search" class="search-input-large" 
                                       placeholder="Search medicines, brands, or conditions..." 
                                       value="<?= htmlspecialchars($search) ?>">
                                <button type="submit" class="search-btn-large">
                                    <i class="fas fa-search"></i>
                                    Search
                                </button>
                            </div>
                        </div>
                        
                        <div class="filter-row">
                            <select name="category" class="filter-select">
                                <option value="">All Categories</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?= $cat['id'] ?>" <?= $category == $cat['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($cat['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            
                            <select name="sort" class="filter-select">
                                <option value="name" <?= $sort == 'name' ? 'selected' : '' ?>>Name A-Z</option>
                                <option value="price_low" <?= $sort == 'price_low' ? 'selected' : '' ?>>Price: Low to High</option>
                                <option value="price_high" <?= $sort == 'price_high' ? 'selected' : '' ?>>Price: High to Low</option>
                                <option value="newest" <?= $sort == 'newest' ? 'selected' : '' ?>>Newest First</option>
                            </select>
                            
                            <button type="submit" class="btn btn-outline">
                                <i class="fas fa-filter"></i>
                                Apply Filters
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </section>

        <!-- Products Section -->
        <section class="products-section">
            <div class="container">
                <!-- Results Info -->
                <div class="results-info">
                    <div class="results-count">
                        <span class="count"><?= $total_products ?></span> products found
                        <?php if ($search): ?>
                            for "<strong><?= htmlspecialchars($search) ?></strong>"
                        <?php endif; ?>
                    </div>
                    
                    <?php if ($search || $category): ?>
                        <div class="active-filters">
                            <?php if ($search): ?>
                                <span class="filter-tag">
                                    Search: <?= htmlspecialchars($search) ?>
                                    <a href="?<?= http_build_query(array_diff_key($_GET, ['search' => ''])) ?>">
                                        <i class="fas fa-times"></i>
                                    </a>
                                </span>
                            <?php endif; ?>
                            
                            <?php if ($category): ?>
                                <?php 
                                $cat_name = '';
                                foreach ($categories as $cat) {
                                    if ($cat['id'] == $category) {
                                        $cat_name = $cat['name'];
                                        break;
                                    }
                                }
                                ?>
                                <span class="filter-tag">
                                    Category: <?= htmlspecialchars($cat_name) ?>
                                    <a href="?<?= http_build_query(array_diff_key($_GET, ['category' => ''])) ?>">
                                        <i class="fas fa-times"></i>
                                    </a>
                                </span>
                            <?php endif; ?>
                            
                            <a href="products.php" class="clear-filters">
                                <i class="fas fa-times-circle"></i>
                                Clear All
                            </a>
                        </div>
                    <?php endif; ?>
                </div>

                <?php if (empty($products)): ?>
                    <!-- Demo Products -->
                    <div class="products-grid">
                        <div class="product-card glass-card" data-id="1">
                            <div class="product-image">
                                <div class="placeholder-image">
                                    <i class="fas fa-pills"></i>
                                </div>
                            </div>
                            <div class="product-info">
                                <div class="product-category">Pain Relief</div>
                                <h3 class="product-name">Paracetamol 500mg</h3>
                                <p class="product-generic">Paracetamol</p>
                                <p class="product-dosage"><i class="fas fa-pills"></i> 500mg</p>
                                <div class="product-price">
                                    <span class="currency">Rs</span>
                                    <span class="amount">5.00</span>
                                    <span class="unit">/ piece</span>
                                </div>
                                <div class="stock-info">
                                    <span class="stock-available">
                                        <i class="fas fa-check-circle"></i>
                                        In Stock
                                    </span>
                                </div>
                                <div class="product-actions">
                                    <button class="btn btn-success add-to-cart" data-id="1">
                                        <i class="fas fa-cart-plus"></i>
                                        Add to Cart
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
                                <div class="product-category">Antibiotics</div>
                                <h3 class="product-name">Amoxicillin 250mg</h3>
                                <p class="product-generic">Amoxicillin</p>
                                <p class="product-dosage"><i class="fas fa-pills"></i> 250mg</p>
                                <div class="product-price">
                                    <span class="currency">Rs</span>
                                    <span class="amount">25.00</span>
                                    <span class="unit">/ piece</span>
                                </div>
                                <div class="stock-info">
                                    <span class="stock-available">
                                        <i class="fas fa-check-circle"></i>
                                        In Stock
                                    </span>
                                </div>
                                <div class="product-actions">
                                    <button class="btn btn-success add-to-cart" data-id="2">
                                        <i class="fas fa-cart-plus"></i>
                                        Add to Cart
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
                                <div class="product-category">Vitamins</div>
                                <h3 class="product-name">Vitamin C 1000mg</h3>
                                <p class="product-generic">Ascorbic Acid</p>
                                <p class="product-dosage"><i class="fas fa-pills"></i> 1000mg</p>
                                <div class="product-price">
                                    <span class="currency">Rs</span>
                                    <span class="amount">15.00</span>
                                    <span class="unit">/ piece</span>
                                </div>
                                <div class="stock-info">
                                    <span class="stock-available">
                                        <i class="fas fa-check-circle"></i>
                                        In Stock
                                    </span>
                                </div>
                                <div class="product-actions">
                                    <button class="btn btn-success add-to-cart" data-id="3">
                                        <i class="fas fa-cart-plus"></i>
                                        Add to Cart
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
                                <div class="product-category">Cold & Flu</div>
                                <h3 class="product-name">Cough Syrup</h3>
                                <p class="product-generic">Dextromethorphan</p>
                                <p class="product-dosage"><i class="fas fa-pills"></i> 100ml</p>
                                <div class="product-price">
                                    <span class="currency">Rs</span>
                                    <span class="amount">20.00</span>
                                    <span class="unit">/ bottle</span>
                                </div>
                                <div class="stock-info">
                                    <span class="stock-available">
                                        <i class="fas fa-check-circle"></i>
                                        In Stock
                                    </span>
                                </div>
                                <div class="product-actions">
                                    <button class="btn btn-success add-to-cart" data-id="4">
                                        <i class="fas fa-cart-plus"></i>
                                        Add to Cart
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <!-- Products Grid -->
                    <div class="products-grid">
                        <?php foreach ($products as $product): ?>
                            <div class="product-card glass-card" data-id="<?= $product['id'] ?>">
                                <div class="product-image">
                                    <?php if ($product['image']): ?>
                                        <img src="../uploads/medicines/<?= $product['image'] ?>" 
                                             alt="<?= htmlspecialchars($product['name']) ?>"
                                             loading="lazy">
                                    <?php else: ?>
                                        <div class="placeholder-image">
                                            <i class="fas fa-pills"></i>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <?php if ($product['prescription_required']): ?>
                                        <span class="prescription-badge">
                                            <i class="fas fa-prescription"></i> Rx
                                        </span>
                                    <?php endif; ?>
                                    
                                    <div class="product-overlay">
                                        <button class="btn btn-outline view-details" data-id="<?= $product['id'] ?>">
                                            <i class="fas fa-eye"></i>
                                            Quick View
                                        </button>
                                    </div>
                                </div>
                                
                                <div class="product-info">
                                    <div class="product-category">
                                        <?= htmlspecialchars($product['category_name']) ?>
                                    </div>
                                    
                                    <h3 class="product-name">
                                        <?= htmlspecialchars($product['name']) ?>
                                    </h3>
                                    
                                    <?php if ($product['generic_name']): ?>
                                        <p class="product-generic">
                                            <?= htmlspecialchars($product['generic_name']) ?>
                                        </p>
                                    <?php endif; ?>
                                    
                                    <?php if ($product['dosage']): ?>
                                        <p class="product-dosage">
                                            <i class="fas fa-pills"></i>
                                            <?= htmlspecialchars($product['dosage']) ?>
                                        </p>
                                    <?php endif; ?>
                                    
                                    <div class="product-price">
                                        <span class="currency">Rs</span>
                                        <span class="amount"><?= number_format($product['selling_price'], 2) ?></span>
                                        <span class="unit">/ <?= htmlspecialchars($product['unit']) ?></span>
                                    </div>
                                    
                                    <div class="stock-info">
                                        <?php if ($product['stock_quantity'] <= $product['min_stock_level']): ?>
                                            <span class="stock-low">
                                                <i class="fas fa-exclamation-triangle"></i>
                                                Low Stock
                                            </span>
                                        <?php else: ?>
                                            <span class="stock-available">
                                                <i class="fas fa-check-circle"></i>
                                                In Stock
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <div class="product-actions">
                                        <button class="btn btn-success add-to-cart" data-id="<?= $product['id'] ?>">
                                            <i class="fas fa-cart-plus"></i>
                                            Add to Cart
                                        </button>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Pagination -->
                    <?php if ($total_pages > 1): ?>
                        <div class="pagination">
                            <?php
                            $query_params = $_GET;
                            
                            // Previous page
                            if ($page > 1):
                                $query_params['page'] = $page - 1;
                            ?>
                                <a href="?<?= http_build_query($query_params) ?>" class="pagination-btn">
                                    <i class="fas fa-chevron-left"></i>
                                    Previous
                                </a>
                            <?php endif; ?>
                            
                            <!-- Page numbers -->
                            <div class="pagination-numbers">
                                <?php
                                $start = max(1, $page - 2);
                                $end = min($total_pages, $page + 2);
                                
                                for ($i = $start; $i <= $end; $i++):
                                    $query_params['page'] = $i;
                                ?>
                                    <a href="?<?= http_build_query($query_params) ?>" 
                                       class="pagination-number <?= $i == $page ? 'active' : '' ?>">
                                        <?= $i ?>
                                    </a>
                                <?php endfor; ?>
                            </div>
                            
                            <!-- Next page -->
                            <?php if ($page < $total_pages):
                                $query_params['page'] = $page + 1;
                            ?>
                                <a href="?<?= http_build_query($query_params) ?>" class="pagination-btn">
                                    Next
                                    <i class="fas fa-chevron-right"></i>
                                </a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </section>
    </main>

    <?php include 'includes/footer.php'; ?>

    <!-- Product Quick View Modal -->
    <div class="modal" id="productModal">
        <div class="modal-overlay"></div>
        <div class="modal-content">
            <div class="modal-header">
                <h3>Product Details</h3>
                <button class="modal-close">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body" id="modalBody">
                <!-- Product details will be loaded here -->
            </div>
        </div>
    </div>

    <script src="assets/js/main.js"></script>
    <script src="assets/js/products.js"></script>
</body>
</html>