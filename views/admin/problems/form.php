<?php
/**
 * @var array|null $problem
 * @var array $symptoms
 * @var array<int,int> $weights symptom_id => weight
 */
$isNew = $problem === null;
$this->layout('layouts/admin', ['title' => $isNew ? 'নতুন সমস্যা' : (string) $problem['name_bn']]);

$action = $isNew ? '/admin/problems' : '/admin/problems/' . $problem['id'];

$byPart = [];
foreach ($symptoms as $s) {
    $byPart[(string) $s['body_part']][] = $s;
}
$partLabels = (array) config('content.body_parts');
?>
<div class="page-head"><h1><?= $isNew ? 'নতুন সমস্যা' : e((string) $problem['name_bn']) ?></h1></div>

<form method="post" action="<?= e($action) ?>" data-guard>
  <?= csrf_field() ?>

  <div class="grid grid--2">
    <div class="field">
      <label for="pr-slug">Slug</label>
      <input class="input" type="text" id="pr-slug" name="slug" required pattern="[a-z0-9\-]+"
             value="<?= e((string) ($problem['slug'] ?? old('slug'))) ?>">
    </div>
    <div class="field">
      <label for="pr-name-bn">বাংলা নাম</label>
      <input class="input" type="text" id="pr-name-bn" name="name_bn" required value="<?= e((string) ($problem['name_bn'] ?? old('name_bn'))) ?>">
    </div>
    <div class="field">
      <label for="pr-name-en">English নাম</label>
      <input class="input" type="text" id="pr-name-en" name="name_en" value="<?= e((string) ($problem['name_en'] ?? '')) ?>">
    </div>
    <div class="field">
      <label for="pr-type">ধরন</label>
      <select class="input" id="pr-type" name="type" required>
        <?php foreach ((array) config('content.problem_type') as $v => $l): ?>
          <option value="<?= e((string) $v) ?>" <?= ($problem['type'] ?? 'pest') === $v ? ' selected' : '' ?>><?= e($l) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="field">
      <label for="pr-sev">ক্ষতির মাত্রা</label>
      <select class="input" id="pr-sev" name="severity" required>
        <?php foreach ((array) config('content.severity') as $v => $l): ?>
          <option value="<?= e((string) $v) ?>" <?= ($problem['severity'] ?? 'medium') === $v ? ' selected' : '' ?>><?= e($l) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
  </div>

  <div class="field">
    <label for="pr-desc">বিবরণ (FREE preview)</label>
    <textarea class="input" id="pr-desc" name="description_bn" required maxlength="2000"><?= e((string) ($problem['description_bn'] ?? old('description_bn'))) ?></textarea>
  </div>

  <div class="field">
    <label for="pr-id">কীভাবে চিনবেন</label>
    <textarea class="input" id="pr-id" name="identification_bn"><?= e((string) ($problem['identification_bn'] ?? '')) ?></textarea>
  </div>

  <div class="grid grid--2">
    <div class="field">
      <label for="pr-organic">জৈব সমাধান (PAID, প্রথমে দেখানো হয়)</label>
      <textarea class="input min-h-sm" id="pr-organic" name="organic_remedy_bn"><?= e((string) ($problem['organic_remedy_bn'] ?? '')) ?></textarea>
    </div>
    <div class="field">
      <label for="pr-chem">রাসায়নিক সমাধান (PAID, মাত্রাসহ)</label>
      <textarea class="input min-h-sm" id="pr-chem" name="chemical_remedy_bn"><?= e((string) ($problem['chemical_remedy_bn'] ?? '')) ?></textarea>
    </div>
  </div>

  <div class="field">
    <label for="pr-prevent">প্রতিরোধ</label>
    <textarea class="input" id="pr-prevent" name="prevention_bn"><?= e((string) ($problem['prevention_bn'] ?? '')) ?></textarea>
  </div>

  <div class="field">
    <label for="pr-safety">সতর্কতা বার্তা</label>
    <input class="input" type="text" id="pr-safety" name="safety_note_bn" maxlength="500"
           value="<?= e((string) ($problem['safety_note_bn'] ?? '')) ?>">
  </div>

  <fieldset>
    <legend>লক্ষণ ও গুরুত্ব (weight ১–১০, ০ = সংযুক্ত নয়)</legend>
    <?php foreach ($byPart as $part => $rows): ?>
      <p class="small muted mb-0"><?= e($partLabels[$part] ?? $part) ?></p>
      <div class="grid grid--3 mb-1">
        <?php foreach ($rows as $symptom): ?>
          <div class="field mb-0">
            <label for="sym-<?= e((string) $symptom['id']) ?>" class="small"><?= e((string) $symptom['name_bn']) ?></label>
            <input class="input" type="number" min="0" max="10" id="sym-<?= e((string) $symptom['id']) ?>"
                   name="symptom_weights[<?= e((string) $symptom['id']) ?>]"
                   value="<?= e((string) ($weights[(int) $symptom['id']] ?? 0)) ?>">
          </div>
        <?php endforeach; ?>
      </div>
    <?php endforeach; ?>
  </fieldset>

  <label class="check mb-1-5">
    <input type="checkbox" name="is_published" value="1" <?= (int) ($problem['is_published'] ?? 0) === 1 ? ' checked' : '' ?>>
    <span>Published</span>
  </label>

  <button class="btn btn--accent" type="submit">সংরক্ষণ করুন</button>
</form>

<?php if (!$isNew): ?>
  <form class="mt-1" method="post" action="/admin/problems/<?= e((string) $problem['id']) ?>/delete"
        data-confirm="মুছে ফেলবেন?">
    <?= csrf_field() ?>
    <button class="btn btn--danger" type="submit">মুছুন</button>
  </form>
<?php endif; ?>
