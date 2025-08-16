<?php
require_once __DIR__ . '/bootstrap.php';

// Check if user is logged in
if (!isLoggedIn()) {
    echo "<p>❌ Not logged in. <a href='quick_login.php'>Click here to login</a></p>";
    exit();
}

$user = getCurrentUser();

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $response = ['success' => false, 'message' => ''];
    
    try {
        global $pdo;
        
        if (isset($_POST['action'])) {
            switch ($_POST['action']) {
                case 'update_profile':
                    $name = trim($_POST['name']);
                    $email = trim($_POST['email']);
                    $phone = trim($_POST['phone']);
                    
                    if (empty($name) || empty($email)) {
                        throw new Exception('Name and email are required');
                    }
                    
                    $stmt = $pdo->prepare("UPDATE users SET name = ?, email = ?, phone = ? WHERE id = ?");
                    $stmt->execute([$name, $email, $phone, $user['id']]);
                    
                    $response = ['success' => true, 'message' => 'Profile updated successfully'];
                    break;
                    
                case 'update_preferences':
                    $theme = $_POST['theme'] ?? 'light';
                    $language = $_POST['language'] ?? 'en';
                    $timezone = $_POST['timezone'] ?? 'Asia/Kolkata';
                    $notifications = isset($_POST['notifications']) ? 1 : 0;
                    $email_notifications = isset($_POST['email_notifications']) ? 1 : 0;
                    
                    // Update or insert user preferences
                    $stmt = $pdo->prepare("
                        INSERT INTO user_preferences (user_id, theme, language, timezone, notifications, email_notifications) 
                        VALUES (?, ?, ?, ?, ?, ?) 
                        ON DUPLICATE KEY UPDATE 
                        theme = VALUES(theme), 
                        language = VALUES(language), 
                        timezone = VALUES(timezone), 
                        notifications = VALUES(notifications), 
                        email_notifications = VALUES(email_notifications)
                    ");
                    $stmt->execute([$user['id'], $theme, $language, $timezone, $notifications, $email_notifications]);
                    
                    $response = ['success' => true, 'message' => 'Preferences updated successfully'];
                    break;
            }
        }
    } catch (Exception $e) {
        $response = ['success' => false, 'message' => $e->getMessage()];
    }
    
    // Show response message
    if ($response['success']) {
        echo "<div style='background: #d4edda; color: #155724; padding: 10px; border-radius: 5px; margin: 10px 0;'>";
        echo "✅ " . $response['message'];
        echo "</div>";
    } else {
        echo "<div style='background: #f8d7da; color: #721c24; padding: 10px; border-radius: 5px; margin: 10px 0;'>";
        echo "❌ " . $response['message'];
        echo "</div>";
    }
}

// Get user preferences
try {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM user_preferences WHERE user_id = ?");
    $stmt->execute([$user['id']]);
    $preferences = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$preferences) {
        $preferences = [
            'theme' => 'light',
            'language' => 'en',
            'timezone' => 'Asia/Kolkata',
            'notifications' => 1,
            'email_notifications' => 1
        ];
    }
} catch (Exception $e) {
    $preferences = [
        'theme' => 'light',
        'language' => 'en',
        'timezone' => 'Asia/Kolkata',
        'notifications' => 1,
        'email_notifications' => 1
    ];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - <?php echo APP_NAME; ?></title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            margin: 0;
            padding: 20px;
            background: #f5f5f5;
            line-height: 1.6;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        .header {
            background: #28a745;
            color: white;
            padding: 20px;
            text-align: center;
        }
        .tabs {
            display: flex;
            border-bottom: 1px solid #ddd;
        }
        .tab {
            flex: 1;
            padding: 15px;
            text-align: center;
            cursor: pointer;
            border: none;
            background: none;
            font-size: 16px;
        }
        .tab.active {
            background: #28a745;
            color: white;
        }
        .tab:hover {
            background: #e9ecef;
        }
        .tab.active:hover {
            background: #218838;
        }
        .content {
            padding: 30px;
        }
        .tab-content {
            display: none;
        }
        .tab-content.active {
            display: block;
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
            color: #333;
        }
        input, select, textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
            box-sizing: border-box;
        }
        input:focus, select:focus, textarea:focus {
            outline: none;
            border-color: #28a745;
            box-shadow: 0 0 0 2px rgba(40, 167, 69, 0.2);
        }
        .btn {
            background: #28a745;
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            margin-top: 10px;
        }
        .btn:hover {
            background: #218838;
        }
        .grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        @media (max-width: 600px) {
            .grid {
                grid-template-columns: 1fr;
            }
            .tabs {
                flex-direction: column;
            }
        }
        .theme-options {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 15px;
            margin-top: 10px;
        }
        .theme-option {
            border: 2px solid #ddd;
            border-radius: 8px;
            padding: 15px;
            text-align: center;
            cursor: pointer;
            transition: all 0.2s;
        }
        .theme-option:hover {
            border-color: #28a745;
        }
        .theme-option.selected {
            border-color: #28a745;
            background: #f8fff9;
        }
        .theme-preview {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            margin: 0 auto 10px;
        }
        .light { background: linear-gradient(45deg, #fff 50%, #f8f9fa 50%); }
        .dark { background: linear-gradient(45deg, #343a40 50%, #212529 50%); }
        .auto { background: linear-gradient(45deg, #fff 25%, #343a40 25%, #343a40 50%, #fff 50%, #fff 75%, #343a40 75%); }
        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 10px;
            margin: 10px 0;
        }
        .checkbox-group input[type="checkbox"] {
            width: auto;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>⚙️ Settings</h1>
            <p>Welcome, <?php echo htmlspecialchars($user['name']); ?>!</p>
        </div>
        
        <div class="tabs">
            <button class="tab active" onclick="showTab('profile')">👤 Profile</button>
            <button class="tab" onclick="showTab('preferences')">🎨 Preferences</button>
            <button class="tab" onclick="showTab('notifications')">🔔 Notifications</button>
        </div>
        
        <div class="content">
            <!-- Profile Tab -->
            <div id="profile" class="tab-content active">
                <h2>Profile Information</h2>
                <form method="POST">
                    <input type="hidden" name="action" value="update_profile">
                    
                    <div class="grid">
                        <div class="form-group">
                            <label>Full Name</label>
                            <input type="text" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label>Email Address</label>
                            <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label>Phone Number</label>
                            <input type="tel" name="phone" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
                        </div>
                        
                        <div class="form-group">
                            <label>Role</label>
                            <input type="text" value="<?php echo htmlspecialchars($user['role']); ?>" readonly style="background: #f8f9fa;">
                        </div>
                    </div>
                    
                    <button type="submit" class="btn">💾 Save Profile</button>
                </form>
            </div>
            
            <!-- Preferences Tab -->
            <div id="preferences" class="tab-content">
                <h2>Preferences</h2>
                <form method="POST">
                    <input type="hidden" name="action" value="update_preferences">
                    
                    <div class="form-group">
                        <label>Theme</label>
                        <div class="theme-options">
                            <div class="theme-option <?php echo $preferences['theme'] === 'light' ? 'selected' : ''; ?>" onclick="selectTheme('light')">
                                <div class="theme-preview light"></div>
                                <strong>Light</strong>
                                <p>Default theme</p>
                                <input type="radio" name="theme" value="light" <?php echo $preferences['theme'] === 'light' ? 'checked' : ''; ?> style="display: none;">
                            </div>
                            
                            <div class="theme-option <?php echo $preferences['theme'] === 'dark' ? 'selected' : ''; ?>" onclick="selectTheme('dark')">
                                <div class="theme-preview dark"></div>
                                <strong>Dark</strong>
                                <p>Dark theme</p>
                                <input type="radio" name="theme" value="dark" <?php echo $preferences['theme'] === 'dark' ? 'checked' : ''; ?> style="display: none;">
                            </div>
                            
                            <div class="theme-option <?php echo $preferences['theme'] === 'auto' ? 'selected' : ''; ?>" onclick="selectTheme('auto')">
                                <div class="theme-preview auto"></div>
                                <strong>Auto</strong>
                                <p>System preference</p>
                                <input type="radio" name="theme" value="auto" <?php echo $preferences['theme'] === 'auto' ? 'checked' : ''; ?> style="display: none;">
                            </div>
                        </div>
                    </div>
                    
                    <div class="grid">
                        <div class="form-group">
                            <label>Language</label>
                            <select name="language">
                                <option value="en" <?php echo $preferences['language'] === 'en' ? 'selected' : ''; ?>>🇺🇸 English</option>
                                <option value="ur" <?php echo $preferences['language'] === 'ur' ? 'selected' : ''; ?>>🇵🇰 Urdu</option>
                                <option value="hi" <?php echo $preferences['language'] === 'hi' ? 'selected' : ''; ?>>🇮🇳 Hindi</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label>Timezone</label>
                            <select name="timezone">
                                <option value="Asia/Kolkata" <?php echo $preferences['timezone'] === 'Asia/Kolkata' ? 'selected' : ''; ?>>Asia/Kolkata (IST)</option>
                                <option value="Asia/Karachi" <?php echo $preferences['timezone'] === 'Asia/Karachi' ? 'selected' : ''; ?>>Asia/Karachi (PKT)</option>
                                <option value="UTC" <?php echo $preferences['timezone'] === 'UTC' ? 'selected' : ''; ?>>UTC</option>
                            </select>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn">💾 Save Preferences</button>
                </form>
            </div>
            
            <!-- Notifications Tab -->
            <div id="notifications" class="tab-content">
                <h2>Notification Settings</h2>
                <form method="POST">
                    <input type="hidden" name="action" value="update_preferences">
                    
                    <div class="checkbox-group">
                        <input type="checkbox" name="notifications" id="notifications" <?php echo $preferences['notifications'] ? 'checked' : ''; ?>>
                        <label for="notifications">🔔 Enable push notifications</label>
                    </div>
                    
                    <div class="checkbox-group">
                        <input type="checkbox" name="email_notifications" id="email_notifications" <?php echo $preferences['email_notifications'] ? 'checked' : ''; ?>>
                        <label for="email_notifications">📧 Enable email notifications</label>
                    </div>
                    
                    <button type="submit" class="btn">💾 Save Notification Settings</button>
                </form>
            </div>
        </div>
    </div>
    
    <div style="text-align: center; margin: 20px; padding: 20px; background: white; border-radius: 8px;">
        <p><a href="admin_dashboard.php" style="color: #28a745; text-decoration: none;">← Back to Dashboard</a></p>
        <p><small>Server: <?php echo $_SERVER['HTTP_HOST']; ?> | PHP: <?php echo PHP_VERSION; ?></small></p>
    </div>

    <script>
        function showTab(tabName) {
            // Hide all tab contents
            const contents = document.querySelectorAll('.tab-content');
            contents.forEach(content => content.classList.remove('active'));
            
            // Remove active class from all tabs
            const tabs = document.querySelectorAll('.tab');
            tabs.forEach(tab => tab.classList.remove('active'));
            
            // Show selected tab content
            document.getElementById(tabName).classList.add('active');
            
            // Add active class to clicked tab
            event.target.classList.add('active');
        }
        
        function selectTheme(theme) {
            // Remove selected class from all theme options
            const options = document.querySelectorAll('.theme-option');
            options.forEach(option => option.classList.remove('selected'));
            
            // Add selected class to clicked option
            event.currentTarget.classList.add('selected');
            
            // Check the corresponding radio button
            const radio = event.currentTarget.querySelector('input[type="radio"]');
            radio.checked = true;
        }
    </script>
</body>
</html>