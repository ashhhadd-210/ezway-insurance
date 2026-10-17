<?php
require __DIR__ . '/app/bootstrap.php';

$page = [
    'slug'        => 'services',
    'path'        => 'services',
    'title'       => L('Home, business, bonds, title & registration services in Austin', 'Seguro de casa, negocios, bonos, títulos y registro en Austin'),
    'description' => L('Home insurance, general liability, workers comp, bonds, title transfers, license plates, registration and LLC registration at 4 Austin offices. Se habla español.', 'Seguro de casa, responsabilidad general, compensación de trabajadores, bonos, transferencia de títulos, placas, registro y registro de LLC en 4 oficinas de Austin.'),
];

// Extra copy per service for this page.
$intro = [
    'home' => L(
        'Your home is your biggest investment. We compare companies to protect the building, your belongings and your liability, whether you own, rent or rent it out.',
        'Tu casa es tu mayor inversión. Comparamos compañías para proteger la construcción, tus pertenencias y tu responsabilidad, ya seas dueño, inquilino o la rentes.'
    ),
    'business' => L(
        'Contractors and small businesses get the coverage their clients and contracts require: certificates of insurance, workers comp, builders risk and more.',
        'Contratistas y pequeños negocios obtienen la cobertura que piden sus clientes y contratos: certificados de seguro, compensación de trabajadores, riesgos de construcción y más.'
    ),
    'bonds' => L(
        'Need a bond for a title, a license or a contract? We help you get the right bond issued quickly.',
        '¿Necesitas un bono para un título, una licencia o un contrato? Te ayudamos a obtener el bono correcto rápido.'
    ),
    'title' => L(
        'Skip the long lines at the county office. We handle titles, plates and registration for you, explained in your language.',
        'Evita las filas largas del condado. Nosotros tramitamos títulos, placas y registro por ti, y te explicamos todo en tu idioma.'
    ),
    'llc' => L(
        'Starting a business? We register your LLC so you can open a bank account, get insured and start working.',
        '¿Vas a abrir un negocio? Registramos tu LLC para que puedas abrir tu cuenta de banco, asegurarte y empezar a trabajar.'
    ),
];
$services = array_filter(services(), fn ($s) => $s['flow'] === 'details');
require APP_ROOT . '/partials/header.php';
?>

<section class="page-hero">
  <div class="hero-bg" aria-hidden="true"><span class="blob b1"></span><span class="grid-lines"></span><?= pinwheel_svg('pinwheel page-pin') ?></div>
  <div class="wrap page-hero-in">
    <p class="pill reveal"><span class="pill-dot"></span><?= e(L('More than car insurance', 'Más que seguro de auto')) ?></p>
    <h1 class="reveal"><?= e(L('One local team for', 'Un solo equipo local para')) ?> <span class="text-teal"><?= e(L('everything you need.', 'todo lo que necesitas.')) ?></span></h1>
    <p class="lead reveal"><?= e(L(
        'Home and business insurance, bonds, titles, plates and registration, and LLC registration. Every service is available at all 4 of our Austin offices, in English or Spanish.',
        'Seguro de casa y negocio, bonos, títulos, placas, registro y registro de LLC. Todos los servicios en nuestras 4 oficinas de Austin, en inglés o español.'
    )) ?></p>
  </div>
</section>

<nav class="cov-nav" aria-label="<?= e(L('Services', 'Servicios')) ?>" data-covnav>
  <div class="wrap">
    <?php foreach ($services as $key => $s): ?>
      <a href="#<?= e($key) ?>"><?= icon($s['icon']) ?><?= e($s['title']) ?></a>
    <?php endforeach; ?>
    <a href="<?= e(lurl('auto-insurance')) ?>"><?= icon('car') ?><?= e(L('Auto insurance', 'Seguro de auto')) ?></a>
  </div>
</nav>

<?php $n = 0; foreach ($services as $key => $s): $n++; ?>
<section class="section svc-sec<?= $n % 2 === 0 ? ' section-tint' : '' ?>" id="<?= e($key) ?>" aria-labelledby="h-<?= e($key) ?>">
  <div class="wrap svc-sec-in<?= $n % 2 === 0 ? ' is-flip' : '' ?>">
    <div class="svc-sec-copy reveal">
      <span class="svc-big-ic"><?= icon($s['icon']) ?></span>
      <h2 id="h-<?= e($key) ?>"><?= e($s['title']) ?></h2>
      <p class="lead"><?= e($intro[$key] ?? $s['short']) ?></p>
      <div class="split-actions">
        <button class="btn btn-primary" type="button" data-quote="<?= e($key) ?>"><?= e(in_array($key, ['title', 'llc'], true) ? L('Request this service', 'Solicitar este servicio') : L('Get a free quote', 'Cotizar gratis')) ?> <?= icon('arrow-right') ?></button>
        <a class="btn btn-ghost" href="<?= e(tel_href(cfg('site.phone'))) ?>"><?= icon('phone') ?> <?= e(cfg('site.phone')) ?></a>
      </div>
      <?php if ($key === 'business'): ?>
      <p class="svc-note"><?= icon('truck') ?> <?= e(L('Need work trucks insured?', '¿Necesitas asegurar trocas de trabajo?')) ?> <a href="<?= e(lurl('auto-insurance')) ?>#commercial"><?= e(L('See commercial auto', 'Ver auto comercial')) ?></a></p>
      <?php elseif ($key === 'title'): ?>
      <p class="svc-note"><?= icon('pin') ?> <?= e(L('Available at', 'Disponible en')) ?> <a href="<?= e(lurl('contact')) ?>"><?= e(L('all 4 Austin offices', 'nuestras 4 oficinas de Austin')) ?></a></p>
      <?php endif; ?>
    </div>
    <ul class="sub-grid reveal">
      <?php foreach ($s['items'] as [$ic, $label]): ?>
      <li class="sub-item"><span class="sub-ic"><?= icon($ic) ?></span><span><?= e($label) ?></span></li>
      <?php endforeach; ?>
    </ul>
  </div>
</section>
<?php endforeach; ?>

<?php require APP_ROOT . '/partials/cta.php'; ?>
<?php require APP_ROOT . '/partials/footer.php'; ?>
