<?php
$this->layout('layouts/public', ['title' => 'Password ভুলে গেছেন']);
?>
<section class="section">
  <div class="wrap">
    <div class="otp-box" id="forgot-password-box">
      <h2>Password ভুলে গেছেন?</h2>
      <p class="sub">আপনার Email দিন — একটি Password Reset Link পাঠানো হবে।</p>

      <form method="post" action="/forgot-password" data-guard>
        <?= csrf_field() ?>

        <div class="field">
          <label for="fp-email">Email</label>
          <input class="input" type="email" id="fp-email" name="email" autocomplete="email"
                 required value="<?= e(old('email')) ?>">
        </div>

        <button class="btn btn--accent btn--block btn--lg" type="submit">Reset Link পাঠান →</button>
      </form>

      <p class="small center mb-0"><a href="/login">Login-এ ফিরে যান</a></p>
    </div>
  </div>
</section>
