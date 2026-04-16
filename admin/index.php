<?php
// ============================================================
//  admin/index.php – Dashboard
// ============================================================
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

requireAdmin();

$pdo = getDB();

// KPI stats
$totalOrders   = $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();
$totalRevenue  = $pdo->query("SELECT COALESCE(SUM(total),0) FROM orders WHERE status != 'cancelled'")->fetchColumn();
$totalUsers    = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'customer'")->fetchColumn();
$pendingBooks  = $pdo->query("SELECT COUNT(*) FROM bookings WHERE status = 'pending'")->fetchColumn();

// Orders per day – last 7 days
$chartRows = $pdo->query(
    "SELECT DATE(created_at) AS day, COUNT(*) AS cnt, COALESCE(SUM(total),0) AS rev
     FROM orders
     WHERE created_at >= CURDATE() - INTERVAL 6 DAY
     GROUP BY day ORDER BY day ASC"
)->fetchAll();

$chartLabels  = [];
$ordersData   = [];
$revenueData  = [];
// Fill all 7 days
for ($i = 6; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-{$i} days"));
    $chartLabels[] = date('D', strtotime($date));
    $found = false;
    foreach ($chartRows as $row) {
        if ($row['day'] === $date) {
            $ordersData[]  = (int)$row['cnt'];
            $revenueData[] = (float)$row['rev'];
            $found = true; break;
        }
    }
    if (!$found) { $ordersData[] = 0; $revenueData[] = 0.0; }
}

// Category order counts
$catRows = $pdo->query(
    "SELECT c.name, COUNT(oi.id) AS cnt
     FROM order_items oi
     JOIN food_items f ON f.id = oi.food_id
     JOIN categories c ON c.id = f.category_id
     GROUP BY c.id ORDER BY cnt DESC"
)->fetchAll();
$catNames = array_column($catRows, 'name');
$catData  = array_column($catRows, 'cnt');

// Recent 10 orders
$recentOrders = $pdo->query(
    "SELECT o.order_code, o.total, o.status, o.created_at, u.name AS customer_name
     FROM orders o JOIN users u ON u.id = o.user_id
     ORDER BY o.created_at DESC LIMIT 10"
)->fetchAll();

$pageTitle = 'Admin Dashboard';
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard | <?= sanitize(APP_NAME) ?> Admin</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="<?= BASE_URL ?>/css/style.css">
  <link rel="stylesheet" href="<?= BASE_URL ?>/css/admin.css">
</head>
<body class="admin-body">

<div class="admin-layout">

  <!-- Sidebar -->
  <?php include __DIR__ . '/sidebar.php'; ?>

  <!-- Main -->
  <main class="admin-main">
    <!-- Top Bar -->
    <header class="admin-topbar">
      <div class="admin-topbar-left">
        <button class="sidebar-toggle-btn" id="sidebarToggleBtn"><i class="fas fa-bars"></i></button>
        <div>
          <div class="admin-topbar-title">Dashboard</div>
          <div class="admin-topbar-subtitle"><?= date('l, d F Y') ?></div>
        </div>
      </div>
      <div class="admin-topbar-right">
        <button class="theme-toggle" id="themeToggle" aria-label="Toggle theme">
          <i class="fas fa-moon" id="themeIcon"></i>
        </button>
        <div class="admin-user-info">
          <div class="admin-avatar"><?= strtoupper(substr($_SESSION['user_name'], 0, 1)) ?></div>
          <div>
            <div class="admin-user-name"><?= sanitize($_SESSION['user_name']) ?></div>
            <div class="admin-user-role">Administrator</div>
          </div>
        </div>
        <a href="<?= BASE_URL ?>/logout.php" class="btn-danger btn-sm">
          <i class="fas fa-sign-out-alt"></i> Logout
        </a>
      </div>
    </header>

    <div class="admin-content">
      <!-- KPI Cards -->
      <div class="kpi-grid">
        <div class="kpi-card kpi-orders">
          <div class="kpi-icon"><i class="fas fa-shopping-bag"></i></div>
          <div class="kpi-info">
            <div class="kpi-value"><?= number_format($totalOrders) ?></div>
            <div class="kpi-label">Total Orders</div>
          </div>
        </div>
        <div class="kpi-card kpi-revenue">
          <div class="kpi-icon"><i class="fas fa-taka-sign"></i></div>
          <div class="kpi-info">
            <div class="kpi-value">৳<?= number_format($totalRevenue, 0) ?></div>
            <div class="kpi-label">Total Revenue</div>
          </div>
        </div>
        <div class="kpi-card kpi-users">
          <div class="kpi-icon"><i class="fas fa-users"></i></div>
          <div class="kpi-info">
            <div class="kpi-value"><?= number_format($totalUsers) ?></div>
            <div class="kpi-label">Customers</div>
          </div>
        </div>
        <div class="kpi-card kpi-bookings">
          <div class="kpi-icon"><i class="fas fa-calendar-check"></i></div>
          <div class="kpi-info">
            <div class="kpi-value"><?= number_format($pendingBooks) ?></div>
            <div class="kpi-label">Pending Bookings</div>
          </div>
        </div>
      </div>

      <!-- Charts -->
      <div class="charts-grid">
        <div class="chart-card">
          <div class="chart-card-header">
            <h3>Orders – Last 7 Days</h3>
            <span>Daily order count</span>
          </div>
          <div class="chart-container" style="height:240px">
            <canvas id="ordersChart"></canvas>
          </div>
        </div>
        <div class="chart-card">
          <div class="chart-card-header">
            <h3>By Category</h3>
          </div>
          <div class="chart-container" style="height:240px">
            <canvas id="categoryChart"></canvas>
          </div>
        </div>
      </div>
      <div class="charts-grid" style="grid-template-columns:1fr">
        <div class="chart-card">
          <div class="chart-card-header">
            <h3>Revenue – Last 7 Days</h3>
            <span>Daily revenue (৳)</span>
          </div>
          <div class="chart-container" style="height:200px">
            <canvas id="revenueChart"></canvas>
          </div>
        </div>
      </div>

      <!-- Recent Orders -->
      <div class="admin-table-card">
        <div class="table-card-header">
          <h3>Recent Orders</h3>
          <a href="<?= BASE_URL ?>/admin/orders.php" class="btn-outline btn-sm">View All</a>
        </div>
        <div class="admin-table-wrap">
          <table class="admin-table">
            <thead>
              <tr>
                <th>Order Code</th>
                <th>Customer</th>
                <th>Amount</th>
                <th>Status</th>
                <th>Date</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($recentOrders as $o):
                $bc = ['placed'=>'badge-info','confirmed'=>'badge-success','preparing'=>'badge-warning',
                       'out_for_delivery'=>'badge-warning','delivered'=>'badge-success','cancelled'=>'badge-danger'];
              ?>
              <tr class="searchable-row">
                <td><strong><?= sanitize($o['order_code']) ?></strong></td>
                <td><?= sanitize($o['customer_name']) ?></td>
                <td>৳<?= number_format($o['total'], 2) ?></td>
                <td><span class="badge <?= $bc[$o['status']] ?? 'badge-secondary' ?>" style="text-transform:capitalize">
                  <?= sanitize(str_replace('_',' ',$o['status'])) ?>
                </span></td>
                <td><?= date('d M, H:i', strtotime($o['created_at'])) ?></td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>

    </div><!-- admin-content -->
  </main>
</div>

<div id="toastContainer"></div>

<script>
window.APP_BASE      = '<?= BASE_URL ?>';
window._chartLabels  = <?= json_encode($chartLabels) ?>;
window._ordersData   = <?= json_encode($ordersData)  ?>;
window._revenueData  = <?= json_encode($revenueData) ?>;
window._catNames     = <?= json_encode($catNames)    ?>;
window._catData      = <?= json_encode($catData)     ?>;
</script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script src="<?= BASE_URL ?>/js/app.js"></script>
<script src="<?= BASE_URL ?>/js/admin.js"></script>
</body>
</html>
