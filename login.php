<?php
// ============================================================
//  login.php
// ============================================================
require_once __DIR__ . '/includes/session.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

// Already logged in → redirect
if (isLoggedIn()) {
    header('Location: ' . BASE_URL . '/index.php');
    exit;
}

$jsonMode = !empty($_SERVER['HTTP_X_REQUESTED_WITH']);
$error    = '';
$success  = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email']    ?? '');
    $password = trim($_POST['password'] ?? '');
    $result   = loginUser($email, $password);

    if ($jsonMode) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(array_merge($result, [
            'redirect' => $_GET['redirect'] ?? BASE_URL . '/index.php',
        ]));
        exit;
    }

    if ($result['success']) {
        header('Location: ' . ($_GET['redirect'] ?? BASE_URL . '/index.php'));
        exit;
    }
    $error = $result['message'];
}

$pageTitle = 'Login';
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login | <?= sanitize(APP_NAME) ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="<?= BASE_URL ?>/css/style.css">
  <link rel="stylesheet" href="<?= BASE_URL ?>/css/animations.css">
</head>
<body>
<div class="auth-page">
  <div class="auth-card">
    <div style="text-align:center;margin-bottom:1.5rem">
      <a href="<?= BASE_URL ?>/index.php" style="font-size:1.75rem;font-weight:800;color:var(--accent);text-decoration:none">
        <i class="fas fa-utensils"></i> <?= sanitize(APP_NAME) ?>
      </a>
    </div>
    <h2>Welcome Back!</h2>
    <p>Sign in to order, book and manage your account.</p>

    <?php if ($error): ?>
      <div class="toast error" style="position:relative;animation:none;margin-bottom:1rem">
        <i class="toast-icon fas fa-times-circle"></i> <?= sanitize($error) ?>
      </div>
    <?php endif; ?>

    <form id="loginForm" action="<?= BASE_URL ?>/login.php<?= isset($_GET['redirect']) ? '?redirect='.urlencode($_GET['redirect']) : '' ?>" method="POST" novalidate>
      <div class="form-group">
        <label class="form-label" for="email">Email Address</label>
        <div class="input-group">
          <i class="input-icon fas fa-envelope"></i>
          <input type="email" id="email" name="email" class="form-control"
                 placeholder="you@example.com" value="<?= sanitize($_POST['email'] ?? '') ?>" required>
        </div>
        <span class="field-error" id="email_error" style="color:#ff6b6b;font-size:.8rem;display:none"></span>
      </div>

      <div class="form-group">
        <label class="form-label" for="password">Password</label>
        <div class="input-group">
          <i class="input-icon fas fa-lock"></i>
          <input type="password" id="password" name="password" class="form-control"
                 placeholder="Enter your password" required>
          <button type="button" class="input-suffix-btn toggle-password" aria-label="Toggle password visibility">
            <i class="fas fa-eye"></i>
          </button>
        </div>
        <span class="field-error" id="password_error" style="color:#ff6b6b;font-size:.8rem;display:none"></span>
      </div>

      <button type="submit" class="btn-primary btn-block btn-ripple" style="margin-top:.5rem">
        <i class="fas fa-sign-in-alt"></i> Sign In
      </button>
    </form>

    <div class="auth-footer">
      <p>Don't have an account? <a href="<?= BASE_URL ?>/register.php">Create one</a></p>
      <p style="margin-top:.5rem;font-size:.78rem;opacity:.7">
        Demo – Admin: admin@restaurant.com / User@123
      </p>
    </div>
  </div>
</div>

<div id="toastContainer"></div>
<script src="<?= BASE_URL ?>/js/app.js"></script>
<script>window.APP_BASE = '<?= BASE_URL ?>';</script>
<script src="<?= BASE_URL ?>/js/auth.js"></script>
</body>
</html>
