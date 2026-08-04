<?php
/**
 * @var array  $questions
 * @var string $query
 * @var int    $page
 */
$this->layout('layouts/app', ['title' => 'প্রশ্ন-উত্তর']);
?>
<div class="page-head">
  <div class="between">
    <h1>প্রশ্ন-উত্তর</h1>
    <a class="btn btn--accent" href="/app/qa/ask">প্রশ্ন করুন</a>
  </div>
</div>

<form method="get" action="/app/qa" class="cluster" style="margin-bottom:1.5rem">
  <input class="input" type="search" name="q" value="<?= e($query) ?>" placeholder="প্রশ্ন খুঁজুন" style="max-width:320px">
  <button class="btn btn--sm" type="submit">খুঁজুন</button>
</form>

<?php if ($questions === []): ?>
  <div class="empty-state">
    <h3>কোনো প্রশ্ন পাওয়া যায়নি</h3>
    <p class="muted">আটকে গেছেন? প্রথম প্রশ্নটা আপনিই করুন।</p>
    <a class="btn btn--accent" href="/app/qa/ask">প্রশ্ন করুন</a>
  </div>
<?php else: ?>
  <div class="stack">
    <?php foreach ($questions as $q): ?>
      <a class="card card--link" href="/app/qa/<?= e((string) $q['id']) ?>">
        <div class="between">
          <h2 class="card__title mb-0" style="font-size:var(--step-0)"><?= e((string) $q['title']) ?></h2>
          <?php if ((int) $q['role'] === 1 || $q['role'] === 'expert'): ?>
            <span class="chip chip--low">বিশেষজ্ঞ</span>
          <?php endif; ?>
        </div>
        <p class="small mb-0"><?= e(str_excerpt((string) $q['body'], 140)) ?></p>
        <p class="small muted mb-0">
          <?= e(bn_num((int) $q['answer_count'])) ?>টি উত্তর
          <?php if (!empty($q['plant_name_bn'])): ?> · <?= e((string) $q['plant_name_bn']) ?><?php endif; ?>
          · <?= e(bn_date((string) $q['created_at'])) ?>
        </p>
      </a>
    <?php endforeach; ?>
  </div>
<?php endif; ?>
