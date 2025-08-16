<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../../config/database.php';

$input = json_decode(file_get_contents('php://input'), true);
$email = $input['email'] ?? '';
$password = $input['password'] ?? '';

if (!$email || !$password) {
    echo json_encode(['success' => false, 'message' => 'Email and password are required']);
    exit();
}

try {
    // Check if user exists and is a customer
    $stmt = $pdo->prepare("
        SELECT u.*, c.id as customer_id, c.customer_code, c.loyalty_points
        FROM users u
        LEFT JOIN customers c ON u.id = c.user_id
        WHERE u.email = ? AND u.role = 'customer' AND u.status = 'active'
    ");
    
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user || !password_verify($password, $user['password'])) {
        echo json_encode(['success' => false, 'message' => 'Invalid email or password']);
        exit();
    }
    
    // Generate a simple token (in production, use JWT or similar)
    $token = bin2hex(random_bytes(32));
    
    // Store token in session or database (for demo, we'll just return it)
    // In production, you'd store this in a sessions table
    
    $userData = [
        'id' => $user['id'],
        'customer_id' => $user['customer_id'],
        'name' => $user['name'],
        'email' => $user['email'],
        'phone' => $user['phone'],
        'customer_code' => $user['customer_code'],
        'loyalty_points' => $user['loyalty_points'] ?? 0
    ];
    
    echo json_encode([
        'success' => true,
        'message' => 'Login successful',
        'token' => $token,
        'user' => $userData
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Login error: ' . $e->getMessage()
    ]);
}
?>