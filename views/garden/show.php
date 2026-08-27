<?php
/**
 * @var array $plant, $tasks, $plants, $locations
 */
use App\Services\ImageService;

$this->layout('layouts/app', ['title' => (string) ($plant['nickname'] ?: $plant['plant_name_bn'] ?: 'আমার গাছ')]);

$taskLabels = (array) config('content.care_task');
?>
<div class="page-head">
  <h1><?= e((string) ($plant['nickname'] ?: $plant['plant_name_bn'] ?: 'আমার গাছ')) ?></h1>
  <?php if (!empty($plant['plant_name_bn'])): ?>
    <p class="muted"><a href="/app/plants/<?= e((string) $plant['plant_slug']) ?>"><?= e((string) $plant['plant_name_bn']) ?> দেখুন →</a></p>
  <?php endif; ?>
</div>

<?php if (!empty($plant['photo'])): ?>
  <div class="plant-photo max-w-sm mb-1-5">
    <img src="/media/<?= e(ImageService::toToken((string) $plant['photo'])) ?>" alt="আমার <?= e((string) ($plant['nickname'] ?: $plant['plant_name_bn'] ?: 'গাছ')) ?>" loading="lazy">
  </div>
<?php elseif (!empty($plant['hero_image'])): ?>
  <div class="plant-photo plant-photo--stock max-w-sm mb-1-5">
    <img src="<?= e((string) $plant['hero_image']) ?>" alt="" loading="lazy">
    <span class="plant-photo__tag small">ক্যাটালগের ছবি</span>
  </div>
<?php endif; ?>

<div class="grid grid--2">
  <form class="card" method="post" action="/app/garden/<?= e((string) $plant['id']) ?>" enctype="multipart/form-data" data-guard>
    <?= csrf_field() ?>
    <h2 class="card__title">তথ্য সম্পাদনা</h2>

    <div class="field">
      <label for="e-photo">নিজের গাছের ছবি বদলান (ঐচ্ছিক)</label>
      <input class="input" type="file" id="e-photo" name="photo" accept="image/jpeg,image/png,image/webp">
    </div>

    <div class="field">
      <label for="e-nick">ডাকনাম</label>
      <input class="input" type="text" id="e-nick" name="nickname" maxlength="80" value="<?= e((string) ($plant['nickname'] ?? '')) ?>">
    </div>

    <div class="field">
      <label for="e-loc">জায়গা</label>
      <select class="input" id="e-loc" name="location" required>
        <?php foreach (['balcony'=>'বারান্দা','rooftop'=>'ছাদ','indoor'=>'ঘরের ভেতর','yard'=>'উঠান'] as $value => $label): ?>
          <option value="<?= e($value) ?>" <?= $plant['location'] === $value ? ' selected' : '' ?>><?= e($label) ?></option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="field">
      <label for="e-planted">রোপণের তারিখ</label>
      <input class="input" type="date" id="e-planted" name="planted_on" max="<?= e(date('Y-m-d')) ?>"
             value="<?= e((string) ($plant['planted_on'] ?? '')) ?>">
    </div>

    <div class="field">
      <label for="e-pot">টবের ব্যাস (সেমি)</label>
      <input class="input" type="number" id="e-pot" name="pot_size_cm" min="5" max="200"
             value="<?= e((string) ($plant['pot_size_cm'] ?? '')) ?>">
    </div>

    <div class="field">
      <label for="e-notes">নোট</label>
      <textarea class="input" id="e-notes" name="notes" maxlength="2000"><?= e((string) ($plant['notes'] ?? '')) ?></textarea>
    </div>

    <label class="check mb-1">
      <input type="checkbox" name="is_archived" value="1" <?= (int) $plant['is_archived'] === 1 ? ' checked' : '' ?>>
      <span>আর্কাইভ করুন (তালিকা থেকে লুকান)</span>
    </label>

    <button class="btn" type="submit">সংরক্ষণ করুন</button>
  </form>

  <div class="card">
    <h2 class="card__title">সাম্প্রতিক কাজ</h2>
    <?php if ($tasks === []): ?>
      <p class="muted mb-0">এখনো কোনো কাজ তৈরি হয়নি।</p>
    <?php else: ?>
      <?php foreach ($tasks as $task): ?>
        <div class="task-row <?= $task['done_at'] !== null ? 'task-row--done' : '' ?>">
          <div class="task-row__label">
            <strong><?= e($taskLabels[(string) $task['task']] ?? (string) $task['task']) ?></strong>
            <span class="small muted"><?= e(bn_date((string) $task['due_on'])) ?></span>
          </div>
          <?php if ($task['done_at'] === null): ?>
            <form method="post" action="/app/garden/task/<?= e((string) $task['id']) ?>/done" data-guard>
              <?= csrf_field() ?>
              <button class="btn btn--sm" type="submit">সম্পন্ন</button>
            </form>
          <?php else: ?>
            <span class="chip chip--low">সম্পন্ন</span>
          <?php endif; ?>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>
</div>

<form class="mt-1-5" method="post" action="/app/garden/<?= e((string) $plant['id']) ?>/delete"
      data-confirm="গাছটি স্থায়ীভাবে মুছে ফেলবেন?">
  <?= csrf_field() ?>
  <button class="btn btn--danger" type="submit">গাছটি মুছে ফেলুন</button>
</form>
