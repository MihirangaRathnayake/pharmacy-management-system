<?php
require_once __DIR__ . '/../bootstrap.php';

// Set JSON header
header('Content-Type: application/json');

// Check if user is logged in
if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

$user = getCurrentUser();

try {
    global $pdo;
    
    if (!$pdo) {
        throw new Exception('Database connection not available');
    }
    
    // Get notifications for the current user
    $stmt = $pdo->prepare("
        SELECT id, title, message, type, is_read, created_at 
        FROM notifications 
        WHERE user_id = ? OR user_id IS NULL 
        ORDER BY created_at DESC 
        LIMIT 10
    ");
    $stmt->execute([$user['id']]);
    $notifications = $stmt->fetchAll();
    
    // Format notifications
    $formattedNotifications = [];
    foreach ($notifications as $notification) {
        $formattedNotifications[] = [
            'id' => $notification['id'],
            'message' => $notification['message'],
            'type' => $notification['type'],
            'is_read' => (bool)$notification['is_read'],
            'time' => date('M j, Y g:i A', strtotime($notification['created_at']))
        ];
    }
    
    // Count unread notifications
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as count 
        FROM notifications 
        WHERE (user_id = ? OR user_id IS NULL) AND is_read = 0
    ");
    $stmt->execute([$user['id']]);
    $unreadCount = $stmt->fetch()['count'];
    
    echo json_encode([
        'success' => true,
        'count' => (int)$unreadCount,
        'notifications' => $formattedNotifications
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'count' => 0,
        'notifications' => []
    ]);
}