<?php
/**
 * @var array  $guides
 * @var string $query
 */
$this->layout('layouts/admin', ['title' => 'গাইড']);
?>
<div class="page-head">
  <div class="between">
    <h1>গাইড (<?= e(bn_num(count($guides))) ?>)</h1>
    <a class="btn btn--accent" href="/admin/guides/new">+ নতুন গাইড</a>
  </div>
</div>

<form method="get" action="/admin/guides" class="cluster" style="margin-bottom:1.5rem">
  <input class="input" type="search" name="q" value="<?= e($query) ?>" placeholder="শিরোনাম বা slug খুঁজুন" style="max-width:300px">
  <button class="btn btn--sm" type="submit">খুঁজুন</button>
</form>

<div class="table-wrap">
  <table>
    <thead><tr><th>শিরোনাম</th><th>বিষয়</th><th>Premium</th><th>Status</th><th>Published</th></tr></thead>
    <tbody>
    <?php foreach ($guides as $guide): ?>
      <tr>
        <td><a href="/admin/guides/<?= e((string) $guide['id']) ?>"><?= e((string) $guide['title_bn']) ?></a></td>
        <td><?= e((string) config('content.guide_category.' . $guide['category'])) ?></td>
        <td><?= (int) $guide['is_premium'] === 1 ? 'হ্যাঁ' : 'না' ?></td>
        <td><span class="chip <?= (int) $guide['is_published'] === 1 ? 'chip--low' : 'chip--muted' ?>">
          <?= (int) $guide['is_published'] === 1 ? 'Published' : 'Draft' ?>
        </span></td>
        <td class="small mono"><?= e((string) ($guide['published_at'] ?? '—')) ?></td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>
