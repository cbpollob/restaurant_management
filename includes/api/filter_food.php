<?php
// ============================================================
//  API: Filter food items by category  –  GET ?category_id=N
// ============================================================
require_once __DIR__ . '/../../includes/session.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/functions.php';

header('Content-Type: application/json; charset=utf-8');

$categoryId = isset($_GET['category_id']) ? (int) $_GET['category_id'] : 0;

$pdo = getDB();

if ($categoryId > 0) {
    $stmt = $pdo->prepare(
        "SELECT f.id, f.name, f.description, f.price, f.image_url, f.stock, f.featured,
                c.name AS category_name,
                COALESCE(AVG(r.rating), 0) AS avg_rating,
                COUNT(r.id) AS review_count
         FROM food_items f
         JOIN categories c ON c.id = f.category_id
         LEFT JOIN reviews r ON r.food_id = f.id
         WHERE f.status = 'active' AND f.category_id = ?
         GROUP BY f.id
         ORDER BY f.featured DESC, f.name ASC"
    );
    $stmt->execute([$categoryId]);
} else {
    // Return all active items when no category filter
    $stmt = $pdo->prepare(
        "SELECT f.id, f.name, f.description, f.price, f.image_url, f.stock, f.featured,
                c.name AS category_name,
                COALESCE(AVG(r.rating), 0) AS avg_rating,
                COUNT(r.id) AS review_count
         FROM food_items f
         JOIN categories c ON c.id = f.category_id
         LEFT JOIN reviews r ON r.food_id = f.id
         WHERE f.status = 'active'
         GROUP BY f.id
         ORDER BY f.featured DESC, f.name ASC"
    );
    $stmt->execute();
}

$items = $stmt->fetchAll();
echo json_encode(['success' => true, 'data' => $items]);
