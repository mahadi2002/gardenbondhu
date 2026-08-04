<?php
/**
 * @var array  $problems
 * @var string $query
 */
$this->layout('layouts/admin', ['title' => 'রোগ ও পোকা']);
?>
<div class="page-head">
  <div class="between">
    <h1>রোগ ও পোকা (<?= e(bn_num(count($problems))) ?>)</h1>
    <a class="btn btn--accent" href="/admin/problems/new">+ নতুন সমস্যা</a>
  </div>
</div>

<form method="get" action="/admin/problems" class="cluster" style="margin-bottom:1.5rem">
  <input class="input" type="search" name="q" value="<?= e($query) ?>" placeholder="নাম বা slug খুঁজুন" style="max-width:300px">
  <button class="btn btn--sm" type="submit">খুঁজুন</button>
</form>

<div class="table-wrap">
  <table>
    <thead><tr><th>নাম</th><th>ধরন</th><th>ক্ষতি</th><th>Status</th><th>Updated</th></tr></thead>
    <tbody>
    <?php foreach ($problems as $problem): ?>
      <tr>
        <td><a href="/admin/problems/<?= e((string) $problem['id']) ?>"><?= e((string) $problem['name_bn']) ?></a></td>
        <td><?= e((string) config('content.problem_type.' . $problem['type'])) ?></td>
        <td><span class="chip chip--<?= e((string) $problem['severity']) ?>"><?= e((string) config('content.severity.' . $problem['severity'])) ?></span></td>
        <td><span class="chip <?= (int) $problem['is_published'] === 1 ? 'chip--low' : 'chip--muted' ?>">
          <?= (int) $problem['is_published'] === 1 ? 'Published' : 'Draft' ?>
        </span></td>
        <td class="small mono"><?= e((string) $problem['updated_at']) ?></td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>
