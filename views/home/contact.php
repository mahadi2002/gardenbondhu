<?php $this->layout('layouts/public', ['title' => 'যোগাযোগ']); ?>
<section class="section">
  <div class="wrap">
    <div class="prose">
      <h1>যোগাযোগ করুন</h1>
      <p class="lede">প্রশ্ন, মতামত বা কোনো সমস্যা — নিচের ফর্মে লিখুন, আমরা দেখব।</p>
    </div>

    <form class="card" method="post" action="/contact" data-guard style="max-width:560px">
      <?= csrf_field() ?>

      <div class="honeypot" aria-hidden="true">
        <label for="website-contact">Website</label>
        <input type="text" id="website-contact" name="website" tabindex="-1" autocomplete="off">
      </div>

      <div class="field<?= error_for('name') ? ' field--error' : '' ?>">
        <label for="c-name">নাম</label>
        <input class="input" type="text" id="c-name" name="name" maxlength="80" required value="<?= e(old('name')) ?>">
        <?php if ($err = error_for('name')): ?><p class="error-text"><?= e($err) ?></p><?php endif; ?>
      </div>

      <div class="field<?= error_for('contact') ? ' field--error' : '' ?>">
        <label for="c-contact">যোগাযোগের নম্বর বা Email</label>
        <input class="input" type="text" id="c-contact" name="contact" maxlength="120" required value="<?= e(old('contact')) ?>">
        <?php if ($err = error_for('contact')): ?><p class="error-text"><?= e($err) ?></p><?php endif; ?>
      </div>

      <div class="field<?= error_for('message') ? ' field--error' : '' ?>">
        <label for="c-message">বার্তা</label>
        <textarea class="input" id="c-message" name="message" maxlength="2000" required><?= e(old('message')) ?></textarea>
        <?php if ($err = error_for('message')): ?><p class="error-text"><?= e($err) ?></p><?php endif; ?>
      </div>

      <button class="btn btn--accent" type="submit">পাঠিয়ে দিন</button>
    </form>
  </div>
</section>
