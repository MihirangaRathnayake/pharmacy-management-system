<?php

/**
 * Notifications API endpoint
 * Handles: fetch, mark read, mark all read, generate auto notifications
 */
require_once dirname(__DIR__) . '/bootstrap.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit();
}

$user = getCurrentUser();
$userId = $user['id'];
$action = $_GET['action'] ?? $_POST['action'] ?? '';

switch ($action) {
    case 'fetch':
        fetchNotifications($pdo, $userId);
        break;
    case 'mark_read':
        markAsRead($pdo, $userId);
        break;
    case 'mark_all_read':
        markAllRead($pdo, $userId);
        break;
    case 'generate':
        generateAutoNotifications($pdo, $userId);
        echo json_encode(['success' => true]);
        break;
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
}

function fetchNotifications($pdo, $userId)
{
    try {
        // Fetch notifications for this user or global (user_id IS NULL)
        $stmt = $pdo->prepare("
            SELECT id, title, message, type, is_read, created_at
            FROM notifications
            WHERE user_id = ? OR user_id IS NULL
            ORDER BY created_at DESC
            LIMIT 20
        ");
        $stmt->execute([$userId]);
        $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Count unread
        $countStmt = $pdo->prepare("
            SELECT COUNT(*) as count FROM notifications
            WHERE (user_id = ? OR user_id IS NULL) AND is_read = 0
        ");
        $countStmt->execute([$userId]);
        $unreadCount = $countStmt->fetch()['count'];

        echo json_encode([
            'success' => true,
            'notifications' => $notifications,
            'unread_count' => (int)$unreadCount
        ]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error fetching notifications']);
    }
}

function markAsRead($pdo, $userId)
{
    $input = json_decode(file_get_contents('php://input'), true);
    $id = intval($input['id'] ?? 0);
    if (!$id) {
        echo json_encode(['success' => false, 'message' => 'Invalid notification ID']);
        return;
    }

    try {
        $stmt = $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE id = ? AND (user_id = ? OR user_id IS NULL)");
        $stmt->execute([$id, $userId]);
        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error updating notification']);
    }
}

function markAllRead($pdo, $userId)
{
    try {
        $stmt = $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE (user_id = ? OR user_id IS NULL) AND is_read = 0");
        $stmt->execute([$userId]);
        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error updating notifications']);
    }
}

function generateAutoNotifications($pdo, $userId)
{
    try {
        // Low stock alerts
        $lowStock = $pdo->query("
            SELECT id, name, stock_quantity, min_stock_level 
            FROM medicines 
            WHERE stock_quantity <= min_stock_level AND status = 'active'
        ")->fetchAll();

        foreach ($lowStock as $med) {
            // Check if a similar unread notification already exists (within last 24h)
            $existing = $pdo->prepare("
                SELECT id FROM notifications 
                WHERE title = 'Low Stock Alert' 
                AND message LIKE ? 
                AND created_at > DATE_SUB(NOW(), INTERVAL 24 HOUR)
                LIMIT 1
            ");
            $existing->execute(['%' . $med['name'] . '%']);
            if (!$existing->fetch()) {
                $stmt = $pdo->prepare("INSERT INTO notifications (user_id, title, message, type) VALUES (NULL, ?, ?, 'warning')");
                $stmt->execute([
                    'Low Stock Alert',
                    $med['name'] . ' has only ' . $med['stock_quantity'] . ' units left (minimum: ' . $med['min_stock_level'] . ')'
                ]);
            }
        }

        // Expiring medicines (within 30 days)
        $expiring = $pdo->query("
            SELECT id, name, expiry_date 
            FROM medicines 
            WHERE expiry_date <= DATE_ADD(CURDATE(), INTERVAL 30 DAY) 
            AND expiry_date >= CURDATE() 
            AND status = 'active'
        ")->fetchAll();

        foreach ($expiring as $med) {
            $existing = $pdo->prepare("
                SELECT id FROM notifications 
                WHERE title = 'Expiry Warning' 
                AND message LIKE ? 
                AND created_at > DATE_SUB(NOW(), INTERVAL 24 HOUR)
                LIMIT 1
            ");
            $existing->execute(['%' . $med['name'] . '%']);
            if (!$existing->fetch()) {
                $daysLeft = (int)((strtotime($med['expiry_date']) - time()) / 86400);
                $stmt = $pdo->prepare("INSERT INTO notifications (user_id, title, message, type) VALUES (NULL, ?, ?, 'error')");
                $stmt->execute([
                    'Expiry Warning',
                    $med['name'] . ' expires in ' . $daysLeft . ' days (' . $med['expiry_date'] . ')'
                ]);
            }
        }

        // Today's sales summary (generate once per day after first sale)
        $todaySales = $pdo->query("SELECT COALESCE(SUM(total_amount), 0) as total, COUNT(*) as count FROM sales WHERE DATE(sale_date) = CURDATE()")->fetch();
        if ($todaySales['count'] > 0) {
            $existing = $pdo->prepare("
                SELECT id FROM notifications 
                WHERE title = 'Daily Sales Update' 
                AND DATE(created_at) = CURDATE()
                LIMIT 1
            ");
            $existing->execute();
            if (!$existing->fetch()) {
                $stmt = $pdo->prepare("INSERT INTO notifications (user_id, title, message, type) VALUES (NULL, ?, ?, 'success')");
                $stmt->execute([
                    'Daily Sales Update',
                    'Today: ' . $todaySales['count'] . ' sales totaling Rs ' . number_format($todaySales['total'], 2)
                ]);
            }
        }

        // Pending prescriptions
        $pending = $pdo->query("SELECT COUNT(*) as count FROM prescriptions WHERE status = 'pending'")->fetch();
        if ($pending['count'] > 0) {
            $existing = $pdo->prepare("
                SELECT id FROM notifications 
                WHERE title = 'Pending Prescriptions' 
                AND created_at > DATE_SUB(NOW(), INTERVAL 24 HOUR)
                LIMIT 1
            ");
            $existing->execute();
            if (!$existing->fetch()) {
                $stmt = $pdo->prepare("INSERT INTO notifications (user_id, title, message, type) VALUES (NULL, ?, ?, 'info')");
                $stmt->execute([
                    'Pending Prescriptions',
                    $pending['count'] . ' prescription(s) awaiting verification'
                ]);
            }
        }
    } catch (Exception $e) {
        // Silently fail for auto-generation
    }
}
