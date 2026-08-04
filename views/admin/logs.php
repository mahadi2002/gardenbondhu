<?php
/**
 * @var array  $rows, $actions
 * @var string $action, $actor
 */
$this->layout('layouts/admin', ['title' => 'Audit log']);
?>
<div class="page-head"><h1>Audit log</h1></div>

<form class="filters" method="get" action="/admin/logs" data-auto-submit>
  <div class="filters__row">
    <div class="field">
      <label for="l-action">Action</label>
      <select class="input" id="l-action" name="action">
        <option value="">সব</option>
        <?php foreach ($actions as $a): ?>
          <option value="<?= e((string) $a['action']) ?>" <?= $action === $a['action'] ? ' selected' : '' ?>><?= e((string) $a['action']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="field">
      <label for="l-actor">Actor</label>
      <select class="input" id="l-actor" name="actor">
        <option value="">সব</option>
        <?php foreach (['user','admin','system','gateway'] as $a): ?>
          <option value="<?= e($a) ?>" <?= $actor === $a ? ' selected' : '' ?>><?= e($a) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
  </div>
</form>

<div class="table-wrap">
  <table>
    <thead><tr><th>Time</th><th>Actor</th><th>Action</th><th>Entity</th><th>Meta</th></tr></thead>
    <tbody>
    <?php foreach ($rows as $row): ?>
      <tr>
        <td class="mono small"><?= e((string) $row['created_at']) ?></td>
        <td><?= e((string) $row['actor_type']) ?><?= $row['actor_id'] ? '#' . e((string) $row['actor_id']) : '' ?></td>
        <td><?= e((string) $row['action']) ?></td>
        <td><?= e((string) ($row['entity'] ?? '—')) ?><?= $row['entity_id'] ? '#' . e((string) $row['entity_id']) : '' ?></td>
        <td class="small mono"><?= e(str_excerpt((string) ($row['meta'] ?? ''), 80)) ?></td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>
