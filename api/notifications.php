<?php
require_once '../bootstrap.php';
requireLogin();

header('Content-Type: application/json');

$user_id = $_SESSION['user_id'];

try {
    // Get notifications for the user
    $stmt = $pdo->prepare("
        SELECT id, title, message, type, is_read, created_at 
        FROM notifications 
        WHERE user_id = ? OR user_id IS NULL 
        ORDER BY created_at DESC 
        LIMIT 10
    ");
    $stmt->execute([$user_id]);
    $notifications = $stmt->fetchAll();
    
    // Count unread notifications
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as count 
        FROM notifications 
        WHERE (user_id = ? OR user_id IS NULL) AND is_read = 0
    ");
    $stmt->execute([$user_id]);
    $unread_count = $stmt->fetch()['count'];
    
    // Format notifications
    $formatted_notifications = [];
    foreach ($notifications as $notification) {
        $formatted_notifications[] = [
            'id' => $notification['id'],
            'message' => $notification['message'],
            'type' => $notification['type'],
            'is_read' => (bool)$notification['is_read'],
            'time' => timeAgo($notification['created_at'])
        ];
    }
    
    echo json_encode([
        'success' => true,
        'count' => $unread_count,
        'notifications' => $formatted_notifications
    ]);
    
} catch (PDOException $e) {
    error_log("Notifications error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'count' => 0,
        'notifications' => []
    ]);
}

function timeAgo($datetime) {
    $time = time() - strtotime($datetime);
    
    if ($time < 60) return 'Just now';
    if ($time < 3600) return floor($time/60) . ' minutes ago';
    if ($time < 86400) return floor($time/3600) . ' hours ago';
    if ($time < 2592000) return floor($time/86400) . ' days ago';
    
    return date('M j, Y', strtotime($datetime));
}
?>