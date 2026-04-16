<?php
// ============================================================
//  cart.php – Cart Page
// ============================================================
require_once __DIR__ . '/includes/session.php';
require_once __DIR__ . '/includes/functions.php';

$pageTitle = 'Shopping Cart';
$cart      = getCartFromSession();
$subtotal  = getCartTotal();

include __DIR__ . '/includes/header.php';
?>

<div class="page-header">
  <div class="container">
    <h1><i class="fas fa-shopping-cart"></i> Your Cart</h1>
    <div class="breadcrumb"><a href="<?= BASE_URL ?>/index.php">Home</a> / Cart</div>
  </div>
</div>

<section class="cart-page">
  <div class="container">
    <?php if (empty($cart)): ?>
      <div class="empty-state">
        <i class="fas fa-shopping-cart"></i>
        <h3>Your cart is empty</h3>
        <p>Looks like you haven't added any delicious items yet!</p>
        <a href="<?= BASE_URL ?>/menu.php" class="btn-primary">
          <i class="fas fa-utensils"></i> Browse Menu
        </a>
      </div>
    <?php else: ?>
    <div style="display:grid;grid-template-columns:1fr 360px;gap:2rem;align-items:start">

      <!-- Cart Table -->
      <div>
        <div class="cart-table-wrap">
          <table class="cart-table">
            <thead>
              <tr>
                <th>Item</th>
                <th>Price</th>
                <th>Quantity</th>
                <th>Total</th>
                <th></th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($cart as $item): ?>
              <tr data-food-id="<?= (int)$item['food_id'] ?>">
                <td>
                  <div style="display:flex;align-items:center;gap:.85rem">
                    <img class="cart-item-img"
                         src="<?= sanitize($item['image'] ?? '') ?>"
                         alt="<?= sanitize($item['name']) ?>"
                         onerror="this.src='data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' width=\'54\' height=\'54\'%3E%3Crect fill=\'%23f0f0f0\' width=\'54\' height=\'54\'/%3E%3C/svg%3E'">
                    <span style="font-weight:600"><?= sanitize($item['name']) ?></span>
                  </div>
                </td>
                <td>৳<?= number_format($item['price'], 2) ?></td>
                <td>
                  <div class="qty-control">
                    <button class="qty-btn qty-btn-minus" aria-label="Decrease quantity">−</button>
                    <span class="qty-display"><?= (int)$item['qty'] ?></span>
                    <button class="qty-btn qty-btn-plus"  aria-label="Increase quantity">+</button>
                  </div>
                </td>
                <td style="font-weight:700;color:var(--accent)">
                  ৳<?= number_format($item['price'] * $item['qty'], 2) ?>
                </td>
                <td>
                  <button class="btn-icon btn-icon-delete cart-remove-btn"
                          data-food-id="<?= (int)$item['food_id'] ?>"
                          title="Remove item">
                    <i class="fas fa-trash"></i>
                  </button>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
        <div style="margin-top:1.25rem;display:flex;gap:1rem;flex-wrap:wrap">
          <a href="<?= BASE_URL ?>/menu.php" class="btn-outline">
            <i class="fas fa-arrow-left"></i> Continue Shopping
          </a>
        </div>
      </div>

      <!-- Summary Box -->
      <div class="cart-summary-box">
        <h3 style="margin-bottom:1.25rem;font-size:1.1rem">Order Summary</h3>

        <div class="summary-row">
          <span>Subtotal</span>
          <span class="cart-subtotal cart-total-value">৳<?= number_format($subtotal, 2) ?></span>
        </div>

        <!-- Promo Code -->
        <div class="promo-row">
          <input type="text" id="promoCodeInput" class="form-control"
                 placeholder="Promo code" style="border-radius:8px;font-size:.88rem">
          <button id="applyPromoBtn" class="btn-outline btn-sm" style="white-space:nowrap">Apply</button>
        </div>

        <div class="summary-row" id="discountRow" style="display:none;color:var(--success)">
          <span>Discount</span>
          <span id="discountValue">- ৳0.00</span>
        </div>

        <div class="summary-row total">
          <span>Total</span>
          <span id="grandTotal">৳<?= number_format($subtotal, 2) ?></span>
        </div>

        <a href="<?= BASE_URL ?>/checkout.php" class="btn-primary btn-block" style="margin-top:1.25rem">
          <i class="fas fa-credit-card"></i> Proceed to Checkout
        </a>
      </div>
    </div>
    <?php endif; ?>
  </div>
</section>

<script>
window.APP_BASE  = '<?= BASE_URL ?>';
window._cartData = <?= json_encode($cart) ?>;
</script>
<script src="<?= BASE_URL ?>/js/checkout.js"></script>
<?php include __DIR__ . '/includes/footer.php'; ?>
