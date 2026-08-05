<?php
/** @var string $content */
use App\Core\View;

$links = [
    '/admin'          => 'Dashboard',
    '/admin/plants'   => 'গাছ',
    '/admin/problems' => 'রোগ ও পোকা',
    '/admin/guides'   => 'গাইড',
    '/admin/qa'       => 'প্রশ্ন-উত্তর',
    '/admin/users'    => 'ব্যবহারকারী',
    '/admin/contact'  => 'Contact Inbox',
    '/admin/logs'     => 'Audit log',
];
?>
<!doctype html>
<html lang="bn" data-theme="<?= e($theme ?? 'light') ?>">
<head>
<?= View::partial('partials/head', get_defined_vars()) ?>
</head>
<body>
<div class="admin-shell">
  <aside class="admin-side">
    <a class="logo" href="/admin">
      <?= View::partial('partials/logo') ?>
      <span>Admin</span>
    </a>
    <?php foreach ($links as $href => $label): ?>
      <a href="<?= e($href) ?>"<?= ($href === '/admin' ? $currentPath === '/admin' : str_starts_with($currentPath, $href)) ? ' aria-current="page"' : '' ?>><?= e($label) ?></a>
    <?php endforeach; ?>
    <form method="post" action="/admin/logout">
      <?= csrf_field() ?>
      <button class="btn btn--ghost btn--sm" type="submit">Logout</button>
    </form>
  </aside>

  <main class="admin-main" id="main">
    <?= View::partial('partials/flash', ['notice' => $notice ?? null]) ?>
    <?= $content ?>
  </main>
</div>

<script src="<?= e(asset('js/app.js')) ?>" defer></script>
</body>
</html>
