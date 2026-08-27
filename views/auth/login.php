<?php
/** @var string $next */
$this->layout('layouts/public', ['title' => 'Login করুন']);
$nextQuery = $next !== '' ? '?next=' . rawurlencode($next) : '';
?>
<section class="section">
  <div class="wrap">
    <div class="otp-box" id="login-box">
      <h2>Login করুন</h2>
      <p class="sub">Email আর Password দিয়ে আপনার অ্যাকাউন্টে ঢুকুন।</p>

      <form method="post" action="/login<?= e($nextQuery) ?>" data-guard>
        <?= csrf_field() ?>

        <div class="field">
          <label for="login-email">Email</label>
          <input class="input" type="email" id="login-email" name="email" autocomplete="email"
                 required value="<?= e(old('email')) ?>">
        </div>

        <div class="field">
          <label for="login-password">Password</label>
          <input class="input" type="password" id="login-password" name="password"
                 autocomplete="current-password" required>
        </div>

        <button class="btn btn--accent btn--block btn--lg" type="submit">Login করুন →</button>
      </form>

      <p class="small center"><a href="/forgot-password">Password ভুলে গেছেন?</a></p>
      <p class="small center mb-0">নতুন? <a href="/register<?= e($nextQuery) ?>">ফ্রি Register করুন</a></p>
    </div>
  </div>
</section>
