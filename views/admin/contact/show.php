<?php
/** @var array $message */
$this->layout('layouts/admin', ['title' => 'Contact Message']);
?>
<div class="page-head"><h1><?= e((string) $message['name']) ?></h1></div>

<div class="card max-w-md">
  <dl class="quick-facts">
    <div class="quick-fact"><dt>যোগাযোগ</dt><dd class="mono"><?= e((string) $message['contact']) ?></dd></div>
    <div class="quick-fact"><dt>পাঠানো হয়েছে</dt><dd><?= e(bn_date((string) $message['created_at'])) ?></dd></div>
    <div class="quick-fact"><dt>অবস্থা</dt><dd><?= e((string) $message['status']) ?></dd></div>
  </dl>

  <div class="prose">
    <p><?= nl2br(e((string) $message['message'])) ?></p>
  </div>

  <?php if ($message['status'] !== 'resolved'): ?>
    <form method="post" action="/admin/contact/<?= e((string) $message['id']) ?>/resolve" data-guard>
      <?= csrf_field() ?>
      <button class="btn btn--accent" type="submit">সমাধান হয়েছে বলে চিহ্নিত করুন</button>
    </form>
  <?php endif; ?>

  <p class="small muted mb-0"><a href="/admin/contact">← Inbox-এ ফিরুন</a></p>
</div>
