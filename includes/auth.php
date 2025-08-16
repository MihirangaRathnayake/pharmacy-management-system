<?php
require_once dirname(__DIR__) . '/config/database.php';

function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

function getCurrentUser() {
    global $pdo;
    
    if (!isLoggedIn()) {
        return null;
    }
    
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function hasRole($role) {
    $user = getCurrentUser();
    return $user && $user['role'] === $role;
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: /auth/login.php');
        exit();
    }
}

function requireRole($role) {
    requireLogin();
    if (!hasRole($role)) {
        header('Location: /index.php');
        exit();
    }
}

function login($email, $password) {
    global $pdo;
    
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND status = 'active'");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_role'] = $user['role'];
        return true;
    }
    
    return false;
}

function logout() {
    session_destroy();
    header('Location: /auth/login.php');
    exit();
}
?>