<?php
// ============================================================
//  order-history.php
// ============================================================
require_once __DIR__ . '/includes/session.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

requireLogin();

$pageTitle = 'My Orders';
$pdo       = getDB();

$stmt = $pdo->prepare(
    "SELECT o.id, o.order_code, o.type, o.total, o.status, o.created_at,
            COUNT(oi.id) AS item_count
     FROM orders o
     LEFT JOIN order_items oi ON oi.order_id = o.id
     WHERE o.user_id = ?
     GROUP BY o.id
     ORDER BY o.created_at DESC"
);
$stmt->execute([$_SESSION['user_id']]);
$orders = $stmt->fetchAll();

$statusBadgeClass = [
    'placed'           => 'badge-info',
    'confirmed'        => 'badge-success',
    'preparing'        => 'badge-warning',
    'out_for_delivery' => 'badge-warning',
    'delivered'        => 'badge-success',
    'cancelled'        => 'badge-danger',
];

include __DIR__ . '/includes/header.php';
?>

<div class="page-header">
  <div class="container">
    <h1><i class="fas fa-receipt"></i> My Orders</h1>
    <div class="breadcrumb"><a href="<?= BASE_URL ?>/index.php">Home</a> / My Orders</div>
  </div>
</div>

<section class="orders-page">
  <div class="container">
    <?php if (empty($orders)): ?>
      <div class="empty-state">
        <i class="fas fa-receipt"></i>
        <h3>No orders yet</h3>
        <p>You haven't placed any orders. Start exploring our menu!</p>
        <a href="<?= BASE_URL ?>/menu.php" class="btn-primary">Browse Menu</a>
      </div>
    <?php else: ?>
      <?php foreach ($orders as $order): ?>
      <div class="order-card reveal">
        <div>
          <div class="order-code"><?= sanitize($order['order_code']) ?></div>
          <div style="font-size:.82rem;color:var(--text-muted);margin-top:.2rem">
            <?= date('d M Y, h:i A', strtotime($order['created_at'])) ?>
          </div>
        </div>
        <div>
          <span class="badge badge-secondary" style="text-transform:capitalize">
            <i class="fas <?= $order['type'] === 'dine-in' ? 'fa-utensils' : 'fa-motorcycle' ?>"></i>
            <?= sanitize($order['type']) ?>
          </span>
        </div>
        <div>
          <div style="font-size:.82rem;color:var(--text-muted)"><?= (int)$order['item_count'] ?> item(s)</div>
          <div style="font-weight:700;color:var(--accent);font-size:1.05rem">
            ৳<?= number_format($order['total'], 2) ?>
          </div>
        </div>
        <div>
          <span class="badge <?= $statusBadgeClass[$order['status']] ?? 'badge-secondary' ?>" style="text-transform:capitalize">
            <?= sanitize(str_replace('_', ' ', $order['status'])) ?>
          </span>
        </div>
        <div>
          <a href="<?= BASE_URL ?>/track-order.php?code=<?= urlencode($order['order_code']) ?>"
             class="btn-outline btn-sm">
            <i class="fas fa-map-marker-alt"></i> Track
          </a>
        </div>
      </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
