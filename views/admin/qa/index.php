<?php
/**
 * @var array  $questions
 * @var string $status
 */
$this->layout('layouts/admin', ['title' => 'প্রশ্ন-উত্তর মডারেশন']);

$tabs = ['pending' => 'যাচাইয়ের অপেক্ষায়', 'approved' => 'অনুমোদিত', 'answered' => 'উত্তর দেওয়া', 'rejected' => 'বাতিল'];
?>
<div class="page-head"><h1>প্রশ্ন-উত্তর মডারেশন</h1></div>

<nav class="cluster mb-1-5">
  <?php foreach ($tabs as $value => $label): ?>
    <a class="btn btn--sm <?= $status === $value ? '' : 'btn--ghost' ?>" href="/admin/qa?status=<?= e($value) ?>"><?= e($label) ?></a>
  <?php endforeach; ?>
</nav>

<?php if ($questions === []): ?>
  <p class="muted">এই তালিকায় কিছু নেই।</p>
<?php else: ?>
  <div class="table-wrap">
    <table>
      <thead><tr><th>শিরোনাম</th><th>User</th><th>উত্তর</th><th>তারিখ</th></tr></thead>
      <tbody>
      <?php foreach ($questions as $q): ?>
        <tr>
          <td><a href="/admin/qa/<?= e((string) $q['id']) ?>"><?= e((string) $q['title']) ?></a></td>
          <td>01••••<?= e((string) $q['msisdn_last4']) ?></td>
          <td class="mono"><?= e(bn_num((int) $q['answer_count'])) ?></td>
          <td class="small mono"><?= e((string) $q['created_at']) ?></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
<?php endif; ?>
