<?php
/** @var string $next */
use App\Core\View;

$this->layout('layouts/public', ['title' => 'Subscribe করুন']);
?>
<section class="section">
  <div class="wrap">
    <?= View::partial('partials/otp-box', get_defined_vars()) ?>

    <div class="prose center flash-slot" >
      <p class="small muted">
        নম্বরটি encrypted অবস্থায় রাখা হয় এবং শুধু subscription যাচাইয়ের জন্য ব্যবহৃত হয়।
        বিস্তারিত <a href="/privacy">Privacy Policy</a>-তে।
      </p>
    </div>
  </div>
</section>
