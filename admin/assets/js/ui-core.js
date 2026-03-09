(function () {
  const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  function animateIn(selector, opts) {
    if (prefersReducedMotion || !window.anime) return;
    const nodes = typeof selector === 'string' ? document.querySelectorAll(selector) : selector;
    if (!nodes || !nodes.length) return;
    window.anime(Object.assign({
      targets: nodes,
      opacity: [0, 1],
      translateY: [14, 0],
      easing: 'easeOutQuad',
      duration: 560,
      delay: window.anime.stagger(55)
    }, opts || {}));
  }

  function countUp(el, target, prefix) {
    if (!el) return;
    const safeTarget = Number(target || 0);
    if (prefersReducedMotion || !window.anime) {
      el.textContent = (prefix || '') + safeTarget.toLocaleString();
      return;
    }

    const obj = { val: 0 };
    window.anime({
      targets: obj,
      val: safeTarget,
      round: 1,
      easing: 'easeOutExpo',
      duration: 900,
      update: function () {
        el.textContent = (prefix || '') + obj.val.toLocaleString();
      }
    });
  }

  function showToast(message, type) {
    const stack = document.getElementById('pcToastStack') || (function () {
      const node = document.createElement('div');
      node.id = 'pcToastStack';
      node.className = 'pc-toast-stack';
      document.body.appendChild(node);
      return node;
    })();

    const toast = document.createElement('div');
    toast.className = 'pc-toast pc-toast-' + (type || 'info');
    toast.setAttribute('role', 'status');
    toast.textContent = message;
    stack.appendChild(toast);

    if (!prefersReducedMotion && window.anime) {
      window.anime({ targets: toast, translateX: [30, 0], opacity: [0, 1], duration: 280, easing: 'easeOutCubic' });
    }

    setTimeout(function () {
      if (!prefersReducedMotion && window.anime) {
        window.anime({
          targets: toast,
          translateX: [0, 30],
          opacity: [1, 0],
          duration: 250,
          easing: 'easeInCubic',
          complete: function () { toast.remove(); }
        });
      } else {
        toast.remove();
      }
    }, 2800);
  }

  function ensureConfirmModal() {
    let backdrop = document.getElementById('pcConfirmBackdrop');
    if (backdrop) return backdrop;

    backdrop = document.createElement('div');
    backdrop.id = 'pcConfirmBackdrop';
    backdrop.className = 'pc-confirm-backdrop';
    backdrop.innerHTML = '' +
      '<div class="pc-confirm" role="dialog" aria-modal="true" aria-labelledby="pcConfirmTitle">' +
      '  <h3 id="pcConfirmTitle" style="font-size:1.1rem;font-weight:700;margin-bottom:.35rem;">Confirm Action</h3>' +
      '  <p id="pcConfirmMessage" style="color:var(--pc-text-muted);margin-bottom:.9rem;">Are you sure?</p>' +
      '  <div style="display:flex;justify-content:flex-end;gap:.5rem;">' +
      '    <button type="button" class="pc-btn pc-btn-muted" id="pcConfirmCancel">Cancel</button>' +
      '    <button type="button" class="pc-btn pc-btn-danger" id="pcConfirmOk">Confirm</button>' +
      '  </div>' +
      '</div>';

    document.body.appendChild(backdrop);
    return backdrop;
  }

  function confirmAction(message, onConfirm) {
    const backdrop = ensureConfirmModal();
    const msg = backdrop.querySelector('#pcConfirmMessage');
    const ok = backdrop.querySelector('#pcConfirmOk');
    const cancel = backdrop.querySelector('#pcConfirmCancel');

    msg.textContent = message || 'Are you sure?';
    backdrop.classList.add('open');

    const close = function () { backdrop.classList.remove('open'); };
    ok.onclick = function () { close(); if (typeof onConfirm === 'function') onConfirm(); };
    cancel.onclick = close;
    backdrop.onclick = function (e) { if (e.target === backdrop) close(); };
  }

  function applyTheme(theme) {
    const html = document.documentElement;
    const resolved = theme === 'auto'
      ? (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light')
      : theme;
    html.setAttribute('data-theme', resolved);
    localStorage.setItem('pcTheme', theme || resolved);
    const icon = document.getElementById('themeIcon');
    if (icon) icon.className = 'fas ' + (resolved === 'dark' ? 'fa-sun' : 'fa-moon');
  }

  function initTheme() {
    const saved = localStorage.getItem('pcTheme');
    if (saved) applyTheme(saved);
  }

  function bindDeleteButtons() {
    document.querySelectorAll('[data-confirm]').forEach(function (btn) {
      btn.addEventListener('click', function (e) {
        e.preventDefault();
        const href = btn.getAttribute('href');
        const msg = btn.getAttribute('data-confirm') || 'Proceed?';
        confirmAction(msg, function () {
          if (href) {
            window.location.href = href;
          } else if (btn.form) {
            btn.form.submit();
          }
        });
      });
    });
  }

  window.PCUI = {
    animateIn,
    countUp,
    showToast,
    confirmAction,
    applyTheme,
    bindDeleteButtons,
    init: function () {
      initTheme();
      bindDeleteButtons();
      animateIn('.pc-animate, .pc-card, .pc-stat-card');
    }
  };

  document.addEventListener('DOMContentLoaded', function () {
    window.PCUI.init();
  });
})();
