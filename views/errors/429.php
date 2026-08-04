<?php
/**
 * @var string   $message
 * @var int|null $retryAfter
 */
$this->layout('layouts/public', ['title' => 'একটু অপেক্ষা করুন']);
?>
<section class="section">
  <div class="wrap prose center">
    <p class="eyebrow mono">429</p>
    <h1><?= e($message !== '' ? $message : 'অনেকবার চেষ্টা হয়েছে। একটু পরে আবার চেষ্টা করুন।') ?></h1>

    <?php if (!empty($retryAfter)): ?>
      <p class="lede">
        আনুমানিক <?= e(\App\Core\RateLimit::humanWait((int) $retryAfter)) ?> পরে আবার চেষ্টা করা যাবে।
      </p>
    <?php endif; ?>

    <p><a class="btn btn--ghost" href="/">হোমে ফিরুন</a></p>
  </div>
</section>
