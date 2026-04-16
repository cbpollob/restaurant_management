<?php
// ============================================================
//  API: Search food items  –  GET ?q=searchterm
// ============================================================
require_once __DIR__ . '/../../includes/session.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/functions.php';

header('Content-Type: application/json; charset=utf-8');

$q = trim($_GET['q'] ?? '');

if ($q === '') {
    echo json_encode(['success' => true, 'data' => []]);
    exit;
}

$pdo  = getDB();
$like = '%' . $q . '%';
$stmt = $pdo->prepare(
    "SELECT f.id, f.name, f.description, f.price, f.image_url, f.stock, f.featured,
            c.name AS category_name,
            COALESCE(AVG(r.rating), 0) AS avg_rating,
            COUNT(r.id) AS review_count
     FROM food_items f
     JOIN categories c ON c.id = f.category_id
     LEFT JOIN reviews r ON r.food_id = f.id
     WHERE f.status = 'active'
       AND (f.name LIKE ? OR f.description LIKE ?)
     GROUP BY f.id
     ORDER BY f.featured DESC, f.name ASC
     LIMIT 30"
);
$stmt->execute([$like, $like]);
$items = $stmt->fetchAll();

echo json_encode(['success' => true, 'data' => $items]);
