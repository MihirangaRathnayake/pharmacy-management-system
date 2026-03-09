<?php
require_once dirname(__DIR__, 2) . '/bootstrap.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit();
}

$user = getCurrentUser();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

$input = json_decode(file_get_contents('php://input'), true);

// Validate input
if (!$input || !isset($input['items']) || empty($input['items'])) {
    echo json_encode(['success' => false, 'message' => 'No items provided']);
    exit();
}

try {
    $pdo->beginTransaction();

    // Calculate totals
    $subtotal = 0;
    $tax_rate = 0.18; // 18% GST
    
    foreach ($input['items'] as $item) {
        $subtotal += $item['quantity'] * $item['unit_price'];
    }
    
    $discount_amount = floatval($input['discount_amount'] ?? 0);
    $tax_amount = ($subtotal - $discount_amount) * $tax_rate;
    $total_amount = $subtotal + $tax_amount - $discount_amount;

    // Generate invoice number
    $invoice_number = $input['invoice_number'] ?? generateInvoiceNumber();

    // Insert sale record
    $stmt = $pdo->prepare("
        INSERT INTO sales (invoice_number, customer_id, user_id, subtotal, tax_amount, discount_amount, total_amount, payment_method, notes, status) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'completed')
    ");
    
    $customer_id = !empty($input['customer_id']) ? $input['customer_id'] : null;
    $payment_method = $input['payment_method'] ?? 'cash';
    $notes = $input['notes'] ?? '';
    
    $stmt->execute([
        $invoice_number,
        $customer_id,
        $user['id'],
        $subtotal,
        $tax_amount,
        $discount_amount,
        $total_amount,
        $payment_method,
        $notes
    ]);

    $sale_id = $pdo->lastInsertId();

    // Insert sale items
    $stmt = $pdo->prepare("
        INSERT INTO sale_items (sale_id, medicine_id, quantity, unit_price, total_price, tax_amount) 
        VALUES (?, ?, ?, ?, ?, ?)
    ");

    foreach ($input['items'] as $item) {
        $item_total = $item['quantity'] * $item['unit_price'];
        $item_tax = $item_total * $tax_rate;
        
        $stmt->execute([
            $sale_id,
            $item['medicine_id'],
            $item['quantity'],
            $item['unit_price'],
            $item_total,
            $item_tax
        ]);

        // Update medicine stock
        $update_stock = $pdo->prepare("UPDATE medicines SET stock_quantity = stock_quantity - ? WHERE id = ?");
        $update_stock->execute([$item['quantity'], $item['medicine_id']]);
    }

    $pdo->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Sale completed successfully',
        'sale_id' => $sale_id,
        'invoice_number' => $invoice_number,
        'total_amount' => $total_amount,
        'invoice_url' => "invoice.php?id={$sale_id}"
    ]);

} catch (Exception $e) {
    $pdo->rollBack();
    error_log("Sale processing error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error processing sale: ' . $e->getMessage()]);
}
?>