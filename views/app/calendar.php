<?php
/**
 * @var int    $month
 * @var string $monthName
 * @var array|null $season
 * @var array  $byActivity, $labels, $myTasks
 */
$this->layout('layouts/app', ['title' => 'মাসের কাজ']);

$prev = $month === 1 ? 12 : $month - 1;
$next = $month === 12 ? 1 : $month + 1;
?>
<div class="page-head">
  <div class="between">
    <h1><?= e($monthName) ?> মাসের কাজ</h1>
    <div class="cluster">
      <a class="btn btn--ghost btn--sm" href="/app/calendar?month=<?= e((string) $prev) ?>">← আগের মাস</a>
      <a class="btn btn--ghost btn--sm" href="/app/calendar?month=<?= e((string) $next) ?>">পরের মাস →</a>
    </div>
  </div>
  <?php if ($season !== null): ?>
    <p class="muted">ঋতু: <?= e((string) $season['name_bn']) ?> (<?= e((string) $season['bn_months']) ?>)</p>
  <?php endif; ?>
</div>

<?php if ($myTasks !== []): ?>
  <section class="section--tight">
    <h2>আপনার বাগানের কাজ</h2>
    <div class="stack">
      <?php foreach ($myTasks as $task): ?>
        <div class="task-row <?= $task['done_at'] !== null ? 'task-row--done' : '' ?>">
          <div class="task-row__label">
            <strong><?= e((string) $task['plant_label']) ?></strong>
            <span class="small muted">
              <?= e((string) config('content.care_task.' . $task['task'])) ?> · <?= e(bn_date((string) $task['due_on'])) ?>
            </span>
          </div>
          <?php if ($task['done_at'] === null): ?>
            <form method="post" action="/app/garden/task/<?= e((string) $task['id']) ?>/done" data-guard>
              <?= csrf_field() ?>
              <button class="btn btn--sm" type="submit">সম্পন্ন</button>
            </form>
          <?php else: ?>
            <span class="chip chip--low">সম্পন্ন</span>
          <?php endif; ?>
        </div>
      <?php endforeach; ?>
    </div>
  </section>
<?php endif; ?>

<section>
  <h2>এই মাসে সাধারণভাবে যা করা যায়</h2>

  <?php if ($byActivity === []): ?>
    <p class="muted">এই মাসের জন্য নির্দিষ্ট কোনো তালিকা এখনো যোগ করা হয়নি।</p>
  <?php endif; ?>

  <?php foreach ($byActivity as $activity => $rows): ?>
    <div class="section--tight">
      <h3><?= e($labels[$activity] ?? $activity) ?></h3>
      <div class="grid grid--4">
        <?php foreach ($rows as $row): ?>
          <a class="card card--link" href="/app/plants/<?= e((string) $row['slug']) ?>">
            <strong><?= e((string) $row['name_bn']) ?></strong>
            <?php if (!empty($row['note_bn'])): ?><p class="small muted mb-0"><?= e((string) $row['note_bn']) ?></p><?php endif; ?>
          </a>
        <?php endforeach; ?>
      </div>
    </div>
  <?php endforeach; ?>
</section>
