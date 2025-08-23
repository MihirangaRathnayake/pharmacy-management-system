<?php
require_once '../../bootstrap.php';
requireLogin();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$user_id = $_SESSION['user_id'];
$current_password = $_POST['current_password'] ?? '';
$new_password = $_POST['new_password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';

// Validation
if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
    echo json_encode(['success' => false, 'message' => 'All fields are required']);
    exit;
}

if ($new_password !== $confirm_password) {
    echo json_encode(['success' => false, 'message' => 'New passwords do not match']);
    exit;
}

if (strlen($new_password) < 6) {
    echo json_encode(['success' => false, 'message' => 'Password must be at least 6 characters long']);
    exit;
}

try {
    // Get current user data
    $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();
    
    if (!$user) {
        echo json_encode(['success' => false, 'message' => 'User not found']);
        exit;
    }
    
    // Verify current password
    if (!password_verify($current_password, $user['password'])) {
        echo json_encode(['success' => false, 'message' => 'Current password is incorrect']);
        exit;
    }
    
    // Hash new password
    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
    
    // Update password
    $stmt = $pdo->prepare("UPDATE users SET password = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
    $stmt->execute([$hashed_password, $user_id]);
    
    echo json_encode(['success' => true, 'message' => 'Password changed successfully']);
    
} catch (PDOException $e) {
    error_log("Password change error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error occurred']);
}
?>