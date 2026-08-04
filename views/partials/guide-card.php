<?php
/**
 * @var array $guide
 * @var bool  $inApp
 */
$href = ($inApp ?? false) ? '/app/guides/' . $guide['slug'] : '/guides/' . $guide['slug'];
?>
<a class="card card--link" href="<?= e($href) ?>">
  <div class="cluster">
    <span class="chip chip--muted"><?= e((string) config('content.guide_category.' . $guide['category'])) ?></span>
    <?php if (!empty($guide['read_minutes'])): ?>
      <span class="small muted"><?= e(bn_num((int) $guide['read_minutes'])) ?> মিনিট পড়া</span>
    <?php endif; ?>
  </div>

  <h3 class="card__title"><?= e((string) $guide['title_bn']) ?></h3>
  <p class="small mb-0"><?= e(str_excerpt((string) ($guide['excerpt_bn'] ?? ''), 120)) ?></p>
</a>
