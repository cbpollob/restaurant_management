<?php
// ============================================================
//  admin/orders.php – Orders Management
// ============================================================
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

requireAdmin();

$pdo = getDB();

// Handle status update via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_id'], $_POST['status'])) {
    $validStatuses = ['placed','confirmed','preparing','out_for_delivery','delivered','cancelled'];
    $newStatus = in_array($_POST['status'], $validStatuses) ? $_POST['status'] : null;
    if ($newStatus) {
        $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?")->execute([$newStatus, (int)$_POST['order_id']]);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['success' => true]);
        exit;
    }
}

// Filter
$filterStatus = $_GET['status'] ?? '';
$validFilter  = ['','placed','confirmed','preparing','out_for_delivery','delivered','cancelled'];
if (!in_array($filterStatus, $validFilter)) $filterStatus = '';

if ($filterStatus !== '') {
    $stmt = $pdo->prepare(
        "SELECT o.*, u.name AS customer_name FROM orders o JOIN users u ON u.id = o.user_id
         WHERE o.status = ? ORDER BY o.created_at DESC"
    );
    $stmt->execute([$filterStatus]);
} else {
    $stmt = $pdo->query(
        "SELECT o.*, u.name AS customer_name FROM orders o JOIN users u ON u.id = o.user_id
         ORDER BY o.created_at DESC"
    );
}
$orders = $stmt->fetchAll();

$statusBadge = ['placed'=>'badge-info','confirmed'=>'badge-success','preparing'=>'badge-warning',
                'out_for_delivery'=>'badge-warning','delivered'=>'badge-success','cancelled'=>'badge-danger'];
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Orders | <?= sanitize(APP_NAME) ?> Admin</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="<?= BASE_URL ?>/css/style.css">
  <link rel="stylesheet" href="<?= BASE_URL ?>/css/admin.css">
</head>
<body class="admin-body">
<div class="admin-layout">
  <?php include __DIR__ . '/sidebar.php'; ?>
  <main class="admin-main">
    <header class="admin-topbar">
      <div class="admin-topbar-left">
        <button class="sidebar-toggle-btn" id="sidebarToggleBtn"><i class="fas fa-bars"></i></button>
        <div class="admin-topbar-title">Orders</div>
      </div>
      <div class="admin-topbar-right">
        <button class="theme-toggle" id="themeToggle"><i class="fas fa-moon" id="themeIcon"></i></button>
        <a href="<?= BASE_URL ?>/logout.php" class="btn-danger btn-sm"><i class="fas fa-sign-out-alt"></i> Logout</a>
      </div>
    </header>

    <div class="admin-content">
      <!-- Filters -->
      <div class="filter-row">
        <span style="font-weight:600;font-size:.9rem">Filter:</span>
        <?php
        $statuses = ['' => 'All', 'placed' => 'Placed', 'confirmed' => 'Confirmed',
                     'preparing' => 'Preparing', 'out_for_delivery' => 'Out for Delivery',
                     'delivered' => 'Delivered', 'cancelled' => 'Cancelled'];
        foreach ($statuses as $key => $label):
        ?>
        <a href="orders.php<?= $key !== '' ? '?status='.$key : '' ?>"
           class="badge <?= $filterStatus === $key ? 'badge-success' : 'badge-secondary' ?>"
           style="text-decoration:none;padding:.3rem .8rem;font-size:.8rem;cursor:pointer">
          <?= $label ?>
        </a>
        <?php endforeach; ?>
      </div>

      <div class="admin-table-card">
        <div class="table-card-header">
          <h3>Orders (<?= count($orders) ?>)</h3>
          <div class="table-search">
            <i class="fas fa-search" style="color:var(--text-muted)"></i>
            <input type="text" id="tableSearch" placeholder="Search…">
          </div>
        </div>
        <div class="admin-table-wrap">
          <table class="admin-table">
            <thead>
              <tr><th>Order Code</th><th>Customer</th><th>Type</th><th>Total</th><th>Promo</th><th>Status</th><th>Date</th><th>Update Status</th></tr>
            </thead>
            <tbody>
              <?php foreach ($orders as $o): ?>
              <tr class="searchable-row">
                <td><strong><?= sanitize($o['order_code']) ?></strong></td>
                <td><?= sanitize($o['customer_name']) ?></td>
                <td style="text-transform:capitalize"><?= sanitize($o['type']) ?></td>
                <td>৳<?= number_format($o['total'], 2) ?></td>
                <td><?= $o['promo_code'] ? '<span class="badge badge-info">'.sanitize($o['promo_code']).'</span>' : '—' ?></td>
                <td><span class="badge <?= $statusBadge[$o['status']] ?? 'badge-secondary' ?>" style="text-transform:capitalize">
                  <?= sanitize(str_replace('_',' ',$o['status'])) ?>
                </span></td>
                <td><?= date('d M, H:i', strtotime($o['created_at'])) ?></td>
                <td>
                  <select class="status-select order-status-select" data-order-id="<?= (int)$o['id'] ?>">
                    <?php foreach (array_keys(array_slice($statuses, 1)) as $st): ?>
                    <option value="<?= $st ?>" <?= $o['status'] === $st ? 'selected' : '' ?>><?= $statuses[$st] ?></option>
                    <?php endforeach; ?>
                  </select>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </main>
</div>
<div id="toastContainer"></div>
<script src="<?= BASE_URL ?>/js/app.js"></script>
<script>
window.APP_BASE = '<?= BASE_URL ?>';
// Override order status update URL for this page
document.querySelectorAll('.order-status-select').forEach(sel => {
  sel.addEventListener('change', async () => {
    const fd = new FormData();
    fd.append('order_id', sel.dataset.orderId);
    fd.append('status',   sel.value);
    const res = await fetch('orders.php', { method: 'POST', body: fd });
    const data = await res.json();
    if (data.success) showToast('Status updated.', 'success');
    else showToast('Update failed.', 'error');
  });
});
</script>
<script src="<?= BASE_URL ?>/js/admin.js"></script>
</body>
</html>
