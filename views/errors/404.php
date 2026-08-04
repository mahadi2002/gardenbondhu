<?php $this->layout('layouts/public', ['title' => 'পেজটি পাওয়া যায়নি']); ?>
<section class="section">
  <div class="wrap prose center">
    <p class="eyebrow mono">404</p>
    <h1>পেজটি খুঁজে পাওয়া যায়নি</h1>
    <p class="lede">লিংকটা হয়তো পুরোনো, বা ঠিকানায় একটু ভুল আছে। খুঁজে দেখুন:</p>

    <form method="get" action="/search" class="stack">
      <label class="visually-hidden" for="err-q">খুঁজুন</label>
      <input class="input" type="search" id="err-q" name="q" placeholder="যেমন: পাতা হলুদ, পুদিনা, মিলিবাগ">
      <button class="btn" type="submit">খুঁজুন</button>
    </form>

    <p class="cluster center">
      <a class="btn btn--ghost" href="/">হোম</a>
      <a class="btn btn--ghost" href="/plants">গাছের তালিকা</a>
      <a class="btn btn--ghost" href="/problems">রোগ ও পোকা</a>
    </p>
  </div>
</section>
