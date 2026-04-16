<?php
// ============================================================
//  API: Track order by order code  –  GET ?order_code=XXX
// ============================================================
require_once __DIR__ . '/../../includes/session.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/functions.php';

header('Content-Type: application/json; charset=utf-8');

$code = trim($_GET['order_code'] ?? '');

if ($code === '') {
    jsonResponse(false, 'Order code is required.');
}

$pdo = getDB();

$stmt = $pdo->prepare(
    "SELECT o.id, o.order_code, o.type, o.subtotal, o.discount, o.total,
            o.status, o.address, o.phone, o.promo_code, o.created_at,
            u.name AS customer_name
     FROM orders o
     JOIN users u ON u.id = o.user_id
     WHERE o.order_code = ?
     LIMIT 1"
);
$stmt->execute([$code]);
$order = $stmt->fetch();

if (!$order) {
    jsonResponse(false, 'Order not found.');
}

// Fetch order items
$itemStmt = $pdo->prepare(
    "SELECT oi.quantity, oi.unit_price, oi.line_total, f.name AS food_name, f.image_url
     FROM order_items oi
     JOIN food_items f ON f.id = oi.food_id
     WHERE oi.order_id = ?"
);
$itemStmt->execute([$order['id']]);
$items = $itemStmt->fetchAll();

jsonResponse(true, 'Order found.', [
    'order' => $order,
    'items' => $items,
]);
