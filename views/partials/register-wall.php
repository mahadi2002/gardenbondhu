<?php
/**
 * The cut-off block on teased content for signed-out visitors. Free to
 * register — no billing involved, just an account.
 *
 * @var string|null $what  what is behind the wall, e.g. 'এই গাছের পূর্ণ যত্ন-নির্দেশিকা'
 */
$what ??= 'বাকি অংশ';
?>
<section class="paywall">
  <p class="eyebrow">এখান থেকে বাকিটা লগইন করলেই দেখা যাবে</p>
  <h3><?= e($what) ?> দেখতে Login বা Register করুন</h3>

  <ul>
    <li>৬০+ গাছের পূর্ণ যত্ন-নির্দেশিকা — মাটি, পানি, রোদ, সার, ছাঁটাই</li>
    <li>পাতা দেখে রোগ নির্ণয় — পোকা ও রোগের জৈব ও রাসায়নিক সমাধান</li>
    <li>প্রতি মাসে কী করতে হবে, তার তালিকা</li>
    <li>নিজের বাগানের রেকর্ড আর কাজের রিমাইন্ডার</li>
  </ul>

  <p class="cluster">
    <a class="btn btn--accent btn--lg" href="/register">ফ্রি Register করুন</a>
    <a class="btn btn--ghost btn--lg" href="/login">Login করুন</a>
  </p>
  <p class="small muted mb-0">সম্পূর্ণ ফ্রি — কোনো কার্ড বা টাকা লাগবে না।</p>
</section>
