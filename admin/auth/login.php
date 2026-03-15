<?php
require_once dirname(__DIR__) . '/bootstrap.php';

// Create users table if it doesn't exist
if (!createUsersTable()) {
    die('Error: Could not create users table. Please check database connection.');
}

// Check if admin user exists, if not redirect to registration
if (!hasAdminUser()) {
    header('Location: register.php');
    exit();
}

// Redirect if already logged in
if (isLoggedIn()) {
    header('Location: ../index.php');
    exit();
}

$error = '';
$message = '';

// Handle messages from URL
if (isset($_GET['message'])) {
    switch ($_GET['message']) {
        case 'admin_exists':
            $message = 'Admin account already exists. Please login below.';
            break;
        case 'logout':
            $message = 'You have been successfully logged out.';
            break;
        case 'access_denied':
            $error = 'Access denied. Please login to continue.';
            break;
    }
}

if ($_POST) {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        $error = 'Please enter both email and password';
    } else {
        $result = loginUser($email, $password);

        if ($result['success']) {
            // Redirect to dashboard
            header('Location: ../index.php');
            exit();
        } else {
            $error = $result['message'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <title>Login - Pharmacy Management System</title>
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
            0%, 100% {
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
            0%, 100% {
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
    <div id="particles-container" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; z-index: -1;"></div>

    <div class="max-w-5xl w-full grid lg:grid-cols-2 gap-8 items-stretch relative z-10">
        <!-- Left Panel - Info Section -->
        <section class="hidden lg:flex glass-card p-10 flex-col justify-between rounded-3xl fade-in" style="animation-delay: 0.1s;">
            <div>
                <div class="inline-flex items-center gap-2 text-emerald-600 text-sm font-semibold mb-6 px-4 py-2 bg-emerald-50 rounded-full border border-emerald-100">
                    <i class="fas fa-shield-heart"></i> Trusted Platform
                </div>
                <h2 class="text-4xl font-bold leading-tight text-slate-800 mb-4">
                    Manage your pharmacy with ease
                </h2>
                <p class="text-base text-slate-600 leading-relaxed">Complete pharmacy management system with inventory, sales, and customer tracking.</p>
            </div>
            <div class="space-y-4">
                <div class="flex items-start gap-3 p-4 bg-gradient-to-r from-emerald-50/50 to-transparent rounded-2xl">
                    <div class="w-8 h-8 rounded-full bg-emerald-100 flex items-center justify-center flex-shrink-0">
                        <i class="fas fa-check text-emerald-600 text-sm"></i>
                    </div>
                    <div>
                        <strong class="text-slate-800 text-sm">Stock Management</strong>
                        <p class="text-slate-600 text-sm">Real-time inventory tracking</p>
                    </div>
                </div>
                <div class="flex items-start gap-3 p-4 bg-gradient-to-r from-blue-50/50 to-transparent rounded-2xl">
                    <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center flex-shrink-0">
                        <i class="fas fa-check text-blue-600 text-sm"></i>
                    </div>
                    <div>
                        <strong class="text-slate-800 text-sm">Quick Sales</strong>
                        <p class="text-slate-600 text-sm">Fast and efficient POS</p>
                    </div>
                </div>
                <div class="flex items-start gap-3 p-4 bg-gradient-to-r from-purple-50/50 to-transparent rounded-2xl">
                    <div class="w-8 h-8 rounded-full bg-purple-100 flex items-center justify-center flex-shrink-0">
                        <i class="fas fa-check text-purple-600 text-sm"></i>
                    </div>
                    <div>
                        <strong class="text-slate-800 text-sm">Reports & Analytics</strong>
                        <p class="text-slate-600 text-sm">Detailed business insights</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Right Panel - Login Form -->
        <div class="max-w-md w-full mx-auto slide-up" style="animation-delay: 0.2s;">
            <!-- Minimal Logo -->
            <div class="text-center mb-8">
                <div class="inline-flex items-center space-x-3 mb-4 logo-animated">
                    <div class="w-14 h-14 bg-gradient-to-br from-emerald-400 to-emerald-600 rounded-2xl flex items-center justify-center shadow-sm">
                        <i class="fas fa-pills text-white text-2xl"></i>
                    </div>
                    <span class="text-3xl font-bold text-slate-800">New Gampaha Pharmacy</span>
                </div>
                <p class="text-slate-500 text-sm">Pharmacy Management System</p>
            </div>

            <!-- Login Form -->
            <div class="glass-card p-8 rounded-3xl" id="loginCard">
                <h2 class="text-2xl font-bold text-slate-800 mb-2 text-center">
                    Welcome Back
                </h2>
                <p class="text-slate-500 text-center mb-8 text-sm">Sign in to your dashboard</p>

                <?php if ($error): ?>
                    <div class="bg-red-50 border-l-4 border-red-400 text-red-700 px-4 py-3 rounded-xl mb-6 flex items-center gap-3">
                        <i class="fas fa-exclamation-circle text-lg"></i>
                        <span class="text-sm"><?php echo htmlspecialchars($error); ?></span>
                    </div>
                <?php endif; ?>

                <?php if ($message): ?>
                    <div class="bg-emerald-50 border-l-4 border-emerald-400 text-emerald-700 px-4 py-3 rounded-xl mb-6 flex items-center gap-3">
                        <i class="fas fa-check-circle text-lg"></i>
                        <span class="text-sm"><?php echo htmlspecialchars($message); ?></span>
                    </div>
                <?php endif; ?>

                <form method="POST" class="space-y-5" id="loginForm">
                    <div class="form-field">
                        <label for="email" class="block text-sm font-medium text-slate-700 mb-2">
                            Email Address
                        </label>
                        <input type="email" id="email" name="email" required
                            value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
                            class="w-full px-4 py-3 animated-input rounded-xl focus:outline-none text-sm"
                            placeholder="admin@pharmacare.com">
                    </div>

                    <div class="form-field">
                        <label for="password" class="block text-sm font-medium text-slate-700 mb-2">
                            Password
                        </label>
                        <div class="relative">
                            <input type="password" id="password" name="password" required
                                class="w-full px-4 py-3 animated-input rounded-xl focus:outline-none pr-12 text-sm"
                                placeholder="Enter your password">
                            <button type="button" onclick="togglePassword()"
                                class="absolute inset-y-0 right-0 pr-4 flex items-center text-slate-400 hover:text-emerald-600 transition-colors">
                                <i class="fas fa-eye text-base" id="toggleIcon"></i>
                            </button>
                        </div>
                    </div>

                    <div class="flex items-center justify-between text-sm">
                        <div class="flex items-center gap-2">
                            <input type="checkbox" id="remember" name="remember"
                                class="w-4 h-4 text-emerald-600 focus:ring-emerald-500 border-gray-300 rounded">
                            <label for="remember" class="text-slate-600 cursor-pointer select-none">Remember me</label>
                        </div>
                        <a href="forgot_password.php" class="text-emerald-600 hover:text-emerald-700 font-medium transition-colors">Forgot password?</a>
                    </div>

                    <button type="submit"
                        class="w-full ripple-btn text-white py-3.5 text-sm font-semibold rounded-xl shadow-sm hover:shadow-md transition-all">
                        <i class="fas fa-sign-in-alt mr-2"></i>
                        Sign In to Dashboard
                    </button>
                </form>
            </div>

            <!-- Info Box -->
            <div class="mt-6 glass-card p-5 rounded-2xl border-l-4 border-blue-200 bg-blue-50/30">
                <div class="flex items-start gap-3">
                    <i class="fas fa-info-circle text-blue-600 text-lg"></i>
                    <div>
                        <h3 class="text-sm font-semibold text-slate-800 mb-1">
                            Need an Account?
                        </h3>
                        <p class="text-xs text-slate-600 mb-2">Setting up the system for the first time?</p>
                        <a href="register.php" class="text-emerald-600 hover:text-emerald-700 font-medium text-sm inline-flex items-center gap-1 transition-colors">
                            Create Admin Account <i class="fas fa-arrow-right text-xs"></i>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Back Link -->
            <div class="mt-4 text-center">
                <a href="../" class="text-sm text-slate-500 hover:text-slate-700 transition-colors inline-flex items-center gap-2">
                    <i class="fas fa-arrow-left text-xs"></i> Back to Home
                </a>
            </div>
        </div>
    </div>

    <script>
        // Toggle Password Visibility
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const toggleIcon = document.getElementById('toggleIcon');

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

        // Auto-focus email field
        document.getElementById('email').focus();

        // Create subtle floating particles
        function createParticles() {
            const container = document.getElementById('particles-container');
            const particleCount = 30;

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
                        translateY: [
                            { value: -80, duration: duration * 500 },
                            { value: 80, duration: duration * 500 }
                        ],
                        translateX: [
                            { value: -40, duration: duration * 250 },
                            { value: 40, duration: duration * 500 },
                            { value: -40, duration: duration * 250 }
                        ],
                        opacity: [
                            { value: 0.5, duration: duration * 250 },
                            { value: 0.1, duration: duration * 500 },
                            { value: 0.5, duration: duration * 250 }
                        ],
                        easing: 'easeInOutSine',
                        loop: true,
                        delay: delay * 1000
                    });
                }
            }
        }

        // Animate form fields on load
        document.addEventListener('DOMContentLoaded', function() {
            createParticles();

            if (typeof anime !== 'undefined') {
                // Subtle stagger animation for form fields
                anime({
                    targets: '.form-field',
                    translateY: [20, 0],
                    opacity: [0, 1],
                    delay: anime.stagger(80, {start: 400}),
                    duration: 600,
                    easing: 'easeOutQuad'
                });

                // Subtle logo animation
                anime({
                    targets: '.logo-animated',
                    scale: [1, 1.02, 1],
                    duration: 3000,
                    easing: 'easeInOutQuad',
                    loop: true
                });
            }
        });
    </script>
</body>

</html>