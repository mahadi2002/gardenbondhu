<?php
/**
 * @var array      $user
 * @var array|null $sub
 * @var array      $charges
 * @var bool       $hasAccess
 */
$this->layout('layouts/public', ['title' => 'Account']);

$statusLabels = [
    'pending'      => 'প্রথম Charge-এর অপেক্ষায়',
    'active'       => 'চালু আছে',
    'grace'        => 'Grace period — ব্যালেন্স কম',
    'expired'      => 'বন্ধ হয়ে গেছে',
    'unsubscribed' => 'Unsubscribe করা হয়েছে',
];
$chargeLabels = ['success' => 'সফল', 'failed' => 'ব্যর্থ', 'pending' => 'অপেক্ষমাণ', 'reversed' => 'ফেরত'];
?>
<section class="section">
  <div class="wrap">
    <div class="page-head">
      <h1>আপনার Account</h1>
      <p class="muted">নম্বর: <span class="mono">01••••<?= e((string) $user['msisdn_last4']) ?></span>
        · <?= e(ucfirst((string) $user['operator'])) ?></p>
    </div>

    <div class="grid grid--2">
      <div class="card">
        <h2 class="card__title">Subscription</h2>

        <?php if ($sub === null): ?>
          <p>কোনো Subscription নেই।</p>
          <a class="btn btn--accent" href="/subscribe">Subscribe Now</a>
        <?php else: ?>
          <p class="cluster">
            <span class="chip <?= $hasAccess ? 'chip--low' : 'chip--high' ?>">
              <?= e($statusLabels[(string) $sub['status']] ?? (string) $sub['status']) ?>
            </span>
          </p>

          <dl class="quick-facts">
            <div class="quick-fact">
              <dt>দৈনিক Charge</dt>
              <dd class="mono">৳<?= e(number_format((float) $sub['daily_amount'], 2)) ?></dd>
            </div>
            <div class="quick-fact">
              <dt>শুরু হয়েছে</dt>
              <dd><?= e(bn_date($sub['started_at'])) ?></dd>
            </div>
            <div class="quick-fact">
              <dt>বর্তমান Access</dt>
              <dd><?= e(bn_date($sub['current_period_end'], true)) ?></dd>
            </div>
          </dl>

          <p class="cluster">
            <?php if ($hasAccess): ?>
              <a class="btn" href="/app">অ্যাপে যান</a>
            <?php elseif ((string) $sub['status'] !== 'unsubscribed'): ?>
              <a class="btn btn--accent" href="/subscribe">আবার শুরু করুন</a>
            <?php endif; ?>

            <?php if ((string) $sub['status'] !== 'unsubscribed'): ?>
              <a class="btn btn--danger btn--sm" href="/account/unsubscribe">Unsubscribe</a>
            <?php else: ?>
              <a class="btn btn--accent" href="/subscribe">আবার শুরু করুন</a>
            <?php endif; ?>
          </p>
        <?php endif; ?>
      </div>

      <div class="card">
        <h2 class="card__title">সাম্প্রতিক Charge</h2>

        <?php if ($charges === []): ?>
          <p class="muted mb-0">এখনো কোনো Charge হয়নি।</p>
        <?php else: ?>
          <div class="table-wrap">
            <table>
              <thead><tr><th>তারিখ</th><th>পরিমাণ</th><th>অবস্থা</th></tr></thead>
              <tbody>
              <?php foreach ($charges as $charge): ?>
                <tr>
                  <td><?= e(bn_date((string) $charge['attempted_at'])) ?></td>
                  <td class="mono">৳<?= e(number_format((float) $charge['amount'], 2)) ?></td>
                  <td>
                    <span class="chip <?= $charge['status'] === 'success' ? 'chip--low' : ($charge['status'] === 'failed' ? 'chip--high' : 'chip--muted') ?>">
                      <?= e($chargeLabels[(string) $charge['status']] ?? (string) $charge['status']) ?>
                    </span>
                  </td>
                </tr>
              <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php endif; ?>
      </div>
    </div>

    <div class="card">
      <h2 class="card__title">অ্যাকাউন্ট মুছে ফেলুন</h2>
      <p class="small">
        মুছে ফেললে আপনার নম্বর আর বাগানের রেকর্ড স্থায়ীভাবে চলে যাবে। Subscription থাকলে
        সেটিও বন্ধ হয়ে যাবে। এই কাজটি ফেরানো যায় না।
      </p>

      <form method="post" action="/account/delete" data-confirm="অ্যাকাউন্ট স্থায়ীভাবে মুছে ফেলবেন?">
        <?= csrf_field() ?>
        <div class="field">
          <label for="confirm-delete">নিশ্চিত করতে <span class="mono">DELETE</span> লিখুন</label>
          <input class="input" type="text" id="confirm-delete" name="confirm" autocomplete="off" required>
        </div>
        <button class="btn btn--danger" type="submit">অ্যাকাউন্ট মুছুন</button>
      </form>
    </div>
  </div>
</section>
