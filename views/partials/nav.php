<?php
/** @var string $currentPath */
$links = [
    '/plants'   => 'গাছ',
    '/problems' => 'রোগ ও পোকা',
    '/guides'   => 'গাইড',
    '/about'    => 'আমাদের কথা',
];
$isLoggedIn = \App\Core\Session::userId() !== null;
?>
<button class="nav-toggle" type="button" data-nav-toggle aria-expanded="false" aria-controls="primary-nav">
  <svg viewBox="0 0 24 24" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
    <path d="M4 7h16M4 12h16M4 17h16"/>
  </svg>
  <span class="visually-hidden">মেনু</span>
</button>

<nav class="nav" id="primary-nav" data-open="false" aria-label="প্রধান মেনু">
  <?php foreach ($links as $href => $label): ?>
    <a href="<?= e($href) ?>"<?= str_starts_with($currentPath, $href) ? ' aria-current="page"' : '' ?>><?= e($label) ?></a>
  <?php endforeach; ?>

  <?php if ($isLoggedIn): ?>
    <a href="/app">আমার অ্যাকাউন্ট</a>
    <a class="price-badge header-cta" href="/app">অ্যাপে যান</a>
  <?php else: ?>
    <a class="price-badge header-cta" href="/subscribe">
      <span class="amount">৳<?= e($dailyAmount) ?></span><span class="per">/day</span>
    </a>
  <?php endif; ?>

  <button class="theme-toggle" type="button" data-theme-toggle
          aria-label="<?= ($theme ?? 'light') === 'dark' ? 'দিনের রঙে দেখুন' : 'রাতের রঙে দেখুন' ?>">
    <?= ($theme ?? 'light') === 'dark' ? '☀' : '☾' ?>
  </button>
</nav>
