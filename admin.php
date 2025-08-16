<?php
require_once __DIR__ . '/bootstrap.php';

// Redirect to login if not authenticated
if (!isLoggedIn()) {
    header('Location: auth/login.php');
    exit();
}

// Check if user is admin
$user = getCurrentUser();
if ($user['role'] !== 'admin') {
    header('Location: auth/login.php');
    exit();
}

// Redirect to admin dashboard
header('Location: index.php');
exit();
?>