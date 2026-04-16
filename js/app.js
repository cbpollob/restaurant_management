/* ============================================================
   RestauRant – app.js  (main global JS)
   ============================================================ */

'use strict';

// ============================================================
//  1. Dark / Light Mode
// ============================================================
(function initTheme() {
  const root   = document.documentElement;
  const btn    = document.getElementById('themeToggle');
  const icon   = document.getElementById('themeIcon');
  const stored = localStorage.getItem('theme') || 'light';

  function applyTheme(theme) {
    root.setAttribute('data-theme', theme);
    if (icon) {
      icon.className = theme === 'dark' ? 'fas fa-sun' : 'fas fa-moon';
    }
    localStorage.setItem('theme', theme);
  }

  applyTheme(stored);

  if (btn) {
    btn.addEventListener('click', () => {
      const current = root.getAttribute('data-theme') || 'light';
      applyTheme(current === 'dark' ? 'light' : 'dark');
    });
  }
})();

// ============================================================
//  2. Sticky Navbar – add 'scrolled' class
// ============================================================
(function initNavbar() {
  const nav    = document.getElementById('mainNavbar');
  const toggle = document.getElementById('navToggle');
  const links  = document.getElementById('navLinks');

  if (!nav) return;

  window.addEventListener('scroll', () => {
    nav.classList.toggle('scrolled', window.scrollY > 60);
  }, { passive: true });

  if (toggle && links) {
    toggle.addEventListener('click', () => {
      links.classList.toggle('open');
      // Animate hamburger
      toggle.classList.toggle('open');
    });
    // Close on link click
    links.querySelectorAll('a').forEach(a => {
      a.addEventListener('click', () => links.classList.remove('open'));
    });
  }
})();

// ============================================================
//  3. Back-to-Top Button
// ============================================================
(function initBackToTop() {
  const btn = document.getElementById('backToTop');
  if (!btn) return;

  window.addEventListener('scroll', () => {
    btn.classList.toggle('visible', window.scrollY > 400);
  }, { passive: true });

  btn.addEventListener('click', () => {
    window.scrollTo({ top: 0, behavior: 'smooth' });
  });
})();

// ============================================================
//  4. Scroll Reveal – IntersectionObserver
// ============================================================
(function initScrollReveal() {
  const items = document.querySelectorAll('.reveal');
  if (!items.length) return;

  const observer = new IntersectionObserver(entries => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        entry.target.classList.add('visible');
        observer.unobserve(entry.target);
      }
    });
  }, { threshold: 0.12, rootMargin: '0px 0px -40px 0px' });

  items.forEach(el => observer.observe(el));
})();

// ============================================================
//  5. Toast Notifications
// ============================================================
const toastIcons = {
  success: 'fas fa-check-circle',
  error:   'fas fa-times-circle',
  warning: 'fas fa-exclamation-triangle',
  info:    'fas fa-info-circle',
};

window.showToast = function(message, type = 'success', duration = 3500) {
  const container = document.getElementById('toastContainer');
  if (!container) return;

  const toast = document.createElement('div');
  toast.className = `toast ${type}`;
  toast.innerHTML = `
    <i class="toast-icon ${toastIcons[type] || toastIcons.info}"></i>
    <span>${message}</span>
  `;
  container.appendChild(toast);

  setTimeout(() => {
    toast.classList.add('removing');
    toast.addEventListener('animationend', () => toast.remove(), { once: true });
  }, duration);
};

// ============================================================
//  6. Loading Spinner helpers
// ============================================================
window.showSpinner = function(buttonEl, originalText) {
  if (!buttonEl) return;
  buttonEl.disabled = true;
  buttonEl.dataset.originalText = buttonEl.innerHTML;
  buttonEl.innerHTML = `<span class="spinner"></span> ${originalText || 'Loading…'}`;
};
window.hideSpinner = function(buttonEl) {
  if (!buttonEl) return;
  buttonEl.disabled = false;
  if (buttonEl.dataset.originalText) {
    buttonEl.innerHTML = buttonEl.dataset.originalText;
  }
};

// ============================================================
//  7. Typing Animation on Hero
// ============================================================
(function initTypingAnimation() {
  const el = document.getElementById('typingText');
  if (!el) return;

  const phrases = [
    'Taste the Extraordinary',
    'Fresh Ingredients, Bold Flavours',
    'Reserve Your Table Today',
    'Order Online, Delivered Fast',
    'Fine Dining at Your Fingertips',
  ];

  let phraseIdx  = 0;
  let charIdx    = 0;
  let deleting   = false;
  let pauseCount = 0;

  function type() {
    const phrase = phrases[phraseIdx];

    if (!deleting) {
      el.textContent = phrase.substring(0, charIdx + 1);
      charIdx++;
      if (charIdx === phrase.length) {
        deleting = true;
        setTimeout(type, 1600);
        return;
      }
    } else {
      el.textContent = phrase.substring(0, charIdx - 1);
      charIdx--;
      if (charIdx === 0) {
        deleting = false;
        phraseIdx = (phraseIdx + 1) % phrases.length;
      }
    }
    setTimeout(type, deleting ? 50 : 90);
  }

  type();
})();

// ============================================================
//  8. Parallax on scroll
// ============================================================
(function initParallax() {
  const parallaxEls = document.querySelectorAll('[data-parallax]');
  if (!parallaxEls.length) return;

  window.addEventListener('scroll', () => {
    const scrolled = window.scrollY;
    parallaxEls.forEach(el => {
      const speed = parseFloat(el.dataset.parallax) || 0.3;
      el.style.backgroundPositionY = `${-scrolled * speed}px`;
    });
  }, { passive: true });
})();

// ============================================================
//  9. Lazy-load images
// ============================================================
(function initLazyImages() {
  const images = document.querySelectorAll('img[data-src]');
  if (!images.length) return;

  const observer = new IntersectionObserver(entries => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        const img = entry.target;
        img.src = img.dataset.src;
        img.removeAttribute('data-src');
        observer.unobserve(img);
      }
    });
  }, { rootMargin: '0px 0px 200px 0px' });

  images.forEach(img => observer.observe(img));
})();

// ============================================================
//  10. Global fetchJSON helper
// ============================================================
window.fetchJSON = async function(url, options = {}) {
  try {
    const res = await fetch(url, {
      headers: { 'X-Requested-With': 'XMLHttpRequest' },
      ...options,
    });
    if (!res.ok) throw new Error(`HTTP error ${res.status}`);
    return await res.json();
  } catch (err) {
    console.error('fetchJSON error:', err);
    return { success: false, message: err.message || 'Network error.' };
  }
};

// ============================================================
//  11. Button Ripple Effect
// ============================================================
document.addEventListener('click', e => {
  const btn = e.target.closest('.btn-ripple');
  if (!btn) return;
  const ripple = document.createElement('span');
  ripple.className = 'ripple-circle';
  const rect = btn.getBoundingClientRect();
  ripple.style.left = `${e.clientX - rect.left - 5}px`;
  ripple.style.top  = `${e.clientY - rect.top  - 5}px`;
  btn.appendChild(ripple);
  ripple.addEventListener('animationend', () => ripple.remove(), { once: true });
});

// ============================================================
//  12. Featured Slider – prev/next buttons
// ============================================================
(function initSlider() {
  const slider = document.getElementById('featuredSlider');
  const prev   = document.getElementById('sliderPrev');
  const next   = document.getElementById('sliderNext');
  if (!slider) return;

  const scrollBy = 290;
  if (prev) prev.addEventListener('click', () => slider.scrollBy({ left: -scrollBy, behavior: 'smooth' }));
  if (next) next.addEventListener('click', () => slider.scrollBy({ left:  scrollBy, behavior: 'smooth' }));
})();
