<?php
require_once dirname(__DIR__, 2) . '/bootstrap.php';
require_once dirname(__DIR__, 2) . '/includes/email_helper.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

$input = json_decode(file_get_contents('php://input'), true);
if (!is_array($input)) {
    $input = $_POST;
}

$email = trim($input['email'] ?? '');
$invoiceId = (int)($input['id'] ?? 0);

if (!$invoiceId) {
    echo json_encode(['success' => false, 'message' => 'Invoice ID is required']);
    exit();
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Please enter a valid email address']);
    exit();
}

try {
    global $pdo;

    if (!$pdo) {
        throw new Exception('Database connection not available');
    }

    $saleStmt = $pdo->prepare("
        SELECT s.*, c.name AS customer_name, c.email AS customer_email, u.name AS cashier_name
        FROM sales s
        LEFT JOIN customers c ON s.customer_id = c.id
        LEFT JOIN users u ON s.user_id = u.id
        WHERE s.id = ?
    ");
    $saleStmt->execute([$invoiceId]);
    $sale = $saleStmt->fetch(PDO::FETCH_ASSOC);

    if (!$sale) {
        echo json_encode(['success' => false, 'message' => 'Invoice not found']);
        exit();
    }

    $itemsStmt = $pdo->prepare("
        SELECT si.quantity, si.unit_price, m.name AS medicine_name
        FROM sale_items si
        LEFT JOIN medicines m ON si.medicine_id = m.id
        WHERE si.sale_id = ?
    ");
    $itemsStmt->execute([$invoiceId]);
    $items = $itemsStmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($items)) {
        echo json_encode(['success' => false, 'message' => 'No invoice items found']);
        exit();
    }

    $invoiceNumber = $sale['invoice_number'] ?? ('INV-' . str_pad((string)$invoiceId, 6, '0', STR_PAD_LEFT));
    $customerName = $sale['customer_name'] ?: 'Walk-in Customer';
    $saleDate = !empty($sale['created_at']) ? date('M d, Y g:i A', strtotime($sale['created_at'])) : date('M d, Y g:i A');
    $paymentMethod = ucfirst($sale['payment_method'] ?? 'Cash');
    $cashierName = $sale['cashier_name'] ?: 'N/A';

    $subtotal = (float)($sale['subtotal'] ?? 0);
    $taxAmount = (float)($sale['tax_amount'] ?? 0);
    $discountAmount = (float)($sale['discount_amount'] ?? 0);
    $grandTotal = (float)($sale['total_amount'] ?? 0);

    if ($subtotal <= 0) {
        $subtotal = 0;
        foreach ($items as $item) {
            $subtotal += ((float)$item['quantity'] * (float)$item['unit_price']);
        }
        $grandTotal = $subtotal + $taxAmount - $discountAmount;
    }

    $itemRows = '';
    foreach ($items as $index => $item) {
        $qty = (float)$item['quantity'];
        $unitPrice = (float)$item['unit_price'];
        $lineTotal = $qty * $unitPrice;

        $itemRows .= '<tr>';
        $itemRows .= '<td style="padding:8px;border:1px solid #e5e7eb;">' . ($index + 1) . '</td>';
        $itemRows .= '<td style="padding:8px;border:1px solid #e5e7eb;">' . htmlspecialchars((string)($item['medicine_name'] ?? 'Unknown Item')) . '</td>';
        $itemRows .= '<td style="padding:8px;border:1px solid #e5e7eb;text-align:right;">' . number_format($qty, 0) . '</td>';
        $itemRows .= '<td style="padding:8px;border:1px solid #e5e7eb;text-align:right;">Rs ' . number_format($unitPrice, 2) . '</td>';
        $itemRows .= '<td style="padding:8px;border:1px solid #e5e7eb;text-align:right;">Rs ' . number_format($lineTotal, 2) . '</td>';
        $itemRows .= '</tr>';
    }

    $subject = 'Invoice ' . $invoiceNumber . ' - New Gampaha Pharmacy';
    $message = '
        <div style="font-family:Arial,sans-serif;max-width:700px;margin:0 auto;color:#111827;">
            <h2 style="color:#059669;margin-bottom:8px;">New Gampaha Pharmacy</h2>
            <p style="margin-top:0;">Thank you for your purchase. Your invoice details are below.</p>

            <div style="background:#f9fafb;padding:12px 16px;border-radius:8px;margin:16px 0;">
                <p style="margin:4px 0;"><strong>Invoice:</strong> ' . htmlspecialchars($invoiceNumber) . '</p>
                <p style="margin:4px 0;"><strong>Date:</strong> ' . htmlspecialchars($saleDate) . '</p>
                <p style="margin:4px 0;"><strong>Customer:</strong> ' . htmlspecialchars($customerName) . '</p>
                <p style="margin:4px 0;"><strong>Cashier:</strong> ' . htmlspecialchars($cashierName) . '</p>
                <p style="margin:4px 0;"><strong>Payment Method:</strong> ' . htmlspecialchars($paymentMethod) . '</p>
            </div>

            <table style="width:100%;border-collapse:collapse;margin-bottom:16px;">
                <thead>
                    <tr style="background:#ecfdf5;">
                        <th style="padding:8px;border:1px solid #e5e7eb;text-align:left;">#</th>
                        <th style="padding:8px;border:1px solid #e5e7eb;text-align:left;">Medicine</th>
                        <th style="padding:8px;border:1px solid #e5e7eb;text-align:right;">Qty</th>
                        <th style="padding:8px;border:1px solid #e5e7eb;text-align:right;">Unit Price</th>
                        <th style="padding:8px;border:1px solid #e5e7eb;text-align:right;">Total</th>
                    </tr>
                </thead>
                <tbody>' . $itemRows . '</tbody>
            </table>

            <div style="margin-left:auto;max-width:280px;">
                <p style="margin:4px 0;display:flex;justify-content:space-between;"><span>Subtotal:</span><strong>Rs ' . number_format($subtotal, 2) . '</strong></p>
                <p style="margin:4px 0;display:flex;justify-content:space-between;"><span>Tax:</span><strong>Rs ' . number_format($taxAmount, 2) . '</strong></p>
                <p style="margin:4px 0;display:flex;justify-content:space-between;"><span>Discount:</span><strong>-Rs ' . number_format($discountAmount, 2) . '</strong></p>
                <p style="margin:8px 0 0 0;display:flex;justify-content:space-between;font-size:18px;"><span>Total:</span><strong style="color:#059669;">Rs ' . number_format($grandTotal, 2) . '</strong></p>
            </div>
        </div>
    ';

    $sent = sendEmail($email, $subject, $message);

    if (!$sent) {
        echo json_encode([
            'success' => false,
            'message' => 'Email send failed. Check SMTP settings in .env and try again.'
        ]);
        exit();
    }

    echo json_encode([
        'success' => true,
        'message' => 'Invoice emailed successfully to ' . $email
    ]);
} catch (Exception $e) {
    error_log('Invoice email error: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Error sending invoice email: ' . $e->getMessage()
    ]);
}

