<?php
/**
 * @var array  $users
 * @var string $q
 */
$this->layout('layouts/admin', ['title' => 'ব্যবহারকারী']);
?>
<div class="page-head"><h1>ব্যবহারকারী</h1></div>

<form method="get" action="/admin/users" class="cluster mb-1-5">
  <input class="input max-w-3xs" type="text" name="q" value="<?= e($q) ?>" placeholder="ইমেইল দিয়ে খুঁজুন">
  <button class="btn btn--sm" type="submit">খুঁজুন</button>
</form>

<div class="table-wrap reveal">
  <table>
    <thead><tr><th>ইমেইল</th><th>Role</th><th>Status</th><th>যোগদান</th></tr></thead>
    <tbody>
    <?php foreach ($users as $u): ?>
      <tr>
        <td><a href="/admin/users/<?= e((string) $u['id']) ?>"><?= e((string) $u['email']) ?></a></td>
        <td><?= e((string) $u['role']) ?></td>
        <td><span class="chip <?= $u['status'] === 'blocked' ? 'chip--high' : 'chip--muted' ?>"><?= e((string) $u['status']) ?></span></td>
        <td class="small mono"><?= e((string) $u['created_at']) ?></td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>
