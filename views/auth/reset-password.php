<?php
/** @var string $token */
$this->layout('layouts/public', ['title' => 'নতুন Password সেট করুন']);
?>
<section class="section">
  <div class="wrap">
    <div class="otp-box" id="reset-password-box">
      <h2>নতুন Password সেট করুন</h2>
      <p class="sub">আপনার নতুন Password দিন।</p>

      <form method="post" action="/reset-password/<?= e($token) ?>" data-guard>
        <?= csrf_field() ?>

        <div class="field">
          <label for="rp-password">নতুন Password</label>
          <input class="input" type="password" id="rp-password" name="password"
                 autocomplete="new-password" minlength="8" required>
          <p class="help">কমপক্ষে ৮ অক্ষর</p>
        </div>

        <div class="field">
          <label for="rp-password-confirm">Password আবার লিখুন</label>
          <input class="input" type="password" id="rp-password-confirm" name="password_confirmation"
                 autocomplete="new-password" minlength="8" required>
        </div>

        <button class="btn btn--accent btn--block btn--lg" type="submit">Password বদলান →</button>
      </form>

      <p class="small center mb-0"><a href="/login">Login-এ ফিরে যান</a></p>
    </div>
  </div>
</section>
