<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../../config/database.php';

$patientName = trim($_POST['patient_name'] ?? '');
$doctorName = trim($_POST['doctor_name'] ?? '');

if (!$patientName || !$doctorName) {
    echo json_encode(['success' => false, 'message' => 'Patient name and doctor name are required']);
    exit();
}

if (!isset($_FILES['prescription_file']) || $_FILES['prescription_file']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['success' => false, 'message' => 'Please upload a prescription file']);
    exit();
}

try {
    $file = $_FILES['prescription_file'];
    
    // Validate file type
    $allowedTypes = ['image/jpeg', 'image/png', 'image/jpg', 'application/pdf'];
    if (!in_array($file['type'], $allowedTypes)) {
        echo json_encode(['success' => false, 'message' => 'Only JPG, PNG, and PDF files are allowed']);
        exit();
    }
    
    // Validate file size (5MB max)
    if ($file['size'] > 5 * 1024 * 1024) {
        echo json_encode(['success' => false, 'message' => 'File size must be less than 5MB']);
        exit();
    }
    
    // Create upload directory if it doesn't exist
    $uploadDir = '../../uploads/prescriptions/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    
    // Generate unique filename
    $fileExtension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $fileName = 'prescription_' . time() . '_' . rand(1000, 9999) . '.' . $fileExtension;
    $filePath = $uploadDir . $fileName;
    
    // Move uploaded file
    if (!move_uploaded_file($file['tmp_name'], $filePath)) {
        echo json_encode(['success' => false, 'message' => 'Failed to upload file']);
        exit();
    }
    
    $pdo->beginTransaction();
    
    // Check if customer exists, create if not
    $customerPhone = $_POST['customer_phone'] ?? '';
    $customerEmail = $_POST['customer_email'] ?? '';
    
    if ($customerPhone) {
        $stmt = $pdo->prepare("SELECT id FROM customers WHERE phone = ? AND status = 'active'");
        $stmt->execute([$customerPhone]);
        $customer = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$customer) {
            // Create new customer
            $customerCode = 'CUST' . date('Ymd') . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
            $stmt = $pdo->prepare("
                INSERT INTO customers (name, phone, email, customer_code, status)
                VALUES (?, ?, ?, ?, 'active')
            ");
            $stmt->execute([$patientName, $customerPhone, $customerEmail, $customerCode]);
            $customerId = $pdo->lastInsertId();
        } else {
            $customerId = $customer['id'];
        }
    } else {
        // Create anonymous customer record
        $customerCode = 'GUEST' . date('Ymd') . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
        $stmt = $pdo->prepare("
            INSERT INTO customers (name, customer_code, status)
            VALUES (?, ?, 'active')
        ");
        $stmt->execute([$patientName, $customerCode]);
        $customerId = $pdo->lastInsertId();
    }
    
    // Insert prescription record
    $stmt = $pdo->prepare("
        INSERT INTO prescriptions (customer_id, doctor_name, prescription_date, image_path, status)
        VALUES (?, ?, CURDATE(), ?, 'pending')
    ");
    
    $relativePath = 'uploads/prescriptions/' . $fileName;
    $stmt->execute([$customerId, $doctorName, $relativePath]);
    $prescriptionId = $pdo->lastInsertId();
    
    $pdo->commit();
    
    echo json_encode([
        'success' => true,
        'message' => 'Prescription uploaded successfully! Our pharmacist will review it shortly.',
        'prescription_id' => $prescriptionId,
        'customer_code' => $customerCode ?? null
    ]);

} catch (Exception $e) {
    $pdo->rollBack();
    
    // Clean up uploaded file on error
    if (isset($filePath) && file_exists($filePath)) {
        unlink($filePath);
    }
    
    echo json_encode([
        'success' => false,
        'message' => 'Upload error: ' . $e->getMessage()
    ]);
}
?>