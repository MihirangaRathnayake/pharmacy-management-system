<?php
require_once dirname(__DIR__) . '/bootstrap.php';

$message = '';
$messageType = '';

if ($_POST) {
    $email = trim($_POST['email']);
    
    if (empty($email)) {
        $message = 'Please enter your email address';
        $messageType = 'error';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = 'Please enter a valid email address';
        $messageType = 'error';
    } else {
        try {
            // Check if email exists
            $stmt = $pdo->prepare("SELECT id, name FROM users WHERE email = ? AND status = 'active'");
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($user) {
                // In a real application, you would:
                // 1. Generate a secure reset token
                // 2. Store it in the database with expiration
                // 3. Send an email with the reset link
                
                // For demo purposes, we'll just show a success message
                $message = 'If an account with that email exists, we\'ve sent password reset instructions to your email address.';
                $messageType = 'success';
                
                // Demo: Show reset instructions
                $resetToken = bin2hex(random_bytes(32));
                $message .= '<br><br><strong>Demo Mode:</strong> In a real application, you would receive an email. For now, you can use the password reset tool: <a href="../reset_admin.php" class="text-blue-600 underline">Reset Password Tool</a>';
            } else {
                // Don't reveal if email exists or not for security
                $message = 'If an account with that email exists, we\'ve sent password reset instructions to your email address.';
                $messageType = 'success';
            }
        } catch (Exception $e) {
            $message = 'An error occurred. Please try again later.';
            $messageType = 'error';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - Pharmacy Management System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-gradient-to-br from-green-50 to-blue-50 min-h-screen flex items-center justify-center">
    <div class="max-w-md w-full mx-4">
        <!-- Logo -->
        <div class="text-center mb-8">
            <div class="inline-flex items-center space-x-2 mb-4">
                <i class="fas fa-pills text-green-600 text-4xl"></i>
                <span class="text-3xl font-bold text-gray-800">PharmaCare</span>
            </div>
            <p class="text-gray-600">Reset Your Password</p>
        </div>

        <!-- Forgot Password Form -->
        <div class="bg-white rounded-lg shadow-lg p-8">
            <div class="text-center mb-6">
                <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-green-100 mb-4">
                    <i class="fas fa-key text-green-600 text-xl"></i>
                </div>
                <h2 class="text-2xl font-bold text-gray-800">Forgot Password?</h2>
                <p class="text-gray-600 mt-2">Enter your email address and we'll send you instructions to reset your password.</p>
            </div>
            
            <?php if ($message): ?>
                <div class="<?php echo $messageType === 'success' ? 'bg-green-100 border border-green-400 text-green-700' : 'bg-red-100 border border-red-400 text-red-700'; ?> px-4 py-3 rounded mb-4">
                    <i class="fas <?php echo $messageType === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'; ?> mr-2"></i>
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>

            <?php if (!$message || $messageType === 'error'): ?>
            <form method="POST" class="space-y-6">
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-envelope mr-1"></i>Email Address
                    </label>
                    <input type="email" id="email" name="email" required
                           value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
                           placeholder="Enter your email address">
                </div>

                <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white font-medium py-2 px-4 rounded-md transition duration-200 flex items-center justify-center">
                    <i class="fas fa-paper-plane mr-2"></i>
                    Send Reset Instructions
                </button>
            </form>
            <?php endif; ?>

            <div class="mt-6 text-center">
                <p class="text-sm text-gray-600">
                    Remember your password? 
                    <a href="login.php" class="text-green-600 hover:text-green-700 font-medium">Sign in</a>
                </p>
            </div>
        </div>

        <!-- Help Section -->
        <div class="mt-6 bg-white rounded-lg shadow-md p-4">
            <h3 class="text-sm font-medium text-gray-700 mb-2">Need Help?</h3>
            <div class="text-xs text-gray-600 space-y-1">
                <p><strong>Demo Users:</strong> Use the reset tool below to restore default passwords</p>
                <div class="flex justify-center space-x-4 mt-3">
                    <a href="../reset_admin.php" class="text-blue-600 hover:text-blue-700">
                        <i class="fas fa-tools mr-1"></i>Reset Tool
                    </a>
                    <a href="../debug_login.php" class="text-purple-600 hover:text-purple-700">
                        <i class="fas fa-bug mr-1"></i>Debug Login
                    </a>
                    <a href="../test_connection.php" class="text-orange-600 hover:text-orange-700">
                        <i class="fas fa-check-circle mr-1"></i>Test System
                    </a>
                </div>
            </div>
        </div>

        <!-- Quick Access -->
        <div class="mt-4 text-center">
            <div class="flex justify-center space-x-4">
                <a href="../index.php" class="text-gray-600 hover:text-gray-800 text-sm">
                    <i class="fas fa-home mr-1"></i>Home
                </a>
                <a href="../customer/index.html" class="text-gray-600 hover:text-gray-800 text-sm">
                    <i class="fas fa-shopping-cart mr-1"></i>Shop
                </a>
                <a href="register.php" class="text-gray-600 hover:text-gray-800 text-sm">
                    <i class="fas fa-user-plus mr-1"></i>Register
                </a>
            </div>
        </div>
    </div>
</body>
</html>