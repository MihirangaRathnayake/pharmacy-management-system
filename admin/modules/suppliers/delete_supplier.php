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
    echo json_encode(['success' => false, 'message' => 'Supplier ID is required']);
    exit();
}

try {
    // Check if supplier exists
    $stmt = $pdo->prepare("SELECT name FROM suppliers WHERE id = ?");
    $stmt->execute([$id]);
    $supplier = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$supplier) {
        echo json_encode(['success' => false, 'message' => 'Supplier not found']);
        exit();
    }

    // Check if supplier has medicines linked
    $medStmt = $pdo->prepare("SELECT COUNT(*) as count FROM medicines WHERE supplier_id = ?");
    $medStmt->execute([$id]);
    $medCount = $medStmt->fetch()['count'];

    if ($medCount > 0) {
        // Mark as inactive instead of deleting
        $updateStmt = $pdo->prepare("UPDATE suppliers SET status = 'inactive', updated_at = NOW() WHERE id = ?");
        $updateStmt->execute([$id]);

        echo json_encode([
            'success' => true,
            'message' => 'Supplier marked as inactive (has linked medicines)',
            'action' => 'deactivated'
        ]);
    } else {
        // Safe to delete
        $deleteStmt = $pdo->prepare("DELETE FROM suppliers WHERE id = ?");
        $deleteStmt->execute([$id]);

        echo json_encode([
            'success' => true,
            'message' => 'Supplier deleted successfully',
            'action' => 'deleted'
        ]);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
