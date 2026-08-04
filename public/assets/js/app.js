/* বাগানবন্ধু — nav, theme, notices, motion.
 * Vanilla ES6. No framework, no bundler, no CDN.
 * Everything here is progressive enhancement: every flow works without it.
 */
(function () {
  'use strict';

  var reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  var finePointer = window.matchMedia('(pointer: fine)').matches;

  var toBn = function (n) {
    return String(n).replace(/\d/g, function (d) { return '০১২৩৪৫৬৭৮৯'[d]; });
  };

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

  /* ── Header: shrink / gain a shadow after the first scroll ──────────── */
  var header = document.querySelector('.site-header');
  if (header) {
    var setScrolled = function () {
      header.dataset.scrolled = window.scrollY > 8 ? 'true' : 'false';
    };
    setScrolled();
    window.addEventListener('scroll', setScrolled, { passive: true });
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

  /* ── Animated stat counters ──────────────────────────────────────────
   * Server renders the real Bangla-numeral value in [data-count-to]; on
   * intersect, JS counts up to it. If the attribute is missing or JS never
   * runs, the static server value is exactly what's already on screen.
   */
  var counters = document.querySelectorAll('[data-count-to]');

  if (counters.length) {
    var animateCount = function (el) {
      var target = parseInt(el.getAttribute('data-count-to'), 10);
      if (!isFinite(target) || target <= 0 || reduceMotion) {
        el.textContent = toBn(target) + (el.dataset.suffix || '');
        return;
      }

      var start = null;
      var duration = 900;

      var tick = function (ts) {
        if (start === null) start = ts;
        var progress = Math.min(1, (ts - start) / duration);
        var eased = 1 - Math.pow(1 - progress, 3);
        var value = Math.round(eased * target);
        el.textContent = toBn(value) + (el.dataset.suffix || '');
        if (progress < 1) requestAnimationFrame(tick);
      };
      requestAnimationFrame(tick);
    };

    if ('IntersectionObserver' in window) {
      var countObserver = new IntersectionObserver(function (entries) {
        entries.forEach(function (entry) {
          if (!entry.isIntersecting) return;
          animateCount(entry.target);
          countObserver.unobserve(entry.target);
        });
      }, { threshold: 0.4 });
      counters.forEach(function (el) { countObserver.observe(el); });
    } else {
      counters.forEach(animateCount);
    }
  }

  /* ── Progress rings ───────────────────────────────────────────────────
   * data-done / data-total drive a conic-gradient donut. The centred label
   * is already server-rendered text; this only sets the ring's fill.
   */
  document.querySelectorAll('[data-ring-done]').forEach(function (ring) {
    var done = parseFloat(ring.getAttribute('data-ring-done')) || 0;
    var total = parseFloat(ring.getAttribute('data-ring-total')) || 0;
    var pct = total > 0 ? Math.min(100, Math.round((done / total) * 100)) : (done > 0 ? 100 : 0);
    ring.style.setProperty('--progress', String(pct));
  });

  /* ── Notices: dismissable, auto-fading, entrance animation ───────────
   * The banner already renders and is fully readable with zero JS; this
   * only adds a close affordance and a timed fade for non-error notices.
   */
  document.querySelectorAll('.notice[data-flash]').forEach(function (notice) {
    if (!reduceMotion) notice.classList.add('notice--enter');

    var closeBtn = document.createElement('button');
    closeBtn.type = 'button';
    closeBtn.className = 'notice__close';
    closeBtn.setAttribute('aria-label', 'বার্তা বন্ধ করুন');
    closeBtn.textContent = '✕';

    var dismiss = function () {
      if (reduceMotion) { notice.remove(); return; }
      notice.classList.add('notice--leave');
      notice.addEventListener('animationend', function () { notice.remove(); }, { once: true });
    };

    closeBtn.addEventListener('click', dismiss);
    notice.appendChild(closeBtn);

    if (notice.dataset.flash === 'success' || notice.dataset.flash === 'info') {
      setTimeout(dismiss, 6000);
    }
  });

  /* ── Button ripple ─────────────────────────────────────────────────── */
  document.addEventListener('click', function (e) {
    if (reduceMotion) return;
    var btn = e.target.closest('.btn');
    if (!btn || btn.disabled) return;

    var rect = btn.getBoundingClientRect();
    var size = Math.max(rect.width, rect.height);
    var ripple = document.createElement('span');

    ripple.className = 'btn__ripple';
    ripple.style.width = ripple.style.height = size + 'px';
    ripple.style.left = (e.clientX - rect.left - size / 2) + 'px';
    ripple.style.top = (e.clientY - rect.top - size / 2) + 'px';

    btn.appendChild(ripple);
    ripple.addEventListener('animationend', function () { ripple.remove(); });
  });

  /* ── Card tilt — fine pointers only, subtle, motion-safe ─────────────── */
  if (finePointer && !reduceMotion) {
    document.querySelectorAll('.card--link').forEach(function (card) {
      card.classList.add('card--tilt');

      card.addEventListener('mousemove', function (e) {
        var rect = card.getBoundingClientRect();
        var px = (e.clientX - rect.left) / rect.width - 0.5;
        var py = (e.clientY - rect.top) / rect.height - 0.5;
        card.style.setProperty('--tilt-x', (py * -4).toFixed(2) + 'deg');
        card.style.setProperty('--tilt-y', (px * 4).toFixed(2) + 'deg');
        card.style.setProperty('--tilt-lift', '-4px');
      });

      card.addEventListener('mouseleave', function () {
        card.style.setProperty('--tilt-x', '0deg');
        card.style.setProperty('--tilt-y', '0deg');
        card.style.setProperty('--tilt-lift', '0px');
      });
    });
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

  /* ── Task-row completion micro-animation ──────────────────────────────
   * When a "সম্পন্ন" form inside a .task-row submits, briefly flash the row
   * before the page navigates away — purely cosmetic, the server call is
   * what actually records completion.
   */
  document.querySelectorAll('.task-row form').forEach(function (form) {
    form.addEventListener('submit', function () {
      if (reduceMotion) return;
      var row = form.closest('.task-row');
      if (row) row.classList.add('task-row--complete');
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
    var update = function () {
      target.textContent = toBn(field.value.length) + (max ? ' / ' + toBn(max) : '') + ' অক্ষর';
    };

    field.addEventListener('input', update);
    update();
  });

  /* ── Floating action button (gated app only) ─────────────────────────
   * A real <button> toggling aria-expanded over real <a> links — nothing
   * here is reachable only by hover, and Escape / an outside click closes it.
   */
  var fabGroup = document.querySelector('[data-fab-group]');
  var fabBackdrop = document.querySelector('[data-fab-backdrop]');
  var fabToggle = fabGroup && fabGroup.querySelector('[data-fab-toggle]');

  if (fabGroup && fabToggle) {
    var closeFab = function () {
      fabGroup.dataset.open = 'false';
      fabToggle.setAttribute('aria-expanded', 'false');
      if (fabBackdrop) fabBackdrop.dataset.open = 'false';
    };
    var openFab = function () {
      fabGroup.dataset.open = 'true';
      fabToggle.setAttribute('aria-expanded', 'true');
      if (fabBackdrop) fabBackdrop.dataset.open = 'true';
    };

    fabToggle.addEventListener('click', function () {
      if (fabGroup.dataset.open === 'true') closeFab(); else openFab();
    });

    if (fabBackdrop) fabBackdrop.addEventListener('click', closeFab);

    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape' && fabGroup.dataset.open === 'true') {
        closeFab();
        fabToggle.focus();
      }
    });
  }
})();
