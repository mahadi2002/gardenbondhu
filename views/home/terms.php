<?php $this->layout('layouts/public', ['title' => 'Terms & Conditions']); ?>
<section class="section">
  <div class="wrap prose">
    <h1>Terms &amp; Conditions</h1>
    <p class="muted">সর্বশেষ হালনাগাদ: <?= e(bn_date(date('Y-m-d'))) ?></p>

    <h2>Subscription ও Billing</h2>
    <p>
      বাগানবন্ধু একটি Daily Micro-Subscription Service, যার মূল্য দিনে ৳<?= e($dailyAmount) ?>
      (Incl. VAT, SD &amp; SC)। এটি শুধুমাত্র Robi ও Airtel নম্বরের জন্য the carrier billing provider-এর মাধ্যমে
      পরিচালিত হয়। Subscribe করলে প্রতিদিন আপনার Mobile Account থেকে টাকা কাটা হবে,
      যতক্ষণ না আপনি Unsubscribe করেন।
    </p>

    <h2>Unsubscribe</h2>
    <p>
      যেকোনো সময় Account পেজ থেকে, অথবা STOP লিখে <?= e($shortcode) ?> নম্বরে SMS করে
      Unsubscribe করা যায়। Unsubscribe করার পর থেকে আর কোনো Charge হবে না।
    </p>

    <h2>ব্যালেন্স কম থাকলে</h2>
    <p>
      কোনো দিনের Charge ব্যর্থ হলে ৪৮ ঘণ্টা পর্যন্ত Access চালু থাকে (Grace Period)।
      এর মধ্যে Recharge করলে Access নিজে থেকেই চালু থাকবে। ৪৮ ঘণ্টার মধ্যে Recharge না
      হলে Subscription বন্ধ হয়ে যাবে।
    </p>

    <h2>কনটেন্ট সম্পর্কে</h2>
    <p>
      এই সাইটের গাছের যত্ন ও রোগ নির্ণয়ের তথ্য সাধারণ নির্দেশনা হিসেবে দেওয়া হয়, কোনো
      নিশ্চিত বৈজ্ঞানিক রোগ নির্ণয় নয়। রাসায়নিক ব্যবহারের ক্ষেত্রে সবসময় প্যাকেটের
      নির্দেশনা অনুসরণ করুন এবং শিশু ও পোষা প্রাণী থেকে দূরে রাখুন।
    </p>

    <h2>দায়বদ্ধতা</h2>
    <p>
      বাগানবন্ধু কোনো গাছ, ফসল বা সম্পত্তির ক্ষতির জন্য দায়ী নয়। এই সাইট শুধুমাত্র
      তথ্যভিত্তিক সহায়তা প্রদান করে।
    </p>
  </div>
</section>
