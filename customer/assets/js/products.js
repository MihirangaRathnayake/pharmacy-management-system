// Products page specific JavaScript

class ProductsPage {
    constructor() {
        this.modal = document.getElementById('productModal');
        this.modalBody = document.getElementById('modalBody');
        this.init();
    }

    init() {
        this.setupEventListeners();
        this.setupFilters();
    }

    setupEventListeners() {
        // View details buttons
        document.addEventListener('click', (e) => {
            if (e.target.classList.contains('view-details') || e.target.closest('.view-details')) {
                const btn = e.target.classList.contains('view-details') ? e.target : e.target.closest('.view-details');
                const productId = btn.dataset.id;
                this.showProductDetails(productId);
            }
        });

        // Modal close events
        if (this.modal) {
            const closeBtn = this.modal.querySelector('.modal-close');
            const overlay = this.modal.querySelector('.modal-overlay');

            if (closeBtn) {
                closeBtn.addEventListener('click', () => this.closeModal());
            }

            if (overlay) {
                overlay.addEventListener('click', () => this.closeModal());
            }

            // Close on Escape key
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape' && this.modal.classList.contains('active')) {
                    this.closeModal();
                }
            });
        }

        // Filter form auto-submit
        const filterSelects = document.querySelectorAll('.filter-select');
        filterSelects.forEach(select => {
            select.addEventListener('change', () => {
                this.submitFilters();
            });
        });

        // Search form enhancements
        const searchForm = document.querySelector('.search-form');
        if (searchForm) {
            const searchInput = searchForm.querySelector('.search-input-large');
            if (searchInput) {
                // Add search suggestions (if implemented)
                searchInput.addEventListener('input', (e) => {
                    this.debounce(() => this.showSearchSuggestions(e.target.value), 300)();
                });
            }
        }
    }

    setupFilters() {
        // URL parameter handling
        const urlParams = new URLSearchParams(window.location.search);
        
        // Highlight active filters
        this.highlightActiveFilters(urlParams);
        
        // Auto-submit on filter change
        this.setupAutoSubmit();
    }

    async showProductDetails(productId) {
        try {
            // Show loading state
            this.showModal();
            this.modalBody.innerHTML = `
                <div class="loading-spinner">
                    <i class="fas fa-spinner fa-spin"></i>
                    <p>Loading product details...</p>
                </div>
            `;

            // Fetch product details
            const response = await fetch(`../api/get_medicine.php?id=${productId}`);
            const data = await response.json();

            if (data.success) {
                this.renderProductDetails(data.data);
            } else {
                throw new Error(data.message || 'Failed to load product details');
            }
        } catch (error) {
            console.error('Error loading product details:', error);
            this.modalBody.innerHTML = `
                <div class="error-message">
                    <i class="fas fa-exclamation-triangle"></i>
                    <p>Failed to load product details. Please try again.</p>
                    <button class="btn btn-primary" onclick="productsPage.closeModal()">Close</button>
                </div>
            `;
        }
    }

    renderProductDetails(product) {
        const expiryDate = product.expiry_date ? new Date(product.expiry_date).toLocaleDateString() : 'N/A';
        const manufactureDate = product.manufacture_date ? new Date(product.manufacture_date).toLocaleDateString() : 'N/A';

        this.modalBody.innerHTML = `
            <div class="product-details">
                <div class="product-details-header">
                    <div class="product-details-image">
                        ${product.image ? 
                            `<img src="../uploads/medicines/${product.image}" alt="${product.name}">` :
                            `<div class="placeholder-large"><i class="fas fa-pills"></i></div>`
                        }
                        ${product.prescription_required ? 
                            `<span class="prescription-badge-large">
                                <i class="fas fa-prescription"></i> Prescription Required
                            </span>` : ''
                        }
                    </div>
                    <div class="product-details-info">
                        <div class="product-category-large">${product.category_name || 'Uncategorized'}</div>
                        <h2 class="product-name-large">${product.name}</h2>
                        ${product.generic_name ? `<p class="product-generic-large">${product.generic_name}</p>` : ''}
                        <div class="product-price-large">
                            <span class="currency">Rs</span>
                            <span class="amount">${parseFloat(product.selling_price).toFixed(2)}</span>
                            <span class="unit">/ ${product.unit}</span>
                        </div>
                        <div class="stock-status">
                            ${product.stock_quantity <= product.min_stock_level ?
                                `<span class="stock-low">
                                    <i class="fas fa-exclamation-triangle"></i>
                                    Low Stock (${product.stock_quantity} remaining)
                                </span>` :
                                `<span class="stock-available">
                                    <i class="fas fa-check-circle"></i>
                                    In Stock (${product.stock_quantity} available)
                                </span>`
                            }
                        </div>
                    </div>
                </div>

                <div class="product-details-body">
                    <div class="details-section">
                        <h3><i class="fas fa-info-circle"></i> Product Information</h3>
                        <div class="details-grid">
                            ${product.dosage ? `
                                <div class="detail-item">
                                    <label>Dosage:</label>
                                    <span>${product.dosage}</span>
                                </div>
                            ` : ''}
                            ${product.batch_number ? `
                                <div class="detail-item">
                                    <label>Batch Number:</label>
                                    <span>${product.batch_number}</span>
                                </div>
                            ` : ''}
                            <div class="detail-item">
                                <label>Unit:</label>
                                <span>${product.unit}</span>
                            </div>
                            <div class="detail-item">
                                <label>Expiry Date:</label>
                                <span>${expiryDate}</span>
                            </div>
                            <div class="detail-item">
                                <label>Manufacture Date:</label>
                                <span>${manufactureDate}</span>
                            </div>
                        </div>
                    </div>

                    ${product.description ? `
                        <div class="details-section">
                            <h3><i class="fas fa-file-text"></i> Description</h3>
                            <p class="product-description">${product.description}</p>
                        </div>
                    ` : ''}

                    <div class="details-section">
                        <h3><i class="fas fa-shield-alt"></i> Safety Information</h3>
                        <div class="safety-info">
                            ${product.prescription_required ? 
                                `<div class="safety-item warning">
                                    <i class="fas fa-prescription"></i>
                                    <span>This medicine requires a valid prescription from a licensed doctor.</span>
                                </div>` : 
                                `<div class="safety-item success">
                                    <i class="fas fa-check-circle"></i>
                                    <span>This is an over-the-counter medicine.</span>
                                </div>`
                            }
                            <div class="safety-item info">
                                <i class="fas fa-exclamation-triangle"></i>
                                <span>Please read the label carefully before use.</span>
                            </div>
                            <div class="safety-item info">
                                <i class="fas fa-thermometer-half"></i>
                                <span>Store in a cool, dry place away from direct sunlight.</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="product-details-footer">
                    <div class="quantity-selector">
                        <label>Quantity:</label>
                        <div class="quantity-controls-large">
                            <button class="qty-btn-large" onclick="this.nextElementSibling.stepDown()">
                                <i class="fas fa-minus"></i>
                            </button>
                            <input type="number" class="quantity-input" value="1" min="1" max="${product.stock_quantity}">
                            <button class="qty-btn-large" onclick="this.previousElementSibling.stepUp()">
                                <i class="fas fa-plus"></i>
                            </button>
                        </div>
                    </div>
                    <button class="btn btn-success btn-large add-to-cart-modal" data-id="${product.id}">
                        <i class="fas fa-cart-plus"></i>
                        Add to Cart
                    </button>
                </div>
            </div>
        `;

        // Setup add to cart functionality for modal
        const addToCartBtn = this.modalBody.querySelector('.add-to-cart-modal');
        if (addToCartBtn) {
            addToCartBtn.addEventListener('click', () => {
                const quantity = this.modalBody.querySelector('.quantity-input').value;
                this.addToCartWithQuantity(product.id, parseInt(quantity));
            });
        }
    }

    async addToCartWithQuantity(productId, quantity) {
        try {
            // Add multiple items to cart
            for (let i = 0; i < quantity; i++) {
                await pharmacare.addToCart(productId);
            }
            
            // Close modal after successful addition
            setTimeout(() => {
                this.closeModal();
            }, 1000);
        } catch (error) {
            console.error('Error adding to cart:', error);
        }
    }

    showModal() {
        if (this.modal) {
            this.modal.classList.add('active');
            document.body.style.overflow = 'hidden';
        }
    }

    closeModal() {
        if (this.modal) {
            this.modal.classList.remove('active');
            document.body.style.overflow = '';
        }
    }

    submitFilters() {
        const form = document.querySelector('.search-form');
        if (form) {
            form.submit();
        }
    }

    highlightActiveFilters(urlParams) {
        // Highlight active category
        const categorySelect = document.querySelector('select[name="category"]');
        if (categorySelect && urlParams.get('category')) {
            categorySelect.style.background = 'var(--gradient-primary)';
            categorySelect.style.color = 'white';
        }

        // Highlight active sort
        const sortSelect = document.querySelector('select[name="sort"]');
        if (sortSelect && urlParams.get('sort')) {
            sortSelect.style.background = 'var(--gradient-primary)';
            sortSelect.style.color = 'white';
        }
    }

    setupAutoSubmit() {
        // Auto-submit form when filters change
        const filterInputs = document.querySelectorAll('.filter-select');
        filterInputs.forEach(input => {
            input.addEventListener('change', () => {
                // Add loading state
                input.style.opacity = '0.6';
                
                // Submit form
                setTimeout(() => {
                    this.submitFilters();
                }, 100);
            });
        });
    }

    async showSearchSuggestions(query) {
        if (query.length < 2) return;

        try {
            const response = await fetch(`../api/search_suggestions.php?q=${encodeURIComponent(query)}`);
            const data = await response.json();

            if (data.success && data.suggestions.length > 0) {
                this.renderSearchSuggestions(data.suggestions);
            }
        } catch (error) {
            console.error('Error fetching suggestions:', error);
        }
    }

    renderSearchSuggestions(suggestions) {
        // Remove existing suggestions
        const existingSuggestions = document.querySelector('.search-suggestions');
        if (existingSuggestions) {
            existingSuggestions.remove();
        }

        // Create suggestions dropdown
        const suggestionsContainer = document.createElement('div');
        suggestionsContainer.className = 'search-suggestions';
        suggestionsContainer.innerHTML = suggestions.map(suggestion => `
            <div class="suggestion-item" data-value="${suggestion.name}">
                <i class="fas fa-pills"></i>
                <span>${suggestion.name}</span>
                ${suggestion.generic_name ? `<small>${suggestion.generic_name}</small>` : ''}
            </div>
        `).join('');

        // Position and show suggestions
        const searchWrapper = document.querySelector('.search-input-wrapper');
        if (searchWrapper) {
            searchWrapper.style.position = 'relative';
            searchWrapper.appendChild(suggestionsContainer);

            // Handle suggestion clicks
            suggestionsContainer.addEventListener('click', (e) => {
                const suggestionItem = e.target.closest('.suggestion-item');
                if (suggestionItem) {
                    const searchInput = document.querySelector('.search-input-large');
                    if (searchInput) {
                        searchInput.value = suggestionItem.dataset.value;
                        this.submitFilters();
                    }
                }
            });

            // Remove suggestions when clicking outside
            document.addEventListener('click', (e) => {
                if (!searchWrapper.contains(e.target)) {
                    suggestionsContainer.remove();
                }
            }, { once: true });
        }
    }

    debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }
}

// Initialize products page
const productsPage = new ProductsPage();

// Additional CSS for modal content
const additionalStyles = `
<style>
.product-details {
    font-family: var(--font-primary);
}

.product-details-header {
    display: grid;
    grid-template-columns: 200px 1fr;
    gap: 2rem;
    margin-bottom: 2rem;
    padding-bottom: 2rem;
    border-bottom: 1px solid #eee;
}

.product-details-image {
    position: relative;
}

.product-details-image img {
    width: 100%;
    height: 200px;
    object-fit: cover;
    border-radius: 12px;
}

.placeholder-large {
    width: 100%;
    height: 200px;
    background: #f0f0f0;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 3rem;
    color: #ccc;
}

.prescription-badge-large {
    position: absolute;
    top: 10px;
    right: 10px;
    background: var(--warning-orange);
    color: white;
    padding: 8px 12px;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 600;
}

.product-category-large {
    font-size: 0.9rem;
    color: var(--accent-purple);
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 0.5rem;
}

.product-name-large {
    font-size: 1.5rem;
    font-weight: 700;
    color: var(--primary-blue);
    margin-bottom: 0.5rem;
    font-family: var(--font-heading);
}

.product-generic-large {
    font-size: 1rem;
    color: #666;
    font-style: italic;
    margin-bottom: 1rem;
}

.product-price-large {
    display: flex;
    align-items: baseline;
    gap: 0.25rem;
    margin-bottom: 1rem;
}

.product-price-large .amount {
    font-size: 2rem;
    font-weight: 700;
    color: var(--success-green);
}

.product-price-large .unit {
    font-size: 0.9rem;
    color: #888;
}

.details-section {
    margin-bottom: 2rem;
}

.details-section h3 {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 1.1rem;
    color: var(--primary-blue);
    margin-bottom: 1rem;
    font-family: var(--font-heading);
}

.details-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
}

.detail-item {
    display: flex;
    justify-content: space-between;
    padding: 0.75rem;
    background: #f8f9fa;
    border-radius: 8px;
}

.detail-item label {
    font-weight: 600;
    color: #666;
}

.product-description {
    line-height: 1.6;
    color: #555;
}

.safety-info {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.safety-item {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.75rem;
    border-radius: 8px;
}

.safety-item.warning {
    background: rgba(245, 158, 11, 0.1);
    color: var(--warning-orange);
}

.safety-item.success {
    background: rgba(16, 185, 129, 0.1);
    color: var(--success-green);
}

.safety-item.info {
    background: rgba(37, 99, 235, 0.1);
    color: var(--primary-blue);
}

.product-details-footer {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 2rem;
    padding-top: 2rem;
    border-top: 1px solid #eee;
}

.quantity-selector {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.quantity-selector label {
    font-weight: 600;
    color: #666;
}

.quantity-controls-large {
    display: flex;
    align-items: center;
    gap: 0;
    border: 2px solid #ddd;
    border-radius: 25px;
    overflow: hidden;
}

.qty-btn-large {
    width: 40px;
    height: 40px;
    border: none;
    background: #f8f9fa;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all var(--transition-fast);
}

.qty-btn-large:hover {
    background: var(--primary-blue);
    color: white;
}

.quantity-input {
    width: 60px;
    height: 40px;
    border: none;
    text-align: center;
    font-weight: 600;
    outline: none;
}

.btn-large {
    padding: 1rem 2rem;
    font-size: 1.1rem;
}

.loading-spinner {
    text-align: center;
    padding: 3rem;
    color: #666;
}

.loading-spinner i {
    font-size: 2rem;
    margin-bottom: 1rem;
    color: var(--primary-blue);
}

.error-message {
    text-align: center;
    padding: 3rem;
    color: #666;
}

.error-message i {
    font-size: 3rem;
    color: var(--error-red);
    margin-bottom: 1rem;
}

.search-suggestions {
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    background: white;
    border: 1px solid #ddd;
    border-radius: 0 0 12px 12px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.1);
    z-index: 100;
    max-height: 300px;
    overflow-y: auto;
}

.suggestion-item {
    padding: 1rem;
    border-bottom: 1px solid #eee;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 0.75rem;
    transition: background var(--transition-fast);
}

.suggestion-item:hover {
    background: #f8f9fa;
}

.suggestion-item:last-child {
    border-bottom: none;
}

.suggestion-item i {
    color: var(--primary-blue);
}

.suggestion-item small {
    color: #666;
    font-style: italic;
    margin-left: auto;
}

@media (max-width: 768px) {
    .product-details-header {
        grid-template-columns: 1fr;
        text-align: center;
    }
    
    .product-details-footer {
        flex-direction: column;
        align-items: stretch;
    }
    
    .quantity-selector {
        justify-content: center;
    }
    
    .details-grid {
        grid-template-columns: 1fr;
    }
}
</style>
`;

// Inject additional styles
document.head.insertAdjacentHTML('beforeend', additionalStyles);