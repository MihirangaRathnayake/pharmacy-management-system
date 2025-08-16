<?php
session_start();

// Log the logout activity (optional)
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $user_name = $_SESSION['user_name'] ?? 'Unknown';
    
    // You could log this to a file or database if needed
    error_log("User logout: ID={$user_id}, Name={$user_name}, Time=" . date('Y-m-d H:i:s'));
}

// Clear all session variables
$_SESSION = array();

// Delete the session cookie if it exists
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Destroy the session
session_destroy();

// Redirect to login page with a logout message
header('Location: login.php?logout=1');
exit();
?>