<?php
/**
 * URL Helper Functions
 * Helps generate correct URLs regardless of where the file is located
 */

// Get the base URL for the application
function getBaseUrl() {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    
    // Try to detect the project folder
    $scriptName = $_SERVER['SCRIPT_NAME'];
    $pathParts = explode('/', $scriptName);
    
    // Look for 'pharmacy-management-system' in the path
    $basePath = '';
    foreach ($pathParts as $part) {
        if ($part === 'pharmacy-management-system') {
            $basePath = '/pharmacy-management-system';
            break;
        }
    }
    
    // If not found, try to detect from current directory
    if (empty($basePath)) {
        $currentDir = dirname($_SERVER['SCRIPT_NAME']);
        if (strpos($currentDir, 'pharmacy-management-system') !== false) {
            $basePath = '/pharmacy-management-system';
        }
    }
    
    return $protocol . '://' . $host . $basePath;
}

// Generate URL for a specific path
function url($path = '') {
    $baseUrl = getBaseUrl();
    $path = ltrim($path, '/');
    return $baseUrl . '/' . $path;
}

// Generate asset URL
function asset($path) {
    return url('assets/' . ltrim($path, '/'));
}

// Generate module URL
function moduleUrl($module, $file = 'index.php') {
    return url('modules/' . $module . '/' . $file);
}

// Generate API URL
function apiUrl($endpoint) {
    return url('api/' . ltrim($endpoint, '/'));
}

// Check if current page matches a path
function isCurrentPage($path) {
    $currentPath = $_SERVER['REQUEST_URI'];
    return strpos($currentPath, $path) !== false;
}

// Generate navigation class (active/inactive)
function navClass($path, $activeClass = 'active', $defaultClass = '') {
    return isCurrentPage($path) ? $activeClass : $defaultClass;
}
?>