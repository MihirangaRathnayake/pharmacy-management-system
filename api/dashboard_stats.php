<?php
header('Content-Type: application/json');
require_once dirname(__DIR__) . '/bootstrap.php';

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

try {
    // Today's sales
    $stmt = $pdo->prepare("
        SELECT COALESCE(SUM(total_amount), 0) as today_sales 
        FROM sales 
        WHERE DATE(sale_date) = CURDATE() AND status = 'completed'
    ");
    $stmt->execute();
    $todaySales = $stmt->fetchColumn();

    // Low stock count
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as low_stock_count 
        FROM medicines 
        WHERE stock_quantity <= min_stock_level AND status = 'active'
    ");
    $stmt->execute();
    $lowStockCount = $stmt->fetchColumn();

    // Pending orders (prescriptions)
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as pending_orders 
        FROM prescriptions 
        WHERE status = 'pending'
    ");
    $stmt->execute();
    $pendingOrders = $stmt->fetchColumn();

    // Total customers
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as total_customers 
        FROM customers 
        WHERE status = 'active'
    ");
    $stmt->execute();
    $totalCustomers = $stmt->fetchColumn();

    echo json_encode([
        'success' => true,
        'todaySales' => $todaySales,
        'lowStockCount' => $lowStockCount,
        'pendingOrders' => $pendingOrders,
        'totalCustomers' => $totalCustomers
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error fetching dashboard data: ' . $e->getMessage()
    ]);
}
?>