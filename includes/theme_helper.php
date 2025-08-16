<?php
/**
 * Theme Helper Functions
 * Manages user theme preferences across the application
 */

function getUserTheme($userId = null) {
    global $pdo;
    
    // If no user ID provided, try to get current user
    if (!$userId && isLoggedIn()) {
        $user = getCurrentUser();
        $userId = $user['id'] ?? null;
    }
    
    if (!$userId || !$pdo) {
        return 'light'; // Default theme
    }
    
    try {
        $stmt = $pdo->prepare("SELECT theme FROM user_preferences WHERE user_id = ?");
        $stmt->execute([$userId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result && $result['theme']) {
            return $result['theme'];
        }
    } catch (Exception $e) {
        // Return default on error
    }
    
    return 'light'; // Default theme
}

function getThemeClass($userId = null) {
    $theme = getUserTheme($userId);
    return $theme === 'dark' ? 'dark' : 'light';
}

function renderThemeScript() {
    $theme = getUserTheme();
    ?>
    <script>
        // Apply theme immediately to prevent flash
        (function() {
            const theme = '<?php echo $theme; ?>';
            const html = document.documentElement;
            
            if (theme === 'auto') {
                const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
                html.setAttribute('data-theme', prefersDark ? 'dark' : 'light');
            } else {
                html.setAttribute('data-theme', theme);
            }
            
            // Store in localStorage for client-side access
            localStorage.setItem('userTheme', theme);
        })();
        
        // Listen for system theme changes if user has auto mode
        if (window.matchMedia && '<?php echo $theme; ?>' === 'auto') {
            window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', function(e) {
                document.documentElement.setAttribute('data-theme', e.matches ? 'dark' : 'light');
            });
        }
    </script>
    <?php
}

function getThemeCSS() {
    // Use absolute path from web root
    return '<link rel="stylesheet" href="/pharmacy-management-system/assets/css/theme.css">';
}

// Function to include theme assets in pages
function includeThemeAssets() {
    echo getThemeCSS();
    renderThemeScript();
}
?>