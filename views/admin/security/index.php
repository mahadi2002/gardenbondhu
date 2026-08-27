<?php
/** @var bool $totpEnabled */
$this->layout('layouts/admin', ['title' => 'Security']);
?>
<div class="page-head"><h1>Security</h1></div>

<div class="card reveal">
  <div class="between">
    <h2 class="card__title mb-0">Admin 2FA (TOTP)</h2>
    <span class="chip <?= $totpEnabled ? 'chip--low' : 'chip--high' ?>">
      <?= $totpEnabled ? 'চালু আছে' : 'বন্ধ আছে' ?>
    </span>
  </div>

  <p class="small muted">
    চালু থাকলে Login-এর সময় Password-এর পর Authenticator app থেকে একটা ৬ সংখ্যার Code
    দিতে হবে — কারো কাছে শুধু Password চলে গেলেও Admin Panel-এ ঢুকতে পারবে না।
  </p>

  <?php if ($totpEnabled): ?>
    <form method="post" action="/admin/security/totp/disable" data-confirm="2FA বন্ধ করবেন? Password লাগবে।">
      <?= csrf_field() ?>
      <div class="field">
        <label for="disable-pass">নিশ্চিত করতে আপনার Password দিন</label>
        <input class="input" type="password" id="disable-pass" name="password" required>
      </div>
      <button class="btn btn--danger btn--sm" type="submit">2FA বন্ধ করুন</button>
    </form>
  <?php else: ?>
    <a class="btn btn--accent btn--sm" href="/admin/security/totp/setup">2FA চালু করুন</a>
  <?php endif; ?>
</div>
