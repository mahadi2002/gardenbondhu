<?php
/**
 * @var array $question
 * @var array $answers
 */
use App\Services\ImageService;

$this->layout('layouts/admin', ['title' => (string) $question['title']]);
?>
<div class="page-head">
  <h1><?= e((string) $question['title']) ?></h1>
  <p class="muted">01••••<?= e((string) $question['msisdn_last4']) ?> · <?= e((string) $question['status']) ?></p>
</div>

<div class="card">
  <p class="pre-line"><?= e((string) $question['body']) ?></p>
  <?php if (!empty($question['image'])): ?>
    <img class="max-w-xs rounded-s" src="/media/<?= e(ImageService::toToken((string) $question['image'])) ?>" alt="প্রশ্নের সাথে যুক্ত ছবি">
  <?php endif; ?>
</div>

<div class="cluster mt-1-5 mb-1-5">
  <?php if ($question['status'] === 'pending'): ?>
    <form method="post" action="/admin/qa/<?= e((string) $question['id']) ?>"><?= csrf_field() ?>
      <input type="hidden" name="action" value="approve">
      <button class="btn" type="submit">অনুমোদন করুন</button>
    </form>
    <form method="post" action="/admin/qa/<?= e((string) $question['id']) ?>"><?= csrf_field() ?>
      <input type="hidden" name="action" value="reject">
      <button class="btn btn--danger" type="submit">বাতিল করুন</button>
    </form>
  <?php endif; ?>
  <form method="post" action="/admin/qa/<?= e((string) $question['id']) ?>"><?= csrf_field() ?>
    <input type="hidden" name="action" value="mark_expert">
    <button class="btn btn--ghost" type="submit">প্রশ্নকারীকে বিশেষজ্ঞ করুন</button>
  </form>
</div>

<h2>উত্তরসমূহ (<?= e(bn_num(count($answers))) ?>)</h2>
<div class="stack">
  <?php foreach ($answers as $answer): ?>
    <div class="card">
      <p class="mb-0 pre-line"><?= e((string) $answer['body']) ?></p>
      <p class="small muted mb-0"><?= e((string) ($answer['admin_name'] ?? $answer['display_name'] ?? '—')) ?> · <?= e((string) $answer['created_at']) ?></p>
    </div>
  <?php endforeach; ?>
</div>

<form class="card mt-1" method="post" action="/admin/qa/<?= e((string) $question['id']) ?>" data-guard>
  <?= csrf_field() ?>
  <input type="hidden" name="action" value="answer">
  <div class="field mb-0">
    <label for="admin-answer">Admin হিসেবে উত্তর দিন</label>
    <textarea class="input" id="admin-answer" name="body" required></textarea>
  </div>
  <button class="btn btn--accent mt-0-75" type="submit">উত্তর জমা দিন</button>
</form>
