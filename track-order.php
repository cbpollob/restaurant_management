<?php
// ============================================================
//  track-order.php
// ============================================================
require_once __DIR__ . '/includes/session.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

$pageTitle  = 'Track Order';
$orderCode  = trim($_GET['code'] ?? '');
$order      = null;
$orderItems = [];
$error      = '';

if ($orderCode !== '') {
    $pdo   = getDB();
    $stmt  = $pdo->prepare(
        "SELECT o.*, u.name AS customer_name
         FROM orders o JOIN users u ON u.id = o.user_id
         WHERE o.order_code = ? LIMIT 1"
    );
    $stmt->execute([$orderCode]);
    $order = $stmt->fetch();

    if ($order) {
        $iStmt = $pdo->prepare(
            "SELECT oi.quantity, oi.unit_price, oi.line_total, f.name AS food_name, f.image_url
             FROM order_items oi JOIN food_items f ON f.id = oi.food_id
             WHERE oi.order_id = ?"
        );
        $iStmt->execute([$order['id']]);
        $orderItems = $iStmt->fetchAll();
    } else {
        $error = 'Order not found. Please check your order code.';
    }
}

$statusSteps = [
    'placed'           => ['label' => 'Order Placed',      'icon' => 'fa-check'],
    'confirmed'        => ['label' => 'Confirmed',          'icon' => 'fa-thumbs-up'],
    'preparing'        => ['label' => 'Preparing',          'icon' => 'fa-fire'],
    'out_for_delivery' => ['label' => 'Out for Delivery',   'icon' => 'fa-motorcycle'],
    'delivered'        => ['label' => 'Delivered',          'icon' => 'fa-flag-checkered'],
];
$statusOrder = array_keys($statusSteps);
$currentIdx  = $order ? array_search($order['status'], $statusOrder) : -1;

include __DIR__ . '/includes/header.php';
?>

<div class="page-header">
  <div class="container">
    <h1><i class="fas fa-map-marker-alt"></i> Track Order</h1>
    <div class="breadcrumb">
      <a href="<?= BASE_URL ?>/index.php">Home</a> /
      <a href="<?= BASE_URL ?>/order-history.php">My Orders</a> / Track
    </div>
  </div>
</div>

<section class="track-page">
  <div class="container" style="max-width:860px">

    <!-- Search by order code -->
    <div class="checkout-card" style="margin-bottom:2rem">
      <h3><i class="fas fa-search"></i> Enter Order Code</h3>
      <form method="GET" style="display:flex;gap:.75rem;flex-wrap:wrap">
        <div class="input-group" style="flex:1;min-width:220px">
          <i class="input-icon fas fa-barcode"></i>
          <input type="text" name="code" class="form-control"
                 placeholder="e.g. ORD-ALPHA001"
                 value="<?= sanitize($orderCode) ?>">
        </div>
        <button type="submit" class="btn-primary btn-ripple">
          <i class="fas fa-search"></i> Track
        </button>
      </form>
    </div>

    <?php if ($error): ?>
      <div class="empty-state">
        <i class="fas fa-search"></i>
        <h3>Order Not Found</h3>
        <p><?= sanitize($error) ?></p>
      </div>
    <?php elseif ($order): ?>

      <?php if ($order['status'] === 'cancelled'): ?>
      <div style="background:#ffebee;border:1px solid #ef9a9a;border-radius:12px;padding:1.25rem;margin-bottom:2rem;display:flex;gap:.75rem;align-items:center;color:#c62828">
        <i class="fas fa-times-circle fa-lg"></i>
        <div>
          <strong>Order Cancelled</strong>
          <div style="font-size:.85rem">This order was cancelled. Contact us for help.</div>
        </div>
      </div>
      <?php else: ?>
      <!-- Status Progress Bar -->
      <div class="checkout-card" style="margin-bottom:2rem">
        <h3 style="margin-bottom:1.75rem">Order Status</h3>
        <div class="status-steps">
          <?php foreach ($statusSteps as $statusKey => $step):
            $idx       = array_search($statusKey, $statusOrder);
            $completed = $currentIdx !== false && $idx < $currentIdx;
            $active    = $idx === $currentIdx;
          ?>
          <div class="status-step <?= $completed ? 'completed' : '' ?> <?= $active ? 'active' : '' ?>">
            <div class="step-circle">
              <?php if ($completed): ?>
                <i class="fas fa-check"></i>
              <?php else: ?>
                <i class="fas <?= $step['icon'] ?>"></i>
              <?php endif; ?>
            </div>
            <div class="step-label"><?= $step['label'] ?></div>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
      <?php endif; ?>

      <!-- Order Details -->
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:1.5rem;margin-bottom:1.5rem">
        <div class="checkout-card">
          <h3>Order Info</h3>
          <table style="width:100%;font-size:.88rem">
            <tr><td style="color:var(--text-muted);padding:.35rem 0">Order Code</td><td><strong><?= sanitize($order['order_code']) ?></strong></td></tr>
            <tr><td style="color:var(--text-muted);padding:.35rem 0">Date</td><td><?= date('d M Y, h:i A', strtotime($order['created_at'])) ?></td></tr>
            <tr><td style="color:var(--text-muted);padding:.35rem 0">Type</td><td style="text-transform:capitalize"><?= sanitize($order['type']) ?></td></tr>
            <tr><td style="color:var(--text-muted);padding:.35rem 0">Status</td>
              <td><span class="badge badge-info" style="text-transform:capitalize"><?= sanitize(str_replace('_',' ',$order['status'])) ?></span></td></tr>
            <?php if ($order['address']): ?>
            <tr><td style="color:var(--text-muted);padding:.35rem 0">Address</td><td><?= sanitize($order['address']) ?></td></tr>
            <?php endif; ?>
          </table>
        </div>
        <div class="checkout-card">
          <h3>Payment</h3>
          <table style="width:100%;font-size:.88rem">
            <tr><td style="color:var(--text-muted);padding:.35rem 0">Subtotal</td><td>৳<?= number_format($order['subtotal'], 2) ?></td></tr>
            <?php if ($order['discount'] > 0): ?>
            <tr><td style="color:var(--text-muted);padding:.35rem 0">Discount</td><td style="color:var(--success)">- ৳<?= number_format($order['discount'], 2) ?></td></tr>
            <?php endif; ?>
            <tr><td style="color:var(--text-muted);padding:.35rem 0;font-weight:700">Total</td>
              <td style="font-weight:700;color:var(--accent);font-size:1.1rem">৳<?= number_format($order['total'], 2) ?></td></tr>
            <?php if ($order['promo_code']): ?>
            <tr><td style="color:var(--text-muted);padding:.35rem 0">Promo</td><td><?= sanitize($order['promo_code']) ?></td></tr>
            <?php endif; ?>
          </table>
        </div>
      </div>

      <!-- Order Items -->
      <div class="checkout-card">
        <h3 style="margin-bottom:1.25rem">Order Items</h3>
        <?php foreach ($orderItems as $oi): ?>
        <div style="display:flex;align-items:center;gap:1rem;padding:.75rem 0;border-bottom:1px solid var(--border-color)">
          <img src="<?= sanitize($oi['image_url'] ?? '') ?>" alt=""
               style="width:54px;height:54px;border-radius:8px;object-fit:cover;flex-shrink:0"
               onerror="this.style.display='none'">
          <div style="flex:1">
            <div style="font-weight:600"><?= sanitize($oi['food_name']) ?></div>
            <div style="font-size:.82rem;color:var(--text-muted)">
              ৳<?= number_format($oi['unit_price'], 2) ?> × <?= (int)$oi['quantity'] ?>
            </div>
          </div>
          <div style="font-weight:700;color:var(--accent)">
            ৳<?= number_format($oi['line_total'], 2) ?>
          </div>
        </div>
        <?php endforeach; ?>
      </div>

    <?php elseif ($orderCode !== ''): ?>
      <!-- Already handled by $error above -->
    <?php else: ?>
      <div class="empty-state">
        <i class="fas fa-search"></i>
        <h3>Enter Your Order Code</h3>
        <p>You can find your order code in your order confirmation email or <a href="<?= BASE_URL ?>/order-history.php">order history</a>.</p>
      </div>
    <?php endif; ?>

  </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
