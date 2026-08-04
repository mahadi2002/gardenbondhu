<?php
/**
 * @var array       $results
 * @var bool         $widened
 * @var array        $chosen
 * @var int[]        $symptomIds
 * @var int|null     $plantId
 * @var string|null  $plantName
 * @var string       $disclaimer
 */
use App\Services\DiagnosisEngine;

$this->layout('layouts/app', ['title' => 'সম্ভাব্য কারণ']);
?>
<div class="page-head">
  <h1>সম্ভাব্য কারণ</h1>
  <p class="muted">
    বাছাই করা লক্ষণ:
    <?php foreach ($chosen as $i => $symptom): ?>
      <?= $i > 0 ? ', ' : '' ?><strong><?= e((string) $symptom['name_bn']) ?></strong>
    <?php endforeach; ?>
    <?php if ($plantName !== null): ?> · গাছ: <strong><?= e($plantName) ?></strong><?php endif; ?>
  </p>
</div>

<div class="notice notice--info">
  <span class="notice__icon" aria-hidden="true">i</span>
  <span><?= e($disclaimer) ?></span>
</div>

<?php if ($widened): ?>
  <div class="notice notice--warn">
    <span class="notice__icon" aria-hidden="true">⚠</span>
    <span>নির্দিষ্ট গাছে কিছু মেলেনি, তাই সব গাছের মধ্যে থেকে ফলাফল দেখানো হচ্ছে।</span>
  </div>
<?php endif; ?>

<?php if ($results === []): ?>
  <div class="empty-state">
    <h3>নির্দিষ্ট কোনো কারণ মেলেনি</h3>
    <p class="muted">লক্ষণগুলো <a href="/app/qa/ask">প্রশ্ন হিসেবে জিজ্ঞেস করুন</a> — ছবি দিলে বুঝতে সুবিধা হবে।</p>
    <a class="btn btn--ghost" href="/app/diagnose">আবার চেষ্টা করুন</a>
  </div>
<?php else: ?>
  <div class="stack">
    <?php foreach ($results as $result): ?>
      <?php $cls = DiagnosisEngine::labelClass((float) $result['confidence']); ?>
      <a class="card card--link" href="/app/problems/<?= e((string) $result['slug']) ?>">
        <div class="between">
          <h2 class="card__title mb-0"><?= e((string) $result['name_bn']) ?></h2>
          <span class="confidence confidence--<?= e($cls) ?>">
            <span class="confidence__bar"><span class="confidence__fill" style="width:<?= e((string) round($result['confidence'] * 100)) ?>%"></span></span>
            <?= e((string) $result['confidence_label']) ?>
          </span>
        </div>
        <div class="cluster">
          <span class="chip chip--muted"><?= e((string) config('content.problem_type.' . $result['type'])) ?></span>
          <span class="chip chip--<?= e((string) $result['severity']) ?>">
            ক্ষতি: <?= e((string) config('content.severity.' . $result['severity'])) ?>
          </span>
          <span class="small muted"><?= e(bn_num((int) $result['matched'])) ?>টি লক্ষণ মিলেছে</span>
        </div>
      </a>
    <?php endforeach; ?>
  </div>
<?php endif; ?>

<p class="cluster" style="margin-top:1.5rem">
  <a class="btn btn--ghost" href="/app/diagnose">নতুন করে শুরু করুন</a>
  <a class="btn btn--ghost" href="/app/qa/ask">প্রশ্ন করুন</a>
</p>
