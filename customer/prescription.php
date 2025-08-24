<?php
// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Prescription - PharmaCare</title>
    
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/amazon-ember-font@latest/amazonember.css" rel="stylesheet">
    <!-- FontAwesome Icons - Multiple CDN fallbacks -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" integrity="sha512-iecdLmaskl7CVkqkXNQ/ZH/XLlvWZOJyj7Yy7tcenmpD1ypASozpmT/E0iPtmFIB46ZmdtAc9eNBvH0H/ZpiBw==" crossorigin="anonymous" referrerpolicy="no-referrer">
    <!-- Backup FontAwesome CDNs -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v6.4.0/css/all.css">
    <!-- FontAwesome 5 fallback -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" integrity="sha512-1ycn6IcaQQ40/MKBW2W4Rhis/DbILU74C1vSrLJxCq57o941Ym01SwNsOMqvEBFlcgUa6xLiPY/NS5R+E6ztJQ==" crossorigin="anonymous" referrerpolicy="no-referrer">
    
    <link rel="stylesheet" href="assets/css/icons-fix.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/theme.css">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar">
        <div class="nav-container">
            <div class="nav-brand">
                <a href="index.php">
                    <i class="fas fa-plus-circle"></i>
                    <span class="brand-text">PharmaCare</span>
                </a>
            </div>
            
            <div class="nav-menu" id="navMenu">
                <a href="index.php" class="nav-link">
                    <i class="fas fa-home"></i>
                    <span>Home</span>
                </a>
                <a href="products.php" class="nav-link">
                    <i class="fas fa-pills"></i>
                    <span>Products</span>
                </a>
                <a href="prescription.php" class="nav-link">
                    <i class="fas fa-prescription"></i>
                    <span>Prescription</span>
                </a>
                <a href="contact.php" class="nav-link">
                    <i class="fas fa-phone"></i>
                    <span>Contact</span>
                </a>
            </div>

            <div class="nav-actions">
                <div class="auth-buttons">
                    <a href="login.php" class="btn btn-outline">
                        <i class="fas fa-sign-in-alt"></i>
                        Login
                    </a>
                    <a href="register.php" class="btn btn-primary">
                        <i class="fas fa-user-plus"></i>
                        Register
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <main style="padding-top: 100px; min-height: 80vh; display: flex; align-items: center; justify-content: center;">
        <div class="container">
            <div class="glass-card" style="max-width: 600px; margin: 0 auto; padding: 3rem; text-align: center;">
                <h1 class="gradient-text" style="font-size: 2.5rem; margin-bottom: 1rem;">Upload Prescription</h1>
                <p style="font-size: 1.1rem; color: #666; margin-bottom: 2rem;">This feature is coming soon! Upload your prescription and get your medicines delivered.</p>
                <div style="font-size: 4rem; color: #ddd; margin-bottom: 2rem;">
                    <i class="fas fa-prescription"></i>
                </div>
                <a href="index.php" class="btn btn-primary">
                    <i class="fas fa-arrow-left"></i>
                    Back to Home
                </a>
            </div>
        </div>
    </main>

    <script src="assets/js/icons-fix.js"></script>
    <script src="assets/js/icons.js"></script>
    <script src="assets/js/main.js"></script>
</body>
</html>