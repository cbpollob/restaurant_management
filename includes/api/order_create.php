<?php
// ============================================================
//  API: Create an order  –  POST
// ============================================================
require_once __DIR__ . '/../../includes/session.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/functions.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, 'Method not allowed.');
}
if (!isLoggedIn()) {
    jsonResponse(false, 'Please log in to place an order.');
}

$cart = getCartFromSession();
if (empty($cart)) {
    jsonResponse(false, 'Your cart is empty.');
}

$type      = in_array($_POST['type'] ?? '', ['dine-in', 'delivery']) ? $_POST['type'] : 'delivery';
$address   = trim($_POST['address']    ?? '');
$phone     = trim($_POST['phone']      ?? '');
$promoCode = strtoupper(trim($_POST['promo_code'] ?? ''));

if ($type === 'delivery' && ($address === '' || $phone === '')) {
    jsonResponse(false, 'Delivery address and phone are required.');
}

$pdo      = getDB();
$subtotal = getCartTotal();
$discount = 0.0;

// Apply promo code if provided
if ($promoCode !== '') {
    $ps = $pdo->prepare(
        "SELECT * FROM promo_codes
         WHERE code = ? AND status = 'active'
           AND expires_at >= CURDATE()
           AND used_count < max_uses
         LIMIT 1"
    );
    $ps->execute([$promoCode]);
    $promo = $ps->fetch();
    if ($promo && $subtotal >= (float) $promo['min_order']) {
        $discount = round($subtotal * $promo['discount_percent'] / 100, 2);
        // Increment used_count
        $pdo->prepare("UPDATE promo_codes SET used_count = used_count + 1 WHERE id = ?")
            ->execute([$promo['id']]);
    }
}

$total     = max(0, $subtotal - $discount);
$orderCode = generateOrderCode();

$pdo->beginTransaction();
try {
    // Insert order
    $ins = $pdo->prepare(
        "INSERT INTO orders (user_id, order_code, type, subtotal, discount, total, status, address, phone, promo_code)
         VALUES (?, ?, ?, ?, ?, ?, 'placed', ?, ?, ?)"
    );
    $ins->execute([
        $_SESSION['user_id'], $orderCode, $type,
        $subtotal, $discount, $total,
        $address ?: null, $phone ?: null,
        $promoCode ?: null,
    ]);
    $orderId = (int) $pdo->lastInsertId();

    // Insert order items
    $itemStmt = $pdo->prepare(
        "INSERT INTO order_items (order_id, food_id, quantity, unit_price, line_total)
         VALUES (?, ?, ?, ?, ?)"
    );
    foreach ($cart as $item) {
        $lineTotal = $item['price'] * $item['qty'];
        $itemStmt->execute([$orderId, $item['food_id'], $item['qty'], $item['price'], $lineTotal]);
        // Decrease stock
        $pdo->prepare("UPDATE food_items SET stock = stock - ? WHERE id = ? AND stock >= ?")
            ->execute([$item['qty'], $item['food_id'], $item['qty']]);
    }

    $pdo->commit();
} catch (Exception $e) {
    $pdo->rollBack();
    error_log('order_create error: ' . $e->getMessage());
    jsonResponse(false, 'Failed to create order. Please try again.');
}

// Clear cart
$_SESSION['cart'] = [];

jsonResponse(true, 'Order created successfully!', [
    'order_id'   => $orderId,
    'order_code' => $orderCode,
    'total'      => $total,
]);
