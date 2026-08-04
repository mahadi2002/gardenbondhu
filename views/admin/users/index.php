<?php
/**
 * @var array  $users
 * @var string $last4
 */
$this->layout('layouts/admin', ['title' => 'ব্যবহারকারী']);
?>
<div class="page-head"><h1>ব্যবহারকারী</h1></div>

<form method="get" action="/admin/users" class="cluster mb-1-5">
  <input class="input max-w-3xs" type="text" name="last4" value="<?= e($last4) ?>" placeholder="শেষ ৪ ডিজিট" maxlength="4" pattern="[0-9]{4}">
  <button class="btn btn--sm" type="submit">খুঁজুন</button>
  <span class="small muted">নম্বর শুধু শেষ ৪ ডিজিট দিয়ে খোঁজা যায় — সম্পূর্ণ নম্বর কোথাও দেখানো হয় না।</span>
</form>

<div class="table-wrap">
  <table>
    <thead><tr><th>নম্বর</th><th>Operator</th><th>Role</th><th>Status</th><th>Subscription</th><th>যোগদান</th></tr></thead>
    <tbody>
    <?php foreach ($users as $u): ?>
      <tr>
        <td><a href="/admin/users/<?= e((string) $u['id']) ?>">01••••<?= e((string) $u['msisdn_last4']) ?></a></td>
        <td><?= e(ucfirst((string) $u['operator'])) ?></td>
        <td><?= e((string) $u['role']) ?></td>
        <td><span class="chip <?= $u['status'] === 'blocked' ? 'chip--high' : 'chip--muted' ?>"><?= e((string) $u['status']) ?></span></td>
        <td><?= e((string) ($u['sub_status'] ?? '—')) ?></td>
        <td class="small mono"><?= e((string) $u['created_at']) ?></td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>
