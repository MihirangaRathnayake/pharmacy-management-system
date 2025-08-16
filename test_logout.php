<?php
session_start();
require_once 'bootstrap.php';

// Auto-login for testing if not logged in
if (!isLoggedIn()) {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE role = 'admin' LIMIT 1");
    $stmt->execute();
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($admin) {
        $_SESSION['user_id'] = $admin['id'];
        $_SESSION['user_role'] = $admin['role'];
        $_SESSION['user_name'] = $admin['name'];
        echo "<p style='color: green;'>✅ Auto-logged in for testing</p>";
    }
}

$user = getCurrentUser();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Logout Functionality</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-50 p-8">
    <div class="max-w-2xl mx-auto">
        <h1 class="text-3xl font-bold mb-6">Logout Functionality Test</h1>
        
        <div class="bg-white p-6 rounded-lg shadow mb-6">
            <h2 class="text-xl font-semibold mb-4">Current Session Status</h2>
            
            <?php if (isLoggedIn()): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                    <p><strong>✅ Logged In</strong></p>
                    <p><strong>User:</strong> <?php echo htmlspecialchars($user['name']); ?></p>
                    <p><strong>Role:</strong> <?php echo htmlspecialchars($user['role']); ?></p>
                    <p><strong>User ID:</strong> <?php echo htmlspecialchars($user['id']); ?></p>
                </div>
                
                <div class="space-y-4">
                    <h3 class="text-lg font-medium">Test Logout Methods:</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="border p-4 rounded">
                            <h4 class="font-medium mb-2">Method 1: Direct Link</h4>
                            <a href="auth/logout.php" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded inline-flex items-center">
                                <i class="fas fa-sign-out-alt mr-2"></i>
                                Direct Logout
                            </a>
                        </div>
                        
                        <div class="border p-4 rounded">
                            <h4 class="font-medium mb-2">Method 2: With Confirmation</h4>
                            <button onclick="confirmLogout()" class="bg-orange-600 hover:bg-orange-700 text-white px-4 py-2 rounded inline-flex items-center">
                                <i class="fas fa-question-circle mr-2"></i>
                                Confirm Logout
                            </button>
                        </div>
                        
                        <div class="border p-4 rounded">
                            <h4 class="font-medium mb-2">Method 3: Admin Dashboard Style</h4>
                            <button onclick="showLogoutModal()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded inline-flex items-center">
                                <i class="fas fa-user-shield mr-2"></i>
                                Dashboard Logout
                            </button>
                        </div>
                        
                        <div class="border p-4 rounded">
                            <h4 class="font-medium mb-2">Method 4: AJAX Logout</h4>
                            <button onclick="ajaxLogout()" class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded inline-flex items-center">
                                <i class="fas fa-wifi mr-2"></i>
                                AJAX Logout
                            </button>
                        </div>
                    </div>
                </div>
                
            <?php else: ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    <p><strong>❌ Not Logged In</strong></p>
                    <p>You are not currently logged in.</p>
                </div>
                
                <a href="auth/login.php" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded inline-flex items-center">
                    <i class="fas fa-sign-in-alt mr-2"></i>
                    Go to Login
                </a>
            <?php endif; ?>
        </div>
        
        <div class="bg-white p-6 rounded-lg shadow">
            <h2 class="text-xl font-semibold mb-4">Session Information</h2>
            <div class="bg-gray-100 p-4 rounded">
                <pre><?php print_r($_SESSION); ?></pre>
            </div>
        </div>
    </div>

    <!-- Logout Modal -->
    <div id="logoutModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden items-center justify-center z-50">
        <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4">
            <div class="flex items-center mb-4">
                <div class="flex-shrink-0">
                    <i class="fas fa-sign-out-alt text-red-600 text-2xl"></i>
                </div>
                <div class="ml-3">
                    <h3 class="text-lg font-medium text-gray-900">Confirm Logout</h3>
                </div>
            </div>
            <div class="mb-4">
                <p class="text-sm text-gray-500">
                    Are you sure you want to logout? You will need to login again to access the system.
                </p>
            </div>
            <div class="flex justify-end space-x-3">
                <button onclick="closeLogoutModal()" 
                        class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-md">
                    Cancel
                </button>
                <button onclick="performLogout()" 
                        class="px-4 py-2 text-sm font-medium text-white bg-red-600 hover:bg-red-700 rounded-md">
                    <i class="fas fa-sign-out-alt mr-1"></i>
                    Logout
                </button>
            </div>
        </div>
    </div>

    <script>
        function confirmLogout() {
            if (confirm('Are you sure you want to logout?')) {
                window.location.href = 'auth/logout.php';
            }
        }
        
        function showLogoutModal() {
            document.getElementById('logoutModal').classList.remove('hidden');
            document.getElementById('logoutModal').classList.add('flex');
        }
        
        function closeLogoutModal() {
            document.getElementById('logoutModal').classList.add('hidden');
            document.getElementById('logoutModal').classList.remove('flex');
        }
        
        function performLogout() {
            const logoutBtn = document.querySelector('#logoutModal button[onclick="performLogout()"]');
            const originalText = logoutBtn.innerHTML;
            logoutBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i>Logging out...';
            logoutBtn.disabled = true;
            
            setTimeout(() => {
                window.location.href = 'auth/logout.php';
            }, 500);
        }
        
        function ajaxLogout() {
            if (confirm('Logout using AJAX?')) {
                fetch('auth/logout.php')
                .then(() => {
                    alert('Logged out successfully!');
                    window.location.reload();
                })
                .catch(error => {
                    alert('Logout failed: ' + error.message);
                });
            }
        }
        
        // Close modal when clicking outside
        document.getElementById('logoutModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeLogoutModal();
            }
        });
    </script>
</body>
</html>