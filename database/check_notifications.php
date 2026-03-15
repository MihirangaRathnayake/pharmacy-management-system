<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

// Get admin user id
$admin = $pdo->query("SELECT id FROM users WHERE role='admin' LIMIT 1")->fetch();
$adminId = $admin ? $admin['id'] : 1;
echo "Admin ID: $adminId\n";

// Manually generate notifications
try {
    // Low stock
    $lowStock = $pdo->query("SELECT name, stock_quantity, min_stock_level FROM medicines WHERE stock_quantity <= min_stock_level AND status = 'active'")->fetchAll();
    echo "Low stock medicines: " . count($lowStock) . "\n";
    foreach ($lowStock as $med) {
        $stmt = $pdo->prepare("INSERT INTO notifications (user_id, title, message, type) VALUES (NULL, ?, ?, 'warning')");
        $stmt->execute(['Low Stock Alert', $med['name'] . ' has only ' . $med['stock_quantity'] . ' units left (minimum: ' . $med['min_stock_level'] . ')']);
        echo "  Created: Low Stock - {$med['name']}\n";
    }

    // Expiring soon
    $expiring = $pdo->query("SELECT name, expiry_date FROM medicines WHERE expiry_date <= DATE_ADD(CURDATE(), INTERVAL 30 DAY) AND expiry_date >= CURDATE() AND status = 'active'")->fetchAll();
    echo "Expiring soon: " . count($expiring) . "\n";
    foreach ($expiring as $med) {
        $stmt = $pdo->prepare("INSERT INTO notifications (user_id, title, message, type) VALUES (NULL, ?, ?, 'error')");
        $stmt->execute(['Expiry Warning', $med['name'] . ' expires on ' . $med['expiry_date']]);
        echo "  Created: Expiry - {$med['name']}\n";
    }

    // Sales summary
    $sales = $pdo->query("SELECT COALESCE(SUM(total_amount),0) as total, COUNT(*) as count FROM sales WHERE DATE(sale_date) = CURDATE()")->fetch();
    if ($sales['count'] > 0) {
        $stmt = $pdo->prepare("INSERT INTO notifications (user_id, title, message, type) VALUES (NULL, ?, ?, 'success')");
        $stmt->execute(['Daily Sales Update', "Today: {$sales['count']} sales totaling Rs " . number_format($sales['total'], 2)]);
        echo "  Created: Daily sales\n";
    }

    // Pending prescriptions
    $pending = $pdo->query("SELECT COUNT(*) as count FROM prescriptions WHERE status = 'pending'")->fetch();
    if ($pending['count'] > 0) {
        $stmt = $pdo->prepare("INSERT INTO notifications (user_id, title, message, type) VALUES (NULL, ?, ?, 'info')");
        $stmt->execute(['Pending Prescriptions', $pending['count'] . ' prescription(s) awaiting verification']);
        echo "  Created: Pending prescriptions\n";
    }

    // Welcome notification for admin
    $stmt = $pdo->prepare("INSERT INTO notifications (user_id, title, message, type) VALUES (?, ?, ?, 'success')");
    $stmt->execute([$adminId, 'Welcome!', 'Your pharmacy notification system is now active']);

    // System update
    $stmt = $pdo->prepare("INSERT INTO notifications (user_id, title, message, type) VALUES (NULL, ?, ?, 'info')");
    $stmt->execute(['System Update', 'Dashboard now shows real-time data from your database']);

    $count = $pdo->query('SELECT COUNT(*) FROM notifications')->fetchColumn();
    echo "\nTotal notifications now: $count\n";

    $rows = $pdo->query('SELECT id, title, type, is_read, created_at FROM notifications ORDER BY created_at DESC LIMIT 10')->fetchAll();
    foreach ($rows as $r) {
        echo "  [{$r['type']}] {$r['title']} (read={$r['is_read']})\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}