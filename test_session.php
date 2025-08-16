<?php
session_start();
require_once 'bootstrap.php';

echo "<h2>Session Test</h2>";

echo "<h3>Session Data:</h3>";
echo "<pre>" . print_r($_SESSION, true) . "</pre>";

echo "<h3>Authentication Check:</h3>";
if (function_exists('isLoggedIn')) {
    $loggedIn = isLoggedIn();
    echo "<p>isLoggedIn(): " . ($loggedIn ? 'true' : 'false') . "</p>";
    
    if ($loggedIn) {
        $user = getCurrentUser();
        echo "<p>Current User: " . print_r($user, true) . "</p>";
    }
} else {
    echo "<p>❌ isLoggedIn function not found</p>";
}

echo "<h3>Database Connection:</h3>";
try {
    $count = $pdo->query("SELECT COUNT(*) FROM medicines")->fetchColumn();
    echo "<p>✅ Database connected. Found {$count} medicines.</p>";
} catch (Exception $e) {
    echo "<p>❌ Database error: " . $e->getMessage() . "</p>";
}

echo "<h3>Quick Login (for testing):</h3>";
if (!isLoggedIn()) {
    echo '<form method="post">';
    echo '<input type="hidden" name="quick_login" value="1">';
    echo '<button type="submit" style="background: #4CAF50; color: white; padding: 10px 20px; border: none; border-radius: 4px;">Quick Login as Admin</button>';
    echo '</form>';
}

// Handle quick login
if (isset($_POST['quick_login'])) {
    // Get admin user
    $stmt = $pdo->prepare("SELECT * FROM users WHERE role = 'admin' LIMIT 1");
    $stmt->execute();
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($admin) {
        $_SESSION['user_id'] = $admin['id'];
        $_SESSION['user_role'] = $admin['role'];
        $_SESSION['user_name'] = $admin['name'];
        echo "<p>✅ Logged in as: " . $admin['name'] . "</p>";
        echo "<script>window.location.reload();</script>";
    } else {
        echo "<p>❌ No admin user found</p>";
    }
}
?>

<style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    pre { background: #f5f5f5; padding: 10px; border-radius: 4px; overflow-x: auto; }
</style>