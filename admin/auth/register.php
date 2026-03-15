<?php
require_once dirname(__DIR__) . '/bootstrap.php';

// Create users table if it doesn't exist
if (!createUsersTable()) {
    die('Error: Could not create users table. Please check database connection.');
}

// Check if admin already exists
if (hasAdminUser()) {
    header('Location: login.php?message=admin_exists');
    exit();
}

$error = '';
$success = '';

if ($_POST) {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirm_password'];

    // Validate passwords match
    if ($password !== $confirmPassword) {
        $error = 'Passwords do not match';
    } else {
        $result = registerUser($name, $email, $password, 'admin');

        if ($result['success']) {
            $success = 'Admin account created successfully! You can now login.';
        } else {
            $error = $result['message'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <title>Admin Registration - Pharmacy Management System</title>
    <?php include '../includes/head.php'; ?>
    <style>
        /* Minimal Light Background */
        .auth-animated-bg {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, #faf5ff 0%, #f0f9ff 50%, #f0fdf4 100%);
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

        /* Clean Card */
        .glass-card {
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(139, 92, 246, 0.08);
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.04);
            transition: all 0.3s ease;
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
            border-color: #8b5cf6;
            box-shadow: 0 0 0 3px rgba(139, 92, 246, 0.1);
        }

        /* Success Animation */
        @keyframes successPop {
            0% {
                transform: scale(0);
                opacity: 0;
            }
            50% {
                transform: scale(1.05);
            }
            100% {
                transform: scale(1);
                opacity: 1;
            }
        }

        .success-icon {
            animation: successPop 0.5s ease-out;
        }

        /* Slide in animations */
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

        /* Subtle Particles */
        .particle {
            position: absolute;
            background: rgba(139, 92, 246, 0.05);
            border-radius: 50%;
            pointer-events: none;
        }
    </style>
</head>

<body class="min-h-screen flex items-center justify-center p-4 relative overflow-hidden" style="background: #fafafa;">
    <!-- Minimal Background -->
    <div class="auth-animated-bg"></div>

    <!-- Subtle Particles Container -->
    <div id="particles-container" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; z-index: -1;"></div>

    <div class="max-w-lg w-full relative z-10 slide-up">
        <!-- Minimal Logo -->
        <div class="text-center mb-8">
            <div class="inline-flex items-center space-x-3 mb-4 logo-animated">
                <div class="w-14 h-14 bg-gradient-to-br from-purple-400 to-emerald-500 rounded-2xl flex items-center justify-center shadow-sm">
                    <i class="fas fa-pills text-white text-2xl"></i>
                </div>
                <span class="text-3xl font-bold text-slate-800">New Gampaha Pharmacy</span>
            </div>
            <p class="text-slate-500 text-sm">Create Admin Account</p>
        </div>

        <!-- Registration Form -->
        <div class="glass-card rounded-3xl shadow-sm p-8">
            <div class="text-center mb-8">
                <div class="w-16 h-16 bg-gradient-to-br from-purple-100 to-emerald-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-user-shield text-purple-600 text-2xl"></i>
                </div>
                <h2 class="text-2xl font-bold text-slate-800 mb-2">
                    Admin Registration
                </h2>
                <p class="text-slate-500 text-sm">Set up your admin account</p>
            </div>

            <?php if ($error): ?>
                <div class="bg-red-50 border-l-4 border-red-400 text-red-700 px-4 py-3 rounded-xl mb-6 flex items-center gap-3">
                    <i class="fas fa-exclamation-circle text-lg"></i>
                    <span class="text-sm"><?php echo htmlspecialchars($error); ?></span>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="bg-emerald-50 border-l-4 border-emerald-400 text-emerald-700 px-4 py-3 rounded-xl mb-6">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="success-icon w-10 h-10 bg-emerald-500 rounded-full flex items-center justify-center">
                            <i class="fas fa-check text-white text-lg"></i>
                        </div>
                        <div>
                            <h4 class="font-semibold text-base">Success!</h4>
                            <p class="text-sm"><?php echo htmlspecialchars($success); ?></p>
                        </div>
                    </div>
                    <a href="login.php" class="inline-flex items-center gap-2 text-emerald-700 font-medium hover:gap-3 transition-all text-sm">
                        Go to Login <i class="fas fa-arrow-right text-xs"></i>
                    </a>
                </div>
            <?php else: ?>
                <form method="POST" class="space-y-4" id="registerForm">
                    <div class="form-field">
                        <label for="name" class="block text-sm font-medium text-slate-700 mb-2">
                            Full Name
                        </label>
                        <input type="text" id="name" name="name" required
                               value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>"
                               class="w-full px-4 py-3 animated-input rounded-xl focus:outline-none text-sm"
                               placeholder="Enter your full name">
                    </div>

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
                                   placeholder="Minimum 6 characters">
                            <button type="button" onclick="togglePassword('password')" class="absolute inset-y-0 right-0 pr-4 flex items-center text-slate-400 hover:text-purple-600 transition-colors">
                                <i class="fas fa-eye text-base" id="passwordToggle"></i>
                            </button>
                        </div>
                        <div id="passwordStrength" class="mt-2 h-1 bg-slate-100 rounded-full overflow-hidden">
                            <div id="passwordStrengthBar" class="h-full transition-all duration-300" style="width: 0%;"></div>
                        </div>
                        <p id="passwordStrengthText" class="text-xs mt-1.5 text-slate-500"></p>
                    </div>

                    <div class="form-field">
                        <label for="confirm_password" class="block text-sm font-medium text-slate-700 mb-2">
                            Confirm Password
                        </label>
                        <div class="relative">
                            <input type="password" id="confirm_password" name="confirm_password" required
                                   class="w-full px-4 py-3 animated-input rounded-xl focus:outline-none pr-12 text-sm"
                                   placeholder="Re-enter your password">
                            <button type="button" onclick="togglePassword('confirm_password')" class="absolute inset-y-0 right-0 pr-4 flex items-center text-slate-400 hover:text-purple-600 transition-colors">
                                <i class="fas fa-eye text-base" id="confirmPasswordToggle"></i>
                            </button>
                        </div>
                    </div>

                    <button type="submit" class="w-full bg-gradient-to-r from-purple-500 to-emerald-500 hover:from-purple-600 hover:to-emerald-600 text-white font-semibold py-3.5 px-4 rounded-xl transition-all duration-300 flex items-center justify-center shadow-sm hover:shadow-md text-sm mt-6">
                        <i class="fas fa-user-plus mr-2"></i>
                        Create Admin Account
                    </button>
                </form>
            <?php endif; ?>
        </div>

        <!-- Info Box -->
        <div class="mt-6 glass-card p-5 rounded-2xl border-l-4 border-purple-200 bg-purple-50/30">
            <div class="flex items-start gap-3">
                <i class="fas fa-info-circle text-purple-600 text-lg"></i>
                <div>
                    <h3 class="text-sm font-semibold text-slate-800 mb-1">
                        First Time Setup
                    </h3>
                    <p class="text-xs text-slate-600 leading-relaxed">This is a one-time setup to create your admin account. After registration, you'll be able to login and manage the system.</p>
                </div>
            </div>
        </div>

        <!-- Links -->
        <div class="mt-4 text-center text-sm flex items-center justify-center gap-4">
            <a href="login.php" class="text-slate-500 hover:text-slate-700 transition-colors inline-flex items-center gap-2">
                <i class="fas fa-sign-in-alt text-xs"></i> Already have an account?
            </a>
            <span class="text-slate-300">|</span>
            <a href="../" class="text-slate-500 hover:text-slate-700 transition-colors inline-flex items-center gap-2">
                <i class="fas fa-arrow-left text-xs"></i> Back to Home
            </a>
        </div>
    </div>

    <script>
        function togglePassword(fieldId) {
            const passwordInput = document.getElementById(fieldId);
            const toggleIcon = document.getElementById(fieldId + 'Toggle');

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

        // Password strength indicator
        document.getElementById('password').addEventListener('input', function() {
            const password = this.value;
            const strengthBar = document.getElementById('passwordStrengthBar');
            const strengthText = document.getElementById('passwordStrengthText');

            let strength = 0;
            let feedback = '';
            let color = '';

            if (password.length >= 6) strength += 25;
            if (password.length >= 8) strength += 25;
            if (/[a-z]/.test(password) && /[A-Z]/.test(password)) strength += 25;
            if (/[0-9]/.test(password)) strength += 25;

            if (strength <= 25) {
                feedback = 'Weak password';
                color = '#ef4444';
            } else if (strength <= 50) {
                feedback = 'Fair password';
                color = '#f59e0b';
            } else if (strength <= 75) {
                feedback = 'Good password';
                color = '#8b5cf6';
            } else {
                feedback = 'Strong password';
                color = '#10b981';
            }

            strengthBar.style.width = strength + '%';
            strengthBar.style.backgroundColor = color;
            strengthText.textContent = feedback;
            strengthText.style.color = color;
        });

        // Form validation
        document.querySelector('form').addEventListener('submit', function(e) {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;

            if (password !== confirmPassword) {
                e.preventDefault();
                alert('Passwords do not match!');
                return false;
            }

            if (password.length < 6) {
                e.preventDefault();
                alert('Password must be at least 6 characters long!');
                return false;
            }
        });

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
                    delay: anime.stagger(80, {start: 300}),
                    duration: 600,
                    easing: 'easeOutQuad'
                });
            }
        });
    </script>
</body>

</html>