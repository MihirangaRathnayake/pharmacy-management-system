<?php
header('Content-Type: application/json');
require_once dirname(__DIR__) . '/bootstrap.php';

// Debug version of search medicines API
$debug = [];

try {
    $debug['auth_check'] = isLoggedIn() ? 'authenticated' : 'not authenticated';
    
    if (!isLoggedIn()) {
        echo json_encode(['success' => false, 'message' => 'Unauthorized', 'debug' => $debug]);
        exit();
    }

    // Get input
    $input = json_decode(file_get_contents('php://input'), true);
    $query = $input['query'] ?? '';
    
    $debug['input'] = $input;
    $debug['query'] = $query;
    $debug['query_length'] = strlen($query);

    if (strlen($query) < 2) {
        echo json_encode(['success' => false, 'message' => 'Query too short', 'debug' => $debug]);
        exit();
    }

    // Check total medicines count
    $countStmt = $pdo->query("SELECT COUNT(*) as total FROM medicines");
    $debug['total_medicines'] = $countStmt->fetch()['total'];
    
    // Check active medicines with stock
    $activeStmt = $pdo->query("SELECT COUNT(*) as active FROM medicines WHERE status = 'active' AND stock_quantity > 0");
    $debug['active_with_stock'] = $activeStmt->fetch()['active'];

    // Perform search
    $stmt = $pdo->prepare("
        SELECT id, name, generic_name, selling_price, stock_quantity, barcode, status
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
    
    $debug['search_query'] = $searchQuery;
    $debug['results_count'] = count($medicines);

    echo json_encode([
        'success' => true,
        'medicines' => $medicines,
        'debug' => $debug
    ]);

} catch (Exception $e) {
    $debug['error'] = $e->getMessage();
    echo json_encode([
        'success' => false,
        'message' => 'Error searching medicines: ' . $e->getMessage(),
        'debug' => $debug
    ]);
}
?>