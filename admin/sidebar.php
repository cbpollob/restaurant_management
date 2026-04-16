<?php
// Shared admin sidebar partial – included by all admin pages
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<aside class="admin-sidebar" id="adminSidebar">
  <div class="admin-sidebar-logo">
    <div class="logo-icon"><i class="fas fa-utensils"></i></div>
    <div class="logo-text">
      <?= sanitize(APP_NAME) ?>
      <small>Admin Panel</small>
    </div>
  </div>

  <nav class="admin-nav">
    <div class="nav-section-title">Main</div>
    <a href="<?= BASE_URL ?>/admin/index.php"    class="<?= $currentPage === 'index.php'    ? 'active' : '' ?>">
      <i class="fas fa-tachometer-alt"></i> Dashboard
    </a>
    <a href="<?= BASE_URL ?>/admin/orders.php"   class="<?= $currentPage === 'orders.php'   ? 'active' : '' ?>">
      <i class="fas fa-shopping-bag"></i> Orders
    </a>
    <a href="<?= BASE_URL ?>/admin/foods.php"    class="<?= $currentPage === 'foods.php'    ? 'active' : '' ?>">
      <i class="fas fa-utensils"></i> Foods
    </a>
    <div class="nav-section-title" style="margin-top:.75rem">Management</div>
    <a href="<?= BASE_URL ?>/admin/users.php"    class="<?= $currentPage === 'users.php'    ? 'active' : '' ?>">
      <i class="fas fa-users"></i> Users
    </a>
    <a href="<?= BASE_URL ?>/admin/bookings.php" class="<?= $currentPage === 'bookings.php' ? 'active' : '' ?>">
      <i class="fas fa-calendar-check"></i> Bookings
    </a>
    <a href="<?= BASE_URL ?>/admin/payments.php" class="<?= $currentPage === 'payments.php' ? 'active' : '' ?>">
      <i class="fas fa-credit-card"></i> Payments
    </a>
  </nav>

  <div class="admin-sidebar-footer">
    <a href="<?= BASE_URL ?>/index.php">
      <i class="fas fa-external-link-alt"></i> View Site
    </a>
  </div>
</aside>
