<?php
// ============================================================
//  API: Submit a food review  –  POST {food_id, rating, comment}
// ============================================================
require_once __DIR__ . '/../../includes/session.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/functions.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, 'Method not allowed.');
}
if (!isLoggedIn()) {
    jsonResponse(false, 'Please log in to submit a review.');
}

$foodId  = isset($_POST['food_id']) ? (int) $_POST['food_id'] : 0;
$rating  = isset($_POST['rating'])  ? (int) $_POST['rating']  : 0;
$comment = trim($_POST['comment'] ?? '');

if ($foodId <= 0 || $rating < 1 || $rating > 5) {
    jsonResponse(false, 'Invalid food ID or rating (must be 1–5).');
}

$pdo    = getDB();
$userId = (int) $_SESSION['user_id'];

// Prevent duplicate review by same user for same food
$dup = $pdo->prepare("SELECT id FROM reviews WHERE user_id = ? AND food_id = ? LIMIT 1");
$dup->execute([$userId, $foodId]);
if ($dup->fetch()) {
    jsonResponse(false, 'You have already reviewed this item.');
}

$ins = $pdo->prepare(
    "INSERT INTO reviews (user_id, food_id, rating, comment) VALUES (?, ?, ?, ?)"
);
$ins->execute([$userId, $foodId, $rating, $comment]);

jsonResponse(true, 'Review submitted. Thank you!', ['review_id' => (int) $pdo->lastInsertId()]);
