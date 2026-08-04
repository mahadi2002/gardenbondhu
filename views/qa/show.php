<?php
/**
 * @var array $question
 * @var array $answers
 * @var bool  $isOwner
 */
use App\Services\ImageService;

$this->layout('layouts/app', ['title' => (string) $question['title']]);

$statusLabels = ['pending' => 'যাচাইয়ের অপেক্ষায়', 'approved' => 'অনুমোদিত', 'answered' => 'উত্তর দেওয়া হয়েছে', 'rejected' => 'বাতিল'];
?>
<div class="page-head">
  <div class="cluster">
    <?php if ($question['status'] === 'pending'): ?>
      <span class="chip chip--medium">যাচাইয়ের অপেক্ষায়</span>
    <?php endif; ?>
    <?php if (!empty($question['plant_name_bn'])): ?>
      <a class="chip" href="/app/plants/<?= e((string) $question['plant_slug']) ?>"><?= e((string) $question['plant_name_bn']) ?></a>
    <?php endif; ?>
  </div>
  <h1><?= e((string) $question['title']) ?></h1>
  <p class="muted">
    <?= e((string) ($question['display_name'] ?: '01••••' . $question['msisdn_last4'])) ?>
    · <?= e(bn_date((string) $question['created_at'])) ?>
  </p>
</div>

<div class="card">
  <p class="pre-line"><?= e((string) $question['body']) ?></p>
  <?php if (!empty($question['image'])): ?>
    <img class="rounded-s" src="/media/<?= e(ImageService::toToken((string) $question['image'])) ?>" alt="" loading="lazy">
  <?php endif; ?>
</div>

<?php if ($isOwner && $question['status'] === 'pending'): ?>
  <div class="notice notice--info mt-1">
    <span class="notice__icon" aria-hidden="true">i</span>
    <span>প্রশ্নটি যাচাইয়ের অপেক্ষায়। অনুমোদনের পর সবাই দেখতে ও উত্তর দিতে পারবেন।</span>
  </div>
<?php endif; ?>

<section class="section--tight">
  <h2><?= e(bn_num(count($answers))) ?>টি উত্তর</h2>

  <div class="stack">
    <?php foreach ($answers as $answer): ?>
      <div class="card">
        <div class="between">
          <strong>
            <?= e((string) ($answer['admin_name'] ?? $answer['display_name'] ?? ('01••••' . ($answer['msisdn_last4'] ?? '')))) ?>
          </strong>
          <?php if ((int) $answer['is_expert'] === 1): ?><span class="chip chip--low">বিশেষজ্ঞ</span><?php endif; ?>
        </div>
        <p class="mb-0 pre-line"><?= e((string) $answer['body']) ?></p>
        <p class="small muted mb-0"><?= e(bn_date((string) $answer['created_at'])) ?></p>
      </div>
    <?php endforeach; ?>
  </div>

  <?php if (in_array((string) $question['status'], ['approved', 'answered'], true)): ?>
    <form class="card mt-1" method="post" action="/app/qa/<?= e((string) $question['id']) ?>/answer" data-guard>
      <?= csrf_field() ?>
      <div class="field mb-0">
        <label for="ans-body">উত্তর লিখুন</label>
        <textarea class="input" id="ans-body" name="body" maxlength="4000" required></textarea>
      </div>
      <button class="btn mt-0-75" type="submit">উত্তর দিন</button>
    </form>
  <?php endif; ?>
</section>
