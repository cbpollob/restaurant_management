<?php
// ============================================================
//  admin/payments.php – Payment Log (Read-Only)
// ============================================================
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

requireAdmin();

$pdo = getDB();

$payments = $pdo->query(
    "SELECT p.id, p.method, p.amount, p.status, p.txn_ref, p.paid_at,
            o.order_code, u.name AS customer_name
     FROM payments p
     JOIN orders o ON o.id = p.order_id
     JOIN users u  ON u.id = o.user_id
     ORDER BY p.id DESC"
)->fetchAll();

$totalRevenue = $pdo->query("SELECT COALESCE(SUM(amount),0) FROM payments WHERE status='success'")->fetchColumn();

$statusBadge = ['pending' => 'badge-warning', 'success' => 'badge-success', 'failed' => 'badge-danger'];
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Payments | <?= sanitize(APP_NAME) ?> Admin</title>
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
        <div class="admin-topbar-title">Payment Log</div>
      </div>
      <div class="admin-topbar-right">
        <button class="theme-toggle" id="themeToggle"><i class="fas fa-moon" id="themeIcon"></i></button>
        <a href="<?= BASE_URL ?>/logout.php" class="btn-danger btn-sm"><i class="fas fa-sign-out-alt"></i> Logout</a>
      </div>
    </header>
    <div class="admin-content">

      <!-- KPI -->
      <div class="kpi-grid" style="grid-template-columns:repeat(3,1fr);max-width:700px;margin-bottom:1.5rem">
        <div class="kpi-card kpi-revenue">
          <div class="kpi-icon"><i class="fas fa-taka-sign"></i></div>
          <div class="kpi-info">
            <div class="kpi-value">৳<?= number_format($totalRevenue, 0) ?></div>
            <div class="kpi-label">Total Revenue</div>
          </div>
        </div>
        <div class="kpi-card kpi-orders">
          <div class="kpi-icon"><i class="fas fa-check-circle"></i></div>
          <div class="kpi-info">
            <div class="kpi-value"><?= count(array_filter($payments, fn($p) => $p['status']==='success')) ?></div>
            <div class="kpi-label">Successful</div>
          </div>
        </div>
        <div class="kpi-card kpi-bookings">
          <div class="kpi-icon"><i class="fas fa-times-circle"></i></div>
          <div class="kpi-info">
            <div class="kpi-value"><?= count(array_filter($payments, fn($p) => $p['status']==='failed')) ?></div>
            <div class="kpi-label">Failed</div>
          </div>
        </div>
      </div>

      <div class="admin-table-card">
        <div class="table-card-header">
          <h3>All Payments (<?= count($payments) ?>)</h3>
          <div class="table-search">
            <i class="fas fa-search" style="color:var(--text-muted)"></i>
            <input type="text" id="tableSearch" placeholder="Search…">
          </div>
        </div>
        <div class="admin-table-wrap">
          <table class="admin-table">
            <thead>
              <tr><th>#</th><th>Order</th><th>Customer</th><th>Method</th><th>Amount</th><th>Status</th><th>TXN Ref</th><th>Paid At</th></tr>
            </thead>
            <tbody>
              <?php foreach ($payments as $p): ?>
              <tr class="searchable-row">
                <td><?= (int)$p['id'] ?></td>
                <td><strong><?= sanitize($p['order_code']) ?></strong></td>
                <td><?= sanitize($p['customer_name']) ?></td>
                <td style="text-transform:uppercase">
                  <?php
                  $methodIcon = ['bkash' => 'fa-mobile-alt', 'nagad' => 'fa-wallet', 'card' => 'fa-credit-card'];
                  $icon = $methodIcon[$p['method']] ?? 'fa-money-bill';
                  ?>
                  <i class="fas <?= $icon ?>"></i> <?= sanitize($p['method']) ?>
                </td>
                <td style="font-weight:700;color:var(--accent)">৳<?= number_format($p['amount'], 2) ?></td>
                <td><span class="badge <?= $statusBadge[$p['status']] ?? 'badge-secondary' ?>"><?= sanitize($p['status']) ?></span></td>
                <td><code style="font-size:.78rem"><?= sanitize($p['txn_ref'] ?? '—') ?></code></td>
                <td><?= $p['paid_at'] ? date('d M Y, H:i', strtotime($p['paid_at'])) : '—' ?></td>
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
<script>window.APP_BASE = '<?= BASE_URL ?>';</script>
<script src="<?= BASE_URL ?>/js/admin.js"></script>
</body>
</html>
