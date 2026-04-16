<?php
// ============================================================
//  API: Add item to cart  –  POST {food_id, quantity}
// ============================================================
require_once __DIR__ . '/../../includes/session.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/functions.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, 'Method not allowed.');
}

$foodId   = isset($_POST['food_id'])  ? (int) $_POST['food_id']  : 0;
$quantity = isset($_POST['quantity']) ? (int) $_POST['quantity'] : 1;

if ($foodId <= 0 || $quantity <= 0) {
    jsonResponse(false, 'Invalid food ID or quantity.');
}

$pdo  = getDB();
$stmt = $pdo->prepare(
    "SELECT id, name, price, image_url, stock FROM food_items WHERE id = ? AND status = 'active' LIMIT 1"
);
$stmt->execute([$foodId]);
$food = $stmt->fetch();

if (!$food) {
    jsonResponse(false, 'Food item not found.');
}
if ($food['stock'] < $quantity) {
    jsonResponse(false, 'Insufficient stock.');
}

addToCart($foodId, $quantity, (float) $food['price'], $food['name'], $food['image_url'] ?? '');

jsonResponse(true, 'Item added to cart.', [
    'cart_count' => getCartCount(),
    'cart_total' => getCartTotal(),
]);
