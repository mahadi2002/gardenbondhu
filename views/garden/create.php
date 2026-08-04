<?php
/**
 * @var array $plants
 * @var array $locations
 */
$this->layout('layouts/app', ['title' => 'গাছ যোগ করুন']);
?>
<div class="page-head"><h1>গাছ যোগ করুন</h1></div>

<form class="card max-w-620" method="post" action="/app/garden" data-guard>
  <?= csrf_field() ?>

  <div class="field">
    <label for="c-plant">কোন গাছ?</label>
    <select class="input" id="c-plant" name="plant_id">
      <option value="">তালিকায় নেই / জানি না</option>
      <?php foreach ($plants as $plant): ?>
        <option value="<?= e((string) $plant['id']) ?>"><?= e((string) $plant['name_bn']) ?></option>
      <?php endforeach; ?>
    </select>
  </div>

  <div class="field">
    <label for="c-nick">ডাকনাম (ঐচ্ছিক)</label>
    <input class="input" type="text" id="c-nick" name="nickname" maxlength="80" placeholder="যেমন: বারান্দার তুলসী">
  </div>

  <div class="field">
    <label for="c-loc">জায়গা</label>
    <select class="input" id="c-loc" name="location" required>
      <?php foreach ($locations as $value => $label): ?>
        <?php if (!in_array($value, ['balcony','rooftop','indoor','yard'], true)) continue; ?>
        <option value="<?= e((string) $value) ?>"><?= e($label) ?></option>
      <?php endforeach; ?>
    </select>
  </div>

  <div class="field">
    <label for="c-planted">রোপণের তারিখ (ঐচ্ছিক)</label>
    <input class="input" type="date" id="c-planted" name="planted_on" max="<?= e(date('Y-m-d')) ?>">
  </div>

  <div class="field">
    <label for="c-pot">টবের ব্যাস (সেমি, ঐচ্ছিক)</label>
    <input class="input" type="number" id="c-pot" name="pot_size_cm" min="5" max="200">
  </div>

  <div class="field">
    <label for="c-notes">নোট (ঐচ্ছিক)</label>
    <textarea class="input" id="c-notes" name="notes" maxlength="2000"></textarea>
  </div>

  <button class="btn btn--accent" type="submit">যোগ করুন</button>
</form>
