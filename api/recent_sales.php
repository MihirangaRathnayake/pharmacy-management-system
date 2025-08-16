<?php
header('Content-Type: application/json');
require_once dirname(__DIR__) . '/bootstrap.php';

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

try {
    $stmt = $pdo->prepare("
        SELECT s.invoice_number, s.total_amount, s.sale_date, c.name as customer_name
        FROM sales s
        LEFT JOIN customers c ON s.customer_id = c.id
        WHERE s.status = 'completed'
        ORDER BY s.sale_date DESC
        LIMIT 5
    ");
    $stmt->execute();
    $sales = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'sales' => $sales
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error fetching recent sales: ' . $e->getMessage()
    ]);
}
?>