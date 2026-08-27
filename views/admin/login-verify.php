<?php
/** Minimal shell — same reasoning as admin/login.php: no admin session yet. */
?>
<!doctype html>
<html lang="bn">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>2FA যাচাই — <?= e($appName) ?></title>
<link rel="icon" href="/favicon.svg" type="image/svg+xml">
<link rel="stylesheet" href="<?= e(asset('css/app.css')) ?>">
</head>
<body>
<main class="section" id="main">
  <div class="wrap max-w-sm">
    <?= \App\Core\View::partial('partials/flash', ['notice' => $notice ?? null]) ?>

    <div class="otp-box">
      <h2>Authenticator Code</h2>
      <p class="sub">আপনার Authenticator app-এ যে ৬ সংখ্যার Code দেখাচ্ছে, সেটা বসান।</p>

      <?php if ($error = error_for('code')): ?>
        <div class="notice notice--error" role="alert">
          <span class="notice__icon" aria-hidden="true">!</span><span><?= e($error) ?></span>
        </div>
      <?php endif; ?>

      <form method="post" action="/admin/login/verify" data-guard>
        <?= csrf_field() ?>

        <div class="field<?= error_for('code') ? ' field--error' : '' ?>">
          <label for="totp-code">Code</label>
          <input class="input input--code" type="text" id="totp-code" name="code"
                 inputmode="numeric" autocomplete="one-time-code" maxlength="6"
                 pattern="[0-9]{6}" required placeholder="------" autofocus>
        </div>

        <button class="btn btn--accent btn--block btn--lg" type="submit">Verify করুন</button>
      </form>
    </div>
  </div>
</main>
</body>
</html>
