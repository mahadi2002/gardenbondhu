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
        <h4>Billing</h4>
        <p class="small">Powered by the carrier billing provider<br>Robi &amp; Airtel Bangladesh</p>
      </div>
    </div>

    <p class="charge-warning">
      ⚠️ এই Service the carrier billing provider-এর মাধ্যমে Charge করা হয়। Daily ৳<?= e($dailyAmount) ?> আপনার Robi /Airtel Account
      থেকে কাটা হবে। Unsubscribe করতে STOP লিখে <?= e($shortcode) ?> নম্বরে SMS করুন।
    </p>

    <div class="footer-bottom">
      <span>© <?= e(date('Y')) ?> <?= e($appName) ?> — সর্বস্বত্ব সংরক্ষিত</span>
      <span class="mono">৳<?= e($dailyAmount) ?>/day · Incl. VAT, SD &amp; SC</span>
    </div>
  </div>
</footer>
