<?php
/**
 * @var array|null $plant
 * @var array $categories, $seasons, $plantSeasons, $problems, $linked
 */
$isNew = $plant === null;
$this->layout('layouts/admin', ['title' => $isNew ? 'নতুন গাছ' : (string) $plant['name_bn']]);

$action = $isNew ? '/admin/plants' : '/admin/plants/' . $plant['id'];
$spaceSelected = $isNew ? [] : explode(',', (string) $plant['space_type']);
?>
<div class="page-head"><h1><?= $isNew ? 'নতুন গাছ' : e((string) $plant['name_bn']) ?></h1></div>

<form method="post" action="<?= e($action) ?>" data-guard>
  <?= csrf_field() ?>

  <div class="grid grid--2">
    <div class="field">
      <label for="p-slug">Slug</label>
      <input class="input" type="text" id="p-slug" name="slug" required pattern="[a-z0-9\-]+"
             value="<?= e((string) ($plant['slug'] ?? old('slug'))) ?>">
    </div>
    <div class="field">
      <label for="p-cat">ধরন</label>
      <select class="input" id="p-cat" name="category_id">
        <option value="">—</option>
        <?php foreach ($categories as $c): ?>
          <option value="<?= e((string) $c['id']) ?>" <?= (int) ($plant['category_id'] ?? 0) === (int) $c['id'] ? ' selected' : '' ?>><?= e((string) $c['name_bn']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="field">
      <label for="p-name-bn">বাংলা নাম</label>
      <input class="input" type="text" id="p-name-bn" name="name_bn" required value="<?= e((string) ($plant['name_bn'] ?? old('name_bn'))) ?>">
    </div>
    <div class="field">
      <label for="p-name-en">English নাম</label>
      <input class="input" type="text" id="p-name-en" name="name_en" value="<?= e((string) ($plant['name_en'] ?? '')) ?>">
    </div>
    <div class="field">
      <label for="p-sci">Scientific name</label>
      <input class="input" type="text" id="p-sci" name="scientific_name" value="<?= e((string) ($plant['scientific_name'] ?? '')) ?>">
    </div>
    <div class="field">
      <label for="p-diff">কষ্টসাধ্যতা</label>
      <select class="input" id="p-diff" name="difficulty" required>
        <?php foreach ((array) config('content.difficulty') as $v => $l): ?>
          <option value="<?= e((string) $v) ?>" <?= ($plant['difficulty'] ?? 'easy') === $v ? ' selected' : '' ?>><?= e($l) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="field">
      <label for="p-sun">রোদ</label>
      <select class="input" id="p-sun" name="sunlight" required>
        <?php foreach ((array) config('content.sunlight') as $v => $l): ?>
          <option value="<?= e((string) $v) ?>" <?= ($plant['sunlight'] ?? 'partial') === $v ? ' selected' : '' ?>><?= e($l) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="field">
      <label for="p-water">পানি</label>
      <select class="input" id="p-water" name="water_need" required>
        <?php foreach ((array) config('content.water') as $v => $l): ?>
          <option value="<?= e((string) $v) ?>" <?= ($plant['water_need'] ?? 'medium') === $v ? ' selected' : '' ?>><?= e($l) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="field">
      <label for="p-habit">Growth habit</label>
      <select class="input" id="p-habit" name="growth_habit">
        <?php foreach ((array) config('content.growth_habit') as $v => $l): ?>
          <option value="<?= e((string) $v) ?>" <?= ($plant['growth_habit'] ?? 'shrub') === $v ? ' selected' : '' ?>><?= e($l) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
  </div>

  <fieldset>
    <legend>জায়গা</legend>
    <div class="cluster">
      <?php foreach ((array) config('content.space') as $v => $l): ?>
        <label class="check">
          <input type="checkbox" name="space_type[]" value="<?= e((string) $v) ?>" <?= in_array($v, $spaceSelected, true) ? ' checked' : '' ?>>
          <span><?= e($l) ?></span>
        </label>
      <?php endforeach; ?>
    </div>
  </fieldset>

  <div class="grid grid--2">
    <div class="field">
      <label for="p-pot">টবের মাপ (টেক্সট)</label>
      <input class="input" type="text" id="p-pot" name="pot_size_cm" value="<?= e((string) ($plant['pot_size_cm'] ?? '')) ?>" placeholder="যেমন: ২৫-৩০ সেমি">
    </div>
    <div class="field">
      <label for="p-hero">Hero image URL</label>
      <input class="input" type="text" id="p-hero" name="hero_image" value="<?= e((string) ($plant['hero_image'] ?? '')) ?>">
    </div>
    <div class="field">
      <label for="p-wi">পানির বিরতি (দিন)</label>
      <input class="input" type="number" id="p-wi" name="water_interval_days" min="1" max="60" value="<?= e((string) ($plant['water_interval_days'] ?? '')) ?>">
    </div>
    <div class="field">
      <label for="p-fi">সারের বিরতি (দিন)</label>
      <input class="input" type="number" id="p-fi" name="fertilizer_interval_days" min="1" max="365" value="<?= e((string) ($plant['fertilizer_interval_days'] ?? '')) ?>">
    </div>
    <div class="field">
      <label for="p-harvest">ফসলের দিন</label>
      <input class="input" type="number" id="p-harvest" name="harvest_days" min="1" max="2000" value="<?= e((string) ($plant['harvest_days'] ?? '')) ?>">
    </div>
  </div>

  <div class="field">
    <label for="p-summary">সংক্ষিপ্ত বিবরণ (FREE preview)</label>
    <textarea class="input" id="p-summary" name="summary_bn" required maxlength="2000"><?= e((string) ($plant['summary_bn'] ?? old('summary_bn'))) ?></textarea>
  </div>

  <div class="field">
    <label for="p-body">পূর্ণ বিবরণ — Markdown (PAID)</label>
    <textarea class="input min-h-md" id="p-body" name="body_bn"><?= e((string) ($plant['body_bn'] ?? '')) ?></textarea>
  </div>

  <div class="grid grid--2">
    <div class="field">
      <label for="p-soil">মাটির মিশ্রণ</label>
      <textarea class="input" id="p-soil" name="soil_mix"><?= e((string) ($plant['soil_mix'] ?? '')) ?></textarea>
    </div>
    <div class="field">
      <label for="p-fert">সার নোট</label>
      <textarea class="input" id="p-fert" name="fertilizer_note"><?= e((string) ($plant['fertilizer_note'] ?? '')) ?></textarea>
    </div>
    <div class="field">
      <label for="p-prune">ছাঁটাই নোট</label>
      <textarea class="input" id="p-prune" name="pruning_note"><?= e((string) ($plant['pruning_note'] ?? '')) ?></textarea>
    </div>
    <div class="field">
      <label for="p-prop">চারা তৈরি</label>
      <textarea class="input" id="p-prop" name="propagation"><?= e((string) ($plant['propagation'] ?? '')) ?></textarea>
    </div>
  </div>

  <?php if ($problems !== []): ?>
    <fieldset>
      <legend>সাধারণ সমস্যা (linked problems)</legend>
      <div class="cluster">
        <?php foreach ($problems as $p): ?>
          <label class="check">
            <input type="checkbox" name="problems[]" value="<?= e((string) $p['id']) ?>" <?= in_array((int) $p['id'], $linked, true) ? ' checked' : '' ?>>
            <span><?= e((string) $p['name_bn']) ?></span>
          </label>
        <?php endforeach; ?>
      </div>
    </fieldset>
  <?php endif; ?>

  <label class="check mb-1-25">
    <input type="checkbox" name="toxic_to_pets" value="1" <?= (int) ($plant['toxic_to_pets'] ?? 0) === 1 ? ' checked' : '' ?>>
    <span>পোষা প্রাণীর জন্য বিষাক্ত</span>
  </label>

  <label class="check mb-1-5">
    <input type="checkbox" name="is_published" value="1" <?= (int) ($plant['is_published'] ?? 0) === 1 ? ' checked' : '' ?>>
    <span>Published (সাইটে দেখা যাবে)</span>
  </label>

  <button class="btn btn--accent" type="submit">সংরক্ষণ করুন</button>
</form>

<?php if (!$isNew): ?>
  <form class="mt-1" method="post" action="/admin/plants/<?= e((string) $plant['id']) ?>/delete"
        data-confirm="গাছটি মুছে ফেলবেন?">
    <?= csrf_field() ?>
    <button class="btn btn--danger" type="submit">মুছুন</button>
  </form>
<?php endif; ?>
