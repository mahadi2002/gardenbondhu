<?php
/** @var array|null $sub */
$this->layout('layouts/public', ['title' => 'Unsubscribe']);
?>
<section class="section">
  <div class="wrap">
    <div class="otp-box">
      <h2>Unsubscribe করবেন?</h2>
      <p class="sub">
        আজকের পর থেকে আর কোনো টাকা কাটা হবে না। আপনার বাগানের রেকর্ড মুছে যাবে না —
        যেদিন ইচ্ছা আবার শুরু করতে পারবেন।
      </p>

      <?php if (!empty($sub['current_period_end'])): ?>
        <div class="notice notice--info">
          <span class="notice__icon" aria-hidden="true">i</span>
          <span>বর্তমান access <?= e(bn_date((string) $sub['current_period_end'], true)) ?> পর্যন্ত ছিল।</span>
        </div>
      <?php endif; ?>

      <form method="post" action="/account/unsubscribe" data-guard>
        <?= csrf_field() ?>

        <div class="field">
          <label for="reason">কারণটা জানালে ভালো হয় (ঐচ্ছিক)</label>
          <select class="input" id="reason" name="reason">
            <option value="">বলতে চাই না</option>
            <option value="too_expensive">খরচ বেশি মনে হচ্ছে</option>
            <option value="not_useful">কাজে লাগছে না</option>
            <option value="content_missing">যা খুঁজছিলাম, পাইনি</option>
            <option value="temporary">কিছুদিন পর আবার নেব</option>
          </select>
        </div>

        <button class="btn btn--danger btn--block" type="submit">হ্যাঁ, Unsubscribe করুন</button>
      </form>

      <p class="small center mb-0"><a href="/account">না, থাক</a></p>

      <hr>

      <p class="small muted mb-0">
        SMS দিয়েও বন্ধ করতে পারেন: STOP লিখে <span class="mono"><?= e($shortcode) ?></span> নম্বরে পাঠান।
      </p>
    </div>
  </div>
</section>
