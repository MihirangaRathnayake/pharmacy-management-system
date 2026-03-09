<?php
require_once '../bootstrap.php';
requireLogin();

header('Content-Type: application/json');

$user_id = $_SESSION['user_id'];
$action = $_POST['action'] ?? '';

try {
    switch ($action) {
        case 'upload_profile_image':
            handleProfileImageUpload($user_id);
            break;
            
        case 'update_profile':
            handleProfileUpdate($user_id);
            break;
            
        case 'update_preferences':
            handlePreferencesUpdate($user_id);
            break;
            
        case 'change_password':
            handlePasswordChange($user_id);
            break;
            
        default:
            throw new Exception('Invalid action');
    }
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

function handleProfileImageUpload($user_id) {
    global $pdo;
    
    if (!isset($_FILES['profile_image']) || $_FILES['profile_image']['error'] !== UPLOAD_ERR_OK) {
        throw new Exception('No file uploaded or upload error');
    }
    
    $file = $_FILES['profile_image'];
    
    // Validate file type
    $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
    $file_type = mime_content_type($file['tmp_name']);
    
    if (!in_array($file_type, $allowed_types)) {
        throw new Exception('Invalid file type. Please upload JPEG, PNG, or GIF images only.');
    }
    
    // Validate file size (5MB max)
    if ($file['size'] > 5 * 1024 * 1024) {
        throw new Exception('File size too large. Maximum size is 5MB.');
    }
    
    // Create uploads directory if it doesn't exist
    $upload_dir = '../../uploads/profiles/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }
    
    // Generate unique filename
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = 'profile_' . $user_id . '_' . time() . '.' . $extension;
    $upload_path = $upload_dir . $filename;
    
    // Get current profile image to delete
    $current_image = $pdo->prepare("SELECT profile_image FROM users WHERE id = ?");
    $current_image->execute([$user_id]);
    $current = $current_image->fetchColumn();
    
    // Move uploaded file
    if (!move_uploaded_file($file['tmp_name'], $upload_path)) {
        throw new Exception('Failed to save uploaded file');
    }
    
    // Update database
    $stmt = $pdo->prepare("UPDATE users SET profile_image = ?, updated_at = NOW() WHERE id = ?");
    $stmt->execute([$filename, $user_id]);
    
    // Delete old profile image
    if ($current && $current !== $filename) {
        $old_path = $upload_dir . $current;
        if (file_exists($old_path)) {
            unlink($old_path);
        }
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Profile image updated successfully',
        'image_url' => url('uploads/profiles/' . $filename)
    ]);
}

function handleProfileUpdate($user_id) {
    global $pdo;
    
    $name = trim($_POST['name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    
    // Validate inputs
    if (empty($name)) {
        throw new Exception('Name is required');
    }
    
    if (strlen($name) < 2) {
        throw new Exception('Name must be at least 2 characters long');
    }
    
    if (!empty($phone) && !preg_match('/^[\+]?[1-9][\d]{0,15}$/', $phone)) {
        throw new Exception('Please enter a valid phone number');
    }
    
    // Update user profile
    $stmt = $pdo->prepare("UPDATE users SET name = ?, phone = ?, address = ?, updated_at = NOW() WHERE id = ?");
    $stmt->execute([$name, $phone, $address, $user_id]);
    
    // Update session data
    $_SESSION['user_name'] = $name;
    
    echo json_encode([
        'success' => true,
        'message' => 'Profile updated successfully',
        'name' => $name
    ]);
}

function handlePreferencesUpdate($user_id) {
    global $pdo;
    
    $theme = $_POST['theme'] ?? 'light';
    $language = $_POST['language'] ?? 'en';
    $timezone = $_POST['timezone'] ?? 'Asia/Kolkata';
    $notifications = ($_POST['notifications'] ?? '0') === '1';
    $email_notifications = ($_POST['email_notifications'] ?? '0') === '1';
    
    // Validate inputs
    $valid_themes = ['light', 'dark', 'auto'];
    $valid_languages = ['en', 'hi'];
    $valid_timezones = ['Asia/Kolkata', 'UTC'];
    
    if (!in_array($theme, $valid_themes)) {
        $theme = 'light';
    }
    
    if (!in_array($language, $valid_languages)) {
        $language = 'en';
    }
    
    if (!in_array($timezone, $valid_timezones)) {
        $timezone = 'Asia/Kolkata';
    }
    
    // Check if preferences exist
    $existing = $pdo->prepare("SELECT user_id FROM user_preferences WHERE user_id = ?");
    $existing->execute([$user_id]);
    
    if ($existing->fetchColumn()) {
        // Update existing preferences
        $stmt = $pdo->prepare("
            UPDATE user_preferences 
            SET theme = ?, language = ?, timezone = ?, notifications = ?, email_notifications = ?, updated_at = NOW() 
            WHERE user_id = ?
        ");
        $stmt->execute([$theme, $language, $timezone, $notifications, $email_notifications, $user_id]);
    } else {
        // Insert new preferences
        $stmt = $pdo->prepare("
            INSERT INTO user_preferences (user_id, theme, language, timezone, notifications, email_notifications, created_at, updated_at) 
            VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())
        ");
        $stmt->execute([$user_id, $theme, $language, $timezone, $notifications, $email_notifications]);
    }
    
    // Check if theme changed
    $theme_changed = isset($_SESSION['user_theme']) && $_SESSION['user_theme'] !== $theme;
    $_SESSION['user_theme'] = $theme;
    
    echo json_encode([
        'success' => true,
        'message' => 'Preferences updated successfully',
        'theme_changed' => $theme_changed
    ]);
}

function handlePasswordChange($user_id) {
    global $pdo;
    
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    
    if (empty($current_password) || empty($new_password)) {
        throw new Exception('All password fields are required');
    }
    
    // Get current password hash
    $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();
    
    if (!$user || !password_verify($current_password, $user['password'])) {
        throw new Exception('Current password is incorrect');
    }
    
    // Validate new password
    if (strlen($new_password) < 8) {
        throw new Exception('New password must be at least 8 characters long');
    }
    
    if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)/', $new_password)) {
        throw new Exception('New password must contain at least one uppercase letter, one lowercase letter, and one number');
    }
    
    // Update password
    $new_password_hash = password_hash($new_password, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("UPDATE users SET password = ?, updated_at = NOW() WHERE id = ?");
    $stmt->execute([$new_password_hash, $user_id]);
    
    echo json_encode([
        'success' => true,
        'message' => 'Password updated successfully'
    ]);
}
?>