<?php
header('Content-Type: application/json');
require_once '../config/database.php';

try {
    if (!isset($_GET['q']) || strlen(trim($_GET['q'])) < 2) {
        throw new Exception('Query must be at least 2 characters');
    }

    $query = trim($_GET['q']);
    $search_param = "%$query%";

    $stmt = $pdo->prepare("
        SELECT DISTINCT name, generic_name
        FROM medicines 
        WHERE status = 'active' 
        AND stock_quantity > 0 
        AND (name LIKE ? OR generic_name LIKE ?)
        ORDER BY 
            CASE 
                WHEN name LIKE ? THEN 1 
                WHEN generic_name LIKE ? THEN 2 
                ELSE 3 
            END,
            name ASC
        LIMIT 8
    ");
    
    $stmt->execute([
        $search_param, 
        $search_param,
        "$query%",
        "$query%"
    ]);
    
    $suggestions = $stmt->fetchAll();

    echo json_encode([
        'success' => true,
        'suggestions' => $suggestions
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}