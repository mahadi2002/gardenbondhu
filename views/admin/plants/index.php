<?php
/**
 * @var array  $plants
 * @var string $query
 */
$this->layout('layouts/admin', ['title' => 'গাছ']);
?>
<div class="page-head">
  <div class="between">
    <h1>গাছ (<?= e(bn_num(count($plants))) ?>)</h1>
    <a class="btn btn--accent" href="/admin/plants/new">+ নতুন গাছ</a>
  </div>
</div>

<form method="get" action="/admin/plants" class="cluster mb-1-5">
  <input class="input max-w-2xs" type="search" name="q" value="<?= e($query) ?>" placeholder="নাম বা slug খুঁজুন">
  <button class="btn btn--sm" type="submit">খুঁজুন</button>
</form>

<div class="table-wrap reveal">
  <table>
    <thead><tr><th>নাম</th><th>কষ্টসাধ্যতা</th><th>Views</th><th>Status</th><th>Updated</th></tr></thead>
    <tbody>
    <?php foreach ($plants as $plant): ?>
      <tr>
        <td><a href="/admin/plants/<?= e((string) $plant['id']) ?>"><?= e((string) $plant['name_bn']) ?></a></td>
        <td><?= e((string) config('content.difficulty.' . $plant['difficulty'])) ?></td>
        <td class="mono"><?= e(bn_num((int) $plant['view_count'])) ?></td>
        <td><span class="chip <?= (int) $plant['is_published'] === 1 ? 'chip--low' : 'chip--muted' ?>">
          <?= (int) $plant['is_published'] === 1 ? 'Published' : 'Draft' ?>
        </span></td>
        <td class="small mono"><?= e((string) $plant['updated_at']) ?></td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>
