<?php
if (!isLoggedIn()) {
    header('Location: ' . url('auth/login.php'));
    exit();
}

if (!isset($user)) {
    $user = getCurrentUser();
}
?>
<!DOCTYPE html>
<html lang="en" data-theme="<?php echo function_exists('getThemeClass') ? getThemeClass() : 'light'; ?>">
<head>
    <title><?php echo isset($page_title) ? htmlspecialchars($page_title) . ' - ' : ''; ?>New Gampaha Pharmacy</title>
    <?php include __DIR__ . '/head.php'; ?>
</head>
<body class="pc-shell">
    <?php include __DIR__ . '/navbar.php'; ?>
    <main class="pc-container">
