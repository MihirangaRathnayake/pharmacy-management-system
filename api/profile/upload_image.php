<?php
require_once '../../bootstrap.php';
requireLogin();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$user_id = $_SESSION['user_id'];

// Check if file was uploaded
if (!isset($_FILES['profile_image']) || $_FILES['profile_image']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['success' => false, 'message' => 'No file uploaded or upload error']);
    exit;
}

$file = $_FILES['profile_image'];

// Validate file type
$allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
if (!in_array($file['type'], $allowed_types)) {
    echo json_encode(['success' => false, 'message' => 'Invalid file type. Only JPEG, PNG, and GIF are allowed']);
    exit;
}

// Validate file size (max 5MB)
if ($file['size'] > 5 * 1024 * 1024) {
    echo json_encode(['success' => false, 'message' => 'File size too large. Maximum 5MB allowed']);
    exit;
}

try {
    // Create uploads directory if it doesn't exist
    $upload_dir = '../../uploads/profiles';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }
    
    // Generate unique filename
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = 'profile_' . $user_id . '_' . time() . '.' . $extension;
    $filepath = $upload_dir . '/' . $filename;
    
    // Get current profile image to delete later
    $stmt = $pdo->prepare("SELECT profile_image FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $current_user = $stmt->fetch();
    $old_image = $current_user['profile_image'];
    
    // Move uploaded file
    if (!move_uploaded_file($file['tmp_name'], $filepath)) {
        echo json_encode(['success' => false, 'message' => 'Failed to save uploaded file']);
        exit;
    }
    
    // Update database
    $stmt = $pdo->prepare("UPDATE users SET profile_image = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
    $stmt->execute([$filename, $user_id]);
    
    // Delete old image if it exists
    if ($old_image && file_exists($upload_dir . '/' . $old_image)) {
        unlink($upload_dir . '/' . $old_image);
    }
    
    // Return success with image URL
    $image_url = '/uploads/profiles/' . $filename;
    echo json_encode([
        'success' => true, 
        'message' => 'Profile image updated successfully',
        'image_url' => $image_url
    ]);
    
} catch (PDOException $e) {
    error_log("Profile image upload error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error occurred']);
} catch (Exception $e) {
    error_log("Profile image upload error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'An error occurred while uploading the image']);
}
?>