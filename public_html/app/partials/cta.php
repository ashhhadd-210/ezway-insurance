<?php defined('APP') or exit; ?>
<section class="cta-band" aria-labelledby="ctaTitle">
  <div class="wrap">
    <div class="cta-card reveal">
      <?= pinwheel_svg('cta-art pinwheel') ?>
      <div class="cta-copy">
        <h2 id="ctaTitle"><?= e(L('Ready when you are. It only takes a few minutes.', 'Listos cuando tú lo estés. Solo toma unos minutos.')) ?></h2>
        <p><?= e(L(
            'Tell us what you need and a licensed agent will compare ' . cfg('site.carriers') . '+ companies for you. Free, with no obligation, in English or Spanish.',
            'Dinos qué necesitas y un agente con licencia comparará más de ' . cfg('site.carriers') . ' compañías por ti. Gratis, sin compromiso, en inglés o español.'
        )) ?></p>
      </div>
      <div class="cta-actions">
        <a class="btn btn-accent btn-lg" href="<?= e(lurl('quote')) ?>" data-quote><?= e(L('Start your quote', 'Empieza tu cotización')) ?> <?= icon('arrow-right') ?></a>
        <a class="cta-phone" href="<?= e(tel_href(cfg('site.phone'))) ?>"><?= icon('phone') ?> <?= e(L('or call', 'o llama al')) ?> <?= e(cfg('site.phone')) ?></a>
      </div>
    </div>
  </div>
</section>
