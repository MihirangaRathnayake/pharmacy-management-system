// Checkout page JavaScript

class CheckoutPage {
    constructor() {
        this.cart = JSON.parse(localStorage.getItem('pharmacare_cart')) || [];
        this.taxRate = 18; // 18% GST
        this.init();
    }

    init() {
        this.loadCartItems();
        this.calculateTotals();
        this.setupEventListeners();
        this.validateCart();
    }

    setupEventListeners() {
        // Form submission
        const checkoutForm = document.getElementById('checkoutForm');
        if (checkoutForm) {
            checkoutForm.addEventListener('submit', (e) => {
                e.preventDefault();
                this.processOrder();
            });
        }

        // Payment method selection
        const paymentMethods = document.querySelectorAll('input[name="payment_method"]');
        paymentMethods.forEach(method => {
            method.addEventListener('change', () => {
                this.updatePaymentInfo(method.value);
            });
        });

        // Real-time form validation
        const requiredInputs = document.querySelectorAll('input[required], textarea[required]');
        requiredInputs.forEach(input => {
            input.addEventListener('blur', () => this.validateField(input));
            input.addEventListener('input', () => this.clearFieldError(input));
        });
    }

    loadCartItems() {
        const cartItemsContainer = document.getElementById('checkoutCartItems');
        if (!cartItemsContainer) return;

        if (this.cart.length === 0) {
            cartItemsContainer.innerHTML = `
                <div class="empty-checkout-cart">
                    <i class="fas fa-shopping-cart"></i>
                    <h3>Your cart is empty</h3>
                    <p>Add some medicines to your cart to proceed with checkout</p>
                    <a href="products.php" class="btn btn-primary">
                        <i class="fas fa-pills"></i>
                        Browse Products
                    </a>
                </div>
            `;
            return;
        }

        const itemsHTML = this.cart.map(item => `
            <div class="checkout-cart-item" data-id="${item.id}">
                <div class="checkout-item-image">
                    ${item.image ? 
                        `<img src="../uploads/medicines/${item.image}" alt="${item.name}">` :
                        `<div class="placeholder"><i class="fas fa-pills"></i></div>`
                    }
                </div>
                <div class="checkout-item-details">
                    <div class="checkout-item-name">${item.name}</div>
                    ${item.generic_name ? `<div class="checkout-item-generic">${item.generic_name}</div>` : ''}
                    <div class="checkout-item-quantity">Quantity: ${item.quantity}</div>
                    ${item.prescription_required ? 
                        `<div class="prescription-badge">
                            <i class="fas fa-prescription"></i> Prescription Required
                        </div>` : ''
                    }
                </div>
                <div class="checkout-item-price">
                    <div class="item-unit-price">Rs ${item.price.toFixed(2)} each</div>
                    <div class="item-total-price">Rs ${(item.price * item.quantity).toFixed(2)}</div>
                </div>
            </div>
        `).join('');

        cartItemsContainer.innerHTML = itemsHTML;

        // Check for prescription required items
        this.checkPrescriptionRequirements();
    }

    checkPrescriptionRequirements() {
        const prescriptionItems = this.cart.filter(item => item.prescription_required);
        
        if (prescriptionItems.length > 0) {
            const warning = document.createElement('div');
            warning.className = 'prescription-warning';
            warning.innerHTML = `
                <i class="fas fa-exclamation-triangle"></i>
                <div class="prescription-warning-text">
                    <h4>Prescription Required</h4>
                    <p>Some items in your cart require a valid prescription. Please ensure you have uploaded your prescription or have it ready for verification.</p>
                </div>
            `;
            
            const cartContainer = document.getElementById('checkoutCartItems');
            cartContainer.insertBefore(warning, cartContainer.firstChild);
        }
    }

    calculateTotals() {
        const subtotal = this.cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
        const taxAmount = (subtotal * this.taxRate) / 100;
        const total = subtotal + taxAmount;

        this.updateTotalsDisplay(subtotal, taxAmount, total);
        this.updateCartData();
    }

    updateTotalsDisplay(subtotal, taxAmount, total) {
        const orderTotals = document.getElementById('orderTotals');
        const finalTotal = document.getElementById('finalTotal');

        if (orderTotals) {
            orderTotals.innerHTML = `
                <div class="total-row subtotal">
                    <span>Subtotal:</span>
                    <span>Rs ${subtotal.toFixed(2)}</span>
                </div>
                <div class="total-row tax">
                    <span>Tax (${this.taxRate}% GST):</span>
                    <span>Rs ${taxAmount.toFixed(2)}</span>
                </div>
                <div class="total-row final">
                    <span>Total Amount:</span>
                    <span>Rs ${total.toFixed(2)}</span>
                </div>
            `;
        }

        if (finalTotal) {
            finalTotal.querySelector('.amount').textContent = `Rs ${total.toFixed(2)}`;
        }
    }

    updateCartData() {
        const cartDataInput = document.getElementById('cartDataInput');
        if (cartDataInput) {
            cartDataInput.value = JSON.stringify(this.cart);
        }
    }

    validateCart() {
        if (this.cart.length === 0) {
            const placeOrderBtn = document.getElementById('placeOrderBtn');
            if (placeOrderBtn) {
                placeOrderBtn.disabled = true;
                placeOrderBtn.innerHTML = '<i class="fas fa-shopping-cart"></i> Cart is Empty';
            }
            return false;
        }
        return true;
    }

    async processOrder() {
        if (!this.validateCart()) {
            this.showNotification('Your cart is empty', 'error');
            return;
        }

        if (!this.validateForm()) {
            this.showNotification('Please fill in all required fields', 'error');
            return;
        }

        const placeOrderBtn = document.getElementById('placeOrderBtn');
        const originalText = placeOrderBtn.innerHTML;
        
        try {
            // Show loading state
            placeOrderBtn.classList.add('loading');
            placeOrderBtn.disabled = true;
            placeOrderBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing Order...';

            // Validate stock availability
            const stockValidation = await this.validateStock();
            if (!stockValidation.valid) {
                throw new Error(stockValidation.message);
            }

            // Submit the form
            const form = document.getElementById('checkoutForm');
            form.submit();

        } catch (error) {
            console.error('Order processing error:', error);
            this.showNotification(error.message || 'Failed to process order', 'error');
            
            // Reset button
            placeOrderBtn.classList.remove('loading');
            placeOrderBtn.disabled = false;
            placeOrderBtn.innerHTML = originalText;
        }
    }

    async validateStock() {
        try {
            for (const item of this.cart) {
                const response = await fetch(`../api/get_medicine.php?id=${item.id}`);
                const data = await response.json();
                
                if (!data.success) {
                    return {
                        valid: false,
                        message: `Product ${item.name} is no longer available`
                    };
                }
                
                if (data.data.stock_quantity < item.quantity) {
                    return {
                        valid: false,
                        message: `Insufficient stock for ${item.name}. Only ${data.data.stock_quantity} available.`
                    };
                }
            }
            
            return { valid: true };
        } catch (error) {
            return {
                valid: false,
                message: 'Failed to validate stock availability'
            };
        }
    }

    validateForm() {
        const form = document.getElementById('checkoutForm');
        const requiredInputs = form.querySelectorAll('input[required], textarea[required]');
        let isValid = true;

        requiredInputs.forEach(input => {
            if (!this.validateField(input)) {
                isValid = false;
            }
        });

        return isValid;
    }

    validateField(input) {
        const value = input.value.trim();
        let isValid = true;
        let message = '';

        if (input.hasAttribute('required') && !value) {
            isValid = false;
            message = 'This field is required';
        }

        if (input.name === 'delivery_address' && value && value.length < 10) {
            isValid = false;
            message = 'Please provide a complete delivery address';
        }

        if (!isValid) {
            this.showFieldError(input, message);
        } else {
            this.clearFieldError(input);
        }

        return isValid;
    }

    showFieldError(input, message) {
        this.clearFieldError(input);
        
        const error = document.createElement('div');
        error.className = 'field-error';
        error.textContent = message;
        
        input.classList.add('error');
        input.parentNode.insertBefore(error, input.nextSibling);
    }

    clearFieldError(input) {
        const error = input.parentNode.querySelector('.field-error');
        if (error) {
            error.remove();
        }
        input.classList.remove('error');
    }

    updatePaymentInfo(paymentMethod) {
        // Update UI based on selected payment method
        const paymentCards = document.querySelectorAll('.payment-card');
        paymentCards.forEach(card => {
            card.classList.remove('selected');
        });

        const selectedCard = document.querySelector(`input[value="${paymentMethod}"] + .payment-card`);
        if (selectedCard) {
            selectedCard.classList.add('selected');
        }

        // Show additional payment fields if needed
        this.showPaymentFields(paymentMethod);
    }

    showPaymentFields(paymentMethod) {
        // Remove existing payment fields
        const existingFields = document.querySelector('.payment-fields');
        if (existingFields) {
            existingFields.remove();
        }

        if (paymentMethod === 'cash') {
            // No additional fields needed for cash on delivery
            return;
        }

        // Create payment fields container
        const paymentFields = document.createElement('div');
        paymentFields.className = 'payment-fields';
        
        let fieldsHTML = '';
        
        switch (paymentMethod) {
            case 'card':
                fieldsHTML = `
                    <div class="form-group">
                        <label>Card Number</label>
                        <input type="text" class="form-input" placeholder="1234 5678 9012 3456" maxlength="19">
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Expiry Date</label>
                            <input type="text" class="form-input" placeholder="MM/YY" maxlength="5">
                        </div>
                        <div class="form-group">
                            <label>CVV</label>
                            <input type="text" class="form-input" placeholder="123" maxlength="3">
                        </div>
                    </div>
                `;
                break;
            case 'upi':
                fieldsHTML = `
                    <div class="form-group">
                        <label>UPI ID</label>
                        <input type="text" class="form-input" placeholder="yourname@upi">
                    </div>
                `;
                break;
            case 'online':
                fieldsHTML = `
                    <div class="form-group">
                        <label>Select Bank</label>
                        <select class="form-input">
                            <option value="">Choose your bank</option>
                            <option value="sbi">State Bank of India</option>
                            <option value="hdfc">HDFC Bank</option>
                            <option value="icici">ICICI Bank</option>
                            <option value="axis">Axis Bank</option>
                        </select>
                    </div>
                `;
                break;
        }
        
        paymentFields.innerHTML = fieldsHTML;
        
        // Insert after payment methods
        const paymentMethods = document.querySelector('.payment-methods');
        paymentMethods.appendChild(paymentFields);
    }

    showNotification(message, type = 'info') {
        const notification = document.createElement('div');
        notification.className = `notification notification-${type}`;
        notification.innerHTML = `
            <div class="notification-content">
                <i class="fas fa-${this.getNotificationIcon(type)}"></i>
                <span>${message}</span>
            </div>
            <button class="notification-close" onclick="this.parentElement.remove()">
                <i class="fas fa-times"></i>
            </button>
        `;

        document.body.appendChild(notification);

        setTimeout(() => notification.classList.add('show'), 100);

        setTimeout(() => {
            notification.classList.remove('show');
            setTimeout(() => notification.remove(), 300);
        }, 5000);
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
}

// Initialize checkout page
document.addEventListener('DOMContentLoaded', () => {
    new CheckoutPage();
});

// Additional enhancements
document.addEventListener('DOMContentLoaded', function() {
    // Auto-format card number input
    document.addEventListener('input', (e) => {
        if (e.target.placeholder === '1234 5678 9012 3456') {
            let value = e.target.value.replace(/\s/g, '').replace(/[^0-9]/gi, '');
            let formattedValue = value.match(/.{1,4}/g)?.join(' ') || value;
            e.target.value = formattedValue;
        }
    });

    // Auto-format expiry date
    document.addEventListener('input', (e) => {
        if (e.target.placeholder === 'MM/YY') {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length >= 2) {
                value = value.substring(0, 2) + '/' + value.substring(2, 4);
            }
            e.target.value = value;
        }
    });

    // Animate checkout steps
    const steps = document.querySelectorAll('.step');
    steps.forEach((step, index) => {
        step.style.animationDelay = `${index * 0.1}s`;
        step.classList.add('slide-in');
    });

    // Animate cards
    const cards = document.querySelectorAll('.glass-card');
    cards.forEach((card, index) => {
        card.style.animationDelay = `${index * 0.2}s`;
        card.classList.add('fade-in');
    });
});

// Additional CSS for enhanced interactions
const checkoutStyles = `
<style>
.field-error {
    color: var(--error-red);
    font-size: 0.85rem;
    margin-top: 0.5rem;
    display: flex;
    align-items: center;
    gap: 0.25rem;
}

.field-error::before {
    content: '⚠';
    font-size: 0.9rem;
}

.form-input.error {
    border-color: var(--error-red);
    box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.1);
}

.payment-fields {
    margin-top: 1rem;
    padding-top: 1rem;
    border-top: 1px solid #eee;
}

.payment-card.selected {
    border-color: var(--primary-blue);
    background: rgba(37, 99, 235, 0.05);
    box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
}

.prescription-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
    background: var(--warning-orange);
    color: white;
    padding: 0.25rem 0.5rem;
    border-radius: 12px;
    font-size: 0.75rem;
    font-weight: 600;
    margin-top: 0.5rem;
}

.notification {
    position: fixed;
    top: 20px;
    right: 20px;
    background: white;
    border-radius: 12px;
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
    border-left: 4px solid var(--primary-blue);
    padding: 1rem 1.5rem;
    max-width: 400px;
    z-index: 3000;
    transform: translateX(100%);
    transition: transform 0.3s ease;
}

.notification.show {
    transform: translateX(0);
}

.notification-content {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.notification-close {
    position: absolute;
    top: 0.5rem;
    right: 0.5rem;
    background: none;
    border: none;
    color: #999;
    cursor: pointer;
    font-size: 0.9rem;
}

.notification-success {
    border-left-color: var(--success-green);
}

.notification-error {
    border-left-color: var(--error-red);
}

.notification-warning {
    border-left-color: var(--warning-orange);
}
</style>
`;

document.head.insertAdjacentHTML('beforeend', checkoutStyles);