<?php
require_once __DIR__ . '/../../bootstrap.php';

// Redirect to login if not authenticated
if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$user = getCurrentUser();

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_FILES['profile_image'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit();
}

try {
    $file = $_FILES['profile_image'];
    
    // Validate file
    if ($file['error'] !== UPLOAD_ERR_OK) {
        throw new Exception('File upload failed');
    }
    
    // Check file size (5MB max)
    if ($file['size'] > MAX_FILE_SIZE) {
        throw new Exception('File size too large. Maximum size is 5MB');
    }
    
    // Check file type
    $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    
    if (!in_array($mimeType, $allowedTypes)) {
        throw new Exception('Invalid file type. Only JPG, PNG, and GIF files are allowed');
    }
    
    // Generate unique filename
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = 'profile_' . $user['id'] . '_' . time() . '.' . $extension;
    $uploadPath = UPLOADS_DIR . '/profiles/' . $filename;
    
    // Create profiles directory if it doesn't exist
    if (!is_dir(UPLOADS_DIR . '/profiles')) {
        mkdir(UPLOADS_DIR . '/profiles', 0755, true);
    }
    
    // Move uploaded file
    if (!move_uploaded_file($file['tmp_name'], $uploadPath)) {
        throw new Exception('Failed to save uploaded file');
    }
    
    // Update user profile image in database
    global $pdo;
    $stmt = $pdo->prepare("UPDATE users SET profile_image = ? WHERE id = ?");
    $stmt->execute([$filename, $user['id']]);
    
    // Remove old profile image if it exists
    $oldImage = $user['profile_image'] ?? null;
    if ($oldImage && file_exists(UPLOADS_DIR . '/profiles/' . $oldImage)) {
        unlink(UPLOADS_DIR . '/profiles/' . $oldImage);
    }
    
    $imageUrl = '/uploads/profiles/' . $filename;
    
    echo json_encode([
        'success' => true,
        'message' => 'Profile image updated successfully',
        'image_url' => $imageUrl
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}