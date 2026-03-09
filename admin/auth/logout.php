<?php
require_once __DIR__ . '/../bootstrap.php';

// Logout the user
logoutUser();

// Redirect to login page with logout message
header('Location: login.php?message=logout');
exit();