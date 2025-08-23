// Contact page JavaScript

class ContactPage {
    constructor() {
        this.init();
    }

    init() {
        this.setupFAQ();
        this.setupFormValidation();
        this.setupAnimations();
        this.setupMapInteractions();
    }

    setupFAQ() {
        const faqItems = document.querySelectorAll('.faq-item');
        
        faqItems.forEach(item => {
            const question = item.querySelector('.faq-question');
            
            question.addEventListener('click', () => {
                // Close other FAQ items
                faqItems.forEach(otherItem => {
                    if (otherItem !== item) {
                        otherItem.classList.remove('active');
                    }
                });
                
                // Toggle current item
                item.classList.toggle('active');
                
                // Animate the answer
                const answer = item.querySelector('.faq-answer');
                if (item.classList.contains('active')) {
                    answer.style.maxHeight = answer.scrollHeight + 'px';
                } else {
                    answer.style.maxHeight = '0';
                }
            });
        });
    }

    setupFormValidation() {
        const form = document.querySelector('.contact-form');
        if (!form) return;

        const inputs = form.querySelectorAll('.form-input');
        
        // Real-time validation
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

        // Character counter for textarea
        const textarea = form.querySelector('textarea');
        if (textarea) {
            this.setupCharacterCounter(textarea);
        }
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

        // Phone validation
        if (input.type === 'tel' && value && !this.isValidPhone(value)) {
            isValid = false;
            message = 'Please enter a valid phone number';
        }

        // Message length validation
        if (input.name === 'message' && value && value.length < 10) {
            isValid = false;
            message = 'Message must be at least 10 characters long';
        }

        if (!isValid) {
            this.showFieldError(input, message);
        } else {
            this.clearFieldError(input);
            input.classList.add('success');
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

        return isValid;
    }

    showFieldError(input, message) {
        this.clearFieldError(input);
        
        const error = document.createElement('div');
        error.className = 'field-error';
        error.textContent = message;
        
        input.classList.add('error');
        input.classList.remove('success');
        
        // Insert error after the input wrapper
        const wrapper = input.closest('.input-wrapper') || input.parentNode;
        wrapper.parentNode.insertBefore(error, wrapper.nextSibling);
        
        // Animate error appearance
        error.style.opacity = '0';
        error.style.transform = 'translateY(-10px)';
        setTimeout(() => {
            error.style.transition = 'all 0.3s ease';
            error.style.opacity = '1';
            error.style.transform = 'translateY(0)';
        }, 100);
    }

    clearFieldError(input) {
        const wrapper = input.closest('.input-wrapper') || input.parentNode;
        const error = wrapper.parentNode.querySelector('.field-error');
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
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Sending...';
            
            // Restore button after 10 seconds (fallback)
            setTimeout(() => {
                submitBtn.classList.remove('loading');
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            }, 10000);
        }
    }

    setupCharacterCounter(textarea) {
        const maxLength = 500;
        const counter = document.createElement('div');
        counter.className = 'character-counter';
        counter.textContent = `0 / ${maxLength}`;
        
        textarea.parentNode.appendChild(counter);
        
        textarea.addEventListener('input', () => {
            const length = textarea.value.length;
            counter.textContent = `${length} / ${maxLength}`;
            
            if (length > maxLength * 0.9) {
                counter.style.color = 'var(--warning-orange)';
            } else if (length > maxLength * 0.8) {
                counter.style.color = 'var(--primary-blue)';
            } else {
                counter.style.color = '#666';
            }
        });
    }

    setupAnimations() {
        // Intersection Observer for animations
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const element = entry.target;
                    
                    if (element.classList.contains('contact-info')) {
                        element.classList.add('slide-in-left');
                    } else if (element.classList.contains('contact-form-section')) {
                        element.classList.add('slide-in-right');
                    } else {
                        element.classList.add('fade-in-up');
                    }
                    
                    observer.unobserve(element);
                }
            });
        }, observerOptions);

        // Observe elements
        const animatedElements = document.querySelectorAll(
            '.contact-info, .contact-form-section, .map-container, .faq-item'
        );
        
        animatedElements.forEach(el => observer.observe(el));

        // Stagger FAQ animations
        const faqItems = document.querySelectorAll('.faq-item');
        faqItems.forEach((item, index) => {
            item.style.animationDelay = `${index * 0.1}s`;
        });
    }

    setupMapInteractions() {
        const mapContainer = document.querySelector('.map-container');
        if (!mapContainer) return;

        const iframe = mapContainer.querySelector('iframe');
        
        // Prevent scroll hijacking
        mapContainer.addEventListener('click', () => {
            iframe.style.pointerEvents = 'auto';
        });

        mapContainer.addEventListener('mouseleave', () => {
            iframe.style.pointerEvents = 'none';
        });

        // Add loading state for map
        iframe.addEventListener('load', () => {
            mapContainer.classList.add('loaded');
        });
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

// Initialize contact page
document.addEventListener('DOMContentLoaded', () => {
    new ContactPage();
});

// Additional enhancements
document.addEventListener('DOMContentLoaded', function() {
    // Auto-resize textarea
    const textareas = document.querySelectorAll('textarea');
    textareas.forEach(textarea => {
        textarea.addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = this.scrollHeight + 'px';
        });
    });

    // Social links hover effects
    const socialLinks = document.querySelectorAll('.social-link');
    socialLinks.forEach(link => {
        link.addEventListener('mouseenter', function() {
            this.style.transform = 'scale(1.1) translateY(-2px) rotate(5deg)';
        });
        
        link.addEventListener('mouseleave', function() {
            this.style.transform = 'scale(1) translateY(0) rotate(0deg)';
        });
    });

    // Contact method hover effects
    const contactMethods = document.querySelectorAll('.contact-method');
    contactMethods.forEach(method => {
        method.addEventListener('mouseenter', function() {
            const icon = this.querySelector('.method-icon');
            icon.style.transform = 'scale(1.1) rotate(10deg)';
        });
        
        method.addEventListener('mouseleave', function() {
            const icon = this.querySelector('.method-icon');
            icon.style.transform = 'scale(1) rotate(0deg)';
        });
    });

    // Form input focus effects
    const formInputs = document.querySelectorAll('.form-input');
    formInputs.forEach(input => {
        input.addEventListener('focus', function() {
            const wrapper = this.closest('.input-wrapper');
            if (wrapper) {
                wrapper.style.transform = 'translateY(-2px)';
                wrapper.style.boxShadow = '0 8px 25px rgba(37, 99, 235, 0.15)';
            }
        });
        
        input.addEventListener('blur', function() {
            const wrapper = this.closest('.input-wrapper');
            if (wrapper) {
                wrapper.style.transform = 'translateY(0)';
                wrapper.style.boxShadow = 'none';
            }
        });
    });

    // Smooth scroll for anchor links
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
});

// Additional CSS for enhanced interactions
const contactStyles = `
<style>
.character-counter {
    font-size: 0.8rem;
    color: #666;
    text-align: right;
    margin-top: 0.25rem;
    transition: color 0.3s ease;
}

.map-container {
    position: relative;
    overflow: hidden;
}

.map-container::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(37, 99, 235, 0.1);
    z-index: 1;
    opacity: 1;
    transition: opacity 0.5s ease;
}

.map-container.loaded::before {
    opacity: 0;
}

.map-container iframe {
    pointer-events: none;
    transition: all 0.3s ease;
}

.input-wrapper {
    transition: all 0.3s ease;
}

.method-icon {
    transition: all 0.3s ease;
}

.social-link {
    transition: all 0.3s ease;
}

.faq-item {
    animation: fadeInUp 0.6s ease-out both;
}

.slide-in-left {
    animation: slideInLeft 0.8s ease-out;
}

.slide-in-right {
    animation: slideInRight 0.8s ease-out;
}

.fade-in-up {
    animation: fadeInUp 0.6s ease-out;
}

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes slideInLeft {
    from {
        opacity: 0;
        transform: translateX(-50px);
    }
    to {
        opacity: 1;
        transform: translateX(0);
    }
}

@keyframes slideInRight {
    from {
        opacity: 0;
        transform: translateX(50px);
    }
    to {
        opacity: 1;
        transform: translateX(0);
    }
}

.form-input.success {
    border-color: var(--success-green);
    box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.1);
}

.form-input.error {
    border-color: var(--error-red);
    box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.1);
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
</style>
`;

document.head.insertAdjacentHTML('beforeend', contactStyles);