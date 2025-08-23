<?php
header('Content-Type: application/json');
session_start();

$response = [
    'authenticated' => false,
    'user' => null
];

if (isset($_SESSION['user_id'])) {
    $response['authenticated'] = true;
    $response['user'] = [
        'id' => $_SESSION['user_id'],
        'name' => $_SESSION['user_name'] ?? '',
        'email' => $_SESSION['user_email'] ?? '',
        'role' => $_SESSION['user_role'] ?? 'customer'
    ];
}

echo json_encode($response);