<?php
/**
 * Bootstrap file for Pharmacy Management System
 * Include this file at the top of every PHP file to ensure proper setup
 */

// Prevent direct access
if (!defined('PHARMACY_SYSTEM')) {
    define('PHARMACY_SYSTEM', true);
}

// Include configuration
require_once __DIR__ . '/config/config.php';

// Include database connection
require_once __DIR__ . '/config/database.php';

// Include authentication functions
require_once __DIR__ . '/includes/auth.php';

// Include URL helper functions
require_once __DIR__ . '/includes/url_helper.php';

// Include theme helper functions
require_once __DIR__ . '/includes/theme_helper.php';

// Helper functions
function redirect($url) {
    header("Location: $url");
    exit();
}

function sanitize($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

function formatCurrency($amount) {
    return 'Rs ' . number_format($amount, 2);
}

function formatDate($date, $format = 'Y-m-d') {
    return date($format, strtotime($date));
}

function generateInvoiceNumber() {
    return 'INV-' . date('Ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
}

function generateCustomerCode() {
    return 'CUST' . date('Ymd') . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT);
}

// Check if system is installed
function checkInstallation() {
    global $pdo;
    
    if (!$pdo) {
        return false;
    }
    
    try {
        $stmt = $pdo->query("SELECT COUNT(*) FROM users LIMIT 1");
        return $stmt !== false;
    } catch (Exception $e) {
        return false;
    }
}

// Auto-redirect to installation if not installed (but not for test files)
$current_file = basename($_SERVER['PHP_SELF']);
$skip_files = ['install.php', 'test_connection.php', 'check_system.php'];

if (!checkInstallation() && !in_array($current_file, $skip_files)) {
    redirect('install.php');
}
?>