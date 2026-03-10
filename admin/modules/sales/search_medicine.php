<?php
require_once dirname(__DIR__, 2) . '/bootstrap.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit();
}

$query = trim($_GET['q'] ?? '');

if (strlen($query) < 1) {
    echo json_encode(['success' => true, 'medicines' => []]);
    exit();
}

$stmt = $pdo->prepare("
    SELECT id, name, generic_name, barcode, selling_price, stock_quantity, expiry_date
    FROM medicines
    WHERE status = 'active'
      AND (name LIKE :q1 OR generic_name LIKE :q2 OR barcode LIKE :q3)
    ORDER BY name ASC
    LIMIT 20
");

$searchTerm = '%' . $query . '%';
$stmt->execute([
    ':q1' => $searchTerm,
    ':q2' => $searchTerm,
    ':q3' => $searchTerm,
]);

$medicines = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode(['success' => true, 'medicines' => $medicines]);
