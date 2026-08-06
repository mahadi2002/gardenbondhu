<?php
/**
 * The cut-off block on teased content.
 *
 * There is nothing to hide here: the paid text was never SELECTed, so it is
 * not in the HTML, not in a data attribute, and not one devtools toggle away.
 * The gradient above is decoration; PHP truncation is the actual control.
 *
 * @var string|null $what  what is behind the wall, e.g. 'এই গাছের পূর্ণ যত্ন-নির্দেশিকা'
 */
$what ??= 'বাকি অংশ';
?>
<section class="paywall">
  <p class="eyebrow">এখান থেকে বাকিটা Subscriber-দের জন্য</p>
  <h3><?= e($what) ?> দেখতে Subscribe করুন</h3>

  <ul>
    <li>৬০+ গাছের পূর্ণ যত্ন-নির্দেশিকা — মাটি, পানি, রোদ, সার, ছাঁটাই</li>
    <li>পাতা দেখে রোগ নির্ণয় — ৪০+ পোকা ও রোগের জৈব ও রাসায়নিক সমাধান</li>
    <li>প্রতি মাসে কী করতে হবে, তার তালিকা</li>
    <li>নিজের বাগানের রেকর্ড আর কাজের রিমাইন্ডার</li>
  </ul>

  <p class="mono"><strong>Daily ৳<?= e($dailyAmount) ?></strong> (Incl. VAT, SD &amp; SC)</p>
  <p><a class="btn btn--accent btn--lg" href="/subscribe">Subscribe Now</a></p>
  <p class="small muted mb-0">Robi &amp; Airtel Users Only &nbsp;|&nbsp; যেকোনো সময় Unsubscribe করুন</p>
</section>
