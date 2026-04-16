<?php
// ============================================================
//  index.php – Homepage
// ============================================================
require_once __DIR__ . '/includes/session.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

$pageTitle = 'Home – Fine Dining & Fast Delivery';

// Fetch featured food items
$pdo       = getDB();
$featured  = $pdo->query(
    "SELECT f.id, f.name, f.description, f.price, f.image_url, c.name AS category_name
     FROM food_items f JOIN categories c ON c.id = f.category_id
     WHERE f.featured = 1 AND f.status = 'active' LIMIT 8"
)->fetchAll();

// Fetch active categories
$categories = $pdo->query(
    "SELECT id, name, slug FROM categories WHERE status = 'active' ORDER BY name"
)->fetchAll();

$catIcons = [
    'starters'    => 'fa-bread-slice',
    'main-course' => 'fa-drumstick-bite',
    'desserts'    => 'fa-ice-cream',
    'drinks'      => 'fa-cocktail',
    'fast-food'   => 'fa-hamburger',
    'seafood'     => 'fa-fish',
];

// Fetch 3 latest reviews
$reviews = $pdo->query(
    "SELECT r.rating, r.comment, u.name AS user_name, f.name AS food_name
     FROM reviews r
     JOIN users u ON u.id = r.user_id
     JOIN food_items f ON f.id = r.food_id
     ORDER BY r.created_at DESC LIMIT 3"
)->fetchAll();

include __DIR__ . '/includes/header.php';
?>

<!-- ===== HERO SECTION ===== -->
<section class="hero" id="hero">
  <div class="container">
    <div class="hero-content">
      <span class="hero-eyebrow"><i class="fas fa-star"></i> Award-Winning Restaurant</span>
      <h1>Welcome to <span><?= sanitize(APP_NAME) ?></span></h1>
      <p class="hero-tagline">
        <span id="typingText"></span><span class="typing-cursor">|</span>
      </p>
      <div class="hero-cta">
        <a href="<?= BASE_URL ?>/menu.php" class="btn-primary btn-lg btn-ripple">
          <i class="fas fa-utensils"></i> Order Now
        </a>
        <a href="<?= BASE_URL ?>/booking.php" class="btn-outline btn-lg" style="color:#fff;border-color:rgba(255,255,255,.5)">
          <i class="fas fa-calendar-check"></i> Book a Table
        </a>
      </div>
      <div style="display:flex;gap:2rem;margin-top:2.5rem;flex-wrap:wrap">
        <div style="text-align:center"><div style="font-size:1.6rem;font-weight:800;color:var(--accent)">500+</div><div style="font-size:.8rem;color:rgba(255,255,255,.6)">Menu Items</div></div>
        <div style="text-align:center"><div style="font-size:1.6rem;font-weight:800;color:var(--secondary)">10K+</div><div style="font-size:.8rem;color:rgba(255,255,255,.6)">Happy Customers</div></div>
        <div style="text-align:center"><div style="font-size:1.6rem;font-weight:800;color:var(--gold)">4.9★</div><div style="font-size:.8rem;color:rgba(255,255,255,.6)">Average Rating</div></div>
      </div>
    </div>
  </div>
  <div class="hero-decoration">
    <div class="hero-circle"></div>
    <div class="hero-circle-sm"></div>
  </div>
</section>

<!-- ===== FEATURED DISHES ===== -->
<?php if ($featured): ?>
<section class="featured-section section-padding" id="featured">
  <div class="container">
    <div class="section-header text-center reveal">
      <h2>Featured Dishes</h2>
      <p>Chef's hand-picked selections – irresistible flavours, every time.</p>
    </div>
    <div class="slider-nav reveal">
      <button class="slider-arrow" id="sliderPrev" aria-label="Previous"><i class="fas fa-chevron-left"></i></button>
      <button class="slider-arrow" id="sliderNext" aria-label="Next"><i class="fas fa-chevron-right"></i></button>
    </div>
    <div class="food-slider reveal" id="featuredSlider">
      <?php foreach ($featured as $item): ?>
      <div class="food-card hover-lift">
        <div class="food-card-img-wrap">
          <img class="food-card-img" data-src="<?= sanitize($item['image_url'] ?? '') ?>"
               src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='270' height='200'%3E%3Crect fill='%23f0f0f0' width='270' height='200'/%3E%3C/svg%3E"
               alt="<?= sanitize($item['name']) ?>" loading="lazy">
          <span class="food-card-badge featured"><i class="fas fa-star"></i> Featured</span>
        </div>
        <div class="food-card-body">
          <div class="food-card-category"><?= sanitize($item['category_name']) ?></div>
          <div class="food-card-name"><?= sanitize($item['name']) ?></div>
          <div class="food-card-footer" style="margin-top:.75rem">
            <span class="food-price"><?= number_format($item['price'], 2) ?></span>
            <button class="btn-primary btn-sm add-to-cart-btn btn-ripple" data-food-id="<?= (int)$item['id'] ?>">
              <i class="fas fa-plus"></i> Add
            </button>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- ===== CATEGORIES ===== -->
<?php if ($categories): ?>
<section class="categories-section section-padding">
  <div class="container">
    <div class="section-header text-center reveal">
      <h2>Browse by Category</h2>
      <p>From starters to desserts – we have everything for every craving.</p>
    </div>
    <div class="categories-grid reveal">
      <?php foreach ($categories as $cat): ?>
      <a href="<?= BASE_URL ?>/menu.php?category=<?= (int)$cat['id'] ?>" class="category-card" style="text-decoration:none">
        <i class="fas <?= sanitize($catIcons[$cat['slug']] ?? 'fa-bowl-food') ?>"></i>
        <span><?= sanitize($cat['name']) ?></span>
      </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- ===== WHY CHOOSE US ===== -->
<section class="features-section section-padding">
  <div class="container">
    <div class="section-header text-center reveal">
      <h2>Why Choose Us?</h2>
      <p>We go above and beyond to make every meal memorable.</p>
    </div>
    <div class="features-grid">
      <?php
      $features = [
        ['fa-bolt',           'Fast Delivery',       'Hot, fresh meals delivered to your door in under 45 minutes or your next order is free.'],
        ['fa-leaf',           'Fresh Ingredients',   'We source locally every morning. No preservatives, no frozen shortcuts – ever.'],
        ['fa-calendar-check', 'Easy Booking',        'Reserve a table in seconds. Choose your date, time slot, and party size online.'],
        ['fa-headset',        '24/7 Support',        'Our friendly team is always here – chat, call or email us anytime, day or night.'],
      ];
      foreach ($features as $idx => $f):
      ?>
      <div class="feature-card reveal" style="animation-delay:<?= $idx * .1 ?>s">
        <div class="feature-icon"><i class="fas <?= $f[0] ?>"></i></div>
        <h3><?= $f[1] ?></h3>
        <p><?= $f[2] ?></p>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ===== TESTIMONIALS ===== -->
<?php if ($reviews): ?>
<section class="reviews-section section-padding">
  <div class="container">
    <div class="section-header text-center reveal">
      <h2>What Our Guests Say</h2>
      <p>Real reviews from real food lovers.</p>
    </div>
    <div class="reviews-grid">
      <?php foreach ($reviews as $rev): ?>
      <div class="review-card reveal">
        <div class="review-quote">"</div>
        <p class="review-text"><?= sanitize($rev['comment']) ?></p>
        <div class="star-rating" style="margin-bottom:.75rem">
          <?php for ($i = 1; $i <= 5; $i++): ?>
            <i class="star fas fa-star <?= $i <= $rev['rating'] ? 'filled' : '' ?>"></i>
          <?php endfor; ?>
        </div>
        <div class="review-author">
          <div class="review-avatar"><?= strtoupper(substr($rev['user_name'], 0, 1)) ?></div>
          <div>
            <div class="review-author-name"><?= sanitize($rev['user_name']) ?></div>
            <div class="review-food-name">On: <?= sanitize($rev['food_name']) ?></div>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- ===== CTA SECTION ===== -->
<section class="cta-section" data-parallax="0.3">
  <div class="container" style="position:relative;z-index:2">
    <h2 class="reveal">Reserve Your Table Tonight</h2>
    <p class="reveal">Experience fine dining with your loved ones. Tables fill up fast on weekends!</p>
    <a href="<?= BASE_URL ?>/booking.php" class="btn-primary btn-lg btn-ripple reveal">
      <i class="fas fa-calendar-alt"></i> Book Now
    </a>
  </div>
</section>

<script>window.APP_BASE = '<?= BASE_URL ?>';</script>
<script src="<?= BASE_URL ?>/js/menu.js"></script>
<?php include __DIR__ . '/includes/footer.php'; ?>
