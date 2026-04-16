/* ============================================================
   RestauRant – cart.js
   ============================================================ */

'use strict';

const BASE_CART = window.APP_BASE || '';

// ============================================================
//  Cart Sidebar open / close
// ============================================================
const sidebar  = document.getElementById('cartSidebar');
const overlay  = document.getElementById('cartOverlay');
const openBtn  = document.getElementById('cartToggle');
const closeBtn = document.getElementById('cartClose');

function openCart() {
  if (!sidebar) return;
  sidebar.classList.add('open');
  if (overlay) overlay.classList.add('open');
  document.body.style.overflow = 'hidden';
  loadCartSidebar();
}
function closeCart() {
  if (!sidebar) return;
  sidebar.classList.remove('open');
  if (overlay) overlay.classList.remove('open');
  document.body.style.overflow = '';
}

if (openBtn)  openBtn.addEventListener('click', openCart);
if (closeBtn) closeBtn.addEventListener('click', closeCart);
if (overlay)  overlay.addEventListener('click', closeCart);

// ============================================================
//  Load / render cart sidebar contents
// ============================================================
async function loadCartSidebar() {
  const itemsEl  = document.getElementById('cartSidebarItems');
  const footerEl = document.getElementById('cartSidebarFooter');
  const totalEl  = document.getElementById('cartSidebarTotal');
  if (!itemsEl) return;

  // Fetch current cart via a simple update (qty=same) to get cart data
  const data = await fetchJSON(`${BASE_CART}/includes/api/cart_update.php`, {
    method: 'POST',
    body: (() => { const fd = new FormData(); fd.append('food_id','0'); fd.append('quantity','0'); return fd; })(),
  }).catch(() => null);

  // Alternatively, we read the cart from the page or a dedicated endpoint.
  // We'll use a lightweight approach: fetch the cart page data.
  renderCartFromData(window._cartData || {});
}

// Called from PHP-injected window._cartData
window.renderCartFromData = function(cart) {
  const itemsEl  = document.getElementById('cartSidebarItems');
  const footerEl = document.getElementById('cartSidebarFooter');
  const totalEl  = document.getElementById('cartSidebarTotal');

  if (!itemsEl) return;

  const keys = Object.keys(cart || {});
  if (!keys.length) {
    itemsEl.innerHTML = '<p class="cart-empty-msg"><i class="fas fa-shopping-cart" style="font-size:2rem;display:block;margin:0 auto 1rem;color:#ccc"></i>Your cart is empty.</p>';
    if (footerEl) footerEl.style.display = 'none';
    return;
  }

  let total = 0;
  let html  = '';
  keys.forEach(key => {
    const item = cart[key];
    const lineTotal = item.price * item.qty;
    total += lineTotal;
    html += `
      <div class="cart-sidebar-item" data-food-id="${item.food_id}">
        <img class="cart-sidebar-item-img"
             src="${item.image || 'data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' width=\'56\' height=\'56\'%3E%3Crect fill=\'%23f0f0f0\' width=\'56\' height=\'56\'/%3E%3C/svg%3E'}"
             alt="${item.name}">
        <div class="cart-sidebar-item-info">
          <h4>${item.name}</h4>
          <span>৳${parseFloat(item.price).toFixed(2)} × ${item.qty}</span>
        </div>
        <div style="display:flex;flex-direction:column;align-items:flex-end;gap:.5rem">
          <span style="font-weight:700;color:var(--accent)">৳${lineTotal.toFixed(2)}</span>
          <button class="btn-icon btn-icon-delete cart-remove-btn" data-food-id="${item.food_id}" title="Remove">
            <i class="fas fa-trash"></i>
          </button>
        </div>
      </div>`;
  });

  itemsEl.innerHTML = html;
  if (footerEl) footerEl.style.display = '';
  if (totalEl)  totalEl.textContent = `৳${total.toFixed(2)}`;
};

// ============================================================
//  Cart Page: Quantity +/- buttons & Remove
// ============================================================
document.addEventListener('click', async e => {
  // Quantity +
  if (e.target.closest('.qty-btn-plus')) {
    const wrap   = e.target.closest('[data-food-id]');
    const foodId = wrap?.dataset.foodId;
    const qtyEl  = wrap?.querySelector('.qty-display');
    if (!foodId || !qtyEl) return;
    const newQty = parseInt(qtyEl.textContent) + 1;
    await updateQty(foodId, newQty, qtyEl, wrap);
    return;
  }
  // Quantity -
  if (e.target.closest('.qty-btn-minus')) {
    const wrap   = e.target.closest('[data-food-id]');
    const foodId = wrap?.dataset.foodId;
    const qtyEl  = wrap?.querySelector('.qty-display');
    if (!foodId || !qtyEl) return;
    const newQty = Math.max(0, parseInt(qtyEl.textContent) - 1);
    if (newQty === 0) {
      await removeItem(foodId, wrap);
    } else {
      await updateQty(foodId, newQty, qtyEl, wrap);
    }
    return;
  }
  // Remove (cart page and sidebar)
  const removeBtn = e.target.closest('.cart-remove-btn');
  if (removeBtn) {
    const foodId = removeBtn.dataset.foodId;
    const wrap   = removeBtn.closest('[data-food-id]');
    if (foodId) await removeItem(foodId, wrap);
    return;
  }
});

async function updateQty(foodId, newQty, qtyEl, rowEl) {
  const fd = new FormData();
  fd.append('food_id',  foodId);
  fd.append('quantity', newQty);
  const data = await fetchJSON(`${BASE_CART}/includes/api/cart_update.php`, { method: 'POST', body: fd });
  if (data.success) {
    if (newQty === 0 && rowEl) { rowEl.remove(); }
    else if (qtyEl) { qtyEl.textContent = newQty; }
    refreshTotals(data);
  } else {
    showToast(data.message || 'Update failed.', 'error');
  }
}

async function removeItem(foodId, rowEl) {
  const fd = new FormData();
  fd.append('food_id', foodId);
  const data = await fetchJSON(`${BASE_CART}/includes/api/cart_remove.php`, { method: 'POST', body: fd });
  if (data.success) {
    if (rowEl) rowEl.remove();
    refreshTotals(data);
    showToast('Item removed.', 'info');
    // Refresh sidebar if open
    if (sidebar && sidebar.classList.contains('open')) {
      window.renderCartFromData(data.cart || {});
    }
  } else {
    showToast(data.message || 'Remove failed.', 'error');
  }
}

function refreshTotals(data) {
  // Update badge
  const badge = document.getElementById('cartBadge');
  if (badge) badge.textContent = data.cart_count || '';

  // Update page total elements
  const totalEls = document.querySelectorAll('.cart-total-value, #cartSidebarTotal');
  totalEls.forEach(el => {
    el.textContent = `৳${parseFloat(data.cart_total || 0).toFixed(2)}`;
  });

  // Update summary discount / grand total if on cart page
  recalcCartPage(data.cart);
}

function recalcCartPage(cart) {
  if (!cart) return;
  let subtotal = 0;
  Object.values(cart).forEach(item => { subtotal += item.price * item.qty; });
  const subtotalEls = document.querySelectorAll('.cart-subtotal');
  subtotalEls.forEach(el => { el.textContent = `৳${subtotal.toFixed(2)}`; });

  // If empty, show empty state
  if (Object.keys(cart).length === 0) {
    const tableWrap = document.querySelector('.cart-table-wrap');
    const summaryBox = document.querySelector('.cart-summary-box');
    if (tableWrap) tableWrap.innerHTML = `
      <div class="empty-state">
        <i class="fas fa-shopping-cart"></i>
        <h3>Your cart is empty</h3>
        <p>Browse our menu and add some delicious items!</p>
        <a href="${BASE_CART}/menu.php" class="btn-primary">Browse Menu</a>
      </div>`;
  }
}
