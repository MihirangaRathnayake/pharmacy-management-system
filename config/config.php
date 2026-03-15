<?php
// Configuration file for Pharmacy Management System

// Define the root directory of the application
define('ROOT_DIR', dirname(__DIR__));

// Load environment variables from .env file
$envFile = ROOT_DIR . '/.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        // Skip comments
        if (strpos(trim($line), '#') === 0) {
            continue;
        }
        // Parse key=value pairs
        if (strpos($line, '=') !== false) {
            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);
            // Set environment variable if not already set
            if (!getenv($key)) {
                putenv("$key=$value");
            }
        }
    }
}

// Define common paths
define('CONFIG_DIR', ROOT_DIR . '/config');
define('INCLUDES_DIR', ROOT_DIR . '/admin/includes');
define('MODULES_DIR', ROOT_DIR . '/admin/modules');
define('API_DIR', ROOT_DIR . '/admin/api');
define('UPLOADS_DIR', ROOT_DIR . '/uploads');
define('ASSETS_DIR', ROOT_DIR . '/admin/assets');

// Database configuration (can be overridden by .env file)
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_NAME', getenv('DB_NAME') ?: 'pharmacy_management');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: '');

// Application settings
define('APP_NAME', 'New Gampaha Pharmacy');
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

// Email settings (loaded from .env file or defaults)
// To enable SMTP email: Copy .env.example to .env and configure your SMTP settings
define('SMTP_ENABLED', getenv('SMTP_ENABLED') === 'true');
define('SMTP_HOST', getenv('SMTP_HOST') ?: 'smtp.gmail.com');
define('SMTP_PORT', getenv('SMTP_PORT') ?: 587);
define('SMTP_USERNAME', getenv('SMTP_USERNAME') ?: '');
define('SMTP_PASSWORD', getenv('SMTP_PASSWORD') ?: '');
define('MAIL_FROM_EMAIL', getenv('MAIL_FROM_EMAIL') ?: 'noreply@pharmacare.com');
define('MAIL_FROM_NAME', getenv('MAIL_FROM_NAME') ?: 'New Gampaha Pharmacy');

// Timezone
date_default_timezone_set('Asia/Kolkata');

// Error reporting (set to 0 in production)
error_reporting(E_ALL);
