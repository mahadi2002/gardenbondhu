<?php
/**
 * @var array $plants
 * @var int   $dueToday
 */
$this->layout('layouts/app', ['title' => 'আমার বাগান']);
?>
<div class="page-head">
  <div class="between">
    <h1>আমার বাগান</h1>
    <a class="btn btn--accent" href="/app/garden/add">+ গাছ যোগ করুন</a>
  </div>
  <?php if ($dueToday > 0): ?>
    <p class="muted">আজ <?= e(bn_num($dueToday)) ?>টি কাজ বাকি — <a href="/app">ড্যাশবোর্ডে দেখুন</a></p>
  <?php endif; ?>
</div>

<?php if ($plants === []): ?>
  <div class="empty-state">
    <h3>এখনো কোনো গাছ যোগ করেননি</h3>
    <p class="muted">প্রথম গাছটা যোগ করুন। পানি-সার দেওয়ার রিমাইন্ডার এমনিতেই তৈরি হয়ে যাবে।</p>
    <a class="btn btn--accent" href="/app/garden/add">গাছ যোগ করুন</a>
  </div>
<?php else: ?>
  <div class="grid grid--3">
    <?php foreach ($plants as $plant): ?>
      <a class="card card--link" href="/app/garden/<?= e((string) $plant['id']) ?>">
        <h3 class="card__title"><?= e((string) ($plant['nickname'] ?: $plant['plant_name_bn'] ?: 'আমার গাছ')) ?></h3>
        <?php if (!empty($plant['plant_name_bn']) && !empty($plant['nickname'])): ?>
          <p class="card__meta"><?= e((string) $plant['plant_name_bn']) ?></p>
        <?php endif; ?>
        <div class="cluster">
          <span class="chip chip--muted"><?= e((string) config('content.space.' . $plant['location'])) ?></span>
          <?php if (!empty($plant['planted_on'])): ?>
            <span class="small muted">রোপণ: <?= e(bn_date((string) $plant['planted_on'])) ?></span>
          <?php endif; ?>
        </div>
      </a>
    <?php endforeach; ?>
  </div>
<?php endif; ?>
