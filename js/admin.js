/* ============================================================
   RestauRant – admin.js
   ============================================================ */

'use strict';

// ============================================================
//  Sidebar Toggle for mobile
// ============================================================
const sidebarToggleBtn = document.getElementById('sidebarToggleBtn');
const adminSidebar     = document.getElementById('adminSidebar');

if (sidebarToggleBtn && adminSidebar) {
  sidebarToggleBtn.addEventListener('click', () => {
    adminSidebar.classList.toggle('open');
  });
  // Close sidebar on outside click (mobile)
  document.addEventListener('click', e => {
    if (window.innerWidth <= 992 &&
        adminSidebar.classList.contains('open') &&
        !adminSidebar.contains(e.target) &&
        !sidebarToggleBtn.contains(e.target)) {
      adminSidebar.classList.remove('open');
    }
  });
}

// ============================================================
//  Chart.js – Dashboard Charts
// ============================================================
function initDashboardCharts() {
  const ordersCtx  = document.getElementById('ordersChart')?.getContext('2d');
  const revenueCtx = document.getElementById('revenueChart')?.getContext('2d');
  const catCtx     = document.getElementById('categoryChart')?.getContext('2d');

  const labels   = window._chartLabels   || ['Mon','Tue','Wed','Thu','Fri','Sat','Sun'];
  const ordersD  = window._ordersData    || [5,8,6,10,12,15,9];
  const revenueD = window._revenueData   || [2500,4000,3000,5000,6000,7500,4500];
  const catNames = window._catNames      || ['Starters','Main','Desserts','Drinks','Fast Food','Seafood'];
  const catData  = window._catData       || [12,30,15,20,18,5];

  const gridColor   = getComputedStyle(document.documentElement).getPropertyValue('--border-color').trim() || '#e5e7eb';
  const textColor   = getComputedStyle(document.documentElement).getPropertyValue('--text-secondary').trim() || '#555';

  if (ordersCtx) {
    new Chart(ordersCtx, {
      type: 'bar',
      data: {
        labels,
        datasets: [{
          label: 'Orders',
          data: ordersD,
          backgroundColor: 'rgba(255,107,53,.75)',
          borderColor:     '#ff6b35',
          borderWidth: 2,
          borderRadius: 6,
        }],
      },
      options: {
        responsive: true, maintainAspectRatio: true,
        plugins: { legend: { display: false } },
        scales: {
          x: { grid: { color: gridColor }, ticks: { color: textColor } },
          y: { grid: { color: gridColor }, ticks: { color: textColor }, beginAtZero: true },
        },
      },
    });
  }

  if (revenueCtx) {
    new Chart(revenueCtx, {
      type: 'line',
      data: {
        labels,
        datasets: [{
          label: 'Revenue (৳)',
          data: revenueD,
          borderColor: '#28a745',
          backgroundColor: 'rgba(40,167,69,.12)',
          borderWidth: 2,
          tension: 0.4,
          fill: true,
          pointBackgroundColor: '#28a745',
          pointRadius: 4,
        }],
      },
      options: {
        responsive: true, maintainAspectRatio: true,
        plugins: { legend: { display: false } },
        scales: {
          x: { grid: { color: gridColor }, ticks: { color: textColor } },
          y: { grid: { color: gridColor }, ticks: { color: textColor }, beginAtZero: true },
        },
      },
    });
  }

  if (catCtx) {
    new Chart(catCtx, {
      type: 'doughnut',
      data: {
        labels: catNames,
        datasets: [{
          data: catData,
          backgroundColor: ['#ff6b35','#28a745','#17a2b8','#ffc107','#6610f2','#e83e8c'],
          borderWidth: 0,
          hoverOffset: 8,
        }],
      },
      options: {
        responsive: true, maintainAspectRatio: true,
        plugins: { legend: { position: 'bottom', labels: { color: textColor, padding: 12, font: { size: 12 } } } },
        cutout: '65%',
      },
    });
  }
}

// Wait for Chart.js to load
if (typeof Chart !== 'undefined') {
  initDashboardCharts();
} else {
  window.addEventListener('load', () => {
    if (typeof Chart !== 'undefined') initDashboardCharts();
  });
}

// ============================================================
//  Delete Confirmation Modal
// ============================================================
const deleteModal    = document.getElementById('deleteModal');
const cancelDelBtn   = document.getElementById('cancelDeleteBtn');
const confirmDelBtn  = document.getElementById('confirmDeleteBtn');
let   pendingDeleteForm = null;

document.addEventListener('click', e => {
  const btn = e.target.closest('.delete-trigger-btn');
  if (!btn) return;
  e.preventDefault();

  pendingDeleteForm = btn.closest('form') || null;
  const name = btn.dataset.name || 'this item';
  const msgEl = deleteModal?.querySelector('.delete-item-name');
  if (msgEl) msgEl.textContent = name;
  if (deleteModal) deleteModal.classList.add('open');
});

if (cancelDelBtn) {
  cancelDelBtn.addEventListener('click', () => {
    if (deleteModal) deleteModal.classList.remove('open');
    pendingDeleteForm = null;
  });
}
if (confirmDelBtn) {
  confirmDelBtn.addEventListener('click', () => {
    if (pendingDeleteForm) {
      pendingDeleteForm.submit();
    }
    if (deleteModal) deleteModal.classList.remove('open');
  });
}

// ============================================================
//  Order Status Update
// ============================================================
document.querySelectorAll('.order-status-select').forEach(sel => {
  sel.addEventListener('change', async () => {
    const orderId  = sel.dataset.orderId;
    const newStatus = sel.value;
    const fd = new FormData();
    fd.append('order_id', orderId);
    fd.append('status',   newStatus);

    const data = await fetchJSON('orders_update.php', { method: 'POST', body: fd });
    if (data && data.success) {
      showToast('Order status updated.', 'success');
    } else {
      showToast('Update failed.', 'error');
    }
  });
});

// ============================================================
//  Image preview on file select
// ============================================================
const foodImageInput = document.getElementById('foodImageInput');
const imagePreview   = document.getElementById('imagePreview');
if (foodImageInput && imagePreview) {
  foodImageInput.addEventListener('change', () => {
    const file = foodImageInput.files[0];
    if (!file) return;
    const reader = new FileReader();
    reader.onload = e => {
      let img = imagePreview.querySelector('img');
      if (!img) {
        img = document.createElement('img');
        imagePreview.appendChild(img);
      }
      img.src = e.target.result;
      imagePreview.querySelector('i')?.remove();
      imagePreview.querySelector('p')?.remove();
    };
    reader.readAsDataURL(file);
  });
}

// ============================================================
//  Client-side table search filter
// ============================================================
const tableSearchInput = document.getElementById('tableSearch');
if (tableSearchInput) {
  tableSearchInput.addEventListener('input', () => {
    const q   = tableSearchInput.value.toLowerCase();
    const rows = document.querySelectorAll('.searchable-row');
    rows.forEach(row => {
      const text = row.textContent.toLowerCase();
      row.style.display = text.includes(q) ? '' : 'none';
    });
  });
}
