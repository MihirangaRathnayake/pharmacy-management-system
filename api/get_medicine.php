<?php
header('Content-Type: application/json');
require_once '../config/database.php';

try {
    if (!isset($_GET['id']) || empty($_GET['id'])) {
        throw new Exception('Medicine ID is required');
    }

    $medicine_id = intval($_GET['id']);

    $stmt = $pdo->prepare("
        SELECT m.*, c.name as category_name, s.name as supplier_name
        FROM medicines m 
        LEFT JOIN categories c ON m.category_id = c.id 
        LEFT JOIN suppliers s ON m.supplier_id = s.id
        WHERE m.id = ? AND m.status = 'active'
    ");
    
    $stmt->execute([$medicine_id]);
    $medicine = $stmt->fetch();

    if (!$medicine) {
        throw new Exception('Medicine not found');
    }

    // Format the response
    $response = [
        'success' => true,
        'data' => [
            'id' => $medicine['id'],
            'name' => $medicine['name'],
            'generic_name' => $medicine['generic_name'],
            'category_name' => $medicine['category_name'],
            'supplier_name' => $medicine['supplier_name'],
            'description' => $medicine['description'],
            'dosage' => $medicine['dosage'],
            'unit' => $medicine['unit'],
            'selling_price' => $medicine['selling_price'],
            'stock_quantity' => $medicine['stock_quantity'],
            'min_stock_level' => $medicine['min_stock_level'],
            'batch_number' => $medicine['batch_number'],
            'expiry_date' => $medicine['expiry_date'],
            'manufacture_date' => $medicine['manufacture_date'],
            'prescription_required' => (bool)$medicine['prescription_required'],
            'image' => $medicine['image'],
            'barcode' => $medicine['barcode']
        ]
    ];

    echo json_encode($response);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}