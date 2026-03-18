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
        // Resolve theme with this priority:
        // 1) Browser-stored value from user toggle
        // 2) DB preference
        // 3) Light default
        (function() {
            const dbTheme = '<?php echo $theme; ?>';
            const html = document.documentElement;
            const storedTheme = localStorage.getItem('theme') || localStorage.getItem('pcTheme') || localStorage.getItem('userTheme');
            const requestedTheme = storedTheme || dbTheme || 'light';
            const resolvedTheme = requestedTheme === 'auto'
                ? (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light')
                : requestedTheme;
            
            html.setAttribute('data-theme', resolvedTheme);
            html.classList.toggle('dark', resolvedTheme === 'dark');
            
            // Keep all keys synced to the chosen preference, without overwriting
            // a user-selected local theme with DB defaults on every page load.
            localStorage.setItem('theme', requestedTheme);
            localStorage.setItem('pcTheme', requestedTheme);
            localStorage.setItem('userTheme', requestedTheme);
        })();
        
        // Listen for system theme changes only when preference is auto.
        if (window.matchMedia) {
            const media = window.matchMedia('(prefers-color-scheme: dark)');
            const handler = function(e) {
                const currentPreference = localStorage.getItem('theme') || localStorage.getItem('pcTheme') || localStorage.getItem('userTheme') || '<?php echo $theme; ?>';
                if (currentPreference !== 'auto') return;
                document.documentElement.setAttribute('data-theme', e.matches ? 'dark' : 'light');
                document.documentElement.classList.toggle('dark', e.matches);
            };
            if (typeof media.addEventListener === 'function') {
                media.addEventListener('change', handler);
            } else if (typeof media.addListener === 'function') {
                media.addListener(handler);
            }
        }
    </script>
    <?php
}

function getThemeCSS() {
    // Use admin design system path (contains all data-theme styles).
    return '<link rel="stylesheet" href="/pharmacy-management-system/admin/assets/css/design-system.css">';
}

// Function to include theme assets in pages
function includeThemeAssets() {
    echo getThemeCSS();
    renderThemeScript();
}
?>
