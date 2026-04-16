<?php
// ============================================================
//  admin/users.php
// ============================================================
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

requireAdmin();

$pdo = getDB();
$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $userId = (int)($_POST['user_id'] ?? 0);

    if ($action === 'toggle_role' && $userId > 0) {
        $row = $pdo->prepare("SELECT role FROM users WHERE id = ? LIMIT 1");
        $row->execute([$userId]);
        $user = $row->fetch();
        if ($user) {
            $newRole = $user['role'] === 'admin' ? 'customer' : 'admin';
            $pdo->prepare("UPDATE users SET role = ? WHERE id = ?")->execute([$newRole, $userId]);
            $msg = "User role changed to {$newRole}.";
        }
    } elseif ($action === 'delete' && $userId > 0 && $userId !== (int)$_SESSION['user_id']) {
        $pdo->prepare("DELETE FROM users WHERE id = ?")->execute([$userId]);
        $msg = 'User deleted.';
    }
}

$users = $pdo->query(
    "SELECT u.id, u.name, u.email, u.phone, u.role, u.created_at,
            COUNT(o.id) AS order_count
     FROM users u
     LEFT JOIN orders o ON o.user_id = u.id
     GROUP BY u.id ORDER BY u.created_at DESC"
)->fetchAll();
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Users | <?= sanitize(APP_NAME) ?> Admin</title>
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
        <div class="admin-topbar-title">Users</div>
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

      <div class="admin-table-card">
        <div class="table-card-header">
          <h3>All Users (<?= count($users) ?>)</h3>
          <div class="table-search">
            <i class="fas fa-search" style="color:var(--text-muted)"></i>
            <input type="text" id="tableSearch" placeholder="Search…">
          </div>
        </div>
        <div class="admin-table-wrap">
          <table class="admin-table">
            <thead>
              <tr><th>#</th><th>Name</th><th>Email</th><th>Phone</th><th>Role</th><th>Orders</th><th>Joined</th><th>Actions</th></tr>
            </thead>
            <tbody>
              <?php foreach ($users as $u): ?>
              <tr class="searchable-row">
                <td><?= (int)$u['id'] ?></td>
                <td>
                  <div style="display:flex;align-items:center;gap:.65rem">
                    <div class="admin-avatar" style="width:32px;height:32px;font-size:.8rem">
                      <?= strtoupper(substr($u['name'], 0, 1)) ?>
                    </div>
                    <?= sanitize($u['name']) ?>
                  </div>
                </td>
                <td><?= sanitize($u['email']) ?></td>
                <td><?= sanitize($u['phone'] ?? '—') ?></td>
                <td>
                  <span class="badge <?= $u['role'] === 'admin' ? 'badge-warning' : 'badge-info' ?>">
                    <?= sanitize($u['role']) ?>
                  </span>
                </td>
                <td><?= (int)$u['order_count'] ?></td>
                <td><?= date('d M Y', strtotime($u['created_at'])) ?></td>
                <td>
                  <div class="table-actions">
                    <form method="POST" style="display:inline">
                      <input type="hidden" name="action"  value="toggle_role">
                      <input type="hidden" name="user_id" value="<?= (int)$u['id'] ?>">
                      <button type="submit" class="btn-icon btn-icon-edit" title="Toggle role">
                        <i class="fas fa-user-shield"></i>
                      </button>
                    </form>
                    <?php if ($u['id'] != $_SESSION['user_id']): ?>
                    <form method="POST" style="display:inline">
                      <input type="hidden" name="action"  value="delete">
                      <input type="hidden" name="user_id" value="<?= (int)$u['id'] ?>">
                      <button type="button" class="btn-icon btn-icon-delete delete-trigger-btn"
                              data-name="<?= sanitize($u['name']) ?>">
                        <i class="fas fa-trash"></i>
                      </button>
                    </form>
                    <?php endif; ?>
                  </div>
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
<!-- Delete Modal -->
<div class="delete-modal" id="deleteModal">
  <div class="delete-modal-box">
    <div class="delete-modal-icon"><i class="fas fa-exclamation-triangle"></i></div>
    <h3>Delete User?</h3>
    <p>Delete <strong class="delete-item-name"></strong>? Their orders and data will remain but they won't be able to log in.</p>
    <div class="delete-modal-actions">
      <button class="btn-outline" id="cancelDeleteBtn">Cancel</button>
      <button class="btn-danger"  id="confirmDeleteBtn">Delete</button>
    </div>
  </div>
</div>
<div id="toastContainer"></div>
<script src="<?= BASE_URL ?>/js/app.js"></script>
<script>window.APP_BASE = '<?= BASE_URL ?>';</script>
<script src="<?= BASE_URL ?>/js/admin.js"></script>
</body>
</html>
