<?php
/**
 * @var array $plants
 * @var array $categories
 * @var array $filters
 * @var bool  $inApp
 * @var int   $total, $page, $perPage
 */
use App\Core\View;

$this->layout($inApp ? 'layouts/app' : 'layouts/public', ['title' => 'গাছের তালিকা']);
$base = $inApp ? '/app/plants' : '/plants';
?>
<?php if (!$inApp): ?><section class="section"><div class="wrap"><?php endif; ?>

<div class="page-head">
  <h1>গাছের তালিকা</h1>
  <p class="muted">
    <?= e(bn_num($total)) ?>টি গাছ · জায়গা, রোদ আর যত্নের ঝক্কি দেখে বেছে নিন
  </p>
</div>

<form class="filters" method="get" action="<?= e($base) ?>" data-auto-submit>
  <div class="filters__row">
    <div class="field">
      <label for="f-cat">ধরন</label>
      <select class="input" id="f-cat" name="category_id">
        <option value="">সব ধরন</option>
        <?php foreach ($categories as $category): ?>
          <option value="<?= e((string) $category['id']) ?>"
            <?= (string) ($filters['category_id'] ?? '') === (string) $category['id'] ? ' selected' : '' ?>>
            <?= e((string) $category['name_bn']) ?> (<?= e(bn_num((int) $category['plant_count'])) ?>)
          </option>
        <?php endforeach; ?>
      </select>
    </div>

    <?php
    $selects = [
        'space_type' => ['জায়গা', (array) config('content.space')],
        'sunlight'   => ['রোদ',   (array) config('content.sunlight')],
        'water_need' => ['পানি',  (array) config('content.water')],
        'difficulty' => ['কষ্টসাধ্যতা', (array) config('content.difficulty')],
    ];
    foreach ($selects as $name => [$label, $options]):
    ?>
      <div class="field">
        <label for="f-<?= e($name) ?>"><?= e($label) ?></label>
        <select class="input" id="f-<?= e($name) ?>" name="<?= e($name) ?>">
          <option value="">সব</option>
          <?php foreach ($options as $value => $optionLabel): ?>
            <option value="<?= e((string) $value) ?>"
              <?= (string) ($filters[$name] ?? '') === (string) $value ? ' selected' : '' ?>>
              <?= e($optionLabel) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
    <?php endforeach; ?>
  </div>

  <div class="cluster flash-slot">
    <label class="check">
      <input type="checkbox" name="toxic_to_pets" value="0"
             <?= ($filters['toxic_to_pets'] ?? '') === '0' ? ' checked' : '' ?>>
      <span>পোষা প্রাণীর জন্য নিরাপদ গাছ শুধু</span>
    </label>
    <button class="btn btn--sm" type="submit">ফিল্টার করুন</button>
    <a class="btn btn--ghost btn--sm" href="<?= e($base) ?>">সব দেখান</a>
  </div>
</form>

<?php if ($plants === []): ?>
  <div class="empty-state">
    <h3>এই শর্তে কোনো গাছ পাওয়া যায়নি</h3>
    <p class="muted">একটা-দুটো ফিল্টার তুলে দিয়ে আবার দেখুন।</p>
    <a class="btn btn--ghost" href="<?= e($base) ?>">সব গাছ দেখুন</a>
  </div>
<?php else: ?>
  <div class="grid grid--3">
    <?php foreach ($plants as $plant): ?>
      <?= View::partial('partials/plant-card', ['plant' => $plant, 'inApp' => $inApp]) ?>
    <?php endforeach; ?>
  </div>

  <?php
  $pages = (int) ceil($total / max(1, $perPage));
  if ($pages > 1):
      $query = $filters;
  ?>
    <nav class="pagination" aria-label="পাতা">
      <?php for ($p = 1; $p <= min($pages, 12); $p++): ?>
        <?php $query['page'] = $p; ?>
        <a class="btn btn--sm <?= $p === $page ? '' : 'btn--ghost' ?>"
           href="<?= e($base . '?' . http_build_query($query)) ?>"
           <?= $p === $page ? ' aria-current="page"' : '' ?>><?= e(bn_num($p)) ?></a>
      <?php endfor; ?>
    </nav>
  <?php endif; ?>
<?php endif; ?>

<?php if (!$inApp && !$isLoggedIn): ?>
  <?= View::partial('partials/register-wall', get_defined_vars() + ['what' => 'প্রতিটি গাছের পূর্ণ যত্ন-নির্দেশিকা']) ?>
<?php endif; ?>

<?php if (!$inApp): ?></div></section><?php endif; ?>
