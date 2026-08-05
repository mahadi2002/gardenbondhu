<?php
/**
 * The landing page. Section order and the exact wording in a few spots
 * (the price badge, the OTP box copy) are locked on purpose — this page
 * gets read closely by people deciding whether to hand over their phone
 * number, wording changes shouldn't happen casually.
 *
 * @var array $symptoms  body_part => rows
 * @var array $stats
 */
use App\Core\View;

$this->layout('layouts/public', [
    'title'       => null,
    'description' => 'গাছ মরে যাচ্ছে কেন, কোন পোকা, কতটুকু পানি — নতুন বাগানিদের জন্য বাংলায় সম্পূর্ণ গাইড। দিনে মাত্র ৳2.78।',
    'scripts'     => ['diagnose.js'],
]);

$bodyParts = (array) config('content.body_parts');

$features = [
    ['গাছ খুঁজুন',      'বারান্দা, ছাদ না ঘরের ভেতর — জায়গা আর রোদ অনুযায়ী সঠিক গাছ বেছে নিন'],
    ['রোগ নির্ণয়',      'পাতার কোন অংশে সমস্যা, সেটা দেখিয়ে দিন — কারণ আর সমাধান পাবেন সাথে সাথে'],
    ['মাটি ও টব',       'কোন গাছে কোন মিশ্রণ, কত বড় টব, কীভাবে পানি নিষ্কাশন'],
    ['পানির হিসাব',     'অতিরিক্ত পানিতেই সবচেয়ে বেশি গাছ মরে — কতটুকু, কত দিন পরপর, জেনে নিন'],
    ['সার ও পুষ্টি',     'NPK মানে কী, কখন দেবেন, কতটুকু দেবেন, কোন লক্ষণে কোন সার'],
    ['মাসের কাজ',       'বাংলা ঋতু ধরে প্রতি মাসের করণীয় — কোন গাছ লাগাবেন, কী ছাঁটবেন'],
    ['চারা তৈরি',       'কাটিং, দাবা কলম, বীজ — নিজেই নতুন চারা বানান'],
    ['আমার বাগান',      'নিজের গাছগুলো যোগ করুন, পানি-সারের রেকর্ড রাখুন, রিমাইন্ডার পান'],
    ['ছাদবাগান গাইড',   'ওজন, পানি নিষ্কাশন, রোদ — ছাদে বাগান করার সম্পূর্ণ নিয়ম'],
    ['প্রশ্ন করুন',       'আটকে গেছেন? ছবি দিয়ে প্রশ্ন করুন, উত্তর পাবেন'],
];

$mistakes = [
    'রোজ পানি দেওয়া — অতিরিক্ত পানিতেই বেশিরভাগ গাছ মরে, শুকিয়ে নয়।',
    'ছায়ার গাছ রোদে, রোদের গাছ ছায়ায় রাখা।',
    'টবের নিচে ছিদ্র না রাখা, বা ছিদ্র বন্ধ হয়ে যাওয়া।',
    'বাগানের এঁটেল মাটি সরাসরি টবে ভরা — শক্ত হয়ে শিকড়ে বাতাস ঢোকে না।',
    'সমস্যা বুঝে না নিয়েই যেকোনো কীটনাশক স্প্রে করা।',
    'কাঁচা গোবর বা বেশি সার দেওয়া — শিকড় পুড়ে যায়।',
];

$faqs = [
    ['টাকা কীভাবে কাটা হবে?',
     'প্রতিদিন ৳' . $dailyAmount . ' আপনার Robi/Airtel ব্যালেন্স থেকে। আলাদা কার্ড বা bKash লাগবে না।'],
    ['বন্ধ করব কীভাবে?',
     'STOP লিখে ' . $shortcode . ' নম্বরে SMS করুন, অথবা Account পেজ থেকে Unsubscribe করুন। সাথে সাথেই বন্ধ হবে।'],
    ['Grameenphone/Banglalink দিয়ে হবে?',
     'এখন শুধু Robi ও Airtel। অন্য অপারেটর শীঘ্রই আসছে।'],
    ['ব্যালেন্স না থাকলে?',
     '২ দিন পর্যন্ত access চালু থাকবে। এর মধ্যে recharge করলে কিছুই হারাবে না।'],
    ['আমি একদম নতুন, কিছুই জানি না — কাজে লাগবে?',
     'এই সাইট নতুনদের জন্যই বানানো। প্রতিটা গাইড শূন্য থেকে শুরু।'],
    ['আমার নম্বর কি অন্য কাউকে দেওয়া হবে?',
     'না। নম্বর encrypted অবস্থায় রাখা হয়, শুধু subscription যাচাইয়ের জন্য ব্যবহৃত হয়।'],
];

$steps = [
    ['নম্বর দিন',       'Robi বা Airtel নম্বর লিখে OTP নিন।'],
    ['Code বসান',       'SMS-এ আসা ৬ সংখ্যার Code বসান।'],
    ['Access চালু',     'সাথে সাথেই সব Content খুলে যাবে।'],
    ['বাগান শুরু',      'নিজের গাছ যোগ করুন, রিমাইন্ডার পান।'],
];
?>

<!-- 2 · HERO ------------------------------------------------------------->
<section class="hero">
  <div class="wrap hero__inner">
    <div>
      <p class="eyebrow">নতুন বাগানিদের জন্য · বাংলায়</p>
      <h1>গাছটা কেন মরে যাচ্ছে, সেটা আগে বুঝুন</h1>
      <p class="lede">
        পাতা হলুদ, কুঁড়ি ঝরে যাচ্ছে, মাটিতে ছোট মাছি — প্রতিটা লক্ষণের পেছনে একটা কারণ আছে।
        বাগানবন্ধু সেই কারণটা ধরিয়ে দেয়, আর কী করতে হবে সেটাও বলে দেয়।
      </p>
      <p class="cluster">
        <a class="btn btn--accent btn--lg" href="#otp-box">Subscribe Now</a>
        <a class="btn btn--ghost btn--lg" href="#diagnose-demo">আগে দেখে নিই</a>
      </p>
      <p class="small muted">Robi &amp; Airtel Users Only &nbsp;|&nbsp; যেকোনো সময় Unsubscribe করুন</p>
    </div>

    <div class="hero__art">
      <svg viewBox="0 0 320 320" role="img" aria-label="বৃষ্টির পর ভেজা পাতা">
        <circle cx="160" cy="160" r="140" fill="none" stroke="var(--line)" stroke-width="1"/>
        <path class="draw-path" d="M160 300V120" stroke="var(--leaf)" stroke-width="8"
              stroke-linecap="round" fill="none"/>
        <g class="draw-fill">
          <path d="M160 196c0-42 30-72 84-78-3 47-33 78-84 78Z" fill="var(--leaf)"/>
          <path d="M160 250c0-36-28-63-74-68 3 41 30 68 74 68Z" fill="var(--leaf-dark)"/>
          <path d="M160 196c20-22 47-39 84-45M160 250c-17-19-41-33-74-38"
                stroke="var(--paper)" stroke-width="2" fill="none" opacity=".5"/>
          <circle cx="160" cy="104" r="20" fill="var(--marigold)"/>
          <circle cx="160" cy="104" r="8" fill="var(--marigold-d)"/>
          <circle cx="206" cy="176" r="5" fill="var(--paper)" opacity=".75"/>
          <circle cx="228" cy="160" r="3.5" fill="var(--paper)" opacity=".6"/>
          <circle cx="118" cy="228" r="4" fill="var(--paper)" opacity=".55"/>
        </g>
      </svg>
    </div>
  </div>
</section>

<!-- 3 · PROBLEM ---------------------------------------------------------->
<section class="section section--tight">
  <div class="wrap reveal">
    <h2>নতুন বাগানিদের ৬টি সাধারণ ভুল</h2>
    <p class="lede">এর মধ্যে অন্তত দুটো আপনিও করছেন। খারাপ কিছু না — কেউ বলে দেয়নি, এই যা।</p>

    <ul class="pain-list">
      <?php foreach ($mistakes as $i => $mistake): ?>
        <li><span class="mark"><?= e(bn_num($i + 1)) ?></span><span><?= e($mistake) ?></span></li>
      <?php endforeach; ?>
    </ul>
  </div>
</section>

<!-- 4 · FEATURES --------------------------------------------------------->
<section class="section">
  <div class="wrap">
    <div class="reveal">
      <p class="eyebrow">কী কী পাবেন</p>
      <h2>বাগান করার প্রতিটা ধাপে</h2>
    </div>

    <div class="grid grid--3">
      <?php foreach ($features as $i => [$featureTitle, $featureText]): ?>
        <article class="card feature-card reveal">
          <p class="num"><?= e(str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT)) ?></p>
          <h3 class="card__title"><?= e($featureTitle) ?></h3>
          <p class="small mb-0"><?= e($featureText) ?></p>
        </article>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- 5 · DIAGNOSE DEMO (signature element) -------------------------------->
<section class="section" id="diagnose-demo">
  <div class="wrap">
    <div class="reveal">
      <p class="eyebrow">লাইভ ডেমো</p>
      <h2>পাতা দেখে রোগ নির্ণয়</h2>
      <p class="lede">
        গাছের যে অংশে সমস্যা, সেখানে চাপ দিন। লক্ষণগুলো বেছে নিন — সম্ভাব্য কারণ আর
        সমাধান সাজিয়ে দেওয়া হবে।
      </p>
    </div>

    <form class="diagnoser" id="diagnose-form" method="post" action="/app/diagnose">
      <?= csrf_field() ?>

      <?= View::partial('partials/leaf-picker', ['bodyParts' => $bodyParts]) ?>

      <div>
        <div class="part-tabs">
          <?php foreach ($bodyParts as $part => $label): ?>
            <?php if (!empty($symptoms[$part])): ?>
              <button class="part-tab" type="button" data-part="<?= e($part) ?>" aria-pressed="false">
                <?= e($label) ?>
              </button>
            <?php endif; ?>
          <?php endforeach; ?>
        </div>

        <?php foreach ($bodyParts as $part => $label): ?>
          <?php if (empty($symptoms[$part])) { continue; } ?>
          <div class="symptom-group" data-part-group="<?= e($part) ?>">
            <h3><?= e($label) ?></h3>
            <div class="symptom-grid">
              <?php foreach ($symptoms[$part] as $symptom): ?>
                <label class="check">
                  <input type="checkbox" name="symptoms[]" value="<?= e((string) $symptom['id']) ?>"
                         data-part="<?= e($part) ?>">
                  <span><?= e((string) $symptom['name_bn']) ?></span>
                </label>
              <?php endforeach; ?>
            </div>
          </div>
        <?php endforeach; ?>

        <p class="small muted" id="symptom-count" role="status">কোনো লক্ষণ বাছাই করা হয়নি</p>

        <p class="cluster">
          <button class="btn btn--lg" type="submit">সম্ভাব্য কারণ দেখুন</button>
          <span class="small muted">ফলাফল দেখতে Subscribe করতে হবে</span>
        </p>
      </div>
    </form>
  </div>
</section>

<!-- 6 · CONTENT STATS ---------------------------------------------------->
<section class="section section--tight">
  <div class="wrap stats reveal">
    <div class="stat">
      <p class="stat__n mb-0" data-count-to="<?= e((string) (int) $stats['plants']) ?>" data-suffix="+"><?= e(bn_num((int) $stats['plants'])) ?>+</p>
      <p class="stat__l mb-0">গাছ</p>
    </div>
    <div class="stat">
      <p class="stat__n mb-0" data-count-to="<?= e((string) (int) $stats['problems']) ?>" data-suffix="+"><?= e(bn_num((int) $stats['problems'])) ?>+</p>
      <p class="stat__l mb-0">রোগ ও পোকা</p>
    </div>
    <div class="stat">
      <p class="stat__n mb-0" data-count-to="<?= e((string) (int) $stats['guides']) ?>" data-suffix="+"><?= e(bn_num((int) $stats['guides'])) ?>+</p>
      <p class="stat__l mb-0">গাইড</p>
    </div>
    <div class="stat">
      <p class="stat__n mb-0" data-count-to="12">১২</p>
      <p class="stat__l mb-0">মাসের ক্যালেন্ডার</p>
    </div>
  </div>
</section>

<!-- 7 · CTA BLOCK -------------------------------------------------------->
<section class="section">
  <div class="wrap">
    <div class="cta-block reveal">
      <h2>🚀 এখনই Start করুন — মাত্র ৳<?= e($dailyAmount) ?>/day</h2>
      <p>Robi &amp; Airtel Users Only &nbsp;|&nbsp; যেকোনো সময় Unsubscribe করুন</p>

      <div class="value-copy">একটা মরে যাওয়া গাছের দাম ২০০ টাকা। একটা ভুল সারের প্যাকেট ১৫০ টাকা।
বাগানবন্ধু — দিনে মাত্র ৳<?= e($dailyAmount) ?>।

প্রতিদিন ৳<?= e($dailyAmount) ?>-তে আপনি পাচ্ছেন:
• ৬০+ গাছের পূর্ণ যত্ন-নির্দেশিকা — মাটি, পানি, রোদ, সার, ছাঁটাই
• পাতা দেখে রোগ নির্ণয় — ৪০+ পোকা ও রোগের জৈব ও রাসায়নিক সমাধান
• প্রতি মাসে কী করতে হবে, তার তালিকা
• পানি ও সারের সঠিক পরিমাণ হিসাব করার টুল
• আপনার নিজের বাগানের রেকর্ড আর কাজের রিমাইন্ডার
• যেকোনো সমস্যায় প্রশ্ন করার সুযোগ

কোনো চুক্তি নেই। কোনো কার্ড লাগবে না। যেদিন ইচ্ছা বন্ধ করে দিন।</div>

      <p><a class="btn btn--accent btn--lg" href="#otp-box">Subscribe Now</a></p>
    </div>
  </div>
</section>

<!-- 8 · HOW IT WORKS ----------------------------------------------------->
<section class="section section--tight">
  <div class="wrap">
    <h2 class="reveal">কীভাবে শুরু করবেন</h2>
    <ol class="steps">
      <?php foreach ($steps as [$stepTitle, $stepText]): ?>
        <li class="reveal">
          <h3><?= e($stepTitle) ?></h3>
          <p class="small mb-0"><?= e($stepText) ?></p>
        </li>
      <?php endforeach; ?>
    </ol>
  </div>
</section>

<!-- 9 · FAQ -------------------------------------------------------------->
<section class="section">
  <div class="wrap faq">
    <h2 class="center reveal">সাধারণ প্রশ্ন</h2>
    <?php foreach ($faqs as [$question, $answer]): ?>
      <details>
        <summary><?= e($question) ?></summary>
        <div><?= e($answer) ?></div>
      </details>
    <?php endforeach; ?>
  </div>
</section>

<!-- 10 · OTP BOX --------------------------------------------------------->
<section class="section section--tight">
  <div class="wrap">
    <?= View::partial('partials/otp-box', get_defined_vars()) ?>
  </div>
</section>
