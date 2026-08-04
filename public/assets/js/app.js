/* বাগানবন্ধু — nav, theme, scroll reveal, form UX.
 * Vanilla ES6. No framework, no bundler, no CDN.
 * Everything here is progressive enhancement: every flow works without it.
 */
(function () {
  'use strict';

  /* ── Mobile nav ─────────────────────────────────────────────────────── */
  var toggle = document.querySelector('[data-nav-toggle]');
  var nav = document.getElementById('primary-nav');

  if (toggle && nav) {
    toggle.addEventListener('click', function () {
      var open = nav.dataset.open === 'true';
      nav.dataset.open = open ? 'false' : 'true';
      toggle.setAttribute('aria-expanded', open ? 'false' : 'true');
    });

    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape' && nav.dataset.open === 'true') {
        nav.dataset.open = 'false';
        toggle.setAttribute('aria-expanded', 'false');
        toggle.focus();
      }
    });
  }

  /* ── Theme toggle ───────────────────────────────────────────────────────
   * Writes a cookie rather than localStorage so PHP can set data-theme on
   * <html> during render — no flash of the wrong palette on the next page.
   */
  var themeBtn = document.querySelector('[data-theme-toggle]');

  if (themeBtn) {
    themeBtn.addEventListener('click', function () {
      var root = document.documentElement;
      var next = root.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';

      root.setAttribute('data-theme', next);
      document.cookie = 'gb_theme=' + next + ';path=/;max-age=31536000;samesite=Lax';
      themeBtn.setAttribute('aria-label', next === 'dark' ? 'দিনের রঙে দেখুন' : 'রাতের রঙে দেখুন');
      themeBtn.textContent = next === 'dark' ? '☀' : '☾';
    });
  }

  /* ── Scroll reveal, 60ms stagger, motion-safe only ──────────────────── */
  var reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  var revealables = document.querySelectorAll('.reveal');

  if (!reduceMotion && revealables.length && 'IntersectionObserver' in window) {
    var observer = new IntersectionObserver(function (entries) {
      entries.forEach(function (entry, i) {
        if (!entry.isIntersecting) return;
        var el = entry.target;
        setTimeout(function () { el.dataset.visible = 'true'; }, i * 60);
        observer.unobserve(el);
      });
    }, { rootMargin: '0px 0px -8% 0px', threshold: 0.08 });

    revealables.forEach(function (el) { observer.observe(el); });
  } else {
    revealables.forEach(function (el) { el.dataset.visible = 'true'; });
  }

  /* ── Phone input: digits only, capped at 11 ─────────────────────────── */
  document.querySelectorAll('[data-phone-input]').forEach(function (input) {
    input.addEventListener('input', function () {
      var digits = input.value.replace(/\D+/g, '');
      if (digits.length > 11) digits = digits.slice(0, 11);
      if (input.value !== digits) input.value = digits;
    });
  });

  /* ── Double-submit guard ────────────────────────────────────────────── */
  document.querySelectorAll('form[data-guard]').forEach(function (form) {
    form.addEventListener('submit', function () {
      var btn = form.querySelector('button[type="submit"], input[type="submit"]');
      if (!btn || btn.dataset.busy === 'true') return;

      btn.dataset.busy = 'true';
      btn.dataset.label = btn.textContent;
      btn.textContent = 'একটু অপেক্ষা করুন…';
      btn.setAttribute('aria-disabled', 'true');

      // Re-enable if the browser restores the page from bfcache.
      window.addEventListener('pageshow', function () {
        btn.dataset.busy = 'false';
        btn.textContent = btn.dataset.label || btn.textContent;
        btn.removeAttribute('aria-disabled');
      }, { once: true });
    });
  });

  /* ── Confirm-before-destroy ─────────────────────────────────────────── */
  document.querySelectorAll('form[data-confirm]').forEach(function (form) {
    form.addEventListener('submit', function (e) {
      if (!window.confirm(form.dataset.confirm)) e.preventDefault();
    });
  });

  /* ── Filter forms auto-submit on change (still works without JS) ────── */
  document.querySelectorAll('form[data-auto-submit] select').forEach(function (select) {
    select.addEventListener('change', function () { select.form.submit(); });
  });

  /* ── Character counter ──────────────────────────────────────────────── */
  document.querySelectorAll('[data-counter]').forEach(function (field) {
    var target = document.getElementById(field.dataset.counter);
    if (!target) return;

    var max = parseInt(field.getAttribute('maxlength') || '0', 10);
    var toBn = function (n) {
      return String(n).replace(/\d/g, function (d) { return '০১২৩৪৫৬৭৮৯'[d]; });
    };
    var update = function () {
      target.textContent = toBn(field.value.length) + (max ? ' / ' + toBn(max) : '') + ' অক্ষর';
    };

    field.addEventListener('input', update);
    update();
  });
})();
