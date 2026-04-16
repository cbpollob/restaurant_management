<?php
// ============================================================
//  checkout.php
// ============================================================
require_once __DIR__ . '/includes/session.php';
require_once __DIR__ . '/includes/functions.php';

$pageTitle = 'Checkout';
$cart      = getCartFromSession();
$subtotal  = getCartTotal();

// Redirect if cart empty
if (empty($cart)) {
    header('Location: ' . BASE_URL . '/cart.php');
    exit;
}

include __DIR__ . '/includes/header.php';
?>

<div class="page-header">
  <div class="container">
    <h1><i class="fas fa-credit-card"></i> Checkout</h1>
    <div class="breadcrumb">
      <a href="<?= BASE_URL ?>/index.php">Home</a> /
      <a href="<?= BASE_URL ?>/cart.php">Cart</a> / Checkout
    </div>
  </div>
</div>

<section class="checkout-page">
  <div class="container">
    <div class="checkout-grid">

      <!-- Left: Order Details -->
      <div>
        <!-- Order Type -->
        <div class="checkout-card">
          <h3><i class="fas fa-concierge-bell"></i> Order Type</h3>
          <div class="order-type-toggle">
            <button class="order-type-btn active" data-type="delivery">
              <i class="fas fa-motorcycle"></i> Delivery
            </button>
            <button class="order-type-btn" data-type="dine-in">
              <i class="fas fa-utensils"></i> Dine-In
            </button>
          </div>
          <input type="hidden" id="orderTypeInput" value="delivery">
        </div>

        <!-- Delivery Info -->
        <div class="checkout-card" id="deliveryFields">
          <h3><i class="fas fa-map-marker-alt"></i> Delivery Information</h3>
          <div class="form-group">
            <label class="form-label" for="deliveryAddress">Delivery Address</label>
            <textarea id="deliveryAddress" class="form-control" rows="3"
                      placeholder="Enter your full delivery address…"></textarea>
          </div>
          <div class="form-group">
            <label class="form-label" for="deliveryPhone">Contact Phone</label>
            <div class="input-group">
              <i class="input-icon fas fa-phone"></i>
              <input type="tel" id="deliveryPhone" class="form-control"
                     placeholder="+880 17XXXXXXXX"
                     value="<?= isLoggedIn() ? '' : '' ?>">
            </div>
          </div>
        </div>

        <!-- Payment Method -->
        <div class="checkout-card">
          <h3><i class="fas fa-wallet"></i> Payment Method</h3>
          <div class="payment-methods">
            <div class="payment-method-card pm-bkash" data-method="bkash">
              <i class="fas fa-mobile-alt"></i>
              <span>bKash</span>
            </div>
            <div class="payment-method-card pm-nagad" data-method="nagad">
              <i class="fas fa-wallet"></i>
              <span>Nagad</span>
            </div>
            <div class="payment-method-card pm-card" data-method="card">
              <i class="fas fa-credit-card"></i>
              <span>Card</span>
            </div>
          </div>

          <!-- bKash fields -->
          <div class="payment-fields" id="fields_bkash" style="display:none;margin-top:1.25rem">
            <div class="form-group">
              <label class="form-label">bKash Account Number</label>
              <div class="input-group">
                <i class="input-icon fas fa-mobile-alt"></i>
                <input type="tel" class="form-control" placeholder="01XXXXXXXXX">
              </div>
            </div>
          </div>

          <!-- Nagad fields -->
          <div class="payment-fields" id="fields_nagad" style="display:none;margin-top:1.25rem">
            <div class="form-group">
              <label class="form-label">Nagad Account Number</label>
              <div class="input-group">
                <i class="input-icon fas fa-wallet"></i>
                <input type="tel" class="form-control" placeholder="01XXXXXXXXX">
              </div>
            </div>
          </div>

          <!-- Card fields -->
          <div class="payment-fields" id="fields_card" style="display:none;margin-top:1.25rem">
            <div class="form-group">
              <label class="form-label">Card Number</label>
              <div class="input-group">
                <i class="input-icon fas fa-credit-card"></i>
                <input type="text" class="form-control" placeholder="1234 5678 9012 3456" maxlength="19">
              </div>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem">
              <div class="form-group">
                <label class="form-label">Expiry Date</label>
                <input type="text" class="form-control" placeholder="MM/YY" maxlength="5">
              </div>
              <div class="form-group">
                <label class="form-label">CVV</label>
                <input type="password" class="form-control" placeholder="•••" maxlength="4">
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Right: Order Summary -->
      <div>
        <div class="checkout-card" style="position:sticky;top:90px">
          <h3><i class="fas fa-receipt"></i> Order Summary</h3>

          <!-- Cart Items -->
          <div style="max-height:280px;overflow-y:auto;margin-bottom:1rem">
            <?php foreach ($cart as $item): ?>
            <div style="display:flex;justify-content:space-between;align-items:center;padding:.6rem 0;border-bottom:1px solid var(--border-color)">
              <div style="display:flex;align-items:center;gap:.75rem;flex:1;min-width:0">
                <img src="<?= sanitize($item['image'] ?? '') ?>" alt=""
                     style="width:44px;height:44px;border-radius:8px;object-fit:cover;flex-shrink:0"
                     onerror="this.style.display='none'">
                <div style="min-width:0">
                  <div style="font-size:.88rem;font-weight:600;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">
                    <?= sanitize($item['name']) ?>
                  </div>
                  <div style="font-size:.78rem;color:var(--text-muted)">×<?= (int)$item['qty'] ?></div>
                </div>
              </div>
              <span style="font-weight:600;color:var(--accent);flex-shrink:0;margin-left:.5rem">
                ৳<?= number_format($item['price'] * $item['qty'], 2) ?>
              </span>
            </div>
            <?php endforeach; ?>
          </div>

          <!-- Promo Code -->
          <div class="promo-row">
            <input type="text" id="promoCodeInput" class="form-control"
                   placeholder="Promo code" style="font-size:.88rem;border-radius:8px">
            <button id="applyPromoBtn" class="btn-outline btn-sm btn-ripple" style="white-space:nowrap">Apply</button>
          </div>

          <!-- Totals -->
          <div class="summary-row">
            <span>Subtotal</span>
            <span id="checkoutSubtotal" data-value="<?= $subtotal ?>">৳<?= number_format($subtotal, 2) ?></span>
          </div>
          <div class="summary-row" id="discountRow" style="display:none;color:var(--success)">
            <span>Discount</span>
            <span id="discountValue">- ৳0.00</span>
          </div>
          <div class="summary-row total">
            <span>Grand Total</span>
            <span id="grandTotal">৳<?= number_format($subtotal, 2) ?></span>
          </div>

          <button id="placeOrderBtn" class="btn-primary btn-block btn-ripple" style="margin-top:1.25rem;font-size:1rem">
            <i class="fas fa-check-circle"></i> Place Order
          </button>
          <p style="text-align:center;font-size:.78rem;color:var(--text-muted);margin-top:.75rem">
            <i class="fas fa-lock"></i> Secure & encrypted checkout
          </p>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Payment Result Modal -->
<div class="modal-overlay" id="paymentModal">
  <div class="modal">
    <div class="modal-icon"></div>
    <h3 class="modal-title">Processing…</h3>
    <p class="modal-msg"></p>
    <button class="btn-primary btn-block modal-action-btn">Continue</button>
  </div>
</div>

<script>window.APP_BASE = '<?= BASE_URL ?>';</script>
<script src="<?= BASE_URL ?>/js/checkout.js"></script>
<?php include __DIR__ . '/includes/footer.php'; ?>
