<?php
/**
 * @var array $guide, $related
 * @var bool  $isSubscribed, $mayReadBody, $inApp
 */
use App\Core\Markdown;
use App\Core\View;

$this->layout($inApp ? 'layouts/app' : 'layouts/public', [
    'title'       => (string) $guide['title_bn'],
    'description' => str_excerpt((string) $guide['excerpt_bn'], 150),
]);

$guideBase = $inApp ? '/app/guides/' : '/guides/';
?>
<?php if (!$inApp): ?><section class="section"><div class="wrap"><?php endif; ?>

<article class="prose max-w-page">
  <div class="page-head">
    <p class="eyebrow"><?= e((string) config('content.guide_category.' . $guide['category'])) ?></p>
    <h1><?= e((string) $guide['title_bn']) ?></h1>
    <?php if (!empty($guide['read_minutes'])): ?>
      <p class="muted"><?= e(bn_num((int) $guide['read_minutes'])) ?> মিনিট পড়া</p>
    <?php endif; ?>
  </div>

  <div class="prose">
    <p class="lede"><?= e((string) $guide['excerpt_bn']) ?></p>

    <?php if ($mayReadBody && !empty($guide['body_bn'])): ?>
      <?= Markdown::render((string) $guide['body_bn']) ?>
    <?php elseif (!$mayReadBody): ?>
      <div class="paywall-cut"><p class="muted">বাকি অংশে ধাপে ধাপে সব কিছু ব্যাখ্যা করা আছে।</p></div>
    <?php endif; ?>
  </div>

  <?php if (!$mayReadBody): ?>
    <?= View::partial('partials/paywall', get_defined_vars() + ['what' => 'এই গাইডের পুরো লেখা']) ?>
  <?php endif; ?>

  <?php if ($related !== []): ?>
    <section>
      <h2>আরও গাইড</h2>
      <div class="grid grid--3">
        <?php foreach ($related as $other): ?>
          <?php if ((int) $other['id'] === (int) $guide['id']) { continue; } ?>
          <?= View::partial('partials/guide-card', ['guide' => $other, 'inApp' => $inApp]) ?>
        <?php endforeach; ?>
      </div>
    </section>
  <?php endif; ?>
</article>

<?php if (!$inApp): ?></div></section><?php endif; ?>
