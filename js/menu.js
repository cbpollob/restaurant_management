/* ============================================================
   RestauRant – menu.js
   ============================================================ */

'use strict';

const BASE = window.APP_BASE || '';

// ============================================================
//  Render star rating HTML
// ============================================================
function starsHtml(avg, count) {
  let html = '<div class="star-rating">';
  for (let i = 1; i <= 5; i++) {
    html += `<i class="star fas fa-star ${i <= Math.round(avg) ? 'filled' : ''}"></i>`;
  }
  html += `<span class="rating-count">(${count})</span></div>`;
  return html;
}

// ============================================================
//  Render food card HTML
// ============================================================
function renderFoodCard(item) {
  const avg   = parseFloat(item.avg_rating || 0).toFixed(1);
  const count = item.review_count || 0;
  return `
    <div class="food-card hover-lift" data-id="${item.id}">
      <div class="food-card-img-wrap">
        <img class="food-card-img" data-src="${item.image_url || ''}"
             src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='400' height='200'%3E%3Crect fill='%23f0f0f0' width='400' height='200'/%3E%3C/svg%3E"
             alt="${item.name}" loading="lazy">
        <span class="food-card-badge">${item.category_name || ''}</span>
        ${item.featured == 1 ? '<span class="food-card-badge featured" style="top:12px;left:auto;right:52px"><i class="fas fa-star"></i> Featured</span>' : ''}
        <button class="wishlist-btn ${item.in_wishlist ? 'active' : ''}"
                data-food-id="${item.id}"
                aria-label="Toggle wishlist">
          <i class="${item.in_wishlist ? 'fas' : 'far'} fa-heart"></i>
        </button>
      </div>
      <div class="food-card-body">
        <div class="food-card-category">${item.category_name || ''}</div>
        <div class="food-card-name">${item.name}</div>
        <div class="food-card-desc">${item.description || ''}</div>
        ${starsHtml(avg, count)}
        <div class="food-card-footer">
          <span class="food-price">${parseFloat(item.price).toFixed(2)}</span>
          <button class="btn-primary btn-sm add-to-cart-btn" data-food-id="${item.id}">
            <i class="fas fa-plus"></i> Add
          </button>
        </div>
      </div>
    </div>`;
}

// ============================================================
//  Update food grid
// ============================================================
function updateGrid(items) {
  const grid = document.getElementById('foodGrid');
  if (!grid) return;

  if (!items || items.length === 0) {
    grid.innerHTML = `
      <div class="empty-state" style="grid-column:1/-1">
        <i class="fas fa-utensils"></i>
        <h3>No items found</h3>
        <p>Try a different search or category.</p>
      </div>`;
    return;
  }
  grid.innerHTML = items.map(renderFoodCard).join('');
  // Re-init lazy images
  if (window.IntersectionObserver) {
    const imgs = grid.querySelectorAll('img[data-src]');
    const obs  = new IntersectionObserver(entries => {
      entries.forEach(e => {
        if (e.isIntersecting) {
          e.target.src = e.target.dataset.src;
          e.target.removeAttribute('data-src');
          obs.unobserve(e.target);
        }
      });
    }, { rootMargin: '0px 0px 200px 0px' });
    imgs.forEach(img => obs.observe(img));
  }
}

// ============================================================
//  Live Search with debounce
// ============================================================
let searchTimer;
const searchInput = document.getElementById('menuSearch');
if (searchInput) {
  searchInput.addEventListener('input', () => {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(async () => {
      const q = searchInput.value.trim();
      if (q === '') {
        // Reset to current category
        const active = document.querySelector('.category-filter-btn.active');
        const catId  = active ? active.dataset.categoryId : '0';
        loadByCategory(catId);
        return;
      }
      const data = await fetchJSON(`${BASE}/includes/api/search_food.php?q=${encodeURIComponent(q)}`);
      if (data.success) updateGrid(data.data);
    }, 320);
  });
}

// ============================================================
//  Category Filter Buttons
// ============================================================
function loadByCategory(categoryId) {
  fetchJSON(`${BASE}/includes/api/filter_food.php?category_id=${categoryId}`)
    .then(data => { if (data.success) updateGrid(data.data); });
}

document.querySelectorAll('.category-filter-btn').forEach(btn => {
  btn.addEventListener('click', () => {
    document.querySelectorAll('.category-filter-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    const catId = btn.dataset.categoryId || '0';
    if (searchInput) searchInput.value = '';
    loadByCategory(catId);
  });
});

// ============================================================
//  Add to Cart
// ============================================================
document.addEventListener('click', async e => {
  const btn = e.target.closest('.add-to-cart-btn');
  if (!btn) return;

  const foodId = btn.dataset.foodId;
  if (!foodId) return;

  const original = btn.innerHTML;
  btn.disabled = true;
  btn.innerHTML = '<span class="spinner"></span>';

  const fd = new FormData();
  fd.append('food_id',  foodId);
  fd.append('quantity', '1');

  const data = await fetchJSON(`${BASE}/includes/api/cart_add.php`, { method: 'POST', body: fd });

  btn.disabled = false;
  btn.innerHTML = original;

  if (data.success) {
    showToast(`Added to cart!`, 'success');
    // Update cart badge
    const badge = document.getElementById('cartBadge');
    if (badge) { badge.textContent = data.cart_count || ''; }
  } else {
    showToast(data.message || 'Failed to add item.', 'error');
  }
});

// ============================================================
//  Wishlist Toggle
// ============================================================
document.addEventListener('click', async e => {
  const btn = e.target.closest('.wishlist-btn');
  if (!btn) return;

  const foodId = btn.dataset.foodId;
  if (!foodId) return;

  const fd = new FormData();
  fd.append('food_id', foodId);

  const data = await fetchJSON(`${BASE}/includes/api/wishlist_toggle.php`, { method: 'POST', body: fd });

  if (data.success) {
    const inWishlist = data.in_wishlist;
    btn.classList.toggle('active', inWishlist);
    btn.querySelector('i').className = inWishlist ? 'fas fa-heart' : 'far fa-heart';
    showToast(inWishlist ? 'Added to wishlist!' : 'Removed from wishlist.', inWishlist ? 'success' : 'info');
  } else {
    showToast(data.message || 'Please log in.', 'warning');
  }
});
