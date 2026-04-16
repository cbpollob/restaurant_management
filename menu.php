<?php
// ============================================================
//  menu.php – Full Menu Page
// ============================================================
require_once __DIR__ . '/includes/session.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

$pageTitle = 'Our Menu';
$pdo       = getDB();

// Fetch categories for filter buttons
$categories = $pdo->query(
    "SELECT id, name FROM categories WHERE status = 'active' ORDER BY name"
)->fetchAll();

// Active category filter
$activeCatId = isset($_GET['category']) ? (int) $_GET['category'] : 0;

// Fetch food items (with avg rating)
if ($activeCatId > 0) {
    $stmt = $pdo->prepare(
        "SELECT f.id, f.name, f.description, f.price, f.image_url, f.featured, f.stock,
                c.name AS category_name,
                COALESCE(AVG(r.rating), 0) AS avg_rating,
                COUNT(r.id) AS review_count
         FROM food_items f
         JOIN categories c ON c.id = f.category_id
         LEFT JOIN reviews r ON r.food_id = f.id
         WHERE f.status = 'active' AND f.category_id = ?
         GROUP BY f.id ORDER BY f.featured DESC, f.name ASC"
    );
    $stmt->execute([$activeCatId]);
} else {
    $stmt = $pdo->prepare(
        "SELECT f.id, f.name, f.description, f.price, f.image_url, f.featured, f.stock,
                c.name AS category_name,
                COALESCE(AVG(r.rating), 0) AS avg_rating,
                COUNT(r.id) AS review_count
         FROM food_items f
         JOIN categories c ON c.id = f.category_id
         LEFT JOIN reviews r ON r.food_id = f.id
         WHERE f.status = 'active'
         GROUP BY f.id ORDER BY f.featured DESC, f.name ASC"
    );
    $stmt->execute();
}
$foodItems = $stmt->fetchAll();

// Wishlist items for logged-in user
$wishlistIds = [];
if (isLoggedIn()) {
    $ws = $pdo->prepare("SELECT food_id FROM wishlist WHERE user_id = ?");
    $ws->execute([$_SESSION['user_id']]);
    $wishlistIds = array_column($ws->fetchAll(), 'food_id');
}

include __DIR__ . '/includes/header.php';
?>

<!-- Page Header -->
<div class="page-header">
  <div class="container">
    <h1><i class="fas fa-utensils"></i> Our Menu</h1>
    <p>Fresh, handcrafted dishes made with the finest ingredients</p>
    <div class="breadcrumb"><a href="<?= BASE_URL ?>/index.php">Home</a> / Menu</div>
  </div>
</div>

<section class="section-padding" style="background:var(--bg-secondary)">
  <div class="container">

    <!-- Search Bar -->
    <div class="reveal" style="margin-bottom:1.5rem">
      <div class="input-group" style="max-width:480px;margin:0 auto">
        <i class="input-icon fas fa-search"></i>
        <input type="text" id="menuSearch" class="form-control"
               placeholder="Search dishes, ingredients…" style="padding-left:2.5rem;border-radius:50px">
      </div>
    </div>

    <!-- Category Filter -->
    <div style="display:flex;flex-wrap:wrap;gap:.65rem;justify-content:center;margin-bottom:2rem" class="reveal">
      <button class="category-filter-btn btn-outline btn-sm <?= $activeCatId === 0 ? 'active' : '' ?>"
              data-category-id="0" style="border-radius:50px">
        <i class="fas fa-th-large"></i> All
      </button>
      <?php foreach ($categories as $cat): ?>
      <button class="category-filter-btn btn-outline btn-sm <?= $activeCatId === (int)$cat['id'] ? 'active' : '' ?>"
              data-category-id="<?= (int)$cat['id'] ?>" style="border-radius:50px">
        <?= sanitize($cat['name']) ?>
      </button>
      <?php endforeach; ?>
    </div>

    <!-- Food Grid -->
    <div class="food-grid" id="foodGrid">
      <?php if (empty($foodItems)): ?>
        <div class="empty-state" style="grid-column:1/-1">
          <i class="fas fa-utensils"></i>
          <h3>No items found</h3>
          <p>Try a different category or search term.</p>
        </div>
      <?php else: ?>
        <?php foreach ($foodItems as $item):
          $inWishlist  = in_array($item['id'], $wishlistIds);
          $avgRating   = round((float)$item['avg_rating'], 1);
          $reviewCount = (int)$item['review_count'];
        ?>
        <div class="food-card hover-lift" data-id="<?= (int)$item['id'] ?>">
          <div class="food-card-img-wrap">
            <img class="food-card-img"
                 data-src="<?= sanitize($item['image_url'] ?? '') ?>"
                 src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='400' height='200'%3E%3Crect fill='%23f0f0f0' width='400' height='200'/%3E%3C/svg%3E"
                 alt="<?= sanitize($item['name']) ?>" loading="lazy">
            <span class="food-card-badge"><?= sanitize($item['category_name']) ?></span>
            <?php if ($item['featured']): ?>
              <span class="food-card-badge featured" style="top:12px;left:auto;right:52px">
                <i class="fas fa-star"></i> Featured
              </span>
            <?php endif; ?>
            <button class="wishlist-btn <?= $inWishlist ? 'active' : '' ?>"
                    data-food-id="<?= (int)$item['id'] ?>"
                    aria-label="Toggle wishlist">
              <i class="<?= $inWishlist ? 'fas' : 'far' ?> fa-heart"></i>
            </button>
          </div>
          <div class="food-card-body">
            <div class="food-card-category"><?= sanitize($item['category_name']) ?></div>
            <div class="food-card-name"><?= sanitize($item['name']) ?></div>
            <div class="food-card-desc"><?= sanitize($item['description'] ?? '') ?></div>
            <div class="star-rating">
              <?php for ($i = 1; $i <= 5; $i++): ?>
                <i class="star fas fa-star <?= $i <= $avgRating ? 'filled' : '' ?>"></i>
              <?php endfor; ?>
              <span class="rating-count">(<?= $reviewCount ?>)</span>
            </div>
            <div class="food-card-footer">
              <span class="food-price"><?= number_format($item['price'], 2) ?></span>
              <?php if ($item['stock'] > 0): ?>
                <button class="btn-primary btn-sm add-to-cart-btn btn-ripple"
                        data-food-id="<?= (int)$item['id'] ?>">
                  <i class="fas fa-plus"></i> Add
                </button>
              <?php else: ?>
                <span class="badge badge-danger">Out of Stock</span>
              <?php endif; ?>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>

  </div>
</section>

<script>window.APP_BASE = '<?= BASE_URL ?>';</script>
<script src="<?= BASE_URL ?>/js/menu.js"></script>
<?php include __DIR__ . '/includes/footer.php'; ?>
