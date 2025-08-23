// Main JavaScript for PharmaCare Customer Website

class PharmaCare {
    constructor() {
        this.cart = JSON.parse(localStorage.getItem('pharmacare_cart')) || [];
        this.init();
    }

    init() {
        this.setupEventListeners();
        this.updateCartUI();
        this.setupSearch();
        this.setupAnimations();
    }

    setupEventListeners() {
        // Navigation toggle
        const navToggle = document.getElementById('navToggle');
        const navMenu = document.getElementById('navMenu');
        
        if (navToggle) {
            navToggle.addEventListener('click', () => {
                navMenu.classList.toggle('active');
            });
        }

        // Cart functionality
        const cartBtn = document.getElementById('cartBtn');
        const cartSidebar = document.getElementById('cartSidebar');
        const cartOverlay = document.getElementById('cartOverlay');
        const closeCart = document.getElementById('closeCart');

        if (cartBtn) {
            cartBtn.addEventListener('click', () => this.toggleCart());
        }

        if (closeCart) {
            closeCart.addEventListener('click', () => this.closeCart());
        }

        if (cartOverlay) {
            cartOverlay.addEventListener('click', () => this.closeCart());
        }

        // Add to cart buttons
        document.addEventListener('click', (e) => {
            if (e.target.classList.contains('add-to-cart') || e.target.closest('.add-to-cart')) {
                const btn = e.target.classList.contains('add-to-cart') ? e.target : e.target.closest('.add-to-cart');
                const productId = btn.dataset.id;
                this.addToCart(productId);
            }
        });

        // User menu dropdown
        const userBtn = document.getElementById('userBtn');
        const userDropdown = document.getElementById('userDropdown');
        
        if (userBtn && userDropdown) {
            userBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                userDropdown.classList.toggle('active');
            });

            document.addEventListener('click', () => {
                userDropdown.classList.remove('active');
            });
        }

        // Checkout button
        const checkoutBtn = document.getElementById('checkoutBtn');
        if (checkoutBtn) {
            checkoutBtn.addEventListener('click', () => this.checkout());
        }
    }

    setupSearch() {
        const searchInputs = document.querySelectorAll('.search-input');
        
        searchInputs.forEach(input => {
            input.addEventListener('input', (e) => {
                this.debounce(() => this.performSearch(e.target.value), 300)();
            });

            input.addEventListener('keypress', (e) => {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    this.performSearch(e.target.value);
                }
            });
        });

        // Search button click
        document.querySelectorAll('.search-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                const input = btn.parentElement.querySelector('.search-input');
                if (input) {
                    this.performSearch(input.value);
                }
            });
        });
    }

    setupAnimations() {
        // Intersection Observer for fade-in animations
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('fade-in');
                }
            });
        }, observerOptions);

        // Observe all cards and sections
        document.querySelectorAll('.glass-card, .feature-card, .category-card, .product-card').forEach(el => {
            observer.observe(el);
        });

        // Parallax effect for hero section
        window.addEventListener('scroll', () => {
            const scrolled = window.pageYOffset;
            const hero = document.querySelector('.hero');
            if (hero) {
                hero.style.transform = `translateY(${scrolled * 0.5}px)`;
            }
        });
    }

    async addToCart(productId) {
        try {
            // Show loading state
            const btn = document.querySelector(`[data-id="${productId}"].add-to-cart`);
            if (btn) {
                btn.classList.add('loading');
                btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Adding...';
            }

            // Try to fetch product details, but fallback to demo data
            let productData = null;
            
            try {
                const response = await fetch(`../api/get_medicine.php?id=${productId}`);
                const product = await response.json();
                if (product.success) {
                    productData = product.data;
                }
            } catch (apiError) {
                console.log('API not available, using demo data');
            }

            // Fallback demo data
            if (!productData) {
                const demoProducts = {
                    '1': { name: 'Paracetamol 500mg', selling_price: 5.00, image: null, prescription_required: false },
                    '2': { name: 'Amoxicillin 250mg', selling_price: 25.00, image: null, prescription_required: true },
                    '3': { name: 'Vitamin C 1000mg', selling_price: 15.00, image: null, prescription_required: false },
                    '4': { name: 'Cough Syrup', selling_price: 20.00, image: null, prescription_required: false }
                };
                productData = demoProducts[productId] || { name: 'Unknown Product', selling_price: 0, image: null, prescription_required: false };
            }

            const existingItem = this.cart.find(item => item.id === productId);
            
            if (existingItem) {
                existingItem.quantity += 1;
            } else {
                this.cart.push({
                    id: productId,
                    name: productData.name,
                    price: parseFloat(productData.selling_price),
                    image: productData.image,
                    quantity: 1,
                    prescription_required: productData.prescription_required || false
                });
            }

            this.saveCart();
            this.updateCartUI();
            this.showNotification('Product added to cart!', 'success');
            
            // Animate button
            if (btn) {
                btn.classList.remove('loading');
                btn.innerHTML = '<i class="fas fa-check"></i> Added!';
                btn.style.background = 'var(--gradient-success)';
                
                setTimeout(() => {
                    btn.innerHTML = '<i class="fas fa-cart-plus"></i> Add to Cart';
                    btn.style.background = '';
                }, 2000);
            }

        } catch (error) {
            console.error('Error adding to cart:', error);
            this.showNotification('Failed to add product to cart', 'error');
            
            // Reset button
            const btn = document.querySelector(`[data-id="${productId}"].add-to-cart`);
            if (btn) {
                btn.classList.remove('loading');
                btn.innerHTML = '<i class="fas fa-cart-plus"></i> Add to Cart';
            }
        }
    }

    removeFromCart(productId) {
        this.cart = this.cart.filter(item => item.id !== productId);
        this.saveCart();
        this.updateCartUI();
        this.showNotification('Product removed from cart', 'info');
    }

    updateQuantity(productId, quantity) {
        const item = this.cart.find(item => item.id === productId);
        if (item) {
            if (quantity <= 0) {
                this.removeFromCart(productId);
            } else {
                item.quantity = quantity;
                this.saveCart();
                this.updateCartUI();
            }
        }
    }

    updateCartUI() {
        const cartCount = document.getElementById('cartCount');
        const cartItems = document.getElementById('cartItems');
        const cartTotal = document.getElementById('cartTotal');

        // Update cart count
        const totalItems = this.cart.reduce((sum, item) => sum + item.quantity, 0);
        if (cartCount) {
            cartCount.textContent = totalItems;
            cartCount.style.display = totalItems > 0 ? 'flex' : 'none';
        }

        // Update cart items
        if (cartItems) {
            if (this.cart.length === 0) {
                cartItems.innerHTML = `
                    <div class="empty-cart">
                        <i class="fas fa-shopping-cart"></i>
                        <p>Your cart is empty</p>
                        <a href="products.php" class="btn btn-primary">Shop Now</a>
                    </div>
                `;
            } else {
                cartItems.innerHTML = this.cart.map(item => `
                    <div class="cart-item" data-id="${item.id}">
                        <div class="cart-item-image">
                            ${item.image ? 
                                `<img src="../uploads/medicines/${item.image}" alt="${item.name}">` :
                                `<div class="placeholder"><i class="fas fa-pills"></i></div>`
                            }
                        </div>
                        <div class="cart-item-details">
                            <h4>${item.name}</h4>
                            <div class="cart-item-price">Rs ${item.price.toFixed(2)}</div>
                            <div class="quantity-controls">
                                <button class="qty-btn" onclick="pharmacare.updateQuantity('${item.id}', ${item.quantity - 1})">
                                    <i class="fas fa-minus"></i>
                                </button>
                                <span class="quantity">${item.quantity}</span>
                                <button class="qty-btn" onclick="pharmacare.updateQuantity('${item.id}', ${item.quantity + 1})">
                                    <i class="fas fa-plus"></i>
                                </button>
                            </div>
                        </div>
                        <button class="remove-item" onclick="pharmacare.removeFromCart('${item.id}')">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                `).join('');
            }
        }

        // Update total
        const total = this.cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
        if (cartTotal) {
            cartTotal.textContent = total.toFixed(2);
        }
    }

    toggleCart() {
        const cartSidebar = document.getElementById('cartSidebar');
        const cartOverlay = document.getElementById('cartOverlay');
        
        if (cartSidebar && cartOverlay) {
            cartSidebar.classList.add('active');
            cartOverlay.classList.add('active');
            document.body.style.overflow = 'hidden';
        }
    }

    closeCart() {
        const cartSidebar = document.getElementById('cartSidebar');
        const cartOverlay = document.getElementById('cartOverlay');
        
        if (cartSidebar && cartOverlay) {
            cartSidebar.classList.remove('active');
            cartOverlay.classList.remove('active');
            document.body.style.overflow = '';
        }
    }

    async checkout() {
        if (this.cart.length === 0) {
            this.showNotification('Your cart is empty', 'warning');
            return;
        }

        // Check if user is logged in
        const response = await fetch('../api/check_auth.php');
        const authData = await response.json();

        if (!authData.authenticated) {
            this.showNotification('Please login to checkout', 'warning');
            setTimeout(() => {
                window.location.href = 'login.php';
            }, 1500);
            return;
        }

        // Redirect to checkout page
        window.location.href = 'checkout.php';
    }

    async performSearch(query) {
        if (query.trim().length < 2) return;

        try {
            const response = await fetch(`../api/search_medicines.php?q=${encodeURIComponent(query)}`);
            const data = await response.json();

            if (data.success) {
                this.displaySearchResults(data.medicines);
            }
        } catch (error) {
            console.error('Search error:', error);
        }
    }

    displaySearchResults(medicines) {
        // This would be implemented based on the current page
        // For now, redirect to products page with search query
        const currentPage = window.location.pathname.split('/').pop();
        if (currentPage !== 'products.php') {
            window.location.href = `products.php?search=${encodeURIComponent(medicines)}`;
        }
    }

    saveCart() {
        localStorage.setItem('pharmacare_cart', JSON.stringify(this.cart));
    }

    showNotification(message, type = 'info') {
        // Create notification element
        const notification = document.createElement('div');
        notification.className = `notification notification-${type}`;
        notification.innerHTML = `
            <div class="notification-content">
                <i class="fas fa-${this.getNotificationIcon(type)}"></i>
                <span>${message}</span>
            </div>
            <button class="notification-close">
                <i class="fas fa-times"></i>
            </button>
        `;

        // Add to page
        document.body.appendChild(notification);

        // Show notification
        setTimeout(() => notification.classList.add('show'), 100);

        // Auto remove
        setTimeout(() => {
            notification.classList.remove('show');
            setTimeout(() => notification.remove(), 300);
        }, 3000);

        // Manual close
        notification.querySelector('.notification-close').addEventListener('click', () => {
            notification.classList.remove('show');
            setTimeout(() => notification.remove(), 300);
        });
    }

    getNotificationIcon(type) {
        const icons = {
            success: 'check-circle',
            error: 'exclamation-circle',
            warning: 'exclamation-triangle',
            info: 'info-circle'
        };
        return icons[type] || 'info-circle';
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

// Initialize the application
const pharmacare = new PharmaCare();

// Additional utility functions
document.addEventListener('DOMContentLoaded', function() {
    // Smooth scrolling for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });

    // Lazy loading for images
    const images = document.querySelectorAll('img[data-src]');
    const imageObserver = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const img = entry.target;
                img.src = img.dataset.src;
                img.classList.remove('lazy');
                imageObserver.unobserve(img);
            }
        });
    });

    images.forEach(img => imageObserver.observe(img));

    // Form validation
    const forms = document.querySelectorAll('form[data-validate]');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            if (!validateForm(this)) {
                e.preventDefault();
            }
        });
    });
});

function validateForm(form) {
    let isValid = true;
    const inputs = form.querySelectorAll('input[required], select[required], textarea[required]');
    
    inputs.forEach(input => {
        if (!input.value.trim()) {
            showFieldError(input, 'This field is required');
            isValid = false;
        } else {
            clearFieldError(input);
        }
    });

    // Email validation
    const emailInputs = form.querySelectorAll('input[type="email"]');
    emailInputs.forEach(input => {
        if (input.value && !isValidEmail(input.value)) {
            showFieldError(input, 'Please enter a valid email address');
            isValid = false;
        }
    });

    return isValid;
}

function showFieldError(input, message) {
    clearFieldError(input);
    const error = document.createElement('div');
    error.className = 'field-error';
    error.textContent = message;
    input.parentNode.appendChild(error);
    input.classList.add('error');
}

function clearFieldError(input) {
    const error = input.parentNode.querySelector('.field-error');
    if (error) {
        error.remove();
    }
    input.classList.remove('error');
}

function isValidEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}

// Export for global access
window.pharmacare = pharmacare;