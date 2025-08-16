// Main JavaScript functionality for PharmaCare Customer Website

// Global variables
let currentUser = null;
let featuredProducts = [];

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    initializeApp();
    loadFeaturedProducts();
    setupEventListeners();
    checkAuthStatus();
});

// Initialize application
function initializeApp() {
    // Check if user is logged in
    const token = localStorage.getItem('customer_token');
    if (token) {
        isLoggedIn = true;
        updateAuthUI();
    }
    
    // Initialize cart
    updateCartCount();
    
    // Setup navbar scroll effect
    setupNavbarScroll();
    
    // Setup mobile menu
    setupMobileMenu();
}

// Setup event listeners
function setupEventListeners() {
    // Search functionality
    const headerSearch = document.getElementById('headerSearch');
    if (headerSearch) {
        let searchTimeout;
        headerSearch.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                if (this.value.length >= 2) {
                    performSearch(this.value);
                }
            }, 300);
        });
    }
    
    // Modal event listeners
    setupModalListeners();
    
    // Form submissions
    setupFormSubmissions();
    
    // Prescription upload
    setupPrescriptionUpload();
}

// Setup navbar scroll effect
function setupNavbarScroll() {
    const navbar = document.getElementById('navbar');
    
    window.addEventListener('scroll', function() {
        if (window.scrollY > 50) {
            navbar.classList.add('scrolled');
        } else {
            navbar.classList.remove('scrolled');
        }
    });
}

// Setup mobile menu
function setupMobileMenu() {
    const mobileMenuBtn = document.getElementById('mobile-menu-btn');
    const navMenu = document.getElementById('nav-menu');
    
    if (mobileMenuBtn && navMenu) {
        mobileMenuBtn.addEventListener('click', function() {
            navMenu.classList.toggle('active');
        });
    }
}

// Load featured products
function loadFeaturedProducts() {
    showLoading();
    
    fetch('/api/customer/search_products.php?limit=8')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                featuredProducts = data.products;
                displayFeaturedProducts(data.products);
            } else {
                console.error('Error loading featured products:', data.message);
            }
        })
        .catch(error => {
            console.error('Error loading featured products:', error);
        })
        .finally(() => {
            hideLoading();
        });
}

// Display featured products
function displayFeaturedProducts(products) {
    const carousel = document.getElementById('productCarousel');
    if (!carousel) return;
    
    carousel.innerHTML = products.map(product => `
        <div class="product-card">
            <img src="${product.image || 'assets/images/default-medicine.jpg'}" 
                 alt="${product.name}" class="product-image"
                 onerror="this.src='assets/images/default-medicine.jpg'">
            <div class="product-info">
                <h3 class="product-name">${product.name}</h3>
                ${product.generic_name ? `<p class="product-description">${product.generic_name}</p>` : ''}
                <div class="product-price">Rs ${parseFloat(product.selling_price).toFixed(2)}</div>
                ${product.prescription_required ? '<span class="prescription-badge">Prescription Required</span>' : ''}
                <button class="add-to-cart-btn" onclick="quickAddToCart(this, ${product.id})" 
                        ${!product.in_stock ? 'disabled' : ''}>
                    <i class="fas fa-cart-plus"></i>
                    ${product.in_stock ? 'Add to Cart' : 'Out of Stock'}
                </button>
            </div>
        </div>
    `).join('');
    
    // Setup carousel navigation
    setupCarousel();
}

// Setup carousel navigation
function setupCarousel() {
    const track = document.getElementById('productCarousel');
    const prevBtn = document.getElementById('prevBtn');
    const nextBtn = document.getElementById('nextBtn');
    
    if (!track || !prevBtn || !nextBtn) return;
    
    let currentIndex = 0;
    const cardWidth = 300; // Including gap
    const visibleCards = Math.floor(track.parentElement.offsetWidth / cardWidth);
    const maxIndex = Math.max(0, featuredProducts.length - visibleCards);
    
    prevBtn.addEventListener('click', function() {
        if (currentIndex > 0) {
            currentIndex--;
            track.style.transform = `translateX(-${currentIndex * cardWidth}px)`;
        }
    });
    
    nextBtn.addEventListener('click', function() {
        if (currentIndex < maxIndex) {
            currentIndex++;
            track.style.transform = `translateX(-${currentIndex * cardWidth}px)`;
        }
    });
}

// Perform search
function performSearch(query) {
    // Redirect to products page with search query
    window.location.href = `products.html?search=${encodeURIComponent(query)}`;
}

// Modal functions
function openModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.add('active');
        document.body.style.overflow = 'hidden';
    }
}

function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.remove('active');
        document.body.style.overflow = 'auto';
    }
}

function switchModal(fromModalId, toModalId) {
    closeModal(fromModalId);
    setTimeout(() => openModal(toModalId), 300);
}

// Setup modal listeners
function setupModalListeners() {
    // Close modal when clicking outside
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('modal')) {
            const modalId = e.target.id;
            closeModal(modalId);
        }
    });
    
    // Close modal with Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            const activeModal = document.querySelector('.modal.active');
            if (activeModal) {
                closeModal(activeModal.id);
            }
        }
    });
}

// Setup form submissions
function setupFormSubmissions() {
    // Login form
    const loginForm = document.getElementById('loginForm');
    if (loginForm) {
        loginForm.addEventListener('submit', handleLogin);
    }
    
    // Register form
    const registerForm = document.getElementById('registerForm');
    if (registerForm) {
        registerForm.addEventListener('submit', handleRegister);
    }
    
    // Prescription form
    const prescriptionForm = document.getElementById('prescriptionForm');
    if (prescriptionForm) {
        prescriptionForm.addEventListener('submit', handlePrescriptionUpload);
    }
}

// Handle login
function handleLogin(e) {
    e.preventDefault();
    
    const email = document.getElementById('loginEmail').value;
    const password = document.getElementById('loginPassword').value;
    
    showLoading();
    
    fetch('/api/customer/login.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ email, password })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            localStorage.setItem('customer_token', data.token);
            localStorage.setItem('customer_data', JSON.stringify(data.user));
            currentUser = data.user;
            isLoggedIn = true;
            
            updateAuthUI();
            closeModal('loginModal');
            showNotification('Login successful!', 'success');
        } else {
            showNotification(data.message || 'Login failed', 'error');
        }
    })
    .catch(error => {
        console.error('Login error:', error);
        showNotification('Login failed. Please try again.', 'error');
    })
    .finally(() => {
        hideLoading();
    });
}

// Handle registration
function handleRegister(e) {
    e.preventDefault();
    
    const name = document.getElementById('registerName').value;
    const email = document.getElementById('registerEmail').value;
    const phone = document.getElementById('registerPhone').value;
    const password = document.getElementById('registerPassword').value;
    
    showLoading();
    
    fetch('/api/customer/register.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ name, email, phone, password })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Registration successful! Please login.', 'success');
            switchModal('registerModal', 'loginModal');
        } else {
            showNotification(data.message || 'Registration failed', 'error');
        }
    })
    .catch(error => {
        console.error('Registration error:', error);
        showNotification('Registration failed. Please try again.', 'error');
    })
    .finally(() => {
        hideLoading();
    });
}

// Setup prescription upload
function setupPrescriptionUpload() {
    const uploadArea = document.getElementById('uploadArea');
    const fileInput = document.getElementById('prescriptionFile');
    
    if (uploadArea && fileInput) {
        uploadArea.addEventListener('click', () => fileInput.click());
        
        uploadArea.addEventListener('dragover', (e) => {
            e.preventDefault();
            uploadArea.classList.add('dragover');
        });
        
        uploadArea.addEventListener('dragleave', () => {
            uploadArea.classList.remove('dragover');
        });
        
        uploadArea.addEventListener('drop', (e) => {
            e.preventDefault();
            uploadArea.classList.remove('dragover');
            
            const files = e.dataTransfer.files;
            if (files.length > 0) {
                fileInput.files = files;
                updateUploadArea(files[0]);
            }
        });
        
        fileInput.addEventListener('change', function() {
            if (this.files.length > 0) {
                updateUploadArea(this.files[0]);
            }
        });
    }
}

// Update upload area display
function updateUploadArea(file) {
    const uploadArea = document.getElementById('uploadArea');
    if (!uploadArea) return;
    
    uploadArea.innerHTML = `
        <i class="fas fa-file-image"></i>
        <p><strong>${file.name}</strong></p>
        <p>Size: ${formatFileSize(file.size)}</p>
        <p class="text-success">File selected successfully</p>
    `;
}

// Handle prescription upload
function handlePrescriptionUpload(e) {
    e.preventDefault();
    
    const patientName = document.getElementById('patientName').value;
    const doctorName = document.getElementById('doctorName').value;
    const fileInput = document.getElementById('prescriptionFile');
    
    if (!fileInput.files.length) {
        showNotification('Please select a prescription file', 'warning');
        return;
    }
    
    const formData = new FormData();
    formData.append('patient_name', patientName);
    formData.append('doctor_name', doctorName);
    formData.append('prescription_file', fileInput.files[0]);
    
    showLoading();
    
    fetch('/api/customer/upload_prescription.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Prescription uploaded successfully!', 'success');
            closeModal('prescriptionModal');
            document.getElementById('prescriptionForm').reset();
        } else {
            showNotification(data.message || 'Upload failed', 'error');
        }
    })
    .catch(error => {
        console.error('Upload error:', error);
        showNotification('Upload failed. Please try again.', 'error');
    })
    .finally(() => {
        hideLoading();
    });
}

// Check authentication status
function checkAuthStatus() {
    const token = localStorage.getItem('customer_token');
    if (token) {
        // Verify token with server
        fetch('/api/customer/verify_token.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': `Bearer ${token}`
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                currentUser = data.user;
                isLoggedIn = true;
                updateAuthUI();
            } else {
                // Token invalid, clear storage
                localStorage.removeItem('customer_token');
                localStorage.removeItem('customer_data');
                isLoggedIn = false;
            }
        })
        .catch(error => {
            console.error('Token verification error:', error);
        });
    }
}

// Update authentication UI
function updateAuthUI() {
    const loginBtn = document.querySelector('.nav-btn');
    if (loginBtn && currentUser) {
        loginBtn.innerHTML = `
            <i class="fas fa-user-circle"></i>
            <span>Hi, ${currentUser.name.split(' ')[0]}</span>
        `;
        loginBtn.onclick = () => showUserMenu();
    }
}

// Show user menu
function showUserMenu() {
    // Create user menu dropdown
    const menu = document.createElement('div');
    menu.className = 'user-menu-dropdown';
    menu.innerHTML = `
        <a href="profile.html"><i class="fas fa-user"></i> My Profile</a>
        <a href="orders.html"><i class="fas fa-shopping-bag"></i> My Orders</a>
        <a href="prescriptions.html"><i class="fas fa-file-medical"></i> My Prescriptions</a>
        <hr>
        <a href="#" onclick="logout()"><i class="fas fa-sign-out-alt"></i> Logout</a>
    `;
    
    // Position and show menu
    document.body.appendChild(menu);
    
    // Remove menu when clicking outside
    setTimeout(() => {
        document.addEventListener('click', function removeMenu() {
            menu.remove();
            document.removeEventListener('click', removeMenu);
        });
    }, 100);
}

// Logout function
function logout() {
    localStorage.removeItem('customer_token');
    localStorage.removeItem('customer_data');
    currentUser = null;
    isLoggedIn = false;
    
    // Reset UI
    const loginBtn = document.querySelector('.nav-btn');
    if (loginBtn) {
        loginBtn.innerHTML = `
            <i class="fas fa-user"></i>
            <span>Login</span>
        `;
        loginBtn.onclick = () => openModal('loginModal');
    }
    
    showNotification('Logged out successfully', 'info');
}

// Cart functions
function openCart() {
    const cartSidebar = document.getElementById('cartSidebar');
    if (cartSidebar) {
        cartSidebar.classList.add('active');
        updateCartUI();
    }
}

function closeCart() {
    const cartSidebar = document.getElementById('cartSidebar');
    if (cartSidebar) {
        cartSidebar.classList.remove('active');
    }
}

// Utility functions
function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}

function showLoading() {
    const spinner = document.getElementById('loadingSpinner');
    if (spinner) {
        spinner.classList.add('active');
    }
}

function hideLoading() {
    const spinner = document.getElementById('loadingSpinner');
    if (spinner) {
        spinner.classList.remove('active');
    }
}

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

// Add to favorites functionality
function toggleFavorite(productId) {
    if (!isLoggedIn) {
        showNotification('Please login to add favorites', 'warning');
        openModal('loginModal');
        return;
    }
    
    // Toggle favorite status
    fetch('/api/customer/toggle_favorite.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Authorization': `Bearer ${localStorage.getItem('customer_token')}`
        },
        body: JSON.stringify({ product_id: productId })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification(data.message, 'success');
            // Update UI to reflect favorite status
            updateFavoriteButton(productId, data.is_favorite);
        } else {
            showNotification(data.message || 'Error updating favorites', 'error');
        }
    })
    .catch(error => {
        console.error('Favorite toggle error:', error);
        showNotification('Error updating favorites', 'error');
    });
}

function updateFavoriteButton(productId, isFavorite) {
    const favoriteBtn = document.querySelector(`[data-product-id="${productId}"] .favorite-btn`);
    if (favoriteBtn) {
        favoriteBtn.innerHTML = isFavorite ? 
            '<i class="fas fa-heart"></i>' : 
            '<i class="far fa-heart"></i>';
        favoriteBtn.classList.toggle('active', isFavorite);
    }
}