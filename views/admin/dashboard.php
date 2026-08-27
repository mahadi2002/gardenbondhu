<?php
/**
 * @var int   $activeUsers, $newUsers7d, $pendingQuestions, $newContacts
 * @var array $content
 */
$this->layout('layouts/admin', ['title' => 'Dashboard']);
?>
<div class="page-head"><h1>Dashboard</h1></div>

<div class="kpi-grid reveal">
  <div class="kpi"><p class="kpi__n mb-0"><?= e(bn_num($activeUsers)) ?></p><p class="kpi__l mb-0">Active users</p></div>
  <div class="kpi"><p class="kpi__n mb-0"><?= e(bn_num($newUsers7d)) ?></p><p class="kpi__l mb-0">New users (7d)</p></div>
  <div class="kpi"><p class="kpi__n mb-0"><?= e(bn_num($pendingQuestions)) ?></p><p class="kpi__l mb-0">Questions pending review</p></div>
  <div class="kpi"><a href="/admin/contact"><p class="kpi__n mb-0"><?= e(bn_num($newContacts)) ?></p><p class="kpi__l mb-0">New contact messages</p></a></div>
</div>

<div class="kpi-grid mt-1-5 reveal">
  <div class="kpi"><p class="kpi__n mb-0"><?= e(bn_num($content['plants'])) ?></p><p class="kpi__l mb-0">Published plants</p></div>
  <div class="kpi"><p class="kpi__n mb-0"><?= e(bn_num($content['problems'])) ?></p><p class="kpi__l mb-0">Published problems</p></div>
  <div class="kpi"><p class="kpi__n mb-0"><?= e(bn_num($content['guides'])) ?></p><p class="kpi__l mb-0">Published guides</p></div>
</div>
