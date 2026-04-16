<?php
// ============================================================
//  admin/bookings.php
// ============================================================
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

requireAdmin();

$pdo = getDB();
$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $bookingId = (int)($_POST['booking_id'] ?? 0);
    $status    = in_array($_POST['status'] ?? '', ['pending','confirmed','cancelled']) ? $_POST['status'] : null;
    if ($bookingId > 0 && $status) {
        $pdo->prepare("UPDATE bookings SET status = ? WHERE id = ?")->execute([$status, $bookingId]);
        $msg = 'Booking status updated.';
    }
}

$filter = in_array($_GET['status'] ?? '', ['pending','confirmed','cancelled']) ? $_GET['status'] : '';
if ($filter) {
    $stmt = $pdo->prepare(
        "SELECT b.*, u.name AS user_name, u.email AS user_email
         FROM bookings b JOIN users u ON u.id = b.user_id
         WHERE b.status = ? ORDER BY b.booking_date ASC, b.time_slot ASC"
    );
    $stmt->execute([$filter]);
} else {
    $stmt = $pdo->query(
        "SELECT b.*, u.name AS user_name, u.email AS user_email
         FROM bookings b JOIN users u ON u.id = b.user_id
         ORDER BY b.booking_date ASC, b.time_slot ASC"
    );
}
$bookings = $stmt->fetchAll();

$statusBadge = ['pending' => 'badge-warning', 'confirmed' => 'badge-success', 'cancelled' => 'badge-danger'];
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Bookings | <?= sanitize(APP_NAME) ?> Admin</title>
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
        <div class="admin-topbar-title">Table Bookings</div>
      </div>
      <div class="admin-topbar-right">
        <button class="theme-toggle" id="themeToggle"><i class="fas fa-moon" id="themeIcon"></i></button>
        <a href="<?= BASE_URL ?>/logout.php" class="btn-danger btn-sm"><i class="fas fa-sign-out-alt"></i> Logout</a>
      </div>
    </header>
    <div class="admin-content">
      <?php if ($msg): ?>
        <div class="toast success" style="position:relative;animation:none;margin-bottom:1rem">
          <i class="fas fa-check-circle toast-icon"></i> <?= sanitize($msg) ?>
        </div>
      <?php endif; ?>

      <!-- Filters -->
      <div class="filter-row">
        <span style="font-weight:600;font-size:.9rem">Filter:</span>
        <?php foreach (['' => 'All', 'pending' => 'Pending', 'confirmed' => 'Confirmed', 'cancelled' => 'Cancelled'] as $k => $l): ?>
        <a href="bookings.php<?= $k ? '?status='.$k : '' ?>"
           class="badge <?= $filter === $k ? 'badge-success' : 'badge-secondary' ?>"
           style="text-decoration:none;padding:.3rem .8rem;font-size:.8rem;cursor:pointer"><?= $l ?></a>
        <?php endforeach; ?>
      </div>

      <div class="admin-table-card">
        <div class="table-card-header">
          <h3>Bookings (<?= count($bookings) ?>)</h3>
          <div class="table-search">
            <i class="fas fa-search" style="color:var(--text-muted)"></i>
            <input type="text" id="tableSearch" placeholder="Search…">
          </div>
        </div>
        <div class="admin-table-wrap">
          <table class="admin-table">
            <thead>
              <tr><th>#</th><th>Customer</th><th>Date</th><th>Time</th><th>Guests</th><th>Notes</th><th>Status</th><th>Update</th></tr>
            </thead>
            <tbody>
              <?php foreach ($bookings as $b): ?>
              <tr class="searchable-row">
                <td><?= (int)$b['id'] ?></td>
                <td>
                  <div style="font-weight:600"><?= sanitize($b['user_name']) ?></div>
                  <div style="font-size:.78rem;color:var(--text-muted)"><?= sanitize($b['user_email']) ?></div>
                </td>
                <td><?= date('d M Y', strtotime($b['booking_date'])) ?></td>
                <td><?= sanitize($b['time_slot']) ?></td>
                <td><?= (int)$b['guests'] ?></td>
                <td style="max-width:200px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">
                  <?= $b['notes'] ? sanitize($b['notes']) : '—' ?>
                </td>
                <td><span class="badge <?= $statusBadge[$b['status']] ?? 'badge-secondary' ?>"><?= sanitize($b['status']) ?></span></td>
                <td>
                  <form method="POST" style="display:flex;gap:.4rem;align-items:center">
                    <input type="hidden" name="booking_id" value="<?= (int)$b['id'] ?>">
                    <select name="status" class="status-select">
                      <option value="pending"   <?= $b['status']==='pending'   ? 'selected' : '' ?>>Pending</option>
                      <option value="confirmed" <?= $b['status']==='confirmed' ? 'selected' : '' ?>>Confirmed</option>
                      <option value="cancelled" <?= $b['status']==='cancelled' ? 'selected' : '' ?>>Cancelled</option>
                    </select>
                    <button type="submit" class="btn-icon btn-icon-view" title="Save"><i class="fas fa-check"></i></button>
                  </form>
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
<script>window.APP_BASE = '<?= BASE_URL ?>';</script>
<script src="<?= BASE_URL ?>/js/admin.js"></script>
</body>
</html>
