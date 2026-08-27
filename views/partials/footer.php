<footer class="site-footer">
  <div class="wrap">
    <div class="footer-grid">
      <div>
        <h4><?= e($appName) ?></h4>
        <p class="small">নতুন বাগানিদের জন্য বাংলায় গাছের যত্ন, রোগ নির্ণয় আর প্রতিদিনের কাজের গাইড।</p>
      </div>

      <div>
        <h4>দেখুন</h4>
        <ul class="footer-links small">
          <li><a href="/plants">গাছের তালিকা</a></li>
          <li><a href="/problems">রোগ ও পোকা</a></li>
          <li><a href="/guides">গাইড</a></li>
        </ul>
      </div>

      <div>
        <h4>তথ্য</h4>
        <ul class="footer-links small">
          <li><a href="/privacy">Privacy Policy</a></li>
          <li><a href="/terms">Terms &amp; Conditions</a></li>
          <li><a href="/contact">Contact Us</a></li>
        </ul>
      </div>

      <div>
        <h4>অ্যাকাউন্ট</h4>
        <ul class="footer-links small">
          <li><a href="/register">Register করুন</a></li>
          <li><a href="/login">Login করুন</a></li>
        </ul>
      </div>
    </div>

    <div class="footer-bottom">
      <span>© <?= e(date('Y')) ?> <?= e($appName) ?> — সর্বস্বত্ব সংরক্ষিত</span>
    </div>
  </div>
</footer>
