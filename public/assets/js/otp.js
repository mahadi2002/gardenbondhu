/* OTP form: digit-only input, auto-submit when complete, resend countdown.
 * The form submits normally without this file; the countdown value comes from
 * PHP in a <script type="application/json"> block, never from an inline script
 * (which the CSP forbids, correctly).
 */
(function () {
  'use strict';

  var toBn = function (n) {
    return String(n).replace(/\d/g, function (d) { return '০১২৩৪৫৬৭৮৯'[d]; });
  };

  /* ── Code input ─────────────────────────────────────────────────────── */
  var code = document.getElementById('otp-code');

  if (code) {
    code.addEventListener('input', function () {
      var digits = code.value.replace(/\D+/g, '').slice(0, 6);
      if (code.value !== digits) code.value = digits;

      // Submit as soon as six digits are in — one less tap on a phone.
      if (digits.length === 6 && code.form && !code.form.dataset.submitted) {
        code.form.dataset.submitted = 'true';
        code.form.requestSubmit
          ? code.form.requestSubmit()
          : code.form.submit();
      }
    });

    code.addEventListener('paste', function (e) {
      var text = (e.clipboardData || window.clipboardData).getData('text') || '';
      var digits = text.replace(/\D+/g, '').slice(0, 6);
      if (digits.length) {
        e.preventDefault();
        code.value = digits;
        code.dispatchEvent(new Event('input', { bubbles: true }));
      }
    });

    code.focus();
  }

  /* ── Resend countdown ───────────────────────────────────────────────── */
  var dataEl = document.getElementById('page-data');
  var button = document.querySelector('[data-resend-button]');
  var label = document.getElementById('resend-timer');

  if (!dataEl || !button) return;

  var data;
  try {
    data = JSON.parse(dataEl.textContent);
  } catch (err) {
    return;
  }

  var remaining = parseInt(data.resendWait, 10) || 0;
  if (remaining <= 0) return;

  button.disabled = true;

  var tick = setInterval(function () {
    remaining -= 1;

    if (remaining <= 0) {
      clearInterval(tick);
      button.disabled = false;
      if (label) label.textContent = '';
      return;
    }

    if (label) label.textContent = toBn(remaining) + ' সেকেন্ড পর আবার পাঠাতে পারবেন।';
  }, 1000);

  if (label) label.textContent = toBn(remaining) + ' সেকেন্ড পর আবার পাঠাতে পারবেন।';
})();
