<?php
// ============================================================
//  API: Simulate payment  –  POST {order_id, method, amount}
// ============================================================
require_once __DIR__ . '/../../includes/session.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/functions.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, 'Method not allowed.');
}
if (!isLoggedIn()) {
    jsonResponse(false, 'Unauthorized.');
}

$orderId = isset($_POST['order_id']) ? (int) $_POST['order_id'] : 0;
$method  = trim($_POST['method']  ?? '');
$amount  = isset($_POST['amount']) ? (float) $_POST['amount'] : 0.0;

if ($orderId <= 0 || !in_array($method, ['bkash', 'nagad', 'card']) || $amount <= 0) {
    jsonResponse(false, 'Invalid payment details.');
}

$pdo = getDB();

// Verify the order belongs to this user
$orderStmt = $pdo->prepare("SELECT id, total, status FROM orders WHERE id = ? AND user_id = ? LIMIT 1");
$orderStmt->execute([$orderId, $_SESSION['user_id']]);
$order = $orderStmt->fetch();

if (!$order) {
    jsonResponse(false, 'Order not found.');
}

// Simulate: 90% success rate
$success   = (mt_rand(1, 10) > 1);
$status    = $success ? 'success' : 'failed';
$txnRef    = 'TXN-' . strtoupper(substr(md5(uniqid('', true)), 0, 10));
$paidAt    = $success ? date('Y-m-d H:i:s') : null;
$newStatus = $success ? 'confirmed' : 'placed';

// Insert payment record
$ins = $pdo->prepare(
    "INSERT INTO payments (order_id, method, amount, status, txn_ref, paid_at)
     VALUES (?, ?, ?, ?, ?, ?)"
);
$ins->execute([$orderId, $method, $amount, $status, $txnRef, $paidAt]);

// Update order status
$pdo->prepare("UPDATE orders SET status = ? WHERE id = ?")
    ->execute([$newStatus, $orderId]);

jsonResponse($success, $success ? 'Payment successful!' : 'Payment failed. Please try again.', [
    'txn_ref'    => $txnRef,
    'order_id'   => $orderId,
    'pay_status' => $status,
]);
