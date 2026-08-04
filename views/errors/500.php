<?php
/**
 * Deliberately dependency-free: this page has to render when the database is
 * down, so it uses no repository, no session and no config lookup that could
 * itself fail.
 */
?>
<!doctype html>
<html lang="bn">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>সাময়িক সমস্যা — বাগানবন্ধু</title>
<link rel="icon" href="/favicon.svg" type="image/svg+xml">
<link rel="stylesheet" href="/assets/css/app.css">
</head>
<body>
<main id="main" class="section">
  <div class="wrap prose center">
    <p class="eyebrow mono">500</p>
    <h1>সাময়িক সমস্যা হচ্ছে। একটু পরে আবার চেষ্টা করুন।</h1>
    <p class="lede">সমস্যাটা আমাদের দিকে, আপনার নয়। আমরা দেখছি।</p>
    <p><a class="btn" href="/">হোমে ফিরুন</a></p>
  </div>
</main>
</body>
</html>
