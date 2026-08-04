<?php
/**
 * @var array $guides, $filters
 * @var bool  $inApp, $isSubscribed
 * @var int   $total
 */
use App\Core\View;

$this->layout($inApp ? 'layouts/app' : 'layouts/public', ['title' => 'গাইড']);
$base = $inApp ? '/app/guides' : '/guides';
?>
<?php if (!$inApp): ?><section class="section"><div class="wrap"><?php endif; ?>

<div class="page-head">
  <h1>গাইড</h1>
  <p class="muted"><?= e(bn_num($total)) ?>টি গাইড</p>
</div>

<form class="filters" method="get" action="<?= e($base) ?>" data-auto-submit>
  <div class="filters__row">
    <div class="field">
      <label for="gf-cat">বিষয়</label>
      <select class="input" id="gf-cat" name="category">
        <option value="">সব বিষয়</option>
        <?php foreach ((array) config('content.guide_category') as $value => $label): ?>
          <option value="<?= e((string) $value) ?>" <?= (string) ($filters['category'] ?? '') === (string) $value ? ' selected' : '' ?>>
            <?= e($label) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>
  </div>
  <div class="cluster flash-slot">
    <button class="btn btn--sm" type="submit">ফিল্টার করুন</button>
    <a class="btn btn--ghost btn--sm" href="<?= e($base) ?>">সব দেখান</a>
  </div>
</form>

<?php if ($guides === []): ?>
  <div class="empty-state"><h3>কোনো গাইড পাওয়া যায়নি</h3></div>
<?php else: ?>
  <div class="grid grid--3">
    <?php foreach ($guides as $guide): ?>
      <?= View::partial('partials/guide-card', ['guide' => $guide, 'inApp' => $inApp]) ?>
    <?php endforeach; ?>
  </div>
<?php endif; ?>

<?php if (!$inApp && !$isSubscribed): ?>
  <?= View::partial('partials/paywall', get_defined_vars() + ['what' => 'গাইডের পুরো লেখা']) ?>
<?php endif; ?>

<?php if (!$inApp): ?></div></section><?php endif; ?>
