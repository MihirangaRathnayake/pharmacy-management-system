// Shopping Cart functionality for PharmaCare Customer Website

// Cart state
let cart = JSON.parse(localStorage.getItem('cart') || '[]');
let isLoggedIn = localStorage.getItem('customer_token') !== null;

// Initialize cart on page load
document.addEventListener('DOMContentLoaded', function() {
    updateCartUI();
    updateCartCount();
});

// Add to cart function
function addToCart(productId, quantity = 1) {
    showLoading();
    
    // Fetch product details from API
    fetch(`/api/customer/get_product.php?id=${productId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const product = data.product;
                
                // Check if prescription required and user not logged in
                if (product.prescription_required && !isLoggedIn) {
                    hideLoading();
                    showNotification('Please login to purchase prescription medicines', 'warning');
                    return;
                }
                
                // Check stock availability
                if (product.stock_quantity < quantity) {
                    hideLoading();
                    showNotification(`Only ${product.stock_quantity} items available in stock`, 'error');
                    return;
                }
                
                // Check if product already in cart
                const existingItem = cart.find(item => item.id === productId);
                
                if (existingItem) {
                    const newQuantity = existingItem.quantity + quantity;
                    if (newQuantity > product.stock_quantity) {
                        hideLoading();
                        showNotification('Cannot add more items. Stock limit reached.', 'error');
                        return;
                    }
                    existingItem.quantity = newQuantity;
                } else {
                    cart.push({
                        id: product.id,
                        name: product.name,
                        generic_name: product.generic_name,
                        price: parseFloat(product.selling_price),
                        image: product.image || 'assets/images/default-medicine.jpg',
                        prescription_required: product.prescription_required,
                        quantity: quantity,
                        stock_quantity: product.stock_quantity
                    });
                }
                
                // Save to localStorage
                localStorage.setItem('cart', JSON.stringify(cart));
                
                // Update UI
                updateCartUI();
                updateCartCount();
                
                hideLoading();
                showNotification(`${product.name} added to cart!`, 'success');
                
                // Show mini cart preview
                showMiniCart();
            } else {
                hideLoading();
                showNotification(data.message || 'Product not found!', 'error');
            }
        })
        .catch(error => {
            console.error('Error adding to cart:', error);
            hideLoading();
            showNotification('Error adding product to cart', 'error');
        });
}

// Remove from cart
function removeFromCart(productId) {
    cart = cart.filter(item => item.id !== productId);
    localStorage.setItem('cart', JSON.stringify(cart));
    updateCartUI();
    updateCartCount();
    showNotification('Item removed from cart', 'info');
}

// Update quantity in cart
function updateQuantity(productId, newQuantity) {
    const item = cart.find(item => item.id === productId);
    
    if (!item) return;
    
    if (newQuantity <= 0) {
        removeFromCart(productId);
        return;
    }
    
    if (newQuantity > item.stock_quantity) {
        showNotification('Cannot add more items. Stock limit reached.', 'error');
        return;
    }
    
    item.quantity = newQuantity;
    localStorage.setItem('cart', JSON.stringify(cart));
    updateCartUI();
    updateCartCount();
}

// Clear entire cart
function clearCart() {
    if (confirm('Are you sure you want to clear your cart?')) {
        cart = [];
        localStorage.setItem('cart', JSON.stringify(cart));
        updateCartUI();
        updateCartCount();
        showNotification('Cart cleared', 'info');
    }
}

// Update cart UI
function updateCartUI() {
    const cartContainer = document.getElementById('cartItems');
    const cartTotal = document.getElementById('cartTotal');
    const cartSubtotal = document.getElementById('cartSubtotal');
    const cartTax = document.getElementById('cartTax');
    const emptyCartMessage = document.getElementById('emptyCart');
    
    if (!cartContainer) return;
    
    if (cart.length === 0) {
        if (emptyCartMessage) emptyCartMessage.style.display = 'block';
        if (cartContainer) cartContainer.innerHTML = '';
        if (cartTotal) cartTotal.textContent = 'Rs 0.00';
        if (cartSubtotal) cartSubtotal.textContent = 'Rs 0.00';
        if (cartTax) cartTax.textContent = 'Rs 0.00';
        return;
    }
    
    if (emptyCartMessage) emptyCartMessage.style.display = 'none';
    
    // Calculate totals
    const subtotal = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
    const taxAmount = subtotal * 0.18; // 18% GST
    const total = subtotal + taxAmount;
    
    // Update cart items
    cartContainer.innerHTML = cart.map(item => `
        <div class="cart-item bg-white rounded-lg shadow-md p-4 mb-4" data-id="${item.id}">
            <div class="flex items-center space-x-4">
                <img src="${item.image}" alt="${item.name}" class="w-16 h-16 object-cover rounded-lg" 
                     onerror="this.src='assets/images/default-medicine.jpg'">
                <div class="flex-1">
                    <h3 class="font-semibold text-gray-800">${item.name}</h3>
                    ${item.generic_name ? `<p class="text-sm text-gray-500">${item.generic_name}</p>` : ''}
                    ${item.prescription_required ? '<span class="inline-block bg-red-100 text-red-800 text-xs px-2 py-1 rounded-full mt-1">Prescription Required</span>' : ''}
                    <p class="text-green-600 font-medium mt-1">Rs ${item.price.toFixed(2)} each</p>
                </div>
                <div class="flex items-center space-x-3">
                    <button onclick="updateQuantity(${item.id}, ${item.quantity - 1})" 
                            class="w-8 h-8 bg-gray-200 hover:bg-gray-300 rounded-full flex items-center justify-center transition duration-200">
                        <i class="fas fa-minus text-sm"></i>
                    </button>
                    <span class="w-12 text-center font-medium">${item.quantity}</span>
                    <button onclick="updateQuantity(${item.id}, ${item.quantity + 1})" 
                            class="w-8 h-8 bg-green-500 hover:bg-green-600 text-white rounded-full flex items-center justify-center transition duration-200">
                        <i class="fas fa-plus text-sm"></i>
                    </button>
                </div>
                <div class="text-right">
                    <p class="font-bold text-gray-800">Rs ${(item.price * item.quantity).toFixed(2)}</p>
                    <button onclick="removeFromCart(${item.id})" 
                            class="text-red-500 hover:text-red-700 text-sm mt-1 transition duration-200">
                        <i class="fas fa-trash mr-1"></i>Remove
                    </button>
                </div>
            </div>
        </div>
    `).join('');
    
    // Update totals
    if (cartSubtotal) cartSubtotal.textContent = `Rs ${subtotal.toFixed(2)}`;
    if (cartTax) cartTax.textContent = `Rs ${taxAmount.toFixed(2)}`;
    if (cartTotal) cartTotal.textContent = `Rs ${total.toFixed(2)}`;
}

// Update cart count in header
function updateCartCount() {
    const cartCount = document.getElementById('cartCount');
    const totalItems = cart.reduce((sum, item) => sum + item.quantity, 0);
    
    if (cartCount) {
        cartCount.textContent = totalItems;
        cartCount.style.display = totalItems > 0 ? 'block' : 'none';
    }
}

// Show mini cart preview
function showMiniCart() {
    const miniCart = document.getElementById('miniCart');
    if (miniCart) {
        miniCart.classList.remove('hidden');
        setTimeout(() => {
            miniCart.classList.add('hidden');
        }, 3000);
    }
}

// Proceed to checkout
function proceedToCheckout() {
    if (cart.length === 0) {
        showNotification('Your cart is empty', 'warning');
        return;
    }
    
    // Check if any prescription medicines require login
    const prescriptionItems = cart.filter(item => item.prescription_required);
    if (prescriptionItems.length > 0 && !isLoggedIn) {
        showNotification('Please login to purchase prescription medicines', 'warning');
        window.location.href = 'login.html';
        return;
    }
    
    // Redirect to checkout page
    window.location.href = 'checkout.html';
}

// Apply coupon code
function applyCoupon() {
    const couponCode = document.getElementById('couponCode').value.trim();
    
    if (!couponCode) {
        showNotification('Please enter a coupon code', 'warning');
        return;
    }
    
    showLoading();
    
    fetch('/api/customer/apply_coupon.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ 
            coupon_code: couponCode,
            cart_total: cart.reduce((sum, item) => sum + (item.price * item.quantity), 0)
        })
    })
    .then(response => response.json())
    .then(data => {
        hideLoading();
        if (data.success) {
            // Apply discount to cart
            localStorage.setItem('applied_coupon', JSON.stringify(data.coupon));
            updateCartUI();
            showNotification(`Coupon applied! You saved Rs ${data.discount_amount}`, 'success');
        } else {
            showNotification(data.message || 'Invalid coupon code', 'error');
        }
    })
    .catch(error => {
        console.error('Error applying coupon:', error);
        hideLoading();
        showNotification('Error applying coupon', 'error');
    });
}

// Utility functions
function showLoading() {
    const loader = document.getElementById('loader');
    if (loader) loader.style.display = 'flex';
}

function hideLoading() {
    const loader = document.getElementById('loader');
    if (loader) loader.style.display = 'none';
}

function showNotification(message, type = 'info') {
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `fixed top-4 right-4 z-50 p-4 rounded-lg shadow-lg max-w-sm transform transition-all duration-300 translate-x-full`;
    
    // Set notification style based on type
    const styles = {
        success: 'bg-green-500 text-white',
        error: 'bg-red-500 text-white',
        warning: 'bg-yellow-500 text-white',
        info: 'bg-blue-500 text-white'
    };
    
    notification.className += ` ${styles[type] || styles.info}`;
    
    // Set notification content
    notification.innerHTML = `
        <div class="flex items-center space-x-2">
            <i class="fas ${type === 'success' ? 'fa-check-circle' : type === 'error' ? 'fa-exclamation-circle' : type === 'warning' ? 'fa-exclamation-triangle' : 'fa-info-circle'}"></i>
            <span>${message}</span>
            <button onclick="this.parentElement.parentElement.remove()" class="ml-2 text-white hover:text-gray-200">
                <i class="fas fa-times"></i>
            </button>
        </div>
    `;
    
    // Add to page
    document.body.appendChild(notification);
    
    // Animate in
    setTimeout(() => {
        notification.classList.remove('translate-x-full');
    }, 100);
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        notification.classList.add('translate-x-full');
        setTimeout(() => {
            if (notification.parentElement) {
                notification.remove();
            }
        }, 300);
    }, 5000);
}

// Quick add to cart from product cards
function quickAddToCart(button, productId) {
    const originalText = button.innerHTML;
    button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Adding...';
    button.disabled = true;
    
    addToCart(productId, 1);
    
    setTimeout(() => {
        button.innerHTML = originalText;
        button.disabled = false;
    }, 1000);
}

// Search products
function searchProducts(query) {
    if (query.length < 2) return;
    
    fetch(`/api/customer/search_products.php?q=${encodeURIComponent(query)}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displaySearchResults(data.products);
            }
        })
        .catch(error => {
            console.error('Search error:', error);
        });
}

// Display search results
function displaySearchResults(products) {
    const resultsContainer = document.getElementById('searchResults');
    if (!resultsContainer) return;
    
    if (products.length === 0) {
        resultsContainer.innerHTML = '<p class="text-gray-500 text-center py-4">No products found</p>';
        return;
    }
    
    resultsContainer.innerHTML = products.map(product => `
        <div class="product-card bg-white rounded-lg shadow-md p-4 hover:shadow-lg transition duration-200">
            <img src="${product.image || 'assets/images/default-medicine.jpg'}" 
                 alt="${product.name}" class="w-full h-32 object-cover rounded-lg mb-3"
                 onerror="this.src='assets/images/default-medicine.jpg'">
            <h3 class="font-semibold text-gray-800 mb-1">${product.name}</h3>
            ${product.generic_name ? `<p class="text-sm text-gray-500 mb-2">${product.generic_name}</p>` : ''}
            <div class="flex justify-between items-center mb-3">
                <span class="text-green-600 font-bold">Rs ${parseFloat(product.selling_price).toFixed(2)}</span>
                ${product.prescription_required ? '<span class="text-xs bg-red-100 text-red-800 px-2 py-1 rounded">Rx</span>' : ''}
            </div>
            <button onclick="quickAddToCart(this, ${product.id})" 
                    class="w-full bg-green-500 hover:bg-green-600 text-white py-2 px-4 rounded-lg transition duration-200">
                <i class="fas fa-cart-plus mr-2"></i>Add to Cart
            </button>
        </div>
    `).join('');
}