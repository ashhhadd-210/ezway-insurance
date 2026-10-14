<?php
require __DIR__ . '/app/bootstrap.php';

$page = [
    'slug'        => 'quote',
    'path'        => 'quote',
    'title'       => 'Get a free auto insurance quote',
    'description' => 'Request a free, no-obligation auto insurance quote. Add every driver and vehicle, choose your coverage and a licensed agent will get back to you.',
];
require APP_ROOT . '/partials/header.php';
?>

<section class="quote-page">
  <div class="hero-bg" aria-hidden="true"><span class="blob b1"></span><span class="blob b2"></span><span class="grid-lines"></span><?= pinwheel_svg('pinwheel page-pin') ?></div>
  <div class="wrap quote-layout">
    <div class="quote-main">
      <h1 class="sr">Get a free auto insurance quote</h1>
      <?php $qwMode = 'page'; require APP_ROOT . '/partials/quote-wizard.php'; ?>
    </div>
    <aside class="quote-aside">
      <div class="qa-card">
        <h2>Why quote with us</h2>
        <ul>
          <li><?= icon('search') ?><span><b>We compare for you</b>Multiple carriers, one simple form.</span></li>
          <li><?= icon('users') ?><span><b>Whole household</b>Add every driver and vehicle at once.</span></li>
          <li><?= icon('lock') ?><span><b>Private &amp; secure</b>License numbers are encrypted.</span></li>
          <li><?= icon('check') ?><span><b>No obligation</b>Free quotes, no pressure.</span></li>
        </ul>
      </div>
      <a class="qa-help" href="<?= e(tel_href(cfg('site.phone'))) ?>">
        <span class="qa-help-ic"><?= icon('phone') ?></span>
        <span><small>Prefer to talk?</small><b><?= e(cfg('site.phone')) ?></b></span>
      </a>
    </aside>
  </div>
</section>

<?php require APP_ROOT . '/partials/footer.php'; ?>
