<?php
/**
 * @var array $todayTasks, $weekTasks, $myPlants, $picks, $guides, $questions
 * @var int   $plantCount, $season, $doneTodayCount, $totalTodayCount
 * @var string $monthName
 */
use App\Core\View;

$this->layout('layouts/app', ['title' => 'ড্যাশবোর্ড']);

$taskLabels = (array) config('content.care_task');
?>
<div class="page-head">
  <h1>স্বাগতম</h1>
  <p class="muted"><?= e($monthName) ?> মাস চলছে — আজ আপনার বাগানে যা করার আছে, নিচে দেখুন।</p>
</div>

<div class="grid grid--2">
  <div class="card">
    <div class="between">
      <h2 class="card__title mb-0">আজকের কাজ</h2>
      <?php if ($totalTodayCount > 0): ?>
        <div class="progress-ring progress-<?= e((string) pct_step((float) $doneTodayCount, (float) $totalTodayCount)) ?>"
             data-ring-done="<?= e((string) $doneTodayCount) ?>" data-ring-total="<?= e((string) $totalTodayCount) ?>"
             role="img" aria-label="<?= e(bn_num($doneTodayCount) . '/' . bn_num($totalTodayCount) . ' কাজ সম্পন্ন') ?>">
          <span class="progress-ring__label"><?= e(bn_num($doneTodayCount) . '/' . bn_num($totalTodayCount)) ?></span>
        </div>
      <?php else: ?>
        <span class="chip <?= $todayTasks === [] ? 'chip--low' : 'chip--medium' ?>"><?= e(bn_num(count($todayTasks))) ?>টি</span>
      <?php endif; ?>
    </div>

    <?php if ($todayTasks === []): ?>
      <p class="muted mb-0">আজ কোনো কাজ বাকি নেই। 🌿</p>
    <?php else: ?>
      <?php foreach ($todayTasks as $task): ?>
        <form class="task-row" method="post" action="/app/garden/task/<?= e((string) $task['id']) ?>/done" data-guard>
          <?= csrf_field() ?>
          <div class="task-row__label">
            <strong><?= e((string) $task['plant_label']) ?></strong>
            <span class="small muted"><?= e($taskLabels[(string) $task['task']] ?? (string) $task['task']) ?></span>
          </div>
          <button class="btn btn--sm" type="submit">সম্পন্ন</button>
        </form>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>

  <div class="card">
    <h2 class="card__title">এই সপ্তাহ</h2>
    <?php if ($weekTasks === []): ?>
      <p class="muted mb-0">এই সপ্তাহে আর নতুন কাজ নেই।</p>
    <?php else: ?>
      <?php foreach ($weekTasks as $task): ?>
        <div class="task-row">
          <div class="task-row__label">
            <strong><?= e((string) $task['plant_label']) ?></strong>
            <span class="small muted">
              <?= e($taskLabels[(string) $task['task']] ?? (string) $task['task']) ?> ·
              <?= e(bn_date((string) $task['due_on'])) ?>
            </span>
          </div>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
    <p class="mb-0"><a href="/app/calendar">পুরো ক্যালেন্ডার দেখুন →</a></p>
  </div>
</div>

<section class="section--tight">
  <div class="between">
    <h2>আমার বাগান</h2>
    <a class="small" href="/app/garden">সব দেখুন (<?= e(bn_num($plantCount)) ?>)</a>
  </div>

  <?php if ($myPlants === []): ?>
    <div class="empty-state">
      <h3>এখনো কোনো গাছ যোগ করেননি</h3>
      <p class="muted">প্রথম গাছটা যোগ করুন — রিমাইন্ডার আর কাজের তালিকা এমনিতেই তৈরি হয়ে যাবে।</p>
      <a class="btn btn--accent" href="/app/garden/add">গাছ যোগ করুন</a>
    </div>
  <?php else: ?>
    <div class="grid grid--4">
      <?php foreach ($myPlants as $plant): ?>
        <a class="card card--link" href="/app/garden/<?= e((string) $plant['id']) ?>">
          <h3 class="card__title"><?= e((string) ($plant['nickname'] ?: $plant['plant_name_bn'] ?: 'আমার গাছ')) ?></h3>
          <p class="small muted mb-0"><?= e((string) config('content.space.' . $plant['location'])) ?></p>
        </a>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</section>

<section class="section--tight">
  <h2>নতুনদের জন্য সহজ গাছ</h2>
  <div class="grid grid--4">
    <?php foreach ($picks as $plant): ?>
      <?= View::partial('partials/plant-card', ['plant' => $plant, 'inApp' => true]) ?>
    <?php endforeach; ?>
  </div>
</section>

<div class="grid grid--2">
  <section class="section--tight">
    <h2>নতুন গাইড</h2>
    <div class="stack">
      <?php foreach ($guides as $guide): ?>
        <?= View::partial('partials/guide-card', ['guide' => $guide, 'inApp' => true]) ?>
      <?php endforeach; ?>
    </div>
  </section>

  <section class="section--tight">
    <h2>সাম্প্রতিক প্রশ্ন</h2>
    <div class="stack">
      <?php foreach ($questions as $q): ?>
        <a class="card card--link" href="/app/qa/<?= e((string) $q['id']) ?>">
          <h3 class="card__title text-step-0"><?= e((string) $q['title']) ?></h3>
          <p class="small muted mb-0"><?= e(bn_num((int) $q['answer_count'])) ?>টি উত্তর</p>
        </a>
      <?php endforeach; ?>
      <?php if ($questions === []): ?><p class="muted mb-0">এখনো কোনো প্রশ্ন নেই।</p><?php endif; ?>
    </div>
  </section>
</div>
