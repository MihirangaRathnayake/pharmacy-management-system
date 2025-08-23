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
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $terms = isset($_POST['terms']);

    // Validation
    if (empty($name) || empty($email) || empty($password) || empty($confirm_password)) {
        $error = 'Please fill in all required fields';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters long';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match';
    } elseif (!$terms) {
        $error = 'Please accept the terms and conditions';
    } else {
        try {
            // Check if email already exists
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            
            if ($stmt->fetch()) {
                $error = 'An account with this email already exists';
            } else {
                // Create user account
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                
                $stmt = $pdo->prepare("
                    INSERT INTO users (name, email, phone, password, role, status) 
                    VALUES (?, ?, ?, ?, 'customer', 'active')
                ");
                
                if ($stmt->execute([$name, $email, $phone, $hashed_password])) {
                    $user_id = $pdo->lastInsertId();
                    
                    // Create customer record
                    $customer_code = 'CUST' . str_pad($user_id, 6, '0', STR_PAD_LEFT);
                    $stmt = $pdo->prepare("
                        INSERT INTO customers (user_id, customer_code, name, email, phone) 
                        VALUES (?, ?, ?, ?, ?)
                    ");
                    $stmt->execute([$user_id, $customer_code, $name, $email, $phone]);
                    
                    // Auto login
                    $_SESSION['user_id'] = $user_id;
                    $_SESSION['user_name'] = $name;
                    $_SESSION['user_email'] = $email;
                    $_SESSION['user_role'] = 'customer';
                    
                    $success = 'Account created successfully! Redirecting...';
                    
                    // Redirect after 2 seconds
                    header('refresh:2;url=index.php');
                } else {
                    $error = 'Failed to create account. Please try again.';
                }
            }
        } catch (Exception $e) {
            $error = 'Registration failed. Please try again.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - PharmaCare</title>
    
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

            <!-- Register Form -->
            <div class="auth-form-container glass-card">
                <div class="auth-form-header">
                    <h1 class="gradient-text">Create Account</h1>
                    <p>Join PharmaCare for convenient medicine delivery</p>
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
                    <div class="form-row">
                        <div class="form-group">
                            <div class="input-wrapper">
                                <i class="fas fa-user input-icon"></i>
                                <input type="text" name="name" class="form-input" 
                                       placeholder="Full Name" 
                                       value="<?= htmlspecialchars($name ?? '') ?>" 
                                       required>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="input-wrapper">
                                <i class="fas fa-phone input-icon"></i>
                                <input type="tel" name="phone" class="form-input" 
                                       placeholder="Phone Number" 
                                       value="<?= htmlspecialchars($phone ?? '') ?>">
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="input-wrapper">
                            <i class="fas fa-envelope input-icon"></i>
                            <input type="email" name="email" class="form-input" 
                                   placeholder="Email Address" 
                                   value="<?= htmlspecialchars($email ?? '') ?>" 
                                   required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <div class="input-wrapper">
                                <i class="fas fa-lock input-icon"></i>
                                <input type="password" name="password" class="form-input" 
                                       placeholder="Password" required>
                                <button type="button" class="password-toggle" onclick="togglePassword(this)">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            <div class="password-strength" id="passwordStrength"></div>
                        </div>

                        <div class="form-group">
                            <div class="input-wrapper">
                                <i class="fas fa-lock input-icon"></i>
                                <input type="password" name="confirm_password" class="form-input" 
                                       placeholder="Confirm Password" required>
                                <button type="button" class="password-toggle" onclick="togglePassword(this)">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="checkbox-wrapper">
                            <input type="checkbox" name="terms" required>
                            <span class="checkmark"></span>
                            I agree to the <a href="terms.php" target="_blank">Terms of Service</a> 
                            and <a href="privacy.php" target="_blank">Privacy Policy</a>
                        </label>
                    </div>

                    <div class="form-group">
                        <label class="checkbox-wrapper">
                            <input type="checkbox" name="newsletter">
                            <span class="checkmark"></span>
                            Subscribe to our newsletter for health tips and offers
                        </label>
                    </div>

                    <button type="submit" class="btn btn-primary btn-full">
                        <i class="fas fa-user-plus"></i>
                        Create Account
                    </button>
                </form>

                <div class="auth-divider">
                    <span>or</span>
                </div>

                <div class="social-login">
                    <button class="btn btn-outline social-btn">
                        <i class="fab fa-google"></i>
                        Sign up with Google
                    </button>
                    <button class="btn btn-outline social-btn">
                        <i class="fab fa-facebook-f"></i>
                        Sign up with Facebook
                    </button>
                </div>

                <div class="auth-footer">
                    <p>Already have an account? 
                        <a href="login.php" class="auth-link">Sign in here</a>
                    </p>
                </div>
            </div>

            <!-- Benefits -->
            <div class="auth-benefits">
                <h3>Why Choose PharmaCare?</h3>
                <div class="benefits-list">
                    <div class="benefit-item">
                        <i class="fas fa-shipping-fast"></i>
                        <span>Fast & Free Delivery</span>
                    </div>
                    <div class="benefit-item">
                        <i class="fas fa-certificate"></i>
                        <span>100% Authentic Medicines</span>
                    </div>
                    <div class="benefit-item">
                        <i class="fas fa-user-md"></i>
                        <span>Expert Consultation</span>
                    </div>
                    <div class="benefit-item">
                        <i class="fas fa-lock"></i>
                        <span>Secure & Private</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="assets/js/main.js"></script>
    <script src="assets/js/auth.js"></script>
</body>
</html>