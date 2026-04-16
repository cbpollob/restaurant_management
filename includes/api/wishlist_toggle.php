<?php
// ============================================================
//  API: Toggle wishlist item  –  POST {food_id}
// ============================================================
require_once __DIR__ . '/../../includes/session.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/functions.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, 'Method not allowed.');
}
if (!isLoggedIn()) {
    jsonResponse(false, 'Please log in to use the wishlist.');
}

$foodId = isset($_POST['food_id']) ? (int) $_POST['food_id'] : 0;
if ($foodId <= 0) {
    jsonResponse(false, 'Invalid food ID.');
}

$pdo    = getDB();
$userId = (int) $_SESSION['user_id'];

// Check if already in wishlist
$check = $pdo->prepare("SELECT id FROM wishlist WHERE user_id = ? AND food_id = ? LIMIT 1");
$check->execute([$userId, $foodId]);
$existing = $check->fetch();

if ($existing) {
    $pdo->prepare("DELETE FROM wishlist WHERE user_id = ? AND food_id = ?")->execute([$userId, $foodId]);
    jsonResponse(true, 'Removed from wishlist.', ['in_wishlist' => false]);
} else {
    $pdo->prepare("INSERT INTO wishlist (user_id, food_id) VALUES (?, ?)")->execute([$userId, $foodId]);
    jsonResponse(true, 'Added to wishlist.', ['in_wishlist' => true]);
}
