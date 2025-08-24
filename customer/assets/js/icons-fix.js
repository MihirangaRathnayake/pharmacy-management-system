// Immediate icon fix - runs as soon as possible
(function() {
    'use strict';
    
    // Function to check if FontAwesome is working
    function isFontAwesomeWorking() {
        // Create a test element
        const test = document.createElement('i');
        test.className = 'fas fa-home';
        test.style.position = 'absolute';
        test.style.left = '-9999px';
        test.style.visibility = 'hidden';
        document.body.appendChild(test);
        
        // Check computed styles
        const styles = window.getComputedStyle(test, ':before');
        const content = styles.getPropertyValue('content');
        const fontFamily = styles.getPropertyValue('font-family');
        
        document.body.removeChild(test);
        
        // FontAwesome is working if we have proper content or font family
        const hasValidContent = content && content !== 'none' && content !== '""';
        const hasFontAwesome = fontFamily && fontFamily.toLowerCase().includes('font awesome');
        
        return hasValidContent || hasFontAwesome;
    }
    
    // Function to apply fallback class
    function applyFallback() {
        document.documentElement.classList.add('no-fontawesome');
        console.warn('FontAwesome not detected, using fallback icons');
    }
    
    // Function to ensure icons are visible
    function ensureIconsVisible() {
        // Wait a bit for fonts to load
        setTimeout(() => {
            if (!isFontAwesomeWorking()) {
                applyFallback();
            }
        }, 1000);
        
        // Also check when fonts are ready
        if (document.fonts && document.fonts.ready) {
            document.fonts.ready.then(() => {
                setTimeout(() => {
                    if (!isFontAwesomeWorking()) {
                        applyFallback();
                    }
                }, 500);
            });
        }
    }
    
    // Run immediately if DOM is ready, otherwise wait
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', ensureIconsVisible);
    } else {
        ensureIconsVisible();
    }
    
    // Also run on window load as final check
    window.addEventListener('load', () => {
        setTimeout(() => {
            if (!isFontAwesomeWorking()) {
                applyFallback();
            }
        }, 1000);
    });
    
})();

// Additional immediate fix for common icons
document.addEventListener('DOMContentLoaded', function() {
    // Force refresh of icon elements
    const icons = document.querySelectorAll('[class*="fa-"]');
    icons.forEach(icon => {
        // Trigger a reflow to ensure proper rendering
        icon.style.display = 'none';
        icon.offsetHeight; // Trigger reflow
        icon.style.display = '';
    });
});