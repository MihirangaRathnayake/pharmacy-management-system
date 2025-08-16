<?php
require_once __DIR__ . '/../bootstrap.php';

// Set JSON header
header('Content-Type: application/json');

// Check if user is logged in
if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

// Check if request is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

try {
    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($input['theme'])) {
        throw new Exception('Theme not specified');
    }
    
    $theme = $input['theme'];
    $validThemes = ['light', 'dark', 'auto'];
    
    if (!in_array($theme, $validThemes)) {
        throw new Exception('Invalid theme');
    }
    
    $user = getCurrentUser();
    global $pdo;
    
    if (!$pdo) {
        throw new Exception('Database connection not available');
    }
    
    // Update or insert user preferences
    $stmt = $pdo->prepare("
        INSERT INTO user_preferences (user_id, theme) 
        VALUES (?, ?) 
        ON DUPLICATE KEY UPDATE theme = VALUES(theme)
    ");
    $stmt->execute([$user['id'], $theme]);
    
    echo json_encode([
        'success' => true,
        'message' => 'Theme updated successfully',
        'theme' => $theme
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>