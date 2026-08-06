<?php
/**
 * The subscribe box. Wording here is locked when called from the landing
 * page (which never passes $isLogin) — it's been tested against real users.
 *
 * Also reused on /subscribe and /login (same OTP mechanism serves both —
 * a returning subscriber's number just logs them straight in). $isLogin
 * swaps the heading/subhead/button for a login-flavored framing there,
 * without touching the landing-page copy.
 *
 * @var bool $isLogin
 */
$isLogin ??= false;
?>
<div class="otp-box" id="otp-box">
  <h2><?= $isLogin ? 'লগইন করুন' : 'আপনার Robi বা Airtel Number দিন' ?></h2>
  <p class="sub">
    <?= $isLogin
        ? 'আগে থেকে Subscribed? নম্বর দিয়ে সরাসরি Login করুন।'
        : 'Instant Access পাবেন সব গার্ডেনিং Content-এ!' ?>
  </p>

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

    <?php if (!$isLogin): ?>
      <p class="badge-line">⚡ Daily ৳<?= e($dailyAmount) ?> (Incl. VAT, SD &amp; SC) — যেকোনো সময় Unsubscribe করুন</p>
    <?php endif; ?>

    <button class="btn btn--accent btn--block btn--lg" type="submit">
      <?= $isLogin ? 'Login করুন →' : 'OTP পাঠান →' ?>
    </button>
  </form>
</div>
