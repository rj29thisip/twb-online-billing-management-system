/**
 * TWB Billing System — Main JavaScript
 */

document.addEventListener('DOMContentLoaded', () => {

  // ── AUTO-DISMISS ALERTS ──────────────────────────────────────
  document.querySelectorAll('.alert').forEach(alert => {
    setTimeout(() => {
      alert.style.opacity = '0';
      alert.style.transition = 'opacity 0.4s';
      setTimeout(() => alert.remove(), 400);
    }, 5000);
  });

  // ── MOBILE SIDEBAR TOGGLE ─────────────────────────────────────
  const sidebar = document.querySelector('.sidebar');
  const topbarLeft = document.querySelector('.topbar-left');

  if (topbarLeft && window.innerWidth <= 768) {
    const hamburger = document.createElement('button');
    hamburger.className = 'icon-btn';
    hamburger.style.marginRight = '12px';
    hamburger.innerHTML = '<span class="material-icons">menu</span>';
    hamburger.onclick = () => sidebar?.classList.toggle('open');
    topbarLeft.prepend(hamburger);
  }

  document.addEventListener('click', e => {
    if (window.innerWidth <= 768 && sidebar?.classList.contains('open')) {
      if (!sidebar.contains(e.target)) {
        sidebar.classList.remove('open');
      }
    }
  });

  // ── CONFIRM DESTRUCTIVE ACTIONS ───────────────────────────────
  document.querySelectorAll('[data-confirm]').forEach(el => {
    el.addEventListener('click', e => {
      const msg = el.getAttribute('data-confirm') || 'Are you sure?';
      if (!confirm(msg)) e.preventDefault();
    });
  });

  // ── FORM VALIDATION ───────────────────────────────────────────
  document.querySelectorAll('form[novalidate]').forEach(form => {
    form.addEventListener('submit', e => {
      let valid = true;
      form.querySelectorAll('[required]').forEach(field => {
        if (!field.value.trim()) {
          field.classList.add('is-invalid');
          valid = false;
        }
      });
      if (!valid) e.preventDefault();
    });

    form.querySelectorAll('.form-control').forEach(field => {
      field.addEventListener('blur', () => {
        if (field.hasAttribute('required') && !field.value.trim()) {
          field.classList.add('is-invalid');
        } else {
          field.classList.remove('is-invalid');
        }
      });
      field.addEventListener('input', () => field.classList.remove('is-invalid'));
    });
  });

  // ── FIX SELECT DROPDOWN VISIBILITY ───────────────────────────
  // Ensure all <select> elements have correct colors
  // (Handles browsers that ignore CSS :option selectors)
  document.querySelectorAll('select.form-control').forEach(sel => {
    // Force the select itself to be readable
    sel.style.backgroundColor = '#202940';
    sel.style.color = 'rgba(255,255,255,0.87)';

    // On change, keep styling
    sel.addEventListener('change', function() {
      this.style.color = 'rgba(255,255,255,0.87)';
    });
  });

  // ── MODAL CLOSE ON OVERLAY CLICK ─────────────────────────────
  document.querySelectorAll('.modal-overlay').forEach(overlay => {
    overlay.addEventListener('click', function(e) {
      if (e.target === this) {
        this.classList.remove('open');
      }
    });
  });

});

/**
 * Helper: show a toast message
 */
function showToast(message, type = 'success') {
  const toast = document.createElement('div');
  toast.className = `alert alert-${type}`;
  toast.style.cssText = 'position:fixed;top:20px;right:20px;z-index:9998;border-radius:10px;max-width:380px;box-shadow:0 8px 32px rgba(0,0,0,0.4);';
  toast.innerHTML = `<span class="material-icons" style="font-size:18px">${type === 'success' ? 'check_circle' : 'error'}</span>${message}`;
  document.body.appendChild(toast);
  setTimeout(() => {
    toast.style.opacity = '0';
    toast.style.transition = 'opacity 0.4s';
    setTimeout(() => toast.remove(), 400);
  }, 4000);
}
