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
$email = $_SESSION['reset_email'] ?? $_GET['email'] ?? '';
$token = $_SESSION['reset_token'] ?? $_GET['token'] ?? '';

// Redirect if no email/token
if (empty($email) || empty($token)) {
    header('Location: forgot_password.php');
    exit();
}

// Handle code verification
if ($_POST && isset($_POST['code'])) {
    $code = trim($_POST['code']);

    if (empty($code)) {
        $error = 'Please enter the verification code';
    } elseif (!preg_match('/^\d{6}$/', $code)) {
        $error = 'Please enter a valid 6-digit code';
    } else {
        try {
            // Verify code
            $stmt = $pdo->prepare("
                SELECT prt.*, u.name
                FROM password_reset_tokens prt
                JOIN users u ON prt.user_id = u.id
                WHERE prt.email = ? AND prt.token = ? AND prt.verification_code = ?
                AND prt.used = FALSE AND prt.verified = FALSE AND prt.expires_at > NOW()
            ");
            $stmt->execute([$email, $token, $code]);
            $resetData = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($resetData) {
                // Mark as verified
                $stmt = $pdo->prepare("UPDATE password_reset_tokens SET verified = TRUE WHERE id = ?");
                $stmt->execute([$resetData['id']]);

                // Store in session for reset password page
                $_SESSION['verified_reset_token'] = $token;
                $_SESSION['verified_reset_email'] = $email;

                // Redirect to reset password page
                header('Location: reset_password.php?token=' . $token);
                exit();
            } else {
                $error = 'Invalid or expired verification code. Please try again.';
            }
        } catch (Exception $e) {
            error_log("Code verification error: " . $e->getMessage());
            $error = 'An error occurred. Please try again.';
        }
    }
}

// Handle resend code
if (isset($_POST['resend'])) {
    try {
        // Get user data
        $stmt = $pdo->prepare("SELECT id, name FROM users WHERE email = ? AND status = 'active'");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            // Generate new code
            $newCode = generateVerificationCode();
            $expiresAt = date('Y-m-d H:i:s', strtotime('+15 minutes'));

            // Update existing token with new code
            $stmt = $pdo->prepare("
                UPDATE password_reset_tokens
                SET verification_code = ?, expires_at = ?, verified = FALSE
                WHERE email = ? AND token = ? AND used = FALSE
            ");
            $stmt->execute([$newCode, $expiresAt, $email, $token]);

            // Send new code
            $emailSent = sendPasswordResetCode($email, $user['name'], $newCode);

            if ($emailSent) {
                $success = 'A new verification code has been sent to your email.';
            } else {
                $error = "Failed to send email. <br><strong>DEV MODE - Your new code is:</strong> {$newCode}";
            }
        }
    } catch (Exception $e) {
        error_log("Resend code error: " . $e->getMessage());
        $error = 'An error occurred while resending the code.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <title>Verify Code - Pharmacy Management System</title>
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

        /* Code Input Styling */
        .code-input {
            font-size: 32px;
            letter-spacing: 16px;
            text-align: center;
            font-family: 'Courier New', monospace;
            font-weight: bold;
            color: #10b981;
        }

        .code-input::-webkit-inner-spin-button,
        .code-input::-webkit-outer-spin-button {
            -webkit-appearance: none;
            margin: 0;
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

        /* Timer */
        .timer {
            font-family: 'Courier New', monospace;
            font-size: 20px;
            font-weight: bold;
            color: #10b981;
        }

        .timer.warning {
            color: #f97316;
        }

        .timer.expired {
            color: #ef4444;
        }

        /* Fade in animations */
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
                <span class="text-3xl font-bold text-slate-800">New Gampaha Pharmacy</span>
            </div>
            <p class="text-slate-500 text-sm">Pharmacy Management System</p>
        </div>

        <!-- Verify Code Form -->
        <div class="glass-card p-8 rounded-3xl">
            <div class="text-center mb-8">
                <div class="w-16 h-16 bg-gradient-to-br from-blue-100 to-blue-200 rounded-2xl flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-shield-alt text-blue-600 text-2xl"></i>
                </div>
                <h2 class="text-2xl font-bold text-slate-800 mb-2">
                    Enter Verification Code
                </h2>
                <p class="text-slate-500 text-sm">We sent a 6-digit code to</p>
                <p class="text-emerald-600 font-medium text-sm mt-1"><?php echo htmlspecialchars($email); ?></p>
            </div>

            <?php if ($error): ?>
                <div class="bg-red-50 border-l-4 border-red-400 text-red-700 px-4 py-3 rounded-xl mb-6 flex items-center gap-3">
                    <i class="fas fa-exclamation-circle text-lg"></i>
                    <span class="text-sm"><?php echo $error; ?></span>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="bg-emerald-50 border-l-4 border-emerald-400 text-emerald-700 px-4 py-3 rounded-xl mb-6 flex items-center gap-3">
                    <i class="fas fa-check-circle text-lg"></i>
                    <span class="text-sm"><?php echo $success; ?></span>
                </div>
            <?php endif; ?>

            <!-- Timer -->
            <div class="text-center mb-6">
                <div class="inline-flex items-center gap-2 bg-gradient-to-r from-emerald-50 to-blue-50 px-4 py-2 rounded-full border border-emerald-100">
                    <i class="fas fa-clock text-emerald-600"></i>
                    <span class="text-sm text-slate-600">Code expires in:</span>
                    <span class="timer" id="timer">15:00</span>
                </div>
            </div>

            <form method="POST" class="space-y-5" id="verifyCodeForm">
                <div class="form-field">
                    <label for="code" class="block text-sm font-medium text-slate-700 mb-2 text-center">
                        Verification Code
                    </label>
                    <input type="text" id="code" name="code" required maxlength="6" pattern="\d{6}"
                        class="w-full px-4 py-4 code-input animated-input rounded-xl focus:outline-none"
                        placeholder="000000" autocomplete="off" autofocus>
                    <p class="text-xs text-slate-500 mt-2 text-center">Enter the 6-digit code from your email</p>
                </div>

                <button type="submit"
                    class="w-full ripple-btn text-white py-3.5 text-sm font-semibold rounded-xl shadow-sm hover:shadow-md transition-all">
                    <i class="fas fa-check mr-2"></i>
                    Verify Code
                </button>
            </form>

            <div class="mt-6 text-center">
                <p class="text-sm text-slate-600 mb-3">Didn't receive the code?</p>
                <form method="POST" class="inline">
                    <button type="submit" name="resend" class="text-emerald-600 hover:text-emerald-700 font-medium text-sm transition-colors inline-flex items-center gap-1">
                        <i class="fas fa-redo text-xs"></i> Resend Code
                    </button>
                </form>
            </div>

            <div class="mt-6 flex items-center justify-center gap-2 text-sm">
                <a href="forgot_password.php" class="text-slate-600 hover:text-slate-800 transition-colors inline-flex items-center gap-1">
                    <i class="fas fa-arrow-left text-xs"></i> Back to Email Entry
                </a>
            </div>
        </div>

        <!-- Back Link -->
        <div class="mt-4 text-center">
            <a href="login.php" class="text-sm text-slate-500 hover:text-slate-700 transition-colors inline-flex items-center gap-2">
                <i class="fas fa-sign-in-alt text-xs"></i> Back to Login
            </a>
        </div>
    </div>

    <script>
        // Auto-format code input (only allow digits)
        const codeInput = document.getElementById('code');
        codeInput.addEventListener('input', function(e) {
            this.value = this.value.replace(/\D/g, '');
            if (this.value.length === 6) {
                // Auto-submit when 6 digits entered
                document.getElementById('verifyCodeForm').submit();
            }
        });

        // Countdown timer (15 minutes)
        const timerElement = document.getElementById('timer');
        let timeLeft = 15 * 60; // 15 minutes in seconds

        function updateTimer() {
            const minutes = Math.floor(timeLeft / 60);
            const seconds = timeLeft % 60;
            timerElement.textContent = `${minutes}:${seconds.toString().padStart(2, '0')}`;

            if (timeLeft <= 60) {
                timerElement.classList.add('warning');
            }
            if (timeLeft <= 0) {
                timerElement.classList.remove('warning');
                timerElement.classList.add('expired');
                timerElement.textContent = 'Expired';
                codeInput.disabled = true;
                return;
            }

            timeLeft--;
            setTimeout(updateTimer, 1000);
        }

        updateTimer();

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
                // Subtle animation for form field
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