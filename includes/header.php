<?php
// ============================================================
//  Shared HTML <head> + Navbar – includes/header.php
// ============================================================
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/session.php';
require_once __DIR__ . '/functions.php';

$cartCount = getCartCount();
$pageTitle = $pageTitle ?? APP_NAME;
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="<?= sanitize(APP_NAME) ?> – Fine dining, fast delivery, easy booking.">
  <title><?= sanitize($pageTitle) ?> | <?= sanitize(APP_NAME) ?></title>

  <!-- Google Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

  <!-- App CSS -->
  <link rel="stylesheet" href="<?= BASE_URL ?>/css/style.css">
  <link rel="stylesheet" href="<?= BASE_URL ?>/css/animations.css">
</head>
<body>

<!-- ===== STICKY NAVBAR ===== -->
<nav class="navbar" id="mainNavbar">
  <div class="nav-container">

    <!-- Logo -->
    <a href="<?= BASE_URL ?>/index.php" class="nav-logo">
      <i class="fas fa-utensils"></i> <?= sanitize(APP_NAME) ?>
    </a>

    <!-- Hamburger (mobile) -->
    <button class="nav-toggle" id="navToggle" aria-label="Toggle navigation">
      <span></span><span></span><span></span>
    </button>

    <!-- Nav Links -->
    <ul class="nav-links" id="navLinks">
      <li><a href="<?= BASE_URL ?>/index.php">Home</a></li>
      <li><a href="<?= BASE_URL ?>/menu.php">Menu</a></li>
      <li><a href="<?= BASE_URL ?>/booking.php">Booking</a></li>

      <?php if (isLoggedIn()): ?>
        <li><a href="<?= BASE_URL ?>/order-history.php">My Orders</a></li>
        <li><a href="<?= BASE_URL ?>/wishlist.php"><i class="fas fa-heart"></i> Wishlist</a></li>
        <?php if (isAdmin()): ?>
          <li><a href="<?= BASE_URL ?>/admin/index.php" class="nav-admin-link"><i class="fas fa-shield-alt"></i> Admin</a></li>
        <?php endif; ?>
        <li><a href="<?= BASE_URL ?>/logout.php" class="btn-nav-logout">Logout</a></li>
      <?php else: ?>
        <li><a href="<?= BASE_URL ?>/login.php">Login</a></li>
        <li><a href="<?= BASE_URL ?>/register.php" class="btn-nav-register">Register</a></li>
      <?php endif; ?>
    </ul>

    <!-- Right controls -->
    <div class="nav-controls">
      <!-- Dark mode toggle -->
      <button class="theme-toggle" id="themeToggle" aria-label="Toggle dark mode">
        <i class="fas fa-moon" id="themeIcon"></i>
      </button>

      <!-- Cart -->
      <button class="cart-btn" id="cartToggle" aria-label="Open cart">
        <i class="fas fa-shopping-cart"></i>
        <span class="cart-badge" id="cartBadge"><?= $cartCount > 0 ? $cartCount : '' ?></span>
      </button>
    </div>

  </div>
</nav>
<!-- End Navbar -->
