<?php
/**
 * Gated plant finder (spec §8.1). Same filter shape as plants/index, but
 * always inside the app shell and always subscribed — this view is never
 * reached signed out.
 *
 * @var array $plants, $categories, $filters
 * @var int   $total
 */
use App\Core\View;

$this->layout('layouts/app', ['title' => 'গাছ খুঁজুন']);
?>
<div class="page-head">
  <h1>গাছ খুঁজুন</h1>
  <p class="muted">জায়গা, রোদ আর যত্নের ঝক্কি বলুন — উপযুক্ত গাছ বেছে দিচ্ছি।</p>
</div>

<form class="filters" method="get" action="/app/plants/finder" data-auto-submit>
  <div class="filters__row">
    <div class="field">
      <label for="ff-cat">ধরন</label>
      <select class="input" id="ff-cat" name="category_id">
        <option value="">সব ধরন</option>
        <?php foreach ($categories as $category): ?>
          <option value="<?= e((string) $category['id']) ?>"
            <?= (string) ($filters['category_id'] ?? '') === (string) $category['id'] ? ' selected' : '' ?>>
            <?= e((string) $category['name_bn']) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>

    <?php
    $selects = [
        'space_type' => ['জায়গা', (array) config('content.space')],
        'sunlight'   => ['রোদ',   (array) config('content.sunlight')],
        'water_need' => ['পানি দেওয়ার সুযোগ', (array) config('content.water')],
        'difficulty' => ['অভিজ্ঞতা', (array) config('content.difficulty')],
    ];
    foreach ($selects as $name => [$label, $options]):
    ?>
      <div class="field">
        <label for="ff-<?= e($name) ?>"><?= e($label) ?></label>
        <select class="input" id="ff-<?= e($name) ?>" name="<?= e($name) ?>">
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
      <span>পোষা প্রাণীর জন্য নিরাপদ শুধু</span>
    </label>
    <button class="btn btn--sm" type="submit">খুঁজুন</button>
    <a class="btn btn--ghost btn--sm" href="/app/plants/finder">শুরু থেকে</a>
  </div>
</form>

<?php if ($plants === []): ?>
  <div class="empty-state">
    <h3>এই শর্তে কোনো গাছ পাওয়া যায়নি</h3>
    <p class="muted">একটা-দুটো ফিল্টার তুলে দিয়ে আবার দেখুন।</p>
  </div>
<?php else: ?>
  <p class="muted"><?= e(bn_num($total)) ?>টি গাছ মিলেছে</p>
  <div class="grid grid--3">
    <?php foreach ($plants as $plant): ?>
      <?= View::partial('partials/plant-card', ['plant' => $plant, 'inApp' => true]) ?>
    <?php endforeach; ?>
  </div>
<?php endif; ?>
