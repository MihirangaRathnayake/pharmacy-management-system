<?php
// Ensure user is logged in and get user data
if (!isLoggedIn()) {
    header('Location: ' . url('auth/login.php'));
    exit();
}

// Get current user data for navbar
if (!isset($user)) {
    $user = getCurrentUser();
}

// Get user preferences for theme
$user_theme = 'light';
if ($user) {
    $stmt = $pdo->prepare("SELECT theme FROM user_preferences WHERE user_id = ?");
    $stmt->execute([$user['id']]);
    $prefs = $stmt->fetch();
    $user_theme = $prefs['theme'] ?? 'light';
}
?>
<!DOCTYPE html>
<html lang="en" data-theme="<?php echo htmlspecialchars($user_theme); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? htmlspecialchars($page_title) . ' - ' : ''; ?>PharmaCare</title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Google Fonts - Inter (Primary Font) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap" rel="stylesheet">
    
    <!-- Amazon Ember Font via Multiple CDN Sources -->
    <style>
        /* Amazon Ember from CDNFonts */
        @import url('https://fonts.cdnfonts.com/css/amazon-ember');
        
        /* Amazon Ember Display Font Faces */
        @font-face {
            font-family: 'Amazon Ember Display';
            src: url('https://d2c87l0yth4zbw.cloudfront.net/fonts/amazon-ember/AmazonEmberDisplay_Rg.woff2') format('woff2'),
                 url('https://d2c87l0yth4zbw.cloudfront.net/fonts/amazon-ember/AmazonEmberDisplay_Rg.woff') format('woff'),
                 url('https://m.media-amazon.com/images/G/01/gc/designs/livepreview/amazon_ember_display_regular._TTH_.ttf') format('truetype');
            font-weight: 400;
            font-style: normal;
            font-display: swap;
        }
        
        @font-face {
            font-family: 'Amazon Ember Display';
            src: url('https://d2c87l0yth4zbw.cloudfront.net/fonts/amazon-ember/AmazonEmberDisplay_Md.woff2') format('woff2'),
                 url('https://d2c87l0yth4zbw.cloudfront.net/fonts/amazon-ember/AmazonEmberDisplay_Md.woff') format('woff');
            font-weight: 500;
            font-style: normal;
            font-display: swap;
        }
        
        @font-face {
            font-family: 'Amazon Ember Display';
            src: url('https://d2c87l0yth4zbw.cloudfront.net/fonts/amazon-ember/AmazonEmberDisplay_Bd.woff2') format('woff2'),
                 url('https://d2c87l0yth4zbw.cloudfront.net/fonts/amazon-ember/AmazonEmberDisplay_Bd.woff') format('woff'),
                 url('https://m.media-amazon.com/images/G/01/gc/designs/livepreview/amazon_ember_display_bold._TTH_.ttf') format('truetype');
            font-weight: 600;
            font-style: normal;
            font-display: swap;
        }
        
        @font-face {
            font-family: 'Amazon Ember Display';
            src: url('https://d2c87l0yth4zbw.cloudfront.net/fonts/amazon-ember/AmazonEmberDisplay_Bd.woff2') format('woff2'),
                 url('https://d2c87l0yth4zbw.cloudfront.net/fonts/amazon-ember/AmazonEmberDisplay_Bd.woff') format('woff');
            font-weight: 700;
            font-style: normal;
            font-display: swap;
        }
        
        /* Amazon Ember Regular Font Faces */
        @font-face {
            font-family: 'Amazon Ember';
            src: url('https://d2c87l0yth4zbw.cloudfront.net/fonts/amazon-ember/AmazonEmber_Rg.woff2') format('woff2'),
                 url('https://d2c87l0yth4zbw.cloudfront.net/fonts/amazon-ember/AmazonEmber_Rg.woff') format('woff');
            font-weight: 400;
            font-style: normal;
            font-display: swap;
        }
        
        @font-face {
            font-family: 'Amazon Ember';
            src: url('https://d2c87l0yth4zbw.cloudfront.net/fonts/amazon-ember/AmazonEmber_Md.woff2') format('woff2'),
                 url('https://d2c87l0yth4zbw.cloudfront.net/fonts/amazon-ember/AmazonEmber_Md.woff') format('woff');
            font-weight: 500;
            font-style: normal;
            font-display: swap;
        }
        
        @font-face {
            font-family: 'Amazon Ember';
            src: url('https://d2c87l0yth4zbw.cloudfront.net/fonts/amazon-ember/AmazonEmber_Bd.woff2') format('woff2'),
                 url('https://d2c87l0yth4zbw.cloudfront.net/fonts/amazon-ember/AmazonEmber_Bd.woff') format('woff');
            font-weight: 600;
            font-style: normal;
            font-display: swap;
        }
        
        @font-face {
            font-family: 'Amazon Ember';
            src: url('https://d2c87l0yth4zbw.cloudfront.net/fonts/amazon-ember/AmazonEmber_Bd.woff2') format('woff2'),
                 url('https://d2c87l0yth4zbw.cloudfront.net/fonts/amazon-ember/AmazonEmber_Bd.woff') format('woff');
            font-weight: 700;
            font-style: normal;
            font-display: swap;
        }
    </style>
    
    <!-- Font Awesome - Multiple CDN Sources for Reliability -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer">
    
    <!-- Fallback Font Awesome CDN -->
    <script>
        // Check if Font Awesome loaded, if not load from alternative CDN
        document.addEventListener('DOMContentLoaded', function() {
            var testIcon = document.createElement('i');
            testIcon.className = 'fas fa-home';
            testIcon.style.position = 'absolute';
            testIcon.style.left = '-9999px';
            document.body.appendChild(testIcon);
            
            var computedStyle = window.getComputedStyle(testIcon, ':before');
            if (computedStyle.content === 'none' || computedStyle.content === '') {
                // Font Awesome didn't load, try alternative CDN
                var fallbackLink = document.createElement('link');
                fallbackLink.rel = 'stylesheet';
                fallbackLink.href = 'https://use.fontawesome.com/releases/v6.5.1/css/all.css';
                document.head.appendChild(fallbackLink);
            }
            document.body.removeChild(testIcon);
        });
    </script>
    
    <!-- Ensure Font Awesome icons display properly -->
    <style>
        /* Font Awesome Icon Fixes */
        .fas, .far, .fab, .fal, .fad, .fa {
            font-family: "Font Awesome 6 Free", "Font Awesome 6 Pro", "Font Awesome 6 Brands" !important;
            font-weight: 900 !important;
            font-style: normal !important;
            font-variant: normal !important;
            text-rendering: auto !important;
            line-height: 1 !important;
            -webkit-font-smoothing: antialiased !important;
            -moz-osx-font-smoothing: grayscale !important;
            display: inline-block !important;
            speak: none !important;
        }
        
        .far {
            font-weight: 400 !important;
        }
        
        .fab {
            font-weight: 400 !important;
            font-family: "Font Awesome 6 Brands" !important;
        }
        
        /* Ensure icons are visible */
        i.fas, i.far, i.fab, i.fal, i.fad, i.fa {
            font-family: "Font Awesome 6 Free" !important;
            font-style: normal !important;
            font-weight: 900 !important;
            text-transform: none !important;
            line-height: 1 !important;
            -webkit-font-smoothing: antialiased !important;
            -moz-osx-font-smoothing: grayscale !important;
        }
        
        /* Override any conflicting font declarations */
        * .fas, * .far, * .fab, * .fal, * .fad, * .fa {
            font-family: "Font Awesome 6 Free" !important;
        }
        
        /* Specific icon visibility fixes */
        .fa-camera, .fa-edit, .fa-save, .fa-times, .fa-chart-line, 
        .fa-rupee-sign, .fa-shopping-bag, .fa-cash-register, 
        .fa-history, .fa-key, .fa-cog, .fa-chart-bar, .fa-sign-out-alt,
        .fa-circle {
            font-family: "Font Awesome 6 Free" !important;
            font-weight: 900 !important;
            display: inline-block !important;
        }
    </style>
    
    <!-- Custom Font System -->
    <link rel="stylesheet" href="<?php echo url('assets/css/fonts.css'); ?>">
    <link rel="stylesheet" href="<?php echo url('assets/css/font-overrides.css'); ?>">
    
    <!-- Custom Theme CSS -->
    <link rel="stylesheet" href="<?php echo url('assets/css/theme.css'); ?>">
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="<?php echo url('assets/images/favicon.ico'); ?>">
</head>
<body class="bg-gray-50">
    <?php include __DIR__ . '/navbar.php'; ?>
    
    <main class="min-h-screen">