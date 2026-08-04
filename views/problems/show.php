<?php
/**
 * @var array $problem, $symptoms, $plants
 * @var bool  $isSubscribed, $inApp
 */
use App\Core\Markdown;
use App\Core\View;

$this->layout($inApp ? 'layouts/app' : 'layouts/public', [
    'title'       => (string) $problem['name_bn'],
    'description' => str_excerpt((string) $problem['description_bn'], 150),
]);

$plantBase = $inApp ? '/app/plants/' : '/plants/';
?>
<?php if (!$inApp): ?><section class="section"><div class="wrap"><?php endif; ?>

<article>
  <div class="page-head">
    <div class="cluster">
      <span class="chip chip--muted"><?= e((string) config('content.problem_type.' . $problem['type'])) ?></span>
      <span class="chip chip--<?= e((string) $problem['severity']) ?>">
        ক্ষতি: <?= e((string) config('content.severity.' . $problem['severity'])) ?>
      </span>
    </div>
    <h1><?= e((string) $problem['name_bn']) ?></h1>
    <?php if (!empty($problem['name_en'])): ?><p class="muted"><?= e((string) $problem['name_en']) ?></p><?php endif; ?>
  </div>

  <div class="prose">
    <p class="lede"><?= e((string) $problem['description_bn']) ?></p>

    <?php if (!empty($problem['identification_bn'])): ?>
      <h2>কীভাবে চিনবেন</h2>
      <?= Markdown::render((string) $problem['identification_bn']) ?>
    <?php endif; ?>
  </div>

  <?php if ($symptoms !== []): ?>
    <section>
      <h2>এই সমস্যার লক্ষণ</h2>
      <ul>
        <?php foreach ($symptoms as $symptom): ?>
          <li><?= e((string) $symptom['name_bn']) ?></li>
        <?php endforeach; ?>
      </ul>
    </section>
  <?php endif; ?>

  <?php if ($isSubscribed): ?>
    <?php if (!empty($problem['organic_remedy_bn'])): ?>
      <div class="remedy remedy--organic">
        <h3>জৈব সমাধান</h3>
        <?= Markdown::render((string) $problem['organic_remedy_bn']) ?>
      </div>
    <?php endif; ?>

    <?php if (!empty($problem['chemical_remedy_bn'])): ?>
      <div class="remedy remedy--chemical">
        <h3>রাসায়নিক সমাধান</h3>
        <?= Markdown::render((string) $problem['chemical_remedy_bn']) ?>
      </div>
    <?php endif; ?>

    <?php if (!empty($problem['prevention_bn'])): ?>
      <section class="prose">
        <h2>ভবিষ্যতে ঠেকাতে</h2>
        <?= Markdown::render((string) $problem['prevention_bn']) ?>
      </section>
    <?php endif; ?>

    <p class="safety-note">
      <?= e((string) ($problem['safety_note_bn'] ?: 'রাসায়নিক ব্যবহারের আগে মাত্রা ভালোভাবে দেখে নিন এবং শিশু ও পোষা প্রাণী থেকে দূরে রাখুন।')) ?>
    </p>
  <?php else: ?>
    <?= View::partial('partials/paywall', get_defined_vars() + ['what' => 'জৈব ও রাসায়নিক সমাধান']) ?>
  <?php endif; ?>

  <?php if ($plants !== []): ?>
    <section>
      <h2>যেসব গাছে এটি বেশি হয়</h2>
      <div class="cluster">
        <?php foreach ($plants as $plant): ?>
          <a class="chip" href="<?= e($plantBase . $plant['slug']) ?>"><?= e((string) $plant['name_bn']) ?></a>
        <?php endforeach; ?>
      </div>
    </section>
  <?php endif; ?>
</article>

<?php if (!$inApp): ?></div></section><?php endif; ?>
