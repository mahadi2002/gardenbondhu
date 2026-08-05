<?php
/**
 * @var array  $messages
 * @var string $status
 * @var int    $newCount
 */
$this->layout('layouts/admin', ['title' => 'Contact Inbox']);

$tabs = ['new' => 'নতুন', 'read' => 'দেখা হয়েছে', 'resolved' => 'সমাধান হয়েছে', 'all' => 'সব'];
?>
<div class="page-head">
  <h1>Contact Inbox<?= $newCount > 0 ? ' <span class="chip chip--high">' . e(bn_num($newCount)) . ' নতুন</span>' : '' ?></h1>
</div>

<nav class="cluster mb-1-5">
  <?php foreach ($tabs as $value => $label): ?>
    <a class="btn btn--sm <?= $status === $value ? '' : 'btn--ghost' ?>" href="/admin/contact?status=<?= e($value) ?>"><?= e($label) ?></a>
  <?php endforeach; ?>
</nav>

<?php if ($messages === []): ?>
  <p class="muted">এই তালিকায় কিছু নেই।</p>
<?php else: ?>
  <div class="table-wrap">
    <table>
      <thead><tr><th>নাম</th><th>যোগাযোগ</th><th>বার্তা</th><th>অবস্থা</th><th>তারিখ</th></tr></thead>
      <tbody>
      <?php foreach ($messages as $m): ?>
        <tr>
          <td><a href="/admin/contact/<?= e((string) $m['id']) ?>"><?= e((string) $m['name']) ?></a></td>
          <td class="mono small"><?= e((string) $m['contact']) ?></td>
          <td><?= e(str_excerpt((string) $m['message'], 60)) ?></td>
          <td>
            <span class="chip <?= $m['status'] === 'new' ? 'chip--high' : ($m['status'] === 'resolved' ? 'chip--low' : 'chip--muted') ?>">
              <?= e(['new' => 'নতুন', 'read' => 'দেখা হয়েছে', 'resolved' => 'সমাধান হয়েছে'][$m['status']] ?? (string) $m['status']) ?>
            </span>
          </td>
          <td class="small mono"><?= e((string) $m['created_at']) ?></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
<?php endif; ?>
