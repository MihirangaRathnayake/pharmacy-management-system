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
$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$address = trim($_POST['address'] ?? '');

// Validation
if (empty($name)) {
    echo json_encode(['success' => false, 'message' => 'Name is required']);
    exit;
}

if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Valid email is required']);
    exit;
}

try {
    // Check if email is already taken by another user
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
    $stmt->execute([$email, $user_id]);
    if ($stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Email is already taken']);
        exit;
    }
    
    // Update user information
    $stmt = $pdo->prepare("
        UPDATE users 
        SET name = ?, email = ?, phone = ?, address = ?, updated_at = CURRENT_TIMESTAMP 
        WHERE id = ?
    ");
    $stmt->execute([$name, $email, $phone, $address, $user_id]);
    
    // Update session data
    $_SESSION['user_name'] = $name;
    $_SESSION['user_email'] = $email;
    
    echo json_encode(['success' => true, 'message' => 'Profile updated successfully']);
    
} catch (PDOException $e) {
    error_log("Profile update error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error occurred']);
}
?>