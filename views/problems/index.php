<?php
/**
 * @var array $problems, $filters
 * @var bool  $inApp, $isLoggedIn
 * @var int   $total, $page, $perPage
 */
use App\Core\View;

$this->layout($inApp ? 'layouts/app' : 'layouts/public', ['title' => 'রোগ ও পোকা']);
$base = $inApp ? '/app/problems' : '/problems';
?>
<?php if (!$inApp): ?><section class="section"><div class="wrap"><?php endif; ?>

<div class="page-head">
  <h1>রোগ ও পোকা</h1>
  <p class="muted"><?= e(bn_num($total)) ?>টি সমস্যা · লক্ষণ দেখে দ্রুত মেলাতে <a href="<?= $inApp ? '/app/diagnose' : '/#diagnose-demo' ?>">রোগ নির্ণয় টুল</a> ব্যবহার করুন</p>
</div>

<form class="filters" method="get" action="<?= e($base) ?>" data-auto-submit>
  <div class="filters__row">
    <div class="field">
      <label for="pf-type">ধরন</label>
      <select class="input" id="pf-type" name="type">
        <option value="">সব ধরন</option>
        <?php foreach ((array) config('content.problem_type') as $value => $label): ?>
          <option value="<?= e((string) $value) ?>" <?= (string) ($filters['type'] ?? '') === (string) $value ? ' selected' : '' ?>>
            <?= e($label) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="field">
      <label for="pf-sev">ক্ষতির মাত্রা</label>
      <select class="input" id="pf-sev" name="severity">
        <option value="">সব</option>
        <?php foreach ((array) config('content.severity') as $value => $label): ?>
          <option value="<?= e((string) $value) ?>" <?= (string) ($filters['severity'] ?? '') === (string) $value ? ' selected' : '' ?>>
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

<?php if ($problems === []): ?>
  <div class="empty-state">
    <h3>কিছু পাওয়া যায়নি</h3>
    <p class="muted">অন্য ফিল্টারে চেষ্টা করুন।</p>
  </div>
<?php else: ?>
  <div class="grid grid--3">
    <?php foreach ($problems as $problem): ?>
      <?= View::partial('partials/problem-card', ['problem' => $problem, 'inApp' => $inApp]) ?>
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
  <?= View::partial('partials/register-wall', get_defined_vars() + ['what' => 'প্রতিটি সমস্যার সমাধান']) ?>
<?php endif; ?>

<?php if (!$inApp): ?></div></section><?php endif; ?>
