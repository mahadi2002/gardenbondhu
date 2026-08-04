<?php
/**
 * @var string $query
 * @var array  $results
 * @var array  $suggestions
 */
use App\Core\View;

$this->layout('layouts/public', ['title' => $query !== '' ? '"' . $query . '" খোঁজার ফলাফল' : 'খুঁজুন']);
?>
<section class="section">
  <div class="wrap">
    <form method="get" action="/search" class="cluster mb-2">
      <label class="visually-hidden" for="search-q">খুঁজুন</label>
      <input class="input max-w-sm" type="search" id="search-q" name="q" value="<?= e($query) ?>"
             placeholder="যেমন: পাতা হলুদ, পুদিনা, মিলিবাগ">
      <button class="btn" type="submit">খুঁজুন</button>
    </form>

    <?php if ($query === ''): ?>
      <p class="muted">গাছের নাম, রোগের নাম বা লক্ষণ লিখে খুঁজুন।</p>
    <?php elseif ($results['total'] === 0): ?>
      <div class="empty-state">
        <h3>কিছু পাওয়া যায়নি। অন্য শব্দে খুঁজে দেখুন, বা প্রশ্ন করুন।</h3>
        <?php if ($suggestions !== []): ?>
          <p class="muted">জনপ্রিয় গাছ:</p>
          <p class="cluster center">
            <?php foreach ($suggestions as $s): ?>
              <a class="chip" href="/plants/<?= e((string) $s['slug']) ?>"><?= e((string) $s['name_bn']) ?></a>
            <?php endforeach; ?>
          </p>
        <?php endif; ?>
      </div>
    <?php else: ?>
      <?php if ($results['plants'] !== []): ?>
        <section class="section--tight">
          <h2>গাছ</h2>
          <div class="grid grid--3">
            <?php foreach ($results['plants'] as $plant): ?>
              <?= View::partial('partials/plant-card', ['plant' => $plant, 'inApp' => false]) ?>
            <?php endforeach; ?>
          </div>
        </section>
      <?php endif; ?>

      <?php if ($results['problems'] !== []): ?>
        <section class="section--tight">
          <h2>রোগ ও পোকা</h2>
          <div class="grid grid--3">
            <?php foreach ($results['problems'] as $problem): ?>
              <?= View::partial('partials/problem-card', ['problem' => $problem, 'inApp' => false]) ?>
            <?php endforeach; ?>
          </div>
        </section>
      <?php endif; ?>

      <?php if ($results['guides'] !== []): ?>
        <section class="section--tight">
          <h2>গাইড</h2>
          <div class="grid grid--3">
            <?php foreach ($results['guides'] as $guide): ?>
              <?= View::partial('partials/guide-card', ['guide' => $guide, 'inApp' => false]) ?>
            <?php endforeach; ?>
          </div>
        </section>
      <?php endif; ?>
    <?php endif; ?>
  </div>
</section>
