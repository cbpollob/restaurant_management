<?php
// ============================================================
//  register.php
// ============================================================
require_once __DIR__ . '/includes/session.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

if (isLoggedIn()) {
    header('Location: ' . BASE_URL . '/index.php');
    exit;
}

$jsonMode = !empty($_SERVER['HTTP_X_REQUESTED_WITH']);
$error    = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name     = trim($_POST['name']             ?? '');
    $email    = trim($_POST['email']            ?? '');
    $phone    = trim($_POST['phone']            ?? '');
    $password = $_POST['password']              ?? '';
    $confirm  = $_POST['confirm_password']      ?? '';

    if ($password !== $confirm) {
        $result = ['success' => false, 'message' => 'Passwords do not match.'];
    } else {
        $result = registerUser($name, $email, $phone, $password);
    }

    if ($jsonMode) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(array_merge($result, ['redirect' => BASE_URL . '/login.php']));
        exit;
    }

    if ($result['success']) {
        header('Location: ' . BASE_URL . '/login.php?registered=1');
        exit;
    }
    $error = $result['message'];
}
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Register | <?= sanitize(APP_NAME) ?></title>
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
    <h2>Create Account</h2>
    <p>Join thousands of food lovers. It's free!</p>

    <?php if ($error): ?>
      <div class="toast error" style="position:relative;animation:none;margin-bottom:1rem">
        <i class="toast-icon fas fa-times-circle"></i> <?= sanitize($error) ?>
      </div>
    <?php endif; ?>

    <form id="registerForm" action="<?= BASE_URL ?>/register.php" method="POST" novalidate>
      <div class="form-group">
        <label class="form-label" for="name">Full Name</label>
        <div class="input-group">
          <i class="input-icon fas fa-user"></i>
          <input type="text" id="name" name="name" class="form-control"
                 placeholder="Your full name" value="<?= sanitize($_POST['name'] ?? '') ?>" required>
        </div>
        <span class="field-error" id="name_error" style="color:#ff6b6b;font-size:.8rem;display:none"></span>
      </div>

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
        <label class="form-label" for="phone">Phone (optional)</label>
        <div class="input-group">
          <i class="input-icon fas fa-phone"></i>
          <input type="tel" id="phone" name="phone" class="form-control"
                 placeholder="+880 17XXXXXXXX" value="<?= sanitize($_POST['phone'] ?? '') ?>">
        </div>
        <span class="field-error" id="phone_error" style="color:#ff6b6b;font-size:.8rem;display:none"></span>
      </div>

      <div class="form-group">
        <label class="form-label" for="password">Password</label>
        <div class="input-group">
          <i class="input-icon fas fa-lock"></i>
          <input type="password" id="password" name="password" class="form-control"
                 placeholder="Min 8 characters" required>
          <button type="button" class="input-suffix-btn toggle-password" aria-label="Toggle password">
            <i class="fas fa-eye"></i>
          </button>
        </div>
        <span id="passwordStrength" style="font-size:.78rem;font-weight:600"></span>
        <span class="field-error" id="password_error" style="color:#ff6b6b;font-size:.8rem;display:none"></span>
      </div>

      <div class="form-group">
        <label class="form-label" for="confirm_password">Confirm Password</label>
        <div class="input-group">
          <i class="input-icon fas fa-lock"></i>
          <input type="password" id="confirm_password" name="confirm_password" class="form-control"
                 placeholder="Repeat your password" required>
          <button type="button" class="input-suffix-btn toggle-password" aria-label="Toggle password">
            <i class="fas fa-eye"></i>
          </button>
        </div>
        <span class="field-error" id="confirm_password_error" style="color:#ff6b6b;font-size:.8rem;display:none"></span>
      </div>

      <button type="submit" class="btn-primary btn-block btn-ripple">
        <i class="fas fa-user-plus"></i> Create Account
      </button>
    </form>

    <div class="auth-footer">
      <p>Already have an account? <a href="<?= BASE_URL ?>/login.php">Sign in</a></p>
    </div>
  </div>
</div>

<div id="toastContainer"></div>
<script src="<?= BASE_URL ?>/js/app.js"></script>
<script>window.APP_BASE = '<?= BASE_URL ?>';</script>
<script src="<?= BASE_URL ?>/js/auth.js"></script>
</body>
</html>
