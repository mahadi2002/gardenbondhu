<?php
/**
 * @var string $secret   raw base32 secret (also submitted as a hidden confirm field via session, not this form)
 * @var string $display  secret formatted in groups of 4 for manual entry
 * @var string $uri      otpauth://totp/... provisioning URI
 */
$this->layout('layouts/admin', ['title' => '2FA সেটআপ']);
?>
<div class="page-head"><h1>2FA সেটআপ</h1></div>

<div class="card reveal">
  <h2 class="card__title">১. Authenticator app-এ যোগ করুন</h2>
  <p class="small muted">
    Google Authenticator, Authy, 1Password বা যেকোনো TOTP Authenticator app খুলুন এবং
    নিচের Key-টা Manual Entry হিসেবে বসান (Account name: এই Admin-এর Email, Issuer: <?= e($appName) ?>)।
  </p>

  <p class="mono totp-secret"><?= e($display) ?></p>

  <p class="small muted mb-0">
    একই ডিভাইসে Authenticator app খোলা থাকলে
    <a href="<?= e($uri) ?>">এই Link-এ ট্যাপ করলে</a> সরাসরি যোগ হয়ে যাবে — QR স্ক্যানের বদলে।
  </p>
</div>

<div class="card reveal">
  <h2 class="card__title">২. একটা Code দিয়ে নিশ্চিত করুন</h2>
  <p class="small muted">Authenticator app-এ এখন যে ৬ সংখ্যার Code দেখাচ্ছে, সেটা বসান।</p>

  <?php if ($error = error_for('code')): ?>
    <div class="notice notice--error" role="alert">
      <span class="notice__icon" aria-hidden="true">!</span><span><?= e($error) ?></span>
    </div>
  <?php endif; ?>

  <form method="post" action="/admin/security/totp/confirm" data-guard>
    <?= csrf_field() ?>
    <div class="field<?= error_for('code') ? ' field--error' : '' ?>">
      <label for="totp-confirm-code">Code</label>
      <input class="input input--code" type="text" id="totp-confirm-code" name="code"
             inputmode="numeric" autocomplete="one-time-code" maxlength="6"
             pattern="[0-9]{6}" required placeholder="------" autofocus>
    </div>
    <button class="btn btn--accent" type="submit">2FA চালু করুন</button>
  </form>
</div>
