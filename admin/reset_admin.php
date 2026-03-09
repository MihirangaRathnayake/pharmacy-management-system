<?php
require_once __DIR__ . '/bootstrap.php';

$message = '';
$error = '';

if (isset($_POST['reset']) && $_POST['reset'] === 'confirm') {
    if ($pdo) {
        try {
            // Delete all admin users
            $stmt = $pdo->prepare("DELETE FROM users WHERE role = 'admin'");
            $stmt->execute();
            
            $deletedCount = $stmt->rowCount();
            $message = "✅ Successfully deleted {$deletedCount} admin user(s). You can now create a new admin account.";
            
            // Clear any existing session
            session_destroy();
            
        } catch (Exception $e) {
            $error = "❌ Error deleting admin users: " . $e->getMessage();
        }
    } else {
        $error = "❌ Database connection failed";
    }
}

// Check current admin users
$adminUsers = [];
if ($pdo) {
    try {
        $stmt = $pdo->prepare("SELECT id, name, email, created_at FROM users WHERE role = 'admin'");
        $stmt->execute();
        $adminUsers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        $error = "Error fetching admin users: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Reset Admin Credentials - Pharmacy Management System</title>
    <?php include 'includes/head.php'; ?>
</head>
<body class="bg-gradient-to-br from-red-50 to-orange-50 min-h-screen">
    <div class="min-h-screen flex items-center justify-center py-12 px-4">
        <div class="max-w-md w-full">
            <!-- Header -->
            <div class="text-center mb-8">
                <div class="inline-flex items-center space-x-2 mb-4">
                    <i class="fas fa-pills text-red-600 text-4xl"></i>
                    <span class="text-3xl font-bold text-gray-800">PharmaCare</span>
                </div>
                <p class="text-gray-600">Reset Admin Credentials</p>
            </div>

            <!-- Reset Form -->
            <div class="bg-white rounded-lg shadow-lg p-8">
                <h2 class="text-2xl font-bold text-gray-800 mb-6 text-center">
                    <i class="fas fa-user-times mr-2 text-red-600"></i>Reset Admin Account
                </h2>
                
                <?php if ($error): ?>
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                        <i class="fas fa-exclamation-circle mr-2"></i>
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>
                
                <?php if ($message): ?>
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                        <i class="fas fa-check-circle mr-2"></i>
                        <?php echo htmlspecialchars($message); ?>
                        <div class="mt-3 space-y-2">
                            <a href="auth/register.php" class="block bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded text-center transition duration-200">
                                <i class="fas fa-user-plus mr-2"></i>Create New Admin Account
                            </a>
                            <a href="start_here.php" class="block bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded text-center transition duration-200">
                                <i class="fas fa-home mr-2"></i>Back to Home
                            </a>
                        </div>
                    </div>
                <?php else: ?>
                    <!-- Current Admin Users -->
                    <?php if (!empty($adminUsers)): ?>
                        <div class="mb-6">
                            <h3 class="text-lg font-semibold text-gray-800 mb-3">Current Admin Users:</h3>
                            <div class="space-y-2">
                                <?php foreach ($adminUsers as $user): ?>
                                    <div class="bg-gray-50 p-3 rounded-lg">
                                        <p class="font-medium text-gray-800"><?php echo htmlspecialchars($user['name']); ?></p>
                                        <p class="text-sm text-gray-600"><?php echo htmlspecialchars($user['email']); ?></p>
                                        <p class="text-xs text-gray-500">Created: <?php echo date('M j, Y', strtotime($user['created_at'])); ?></p>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Warning -->
                    <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
                        <h3 class="text-red-800 font-semibold mb-2">
                            <i class="fas fa-exclamation-triangle mr-2"></i>Warning!
                        </h3>
                        <div class="text-red-700 text-sm space-y-1">
                            <p>• This will delete ALL admin users from the database</p>
                            <p>• You will need to create a new admin account</p>
                            <p>• This action cannot be undone</p>
                            <p>• All current sessions will be terminated</p>
                        </div>
                    </div>

                    <!-- Reset Form -->
                    <form method="POST" onsubmit="return confirm('Are you sure you want to delete all admin users? This cannot be undone!')">
                        <div class="mb-6">
                            <label class="flex items-center">
                                <input type="checkbox" required class="mr-2">
                                <span class="text-sm text-gray-700">I understand that this will delete all admin users</span>
                            </label>
                        </div>
                        
                        <button type="submit" name="reset" value="confirm" class="w-full bg-red-600 hover:bg-red-700 text-white font-medium py-2 px-4 rounded-md transition duration-200 flex items-center justify-center">
                            <i class="fas fa-trash mr-2"></i>
                            Delete All Admin Users
                        </button>
                    </form>
                <?php endif; ?>
            </div>

            <!-- Quick Links -->
            <div class="mt-6 text-center text-sm text-gray-500 space-x-4">
                <a href="start_here.php" class="hover:text-gray-700">← Back to Home</a>
                <a href="auth/login.php" class="hover:text-gray-700">Try Login</a>
                <a href="router.php" class="hover:text-gray-700">Debug System</a>
            </div>
        </div>
    </div>
</body>
</html>