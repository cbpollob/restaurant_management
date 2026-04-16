<?php
// ============================================================
//  API: Create a table booking  –  POST
// ============================================================
require_once __DIR__ . '/../../includes/session.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/functions.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, 'Method not allowed.');
}

// Must be logged in
if (!isLoggedIn()) {
    jsonResponse(false, 'Please log in to book a table.');
}

$bookingDate = trim($_POST['booking_date'] ?? '');
$timeSlot    = trim($_POST['time_slot']    ?? '');
$guests      = isset($_POST['guests']) ? (int) $_POST['guests'] : 2;
$notes       = trim($_POST['notes']        ?? '');

if (!$bookingDate || !$timeSlot) {
    jsonResponse(false, 'Date and time slot are required.');
}
if ($guests < 1 || $guests > 20) {
    jsonResponse(false, 'Guest count must be between 1 and 20.');
}
// Basic date validation
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $bookingDate) || strtotime($bookingDate) < strtotime('today')) {
    jsonResponse(false, 'Invalid booking date. Must be today or a future date.');
}

$pdo = getDB();

// Check slot availability
$check = $pdo->prepare(
    "SELECT id FROM bookings WHERE booking_date = ? AND time_slot = ? AND status != 'cancelled' LIMIT 1"
);
$check->execute([$bookingDate, $timeSlot]);
if ($check->fetch()) {
    jsonResponse(false, 'Sorry, that time slot is already booked. Please choose another.');
}

$ins = $pdo->prepare(
    "INSERT INTO bookings (user_id, booking_date, time_slot, guests, notes, status)
     VALUES (?, ?, ?, ?, ?, 'pending')"
);
$ins->execute([$_SESSION['user_id'], $bookingDate, $timeSlot, $guests, $notes]);
$bookingId = $pdo->lastInsertId();

jsonResponse(true, 'Booking created successfully! We will confirm shortly.', [
    'booking_id' => (int) $bookingId,
]);
