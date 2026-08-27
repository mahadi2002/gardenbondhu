<?php
/**
 * @var array $plants
 * @var int   $dueToday
 */
use App\Services\ImageService;

$this->layout('layouts/app', ['title' => 'আমার বাগান']);
?>
<div class="page-head">
  <div class="between">
    <h1>আমার বাগান</h1>
    <a class="btn btn--accent" href="/app/garden/add">+ গাছ যোগ করুন</a>
  </div>
  <?php if ($dueToday > 0): ?>
    <p class="muted">আজ <?= e(bn_num($dueToday)) ?>টি কাজ বাকি — <a href="/app">ড্যাশবোর্ডে দেখুন</a></p>
  <?php endif; ?>
</div>

<?php if ($plants === []): ?>
  <div class="empty-state">
    <h3>এখনো কোনো গাছ যোগ করেননি</h3>
    <p class="muted">প্রথম গাছটা যোগ করুন। পানি-সার দেওয়ার রিমাইন্ডার এমনিতেই তৈরি হয়ে যাবে।</p>
    <a class="btn btn--accent" href="/app/garden/add">গাছ যোগ করুন</a>
  </div>
<?php else: ?>
  <div class="grid grid--3">
    <?php foreach ($plants as $plant): ?>
      <a class="card card--link" href="/app/garden/<?= e((string) $plant['id']) ?>">
        <div class="card__media">
          <?php if (!empty($plant['photo'])): ?>
            <img src="/media/<?= e(ImageService::toToken((string) $plant['photo'])) ?>" alt="" loading="lazy" width="400" height="300">
          <?php elseif (!empty($plant['hero_image'])): ?>
            <img src="<?= e((string) $plant['hero_image']) ?>" alt="" loading="lazy" width="400" height="300">
          <?php else: ?>
            <svg viewBox="0 0 64 64" aria-hidden="true" fill="#fff">
              <path d="M32 58V28c0-13 9-22 26-23 0 15-9 23-26 23Z" opacity=".85"/>
              <path d="M32 44C18 44 8 36 7 22c14 0 25 8 25 22Z" opacity=".55"/>
            </svg>
          <?php endif; ?>
        </div>
        <h3 class="card__title"><?= e((string) ($plant['nickname'] ?: $plant['plant_name_bn'] ?: 'আমার গাছ')) ?></h3>
        <?php if (!empty($plant['plant_name_bn']) && !empty($plant['nickname'])): ?>
          <p class="card__meta"><?= e((string) $plant['plant_name_bn']) ?></p>
        <?php endif; ?>
        <div class="cluster">
          <span class="chip chip--muted"><?= e((string) config('content.space.' . $plant['location'])) ?></span>
          <?php if (!empty($plant['planted_on'])): ?>
            <span class="small muted">রোপণ: <?= e(bn_date((string) $plant['planted_on'])) ?></span>
          <?php endif; ?>
        </div>
      </a>
    <?php endforeach; ?>
  </div>
<?php endif; ?>
