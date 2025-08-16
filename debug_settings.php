<?php
// Debug version of settings page without authentication
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';

// Mock user data for testing
$user = [
    'id' => 1,
    'name' => 'Test Admin',
    'email' => 'admin@test.com',
    'role' => 'admin',
    'phone' => '+1234567890'
];

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

// Simple profile image function for testing
function getProfileImageUrl($userId) {
    return 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMTAwIiBoZWlnaHQ9IjEwMCIgdmlld0JveD0iMCAwIDEwMCAxMDAiIGZpbGw9Im5vbmUiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+CjxjaXJjbGUgY3g9IjUwIiBjeT0iNTAiIHI9IjUwIiBmaWxsPSIjRTVFN0VCIi8+CjxwYXRoIGQ9Ik01MCAyNUM0My4zNzUgMjUgMzggMzAuMzc1IDM4IDM3QzM4IDQzLjYyNSA0My4zNzUgNDkgNTAgNDlDNTYuNjI1IDQ5IDYyIDQzLjYyNSA2MiAzN0M2MiAzMC4zNzUgNTYuNjI1IDI1IDUwIDI1WiIgZmlsbD0iIzlDQTNBRiIvPgo8cGF0aCBkPSJNNTAgNTVDNDAuNjI1IDU1IDMzIDYyLjYyNSAzMyA3MlY3NUg2N1Y3MkM2NyA2Mi42MjUgNTkuMzc1IDU1IDUwIDU1WiIgZmlsbD0iIzlDQTNBRiIvPgo8L3N2Zz4K';
}
?>
<!DOCTYPE html>
<html lang="en" data-theme="<?php echo $preferences['theme']; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings Debug - <?php echo APP_NAME; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        
        /* Dark mode styles */
        [data-theme="dark"] {
            --bg-primary: #1a1a1a;
            --bg-secondary: #2d2d2d;
            --bg-tertiary: #404040;
            --text-primary: #ffffff;
            --text-secondary: #d1d5db;
            --border-color: #404040;
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
        
        [data-theme="dark"] .bg-gray-100 {
            background-color: var(--bg-tertiary) !important;
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
        
        .profile-image-container {
            position: relative;
            display: inline-block;
        }
        
        .profile-image-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: opacity 0.3s;
            border-radius: 50%;
            cursor: pointer;
        }
        
        .profile-image-container:hover .profile-image-overlay {
            opacity: 1;
        }
    </style>
</head>
<body class="bg-gray-50 transition-colors duration-300">
    <div class="bg-blue-600 text-white p-4 mb-6">
        <div class="container mx-auto">
            <h1 class="text-xl font-bold">🔧 Settings Debug Mode</h1>
            <p>This is a debug version without authentication requirements</p>
        </div>
    </div>
    
    <div class="container mx-auto px-4 py-8">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-800 mb-2">Settings</h1>
            <p class="text-gray-600">Manage your account settings and preferences</p>
        </div>

        <!-- Settings Navigation -->
        <div class="bg-white rounded-lg shadow-md mb-6">
            <div class="border-b border-gray-200">
                <nav class="flex space-x-8 px-6">
                    <button class="settings-tab py-4 px-2 border-b-2 border-green-500 text-green-600 font-medium" data-tab="profile">
                        <i class="fas fa-user mr-2"></i>Profile
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
                
                <div class="space-y-6">
                    <!-- Profile Picture -->
                    <div class="flex items-center space-x-6">
                        <div class="profile-image-container">
                            <img id="profileImage" src="<?php echo getProfileImageUrl($user['id']); ?>" 
                                 alt="Profile Picture" class="w-24 h-24 rounded-full object-cover border-4 border-gray-200">
                            <div class="profile-image-overlay">
                                <i class="fas fa-camera text-white text-xl"></i>
                            </div>
                        </div>
                        <div>
                            <h3 class="text-lg font-medium text-gray-800"><?php echo htmlspecialchars($user['name']); ?></h3>
                            <p class="text-gray-600"><?php echo htmlspecialchars($user['role']); ?></p>
                            <button type="button" class="mt-2 text-sm text-green-600 hover:text-green-700">
                                Change Picture
                            </button>
                        </div>
                    </div>
                    
                    <!-- Form Fields -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Full Name</label>
                            <input type="text" value="<?php echo htmlspecialchars($user['name']); ?>" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Email Address</label>
                            <input type="email" value="<?php echo htmlspecialchars($user['email']); ?>" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Phone Number</label>
                            <input type="tel" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Role</label>
                            <input type="text" value="<?php echo htmlspecialchars($user['role']); ?>" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-100" readonly>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Preferences Tab -->
            <div id="preferences-tab" class="settings-content p-6 hidden">
                <h2 class="text-xl font-semibold text-gray-800 mb-6">Preferences</h2>
                
                <div class="space-y-6">
                    <!-- Theme Selection -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-3">Theme</label>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <label class="theme-option cursor-pointer">
                                <input type="radio" name="theme" value="light" <?php echo $preferences['theme'] === 'light' ? 'checked' : ''; ?> class="sr-only">
                                <div class="theme-card border-2 border-gray-200 rounded-lg p-4 hover:border-green-500 transition duration-200">
                                    <div class="flex items-center space-x-3">
                                        <div class="w-6 h-6 bg-white border border-gray-300 rounded"></div>
                                        <div>
                                            <p class="font-medium text-gray-800">Light</p>
                                            <p class="text-sm text-gray-600">Default light theme</p>
                                        </div>
                                    </div>
                                </div>
                            </label>
                            
                            <label class="theme-option cursor-pointer">
                                <input type="radio" name="theme" value="dark" <?php echo $preferences['theme'] === 'dark' ? 'checked' : ''; ?> class="sr-only">
                                <div class="theme-card border-2 border-gray-200 rounded-lg p-4 hover:border-green-500 transition duration-200">
                                    <div class="flex items-center space-x-3">
                                        <div class="w-6 h-6 bg-gray-800 border border-gray-600 rounded"></div>
                                        <div>
                                            <p class="font-medium text-gray-800">Dark</p>
                                            <p class="text-sm text-gray-600">Dark theme for low light</p>
                                        </div>
                                    </div>
                                </div>
                            </label>
                            
                            <label class="theme-option cursor-pointer">
                                <input type="radio" name="theme" value="auto" <?php echo $preferences['theme'] === 'auto' ? 'checked' : ''; ?> class="sr-only">
                                <div class="theme-card border-2 border-gray-200 rounded-lg p-4 hover:border-green-500 transition duration-200">
                                    <div class="flex items-center space-x-3">
                                        <div class="w-6 h-6 bg-gradient-to-r from-white to-gray-800 border border-gray-300 rounded"></div>
                                        <div>
                                            <p class="font-medium text-gray-800">Auto</p>
                                            <p class="text-sm text-gray-600">Follow system preference</p>
                                        </div>
                                    </div>
                                </div>
                            </label>
                        </div>
                    </div>
                    
                    <!-- Language -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Language</label>
                        <select class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500">
                            <option value="en" <?php echo $preferences['language'] === 'en' ? 'selected' : ''; ?>>English</option>
                            <option value="ur" <?php echo $preferences['language'] === 'ur' ? 'selected' : ''; ?>>Urdu</option>
                            <option value="hi" <?php echo $preferences['language'] === 'hi' ? 'selected' : ''; ?>>Hindi</option>
                        </select>
                    </div>
                    
                    <!-- Timezone -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Timezone</label>
                        <select class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500">
                            <option value="Asia/Kolkata" <?php echo $preferences['timezone'] === 'Asia/Kolkata' ? 'selected' : ''; ?>>Asia/Kolkata (IST)</option>
                            <option value="Asia/Karachi" <?php echo $preferences['timezone'] === 'Asia/Karachi' ? 'selected' : ''; ?>>Asia/Karachi (PKT)</option>
                            <option value="UTC" <?php echo $preferences['timezone'] === 'UTC' ? 'selected' : ''; ?>>UTC</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Simple tab switching
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
            
            // Update theme cards on load
            const currentTheme = document.querySelector('input[name="theme"]:checked')?.value || 'light';
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