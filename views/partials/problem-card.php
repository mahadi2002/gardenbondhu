<?php
/**
 * @var array $problem
 * @var bool  $inApp
 */
$href = ($inApp ?? false) ? '/app/problems/' . $problem['slug'] : '/problems/' . $problem['slug'];
?>
<a class="card card--link" href="<?= e($href) ?>">
  <div class="cluster">
    <span class="chip chip--muted"><?= e((string) config('content.problem_type.' . $problem['type'])) ?></span>
    <span class="chip chip--<?= e((string) $problem['severity']) ?>">
      ক্ষতি: <?= e((string) config('content.severity.' . $problem['severity'])) ?>
    </span>
  </div>

  <h3 class="card__title"><?= e((string) $problem['name_bn']) ?></h3>

  <?php if (!empty($problem['name_en'])): ?>
    <p class="card__meta"><?= e((string) $problem['name_en']) ?></p>
  <?php endif; ?>

  <p class="small mb-0"><?= e(str_excerpt((string) ($problem['description_bn'] ?? ''), 110)) ?></p>
</a>
