<?php
header('Content-Type: application/json');
require_once dirname(__DIR__) . '/bootstrap.php';

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

try {
    $alerts = [];

    // Low stock alerts
    $stmt = $pdo->prepare("
        SELECT name, stock_quantity, min_stock_level, 'low_stock' as type
        FROM medicines 
        WHERE stock_quantity <= min_stock_level AND status = 'active'
        ORDER BY stock_quantity ASC
        LIMIT 5
    ");
    $stmt->execute();
    $lowStockAlerts = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Expiring medicines (within 30 days)
    $stmt = $pdo->prepare("
        SELECT name, expiry_date, 'expiring' as type
        FROM medicines 
        WHERE expiry_date <= DATE_ADD(CURDATE(), INTERVAL 30 DAY) 
        AND expiry_date > CURDATE()
        AND status = 'active'
        ORDER BY expiry_date ASC
        LIMIT 5
    ");
    $stmt->execute();
    $expiringAlerts = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $alerts = array_merge($lowStockAlerts, $expiringAlerts);

    echo json_encode([
        'success' => true,
        'alerts' => $alerts
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error fetching stock alerts: ' . $e->getMessage()
    ]);
}
?>