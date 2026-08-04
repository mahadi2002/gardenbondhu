<?php
/** @var array|null $sub */
$this->layout('layouts/public', ['title' => 'Subscription বন্ধ আছে']);

$status = $sub['status'] ?? 'expired';
?>
<section class="section">
  <div class="wrap">
    <div class="otp-box">
      <?php if ($status === 'pending'): ?>
        <h2>প্রথম Charge হয়নি</h2>
        <p class="sub">ব্যালেন্স কম মনে হচ্ছে। Recharge করে আবার চেষ্টা করুন।</p>
      <?php elseif ($status === 'unsubscribed'): ?>
        <h2>আপনার Subscription বন্ধ আছে</h2>
        <p class="sub">আবার শুরু করতে নিচের বোতামে চাপ দিন। আপনার বাগানের রেকর্ড মুছে যায়নি।</p>
      <?php else: ?>
        <h2>আপনার Subscription বন্ধ হয়ে গেছে</h2>
        <p class="sub">আবার শুরু করতে Recharge করুন। আপনার গাছ আর রেকর্ড সব ঠিক আছে।</p>
      <?php endif; ?>

      <div class="notice notice--info">
        <span class="notice__icon" aria-hidden="true">i</span>
        <span>
          Robi বা Airtel ব্যালেন্সে অন্তত ৳<?= e($dailyAmount) ?> রাখুন। পরের Charge cycle-এ
          নিজে থেকেই access ফিরে আসবে — আলাদা কিছু করতে হবে না।
        </span>
      </div>

      <p class="cluster">
        <a class="btn btn--accent btn--lg" href="/subscribe">আবার Subscribe করুন</a>
        <a class="btn btn--ghost" href="/account">Account দেখুন</a>
      </p>

      <?php if (!empty($sub['current_period_end'])): ?>
        <p class="small muted mb-0">
          শেষ Access ছিল: <?= e(bn_date((string) $sub['current_period_end'], true)) ?>
        </p>
      <?php endif; ?>
    </div>
  </div>
</section>
