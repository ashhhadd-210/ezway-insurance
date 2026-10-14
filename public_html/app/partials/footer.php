<?php
defined('APP') or exit;
$isQuotePage = ($page['slug'] ?? '') === 'quote';
$addr = cfg('site.address');
?>
</main>

<footer class="footer">
  <div class="wrap footer-grid">
    <div class="footer-brand">
      <a class="logo logo-light" href="<?= e(url()) ?>"><?= logo_html(true) ?></a>
      <p><?= e(cfg('site.tagline')) ?>. Licensed agents who compare coverage for you and stay with you when it's time to file a claim.</p>
      <a class="btn btn-primary" href="<?= e(url('quote')) ?>" data-quote>Start your quote <?= icon('arrow-right') ?></a>
    </div>
    <div>
      <h2 class="footer-h">Explore</h2>
      <ul class="footer-links">
        <li><a href="<?= e(url()) ?>">Home</a></li>
        <li><a href="<?= e(url('about')) ?>">About us</a></li>
        <li><a href="<?= e(url('coverage')) ?>">Coverage</a></li>
        <li><a href="<?= e(url('contact')) ?>">Contact</a></li>
        <li><a href="<?= e(url('quote')) ?>">Get a quote</a></li>
      </ul>
    </div>
    <div>
      <h2 class="footer-h">Contact</h2>
      <ul class="footer-links footer-contact">
        <li><a href="<?= e(tel_href(cfg('site.phone'))) ?>"><?= icon('phone') ?><?= e(cfg('site.phone')) ?></a></li>
        <li><a href="mailto:<?= e(cfg('site.email')) ?>"><?= icon('mail') ?><?= e(cfg('site.email')) ?></a></li>
        <li><span><?= icon('pin') ?><?= e($addr['street']) ?><br><?= e($addr['city'] . ', ' . $addr['state'] . ' ' . $addr['zip']) ?></span></li>
      </ul>
    </div>
    <div>
      <h2 class="footer-h">Hours</h2>
      <dl class="footer-hours">
        <?php foreach (cfg('site.hours', []) as [$d, $h]): ?>
          <div><dt><?= e($d) ?></dt><dd><?= e($h) ?></dd></div>
        <?php endforeach; ?>
      </dl>
    </div>
  </div>
  <div class="wrap footer-bottom">
    <p>© <?= date('Y') ?> <?= e(cfg('site.legal_name')) ?>. All rights reserved.</p>
    <p class="footer-legal">Quotes are estimates and not a guarantee of coverage. Coverage is subject to underwriting and the terms of the policy issued. <a href="<?= e(url('privacy')) ?>">Privacy policy</a></p>
  </div>
</footer>

<?php if (!$isQuotePage): ?>
<button class="fab" type="button" data-quote data-fab aria-label="Start your quote">
  <span class="fab-ring" aria-hidden="true"></span>
  <span class="fab-icon"><?= icon('shield') ?></span>
  <span class="fab-text">Start your quote</span>
  <?= icon('arrow-right', 'fab-arrow') ?>
</button>

<div class="q-overlay" data-qoverlay hidden>
  <div class="q-sheet" role="dialog" aria-modal="true" aria-labelledby="qwTitle">
    <?php $qwMode = 'modal'; require APP_ROOT . '/partials/quote-wizard.php'; ?>
  </div>
</div>
<?php endif; ?>

<div class="toast" role="status" aria-live="polite" data-toast></div>

<script src="<?= e(asset('js/site.js')) ?>" defer></script>
<script src="<?= e(asset('js/quote.js')) ?>" defer></script>
<?php if (turnstile_enabled()): ?>
<script src="https://challenges.cloudflare.com/turnstile/v0/api.js?render=explicit" defer></script>
<?php endif; ?>
</body>
</html>
