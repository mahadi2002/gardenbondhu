<?php
/** @var string $next */
$this->layout('layouts/public', ['title' => 'Register করুন']);
$nextQuery = $next !== '' ? '?next=' . rawurlencode($next) : '';
?>
<section class="section">
  <div class="wrap">
    <div class="otp-box" id="register-box">
      <h2>নতুন অ্যাকাউন্ট তৈরি করুন</h2>
      <p class="sub">Email আর Password দিয়ে ফ্রি Register করুন — সম্পূর্ণ Content সাথে সাথে খুলে যাবে।</p>

      <?php if ($error = error_for('email')): ?>
        <div class="notice notice--error" role="alert">
          <span class="notice__icon" aria-hidden="true">!</span><span><?= e($error) ?></span>
        </div>
      <?php elseif ($error = error_for('password_confirmation')): ?>
        <div class="notice notice--error" role="alert">
          <span class="notice__icon" aria-hidden="true">!</span><span><?= e($error) ?></span>
        </div>
      <?php endif; ?>

      <form method="post" action="/register<?= e($nextQuery) ?>" data-guard>
        <?= csrf_field() ?>

        <div class="honeypot" aria-hidden="true">
          <label for="website-register">Website</label>
          <input type="text" id="website-register" name="website" tabindex="-1" autocomplete="off">
        </div>

        <div class="field<?= error_for('email') ? ' field--error' : '' ?>">
          <label for="reg-email">Email</label>
          <input class="input" type="email" id="reg-email" name="email" autocomplete="email"
                 required value="<?= e(old('email')) ?>">
        </div>

        <div class="field<?= error_for('password') ? ' field--error' : '' ?>">
          <label for="reg-password">Password</label>
          <input class="input" type="password" id="reg-password" name="password"
                 autocomplete="new-password" minlength="8" required>
          <p class="help">কমপক্ষে ৮ অক্ষর</p>
        </div>

        <div class="field<?= error_for('password_confirmation') ? ' field--error' : '' ?>">
          <label for="reg-password-confirm">Password আবার লিখুন</label>
          <input class="input" type="password" id="reg-password-confirm" name="password_confirmation"
                 autocomplete="new-password" minlength="8" required>
        </div>

        <button class="btn btn--accent btn--block btn--lg" type="submit">Register করুন →</button>
      </form>

      <p class="small center mb-0">আগে থেকে Account আছে? <a href="/login<?= e($nextQuery) ?>">Login করুন</a></p>
    </div>
  </div>
</section>
