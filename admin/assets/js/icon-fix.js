/**
 * FontAwesome Icon Fix for Admin Dashboard
 * Ensures all icons load properly
 */

document.addEventListener('DOMContentLoaded', function() {
    // Function to check if FontAwesome is loaded
    function isFontAwesomeLoaded() {
        const testElement = document.createElement('i');
        testElement.className = 'fas fa-home';
        testElement.style.position = 'absolute';
        testElement.style.left = '-9999px';
        testElement.style.visibility = 'hidden';
        document.body.appendChild(testElement);
        
        const computedStyle = window.getComputedStyle(testElement, ':before');
        const isLoaded = computedStyle.content !== 'none' && computedStyle.content !== '';
        
        document.body.removeChild(testElement);
        return isLoaded;
    }
    
    // Function to load FontAwesome fallback
    function loadFontAwesomeFallback() {
        console.log('Loading FontAwesome fallback...');
        
        // Try multiple CDN sources
        const cdnSources = [
            'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css',
            'https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.6.0/css/all.min.css',
            'https://use.fontawesome.com/releases/v6.6.0/css/all.css',
            'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css'
        ];
        
        cdnSources.forEach((src, index) => {
            setTimeout(() => {
                const link = document.createElement('link');
                link.rel = 'stylesheet';
                link.href = src;
                link.crossOrigin = 'anonymous';
                document.head.appendChild(link);
            }, index * 100);
        });
    }
    
    // Function to fix icon classes
    function fixIconClasses() {
        const icons = document.querySelectorAll('i[class*="fa-"]');
        icons.forEach(icon => {
            // Ensure proper FontAwesome classes
            if (!icon.classList.contains('fas') && !icon.classList.contains('far') && 
                !icon.classList.contains('fab') && !icon.classList.contains('fal')) {
                icon.classList.add('fas');
            }
            
            // Force font family
            icon.style.fontFamily = '"Font Awesome 6 Free", "Font Awesome 5 Free"';
            icon.style.fontWeight = '900';
            icon.style.fontStyle = 'normal';
            icon.style.display = 'inline-block';
        });
    }
    
    // Check if FontAwesome is loaded, if not load fallback
    setTimeout(() => {
        if (!isFontAwesomeLoaded()) {
            console.log('FontAwesome not detected, loading fallback...');
            loadFontAwesomeFallback();
            
            // Fix icon classes after a delay
            setTimeout(fixIconClasses, 1000);
        } else {
            console.log('FontAwesome loaded successfully');
            fixIconClasses();
        }
    }, 500);
    
    // Force fix icon classes on page load
    fixIconClasses();
    
    // Re-fix icons when new content is added dynamically
    const observer = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
            if (mutation.addedNodes.length > 0) {
                setTimeout(fixIconClasses, 100);
            }
        });
    });
    
    observer.observe(document.body, {
        childList: true,
        subtree: true
    });
});

// Global function to manually fix icons
window.fixFontAwesomeIcons = function() {
    const icons = document.querySelectorAll('i[class*="fa-"]');
    icons.forEach(icon => {
        icon.style.fontFamily = '"Font Awesome 6 Free", "Font Awesome 5 Free"';
        icon.style.fontWeight = '900';
        icon.style.fontStyle = 'normal';
        icon.style.display = 'inline-block';
        icon.style.visibility = 'visible';
    });
    console.log('FontAwesome icons fixed manually');
};