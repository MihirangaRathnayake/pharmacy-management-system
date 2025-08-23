<?php
require_once '../config/database.php';
require_once '../includes/auth.php';

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php?redirect=checkout.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$error = '';
$success = '';

// Get user details
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

// Get customer details
$stmt = $pdo->prepare("SELECT * FROM customers WHERE user_id = ?");
$stmt->execute([$user_id]);
$customer = $stmt->fetch();

if ($_POST && isset($_POST['place_order'])) {
    $cart_data = json_decode($_POST['cart_data'] ?? '[]', true);
    $payment_method = $_POST['payment_method'] ?? 'cash';
    $delivery_address = trim($_POST['delivery_address'] ?? '');
    $notes = trim($_POST['notes'] ?? '');

    if (empty($cart_data)) {
        $error = 'Your cart is empty';
    } elseif (empty($delivery_address)) {
        $error = 'Please provide a delivery address';
    } else {
        try {
            $pdo->beginTransaction();

            // Calculate totals
            $subtotal = 0;
            $valid_items = [];

            foreach ($cart_data as $item) {
                $stmt = $pdo->prepare("SELECT * FROM medicines WHERE id = ? AND status = 'active' AND stock_quantity >= ?");
                $stmt->execute([$item['id'], $item['quantity']]);
                $medicine = $stmt->fetch();

                if ($medicine) {
                    $item_total = $medicine['selling_price'] * $item['quantity'];
                    $subtotal += $item_total;
                    $valid_items[] = [
                        'medicine' => $medicine,
                        'quantity' => $item['quantity'],
                        'unit_price' => $medicine['selling_price'],
                        'total_price' => $item_total
                    ];
                }
            }

            if (empty($valid_items)) {
                throw new Exception('No valid items in cart');
            }

            $tax_rate = 18; // 18% GST
            $tax_amount = ($subtotal * $tax_rate) / 100;
            $total_amount = $subtotal + $tax_amount;

            // Generate invoice number
            $invoice_number = 'INV' . date('Ymd') . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);

            // Create sale record
            $stmt = $pdo->prepare("
                INSERT INTO sales (invoice_number, customer_id, user_id, subtotal, tax_amount, total_amount, payment_method, notes) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $customer_id = $customer ? $customer['id'] : null;
            $stmt->execute([
                $invoice_number, 
                $customer_id, 
                $user_id, 
                $subtotal, 
                $tax_amount, 
                $total_amount, 
                $payment_method, 
                "Delivery Address: $delivery_address\n\nNotes: $notes"
            ]);

            $sale_id = $pdo->lastInsertId();

            // Add sale items and update stock
            foreach ($valid_items as $item) {
                // Add sale item
                $stmt = $pdo->prepare("
                    INSERT INTO sale_items (sale_id, medicine_id, quantity, unit_price, total_price, batch_number, expiry_date) 
                    VALUES (?, ?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([
                    $sale_id,
                    $item['medicine']['id'],
                    $item['quantity'],
                    $item['unit_price'],
                    $item['total_price'],
                    $item['medicine']['batch_number'],
                    $item['medicine']['expiry_date']
                ]);

                // Update stock
                $stmt = $pdo->prepare("UPDATE medicines SET stock_quantity = stock_quantity - ? WHERE id = ?");
                $stmt->execute([$item['quantity'], $item['medicine']['id']]);

                // Add stock movement record
                $stmt = $pdo->prepare("
                    INSERT INTO stock_movements (medicine_id, movement_type, quantity, reference_type, reference_id, created_by) 
                    VALUES (?, 'out', ?, 'sale', ?, ?)
                ");
                $stmt->execute([$item['medicine']['id'], $item['quantity'], $sale_id, $user_id]);
            }

            $pdo->commit();
            
            $success = "Order placed successfully! Your order number is: $invoice_number";
            
            // Redirect to order confirmation
            header("Location: order-confirmation.php?order=$invoice_number");
            exit();

        } catch (Exception $e) {
            $pdo->rollBack();
            $error = 'Failed to place order: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - PharmaCare</title>
    
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/amazon-ember-font@latest/amazonember.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/theme.css">
    <link rel="stylesheet" href="assets/css/checkout.css">
</head>
<body>
    <?php include 'includes/navbar.php'; ?>

    <main class="checkout-page">
        <div class="container">
            <div class="checkout-header">
                <h1 class="gradient-text">Checkout</h1>
                <div class="checkout-steps">
                    <div class="step active">
                        <i class="fas fa-shopping-cart"></i>
                        <span>Cart</span>
                    </div>
                    <div class="step active">
                        <i class="fas fa-user"></i>
                        <span>Details</span>
                    </div>
                    <div class="step active">
                        <i class="fas fa-credit-card"></i>
                        <span>Payment</span>
                    </div>
                    <div class="step">
                        <i class="fas fa-check"></i>
                        <span>Complete</span>
                    </div>
                </div>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <div class="checkout-content">
                <div class="checkout-main">
                    <!-- Order Summary -->
                    <div class="order-summary glass-card">
                        <h2><i class="fas fa-list"></i> Order Summary</h2>
                        <div class="cart-items" id="checkoutCartItems">
                            <!-- Items will be loaded by JavaScript -->
                        </div>
                        <div class="order-totals" id="orderTotals">
                            <!-- Totals will be calculated by JavaScript -->
                        </div>
                    </div>

                    <!-- Delivery Information -->
                    <div class="delivery-info glass-card">
                        <h2><i class="fas fa-truck"></i> Delivery Information</h2>
                        <form method="POST" class="checkout-form" id="checkoutForm">
                            <input type="hidden" name="cart_data" id="cartDataInput">
                            <input type="hidden" name="place_order" value="1">

                            <div class="form-group">
                                <label for="delivery_address">Delivery Address *</label>
                                <textarea name="delivery_address" id="delivery_address" class="form-input" 
                                          rows="3" placeholder="Enter your complete delivery address" required><?= htmlspecialchars($user['address'] ?? '') ?></textarea>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label for="contact_name">Contact Name</label>
                                    <input type="text" name="contact_name" id="contact_name" class="form-input" 
                                           value="<?= htmlspecialchars($user['name']) ?>" readonly>
                                </div>
                                <div class="form-group">
                                    <label for="contact_phone">Contact Phone</label>
                                    <input type="tel" name="contact_phone" id="contact_phone" class="form-input" 
                                           value="<?= htmlspecialchars($user['phone'] ?? '') ?>" readonly>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="delivery_time">Preferred Delivery Time</label>
                                <select name="delivery_time" id="delivery_time" class="form-input">
                                    <option value="anytime">Anytime</option>
                                    <option value="morning">Morning (9 AM - 12 PM)</option>
                                    <option value="afternoon">Afternoon (12 PM - 5 PM)</option>
                                    <option value="evening">Evening (5 PM - 8 PM)</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="notes">Special Instructions</label>
                                <textarea name="notes" id="notes" class="form-input" 
                                          rows="2" placeholder="Any special delivery instructions..."></textarea>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="checkout-sidebar">
                    <!-- Payment Methods -->
                    <div class="payment-methods glass-card">
                        <h2><i class="fas fa-credit-card"></i> Payment Method</h2>
                        
                        <div class="payment-options">
                            <label class="payment-option">
                                <input type="radio" name="payment_method" value="cash" checked form="checkoutForm">
                                <div class="payment-card">
                                    <i class="fas fa-money-bill-wave"></i>
                                    <div class="payment-info">
                                        <h3>Cash on Delivery</h3>
                                        <p>Pay when you receive your order</p>
                                    </div>
                                </div>
                            </label>

                            <label class="payment-option">
                                <input type="radio" name="payment_method" value="card" form="checkoutForm">
                                <div class="payment-card">
                                    <i class="fas fa-credit-card"></i>
                                    <div class="payment-info">
                                        <h3>Credit/Debit Card</h3>
                                        <p>Secure online payment</p>
                                    </div>
                                </div>
                            </label>

                            <label class="payment-option">
                                <input type="radio" name="payment_method" value="upi" form="checkoutForm">
                                <div class="payment-card">
                                    <i class="fas fa-mobile-alt"></i>
                                    <div class="payment-info">
                                        <h3>UPI Payment</h3>
                                        <p>Pay using UPI apps</p>
                                    </div>
                                </div>
                            </label>

                            <label class="payment-option">
                                <input type="radio" name="payment_method" value="online" form="checkoutForm">
                                <div class="payment-card">
                                    <i class="fas fa-university"></i>
                                    <div class="payment-info">
                                        <h3>Net Banking</h3>
                                        <p>Direct bank transfer</p>
                                    </div>
                                </div>
                            </label>
                        </div>
                    </div>

                    <!-- Order Actions -->
                    <div class="order-actions glass-card">
                        <div class="final-total" id="finalTotal">
                            <div class="total-row">
                                <span>Total Amount:</span>
                                <span class="amount">Rs 0.00</span>
                            </div>
                        </div>

                        <button type="submit" form="checkoutForm" class="btn btn-success btn-full btn-large" id="placeOrderBtn">
                            <i class="fas fa-shopping-bag"></i>
                            Place Order
                        </button>

                        <div class="security-info">
                            <div class="security-item">
                                <i class="fas fa-shield-alt"></i>
                                <span>Secure & encrypted payment</span>
                            </div>
                            <div class="security-item">
                                <i class="fas fa-undo"></i>
                                <span>Easy returns & refunds</span>
                            </div>
                            <div class="security-item">
                                <i class="fas fa-headset"></i>
                                <span>24/7 customer support</span>
                            </div>
                        </div>
                    </div>

                    <!-- Delivery Info -->
                    <div class="delivery-promise glass-card">
                        <h3><i class="fas fa-truck-fast"></i> Delivery Promise</h3>
                        <div class="promise-items">
                            <div class="promise-item">
                                <i class="fas fa-clock"></i>
                                <span>Same day delivery available</span>
                            </div>
                            <div class="promise-item">
                                <i class="fas fa-box"></i>
                                <span>Secure packaging</span>
                            </div>
                            <div class="promise-item">
                                <i class="fas fa-thermometer-half"></i>
                                <span>Temperature controlled</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <?php include 'includes/footer.php'; ?>

    <script src="assets/js/main.js"></script>
    <script src="assets/js/checkout.js"></script>
</body>
</html>