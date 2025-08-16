<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../../config/database.php';

$input = json_decode(file_get_contents('php://input'), true);
$name = trim($input['name'] ?? '');
$email = trim($input['email'] ?? '');
$phone = trim($input['phone'] ?? '');
$password = $input['password'] ?? '';

// Validation
if (!$name || !$email || !$phone || !$password) {
    echo json_encode(['success' => false, 'message' => 'All fields are required']);
    exit();
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Invalid email format']);
    exit();
}

if (strlen($password) < 6) {
    echo json_encode(['success' => false, 'message' => 'Password must be at least 6 characters']);
    exit();
}

try {
    $pdo->beginTransaction();
    
    // Check if email already exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Email already registered']);
        exit();
    }
    
    // Check if phone already exists
    $stmt = $pdo->prepare("SELECT id FROM customers WHERE phone = ?");
    $stmt->execute([$phone]);
    if ($stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Phone number already registered']);
        exit();
    }
    
    // Create user account
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("
        INSERT INTO users (name, email, password, phone, role, status)
        VALUES (?, ?, ?, ?, 'customer', 'active')
    ");
    $stmt->execute([$name, $email, $hashedPassword, $phone]);
    $userId = $pdo->lastInsertId();
    
    // Create customer record
    $customerCode = 'CUST' . date('Ymd') . str_pad($userId, 4, '0', STR_PAD_LEFT);
    $stmt = $pdo->prepare("
        INSERT INTO customers (user_id, name, email, phone, customer_code, status)
        VALUES (?, ?, ?, ?, ?, 'active')
    ");
    $stmt->execute([$userId, $name, $email, $phone, $customerCode]);
    
    $pdo->commit();
    
    echo json_encode([
        'success' => true,
        'message' => 'Registration successful! You can now login.',
        'customer_code' => $customerCode
    ]);

} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode([
        'success' => false,
        'message' => 'Registration error: ' . $e->getMessage()
    ]);
}
?>