<?php
/**
 * The required subscribe box (spec §5.1). Every string here is verbatim.
 * It posts straight to the real OTP route — this is the funnel, not a demo.
 */
?>
<div class="otp-box" id="otp-box">
  <h2>আপনার Robi বা Airtel Number দিন</h2>
  <p class="sub">Instant Access পাবেন সব গার্ডেনিং Content-এ!</p>

  <?php if ($error = error_for('msisdn')): ?>
    <div class="notice notice--error" role="alert">
      <span class="notice__icon" aria-hidden="true">!</span><span><?= e($error) ?></span>
    </div>
  <?php endif; ?>

  <form method="post" action="/subscribe/otp" data-guard>
    <?= csrf_field() ?>

    <div class="honeypot" aria-hidden="true">
      <label for="website-otpbox">Website</label>
      <input type="text" id="website-otpbox" name="website" tabindex="-1" autocomplete="off">
    </div>

    <div class="field<?= error_for('msisdn') ? ' field--error' : '' ?>">
      <label for="msisdn-landing">Mobile Number</label>
      <input class="input input--phone" type="tel" id="msisdn-landing" name="msisdn"
             inputmode="numeric" autocomplete="tel" maxlength="11" required
             placeholder="01XXXXXXXXX" value="<?= e(old('msisdn')) ?>"
             data-phone-input aria-describedby="msisdn-landing-help">
      <p class="help" id="msisdn-landing-help"><?= e($operatorNote) ?></p>
    </div>

    <p class="badge-line">⚡ Daily মাত্র ৳<?= e($dailyAmount) ?> — যেকোনো সময় Unsubscribe করুন</p>

    <button class="btn btn--accent btn--block btn--lg" type="submit">OTP পাঠান →</button>
  </form>
</div>
