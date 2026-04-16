/* ============================================================
   RestauRant – auth.js  (login & register pages)
   ============================================================ */

'use strict';

// ============================================================
//  Show / hide password toggle
// ============================================================
document.querySelectorAll('.toggle-password').forEach(btn => {
  btn.addEventListener('click', () => {
    const input = btn.closest('.input-group').querySelector('input');
    if (!input) return;
    const show = input.type === 'password';
    input.type = show ? 'text' : 'password';
    btn.querySelector('i').className = show ? 'fas fa-eye-slash' : 'fas fa-eye';
  });
});

// ============================================================
//  Validate helpers
// ============================================================
function isValidEmail(email) {
  return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email.trim());
}
function isValidPhone(phone) {
  return /^[\d\s\-\+\(\)]{7,20}$/.test(phone.trim());
}

function setError(fieldId, msg) {
  const el = document.getElementById(fieldId + '_error');
  if (el) { el.textContent = msg; el.style.display = msg ? 'block' : 'none'; }
}
function clearErrors(formEl) {
  formEl.querySelectorAll('.field-error').forEach(el => {
    el.textContent = ''; el.style.display = 'none';
  });
}

// ============================================================
//  Login Form
// ============================================================
const loginForm = document.getElementById('loginForm');
if (loginForm) {
  loginForm.addEventListener('submit', async e => {
    e.preventDefault();
    clearErrors(loginForm);

    const email    = loginForm.email.value.trim();
    const password = loginForm.password.value;
    let   valid    = true;

    if (!isValidEmail(email)) {
      setError('email', 'Please enter a valid email address.');
      valid = false;
    }
    if (password.length < 1) {
      setError('password', 'Password is required.');
      valid = false;
    }
    if (!valid) return;

    const submitBtn = loginForm.querySelector('[type="submit"]');
    showSpinner(submitBtn, 'Signing in…');

    const fd = new FormData(loginForm);
    const data = await fetchJSON(loginForm.action || window.location.href, {
      method: 'POST', body: fd,
    });

    hideSpinner(submitBtn);

    if (data.success) {
      showToast('Login successful! Redirecting…', 'success');
      setTimeout(() => {
        window.location.href = data.redirect || 'index.php';
      }, 900);
    } else {
      showToast(data.message || 'Login failed.', 'error');
      const pwField = loginForm.querySelector('#password');
      if (pwField) { pwField.value = ''; pwField.classList.add('anim-shake'); }
    }
  });
}

// ============================================================
//  Register Form
// ============================================================
const registerForm = document.getElementById('registerForm');
if (registerForm) {
  registerForm.addEventListener('submit', async e => {
    e.preventDefault();
    clearErrors(registerForm);

    const name     = registerForm.name.value.trim();
    const email    = registerForm.email.value.trim();
    const phone    = registerForm.phone.value.trim();
    const password = registerForm.password.value;
    const confirm  = registerForm.confirm_password.value;
    let   valid    = true;

    if (name.length < 2) {
      setError('name', 'Name must be at least 2 characters.');
      valid = false;
    }
    if (!isValidEmail(email)) {
      setError('email', 'Please enter a valid email address.');
      valid = false;
    }
    if (phone && !isValidPhone(phone)) {
      setError('phone', 'Please enter a valid phone number.');
      valid = false;
    }
    if (password.length < 8) {
      setError('password', 'Password must be at least 8 characters.');
      valid = false;
    }
    if (password !== confirm) {
      setError('confirm_password', 'Passwords do not match.');
      valid = false;
    }
    if (!valid) return;

    const submitBtn = registerForm.querySelector('[type="submit"]');
    showSpinner(submitBtn, 'Creating account…');

    const fd = new FormData(registerForm);
    const data = await fetchJSON(registerForm.action || window.location.href, {
      method: 'POST', body: fd,
    });

    hideSpinner(submitBtn);

    if (data.success) {
      showToast('Account created! Redirecting to login…', 'success');
      setTimeout(() => {
        window.location.href = data.redirect || 'login.php';
      }, 1200);
    } else {
      showToast(data.message || 'Registration failed.', 'error');
    }
  });
}

// ============================================================
//  Password strength indicator
// ============================================================
const pwInput = document.getElementById('password');
const pwStrength = document.getElementById('passwordStrength');
if (pwInput && pwStrength) {
  pwInput.addEventListener('input', () => {
    const pw  = pwInput.value;
    let score = 0;
    if (pw.length >= 8)   score++;
    if (/[A-Z]/.test(pw)) score++;
    if (/[0-9]/.test(pw)) score++;
    if (/[^A-Za-z0-9]/.test(pw)) score++;

    const levels = ['', 'Weak', 'Fair', 'Good', 'Strong'];
    const colors = ['', '#dc3545', '#ffc107', '#17a2b8', '#28a745'];
    pwStrength.textContent = score ? levels[score] : '';
    pwStrength.style.color = colors[score] || '';
  });
}
