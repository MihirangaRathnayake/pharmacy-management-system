// Products page JavaScript functionality

let currentProducts = [];
let currentPage = 1;
let totalPages = 1;
let currentFilters = {
    search: '',
    category: '',
    sort: 'name',
    prescription: ''
};

// Initialize products page
document.addEventListener('DOMContentLoaded', function () {
    initializeProductsPage();
    setupProductFilters();
    loadProducts();
});

// Initialize products page
function initializeProductsPage() {
    // Get URL parameters
    const urlParams = new URLSearchParams(window.location.search);
    const searchQuery = urlParams.get('search');
    const category = urlParams.get('category');

    if (searchQuery) {
        currentFilters.search = searchQuery;
        document.getElementById('headerSearch').value = searchQuery;
    }

    if (category) {
        currentFilters.category = category;
        document.getElementById('categoryFilter').value = category;
    }

    // Generate loading skeleton
    generateLoadingSkeleton();
}

// Setup product filters
function setupProductFilters() {
    // Category filter
    document.getElementById('categoryFilter').addEventListener('change', function () {
        currentFilters.category = this.value;
        currentPage = 1;
        loadProducts();
    });

    // Sort filter
    document.getElementById('sortFilter').addEventListener('change', function () {
        currentFilters.sort = this.value;
        currentPage = 1;
        loadProducts();
    });

    // Prescription filter
    document.getElementById('prescriptionFilter').addEventListener('change', function () {
        currentFilters.prescription = this.value;
        currentPage = 1;
        loadProducts();
    });

    // Header search
    const headerSearch = document.getElementById('headerSearch');
    let searchTimeout;
    headerSearch.addEventListener('input', function () {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            currentFilters.search = this.value;
            currentPage = 1;
            loadProducts();
        }, 500);
    });
}

// Load products
function loadProducts() {
    showLoadingSkeleton();

    // Build query parameters
    const params = new URLSearchParams({
        q: currentFilters.search,
        category: currentFilters.category,
        sort: currentFilters.sort,
        prescription: currentFilters.prescription,
        page: currentPage,
        limit: 12
    });

    // Remove empty parameters
    for (let [key, value] of [...params]) {
        if (!value) params.delete(key);
    }

    fetch(`/api/customer/search_products.php?${params}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                currentProducts = data.products;
                displayProducts(data.products);
                updateResultsCount(data.total);
                updatePagination(data.total);
            } else {
                showNoProducts();
            }
        })
        .catch(error => {
            console.error('Error loading products:', error);
            showNoProducts();
        })
        .finally(() => {
            hideLoadingSkeleton();
        });
}

// Display products
function displayProducts(products) {
    const productsGrid = document.getElementById('productsGrid');
    const noProducts = document.getElementById('noProducts');

    if (products.length === 0) {
        showNoProducts();
        return;
    }

    noProducts.style.display = 'none';

    productsGrid.innerHTML = products.map(product => `
        <div class="product-card" data-product-id="${product.id}">
            <div class="product-image-container">
                <img src="${product.image || 'assets/images/default-medicine.jpg'}" 
                     alt="${product.name}" class="product-image"
                     onerror="this.src='assets/images/default-medicine.jpg'">
                
                <div class="product-badges">
                    ${product.prescription_required ?
            '<span class="badge badge-prescription">Prescription Required</span>' :
            '<span class="badge badge-otc">OTC</span>'
        }
                </div>
                
                <button class="favorite-btn" onclick="toggleFavorite(${product.id})" title="Add to favorites">
                    <i class="far fa-heart"></i>
                </button>
            </div>
            
            <div class="product-info">
                <h3 class="product-name">${product.name}</h3>
                ${product.generic_name ? `<p class="product-generic">${product.generic_name}</p>` : ''}
                ${product.description ? `<p class="product-description">${product.description}</p>` : ''}
                
                <div class="product-price-section">
                    <span class="product-price">Rs ${parseFloat(product.selling_price).toFixed(2)}</span>
                    <span class="product-stock">
                        ${product.stock_quantity > 0 ?
            `${product.stock_quantity} in stock` :
            'Out of stock'
        }
                    </span>
                </div>
                
                <div class="product-actions">
                    <button class="btn-add-cart" 
                            onclick="quickAddToCart(this, ${product.id})"
                            ${!product.in_stock ? 'disabled' : ''}>
                        <i class="fas fa-cart-plus"></i>
                        ${product.in_stock ? 'Add to Cart' : 'Out of Stock'}
                    </button>
                    <button class="btn-view-details" onclick="viewProductDetails(${product.id})">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
            </div>
        </div>
    `).join('');
}

// Show no products message
function showNoProducts() {
    const productsGrid = document.getElementById('productsGrid');
    const noProducts = document.getElementById('noProducts');
    const pagination = document.getElementById('pagination');

    productsGrid.innerHTML = '';
    noProducts.style.display = 'block';
    pagination.style.display = 'none';

    updateResultsCount(0);
}

// Update results count
function updateResultsCount(total) {
    const resultsCount = document.getElementById('resultsCount');
    if (total === 0) {
        resultsCount.textContent = 'No products found';
    } else if (total === 1) {
        resultsCount.textContent = '1 product found';
    } else {
        resultsCount.textContent = `${total} products found`;
    }
}

// Update pagination
function updatePagination(total) {
    const pagination = document.getElementById('pagination');
    const itemsPerPage = 12;
    totalPages = Math.ceil(total / itemsPerPage);

    if (totalPages <= 1) {
        pagination.style.display = 'none';
        return;
    }

    pagination.style.display = 'flex';

    let paginationHTML = '';

    // Previous button
    if (currentPage > 1) {
        paginationHTML += `
            <button class="pagination-btn" onclick="changePage(${currentPage - 1})">
                <i class="fas fa-chevron-left"></i> Previous
            </button>
        `;
    }

    // Page numbers
    const startPage = Math.max(1, currentPage - 2);
    const endPage = Math.min(totalPages, currentPage + 2);

    if (startPage > 1) {
        paginationHTML += `<button class="pagination-btn" onclick="changePage(1)">1</button>`;
        if (startPage > 2) {
            paginationHTML += `<span class="pagination-ellipsis">...</span>`;
        }
    }

    for (let i = startPage; i <= endPage; i++) {
        paginationHTML += `
            <button class="pagination-btn ${i === currentPage ? 'active' : ''}" 
                    onclick="changePage(${i})">${i}</button>
        `;
    }

    if (endPage < totalPages) {
        if (endPage < totalPages - 1) {
            paginationHTML += `<span class="pagination-ellipsis">...</span>`;
        }
        paginationHTML += `<button class="pagination-btn" onclick="changePage(${totalPages})">${totalPages}</button>`;
    }

    // Next button
    if (currentPage < totalPages) {
        paginationHTML += `
            <button class="pagination-btn" onclick="changePage(${currentPage + 1})">
                Next <i class="fas fa-chevron-right"></i>
            </button>
        `;
    }

    pagination.innerHTML = paginationHTML;
}

// Change page
function changePage(page) {
    if (page >= 1 && page <= totalPages && page !== currentPage) {
        currentPage = page;
        loadProducts();

        // Scroll to top
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }
}

// Clear filters
function clearFilters() {
    currentFilters = {
        search: '',
        category: '',
        sort: 'name',
        prescription: ''
    };

    // Reset form elements
    document.getElementById('headerSearch').value = '';
    document.getElementById('categoryFilter').value = '';
    document.getElementById('sortFilter').value = 'name';
    document.getElementById('prescriptionFilter').value = '';

    currentPage = 1;
    loadProducts();

    // Update URL
    window.history.pushState({}, '', window.location.pathname);
}

// View product details
function viewProductDetails(productId) {
    // For now, just show product info in a modal
    // In a full implementation, you'd navigate to a product detail page
    const product = currentProducts.find(p => p.id === productId);
    if (product) {
        showProductModal(product);
    }
}

// Show product modal
function showProductModal(product) {
    const modal = document.createElement('div');
    modal.className = 'modal active';
    modal.innerHTML = `
        <div class="modal-content" style="max-width: 600px;">
            <div class="modal-header">
                <h2>${product.name}</h2>
                <button class="modal-close" onclick="this.closest('.modal').remove()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <div style="display: grid; grid-template-columns: 200px 1fr; gap: 20px; margin-bottom: 20px;">
                    <img src="${product.image || 'assets/images/default-medicine.jpg'}" 
                         alt="${product.name}" style="width: 100%; border-radius: 8px;"
                         onerror="this.src='assets/images/default-medicine.jpg'">
                    <div>
                        ${product.generic_name ? `<p><strong>Generic Name:</strong> ${product.generic_name}</p>` : ''}
                        ${product.category_name ? `<p><strong>Category:</strong> ${product.category_name}</p>` : ''}
                        ${product.dosage ? `<p><strong>Dosage:</strong> ${product.dosage}</p>` : ''}
                        <p><strong>Price:</strong> Rs ${parseFloat(product.selling_price).toFixed(2)}</p>
                        <p><strong>Stock:</strong> ${product.stock_quantity} ${product.unit || 'units'}</p>
                        <p><strong>Type:</strong> ${product.prescription_required ? 'Prescription Required' : 'Over-the-Counter'}</p>
                    </div>
                </div>
                ${product.description ? `<p><strong>Description:</strong> ${product.description}</p>` : ''}
                <div style="margin-top: 20px;">
                    <button class="btn btn-primary" onclick="addToCart(${product.id}); this.closest('.modal').remove();"
                            ${!product.in_stock ? 'disabled' : ''}>
                        <i class="fas fa-cart-plus"></i>
                        ${product.in_stock ? 'Add to Cart' : 'Out of Stock'}
                    </button>
                </div>
            </div>
        </div>
    `;

    document.body.appendChild(modal);
    document.body.style.overflow = 'hidden';

    // Close modal when clicking outside
    modal.addEventListener('click', function (e) {
        if (e.target === modal) {
            modal.remove();
            document.body.style.overflow = 'auto';
        }
    });
}

// Generate loading skeleton
function generateLoadingSkeleton() {
    const skeleton = document.getElementById('loadingSkeleton');
    const skeletonHTML = Array(8).fill().map(() => `
        <div class="product-skeleton">
            <div class="skeleton-image"></div>
            <div class="skeleton-content">
                <div class="skeleton-line"></div>
                <div class="skeleton-line short"></div>
                <div class="skeleton-line medium"></div>
                <div class="skeleton-line short"></div>
            </div>
        </div>
    `).join('');

    skeleton.innerHTML = skeletonHTML;
}

// Show loading skeleton
function showLoadingSkeleton() {
    document.getElementById('loadingSkeleton').style.display = 'grid';
    document.getElementById('productsGrid').style.display = 'none';
    document.getElementById('noProducts').style.display = 'none';
}

// Hide loading skeleton
function hideLoadingSkeleton() {
    document.getElementById('loadingSkeleton').style.display = 'none';
    document.getElementById('productsGrid').style.display = 'grid';
}

// Search functionality from header
function performHeaderSearch() {
    const query = document.getElementById('headerSearch').value;
    currentFilters.search = query;
    currentPage = 1;
    loadProducts();
}

// Add event listener for Enter key in search
document.getElementById('headerSearch').addEventListener('keypress', function (e) {
    if (e.key === 'Enter') {
        performHeaderSearch();
    }
});

// Filter by category (called from category links)
function filterByCategory(category) {
    currentFilters.category = category;
    document.getElementById('categoryFilter').value = category;
    currentPage = 1;
    loadProducts();
}

// Quick filters
function showOnlyPrescription() {
    currentFilters.prescription = 'prescription';
    document.getElementById('prescriptionFilter').value = 'prescription';
    currentPage = 1;
    loadProducts();
}

function showOnlyOTC() {
    currentFilters.prescription = 'otc';
    document.getElementById('prescriptionFilter').value = 'otc';
    currentPage = 1;
    loadProducts();
}

// Sort products
function sortByPrice(order = 'low') {
    currentFilters.sort = order === 'low' ? 'price_low' : 'price_high';
    document.getElementById('sortFilter').value = currentFilters.sort;
    currentPage = 1;
    loadProducts();
}