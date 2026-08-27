<?php
/**
 * @var array $user
 * @var int   $plantCount
 */
$this->layout('layouts/public', ['title' => 'Account']);
?>
<section class="section">
  <div class="wrap">
    <div class="page-head">
      <h1>আপনার Account</h1>
      <p class="muted">ইমেইল: <span class="mono"><?= e((string) $user['email']) ?></span></p>
    </div>

    <div class="card">
      <h2 class="card__title">আমার বাগান</h2>
      <p>আপনি <?= e(bn_num($plantCount)) ?>টি গাছ যোগ করেছেন।</p>
      <a class="btn" href="/app/garden">বাগানে যান</a>
    </div>

    <div class="card">
      <h2 class="card__title">অ্যাকাউন্ট মুছে ফেলুন</h2>
      <p class="small">
        মুছে ফেললে আপনার তথ্য আর বাগানের রেকর্ড স্থায়ীভাবে চলে যাবে। এই কাজটি ফেরানো যায় না।
      </p>

      <form method="post" action="/account/delete" data-confirm="অ্যাকাউন্ট স্থায়ীভাবে মুছে ফেলবেন?">
        <?= csrf_field() ?>
        <div class="field">
          <label for="confirm-delete">নিশ্চিত করতে <span class="mono">DELETE</span> লিখুন</label>
          <input class="input" type="text" id="confirm-delete" name="confirm" autocomplete="off" required>
        </div>
        <button class="btn btn--danger" type="submit">অ্যাকাউন্ট মুছুন</button>
      </form>
    </div>
  </div>
</section>
