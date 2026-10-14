<?php defined('APP') or exit; ?>
<section class="cta-band" aria-labelledby="ctaTitle">
  <div class="wrap">
    <div class="cta-card reveal">
      <?= pinwheel_svg('cta-art pinwheel') ?>
      <div class="cta-copy">
        <h2 id="ctaTitle">Ready when you are. It only takes a few minutes.</h2>
        <p>Add your drivers and vehicles, choose your coverage, and a licensed agent will get back to you with options. Free, with no obligation.</p>
      </div>
      <div class="cta-actions">
        <a class="btn btn-accent btn-lg" href="<?= e(url('quote')) ?>" data-quote>Start your quote <?= icon('arrow-right') ?></a>
        <a class="cta-phone" href="<?= e(tel_href(cfg('site.phone'))) ?>"><?= icon('phone') ?> or call <?= e(cfg('site.phone')) ?></a>
      </div>
    </div>
  </div>
</section>
