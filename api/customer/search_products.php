<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../../config/database.php';

$query = $_GET['q'] ?? '';
$category = $_GET['category'] ?? '';
$limit = min(20, intval($_GET['limit'] ?? 12));
$offset = max(0, intval($_GET['offset'] ?? 0));

if (strlen($query) < 2 && !$category) {
    echo json_encode(['success' => false, 'message' => 'Search query too short']);
    exit();
}

try {
    $whereConditions = ["m.status = 'active'", "m.stock_quantity > 0"];
    $params = [];
    
    if ($query) {
        $whereConditions[] = "(m.name LIKE :query OR m.generic_name LIKE :query OR m.description LIKE :query)";
        $params['query'] = "%$query%";
    }
    
    if ($category) {
        $whereConditions[] = "c.name = :category";
        $params['category'] = $category;
    }
    
    $whereClause = implode(' AND ', $whereConditions);
    
    $stmt = $pdo->prepare("
        SELECT m.id, m.name, m.generic_name, m.description, m.selling_price, 
               m.stock_quantity, m.prescription_required, m.image, m.dosage, m.unit,
               c.name as category_name
        FROM medicines m
        LEFT JOIN categories c ON m.category_id = c.id
        WHERE $whereClause
        ORDER BY m.name ASC
        LIMIT :limit OFFSET :offset
    ");
    
    foreach ($params as $key => $value) {
        $stmt->bindValue(":$key", $value);
    }
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    
    $stmt->execute();
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Format products for customer display
    $formattedProducts = array_map(function($product) {
        return [
            'id' => $product['id'],
            'name' => $product['name'],
            'generic_name' => $product['generic_name'],
            'description' => $product['description'],
            'selling_price' => $product['selling_price'],
            'stock_quantity' => $product['stock_quantity'],
            'prescription_required' => (bool)$product['prescription_required'],
            'image' => $product['image'] ?: null,
            'category_name' => $product['category_name'],
            'dosage' => $product['dosage'],
            'unit' => $product['unit'],
            'in_stock' => $product['stock_quantity'] > 0
        ];
    }, $products);
    
    echo json_encode([
        'success' => true,
        'products' => $formattedProducts,
        'total' => count($formattedProducts)
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error searching products: ' . $e->getMessage()
    ]);
}
?>