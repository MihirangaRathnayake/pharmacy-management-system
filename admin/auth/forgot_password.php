<?php
require_once dirname(__DIR__) . '/bootstrap.php';
require_once dirname(__DIR__) . '/includes/email_helper.php';

// Redirect if already logged in
if (isLoggedIn()) {
    header('Location: ../index.php');
    exit();
}

$error = '';
$success = '';

if ($_POST) {
    $email = trim($_POST['email']);

    if (empty($email)) {
        $error = 'Please enter your email address';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address';
    } else {
        try {
            // Check if user exists
            $stmt = $pdo->prepare("SELECT id, name, email FROM users WHERE email = ? AND status = 'active'");
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user) {
                // Generate reset token
                $token = bin2hex(random_bytes(32));
                $expiresAt = date('Y-m-d H:i:s', strtotime('+1 hour'));

                // Create password_reset_tokens table if it doesn't exist
                $pdo->exec("
                    CREATE TABLE IF NOT EXISTS password_reset_tokens (
                        id INT PRIMARY KEY AUTO_INCREMENT,
                        user_id INT NOT NULL,
                        email VARCHAR(100) NOT NULL,
                        token VARCHAR(255) NOT NULL,
                        expires_at DATETIME NOT NULL,
                        used BOOLEAN DEFAULT FALSE,
                        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
                        INDEX idx_token (token),
                        INDEX idx_email (email),
                        INDEX idx_expires (expires_at)
                    )
                ");

                // Store token in database
                $stmt = $pdo->prepare("
                    INSERT INTO password_reset_tokens (user_id, email, token, expires_at)
                    VALUES (?, ?, ?, ?)
                ");
                $stmt->execute([$user['id'], $email, $token, $expiresAt]);

                // Generate reset link
                $resetLink = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") .
                             "://" . $_SERVER['HTTP_HOST'] .
                             dirname($_SERVER['REQUEST_URI']) . "/reset_password.php?token=" . $token;

                // Send reset link via email
                $emailSent = sendPasswordResetLink($email, $user['name'], $resetLink);

                if ($emailSent) {
                    $success = "A password reset link has been sent to your email address. Please check your inbox.";
                } else {
                    // Email sending failed - show error with development mode fallback
                    $success = "Email delivery failed. <br><br>
                                <strong>DEVELOPMENT MODE:</strong><br>
                                <small>Click the link below to reset your password:</small><br>
                                <a href='reset_password.php?token={$token}' class='inline-block mt-3 px-6 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 font-medium'>Reset Password →</a><br><br>
                                <small>Note: In production, this link would be sent to your email.</small>";
                }

            } else {
                // Don't reveal if email exists or not (security best practice)
                $success = "If an account with that email exists, a password reset link has been sent.";
            }
        } catch (Exception $e) {
            error_log("Forgot password error: " . $e->getMessage());
            $error = "An error occurred. Please try again later.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <title>Forgot Password - Pharmacy Management System</title>
    <?php include '../includes/head.php'; ?>
    <style>
    /* Minimal Light Background */
    .auth-animated-bg {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: linear-gradient(135deg, #f0fdf4 0%, #e0f2fe 50%, #f5f3ff 100%);
        background-size: 200% 200%;
        animation: gradientShift 20s ease infinite;
        z-index: -2;
    }

    @keyframes gradientShift {

        0%,
        100% {
            background-position: 0% 50%;
        }

        50% {
            background-position: 100% 50%;
        }
    }

    /* Subtle Floating Particles */
    .particle {
        position: absolute;
        background: rgba(16, 185, 129, 0.05);
        border-radius: 50%;
        pointer-events: none;
    }

    /* Clean Minimal Card */
    .glass-card {
        background: rgba(255, 255, 255, 0.98);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(16, 185, 129, 0.08);
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.04);
        transition: all 0.3s ease;
    }

    .glass-card:hover {
        box-shadow: 0 8px 30px rgba(0, 0, 0, 0.08);
    }

    /* Minimal Logo */
    .logo-animated {
        animation: logoPulse 3s ease-in-out infinite;
    }

    @keyframes logoPulse {

        0%,
        100% {
            transform: scale(1);
        }

        50% {
            transform: scale(1.02);
        }
    }

    /* Subtle Input Animation */
    .animated-input {
        transition: all 0.2s ease;
        border: 1.5px solid #e5e7eb;
    }

    .animated-input:focus {
        border-color: #10b981;
        box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.1);
    }

    /* Minimal Button */
    .ripple-btn {
        position: relative;
        overflow: hidden;
        background: linear-gradient(135deg, #10b981, #059669);
        transition: all 0.3s ease;
    }

    .ripple-btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
    }

    .ripple-btn::before {
        content: '';
        position: absolute;
        top: 50%;
        left: 50%;
        width: 0;
        height: 0;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.3);
        transform: translate(-50%, -50%);
        transition: width 0.5s, height 0.5s;
    }

    .ripple-btn:active::before {
        width: 300px;
        height: 300px;
    }

    /* Fade in animations */
    .fade-in {
        animation: fadeIn 0.6s ease-out forwards;
        opacity: 0;
    }

    @keyframes fadeIn {
        to {
            opacity: 1;
        }
    }

    .slide-up {
        animation: slideUp 0.6s ease-out forwards;
        opacity: 0;
        transform: translateY(20px);
    }

    @keyframes slideUp {
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    </style>
</head>

<body class="min-h-screen flex items-center justify-center p-4 relative overflow-hidden" style="background: #fafafa;">
    <!-- Minimal Background -->
    <div class="auth-animated-bg"></div>

    <!-- Subtle Particles Container -->
    <div id="particles-container" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; z-index: -1;">
    </div>

    <div class="max-w-md w-full mx-auto relative z-10 slide-up">
        <!-- Minimal Logo -->
        <div class="text-center mb-8">
            <div class="inline-flex items-center space-x-3 mb-4 logo-animated">
                <div
                    class="w-14 h-14 bg-gradient-to-br from-emerald-400 to-emerald-600 rounded-2xl flex items-center justify-center shadow-sm">
                    <i class="fas fa-pills text-white text-2xl"></i>
                </div>
                <span class="text-3xl font-bold text-slate-800">New Gampaha Pharmacy</span>
            </div>
            <p class="text-slate-500 text-sm">Pharmacy Management System</p>
        </div>

        <!-- Forgot Password Form -->
        <div class="glass-card p-8 rounded-3xl">
            <div class="text-center mb-8">
                <div
                    class="w-16 h-16 bg-gradient-to-br from-emerald-100 to-emerald-200 rounded-2xl flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-key text-emerald-600 text-2xl"></i>
                </div>
                <h2 class="text-2xl font-bold text-slate-800 mb-2">
                    Forgot Password?
                </h2>
                <p class="text-slate-500 text-sm">Enter your email to reset your password</p>
            </div>

            <?php if ($error): ?>
            <div
                class="bg-red-50 border-l-4 border-red-400 text-red-700 px-4 py-3 rounded-xl mb-6 flex items-center gap-3">
                <i class="fas fa-exclamation-circle text-lg"></i>
                <span class="text-sm"><?php echo htmlspecialchars($error); ?></span>
            </div>
            <?php endif; ?>

            <?php if ($success): ?>
            <div class="bg-emerald-50 border-l-4 border-emerald-400 text-emerald-700 px-4 py-3 rounded-xl mb-6">
                <div class="flex items-start gap-3">
                    <i class="fas fa-check-circle text-lg mt-0.5"></i>
                    <div class="text-sm"><?php echo $success; ?></div>
                </div>
            </div>
            <?php endif; ?>

            <form method="POST" class="space-y-5" id="forgotPasswordForm">
                <div class="form-field">
                    <label for="email" class="block text-sm font-medium text-slate-700 mb-2">
                        Email Address
                    </label>
                    <div class="relative">
                        <input type="email" id="email" name="email" required
                            value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
                            class="w-full px-4 py-3 pl-12 animated-input rounded-xl focus:outline-none text-sm"
                            placeholder="your@email.com">
                        <i class="fas fa-envelope absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
                    </div>
                    <p class="text-xs text-slate-500 mt-2">We'll send you a link to reset your password</p>
                </div>

                <button type="submit"
                    class="w-full ripple-btn text-white py-3.5 text-sm font-semibold rounded-xl shadow-sm hover:shadow-md transition-all">
                    <i class="fas fa-paper-plane mr-2"></i>
                    Send Reset Link
                </button>
            </form>

            <div class="mt-6 flex items-center justify-center gap-2 text-sm">
                <span class="text-slate-600">Remember your password?</span>
                <a href="login.php"
                    class="text-emerald-600 hover:text-emerald-700 font-medium transition-colors inline-flex items-center gap-1">
                    Sign In <i class="fas fa-arrow-right text-xs"></i>
                </a>
            </div>
        </div>

        <!-- Back Link -->
        <div class="mt-4 text-center">
            <a href="../"
                class="text-sm text-slate-500 hover:text-slate-700 transition-colors inline-flex items-center gap-2">
                <i class="fas fa-arrow-left text-xs"></i> Back to Home
            </a>
        </div>
    </div>

    <script>
    // Auto-focus email field
    document.getElementById('email').focus();

    // Create subtle floating particles
    function createParticles() {
        const container = document.getElementById('particles-container');
        const particleCount = 25;

        for (let i = 0; i < particleCount; i++) {
            const particle = document.createElement('div');
            particle.className = 'particle';

            const size = Math.random() * 60 + 20;
            const startX = Math.random() * window.innerWidth;
            const startY = Math.random() * window.innerHeight;
            const duration = Math.random() * 25 + 15;
            const delay = Math.random() * 5;

            particle.style.width = `${size}px`;
            particle.style.height = `${size}px`;
            particle.style.left = `${startX}px`;
            particle.style.top = `${startY}px`;

            container.appendChild(particle);

            // Animate with anime.js
            if (typeof anime !== 'undefined') {
                anime({
                    targets: particle,
                    translateY: [{
                            value: -80,
                            duration: duration * 500
                        },
                        {
                            value: 80,
                            duration: duration * 500
                        }
                    ],
                    translateX: [{
                            value: -40,
                            duration: duration * 250
                        },
                        {
                            value: 40,
                            duration: duration * 500
                        },
                        {
                            value: -40,
                            duration: duration * 250
                        }
                    ],
                    opacity: [{
                            value: 0.5,
                            duration: duration * 250
                        },
                        {
                            value: 0.1,
                            duration: duration * 500
                        },
                        {
                            value: 0.5,
                            duration: duration * 250
                        }
                    ],
                    easing: 'easeInOutSine',
                    loop: true,
                    delay: delay * 1000
                });
            }
        }
    }

    // Animate form on load
    document.addEventListener('DOMContentLoaded', function() {
        createParticles();

        if (typeof anime !== 'undefined') {
            // Subtle stagger animation for form field
            anime({
                targets: '.form-field',
                translateY: [20, 0],
                opacity: [0, 1],
                delay: 400,
                duration: 600,
                easing: 'easeOutQuad'
            });
        }
    });
    </script>
</body>

</html>