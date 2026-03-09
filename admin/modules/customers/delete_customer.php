<?php
session_start();
require_once dirname(__DIR__, 2) . '/bootstrap.php';

requireLogin();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

$input = json_decode(file_get_contents('php://input'), true);
$id = $input['id'] ?? null;

if (!$id) {
    echo json_encode(['success' => false, 'message' => 'Customer ID is required']);
    exit();
}

try {
    // Check if customer exists
    $stmt = $pdo->prepare("SELECT name FROM customers WHERE id = ?");
    $stmt->execute([$id]);
    $customer = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$customer) {
        echo json_encode(['success' => false, 'message' => 'Customer not found']);
        exit();
    }
    
    // Check if customer has sales history
    $salesStmt = $pdo->prepare("SELECT COUNT(*) as count FROM sales WHERE customer_id = ?");
    $salesStmt->execute([$id]);
    $salesCount = $salesStmt->fetch()['count'];
    
    if ($salesCount > 0) {
        // If customer has sales history, mark as inactive instead of deleting
        $updateStmt = $pdo->prepare("UPDATE customers SET status = 'inactive', updated_at = NOW() WHERE id = ?");
        $updateStmt->execute([$id]);
        
        echo json_encode([
            'success' => true, 
            'message' => 'Customer marked as inactive (has purchase history)',
            'action' => 'deactivated'
        ]);
    } else {
        // If no sales history, safe to delete
        $deleteStmt = $pdo->prepare("DELETE FROM customers WHERE id = ?");
        $deleteStmt->execute([$id]);
        
        echo json_encode([
            'success' => true, 
            'message' => 'Customer deleted successfully',
            'action' => 'deleted'
        ]);
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>