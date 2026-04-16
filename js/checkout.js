/* ============================================================
   RestauRant – checkout.js
   ============================================================ */

'use strict';

const BASE_CO = window.APP_BASE || '';

// ============================================================
//  Payment Method Selection
// ============================================================
let selectedMethod = '';
document.querySelectorAll('.payment-method-card').forEach(card => {
  card.addEventListener('click', () => {
    document.querySelectorAll('.payment-method-card').forEach(c => c.classList.remove('selected'));
    card.classList.add('selected');
    selectedMethod = card.dataset.method;

    // Show/hide method-specific fields
    document.querySelectorAll('.payment-fields').forEach(f => f.style.display = 'none');
    const fields = document.getElementById(`fields_${selectedMethod}`);
    if (fields) fields.style.display = 'block';
  });
});

// ============================================================
//  Order Type Toggle (Dine-in / Delivery)
// ============================================================
document.querySelectorAll('.order-type-btn').forEach(btn => {
  btn.addEventListener('click', () => {
    document.querySelectorAll('.order-type-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    const type = btn.dataset.type;
    const deliveryFields = document.getElementById('deliveryFields');
    if (deliveryFields) {
      deliveryFields.style.display = type === 'delivery' ? 'block' : 'none';
    }
    const typeInput = document.getElementById('orderTypeInput');
    if (typeInput) typeInput.value = type;
  });
});

// ============================================================
//  Promo Code Application
// ============================================================
const promoBtn   = document.getElementById('applyPromoBtn');
const promoInput = document.getElementById('promoCodeInput');
let   promoApplied = false;
let   discountAmount = 0;

if (promoBtn) {
  promoBtn.addEventListener('click', async () => {
    const code       = promoInput?.value.trim();
    const subtotalEl = document.getElementById('checkoutSubtotal');
    const subtotal   = parseFloat(subtotalEl?.dataset.value || subtotalEl?.textContent.replace(/[^\d.]/g,'')) || 0;

    if (!code) { showToast('Enter a promo code.', 'warning'); return; }

    showSpinner(promoBtn, 'Applying…');
    const fd = new FormData();
    fd.append('promo_code',  code);
    fd.append('order_total', subtotal);

    const data = await fetchJSON(`${BASE_CO}/includes/api/apply_promo.php`, { method: 'POST', body: fd });
    hideSpinner(promoBtn);

    if (data.success) {
      discountAmount = data.discount_amount;
      promoApplied   = true;

      const discountEl  = document.getElementById('discountRow');
      const discountVal = document.getElementById('discountValue');
      const grandTotal  = document.getElementById('grandTotal');

      if (discountEl)  discountEl.style.display  = '';
      if (discountVal) discountVal.textContent    = `- ৳${discountAmount.toFixed(2)}`;
      if (grandTotal)  grandTotal.textContent     = `৳${data.new_total.toFixed(2)}`;

      // Store for order creation
      window._promoCode    = code;
      window._discountAmt  = discountAmount;
      window._newTotal     = data.new_total;

      showToast(`${data.message}`, 'success');
      if (promoInput) promoInput.disabled = true;
      promoBtn.textContent = '✓ Applied';
      promoBtn.disabled    = true;
    } else {
      showToast(data.message || 'Invalid promo code.', 'error');
    }
  });
}

// ============================================================
//  Place Order button
// ============================================================
const placeOrderBtn = document.getElementById('placeOrderBtn');
if (placeOrderBtn) {
  placeOrderBtn.addEventListener('click', async () => {
    if (!selectedMethod) {
      showToast('Please select a payment method.', 'warning');
      return;
    }

    const type    = document.getElementById('orderTypeInput')?.value || 'delivery';
    const address = document.getElementById('deliveryAddress')?.value.trim() || '';
    const phone   = document.getElementById('deliveryPhone')?.value.trim()   || '';

    if (type === 'delivery' && (!address || !phone)) {
      showToast('Delivery address and phone are required.', 'warning');
      return;
    }

    showSpinner(placeOrderBtn, 'Placing order…');

    // Step 1: Create order
    const orderFd = new FormData();
    orderFd.append('type',       type);
    orderFd.append('address',    address);
    orderFd.append('phone',      phone);
    orderFd.append('promo_code', window._promoCode || '');

    const orderData = await fetchJSON(`${BASE_CO}/includes/api/order_create.php`, {
      method: 'POST', body: orderFd,
    });

    if (!orderData.success) {
      hideSpinner(placeOrderBtn);
      showToast(orderData.message || 'Order creation failed.', 'error');
      return;
    }

    // Step 2: Simulate payment
    const payFd = new FormData();
    payFd.append('order_id', orderData.order_id);
    payFd.append('method',   selectedMethod);
    payFd.append('amount',   window._newTotal || orderData.total);

    const payData = await fetchJSON(`${BASE_CO}/includes/api/payment_simulate.php`, {
      method: 'POST', body: payFd,
    });

    hideSpinner(placeOrderBtn);

    // Step 3: Show result modal
    showPaymentModal(payData.success, orderData.order_code, payData.txn_ref);
  });
}

// ============================================================
//  Payment Result Modal
// ============================================================
function showPaymentModal(success, orderCode, txnRef) {
  const modal = document.getElementById('paymentModal');
  if (!modal) {
    // Fallback
    if (success) {
      showToast('Payment successful! Redirecting…', 'success');
      setTimeout(() => { window.location.href = `${BASE_CO}/order-history.php`; }, 2000);
    } else {
      showToast('Payment failed. Please try again.', 'error');
    }
    return;
  }

  const iconEl    = modal.querySelector('.modal-icon');
  const titleEl   = modal.querySelector('.modal-title');
  const msgEl     = modal.querySelector('.modal-msg');
  const actionBtn = modal.querySelector('.modal-action-btn');

  if (success) {
    if (iconEl)  iconEl.innerHTML = `
      <svg class="payment-success-anim" viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg">
        <circle class="circle-bg" cx="50" cy="50" r="45"/>
        <circle class="circle-fg" cx="50" cy="50" r="45"/>
        <polyline class="check-mark" points="28,52 43,67 72,37"/>
      </svg>`;
    if (titleEl) titleEl.textContent = 'Payment Successful!';
    if (msgEl)   msgEl.innerHTML     = `Order <strong>${orderCode}</strong> confirmed.<br>Ref: <code>${txnRef}</code>`;
    if (actionBtn) {
      actionBtn.textContent = 'View My Orders';
      actionBtn.onclick = () => { window.location.href = `${BASE_CO}/order-history.php`; };
    }
  } else {
    if (iconEl)  iconEl.innerHTML = `<i class="fas fa-times-circle payment-fail-anim"></i>`;
    if (titleEl) titleEl.textContent = 'Payment Failed';
    if (msgEl)   msgEl.textContent   = 'Your payment could not be processed. Please try again.';
    if (actionBtn) {
      actionBtn.textContent = 'Try Again';
      actionBtn.onclick = () => { modal.classList.remove('open'); };
    }
  }

  modal.classList.add('open');
}
