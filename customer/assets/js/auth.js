// Authentication page JavaScript

class AuthPage {
    constructor() {
        this.init();
    }

    init() {
        this.setupPasswordToggle();
        this.setupPasswordStrength();
        this.setupFormValidation();
        this.setupSocialLogin();
        this.setupAnimations();
    }

    setupPasswordToggle() {
        // Password visibility toggle functionality is handled by the global togglePassword function
        window.togglePassword = (button) => {
            const input = button.parentElement.querySelector('input');
            const icon = button.querySelector('i');
            
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        };
    }

    setupPasswordStrength() {
        const passwordInput = document.querySelector('input[name="password"]');
        const strengthIndicator = document.getElementById('passwordStrength');
        
        if (passwordInput && strengthIndicator) {
            passwordInput.addEventListener('input', (e) => {
                const password = e.target.value;
                const strength = this.calculatePasswordStrength(password);
                this.updatePasswordStrength(strengthIndicator, strength);
            });
        }
    }

    calculatePasswordStrength(password) {
        let score = 0;
        
        // Length check
        if (password.length >= 8) score += 1;
        if (password.length >= 12) score += 1;
        
        // Character variety checks
        if (/[a-z]/.test(password)) score += 1;
        if (/[A-Z]/.test(password)) score += 1;
        if (/[0-9]/.test(password)) score += 1;
        if (/[^A-Za-z0-9]/.test(password)) score += 1;
        
        // Return strength level
        if (score < 3) return 'weak';
        if (score < 5) return 'medium';
        return 'strong';
    }

    updatePasswordStrength(indicator, strength) {
        indicator.className = `password-strength ${strength}`;
        
        // Add visual feedback
        indicator.style.transform = 'scaleX(0)';
        setTimeout(() => {
            indicator.style.transform = 'scaleX(1)';
        }, 100);
    }

    setupFormValidation() {
        const forms = document.querySelectorAll('form[data-validate]');
        
        forms.forEach(form => {
            // Real-time validation
            const inputs = form.querySelectorAll('input[required]');
            inputs.forEach(input => {
                input.addEventListener('blur', () => this.validateField(input));
                input.addEventListener('input', () => this.clearFieldError(input));
            });

            // Form submission
            form.addEventListener('submit', (e) => {
                if (!this.validateForm(form)) {
                    e.preventDefault();
                    this.showFormErrors(form);
                } else {
                    this.showLoadingState(form);
                }
            });
        });
    }

    validateField(input) {
        const value = input.value.trim();
        let isValid = true;
        let message = '';

        // Required field check
        if (input.hasAttribute('required') && !value) {
            isValid = false;
            message = 'This field is required';
        }

        // Email validation
        if (input.type === 'email' && value && !this.isValidEmail(value)) {
            isValid = false;
            message = 'Please enter a valid email address';
        }

        // Password validation
        if (input.name === 'password' && value && value.length < 6) {
            isValid = false;
            message = 'Password must be at least 6 characters long';
        }

        // Confirm password validation
        if (input.name === 'confirm_password' && value) {
            const passwordInput = document.querySelector('input[name="password"]');
            if (passwordInput && value !== passwordInput.value) {
                isValid = false;
                message = 'Passwords do not match';
            }
        }

        // Phone validation
        if (input.type === 'tel' && value && !this.isValidPhone(value)) {
            isValid = false;
            message = 'Please enter a valid phone number';
        }

        if (!isValid) {
            this.showFieldError(input, message);
        } else {
            this.clearFieldError(input);
        }

        return isValid;
    }

    validateForm(form) {
        const inputs = form.querySelectorAll('input[required], select[required], textarea[required]');
        let isValid = true;

        inputs.forEach(input => {
            if (!this.validateField(input)) {
                isValid = false;
            }
        });

        // Check terms and conditions
        const termsCheckbox = form.querySelector('input[name="terms"]');
        if (termsCheckbox && !termsCheckbox.checked) {
            this.showFieldError(termsCheckbox, 'Please accept the terms and conditions');
            isValid = false;
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
        
        // Animate error appearance
        error.style.opacity = '0';
        error.style.transform = 'translateY(-10px)';
        setTimeout(() => {
            error.style.opacity = '1';
            error.style.transform = 'translateY(0)';
        }, 100);
    }

    clearFieldError(input) {
        const error = input.parentNode.querySelector('.field-error');
        if (error) {
            error.remove();
        }
        input.classList.remove('error');
    }

    showFormErrors(form) {
        const firstError = form.querySelector('.field-error');
        if (firstError) {
            firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    }

    showLoadingState(form) {
        const submitBtn = form.querySelector('button[type="submit"]');
        if (submitBtn) {
            submitBtn.classList.add('loading');
            submitBtn.disabled = true;
            
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
            
            // Restore button after 10 seconds (fallback)
            setTimeout(() => {
                submitBtn.classList.remove('loading');
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            }, 10000);
        }
    }

    setupSocialLogin() {
        const socialButtons = document.querySelectorAll('.social-btn');
        
        socialButtons.forEach(button => {
            button.addEventListener('click', (e) => {
                e.preventDefault();
                
                const provider = button.textContent.toLowerCase().includes('google') ? 'google' : 'facebook';
                this.handleSocialLogin(provider);
            });
        });
    }

    handleSocialLogin(provider) {
        // Show loading state
        const button = event.target.closest('.social-btn');
        const originalText = button.innerHTML;
        button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Connecting...';
        button.disabled = true;

        // Simulate social login (replace with actual implementation)
        setTimeout(() => {
            // Reset button
            button.innerHTML = originalText;
            button.disabled = false;
            
            // Show message
            this.showNotification(`${provider} login is not yet implemented`, 'info');
        }, 2000);
    }

    setupAnimations() {
        // Stagger animation for form elements
        const formElements = document.querySelectorAll('.form-group, .auth-divider, .social-login, .auth-footer');
        
        formElements.forEach((element, index) => {
            element.style.opacity = '0';
            element.style.transform = 'translateY(20px)';
            
            setTimeout(() => {
                element.style.transition = 'all 0.5s ease-out';
                element.style.opacity = '1';
                element.style.transform = 'translateY(0)';
            }, index * 100);
        });

        // Floating shapes animation
        this.animateFloatingShapes();
    }

    animateFloatingShapes() {
        const shapes = document.querySelectorAll('.shape');
        
        shapes.forEach((shape, index) => {
            // Random movement
            setInterval(() => {
                const x = Math.random() * 20 - 10; // -10 to 10
                const y = Math.random() * 20 - 10; // -10 to 10
                
                shape.style.transform = `translate(${x}px, ${y}px)`;
            }, 3000 + (index * 500));
        });
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

        // Show notification
        setTimeout(() => notification.classList.add('show'), 100);

        // Auto remove
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

    isValidEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }

    isValidPhone(phone) {
        const phoneRegex = /^[\+]?[1-9][\d]{0,15}$/;
        return phoneRegex.test(phone.replace(/[\s\-\(\)]/g, ''));
    }
}

// Initialize auth page
document.addEventListener('DOMContentLoaded', () => {
    new AuthPage();
});

// Additional utility functions
document.addEventListener('DOMContentLoaded', function() {
    // Auto-focus first input
    const firstInput = document.querySelector('.form-input');
    if (firstInput) {
        setTimeout(() => firstInput.focus(), 500);
    }

    // Keyboard navigation
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Enter' && e.target.classList.contains('form-input')) {
            const form = e.target.closest('form');
            const inputs = Array.from(form.querySelectorAll('.form-input'));
            const currentIndex = inputs.indexOf(e.target);
            
            if (currentIndex < inputs.length - 1) {
                e.preventDefault();
                inputs[currentIndex + 1].focus();
            }
        }
    });

    // Remember form data in localStorage (except passwords)
    const form = document.querySelector('.auth-form');
    if (form) {
        const inputs = form.querySelectorAll('input:not([type="password"])');
        
        // Load saved data
        inputs.forEach(input => {
            const savedValue = localStorage.getItem(`form_${input.name}`);
            if (savedValue && input.type !== 'checkbox') {
                input.value = savedValue;
            } else if (savedValue && input.type === 'checkbox') {
                input.checked = savedValue === 'true';
            }
        });

        // Save data on input
        inputs.forEach(input => {
            input.addEventListener('input', () => {
                if (input.type === 'checkbox') {
                    localStorage.setItem(`form_${input.name}`, input.checked);
                } else {
                    localStorage.setItem(`form_${input.name}`, input.value);
                }
            });
        });

        // Clear saved data on successful submission
        form.addEventListener('submit', (e) => {
            if (form.checkValidity()) {
                inputs.forEach(input => {
                    localStorage.removeItem(`form_${input.name}`);
                });
            }
        });
    }
});

// Enhanced input interactions
document.addEventListener('DOMContentLoaded', function() {
    const inputs = document.querySelectorAll('.form-input');
    
    inputs.forEach(input => {
        // Floating label effect
        input.addEventListener('focus', () => {
            input.parentElement.classList.add('focused');
        });
        
        input.addEventListener('blur', () => {
            if (!input.value) {
                input.parentElement.classList.remove('focused');
            }
        });
        
        // Check if input has value on load
        if (input.value) {
            input.parentElement.classList.add('focused');
        }
    });
});

// Additional CSS for enhanced interactions
const enhancedStyles = `
<style>
.input-wrapper.focused .input-icon {
    color: var(--primary-blue);
    transform: scale(1.1);
}

.form-input:focus {
    animation: inputGlow 0.3s ease-out;
}

@keyframes inputGlow {
    0% {
        box-shadow: 0 0 0 0 rgba(37, 99, 235, 0.4);
    }
    70% {
        box-shadow: 0 0 0 10px rgba(37, 99, 235, 0);
    }
    100% {
        box-shadow: 0 0 0 0 rgba(37, 99, 235, 0);
    }
}

.field-error {
    color: var(--error-red);
    font-size: 0.85rem;
    margin-top: 0.5rem;
    display: flex;
    align-items: center;
    gap: 0.25rem;
    transition: all 0.3s ease;
}

.field-error::before {
    content: '⚠';
    font-size: 0.9rem;
}

.form-input.error {
    border-color: var(--error-red);
    box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.1);
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

document.head.insertAdjacentHTML('beforeend', enhancedStyles);