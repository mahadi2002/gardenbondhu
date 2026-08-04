<?php
/**
 * @var array|null $result
 * @var string|null $tool
 * @var array|null $errors
 */
$this->layout('layouts/app', ['title' => 'হিসাবের টুল']);
?>
<div class="page-head">
  <h1>হিসাবের টুল</h1>
  <p class="muted">প্রতিটা হিসাবের সাথে সূত্রটাও দেখানো হয় — এটা একটা আনুমানিক হিসাব, চোখে দেখে মাটির অবস্থাও যাচাই করে নিন।</p>
</div>

<div class="grid grid--3">
  <!-- Water -->
  <div class="card">
    <h2 class="card__title">পানির হিসাব</h2>
    <form method="post" action="/app/tools/water" data-guard>
      <?= csrf_field() ?>
      <div class="field">
        <label for="w-diameter">টবের ব্যাস (সেমি)</label>
        <input class="input" type="number" id="w-diameter" name="diameter" min="5" max="120" step="1" required>
      </div>
      <div class="field">
        <label for="w-need">পানির চাহিদা</label>
        <select class="input" id="w-need" name="water_need" required>
          <?php foreach ((array) config('content.water') as $value => $label): ?>
            <option value="<?= e((string) $value) ?>"><?= e($label) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="field">
        <label for="w-season">ঋতু</label>
        <select class="input" id="w-season" name="season" required>
          <?php $seasons = [1=>'গ্রীষ্ম',2=>'বর্ষা',3=>'শরৎ',4=>'হেমন্ত',5=>'শীত',6=>'বসন্ত']; ?>
          <?php foreach ($seasons as $value => $label): ?>
            <option value="<?= e((string) $value) ?>" <?= $value === \App\Services\CareScheduler::currentSeasonId() ? ' selected' : '' ?>><?= e($label) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="field">
        <label for="w-loc">জায়গা</label>
        <select class="input" id="w-loc" name="location" required>
          <?php foreach (['balcony'=>'বারান্দা','rooftop'=>'ছাদ','indoor'=>'ঘরের ভেতর','yard'=>'উঠান'] as $value => $label): ?>
            <option value="<?= e($value) ?>"><?= e($label) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <button class="btn btn--block" type="submit">হিসাব করুন</button>
    </form>

    <?php if (($tool ?? null) === 'water' && $result): ?>
      <div class="formula mt-1">
        <p class="mb-0"><strong><?= e((string) $result['litres']) ?> লিটার</strong>, প্রতি <?= e(bn_num($result['interval'])) ?> দিনে একবার</p>
        <p class="small mb-0"><?= e($result['formula']) ?></p>
        <?php if ($result['warning']): ?><p class="small mb-0 text-brick"><?= e($result['warning']) ?></p><?php endif; ?>
      </div>
    <?php endif; ?>
  </div>

  <!-- Fertilizer -->
  <div class="card">
    <h2 class="card__title">সারের হিসাব</h2>
    <form method="post" action="/app/tools/fertilizer" data-guard>
      <?= csrf_field() ?>
      <div class="field">
        <label for="f-vol">টবের মাটির আয়তন (লিটার)</label>
        <input class="input" type="number" id="f-vol" name="pot_volume" min="1" max="200" step="1" required>
      </div>
      <div class="field">
        <label for="f-type">সারের ধরন</label>
        <select class="input" id="f-type" name="type" required>
          <option value="npk_balanced">NPK ১০-১০-১০</option>
          <option value="npk_bloom">NPK ফুলের সার</option>
          <option value="vermicompost">কেঁচো সার</option>
          <option value="mustard_cake">সরিষার খৈল</option>
          <option value="cow_dung">পচা গোবর</option>
        </select>
      </div>
      <button class="btn btn--block" type="submit">হিসাব করুন</button>
    </form>

    <?php if (($tool ?? null) === 'fertilizer' && $result): ?>
      <div class="formula mt-1">
        <p class="mb-0"><strong><?= e((string) $result['grams']) ?> গ্রাম</strong> (<?= e($result['label']) ?>), প্রতি <?= e(bn_num($result['interval'])) ?> দিনে</p>
        <p class="small mb-0"><?= e($result['formula']) ?></p>
        <p class="small mb-0"><?= e($result['note']) ?></p>
        <?php if ($result['warning']): ?><p class="small mb-0 text-brick"><?= e($result['warning']) ?></p><?php endif; ?>
      </div>
    <?php endif; ?>
  </div>

  <!-- Pot size -->
  <div class="card">
    <h2 class="card__title">টবের মাপ</h2>
    <form method="post" action="/app/tools/pot" data-guard>
      <?= csrf_field() ?>
      <div class="field">
        <label for="p-mat">গাছের বয়স</label>
        <select class="input" id="p-mat" name="maturity" required>
          <option value="seedling">চারা</option>
          <option value="young">তরুণ গাছ</option>
          <option value="mature">পূর্ণবয়স্ক</option>
        </select>
      </div>
      <div class="field">
        <label for="p-root">শিকড়ের ধরন</label>
        <select class="input" id="p-root" name="root_type" required>
          <option value="shallow">অগভীর</option>
          <option value="medium">মাঝারি</option>
          <option value="deep">গভীর</option>
          <option value="tap">মূলমূল (Taproot)</option>
        </select>
      </div>
      <button class="btn btn--block" type="submit">হিসাব করুন</button>
    </form>

    <?php if (($tool ?? null) === 'pot' && $result): ?>
      <div class="formula mt-1">
        <p class="mb-0"><strong>ব্যাস <?= e(bn_num($result['diameter'])) ?> সেমি</strong>, গভীরতা <?= e(bn_num($result['depth'])) ?> সেমি</p>
        <p class="small mb-0"><?= e($result['formula']) ?></p>
        <p class="small mb-0"><?= e($result['note']) ?></p>
      </div>
    <?php endif; ?>
  </div>
</div>
