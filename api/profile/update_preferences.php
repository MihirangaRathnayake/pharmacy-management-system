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
$theme = $_POST['theme'] ?? 'light';
$language = $_POST['language'] ?? 'en';
$timezone = $_POST['timezone'] ?? 'Asia/Kolkata';
$notifications = isset($_POST['notifications']) ? 1 : 0;
$email_notifications = isset($_POST['email_notifications']) ? 1 : 0;

// Validation
$valid_themes = ['light', 'dark', 'auto'];
$valid_languages = ['en', 'hi'];
$valid_timezones = ['Asia/Kolkata', 'UTC'];

if (!in_array($theme, $valid_themes)) {
    echo json_encode(['success' => false, 'message' => 'Invalid theme selected']);
    exit;
}

if (!in_array($language, $valid_languages)) {
    echo json_encode(['success' => false, 'message' => 'Invalid language selected']);
    exit;
}

if (!in_array($timezone, $valid_timezones)) {
    echo json_encode(['success' => false, 'message' => 'Invalid timezone selected']);
    exit;
}

try {
    // Check if preferences exist
    $stmt = $pdo->prepare("SELECT id FROM user_preferences WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $exists = $stmt->fetch();
    
    if ($exists) {
        // Update existing preferences
        $stmt = $pdo->prepare("
            UPDATE user_preferences 
            SET theme = ?, language = ?, timezone = ?, notifications = ?, email_notifications = ?, updated_at = CURRENT_TIMESTAMP 
            WHERE user_id = ?
        ");
        $stmt->execute([$theme, $language, $timezone, $notifications, $email_notifications, $user_id]);
    } else {
        // Insert new preferences
        $stmt = $pdo->prepare("
            INSERT INTO user_preferences (user_id, theme, language, timezone, notifications, email_notifications) 
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([$user_id, $theme, $language, $timezone, $notifications, $email_notifications]);
    }
    
    // Update session data
    $_SESSION['user_theme'] = $theme;
    $_SESSION['user_language'] = $language;
    $_SESSION['user_timezone'] = $timezone;
    
    echo json_encode(['success' => true, 'message' => 'Preferences updated successfully']);
    
} catch (PDOException $e) {
    error_log("Preferences update error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error occurred']);
}
?>