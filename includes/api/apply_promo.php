<?php
// ============================================================
//  API: Apply promo code  –  POST {promo_code, order_total}
// ============================================================
require_once __DIR__ . '/../../includes/session.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/functions.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, 'Method not allowed.');
}

$code       = strtoupper(trim($_POST['promo_code']   ?? ''));
$orderTotal = isset($_POST['order_total']) ? (float) $_POST['order_total'] : 0.0;

if ($code === '' || $orderTotal <= 0) {
    jsonResponse(false, 'Promo code and order total are required.');
}

$pdo  = getDB();
$stmt = $pdo->prepare(
    "SELECT * FROM promo_codes
     WHERE code = ? AND status = 'active'
       AND expires_at >= CURDATE()
       AND used_count < max_uses
     LIMIT 1"
);
$stmt->execute([$code]);
$promo = $stmt->fetch();

if (!$promo) {
    jsonResponse(false, 'Invalid, expired, or maxed-out promo code.');
}
if ($orderTotal < (float) $promo['min_order']) {
    jsonResponse(false, sprintf(
        'Minimum order of ৳%.2f required to use this code.',
        $promo['min_order']
    ));
}

$discountPercent = (int) $promo['discount_percent'];
$discountAmount  = round($orderTotal * $discountPercent / 100, 2);
$newTotal        = max(0, $orderTotal - $discountAmount);

jsonResponse(true, "Promo applied! {$discountPercent}% off.", [
    'discount_percent' => $discountPercent,
    'discount_amount'  => $discountAmount,
    'new_total'        => $newTotal,
]);
