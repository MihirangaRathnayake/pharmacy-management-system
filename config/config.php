<?php
// Configuration file for Pharmacy Management System

// Define the root directory of the application
define('ROOT_DIR', dirname(__DIR__));

// Define common paths
define('CONFIG_DIR', ROOT_DIR . '/config');
define('INCLUDES_DIR', ROOT_DIR . '/includes');
define('MODULES_DIR', ROOT_DIR . '/modules');
define('API_DIR', ROOT_DIR . '/api');
define('UPLOADS_DIR', ROOT_DIR . '/uploads');
define('ASSETS_DIR', ROOT_DIR . '/assets');

// Database configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'pharmacy_management');
define('DB_USER', 'root');
define('DB_PASS', '');

// Application settings
define('APP_NAME', 'PharmaCare');
define('APP_VERSION', '1.0.0');
define('APP_URL', 'http://localhost/pharmacy-management-system');

// Security settings
define('SESSION_TIMEOUT', 3600); // 1 hour
define('MAX_LOGIN_ATTEMPTS', 5);
define('PASSWORD_MIN_LENGTH', 6);

// File upload settings
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_IMAGE_TYPES', ['jpg', 'jpeg', 'png', 'gif']);
define('ALLOWED_DOC_TYPES', ['pdf', 'doc', 'docx']);

// Pagination settings
define('ITEMS_PER_PAGE', 20);

// Tax and currency settings
define('DEFAULT_TAX_RATE', 18); // 18% GST
define('CURRENCY_SYMBOL', 'Rs');
define('CURRENCY_CODE', 'PKR');

// Email settings (for future use)
define('SMTP_HOST', 'localhost');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', '');
define('SMTP_PASSWORD', '');

// Timezone
date_default_timezone_set('Asia/Kolkata');

// Error reporting (set to 0 in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Auto-create upload directories if they don't exist
$upload_dirs = [
    UPLOADS_DIR,
    UPLOADS_DIR . '/prescriptions',
    UPLOADS_DIR . '/profiles',
    UPLOADS_DIR . '/medicines'
];

foreach ($upload_dirs as $dir) {
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
}
?>