<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../../config/database.php';

$productId = $_GET['id'] ?? null;

if (!$productId) {
    echo json_encode(['success' => false, 'message' => 'Product ID is required']);
    exit();
}

try {
    $stmt = $pdo->prepare("
        SELECT m.*, c.name as category_name, s.name as supplier_name
        FROM medicines m
        LEFT JOIN categories c ON m.category_id = c.id
        LEFT JOIN suppliers s ON m.supplier_id = s.id
        WHERE m.id = ? AND m.status = 'active'
    ");
    
    $stmt->execute([$productId]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$product) {
        echo json_encode(['success' => false, 'message' => 'Product not found']);
        exit();
    }
    
    // Format product data for customer
    $productData = [
        'id' => $product['id'],
        'name' => $product['name'],
        'generic_name' => $product['generic_name'],
        'description' => $product['description'],
        'selling_price' => $product['selling_price'],
        'stock_quantity' => $product['stock_quantity'],
        'prescription_required' => (bool)$product['prescription_required'],
        'image' => $product['image'] ?: null,
        'category_name' => $product['category_name'],
        'supplier_name' => $product['supplier_name'],
        'dosage' => $product['dosage'],
        'unit' => $product['unit']
    ];
    
    echo json_encode([
        'success' => true,
        'product' => $productData
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error fetching product: ' . $e->getMessage()
    ]);
}
?>