<?php
// ============================================================
//  API: Update cart item quantity  –  POST {food_id, quantity}
// ============================================================
require_once __DIR__ . '/../../includes/session.php';
require_once __DIR__ . '/../../includes/functions.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, 'Method not allowed.');
}

$foodId   = isset($_POST['food_id'])  ? (int) $_POST['food_id']  : 0;
$quantity = isset($_POST['quantity']) ? (int) $_POST['quantity'] : 0;

if ($foodId <= 0) {
    jsonResponse(false, 'Invalid food ID.');
}

updateCartQty($foodId, $quantity);

jsonResponse(true, 'Cart updated.', [
    'cart_count' => getCartCount(),
    'cart_total' => getCartTotal(),
    'cart'       => getCartFromSession(),
]);
