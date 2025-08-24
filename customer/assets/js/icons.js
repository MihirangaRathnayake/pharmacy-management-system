// Icon fallback and loading management
(function() {
    'use strict';
    
    // Check if FontAwesome is loaded
    function checkFontAwesome() {
        // Method 1: Check if FontAwesome CSS is loaded
        const links = document.querySelectorAll('link[href*="font-awesome"]');
        if (links.length === 0) return false;
        
        // Method 2: Test if FontAwesome font is available
        const testElement = document.createElement('i');
        testElement.className = 'fas fa-home';
        testElement.style.position = 'absolute';
        testElement.style.left = '-9999px';
        testElement.style.visibility = 'hidden';
        document.body.appendChild(testElement);
        
        const computedStyle = window.getComputedStyle(testElement, ':before');
        const content = computedStyle.getPropertyValue('content');
        const fontFamily = computedStyle.getPropertyValue('font-family');
        
        document.body.removeChild(testElement);
        
        // FontAwesome loaded if content is not 'none' or empty, or if font family contains FontAwesome
        const hasContent = content && content !== 'none' && content !== '""' && content !== '"\\f015"';
        const hasFont = fontFamily && fontFamily.toLowerCase().includes('font awesome');
        
        return hasContent || hasFont;
    }
    
    // Fallback icon mapping
    const iconFallbacks = {
        'fa-plus-circle': '⊕',
        'fa-home': '🏠',
        'fa-pills': '💊',
        'fa-prescription': '📋',
        'fa-phone': '📞',
        'fa-search': '🔍',
        'fa-shopping-cart': '🛒',
        'fa-user': '👤',
        'fa-sign-in-alt': '🔑',
        'fa-user-plus': '👥',
        'fa-heartbeat': '💓',
        'fa-shield-alt': '🛡️',
        'fa-truck': '🚚',
        'fa-band-aid': '🩹',
        'fa-syringe': '💉',
        'fa-leaf': '🍃',
        'fa-thermometer-half': '🌡️',
        'fa-cart-plus': '🛒+',
        'fa-eye': '👁️',
        'fa-arrow-right': '→',
        'fa-shipping-fast': '🚀',
        'fa-certificate': '🏆',
        'fa-user-md': '👨‍⚕️',
        'fa-lock': '🔒',
        'fa-facebook-f': 'f',
        'fa-twitter': 't',
        'fa-instagram': '📷',
        'fa-linkedin-in': 'in',
        'fa-map-marker-alt': '📍',
        'fa-envelope': '✉️',
        'fa-clock': '🕐',
        'fa-times': '✕',
        'fa-credit-card': '💳',
        'fa-check': '✓',
        'fa-spinner': '⟳',
        'fa-exclamation-circle': '⚠',
        'fa-check-circle': '✓',
        'fa-info-circle': 'ℹ',
        'fa-minus': '−',
        'fa-plus': '+',
        'fa-trash': '🗑️',
        'fa-edit': '✏️',
        'fa-save': '💾',
        'fa-download': '⬇',
        'fa-upload': '⬆',
        'fa-star': '⭐',
        'fa-heart': '❤️',
        'fa-thumbs-up': '👍',
        'fa-comment': '💬',
        'fa-share': '📤',
        'fa-print': '🖨️',
        'fa-calendar': '📅',
        'fa-location-arrow': '📍',
        'fa-cog': '⚙️',
        'fa-bell': '🔔',
        'fa-question-circle': '❓'
    };
    
    // Apply fallback icons
    function applyFallbacks() {
        const icons = document.querySelectorAll('[class*="fa-"]');
        
        icons.forEach(icon => {
            const classes = icon.className.split(' ');
            let fallbackText = '';
            
            // Find the first matching fallback
            for (const className of classes) {
                if (iconFallbacks[className]) {
                    fallbackText = iconFallbacks[className];
                    break;
                }
            }
            
            if (fallbackText) {
                icon.textContent = fallbackText;
                icon.style.fontFamily = 'Arial, sans-serif';
                icon.style.fontSize = '1em';
                icon.style.fontWeight = 'normal';
            }
        });
    }
    
    // Initialize icon system
    function initIcons() {
        // Wait for DOM to be ready
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initIcons);
            return;
        }
        
        // Check if FontAwesome loaded after a short delay
        setTimeout(() => {
            if (!checkFontAwesome()) {
                console.warn('FontAwesome not loaded, applying fallback icons');
                applyFallbacks();
            }
        }, 1000);
        
        // Also check when fonts are loaded
        if (document.fonts) {
            document.fonts.ready.then(() => {
                setTimeout(() => {
                    if (!checkFontAwesome()) {
                        applyFallbacks();
                    }
                }, 500);
            });
        }
    }
    
    // Start initialization
    initIcons();
    
    // Re-apply fallbacks for dynamically added content
    window.applyIconFallbacks = applyFallbacks;
    
})();

// Additional FontAwesome loading attempts
(function() {
    // Try loading FontAwesome from multiple CDNs
    const fontAwesomeCDNs = [
        'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css',
        'https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.5.1/css/all.min.css',
        'https://use.fontawesome.com/releases/v6.5.1/css/all.css',
        'https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css'
    ];
    
    let loadedSuccessfully = false;
    
    function loadFontAwesome(url, index = 0) {
        if (loadedSuccessfully || index >= fontAwesomeCDNs.length) return;
        
        const link = document.createElement('link');
        link.rel = 'stylesheet';
        link.href = url;
        
        link.onload = () => {
            loadedSuccessfully = true;
            console.log('FontAwesome loaded from:', url);
        };
        
        link.onerror = () => {
            console.warn('Failed to load FontAwesome from:', url);
            if (index + 1 < fontAwesomeCDNs.length) {
                loadFontAwesome(fontAwesomeCDNs[index + 1], index + 1);
            }
        };
        
        document.head.appendChild(link);
    }
    
    // Try loading if not already loaded
    setTimeout(() => {
        const existingFA = document.querySelector('link[href*="font-awesome"]');
        if (!existingFA || !loadedSuccessfully) {
            loadFontAwesome(fontAwesomeCDNs[0]);
        }
    }, 100);
})();