<?php
header('Content-Type: application/json');
require_once dirname(__DIR__) . '/bootstrap.php';

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$input = json_decode(file_get_contents('php://input'), true);
$query = $input['query'] ?? '';

if (strlen($query) < 2) {
    echo json_encode(['success' => false, 'message' => 'Query too short']);
    exit();
}

try {
    $stmt = $pdo->prepare("
        SELECT id, name, generic_name, selling_price, stock_quantity, barcode
        FROM medicines 
        WHERE (name LIKE :query1 OR generic_name LIKE :query2 OR barcode LIKE :query3)
        AND status = 'active' 
        AND stock_quantity > 0
        ORDER BY name ASC
        LIMIT 10
    ");
    
    $searchQuery = "%$query%";
    $stmt->execute([
        'query1' => $searchQuery,
        'query2' => $searchQuery,
        'query3' => $searchQuery
    ]);
    $medicines = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'medicines' => $medicines
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error searching medicines: ' . $e->getMessage()
    ]);
}
?>