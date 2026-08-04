<?php
/**
 * @var string|null $title
 * @var string|null $description
 * @var string      $theme
 */
$pageTitle = isset($title) && $title !== ''
    ? $title . ' — ' . ($appName ?? 'বাগানবন্ধু')
    : ($appName ?? 'বাগানবন্ধু') . ' — নতুন বাগানিদের জন্য বাংলা গাইড';

$metaDescription = $description
    ?? 'গাছের যত্ন, রোগ নির্ণয়, মাটি-পানি-সারের হিসাব — নতুন বাগানিদের জন্য সম্পূর্ণ বাংলা গাইড।';
?>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($pageTitle) ?></title>
<meta name="description" content="<?= e($metaDescription) ?>">
<meta name="theme-color" content="#17452C">
<link rel="icon" href="/favicon.svg" type="image/svg+xml">
<link rel="stylesheet" href="<?= e(asset('css/app.css')) ?>">
<meta property="og:title" content="<?= e($pageTitle) ?>">
<meta property="og:description" content="<?= e($metaDescription) ?>">
<meta property="og:type" content="website">
<meta property="og:locale" content="bn_BD">
