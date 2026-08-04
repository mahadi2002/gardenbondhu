<?php
/**
 * @var array $user
 * @var array $subs
 * @var array<int,array> $charges
 * @var bool  $hasAccess
 * @var int   $plantCount, $questionCount
 */
$this->layout('layouts/admin', ['title' => '01••••' . $user['msisdn_last4']]);
?>
<div class="page-head">
  <h1>01••••<?= e((string) $user['msisdn_last4']) ?></h1>
  <p class="muted">
    <?= e(ucfirst((string) $user['operator'])) ?> · Role: <?= e((string) $user['role']) ?> ·
    Status: <?= e((string) $user['status']) ?> ·
    Access এখন: <?= $hasAccess ? 'হ্যাঁ' : 'না' ?>
  </p>
</div>

<div class="cluster" style="margin-bottom:1.5rem">
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
  <form method="post" action="/admin/users/<?= e((string) $user['id']) ?>" class="cluster"><?= csrf_field() ?>
    <input type="hidden" name="action" value="extend_grace">
    <input class="input" type="number" name="hours" value="48" min="1" max="168" style="width:80px">
    <button class="btn btn--sm btn--ghost" type="submit">Grace দিন</button>
  </form>
</div>

<div class="kpi-grid">
  <div class="kpi"><p class="kpi__n mb-0"><?= e(bn_num($plantCount)) ?></p><p class="kpi__l mb-0">আমার বাগানে গাছ</p></div>
  <div class="kpi"><p class="kpi__n mb-0"><?= e(bn_num($questionCount)) ?></p><p class="kpi__l mb-0">প্রশ্ন করেছেন</p></div>
</div>

<h2>Subscription History</h2>
<?php foreach ($subs as $sub): ?>
  <div class="card" style="margin-bottom:1rem">
    <div class="between">
      <strong><?= e((string) $sub['status']) ?></strong>
      <span class="small mono">#<?= e((string) $sub['id']) ?></span>
    </div>
    <p class="small muted mb-0">
      Started: <?= e((string) ($sub['started_at'] ?? '—')) ?> ·
      Period end: <?= e((string) ($sub['current_period_end'] ?? '—')) ?>
    </p>

    <?php if (!empty($charges[(int) $sub['id']])): ?>
      <div class="table-wrap" style="margin-top:.75rem">
        <table>
          <thead><tr><th>তারিখ</th><th>Amount</th><th>Status</th><th>Reason</th></tr></thead>
          <tbody>
          <?php foreach ($charges[(int) $sub['id']] as $charge): ?>
            <tr>
              <td class="small mono"><?= e((string) $charge['attempted_at']) ?></td>
              <td class="mono">৳<?= e(number_format((float) $charge['amount'], 2)) ?></td>
              <td><?= e((string) $charge['status']) ?></td>
              <td class="small"><?= e((string) ($charge['failure_reason'] ?? '—')) ?></td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>
<?php endforeach; ?>
