<?php
require __DIR__ . '/app/bootstrap.php';

$page = [
    'slug'        => 'quote',
    'path'        => 'quote',
    'title'       => L('Get a free quote', 'Cotiza gratis'),
    'description' => L('Request a free, no-obligation quote for auto, home, business, bonds or title services. Any type of ID accepted. A licensed agent compares 20+ companies for you.', 'Pide una cotización gratis y sin compromiso de auto, casa, negocio, bonos o títulos. Aceptamos cualquier tipo de ID. Un agente con licencia compara más de 20 compañías por ti.'),
];
require APP_ROOT . '/partials/header.php';
?>

<section class="quote-page">
  <div class="hero-bg" aria-hidden="true"><span class="blob b1"></span><span class="blob b2"></span><span class="grid-lines"></span><?= pinwheel_svg('pinwheel page-pin') ?></div>
  <div class="wrap quote-layout">
    <div class="quote-main">
      <h1 class="sr"><?= e(L('Get a free quote', 'Cotiza gratis')) ?></h1>
      <?php $qwMode = 'page'; require APP_ROOT . '/partials/quote-wizard.php'; ?>
    </div>
    <aside class="quote-aside">
      <div class="qa-card">
        <h2><?= e(L('Why quote with us', 'Por qué cotizar con nosotros')) ?></h2>
        <ul>
          <li><?= icon('search') ?><span><b><?= e(L('We compare for you', 'Comparamos por ti')) ?></b><?= e(L(cfg('site.carriers') . '+ insurance companies, one simple form.', 'Más de ' . cfg('site.carriers') . ' compañías, un solo formulario.')) ?></span></li>
          <li><?= icon('id') ?><span><b><?= e(L('Any type of ID', 'Cualquier tipo de ID')) ?></b><?= e(L('No license? No problem.', '¿Sin licencia? No hay problema.')) ?></span></li>
          <li><?= icon('language') ?><span><b><?= e(L('English & Spanish', 'Inglés y español')) ?></b><?= e(L('Service in your language.', 'Atención en tu idioma.')) ?></span></li>
          <li><?= icon('lock') ?><span><b><?= e(L('Private & secure', 'Privado y seguro')) ?></b><?= e(L('ID numbers are encrypted.', 'Los números de ID están cifrados.')) ?></span></li>
        </ul>
      </div>
      <a class="qa-help" href="<?= e(tel_href(cfg('site.phone'))) ?>">
        <span class="qa-help-ic"><?= icon('phone') ?></span>
        <span><small><?= e(L('Prefer to talk?', '¿Prefieres hablar?')) ?></small><b><?= e(cfg('site.phone')) ?></b></span>
      </a>
    </aside>
  </div>
</section>

<?php require APP_ROOT . '/partials/footer.php'; ?>
