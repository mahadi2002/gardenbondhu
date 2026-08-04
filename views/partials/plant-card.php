<?php
/**
 * @var array $plant
 * @var bool  $inApp
 */
$href = ($inApp ?? false) ? '/app/plants/' . $plant['slug'] : '/plants/' . $plant['slug'];
?>
<a class="card card--link" href="<?= e($href) ?>">
  <div class="card__media">
    <?php if (!empty($plant['hero_image'])): ?>
      <img src="<?= e((string) $plant['hero_image']) ?>" alt="" loading="lazy" width="400" height="300">
    <?php else: ?>
      <svg viewBox="0 0 64 64" aria-hidden="true" fill="#fff">
        <path d="M32 58V28c0-13 9-22 26-23 0 15-9 23-26 23Z" opacity=".85"/>
        <path d="M32 44C18 44 8 36 7 22c14 0 25 8 25 22Z" opacity=".55"/>
      </svg>
    <?php endif; ?>
  </div>

  <h3 class="card__title"><?= e((string) $plant['name_bn']) ?></h3>

  <?php if (!empty($plant['name_en'])): ?>
    <p class="card__meta"><?= e((string) $plant['name_en']) ?></p>
  <?php endif; ?>

  <p class="small"><?= e(str_excerpt((string) ($plant['summary_bn'] ?? ''), 96)) ?></p>

  <div class="cluster">
    <span class="chip chip--muted"><?= e((string) config('content.sunlight.' . $plant['sunlight'])) ?></span>
    <span class="chip chip--muted"><?= e((string) config('content.water.' . $plant['water_need'])) ?></span>
    <span class="chip <?= $plant['difficulty'] === 'easy' ? 'chip--low' : ($plant['difficulty'] === 'hard' ? 'chip--high' : 'chip--medium') ?>">
      <?= e((string) config('content.difficulty.' . $plant['difficulty'])) ?>
    </span>
  </div>
</a>
