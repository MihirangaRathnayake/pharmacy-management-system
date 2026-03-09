<?php
require_once dirname(__DIR__) . '/bootstrap.php';

// Redirect if already logged in
if (isLoggedIn()) {
    header('Location: ../index.php');
    exit();
}

$error = '';
$success = '';
$validToken = false;
$token = $_GET['token'] ?? '';

// Verify token
if (!empty($token)) {
    try {
        $stmt = $pdo->prepare("
            SELECT prt.*, u.email, u.name
            FROM password_reset_tokens prt
            JOIN users u ON prt.user_id = u.id
            WHERE prt.token = ? AND prt.used = FALSE AND prt.expires_at > NOW()
        ");
        $stmt->execute([$token]);
        $resetData = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($resetData) {
            $validToken = true;
        } else {
            $error = 'This password reset link is invalid or has expired. Please request a new one.';
        }
    } catch (Exception $e) {
        error_log("Reset password token verification error: " . $e->getMessage());
        $error = 'An error occurred. Please try again later.';
    }
} else {
    $error = 'No reset token provided.';
}

// Handle password reset form submission
if ($_POST && $validToken) {
    $newPassword = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    if (empty($newPassword) || empty($confirmPassword)) {
        $error = 'Please enter both password fields';
    } elseif (strlen($newPassword) < 6) {
        $error = 'Password must be at least 6 characters long';
    } elseif ($newPassword !== $confirmPassword) {
        $error = 'Passwords do not match';
    } else {
        try {
            // Update user password
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt->execute([$hashedPassword, $resetData['user_id']]);

            // Mark token as used
            $stmt = $pdo->prepare("UPDATE password_reset_tokens SET used = TRUE WHERE id = ?");
            $stmt->execute([$resetData['id']]);

            $success = 'Your password has been reset successfully! You can now log in with your new password.';
            $validToken = false; // Prevent form from showing again
        } catch (Exception $e) {
            error_log("Reset password error: " . $e->getMessage());
            $error = 'An error occurred while resetting your password. Please try again.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <title>Reset Password - Pharmacy Management System</title>
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

        /* Password Strength Indicator */
        .strength-bar {
            height: 4px;
            border-radius: 2px;
            transition: all 0.3s ease;
        }

        .strength-weak {
            background: #ef4444;
        }

        .strength-medium {
            background: #f97316;
        }

        .strength-strong {
            background: #10b981;
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
    <div id="particles-container" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; z-index: -1;"></div>

    <div class="max-w-md w-full mx-auto relative z-10 slide-up">
        <!-- Minimal Logo -->
        <div class="text-center mb-8">
            <div class="inline-flex items-center space-x-3 mb-4 logo-animated">
                <div class="w-14 h-14 bg-gradient-to-br from-emerald-400 to-emerald-600 rounded-2xl flex items-center justify-center shadow-sm">
                    <i class="fas fa-pills text-white text-2xl"></i>
                </div>
                <span class="text-3xl font-bold text-slate-800">PharmaCare</span>
            </div>
            <p class="text-slate-500 text-sm">Pharmacy Management System</p>
        </div>

        <!-- Reset Password Form -->
        <div class="glass-card p-8 rounded-3xl">
            <div class="text-center mb-8">
                <div class="w-16 h-16 bg-gradient-to-br from-purple-100 to-purple-200 rounded-2xl flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-lock text-purple-600 text-2xl"></i>
                </div>
                <h2 class="text-2xl font-bold text-slate-800 mb-2">
                    Reset Your Password
                </h2>
                <p class="text-slate-500 text-sm">Enter your new password below</p>
            </div>

            <?php if ($error): ?>
                <div class="bg-red-50 border-l-4 border-red-400 text-red-700 px-4 py-3 rounded-xl mb-6 flex items-center gap-3">
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
                <a href="login.php" class="w-full inline-flex items-center justify-center ripple-btn text-white py-3.5 text-sm font-semibold rounded-xl shadow-sm hover:shadow-md transition-all">
                    <i class="fas fa-sign-in-alt mr-2"></i>
                    Go to Login
                </a>
            <?php elseif ($validToken): ?>
                <form method="POST" class="space-y-5" id="resetPasswordForm">
                    <div class="form-field">
                        <label for="password" class="block text-sm font-medium text-slate-700 mb-2">
                            New Password
                        </label>
                        <div class="relative">
                            <input type="password" id="password" name="password" required
                                class="w-full px-4 py-3 pl-12 animated-input rounded-xl focus:outline-none text-sm"
                                placeholder="Enter new password" minlength="6">
                            <i class="fas fa-lock absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
                            <button type="button" onclick="togglePassword('password')"
                                class="absolute inset-y-0 right-0 pr-4 flex items-center text-slate-400 hover:text-emerald-600 transition-colors">
                                <i class="fas fa-eye text-base" id="toggleIconPassword"></i>
                            </button>
                        </div>
                        <div class="mt-2">
                            <div class="strength-bar strength-weak" id="strengthBar" style="width: 0%"></div>
                            <p class="text-xs text-slate-500 mt-1" id="strengthText">Minimum 6 characters</p>
                        </div>
                    </div>

                    <div class="form-field">
                        <label for="confirm_password" class="block text-sm font-medium text-slate-700 mb-2">
                            Confirm Password
                        </label>
                        <div class="relative">
                            <input type="password" id="confirm_password" name="confirm_password" required
                                class="w-full px-4 py-3 pl-12 animated-input rounded-xl focus:outline-none text-sm"
                                placeholder="Confirm new password" minlength="6">
                            <i class="fas fa-lock absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
                            <button type="button" onclick="togglePassword('confirm_password')"
                                class="absolute inset-y-0 right-0 pr-4 flex items-center text-slate-400 hover:text-emerald-600 transition-colors">
                                <i class="fas fa-eye text-base" id="toggleIconConfirm"></i>
                            </button>
                        </div>
                    </div>

                    <button type="submit"
                        class="w-full ripple-btn text-white py-3.5 text-sm font-semibold rounded-xl shadow-sm hover:shadow-md transition-all">
                        <i class="fas fa-check mr-2"></i>
                        Reset Password
                    </button>
                </form>
            <?php else: ?>
                <a href="forgot_password.php" class="w-full inline-flex items-center justify-center ripple-btn text-white py-3.5 text-sm font-semibold rounded-xl shadow-sm hover:shadow-md transition-all">
                    <i class="fas fa-redo mr-2"></i>
                    Request New Reset Link
                </a>
            <?php endif; ?>

            <div class="mt-6 flex items-center justify-center gap-2 text-sm">
                <span class="text-slate-600">Remember your password?</span>
                <a href="login.php" class="text-emerald-600 hover:text-emerald-700 font-medium transition-colors inline-flex items-center gap-1">
                    Sign In <i class="fas fa-arrow-right text-xs"></i>
                </a>
            </div>
        </div>

        <!-- Back Link -->
        <div class="mt-4 text-center">
            <a href="../" class="text-sm text-slate-500 hover:text-slate-700 transition-colors inline-flex items-center gap-2">
                <i class="fas fa-arrow-left text-xs"></i> Back to Home
            </a>
        </div>
    </div>

    <script>
        // Toggle Password Visibility
        function togglePassword(fieldId) {
            const passwordInput = document.getElementById(fieldId);
            const toggleIcon = document.getElementById('toggleIcon' + fieldId.charAt(0).toUpperCase() + fieldId.slice(1).replace('_', ''));

            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.classList.remove('fa-eye');
                toggleIcon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                toggleIcon.classList.remove('fa-eye-slash');
                toggleIcon.classList.add('fa-eye');
            }
        }

        // Password Strength Indicator
        const passwordInput = document.getElementById('password');
        if (passwordInput) {
            passwordInput.addEventListener('input', function() {
                const password = this.value;
                const strengthBar = document.getElementById('strengthBar');
                const strengthText = document.getElementById('strengthText');

                let strength = 0;
                let feedback = '';

                if (password.length >= 6) strength++;
                if (password.length >= 10) strength++;
                if (/[a-z]/.test(password) && /[A-Z]/.test(password)) strength++;
                if (/[0-9]/.test(password)) strength++;
                if (/[^a-zA-Z0-9]/.test(password)) strength++;

                if (strength === 0) {
                    strengthBar.style.width = '0%';
                    feedback = 'Minimum 6 characters';
                } else if (strength <= 2) {
                    strengthBar.style.width = '33%';
                    strengthBar.className = 'strength-bar strength-weak';
                    feedback = 'Weak password';
                } else if (strength <= 4) {
                    strengthBar.style.width = '66%';
                    strengthBar.className = 'strength-bar strength-medium';
                    feedback = 'Medium strength';
                } else {
                    strengthBar.style.width = '100%';
                    strengthBar.className = 'strength-bar strength-strong';
                    feedback = 'Strong password';
                }

                strengthText.textContent = feedback;
            });
        }

        // Auto-focus password field
        if (document.getElementById('password')) {
            document.getElementById('password').focus();
        }

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
                // Subtle stagger animation for form fields
                anime({
                    targets: '.form-field',
                    translateY: [20, 0],
                    opacity: [0, 1],
                    delay: anime.stagger(80, {
                        start: 400
                    }),
                    duration: 600,
                    easing: 'easeOutQuad'
                });
            }
        });
    </script>
</body>

</html>