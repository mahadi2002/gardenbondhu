<?php
/**
 * @var array $symptoms   body_part => rows
 * @var array $bodyParts
 * @var array $plants
 * @var array $selected
 * @var int|null $plantId
 */
use App\Core\View;

$this->layout('layouts/app', ['title' => 'রোগ নির্ণয়', 'scripts' => ['diagnose.js']]);
?>
<div class="page-head">
  <h1>পাতা দেখে রোগ নির্ণয়</h1>
  <p class="muted">গাছের যে অংশে সমস্যা, সেখানে চাপ দিন। যত বেশি লক্ষণ বাছাই করবেন, ফলাফল তত সঠিক হবে।</p>
</div>

<form class="diagnoser" id="diagnose-form" method="post" action="/app/diagnose">
  <?= csrf_field() ?>

  <?= View::partial('partials/leaf-picker', ['bodyParts' => $bodyParts]) ?>

  <div>
    <div class="field">
      <label for="plant-select">কোন গাছ? (ঐচ্ছিক, ফলাফল আরও নির্দিষ্ট হবে)</label>
      <select class="input" id="plant-select" name="plant_id">
        <option value="">জানি না / সব গাছ</option>
        <?php foreach ($plants as $plant): ?>
          <option value="<?= e((string) $plant['id']) ?>" <?= (string) $plantId === (string) $plant['id'] ? ' selected' : '' ?>>
            <?= e((string) $plant['name_bn']) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="part-tabs">
      <?php foreach ($bodyParts as $part => $label): ?>
        <?php if (!empty($symptoms[$part])): ?>
          <button class="part-tab" type="button" data-part="<?= e($part) ?>" aria-pressed="false"><?= e($label) ?></button>
        <?php endif; ?>
      <?php endforeach; ?>
    </div>

    <?php foreach ($bodyParts as $part => $label): ?>
      <?php if (empty($symptoms[$part])) { continue; } ?>
      <div class="symptom-group" data-part-group="<?= e($part) ?>">
        <h3><?= e($label) ?></h3>
        <div class="symptom-grid">
          <?php foreach ($symptoms[$part] as $symptom): ?>
            <label class="check">
              <input type="checkbox" name="symptoms[]" value="<?= e((string) $symptom['id']) ?>"
                     data-part="<?= e($part) ?>"
                     <?= in_array((string) $symptom['id'], array_map('strval', $selected), true) ? ' checked' : '' ?>>
              <span><?= e((string) $symptom['name_bn']) ?></span>
            </label>
          <?php endforeach; ?>
        </div>
      </div>
    <?php endforeach; ?>

    <p class="small muted" id="symptom-count" role="status">কোনো লক্ষণ বাছাই করা হয়নি</p>

    <button class="btn btn--accent btn--lg" type="submit">ফলাফল দেখুন</button>
  </div>
</form>
