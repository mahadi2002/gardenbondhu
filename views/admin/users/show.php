<?php
/**
 * @var array $user
 * @var int   $plantCount, $questionCount
 */
$this->layout('layouts/admin', ['title' => (string) $user['email']]);
?>
<div class="page-head">
  <h1><?= e((string) $user['email']) ?></h1>
  <p class="muted">
    Role: <?= e((string) $user['role']) ?> ·
    Status: <?= e((string) $user['status']) ?>
  </p>
</div>

<div class="cluster mb-1-5">
  <form method="post" action="/admin/users/<?= e((string) $user['id']) ?>"><?= csrf_field() ?>
    <input type="hidden" name="action" value="<?= $user['status'] === 'blocked' ? 'unblock' : 'block' ?>">
    <button class="btn btn--sm <?= $user['status'] === 'blocked' ? '' : 'btn--danger' ?>" type="submit">
      <?= $user['status'] === 'blocked' ? 'Unblock করুন' : 'Block করুন' ?>
    </button>
  </form>
  <form method="post" action="/admin/users/<?= e((string) $user['id']) ?>"><?= csrf_field() ?>
    <input type="hidden" name="action" value="<?= $user['role'] === 'expert' ? 'mark_user' : 'mark_expert' ?>">
    <button class="btn btn--sm btn--ghost" type="submit">
      <?= $user['role'] === 'expert' ? 'সাধারণ ব্যবহারকারী করুন' : 'বিশেষজ্ঞ করুন' ?>
    </button>
  </form>
</div>

<div class="kpi-grid">
  <div class="kpi"><p class="kpi__n mb-0"><?= e(bn_num($plantCount)) ?></p><p class="kpi__l mb-0">আমার বাগানে গাছ</p></div>
  <div class="kpi"><p class="kpi__n mb-0"><?= e(bn_num($questionCount)) ?></p><p class="kpi__l mb-0">প্রশ্ন করেছেন</p></div>
</div>
