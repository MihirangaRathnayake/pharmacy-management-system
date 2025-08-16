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
    echo json_encode(['success' => false, 'message' => 'Medicine ID is required']);
    exit();
}

try {
    // Check if medicine exists
    $stmt = $pdo->prepare("SELECT name FROM medicines WHERE id = ?");
    $stmt->execute([$id]);
    $medicine = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$medicine) {
        echo json_encode(['success' => false, 'message' => 'Medicine not found']);
        exit();
    }
    
    // Check if medicine has been sold (has sale records)
    $salesStmt = $pdo->prepare("SELECT COUNT(*) as count FROM sale_items WHERE medicine_id = ?");
    $salesStmt->execute([$id]);
    $salesCount = $salesStmt->fetch()['count'];
    
    if ($salesCount > 0) {
        // If medicine has sales history, mark as discontinued instead of deleting
        $updateStmt = $pdo->prepare("UPDATE medicines SET status = 'discontinued', updated_at = NOW() WHERE id = ?");
        $updateStmt->execute([$id]);
        
        echo json_encode([
            'success' => true, 
            'message' => 'Medicine marked as discontinued (has sales history)',
            'action' => 'discontinued'
        ]);
    } else {
        // If no sales history, safe to delete
        $deleteStmt = $pdo->prepare("DELETE FROM medicines WHERE id = ?");
        $deleteStmt->execute([$id]);
        
        echo json_encode([
            'success' => true, 
            'message' => 'Medicine deleted successfully',
            'action' => 'deleted'
        ]);
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>