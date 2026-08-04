<?php
/**
 * @var int   $activeSubs, $newUsers7d, $pendingQuestions
 * @var array $chargesToday, $statusBreakdown, $failures, $byOperator, $trend, $content
 */
$this->layout('layouts/admin', ['title' => 'Dashboard']);

$maxTrend = max(1, ...array_map(static fn($r) => max((int) $r['success'], (int) $r['failed']), $trend ?: [['success' => 0, 'failed' => 0]]));
?>
<div class="page-head"><h1>Dashboard</h1></div>

<div class="kpi-grid">
  <div class="kpi"><p class="kpi__n mb-0"><?= e(bn_num($activeSubs)) ?></p><p class="kpi__l mb-0">Active subscribers</p></div>
  <div class="kpi"><p class="kpi__n mb-0"><?= e(bn_num($newUsers7d)) ?></p><p class="kpi__l mb-0">New users (7d)</p></div>
  <div class="kpi"><p class="kpi__n mb-0"><?= e(bn_num($chargesToday['success'] ?? 0)) ?></p><p class="kpi__l mb-0">Charges succeeded today</p></div>
  <div class="kpi"><p class="kpi__n mb-0"><?= e(bn_num($chargesToday['failed'] ?? 0)) ?></p><p class="kpi__l mb-0">Charges failed today</p></div>
  <div class="kpi"><p class="kpi__n mb-0"><?= e(bn_num($pendingQuestions)) ?></p><p class="kpi__l mb-0">Questions pending review</p></div>
</div>

<div class="grid grid--2">
  <div class="card">
    <h2 class="card__title">Charge trend (14 days)</h2>
    <div class="bar-chart">
      <?php foreach ($trend as $day): ?>
        <div style="height:<?= e((string) max(2, round(((int) $day['success']) / $maxTrend * 90))) ?>px" title="<?= e((string) $day['day']) ?> success"></div>
      <?php endforeach; ?>
    </div>
    <div class="bar-chart">
      <?php foreach ($trend as $day): ?>
        <div class="fail" style="height:<?= e((string) max(2, round(((int) $day['failed']) / $maxTrend * 90))) ?>px" title="<?= e((string) $day['day']) ?> failed"></div>
      <?php endforeach; ?>
    </div>
    <p class="small muted mb-0">সবুজ = সফল, লাল = ব্যর্থ</p>
  </div>

  <div class="card">
    <h2 class="card__title">Subscription status</h2>
    <div class="table-wrap">
      <table>
        <?php foreach ($statusBreakdown as $status => $count): ?>
          <tr><td><?= e($status) ?></td><td class="mono"><?= e(bn_num($count)) ?></td></tr>
        <?php endforeach; ?>
      </table>
    </div>

    <h3 style="margin-top:1.5rem">Operator breakdown</h3>
    <div class="table-wrap">
      <table>
        <?php foreach ($byOperator as $operator => $count): ?>
          <tr><td><?= e(ucfirst($operator)) ?></td><td class="mono"><?= e(bn_num($count)) ?></td></tr>
        <?php endforeach; ?>
      </table>
    </div>
  </div>
</div>

<div class="card">
  <h2 class="card__title">Recent failed charges</h2>
  <?php if ($failures === []): ?>
    <p class="muted mb-0">কোনো ব্যর্থ Charge নেই।</p>
  <?php else: ?>
    <div class="table-wrap">
      <table>
        <thead><tr><th>User</th><th>Amount</th><th>Reason</th><th>At</th></tr></thead>
        <tbody>
        <?php foreach ($failures as $f): ?>
          <tr>
            <td><a href="/admin/users/<?= e((string) $f['user_id']) ?>">01••••<?= e((string) $f['msisdn_last4']) ?></a></td>
            <td class="mono">৳<?= e(number_format((float) $f['amount'], 2)) ?></td>
            <td><?= e((string) ($f['failure_reason'] ?: $f['status_code'] ?: '—')) ?></td>
            <td><?= e(bn_date((string) $f['attempted_at'], true)) ?></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</div>

<div class="kpi-grid" style="margin-top:1.5rem">
  <div class="kpi"><p class="kpi__n mb-0"><?= e(bn_num($content['plants'])) ?></p><p class="kpi__l mb-0">Published plants</p></div>
  <div class="kpi"><p class="kpi__n mb-0"><?= e(bn_num($content['problems'])) ?></p><p class="kpi__l mb-0">Published problems</p></div>
  <div class="kpi"><p class="kpi__n mb-0"><?= e(bn_num($content['guides'])) ?></p><p class="kpi__l mb-0">Published guides</p></div>
</div>
