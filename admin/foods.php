<?php
// ============================================================
//  admin/foods.php – Food Management
// ============================================================
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

requireAdmin();

$pdo = getDB();

// Handle POST actions
$formError   = '';
$formSuccess = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'delete') {
        $id = (int)($_POST['food_id'] ?? 0);
        if ($id > 0) {
            $pdo->prepare("DELETE FROM food_items WHERE id = ?")->execute([$id]);
            $formSuccess = 'Food item deleted.';
        }
    } elseif (in_array($action, ['add', 'edit'])) {
        $id          = (int)($_POST['food_id']    ?? 0);
        $categoryId  = (int)($_POST['category_id'] ?? 0);
        $name        = trim($_POST['name']         ?? '');
        $description = trim($_POST['description']  ?? '');
        $price       = (float)($_POST['price']     ?? 0);
        $stock       = (int)($_POST['stock']       ?? 0);
        $featured    = isset($_POST['featured']) ? 1 : 0;
        $status      = in_array($_POST['status'] ?? '', ['active','inactive']) ? $_POST['status'] : 'active';
        $imageUrl    = trim($_POST['image_url']    ?? '');

        if ($name === '' || $categoryId <= 0 || $price <= 0) {
            $formError = 'Name, category and price are required.';
        } else {
            if ($action === 'add') {
                $pdo->prepare(
                    "INSERT INTO food_items (category_id, name, description, price, image_url, stock, featured, status)
                     VALUES (?,?,?,?,?,?,?,?)"
                )->execute([$categoryId, $name, $description, $price, $imageUrl, $stock, $featured, $status]);
                $formSuccess = 'Food item added successfully.';
            } else {
                $pdo->prepare(
                    "UPDATE food_items SET category_id=?, name=?, description=?, price=?, image_url=?, stock=?, featured=?, status=? WHERE id=?"
                )->execute([$categoryId, $name, $description, $price, $imageUrl, $stock, $featured, $status, $id]);
                $formSuccess = 'Food item updated successfully.';
            }
        }
    }
}

// Fetch items
$foods = $pdo->query(
    "SELECT f.*, c.name AS category_name FROM food_items f
     JOIN categories c ON c.id = f.category_id ORDER BY f.id DESC"
)->fetchAll();
$categories = $pdo->query("SELECT id, name FROM categories WHERE status='active' ORDER BY name")->fetchAll();

// Editing?
$editItem = null;
if (isset($_GET['edit'])) {
    $s = $pdo->prepare("SELECT * FROM food_items WHERE id = ? LIMIT 1");
    $s->execute([(int)$_GET['edit']]);
    $editItem = $s->fetch();
}
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Foods | <?= sanitize(APP_NAME) ?> Admin</title>
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
        <div class="admin-topbar-title">Food Management</div>
      </div>
      <div class="admin-topbar-right">
        <button class="theme-toggle" id="themeToggle"><i class="fas fa-moon" id="themeIcon"></i></button>
        <a href="<?= BASE_URL ?>/logout.php" class="btn-danger btn-sm"><i class="fas fa-sign-out-alt"></i> Logout</a>
      </div>
    </header>

    <div class="admin-content">
      <!-- Add / Edit Form -->
      <div class="admin-table-card" style="margin-bottom:1.5rem">
        <div class="table-card-header">
          <h3><?= $editItem ? '<i class="fas fa-edit"></i> Edit Food Item' : '<i class="fas fa-plus"></i> Add New Food Item' ?></h3>
        </div>
        <div style="padding:1.5rem">
          <?php if ($formError):   ?><div class="toast error"   style="position:relative;animation:none;margin-bottom:1rem"><i class="fas fa-times-circle toast-icon"></i> <?= sanitize($formError) ?></div><?php endif; ?>
          <?php if ($formSuccess): ?><div class="toast success" style="position:relative;animation:none;margin-bottom:1rem"><i class="fas fa-check-circle toast-icon"></i> <?= sanitize($formSuccess) ?></div><?php endif; ?>

          <form method="POST" action="foods.php">
            <input type="hidden" name="action"  value="<?= $editItem ? 'edit' : 'add' ?>">
            <?php if ($editItem): ?><input type="hidden" name="food_id" value="<?= (int)$editItem['id'] ?>"><?php endif; ?>
            <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:1rem">
              <div class="form-group">
                <label class="form-label">Name *</label>
                <input type="text" name="name" class="form-control" value="<?= sanitize($editItem['name'] ?? '') ?>" required>
              </div>
              <div class="form-group">
                <label class="form-label">Category *</label>
                <select name="category_id" class="form-control" required>
                  <option value="">-- Select --</option>
                  <?php foreach ($categories as $cat): ?>
                  <option value="<?= (int)$cat['id'] ?>" <?= ($editItem['category_id'] ?? 0) == $cat['id'] ? 'selected' : '' ?>>
                    <?= sanitize($cat['name']) ?>
                  </option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="form-group">
                <label class="form-label">Price (৳) *</label>
                <input type="number" name="price" class="form-control" step="0.01" min="0" value="<?= sanitize($editItem['price'] ?? '') ?>" required>
              </div>
              <div class="form-group">
                <label class="form-label">Stock</label>
                <input type="number" name="stock" class="form-control" min="0" value="<?= (int)($editItem['stock'] ?? 0) ?>">
              </div>
              <div class="form-group">
                <label class="form-label">Status</label>
                <select name="status" class="form-control">
                  <option value="active"   <?= ($editItem['status'] ?? '') === 'active'   ? 'selected' : '' ?>>Active</option>
                  <option value="inactive" <?= ($editItem['status'] ?? '') === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                </select>
              </div>
              <div class="form-group" style="display:flex;align-items:center;gap:.75rem;padding-top:1.75rem">
                <input type="checkbox" name="featured" id="featuredChk" value="1"
                       <?= ($editItem['featured'] ?? 0) ? 'checked' : '' ?> style="width:18px;height:18px">
                <label for="featuredChk" class="form-label" style="margin:0">Featured</label>
              </div>
            </div>
            <div class="form-group">
              <label class="form-label">Description</label>
              <textarea name="description" class="form-control" rows="2"><?= sanitize($editItem['description'] ?? '') ?></textarea>
            </div>
            <div class="form-group">
              <label class="form-label">Image URL</label>
              <input type="url" name="image_url" id="imageUrlInput" class="form-control"
                     placeholder="https://…" value="<?= sanitize($editItem['image_url'] ?? '') ?>">
              <small style="color:var(--text-muted)">Or upload below using the upload tool.</small>
            </div>
            <div style="display:flex;gap:.75rem;flex-wrap:wrap">
              <button type="submit" class="btn-primary btn-ripple">
                <i class="fas fa-save"></i> <?= $editItem ? 'Update Item' : 'Add Item' ?>
              </button>
              <?php if ($editItem): ?>
              <a href="foods.php" class="btn-outline"><i class="fas fa-times"></i> Cancel</a>
              <?php endif; ?>
            </div>
          </form>
        </div>
      </div>

      <!-- Food List -->
      <div class="admin-table-card">
        <div class="table-card-header">
          <h3>All Food Items (<?= count($foods) ?>)</h3>
          <div class="table-search">
            <i class="fas fa-search" style="color:var(--text-muted)"></i>
            <input type="text" id="tableSearch" placeholder="Search…">
          </div>
        </div>
        <div class="admin-table-wrap">
          <table class="admin-table">
            <thead>
              <tr><th>Image</th><th>Name</th><th>Category</th><th>Price</th><th>Stock</th><th>Featured</th><th>Status</th><th>Actions</th></tr>
            </thead>
            <tbody>
              <?php foreach ($foods as $f): ?>
              <tr class="searchable-row">
                <td><img class="food-thumb" src="<?= sanitize($f['image_url'] ?? '') ?>" alt=""
                         onerror="this.src='data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' width=\'44\' height=\'44\'%3E%3Crect fill=\'%23f0f0f0\' width=\'44\' height=\'44\'/%3E%3C/svg%3E'"></td>
                <td><strong><?= sanitize($f['name']) ?></strong></td>
                <td><?= sanitize($f['category_name']) ?></td>
                <td>৳<?= number_format($f['price'], 2) ?></td>
                <td><?= (int)$f['stock'] ?></td>
                <td><?= $f['featured'] ? '<i class="fas fa-star" style="color:var(--gold)"></i>' : '—' ?></td>
                <td><span class="badge <?= $f['status'] === 'active' ? 'badge-success' : 'badge-danger' ?>"><?= sanitize($f['status']) ?></span></td>
                <td>
                  <div class="table-actions">
                    <a href="foods.php?edit=<?= (int)$f['id'] ?>" class="btn-icon btn-icon-edit" title="Edit"><i class="fas fa-edit"></i></a>
                    <form method="POST" action="foods.php" style="display:inline">
                      <input type="hidden" name="action"  value="delete">
                      <input type="hidden" name="food_id" value="<?= (int)$f['id'] ?>">
                      <button type="button" class="btn-icon btn-icon-delete delete-trigger-btn"
                              data-name="<?= sanitize($f['name']) ?>" title="Delete">
                        <i class="fas fa-trash"></i>
                      </button>
                    </form>
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
    <h3>Delete Item?</h3>
    <p>Are you sure you want to delete <strong class="delete-item-name"></strong>? This cannot be undone.</p>
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
