<?php
// Start session first if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../bootstrap.php';

// Redirect to login if not authenticated
if (!isLoggedIn()) {
    header('Location: ../../auth/login.php');
    exit();
}

$user = getCurrentUser();
$message = '';
$messageType = '';

// Simple profile image function
function getProfileImageUrl($userId) {
    global $pdo;
    try {
        if ($pdo) {
            $stmt = $pdo->prepare("SELECT profile_image FROM users WHERE id = ?");
            $stmt->execute([$userId]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($user && $user['profile_image'] && file_exists(__DIR__ . '/../../uploads/profiles/' . $user['profile_image'])) {
                return '../../uploads/profiles/' . $user['profile_image'];
            }
        }
    } catch (Exception $e) {
        // Return default image on error
    }
    
    // Return default avatar SVG
    return 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMTAwIiBoZWlnaHQ9IjEwMCIgdmlld0JveD0iMCAwIDEwMCAxMDAiIGZpbGw9Im5vbmUiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+CjxjaXJjbGUgY3g9IjUwIiBjeT0iNTAiIHI9IjUwIiBmaWxsPSIjRTVFN0VCIi8+CjxwYXRoIGQ9Ik01MCAyNUM0My4zNzUgMjUgMzggMzAuMzc1IDM4IDM3QzM4IDQzLjYyNSA0My4zNzUgNDkgNTAgNDlDNTYuNjI1IDQ5IDYyIDQzLjYyNSA2MiAzN0M2MiAzMC4zNzUgNTYuNjI1IDI1IDUwIDI1WiIgZmlsbD0iIzlDQTNBRiIvPgo8cGF0aCBkPSJNNTAgNTVDNDAuNjI1IDU1IDMzIDYyLjYyNSAzMyA3MlY3NUg2N1Y3MkM2NyA2Mi42MjUgNTkuMzc1IDU1IDUwIDU1WiIgZmlsbD0iIzlDQTNBRiIvPgo8L3N2Zz4K';
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        global $pdo;
        
        if (isset($_POST['action'])) {
            switch ($_POST['action']) {
                case 'update_profile':
                    $name = trim($_POST['name']);
                    $email = trim($_POST['email']);
                    $phone = trim($_POST['phone']);
                    $address = trim($_POST['address'] ?? '');
                    
                    if (empty($name) || empty($email)) {
                        throw new Exception('Name and email are required');
                    }
                    
                    $stmt = $pdo->prepare("UPDATE users SET name = ?, email = ?, phone = ?, address = ? WHERE id = ?");
                    $stmt->execute([$name, $email, $phone, $address, $user['id']]);
                    
                    $message = 'Profile updated successfully!';
                    $messageType = 'success';
                    
                    // Refresh user data
                    $user = getCurrentUser();
                    break;
                    
                case 'change_password':
                    $current_password = $_POST['current_password'];
                    $new_password = $_POST['new_password'];
                    $confirm_password = $_POST['confirm_password'];
                    
                    if (!password_verify($current_password, $user['password'])) {
                        throw new Exception('Current password is incorrect');
                    }
                    
                    if ($new_password !== $confirm_password) {
                        throw new Exception('New passwords do not match');
                    }
                    
                    if (strlen($new_password) < 6) {
                        throw new Exception('Password must be at least 6 characters long');
                    }
                    
                    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
                    $stmt->execute([$hashed_password, $user['id']]);
                    
                    $message = 'Password changed successfully!';
                    $messageType = 'success';
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
                    
                    $message = 'Preferences updated successfully!';
                    $messageType = 'success';
                    break;
            }
        }
    } catch (Exception $e) {
        $message = $e->getMessage();
        $messageType = 'error';
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
<html lang="en" data-theme="<?php echo $preferences['theme']; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - <?php echo APP_NAME; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        
        /* Dark mode styles */
        [data-theme="dark"] {
            --bg-primary: #111827;
            --bg-secondary: #1f2937;
            --bg-tertiary: #374151;
            --text-primary: #f9fafb;
            --text-secondary: #d1d5db;
            --border-color: #374151;
        }
        
        [data-theme="dark"] body {
            background-color: var(--bg-primary);
            color: var(--text-primary);
        }
        
        [data-theme="dark"] .bg-white {
            background-color: var(--bg-secondary) !important;
        }
        
        [data-theme="dark"] .bg-gray-50 {
            background-color: var(--bg-primary) !important;
        }
        
        [data-theme="dark"] .text-gray-800,
        [data-theme="dark"] .text-gray-900 {
            color: var(--text-primary) !important;
        }
        
        [data-theme="dark"] .text-gray-600,
        [data-theme="dark"] .text-gray-700 {
            color: var(--text-secondary) !important;
        }
        
        [data-theme="dark"] .border-gray-200,
        [data-theme="dark"] .border-gray-300 {
            border-color: var(--border-color) !important;
        }
        
        [data-theme="dark"] input,
        [data-theme="dark"] select,
        [data-theme="dark"] textarea {
            background-color: var(--bg-tertiary) !important;
            color: var(--text-primary) !important;
            border-color: var(--border-color) !important;
        }
        
        .theme-option input[type="radio"]:checked + .theme-card {
            border-color: #10b981 !important;
            background-color: #f0fdf4 !important;
        }
        
        .theme-card {
            transition: all 0.2s ease-in-out;
        }
        
        .theme-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }
        
        .alert {
            padding: 1rem;
            border-radius: 0.5rem;
            margin-bottom: 1rem;
            border: 1px solid transparent;
        }
        
        .alert-success {
            background-color: #d1fae5;
            border-color: #a7f3d0;
            color: #065f46;
        }
        
        .alert-error {
            background-color: #fee2e2;
            border-color: #fecaca;
            color: #991b1b;
        }
    </style>
</head>
<body class="bg-gray-50 transition-colors duration-300">
    <!-- Navigation Bar -->
    <nav class="bg-white shadow-lg sticky top-0 z-50">
        <div class="container mx-auto px-4">
            <div class="flex justify-between items-center py-4">
                <div class="flex items-center space-x-2">
                    <i class="fas fa-pills text-green-600 text-2xl"></i>
                    <span class="text-xl font-bold text-gray-800"><?php echo APP_NAME; ?></span>
                </div>
                
                <div class="flex items-center space-x-4">
                    <span class="text-gray-700">Welcome, <?php echo htmlspecialchars($user['name']); ?>!</span>
                    <a href="../../admin_dashboard.php" class="text-green-600 hover:text-green-700">
                        <i class="fas fa-home mr-1"></i>Dashboard
                    </a>
                    <a href="../../auth/logout.php" class="text-red-600 hover:text-red-700">
                        <i class="fas fa-sign-out-alt mr-1"></i>Logout
                    </a>
                </div>
            </div>
        </div>
    </nav>
    
    <div class="container mx-auto px-4 py-8">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-800 mb-2">Settings</h1>
            <p class="text-gray-600">Manage your account settings and preferences</p>
        </div>

        <!-- Success/Error Messages -->
        <?php if ($message): ?>
            <div class="alert alert-<?php echo $messageType; ?>">
                <div class="flex items-center">
                    <i class="fas fa-<?php echo $messageType === 'success' ? 'check-circle' : 'exclamation-circle'; ?> mr-2"></i>
                    <?php echo htmlspecialchars($message); ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- Settings Navigation -->
        <div class="bg-white rounded-lg shadow-md mb-6">
            <div class="border-b border-gray-200">
                <nav class="flex space-x-8 px-6">
                    <button class="settings-tab py-4 px-2 border-b-2 border-green-500 text-green-600 font-medium" data-tab="profile">
                        <i class="fas fa-user mr-2"></i>Profile
                    </button>
                    <button class="settings-tab py-4 px-2 border-b-2 border-transparent text-gray-500 hover:text-gray-700 font-medium" data-tab="security">
                        <i class="fas fa-shield-alt mr-2"></i>Security
                    </button>
                    <button class="settings-tab py-4 px-2 border-b-2 border-transparent text-gray-500 hover:text-gray-700 font-medium" data-tab="preferences">
                        <i class="fas fa-cog mr-2"></i>Preferences
                    </button>
                </nav>
            </div>
        </div>

        <!-- Settings Content -->
        <div class="bg-white rounded-lg shadow-md">
            <!-- Profile Tab -->
            <div id="profile-tab" class="settings-content p-6">
                <h2 class="text-xl font-semibold text-gray-800 mb-6">Profile Information</h2>
                
                <div class="flex items-center space-x-6 mb-8">
                    <div>
                        <img src="<?php echo getProfileImageUrl($user['id']); ?>" 
                             alt="Profile Picture" class="w-24 h-24 rounded-full object-cover border-4 border-gray-200">
                    </div>
                    <div>
                        <h3 class="text-lg font-medium text-gray-800"><?php echo htmlspecialchars($user['name']); ?></h3>
                        <p class="text-gray-600"><?php echo htmlspecialchars($user['role']); ?></p>
                        <p class="text-sm text-gray-500">Member since <?php echo date('M Y', strtotime($user['created_at'])); ?></p>
                    </div>
                </div>
                
                <form method="POST" class="space-y-6">
                    <input type="hidden" name="action" value="update_profile">
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Full Name</label>
                            <input type="text" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" 
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500" required>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Email Address</label>
                            <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" 
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500" required>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Phone Number</label>
                            <input type="tel" name="phone" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>" 
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Role</label>
                            <input type="text" value="<?php echo ucfirst(htmlspecialchars($user['role'])); ?>" 
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg bg-gray-50" readonly>
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Address</label>
                        <textarea name="address" rows="3" 
                                  class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500" 
                                  placeholder="Enter your address"><?php echo htmlspecialchars($user['address'] ?? ''); ?></textarea>
                    </div>
                    
                    <div class="flex justify-end">
                        <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-8 py-3 rounded-lg font-medium transition duration-200">
                            <i class="fas fa-save mr-2"></i>Save Changes
                        </button>
                    </div>
                </form>
            </div>

            <!-- Security Tab -->
            <div id="security-tab" class="settings-content p-6 hidden">
                <h2 class="text-xl font-semibold text-gray-800 mb-6">Security Settings</h2>
                
                <form method="POST" class="space-y-6">
                    <input type="hidden" name="action" value="change_password">
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Current Password</label>
                        <input type="password" name="current_password" 
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500" required>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">New Password</label>
                        <input type="password" name="new_password" 
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500" required>
                        <p class="text-sm text-gray-500 mt-1">Password must be at least 6 characters long</p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Confirm New Password</label>
                        <input type="password" name="confirm_password" 
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500" required>
                    </div>
                    
                    <div class="flex justify-end">
                        <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-8 py-3 rounded-lg font-medium transition duration-200">
                            <i class="fas fa-key mr-2"></i>Change Password
                        </button>
                    </div>
                </form>
            </div>

            <!-- Preferences Tab -->
            <div id="preferences-tab" class="settings-content p-6 hidden">
                <h2 class="text-xl font-semibold text-gray-800 mb-6">Preferences</h2>
                
                <form method="POST" class="space-y-8">
                    <input type="hidden" name="action" value="update_preferences">
                    
                    <!-- Theme Selection -->
                    <div>
                        <label class="block text-lg font-medium text-gray-800 mb-4">Theme Appearance</label>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <label class="theme-option cursor-pointer">
                                <input type="radio" name="theme" value="light" <?php echo $preferences['theme'] === 'light' ? 'checked' : ''; ?> class="sr-only">
                                <div class="theme-card border-2 border-gray-200 rounded-xl p-6 hover:border-green-500 transition duration-200 text-center">
                                    <div class="w-16 h-16 bg-gradient-to-br from-white to-gray-100 border-2 border-gray-300 rounded-full mx-auto mb-4 flex items-center justify-center">
                                        <i class="fas fa-sun text-yellow-500 text-xl"></i>
                                    </div>
                                    <h3 class="font-semibold text-gray-800 mb-2">Light Mode</h3>
                                    <p class="text-sm text-gray-600">Clean and bright interface</p>
                                </div>
                            </label>
                            
                            <label class="theme-option cursor-pointer">
                                <input type="radio" name="theme" value="dark" <?php echo $preferences['theme'] === 'dark' ? 'checked' : ''; ?> class="sr-only">
                                <div class="theme-card border-2 border-gray-200 rounded-xl p-6 hover:border-green-500 transition duration-200 text-center">
                                    <div class="w-16 h-16 bg-gradient-to-br from-gray-700 to-gray-900 border-2 border-gray-600 rounded-full mx-auto mb-4 flex items-center justify-center">
                                        <i class="fas fa-moon text-blue-400 text-xl"></i>
                                    </div>
                                    <h3 class="font-semibold text-gray-800 mb-2">Dark Mode</h3>
                                    <p class="text-sm text-gray-600">Easy on the eyes</p>
                                </div>
                            </label>
                            
                            <label class="theme-option cursor-pointer">
                                <input type="radio" name="theme" value="auto" <?php echo $preferences['theme'] === 'auto' ? 'checked' : ''; ?> class="sr-only">
                                <div class="theme-card border-2 border-gray-200 rounded-xl p-6 hover:border-green-500 transition duration-200 text-center">
                                    <div class="w-16 h-16 bg-gradient-to-r from-yellow-200 via-gray-400 to-gray-800 border-2 border-gray-300 rounded-full mx-auto mb-4 flex items-center justify-center">
                                        <i class="fas fa-adjust text-gray-700 text-xl"></i>
                                    </div>
                                    <h3 class="font-semibold text-gray-800 mb-2">Auto Mode</h3>
                                    <p class="text-sm text-gray-600">Follows system setting</p>
                                </div>
                            </label>
                        </div>
                    </div>
                    
                    <!-- Language and Timezone -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-3">Language</label>
                            <select name="language" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">
                                <option value="en" <?php echo $preferences['language'] === 'en' ? 'selected' : ''; ?>>🇺🇸 English</option>
                                <option value="ur" <?php echo $preferences['language'] === 'ur' ? 'selected' : ''; ?>>🇵🇰 اردو (Urdu)</option>
                                <option value="hi" <?php echo $preferences['language'] === 'hi' ? 'selected' : ''; ?>>🇮🇳 हिंदी (Hindi)</option>
                            </select>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-3">Timezone</label>
                            <select name="timezone" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">
                                <option value="Asia/Kolkata" <?php echo $preferences['timezone'] === 'Asia/Kolkata' ? 'selected' : ''; ?>>Asia/Kolkata (IST)</option>
                                <option value="Asia/Karachi" <?php echo $preferences['timezone'] === 'Asia/Karachi' ? 'selected' : ''; ?>>Asia/Karachi (PKT)</option>
                                <option value="UTC" <?php echo $preferences['timezone'] === 'UTC' ? 'selected' : ''; ?>>UTC</option>
                            </select>
                        </div>
                    </div>
                    
                    <!-- Notifications -->
                    <div>
                        <label class="block text-lg font-medium text-gray-800 mb-4">Notifications</label>
                        <div class="space-y-4">
                            <div class="flex items-center">
                                <input type="checkbox" name="notifications" <?php echo $preferences['notifications'] ? 'checked' : ''; ?> 
                                       class="rounded border-gray-300 text-green-600 focus:ring-green-500">
                                <label class="ml-3 text-sm text-gray-700">Enable push notifications</label>
                            </div>
                            
                            <div class="flex items-center">
                                <input type="checkbox" name="email_notifications" <?php echo $preferences['email_notifications'] ? 'checked' : ''; ?> 
                                       class="rounded border-gray-300 text-green-600 focus:ring-green-500">
                                <label class="ml-3 text-sm text-gray-700">Enable email notifications</label>
                            </div>
                        </div>
                    </div>
                    
                    <div class="flex justify-end">
                        <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-8 py-3 rounded-lg font-medium transition duration-200" onclick="this.form.addEventListener('submit', function() { setTimeout(() => window.location.reload(), 1000); })">
                            <i class="fas fa-save mr-2"></i>Save Preferences
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Tab switching functionality
        document.addEventListener('DOMContentLoaded', function() {
            const tabs = document.querySelectorAll('.settings-tab');
            const contents = document.querySelectorAll('.settings-content');
            
            tabs.forEach(tab => {
                tab.addEventListener('click', function() {
                    const targetTab = this.dataset.tab;
                    
                    // Update tab buttons
                    tabs.forEach(t => {
                        t.classList.remove('border-green-500', 'text-green-600');
                        t.classList.add('border-transparent', 'text-gray-500');
                    });
                    
                    this.classList.remove('border-transparent', 'text-gray-500');
                    this.classList.add('border-green-500', 'text-green-600');
                    
                    // Update content
                    contents.forEach(content => {
                        content.classList.add('hidden');
                    });
                    
                    document.getElementById(targetTab + '-tab').classList.remove('hidden');
                });
            });
            
            // Theme switching
            const themeInputs = document.querySelectorAll('input[name="theme"]');
            themeInputs.forEach(input => {
                input.addEventListener('change', function() {
                    if (this.checked) {
                        applyTheme(this.value);
                        updateThemeCards(this.value);
                    }
                });
            });
            
            // Initialize theme
            const currentTheme = document.querySelector('input[name="theme"]:checked')?.value || 'light';
            applyTheme(currentTheme);
            updateThemeCards(currentTheme);
        });
        
        function updateThemeCards(selectedTheme) {
            document.querySelectorAll('.theme-card').forEach(card => {
                card.classList.remove('border-green-500', 'bg-green-50');
                card.classList.add('border-gray-200');
            });
            
            const selectedCard = document.querySelector(`input[value="${selectedTheme}"]`).closest('.theme-option').querySelector('.theme-card');
            selectedCard.classList.remove('border-gray-200');
            selectedCard.classList.add('border-green-500', 'bg-green-50');
        }
        
        function applyTheme(theme) {
            const html = document.documentElement;
            
            if (theme === 'auto') {
                const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
                theme = prefersDark ? 'dark' : 'light';
            }
            
            html.setAttribute('data-theme', theme);
            localStorage.setItem('theme', theme);
        }
    </script>
</body>
</html>