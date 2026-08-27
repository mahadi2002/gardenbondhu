<?php
/**
 * Gated app shell: left sidebar ≥900px, bottom tab bar below.
 * Most of this audience is on a phone — this is a ৳2.78/day product.
 *
 * @var string $content
 */
use App\Core\View;

$sideLinks = [
    'ড্যাশবোর্ড' => [
        ['/app',          'হোম',        'M3 11l9-8 9 8v9a2 2 0 0 1-2 2h-4v-6H9v6H5a2 2 0 0 1-2-2z'],
        ['/app/garden',   'আমার বাগান', 'M12 21V9m0 0c0-4 3-7 8-7 0 5-3 8-8 8m0 5c-4 0-7-2-7-6 4 0 7 2 7 6'],
        ['/app/calendar', 'মাসের কাজ',   'M4 6h16v15H4zM4 10h16M8 3v4M16 3v4'],
    ],
    'জ্ঞানভাণ্ডার' => [
        ['/app/plants',   'গাছ',        'M12 21V9m0 0c0-4 3-7 8-7 0 5-3 8-8 8'],
        ['/app/diagnose', 'রোগ নির্ণয়',  'M11 4a7 7 0 1 0 0 14 7 7 0 0 0 0-14zM20 20l-4-4'],
        ['/app/guides',   'গাইড',       'M5 4h11a3 3 0 0 1 3 3v13H8a3 3 0 0 1-3-3z M8 8h8M8 12h6'],
        ['/app/tools',    'হিসাবের টুল', 'M6 3h12v18H6zM9 7h6M9 11h6M9 15h2'],
        ['/app/qa',       'প্রশ্ন-উত্তর',  'M12 19h.01M9.5 9a2.5 2.5 0 1 1 3.6 2.2c-.7.4-1.1 1-1.1 1.8v.5M4 5h16v13H8l-4 3z'],
    ],
];

$tabs = [
    ['/app',          'হোম',      'M3 11l9-8 9 8v9a2 2 0 0 1-2 2h-4v-6H9v6H5a2 2 0 0 1-2-2z'],
    ['/app/garden',   'বাগান',    'M12 21V9m0 0c0-4 3-7 8-7 0 5-3 8-8 8m0 5c-4 0-7-2-7-6 4 0 7 2 7 6'],
    ['/app/diagnose', 'নির্ণয়',   'M11 4a7 7 0 1 0 0 14 7 7 0 0 0 0-14zM20 20l-4-4'],
    ['/app/plants',   'গাছ',      'M5 21h14M12 21V8m0 0c0-3.5 2.6-6 7-6 0 4.4-2.6 6-7 6'],
    ['/app/qa',       'প্রশ্ন',     'M4 5h16v13H8l-4 3z'],
];

$isCurrent = static function (string $href) use ($currentPath): bool {
    return $href === '/app' ? $currentPath === '/app' : str_starts_with($currentPath, $href);
};
?>
<!doctype html>
<html lang="bn" data-theme="<?= e($theme ?? 'light') ?>" data-season="<?= e((string) ($season ?? 6)) ?>">
<head>
<?= View::partial('partials/head', get_defined_vars()) ?>
</head>
<body>
<a class="skip-link" href="#main">মূল অংশে যান</a>

<div class="app-shell">
  <header class="app-topbar">
    <div class="wrap app-topbar__inner">
      <a class="logo" href="/app">
        <?= View::partial('partials/logo') ?>
        <span><?= e($appName) ?></span>
      </a>

      <nav class="nav" aria-label="অ্যাকাউন্ট">
        <a href="/account"<?= $currentPath === '/account' ? ' aria-current="page"' : '' ?>>Account</a>
        <button class="theme-toggle" type="button" data-theme-toggle
                aria-label="<?= ($theme ?? 'light') === 'dark' ? 'দিনের রঙে দেখুন' : 'রাতের রঙে দেখুন' ?>">
          <?= ($theme ?? 'light') === 'dark' ? '☀' : '☾' ?>
        </button>
        <form method="post" action="/logout" data-guard>
          <?= csrf_field() ?>
          <button class="btn btn--ghost btn--sm" type="submit">Logout</button>
        </form>
      </nav>
    </div>
  </header>

  <div class="app-body">
    <aside class="app-sidebar">
      <?php foreach ($sideLinks as $group => $items): ?>
        <div class="side-nav">
          <p class="group-label"><?= e($group) ?></p>
          <?php foreach ($items as [$href, $label, $path]): ?>
            <a href="<?= e($href) ?>"<?= $isCurrent($href) ? ' aria-current="page"' : '' ?>>
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"
                   stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="<?= e($path) ?>"/></svg>
              <span><?= e($label) ?></span>
            </a>
          <?php endforeach; ?>
        </div>
      <?php endforeach; ?>
    </aside>

    <main class="app-main" id="main">
      <?= View::partial('partials/flash', ['notice' => $notice ?? null]) ?>
      <?= $content ?>
    </main>
  </div>

  <nav class="tabbar" aria-label="দ্রুত মেনু">
    <?php foreach ($tabs as [$href, $label, $path]): ?>
      <a href="<?= e($href) ?>"<?= $isCurrent($href) ? ' aria-current="page"' : '' ?>>
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"
             stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="<?= e($path) ?>"/></svg>
        <span><?= e($label) ?></span>
      </a>
    <?php endforeach; ?>
  </nav>

  <div class="fab-backdrop" data-fab-backdrop></div>
  <div class="fab-group" data-fab-group data-open="false">
    <ul class="fab-menu">
      <li>
        <a href="/app/garden/add">
          <span class="fab-menu__icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 21V9m0 0c0-4 3-7 8-7 0 5-3 8-8 8m0 5c-4 0-7-2-7-6 4 0 7 2 7 6"/></svg></span>
          <span>গাছ যোগ করুন</span>
        </a>
      </li>
      <li>
        <a href="/app/diagnose">
          <span class="fab-menu__icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M11 4a7 7 0 1 0 0 14 7 7 0 0 0 0-14zM20 20l-4-4"/></svg></span>
          <span>রোগ নির্ণয়</span>
        </a>
      </li>
      <li>
        <a href="/app/qa/ask">
          <span class="fab-menu__icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 19h.01M9.5 9a2.5 2.5 0 1 1 3.6 2.2c-.7.4-1.1 1-1.1 1.8v.5M4 5h16v13H8l-4 3z"/></svg></span>
          <span>প্রশ্ন করুন</span>
        </a>
      </li>
    </ul>
    <button class="fab" type="button" data-fab-toggle aria-expanded="false" aria-haspopup="true" aria-label="দ্রুত কাজ">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>
    </button>
  </div>
</div>

<script src="<?= e(asset('js/app.js')) ?>" defer></script>
<?php foreach (($scripts ?? []) as $script): ?>
  <script src="<?= e(asset('js/' . $script)) ?>" defer></script>
<?php endforeach; ?>
</body>
</html>
