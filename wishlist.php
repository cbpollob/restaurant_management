<?php
// ============================================================
//  wishlist.php
// ============================================================
require_once __DIR__ . '/includes/session.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

requireLogin();

$pageTitle = 'My Wishlist';
$pdo       = getDB();

$stmt = $pdo->prepare(
    "SELECT f.id, f.name, f.description, f.price, f.image_url, f.stock,
            c.name AS category_name,
            COALESCE(AVG(r.rating), 0) AS avg_rating,
            COUNT(r.id) AS review_count
     FROM wishlist w
     JOIN food_items f ON f.id = w.food_id
     JOIN categories c ON c.id = f.category_id
     LEFT JOIN reviews r ON r.food_id = f.id
     WHERE w.user_id = ?
     GROUP BY f.id
     ORDER BY w.created_at DESC"
);
$stmt->execute([$_SESSION['user_id']]);
$items = $stmt->fetchAll();

include __DIR__ . '/includes/header.php';
?>

<div class="page-header">
  <div class="container">
    <h1><i class="fas fa-heart"></i> My Wishlist</h1>
    <div class="breadcrumb"><a href="<?= BASE_URL ?>/index.php">Home</a> / Wishlist</div>
  </div>
</div>

<section class="wishlist-page">
  <div class="container">
    <?php if (empty($items)): ?>
      <div class="empty-state">
        <i class="fas fa-heart-broken"></i>
        <h3>Your wishlist is empty</h3>
        <p>Save your favourite dishes here so you can find them easily later.</p>
        <a href="<?= BASE_URL ?>/menu.php" class="btn-primary">Browse Menu</a>
      </div>
    <?php else: ?>
      <p style="margin-bottom:1.5rem;color:var(--text-muted)">
        <?= count($items) ?> saved item<?= count($items) !== 1 ? 's' : '' ?>
      </p>
      <div class="food-grid">
        <?php foreach ($items as $item):
          $avg   = round((float)$item['avg_rating'], 1);
          $count = (int)$item['review_count'];
        ?>
        <div class="food-card hover-lift" data-id="<?= (int)$item['id'] ?>">
          <div class="food-card-img-wrap">
            <img class="food-card-img"
                 data-src="<?= sanitize($item['image_url'] ?? '') ?>"
                 src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='400' height='200'%3E%3Crect fill='%23f0f0f0' width='400' height='200'/%3E%3C/svg%3E"
                 alt="<?= sanitize($item['name']) ?>" loading="lazy">
            <span class="food-card-badge"><?= sanitize($item['category_name']) ?></span>
            <button class="wishlist-btn active" data-food-id="<?= (int)$item['id'] ?>" aria-label="Remove from wishlist">
              <i class="fas fa-heart"></i>
            </button>
          </div>
          <div class="food-card-body">
            <div class="food-card-category"><?= sanitize($item['category_name']) ?></div>
            <div class="food-card-name"><?= sanitize($item['name']) ?></div>
            <div class="food-card-desc"><?= sanitize($item['description'] ?? '') ?></div>
            <div class="star-rating">
              <?php for ($i = 1; $i <= 5; $i++): ?>
                <i class="star fas fa-star <?= $i <= $avg ? 'filled' : '' ?>"></i>
              <?php endfor; ?>
              <span class="rating-count">(<?= $count ?>)</span>
            </div>
            <div class="food-card-footer">
              <span class="food-price"><?= number_format($item['price'], 2) ?></span>
              <?php if ($item['stock'] > 0): ?>
              <button class="btn-primary btn-sm add-to-cart-btn btn-ripple" data-food-id="<?= (int)$item['id'] ?>">
                <i class="fas fa-cart-plus"></i> Add
              </button>
              <?php else: ?>
              <span class="badge badge-danger">Out of Stock</span>
              <?php endif; ?>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</section>

<script>window.APP_BASE = '<?= BASE_URL ?>';</script>
<script src="<?= BASE_URL ?>/js/menu.js"></script>
<?php include __DIR__ . '/includes/footer.php'; ?>
