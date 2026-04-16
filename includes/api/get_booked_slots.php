<?php
// ============================================================
//  API: Get booked time slots for a date  –  GET ?date=YYYY-MM-DD
// ============================================================
require_once __DIR__ . '/../../includes/session.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/functions.php';

header('Content-Type: application/json; charset=utf-8');

$date = trim($_GET['date'] ?? '');

if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
    jsonResponse(false, 'Invalid date format. Use YYYY-MM-DD.');
}

$pdo  = getDB();
$stmt = $pdo->prepare(
    "SELECT time_slot FROM bookings
     WHERE booking_date = ? AND status != 'cancelled'"
);
$stmt->execute([$date]);
$rows = $stmt->fetchAll();

$slots = array_column($rows, 'time_slot');

echo json_encode(['success' => true, 'booked_slots' => $slots]);
