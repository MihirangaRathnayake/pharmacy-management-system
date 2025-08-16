<?php
header('Content-Type: application/json');
session_start();
require_once '../config/database.php';
require_once '../includes/auth.php';

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$input = json_decode(file_get_contents('php://input'), true);

// Validate input
if (!$input || !isset($input['items']) || empty($input['items'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid sale data']);
    exit();
}

try {
    $pdo->beginTransaction();
    
    $user = getCurrentUser();
    
    // Insert sale record
    $stmt = $pdo->prepare("
        INSERT INTO sales (invoice_number, customer_id, user_id, subtotal, tax_amount, discount_amount, total_amount, payment_method, notes)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    
    $stmt->execute([
        $input['invoiceNumber'],
        $input['customerId'],
        $user['id'],
        $input['subtotal'],
        $input['taxAmount'],
        $input['discountAmount'],
        $input['totalAmount'],
        $input['paymentMethod'],
        $input['notes']
    ]);
    
    $saleId = $pdo->lastInsertId();
    
    // Insert sale items and update stock
    foreach ($input['items'] as $item) {
        // Insert sale item
        $stmt = $pdo->prepare("
            INSERT INTO sale_items (sale_id, medicine_id, quantity, unit_price, total_price)
            VALUES (?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([
            $saleId,
            $item['medicineId'],
            $item['quantity'],
            $item['price'],
            $item['total']
        ]);
        
        // Update medicine stock
        $stmt = $pdo->prepare("
            UPDATE medicines 
            SET stock_quantity = stock_quantity - ? 
            WHERE id = ? AND stock_quantity >= ?
        ");
        
        $stmt->execute([$item['quantity'], $item['medicineId'], $item['quantity']]);
        
        if ($stmt->rowCount() === 0) {
            throw new Exception("Insufficient stock for medicine ID: " . $item['medicineId']);
        }
        
        // Record stock movement
        $stmt = $pdo->prepare("
            INSERT INTO stock_movements (medicine_id, movement_type, quantity, reference_type, reference_id, created_by)
            VALUES (?, 'out', ?, 'sale', ?, ?)
        ");
        
        $stmt->execute([$item['medicineId'], $item['quantity'], $saleId, $user['id']]);
    }
    
    $pdo->commit();
    
    echo json_encode([
        'success' => true,
        'message' => 'Sale processed successfully',
        'saleId' => $saleId
    ]);

} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode([
        'success' => false,
        'message' => 'Error processing sale: ' . $e->getMessage()
    ]);
}
?>