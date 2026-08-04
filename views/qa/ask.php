<?php
/**
 * @var array $plants
 * @var int   $askedToday
 */
$this->layout('layouts/app', ['title' => 'প্রশ্ন করুন']);
?>
<div class="page-head"><h1>প্রশ্ন করুন</h1></div>

<?php if ($askedToday >= 5): ?>
  <div class="notice notice--warn">
    <span class="notice__icon" aria-hidden="true">⚠</span>
    <span>আজকের জন্য প্রশ্নের সীমা শেষ (৫টি)। আগামীকাল আবার করতে পারবেন।</span>
  </div>
<?php else: ?>
  <form class="card" method="post" action="/app/qa" enctype="multipart/form-data" data-guard style="max-width:680px">
    <?= csrf_field() ?>

    <div class="field<?= error_for('title') ? ' field--error' : '' ?>">
      <label for="q-title">শিরোনাম</label>
      <input class="input" type="text" id="q-title" name="title" maxlength="200" required
             value="<?= e(old('title')) ?>" placeholder="সংক্ষেপে সমস্যাটা লিখুন">
      <?php if ($err = error_for('title')): ?><p class="error-text"><?= e($err) ?></p><?php endif; ?>
    </div>

    <div class="field">
      <label for="q-plant">গাছ (ঐচ্ছিক)</label>
      <select class="input" id="q-plant" name="plant_id">
        <option value="">নির্দিষ্ট নয়</option>
        <?php foreach ($plants as $plant): ?>
          <option value="<?= e((string) $plant['id']) ?>"><?= e((string) $plant['name_bn']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="field<?= error_for('body') ? ' field--error' : '' ?>">
      <label for="q-body">বিস্তারিত</label>
      <textarea class="input" id="q-body" name="body" maxlength="4000" required data-counter="q-body-count"
                placeholder="কবে থেকে শুরু হয়েছে, গাছের কোন অংশে সমস্যা, আপনি কী করেছেন — যত বিস্তারিত লিখবেন, উত্তর তত ভালো পাবেন।"><?= e(old('body')) ?></textarea>
      <p class="help" id="q-body-count"></p>
      <?php if ($err = error_for('body')): ?><p class="error-text"><?= e($err) ?></p><?php endif; ?>
    </div>

    <div class="field<?= error_for('image') ? ' field--error' : '' ?>">
      <label for="q-image">ছবি (ঐচ্ছিক, সর্বোচ্চ ৪ MB)</label>
      <input class="input" type="file" id="q-image" name="image" accept="image/jpeg,image/png,image/webp">
      <?php if ($err = error_for('image')): ?><p class="error-text"><?= e($err) ?></p><?php endif; ?>
    </div>

    <button class="btn btn--accent" type="submit">প্রশ্ন জমা দিন</button>
  </form>
<?php endif; ?>
