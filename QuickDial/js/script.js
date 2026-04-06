/**
 * QuickDial – Main JavaScript
 * File: js/script.js
 */

/* ── Mobile nav toggle ── */
const hamburger = document.getElementById('hamburger');
const navLinks  = document.getElementById('navLinks');
if (hamburger && navLinks) {
  hamburger.addEventListener('click', () => {
    navLinks.classList.toggle('open');
    const spans = hamburger.querySelectorAll('span');
    hamburger.classList.toggle('active');
  });
}

/* ── Auto-dismiss alerts ── */
document.querySelectorAll('.alert[data-dismiss]').forEach(el => {
  setTimeout(() => { el.style.opacity = '0'; setTimeout(() => el.remove(), 300); },
    parseInt(el.dataset.dismiss) || 4000);
});

/* ── Star-rating interactive ── */
function initStarRating() {
  const container = document.querySelector('.star-rating');
  if (!container) return;
  const inputs = container.querySelectorAll('input');
  inputs.forEach(inp => {
    inp.addEventListener('change', () => {
      document.getElementById('ratingValue') &&
        (document.getElementById('ratingValue').value = inp.value);
    });
  });
}
initStarRating();

/* ── Category pill click → search ── */
document.querySelectorAll('.cat-pill').forEach(pill => {
  pill.addEventListener('click', () => {
    const cat = pill.dataset.cat;
    window.location.href = `search.php?category=${encodeURIComponent(cat)}`;
  });
});

/* ── Category card click → search ── */
document.querySelectorAll('.cat-card[data-cat]').forEach(card => {
  card.addEventListener('click', () => {
    window.location.href = `search.php?category=${encodeURIComponent(card.dataset.cat)}`;
  });
});

/* ── Form validation helpers ── */
function showError(inputId, msg) {
  const el = document.getElementById(inputId);
  if (!el) return;
  el.classList.add('is-invalid');
  el.classList.remove('is-valid');
  let fb = el.parentElement.querySelector('.invalid-feedback');
  if (!fb) { fb = document.createElement('div'); fb.className = 'invalid-feedback'; el.parentElement.appendChild(fb); }
  fb.textContent = msg;
  fb.style.display = 'block';
}
function showSuccess(inputId) {
  const el = document.getElementById(inputId);
  if (!el) return;
  el.classList.remove('is-invalid');
  el.classList.add('is-valid');
  const fb = el.parentElement.querySelector('.invalid-feedback');
  if (fb) fb.style.display = 'none';
}
function clearValidation(inputId) {
  const el = document.getElementById(inputId);
  if (!el) return;
  el.classList.remove('is-invalid', 'is-valid');
}

/* ── Register form validation ── */
const registerForm = document.getElementById('registerForm');
if (registerForm) {
  registerForm.addEventListener('submit', function(e) {
    let valid = true;

    const name = document.getElementById('name');
    if (name && name.value.trim().length < 2) {
      showError('name', 'Name must be at least 2 characters.'); valid = false;
    } else if (name) showSuccess('name');

    const email = document.getElementById('email');
    const emailRx = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (email && !emailRx.test(email.value)) {
      showError('email', 'Enter a valid email address.'); valid = false;
    } else if (email) showSuccess('email');

    const phone = document.getElementById('phone');
    if (phone && phone.value && !/^[0-9]{10}$/.test(phone.value)) {
      showError('phone', 'Enter a valid 10-digit phone number.'); valid = false;
    } else if (phone) showSuccess('phone');

    const pass  = document.getElementById('password');
    const pass2 = document.getElementById('confirm_password');
    if (pass && pass.value.length < 6) {
      showError('password', 'Password must be at least 6 characters.'); valid = false;
    } else if (pass) showSuccess('password');
    if (pass && pass2 && pass.value !== pass2.value) {
      showError('confirm_password', 'Passwords do not match.'); valid = false;
    } else if (pass2) showSuccess('confirm_password');

    if (!valid) e.preventDefault();
  });
}

/* ── Login form validation ── */
const loginForm = document.getElementById('loginForm');
if (loginForm) {
  loginForm.addEventListener('submit', function(e) {
    let valid = true;
    const email = document.getElementById('email');
    const emailRx = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (email && !emailRx.test(email.value)) {
      showError('email', 'Enter a valid email.'); valid = false;
    }
    const pass = document.getElementById('password');
    if (pass && pass.value.length < 1) {
      showError('password', 'Password is required.'); valid = false;
    }
    if (!valid) e.preventDefault();
  });
}

/* ── Add business form validation ── */
const bizForm = document.getElementById('bizForm');
if (bizForm) {
  bizForm.addEventListener('submit', function(e) {
    let valid = true;
    const req = ['biz_name','biz_phone','biz_address','biz_city','biz_category'];
    req.forEach(id => {
      const el = document.getElementById(id);
      if (el && el.value.trim() === '') {
        showError(id, 'This field is required.'); valid = false;
      } else if (el) showSuccess(id);
    });
    const phone = document.getElementById('biz_phone');
    if (phone && phone.value && !/^[0-9]{10}$/.test(phone.value)) {
      showError('biz_phone', 'Enter a valid 10-digit phone number.'); valid = false;
    }
    if (!valid) e.preventDefault();
  });
}

/* ── Contact form validation ── */
const contactForm = document.getElementById('contactForm');
if (contactForm) {
  contactForm.addEventListener('submit', function(e) {
    let valid = true;
    const req = ['con_name','con_email','con_message'];
    req.forEach(id => {
      const el = document.getElementById(id);
      if (el && el.value.trim() === '') {
        showError(id, 'This field is required.'); valid = false;
      } else if (el) showSuccess(id);
    });
    if (!valid) e.preventDefault();
  });
}

/* ── Smooth scroll for anchor links ── */
document.querySelectorAll('a[href^="#"]').forEach(a => {
  a.addEventListener('click', e => {
    const target = document.querySelector(a.getAttribute('href'));
    if (target) { e.preventDefault(); target.scrollIntoView({ behavior: 'smooth' }); }
  });
});

/* ── Animate cards on scroll ── */
if ('IntersectionObserver' in window) {
  const obs = new IntersectionObserver(entries => {
    entries.forEach(en => {
      if (en.isIntersecting) {
        en.target.style.opacity = '1';
        en.target.style.transform = 'translateY(0)';
        obs.unobserve(en.target);
      }
    });
  }, { threshold: 0.1 });

  document.querySelectorAll('.business-card, .cat-card, .step-card, .biz-list-card').forEach(el => {
    el.style.opacity = '0';
    el.style.transform = 'translateY(20px)';
    el.style.transition = 'opacity .4s ease, transform .4s ease';
    obs.observe(el);
  });
}

/* ── Admin: confirm before delete ── */
document.querySelectorAll('.confirm-delete').forEach(btn => {
  btn.addEventListener('click', e => {
    if (!confirm('Are you sure you want to delete this item? This cannot be undone.')) {
      e.preventDefault();
    }
  });
});

/* ── Sort select on search page ── */
const sortSelect = document.getElementById('sortSelect');
if (sortSelect) {
  sortSelect.addEventListener('change', () => {
    const url = new URL(window.location.href);
    url.searchParams.set('sort', sortSelect.value);
    window.location.href = url.toString();
  });
}
