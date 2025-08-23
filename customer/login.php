<?php
require_once '../config/database.php';
require_once '../includes/auth.php';

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

$error = '';
$success = '';

if ($_POST) {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $remember = isset($_POST['remember']);

    if (empty($email) || empty($password)) {
        $error = 'Please fill in all fields';
    } else {
        try {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND status = 'active'");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                // Set session
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_role'] = $user['role'];

                // Set remember me cookie
                if ($remember) {
                    $token = bin2hex(random_bytes(32));
                    setcookie('remember_token', $token, time() + (30 * 24 * 60 * 60), '/'); // 30 days
                    
                    // Store token in database (you might want to create a remember_tokens table)
                    $stmt = $pdo->prepare("UPDATE users SET remember_token = ? WHERE id = ?");
                    $stmt->execute([$token, $user['id']]);
                }

                // Redirect to intended page or dashboard
                $redirect = $_GET['redirect'] ?? 'index.php';
                header('Location: ' . $redirect);
                exit();
            } else {
                $error = 'Invalid email or password';
            }
        } catch (Exception $e) {
            $error = 'Login failed. Please try again.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - PharmaCare</title>
    
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/amazon-ember-font@latest/amazonember.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/theme.css">
    <link rel="stylesheet" href="assets/css/auth.css">
</head>
<body class="auth-page">
    <div class="auth-container">
        <div class="auth-background">
            <div class="floating-shapes">
                <div class="shape shape-1"></div>
                <div class="shape shape-2"></div>
                <div class="shape shape-3"></div>
                <div class="shape shape-4"></div>
            </div>
        </div>

        <div class="auth-content">
            <!-- Brand Header -->
            <div class="auth-header">
                <a href="index.php" class="auth-brand">
                    <i class="fas fa-plus-circle"></i>
                    <span>PharmaCare</span>
                </a>
                <p class="auth-subtitle">Your trusted online pharmacy</p>
            </div>

            <!-- Login Form -->
            <div class="auth-form-container glass-card">
                <div class="auth-form-header">
                    <h1 class="gradient-text">Welcome Back</h1>
                    <p>Sign in to your account to continue</p>
                </div>

                <?php if ($error): ?>
                    <div class="alert alert-error">
                        <i class="fas fa-exclamation-circle"></i>
                        <?= htmlspecialchars($error) ?>
                    </div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i>
                        <?= htmlspecialchars($success) ?>
                    </div>
                <?php endif; ?>

                <form method="POST" class="auth-form" data-validate>
                    <div class="form-group">
                        <div class="input-wrapper">
                            <i class="fas fa-envelope input-icon"></i>
                            <input type="email" name="email" class="form-input" 
                                   placeholder="Enter your email" 
                                   value="<?= htmlspecialchars($email ?? '') ?>" 
                                   required>
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="input-wrapper">
                            <i class="fas fa-lock input-icon"></i>
                            <input type="password" name="password" class="form-input" 
                                   placeholder="Enter your password" required>
                            <button type="button" class="password-toggle" onclick="togglePassword(this)">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="form-options">
                            <label class="checkbox-wrapper">
                                <input type="checkbox" name="remember">
                                <span class="checkmark"></span>
                                Remember me
                            </label>
                            <a href="forgot-password.php" class="forgot-link">
                                Forgot password?
                            </a>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary btn-full">
                        <i class="fas fa-sign-in-alt"></i>
                        Sign In
                    </button>
                </form>

                <div class="auth-divider">
                    <span>or</span>
                </div>

                <div class="social-login">
                    <button class="btn btn-outline social-btn">
                        <i class="fab fa-google"></i>
                        Continue with Google
                    </button>
                    <button class="btn btn-outline social-btn">
                        <i class="fab fa-facebook-f"></i>
                        Continue with Facebook
                    </button>
                </div>

                <div class="auth-footer">
                    <p>Don't have an account? 
                        <a href="register.php" class="auth-link">Sign up here</a>
                    </p>
                </div>
            </div>

            <!-- Quick Access -->
            <div class="quick-access">
                <h3>Quick Access</h3>
                <div class="quick-links">
                    <a href="index.php" class="quick-link">
                        <i class="fas fa-home"></i>
                        <span>Home</span>
                    </a>
                    <a href="products.php" class="quick-link">
                        <i class="fas fa-pills"></i>
                        <span>Products</span>
                    </a>
                    <a href="contact.php" class="quick-link">
                        <i class="fas fa-phone"></i>
                        <span>Contact</span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script src="assets/js/main.js"></script>
    <script src="assets/js/auth.js"></script>
</body>
</html>