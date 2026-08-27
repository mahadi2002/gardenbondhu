<?php
/**
 * One template for both the public teaser and the logged-in page. The full
 * detail sections only render when $isLoggedIn is true — free registration,
 * not a paywall.
 *
 * @var array $plant, $seasons, $problems, $related
 * @var bool  $isLoggedIn, $inApp
 */
use App\Core\Markdown;
use App\Core\View;

$this->layout($inApp ? 'layouts/app' : 'layouts/public', [
    'title'       => (string) $plant['name_bn'],
    'description' => str_excerpt((string) $plant['summary_bn'], 150),
]);

$problemBase = $inApp ? '/app/problems/' : '/problems/';
?>
<?php if (!$inApp): ?><section class="section"><div class="wrap"><?php endif; ?>

<article>
  <div class="page-head">
    <?php if (!empty($plant['category_bn'])): ?>
      <p class="eyebrow"><?= e((string) $plant['category_bn']) ?></p>
    <?php endif; ?>

    <h1><?= e((string) $plant['name_bn']) ?></h1>
    <p class="muted">
      <?= e((string) ($plant['name_en'] ?? '')) ?>
      <?php if (!empty($plant['scientific_name'])): ?>
        · <em><?= e((string) $plant['scientific_name']) ?></em>
      <?php endif; ?>
    </p>
  </div>

  <?php if (!empty($plant['hero_image'])): ?>
    <img class="card__media" src="<?= e((string) $plant['hero_image']) ?>"
         alt="<?= e((string) $plant['name_bn']) ?>" width="1180" height="600">
  <?php endif; ?>

  <dl class="quick-facts">
    <div class="quick-fact">
      <dt>রোদ</dt><dd><?= e((string) config('content.sunlight.' . $plant['sunlight'])) ?></dd>
    </div>
    <div class="quick-fact">
      <dt>পানি</dt><dd><?= e((string) config('content.water.' . $plant['water_need'])) ?></dd>
    </div>
    <div class="quick-fact">
      <dt>কষ্টসাধ্যতা</dt><dd><?= e((string) config('content.difficulty.' . $plant['difficulty'])) ?></dd>
    </div>
    <div class="quick-fact">
      <dt>টবের মাপ</dt><dd><?= e((string) ($plant['pot_size_cm'] ?: '—')) ?></dd>
    </div>
    <?php if (!empty($plant['harvest_days'])): ?>
      <div class="quick-fact">
        <dt>ফসল</dt><dd><?= e(bn_num((int) $plant['harvest_days'])) ?> দিনে</dd>
      </div>
    <?php endif; ?>
  </dl>

  <?php if ((int) $plant['toxic_to_pets'] === 1): ?>
    <p class="safety-note">
      ⚠️ এই গাছ পোষা প্রাণীর জন্য বিষাক্ত। বিড়াল-কুকুরের নাগালের বাইরে রাখুন।
    </p>
  <?php endif; ?>

  <div class="prose">
    <p class="lede"><?= e((string) $plant['summary_bn']) ?></p>
  </div>

  <?php if ($isLoggedIn): ?>
    <?php if (!empty($plant['body_bn'])): ?>
      <div class="prose"><?= Markdown::render((string) $plant['body_bn']) ?></div>
    <?php endif; ?>

    <?php
    $sections = [
        'soil_mix'        => 'মাটি ও টব',
        'fertilizer_note' => 'সার',
        'pruning_note'    => 'ছাঁটাই',
        'propagation'     => 'চারা তৈরি',
    ];
    foreach ($sections as $field => $heading):
        if (empty($plant[$field])) { continue; }
    ?>
      <section class="prose">
        <h2><?= e($heading) ?></h2>
        <?= Markdown::render((string) $plant[$field]) ?>
      </section>
    <?php endforeach; ?>

    <?php if (!empty($plant['water_interval_days'])): ?>
      <p class="formula">
        গড়ে প্রতি <?= e(bn_num((int) $plant['water_interval_days'])) ?> দিনে একবার পানি।
        ঋতু আর জায়গা অনুযায়ী এটা বদলায় — <a href="/app/tools">পানির হিসাবের টুল</a> দিয়ে
        আপনার টবের জন্য মাপটা বের করে নিন।
      </p>
    <?php endif; ?>
  <?php else: ?>
    <div class="paywall-cut prose">
      <p class="muted">
        মাটি কেমন হবে, কত দিন পরপর পানি, কোন সার কখন, কীভাবে ছাঁটবেন আর কীভাবে নতুন চারা
        বানাবেন — পুরোটা লেখা আছে।
      </p>
    </div>
    <?= View::partial('partials/register-wall', get_defined_vars() + ['what' => 'এই গাছের পূর্ণ যত্ন-নির্দেশিকা']) ?>
  <?php endif; ?>

  <?php if ($seasons !== []): ?>
    <section>
      <h2>বছরজুড়ে কী করবেন</h2>
      <div class="season-strip">
        <?php foreach ($seasons as $season): ?>
          <span class="chip chip--muted">
            <?= e((string) $season['name_bn']) ?> — <?= e((string) config('content.activity.' . $season['activity'])) ?>
          </span>
        <?php endforeach; ?>
      </div>
    </section>
  <?php endif; ?>

  <?php if ($problems !== []): ?>
    <section>
      <h2>এই গাছের সাধারণ সমস্যা</h2>
      <div class="grid grid--3">
        <?php foreach ($problems as $problem): ?>
          <a class="card card--link" href="<?= e($problemBase . $problem['slug']) ?>">
            <span class="chip chip--<?= e((string) $problem['severity']) ?>">
              <?= e((string) config('content.problem_type.' . $problem['type'])) ?>
            </span>
            <h3 class="card__title"><?= e((string) $problem['name_bn']) ?></h3>
            <p class="card__meta mb-0">
              <?= e(match ((string) $problem['frequency']) {
                  'common' => 'প্রায়ই হয়', 'occasional' => 'মাঝে মাঝে', default => 'কদাচিৎ',
              }) ?>
            </p>
          </a>
        <?php endforeach; ?>
      </div>
    </section>
  <?php endif; ?>

  <?php if ($related !== []): ?>
    <section>
      <h2>একই ধরনের গাছ</h2>
      <div class="grid grid--4">
        <?php foreach ($related as $other): ?>
          <?= View::partial('partials/plant-card', ['plant' => $other, 'inApp' => $inApp]) ?>
        <?php endforeach; ?>
      </div>
    </section>
  <?php endif; ?>
</article>

<?php if (!$inApp): ?></div></section><?php endif; ?>
