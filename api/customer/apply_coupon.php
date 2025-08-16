<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../../config/database.php';

$input = json_decode(file_get_contents('php://input'), true);
$couponCode = $input['coupon_code'] ?? '';
$cartTotal = floatval($input['cart_total'] ?? 0);

if (!$couponCode || $cartTotal <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid coupon code or cart total']);
    exit();
}

try {
    // For demo purposes, we'll use predefined coupons
    // In a real application, you'd have a coupons table
    $coupons = [
        'WELCOME20' => [
            'type' => 'percentage',
            'value' => 20,
            'min_amount' => 100,
            'max_discount' => 500,
            'description' => '20% off on first order'
        ],
        'SAVE50' => [
            'type' => 'fixed',
            'value' => 50,
            'min_amount' => 200,
            'max_discount' => 50,
            'description' => 'Rs 50 off on orders above Rs 200'
        ],
        'HEALTH10' => [
            'type' => 'percentage',
            'value' => 10,
            'min_amount' => 300,
            'max_discount' => 200,
            'description' => '10% off on health products'
        ]
    ];
    
    $coupon = $coupons[strtoupper($couponCode)] ?? null;
    
    if (!$coupon) {
        echo json_encode(['success' => false, 'message' => 'Invalid coupon code']);
        exit();
    }
    
    if ($cartTotal < $coupon['min_amount']) {
        echo json_encode([
            'success' => false, 
            'message' => "Minimum order amount of Rs {$coupon['min_amount']} required for this coupon"
        ]);
        exit();
    }
    
    // Calculate discount
    if ($coupon['type'] === 'percentage') {
        $discountAmount = ($cartTotal * $coupon['value']) / 100;
        $discountAmount = min($discountAmount, $coupon['max_discount']);
    } else {
        $discountAmount = min($coupon['value'], $coupon['max_discount']);
    }
    
    $discountAmount = min($discountAmount, $cartTotal); // Can't discount more than cart total
    
    echo json_encode([
        'success' => true,
        'coupon' => [
            'code' => strtoupper($couponCode),
            'type' => $coupon['type'],
            'value' => $coupon['value'],
            'description' => $coupon['description']
        ],
        'discount_amount' => round($discountAmount, 2),
        'new_total' => round($cartTotal - $discountAmount, 2)
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error applying coupon: ' . $e->getMessage()
    ]);
}
?>