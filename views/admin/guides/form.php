<?php
/** @var array|null $guide */
$isNew = $guide === null;
$this->layout('layouts/admin', ['title' => $isNew ? 'নতুন গাইড' : (string) $guide['title_bn']]);

$action = $isNew ? '/admin/guides' : '/admin/guides/' . $guide['id'];
?>
<div class="page-head"><h1><?= $isNew ? 'নতুন গাইড' : e((string) $guide['title_bn']) ?></h1></div>

<form method="post" action="<?= e($action) ?>" data-guard>
  <?= csrf_field() ?>

  <div class="grid grid--2">
    <div class="field">
      <label for="g-slug">Slug</label>
      <input class="input" type="text" id="g-slug" name="slug" required pattern="[a-z0-9\-]+"
             value="<?= e((string) ($guide['slug'] ?? old('slug'))) ?>">
    </div>
    <div class="field">
      <label for="g-title">শিরোনাম</label>
      <input class="input" type="text" id="g-title" name="title_bn" required value="<?= e((string) ($guide['title_bn'] ?? old('title_bn'))) ?>">
    </div>
    <div class="field">
      <label for="g-cat">বিষয়</label>
      <select class="input" id="g-cat" name="category" required>
        <?php foreach ((array) config('content.guide_category') as $v => $l): ?>
          <option value="<?= e((string) $v) ?>" <?= ($guide['category'] ?? 'basic') === $v ? ' selected' : '' ?>><?= e($l) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="field">
      <label for="g-read">পড়ার সময় (মিনিট)</label>
      <input class="input" type="number" id="g-read" name="read_minutes" min="1" max="120" value="<?= e((string) ($guide['read_minutes'] ?? '')) ?>">
    </div>
  </div>

  <div class="field">
    <label for="g-excerpt">সংক্ষিপ্ত বিবরণ (FREE)</label>
    <textarea class="input" id="g-excerpt" name="excerpt_bn" required maxlength="2000"><?= e((string) ($guide['excerpt_bn'] ?? old('excerpt_bn'))) ?></textarea>
  </div>

  <div class="field">
    <label for="g-body">পূর্ণ লেখা — Markdown</label>
    <textarea class="input min-h-lg" id="g-body" name="body_bn"><?= e((string) ($guide['body_bn'] ?? '')) ?></textarea>
  </div>

  <div class="grid grid--2">
    <label class="check">
      <input type="checkbox" name="is_premium" value="1" <?= (int) ($guide['is_premium'] ?? 1) === 1 ? ' checked' : '' ?>>
      <span>Premium (Subscriber-দের জন্য)</span>
    </label>
    <label class="check">
      <input type="checkbox" name="is_published" value="1" <?= (int) ($guide['is_published'] ?? 0) === 1 ? ' checked' : '' ?>>
      <span>Published</span>
    </label>
  </div>

  <button class="btn btn--accent mt-1" type="submit">সংরক্ষণ করুন</button>
</form>

<?php if (!$isNew): ?>
  <form class="mt-1" method="post" action="/admin/guides/<?= e((string) $guide['id']) ?>/delete"
        data-confirm="মুছে ফেলবেন?">
    <?= csrf_field() ?>
    <button class="btn btn--danger" type="submit">মুছুন</button>
  </form>
<?php endif; ?>
