<?php
/**
 * Icon Fix Helper Functions
 * Provides functions to include icon fix CSS and JavaScript
 */

/**
 * Render icon fix CSS links
 * @param string $basePath Base path for CSS files (default: 'assets/css/')
 * @return string HTML link tags for icon fix CSS
 */
function renderIconFixCSS($basePath = 'assets/css/') {
    return '
    <!-- FontAwesome Icon Fix CSS -->
    <link rel="stylesheet" href="' . $basePath . 'fontawesome-fix.css">
    <link rel="stylesheet" href="' . $basePath . 'admin-icons-fix.css">
    ';
}

/**
 * Render icon fix JavaScript
 * @param string $basePath Base path for JS files (default: 'assets/js/')
 * @return string HTML script tag for icon fix JavaScript
 */
function renderIconFixJS($basePath = 'assets/js/') {
    return '
    <!-- FontAwesome Icon Fix JavaScript -->
    <script src="' . $basePath . 'icon-fix.js"></script>
    ';
}

/**
 * Render complete FontAwesome setup with multiple CDN fallbacks
 * @param string $basePath Base path for local assets
 * @return string Complete HTML for FontAwesome setup
 */
function renderFontAwesomeSetup($basePath = 'assets/') {
    return '
    <!-- FontAwesome Icons - Multiple CDN fallbacks -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" integrity="sha512-iecdLmaskl7CVkqkXNQ/ZH/XLlvWZOJyj7Yy7tcenmpD1ypASozpmT/E0iPtmFIB46ZmdtAc9eNBvH0H/ZpiBw==" crossorigin="anonymous" referrerpolicy="no-referrer">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v6.4.0/css/all.css">
    <!-- FontAwesome 5 fallback -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" integrity="sha512-1ycn6IcaQQ40/MKBW2W4Rhis/DbILU74C1vSrLJxCq57o941Ym01SwNsOMqvEBFlcgUa6xLiPY/NS5R+E6ztJQ==" crossorigin="anonymous" referrerpolicy="no-referrer">
    
    ' . renderIconFixCSS($basePath . 'css/') . '
    ';
}

/**
 * Check if an icon class is supported
 * @param string $iconClass FontAwesome icon class (e.g., 'fa-user')
 * @return bool True if icon is supported
 */
function isIconSupported($iconClass) {
    $supportedIcons = [
        'fa-user', 'fa-users', 'fa-user-plus', 'fa-phone', 'fa-envelope', 'fa-calendar',
        'fa-eye', 'fa-edit', 'fa-trash', 'fa-search', 'fa-plus', 'fa-minus', 'fa-times',
        'fa-check-circle', 'fa-exclamation-circle', 'fa-exclamation-triangle', 'fa-info-circle',
        'fa-clock', 'fa-spinner', 'fa-arrow-left', 'fa-arrow-right', 'fa-home', 'fa-pills',
        'fa-boxes', 'fa-shopping-cart', 'fa-chart-bar', 'fa-birthday-cake', 'fa-venus-mars',
        'fa-map-marker-alt', 'fa-phone-alt', 'fa-notes-medical', 'fa-shopping-bag',
        'fa-file-medical', 'fa-dollar-sign', 'fa-save', 'fa-cog', 'fa-bell', 'fa-moon', 'fa-sun'
    ];
    
    return in_array($iconClass, $supportedIcons);
}

/**
 * Render an icon with fallback
 * @param string $iconClass FontAwesome icon class
 * @param string $fallbackText Fallback text if icon not supported
 * @param string $additionalClasses Additional CSS classes
 * @return string HTML for icon
 */
function renderIcon($iconClass, $fallbackText = '', $additionalClasses = '') {
    $classes = "fas $iconClass $additionalClasses";
    
    if (!isIconSupported($iconClass) && $fallbackText) {
        return "<span class=\"$additionalClasses\">$fallbackText</span>";
    }
    
    return "<i class=\"$classes\"></i>";
}
?>