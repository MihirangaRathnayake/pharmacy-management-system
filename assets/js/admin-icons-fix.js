// Admin Dashboard Icon Fix - runs as soon as possible
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
        document.body.classList.add('no-fontawesome');
        console.warn('FontAwesome not detected in admin dashboard, using fallback icons');
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
    
    // Function to force refresh admin dashboard icons
    function refreshAdminIcons() {
        const icons = document.querySelectorAll('[class*="fa-"]');
        icons.forEach(icon => {
            // Trigger a reflow to ensure proper rendering
            icon.style.display = 'none';
            icon.offsetHeight; // Trigger reflow
            icon.style.display = '';
            
            // Ensure proper font family
            const computedStyle = window.getComputedStyle(icon);
            if (!computedStyle.fontFamily.toLowerCase().includes('font awesome')) {
                icon.style.fontFamily = '"Font Awesome 6 Free", "Font Awesome 5 Free", "FontAwesome", Arial, sans-serif';
                if (icon.classList.contains('fas') || icon.classList.contains('fa-solid')) {
                    icon.style.fontWeight = '900';
                } else if (icon.classList.contains('fab') || icon.classList.contains('fa-brands')) {
                    icon.style.fontWeight = '400';
                    icon.style.fontFamily = '"Font Awesome 6 Brands", "Font Awesome 5 Brands", "FontAwesome", Arial, sans-serif';
                }
            }
        });
    }
    
    // Run immediately if DOM is ready, otherwise wait
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => {
            ensureIconsVisible();
            refreshAdminIcons();
        });
    } else {
        ensureIconsVisible();
        refreshAdminIcons();
    }
    
    // Also run on window load as final check
    window.addEventListener('load', () => {
        setTimeout(() => {
            if (!isFontAwesomeWorking()) {
                applyFallback();
            }
            refreshAdminIcons();
        }, 1000);
    });
    
    // Export function for manual refresh
    window.refreshAdminIcons = refreshAdminIcons;
    
})();

// Additional admin-specific icon fixes
document.addEventListener('DOMContentLoaded', function() {
    // Fix for dynamically loaded content
    const observer = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
            if (mutation.type === 'childList') {
                mutation.addedNodes.forEach(function(node) {
                    if (node.nodeType === 1) { // Element node
                        const icons = node.querySelectorAll ? node.querySelectorAll('[class*="fa-"]') : [];
                        icons.forEach(icon => {
                            // Ensure proper styling for new icons
                            if (!icon.style.fontFamily || !icon.style.fontFamily.includes('Font Awesome')) {
                                icon.style.fontFamily = '"Font Awesome 6 Free", "Font Awesome 5 Free", "FontAwesome", Arial, sans-serif';
                                icon.style.fontWeight = icon.classList.contains('fab') ? '400' : '900';
                            }
                        });
                    }
                });
            }
        });
    });
    
    // Start observing
    observer.observe(document.body, {
        childList: true,
        subtree: true
    });
    
    // Fix for Tailwind CSS conflicts
    const style = document.createElement('style');
    style.textContent = `
        [class*="fa-"] {
            font-family: "Font Awesome 6 Free", "Font Awesome 5 Free", "FontAwesome", Arial, sans-serif !important;
            font-style: normal !important;
            font-variant: normal !important;
            text-rendering: auto !important;
            line-height: 1 !important;
            -webkit-font-smoothing: antialiased !important;
            -moz-osx-font-smoothing: grayscale !important;
        }
        .fab, [class*="fa-brands"] {
            font-family: "Font Awesome 6 Brands", "Font Awesome 5 Brands", "FontAwesome", Arial, sans-serif !important;
            font-weight: 400 !important;
        }
        .fas, [class*="fa-solid"] {
            font-weight: 900 !important;
        }
    `;
    document.head.appendChild(style);
});