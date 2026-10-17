<?php
require __DIR__ . '/app/bootstrap.php';

$faqs = [
    [L('Can I get car insurance without a driver license?', '¿Puedo sacar seguro de auto sin licencia de manejo?'),
     L('Yes. No license, no problem: we accept any type of ID, including foreign licenses, passports and matrícula consular. Your agent finds the companies that accept it.', 'Sí. ¿Sin licencia? No hay problema: aceptamos cualquier tipo de ID, como licencias extranjeras, pasaporte y matrícula consular. Tu agente busca las compañías que la aceptan.')],
    [L('Do you speak Spanish?', '¿Hablan español?'),
     L(spanish_enabled() ? 'Yes. Our team speaks English and Spanish, and this whole website, including the quote form, is available in Spanish.' : 'Yes. Our team speaks English and Spanish. Call or visit any of our offices and we\'ll help you in the language you prefer.', 'Sí. Nuestro equipo habla inglés y español, y todo este sitio, incluyendo la cotización, está en español.')],
    [L('Do you only do car insurance?', '¿Solo hacen seguro de auto?'),
     L('No. We also do home, commercial auto, general liability, workers comp and bonds, plus title transfers, plates, registration and LLC registration, at all 4 of our Austin offices.', 'No. También hacemos seguro de casa, auto comercial, responsabilidad general, compensación de trabajadores y bonos, además de transferencias de título, placas, registro y registro de LLC, en nuestras 4 oficinas de Austin.')],
    [L('Does a quote cost anything?', '¿La cotización cuesta algo?'),
     L('No. Quotes are free and there\'s no obligation to buy. We compare ' . cfg('site.carriers') . '+ companies and you decide in your own time.', 'No. Las cotizaciones son gratis y sin compromiso. Comparamos más de ' . cfg('site.carriers') . ' compañías y tú decides con calma.')],
];

$page = [
    'slug'        => 'home',
    'path'        => '',
    'title'       => '',
    'description' => LA(cfg('site.description')),
    'schema'      => [
        ['@type' => 'WebSite', 'name' => cfg('site.name'), 'url' => site_url(), 'inLanguage' => ['en', 'es']],
        ['@type' => 'FAQPage', 'mainEntity' => array_map(fn ($f) => ['@type' => 'Question', 'name' => $f[0], 'acceptedAnswer' => ['@type' => 'Answer', 'text' => $f[1]]], $faqs)],
    ],
];
require APP_ROOT . '/partials/header.php';
?>

<!-- ============ HERO ============ -->
<section class="hero">
  <div class="hero-bg" aria-hidden="true"><span class="blob b1"></span><span class="blob b2"></span><span class="grid-lines"></span><?= pinwheel_svg('pinwheel hero-pin') ?></div>
  <div class="wrap hero-in">
    <div class="hero-copy">
      <p class="pill reveal"><span class="pill-dot"></span><?= e(L('Licensed in Texas · 4 Austin offices · Se habla español', 'Con licencia en Texas · 4 oficinas en Austin · Hablamos inglés y español')) ?></p>
      <h1 class="hero-h reveal">
        <?= e(L('Car, home & business insurance,', 'Seguros de auto, casa y negocio,')) ?>
        <span class="hl"><?= e(L('made EZ.', 'más fácil.')) ?><svg viewBox="0 0 300 20" preserveAspectRatio="none" aria-hidden="true"><path d="M3 15c60-9 140-13 294-6" /></svg></span>
      </h1>
      <p class="lead reveal"><?= e(L(
          'One local team for everything you drive, own and build: ' . cfg('site.carriers') . '+ insurance companies compared for you, plus titles, plates, bonds and LLC registration. Tell us what you need once and we find your best price.',
          'Un solo equipo local para todo lo que manejas, tienes y construyes: comparamos más de ' . cfg('site.carriers') . ' compañías por ti, además de títulos, placas, bonos y registro de LLC. Dinos qué necesitas una sola vez y encontramos tu mejor precio.'
      )) ?></p>

      <form class="zip-form reveal" data-zipform data-service="">
        <label class="zip-field">
          <span class="sr"><?= e(L('ZIP code', 'Código postal')) ?></span>
          <?= icon('pin') ?>
          <input name="zip" inputmode="numeric" maxlength="5" placeholder="<?= e(L('Enter your ZIP code', 'Tu código postal')) ?>" autocomplete="postal-code">
        </label>
        <button class="btn btn-primary btn-lg" type="submit"><?= e(L('Start my quote', 'Cotizar ahora')) ?> <?= icon('arrow-right') ?></button>
      </form>

      <ul class="hero-ticks reveal">
        <li><?= icon('id') ?> <?= e(L('No license? No problem', '¿Sin licencia? No hay problema')) ?></li>
        <li><?= icon('search') ?> <?= e(L(cfg('site.carriers') . '+ companies compared', 'Más de ' . cfg('site.carriers') . ' compañías')) ?></li>
        <li><?= icon('check') ?> <?= e(L('Free, no obligation', 'Gratis y sin compromiso')) ?></li>
      </ul>
    </div>

    <div class="hero-art reveal" data-parallax>
      <svg class="scene" viewBox="0 0 560 520" role="img" aria-label="<?= e(L('Illustration of a home, a business and a car protected by one shield', 'Ilustración de una casa, un negocio y un auto protegidos por un mismo escudo')) ?>">
        <defs>
          <linearGradient id="sky" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#0D1640"/><stop offset="1" stop-color="#1B2A68"/></linearGradient>
          <radialGradient id="sun" cx=".5" cy=".5" r=".5"><stop offset="0" stop-color="#FFD48A"/><stop offset=".55" stop-color="#FFB547" stop-opacity=".55"/><stop offset="1" stop-color="#FFB547" stop-opacity="0"/></radialGradient>
          <linearGradient id="beam" x1="0" y1="0" x2="1" y2="0"><stop offset="0" stop-color="#FFE1A6" stop-opacity=".75"/><stop offset="1" stop-color="#FFE1A6" stop-opacity="0"/></linearGradient>
          <linearGradient id="carBody" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#FFC567"/><stop offset="1" stop-color="#F29A2E"/></linearGradient>
          <linearGradient id="glass" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="#CFE9F5"/><stop offset="1" stop-color="#7FB7D6"/></linearGradient>
          <linearGradient id="shieldG" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#7CC9E6" stop-opacity=".35"/><stop offset="1" stop-color="#7CC9E6" stop-opacity=".04"/></linearGradient>
          <clipPath id="sceneClip"><rect width="560" height="520" rx="36"/></clipPath>
        </defs>
        <g clip-path="url(#sceneClip)">
          <rect width="560" height="520" fill="url(#sky)"/>
          <g class="stars" fill="#fff">
            <circle cx="60" cy="70" r="1.6"/><circle cx="140" cy="40" r="1.2"/><circle cx="220" cy="92" r="1.4"/><circle cx="300" cy="36" r="1.1"/>
            <circle cx="350" cy="120" r="1.3"/><circle cx="500" cy="60" r="1.5"/><circle cx="96" cy="150" r="1"/><circle cx="520" cy="170" r="1.2"/>
          </g>
          <circle cx="430" cy="128" r="120" fill="url(#sun)"/>
          <circle cx="430" cy="128" r="40" fill="#FFD48A"/>
          <g fill="#142259">
            <rect x="20" y="250" width="46" height="150"/><rect x="72" y="214" width="38" height="186"/><rect x="116" y="262" width="54" height="138"/>
            <rect x="378" y="236" width="42" height="164"/><rect x="426" y="200" width="50" height="200"/><rect x="482" y="256" width="60" height="144"/>
          </g>
          <g class="windows" fill="#FFD48A">
            <rect x="30" y="266" width="8" height="10"/><rect x="48" y="290" width="8" height="10"/><rect x="82" y="232" width="8" height="10"/><rect x="94" y="268" width="8" height="10"/>
            <rect x="130" y="280" width="8" height="10"/><rect x="152" y="306" width="8" height="10"/><rect x="390" y="252" width="8" height="10"/><rect x="404" y="290" width="8" height="10"/>
            <rect x="438" y="220" width="8" height="10"/><rect x="458" y="258" width="8" height="10"/><rect x="496" y="276" width="8" height="10"/><rect x="520" y="304" width="8" height="10"/>
          </g>
          <path d="M0 360c80-40 160-46 250-24s170 20 310-18v202H0Z" fill="#1C2C70"/>
          <path d="M0 392c110-26 220-22 320-4s170 10 240-8v140H0Z" fill="#26399A"/>
          <g class="shield-big">
            <path d="M280 96 168 138v86c0 74 47 126 112 148 65-22 112-74 112-148v-86L280 96Z" fill="url(#shieldG)" stroke="#8ED3EC" stroke-opacity=".7" stroke-width="2.5"/>
            <path class="shield-check" d="m240 214 28 28 54-56" fill="none" stroke="#8ED3EC" stroke-width="10" stroke-linecap="round" stroke-linejoin="round"/>
          </g>
          <g class="house" transform="translate(30 300)">
            <rect x="106" y="10" width="15" height="28" fill="#B84A3C"/>
            <rect x="12" y="40" width="126" height="66" rx="2" fill="#F3EEE4"/>
            <path d="M0 44 75 0l75 44Z" fill="#F26D5B"/>
            <path d="M75 0 150 44h-14L75 8Z" fill="#000" opacity=".12"/>
            <g class="win" fill="#FFD48A"><rect x="24" y="56" width="28" height="24" rx="3"/><rect x="98" y="56" width="28" height="24" rx="3"/></g>
            <path d="M38 56v24M24 68h28M112 56v24M98 68h28" stroke="#E5B25C" stroke-width="2"/>
            <rect x="64" y="66" width="22" height="40" rx="3" fill="#2B3990"/>
            <circle cx="81" cy="87" r="2" fill="#FFD48A"/>
            <path d="M75 20v10" stroke="#fff" stroke-opacity=".5" stroke-width="2"/>
          </g>
          <g class="shop" transform="translate(392 284)">
            <rect x="0" y="22" width="150" height="100" rx="2" fill="#E8EEFB"/>
            <rect x="-4" y="12" width="158" height="16" rx="3" fill="#2B3990"/>
            <rect x="36" y="30" width="78" height="18" rx="4" fill="#FFB547"/>
            <text x="75" y="43" text-anchor="middle" font-family="Figtree, Arial, sans-serif" font-size="11" font-weight="800" fill="#0D1640"><?= e(L('OPEN', 'ABIERTO')) ?></text>
            <g class="awning">
              <rect x="0" y="54" width="150" height="12" fill="#fff"/>
              <path d="M0 54h15v12H0zM30 54h15v12H30zM60 54h15v12H60zM90 54h15v12H90zM120 54h15v12h-15z" fill="#FFB547"/>
              <path d="M0 66a7.5 7.5 0 0 0 15 0 7.5 7.5 0 0 0 15 0 7.5 7.5 0 0 0 15 0 7.5 7.5 0 0 0 15 0 7.5 7.5 0 0 0 15 0 7.5 7.5 0 0 0 15 0 7.5 7.5 0 0 0 15 0 7.5 7.5 0 0 0 15 0 7.5 7.5 0 0 0 15 0 7.5 7.5 0 0 0 15 0Z" fill="#FFB547"/>
            </g>
            <rect x="10" y="78" width="58" height="36" rx="3" fill="#CFE9F5"/>
            <path d="M18 104 32 86M30 108l14-18" stroke="#fff" stroke-opacity=".7" stroke-width="3" stroke-linecap="round"/>
            <rect x="80" y="76" width="28" height="46" rx="3" fill="#9CC9E8"/>
            <rect x="116" y="78" width="26" height="36" rx="3" fill="#CFE9F5"/>
          </g>
          <rect y="414" width="560" height="106" fill="#0A1233"/>
          <rect y="414" width="560" height="5" fill="#1E2C66"/>
          <path class="lane" d="M-40 470H600" stroke="#FFD48A" stroke-width="5" stroke-dasharray="34 26" stroke-linecap="round"/>
          <g class="car" transform="translate(176 342) scale(.8)">
            <path class="beam" d="M268 58 420 36v70L268 74Z" fill="url(#beam)"/>
            <g class="speed" stroke="#8ED3EC" stroke-width="3" stroke-linecap="round" opacity=".7">
              <path d="M-50 40h34"/><path d="M-70 60h48"/><path d="M-40 78h22"/>
            </g>
            <ellipse cx="141" cy="98" rx="138" ry="9" fill="#000" opacity=".35"/>
            <g class="car-body">
              <!-- Body drawn facing left, mirrored so the long hood and sloped windshield face the direction of travel -->
              <g transform="translate(282 0) scale(-1 1)">
                <path d="M10 80c0-14 8-20 22-22l46-6c14-18 34-30 62-32h46c20 0 34 10 48 28l18 6c14 4 20 12 20 24v8c0 4-3 6-7 6H14c-3 0-4-2-4-6Z" fill="url(#carBody)"/>
                <path d="M92 52c10-14 24-22 44-22h14v22Zm68-22h24c14 0 24 8 34 22h-58Z" fill="url(#glass)"/>
                <path d="M102 50c6-8 14-14 26-16" stroke="#fff" stroke-opacity=".7" stroke-width="3" stroke-linecap="round" fill="none"/>
                <path d="M155 52v34" stroke="#D9821F" stroke-width="2"/>
                <rect x="128" y="60" width="14" height="4" rx="2" fill="#D9821F"/><rect x="170" y="60" width="14" height="4" rx="2" fill="#D9821F"/>
              </g>
              <path d="M12 72h258" stroke="#fff" stroke-opacity=".35" stroke-width="2"/>
              <rect x="256" y="62" width="13" height="8" rx="3" fill="#FFF3D1"/>
              <rect x="11" y="69" width="7" height="8" rx="2.5" fill="#E5484D"/>
            </g>
            <g transform="translate(70 92)"><g class="wheel"><circle r="21" fill="#0D1640"/><circle r="11" fill="#9FB3C8"/><path d="M0-11v22M-11 0h22" stroke="#0D1640" stroke-width="3"/><circle r="3.5" fill="#0D1640"/></g></g>
            <g transform="translate(214 92)"><g class="wheel"><circle r="21" fill="#0D1640"/><circle r="11" fill="#9FB3C8"/><path d="M0-11v22M-11 0h22" stroke="#0D1640" stroke-width="3"/><circle r="3.5" fill="#0D1640"/></g></g>
          </g>
        </g>
      </svg>

      <div class="chip chip-1" data-depth="18">
        <span class="chip-ic teal"><?= icon('home') ?></span>
        <span><small><?= e(L('Home insurance', 'Seguro de casa')) ?></small><b><?= e(L('Protected', 'Protegida')) ?></b></span>
        <span class="chip-ok"><?= icon('check') ?></span>
      </div>
      <div class="chip chip-2" data-depth="28">
        <span class="chip-ic teal"><?= icon('briefcase') ?></span>
        <span><small><?= e(L('Business & commercial auto', 'Negocio y auto comercial')) ?></small><b><?= e(L('Covered', 'Asegurado')) ?></b></span>
        <span class="chip-ok"><?= icon('check') ?></span>
      </div>
      <div class="chip chip-3" data-depth="12">
        <span class="chip-ic amber"><?= icon('sparkle') ?></span>
        <span><small><?= e(L('We also handle', 'También hacemos')) ?></small>
          <b class="rot" aria-label="<?= e(L('Title transfers, license plates, bonds, LLC registration', 'Títulos, placas, bonos, registro de LLC')) ?>"><span class="rot-in" aria-hidden="true">
            <span><?= e(L('Title transfers', 'Transferencia de títulos')) ?></span><span><?= e(L('License plates', 'Placas y registro')) ?></span><span><?= e(L('Bonds', 'Bonos')) ?></span><span><?= e(L('LLC registration', 'Registro de LLC')) ?></span><span><?= e(L('Title transfers', 'Transferencia de títulos')) ?></span>
          </span></b>
        </span>
      </div>
    </div>
  </div>
</section>

<!-- ============ SERVICES MARQUEE ============ -->
<div class="marquee" aria-hidden="true">
  <div class="marquee-track">
    <?php
    $marquee = [['car', L('Personal auto', 'Auto personal')], ['truck', L('Commercial auto', 'Auto comercial')], ['home', L('Home', 'Casa')],
        ['shield', L('General liability', 'Responsabilidad general')], ['hardhat', L('Workers comp', 'Compensación de trabajadores')],
        ['seal', L('Bonds', 'Bonos')], ['doc', L('Title transfers', 'Transferencia de títulos')], ['plate', L('License plates', 'Placas')],
        ['building', L('LLC registration', 'Registro de LLC')]];
    for ($r = 0; $r < 2; $r++): foreach ($marquee as [$ic, $label]): ?>
      <span><?= icon($ic) ?><?= e($label) ?></span>
    <?php endforeach; endfor; ?>
  </div>
</div>

<!-- ============ SERVICES ============ -->
<section class="section" aria-labelledby="svcTitle">
  <div class="wrap">
    <header class="section-head reveal">
      <p class="eyebrow"><?= e(L('Everything under one roof', 'Todo en un solo lugar')) ?></p>
      <h2 id="svcTitle"><?= e(L('Insurance and auto services, done the EZ way', 'Seguros y servicios de auto, de la forma más fácil')) ?></h2>
      <p><?= e(L('From your first car to your growing business, one local team takes care of it all.', 'Desde tu primer auto hasta tu negocio, un solo equipo local se encarga de todo.')) ?></p>
    </header>
    <div class="svc-grid">
      <?php foreach (services() as $key => $s): ?>
      <article class="svc-card reveal">
        <span class="svc-ic"><?= icon($s['icon']) ?></span>
        <h3><?= e($s['title']) ?></h3>
        <p><?= e($s['short']) ?></p>
        <div class="svc-actions">
          <button class="btn btn-primary btn-sm" type="button" data-quote="<?= e($key) ?>"><?= e(L('Get a quote', 'Cotizar')) ?></button>
          <a class="cov-more" href="<?= e(lurl($s['page'])) ?>"><?= e(L('Learn more', 'Ver más')) ?> <?= icon('arrow-right') ?></a>
        </div>
      </article>
      <?php endforeach; ?>
      <a class="svc-card svc-card-call reveal" href="<?= e(tel_href(cfg('site.phone'))) ?>">
        <span class="svc-ic"><?= icon('phone') ?></span>
        <h3><?= e(L('Not sure what you need?', '¿No sabes qué necesitas?')) ?></h3>
        <p><?= e(L('Call or stop by any of our 4 Austin offices. We\'ll point you in the right direction, in English or Spanish.', 'Llámanos o visita cualquiera de nuestras 4 oficinas en Austin. Te orientamos en inglés o en español.')) ?></p>
        <span class="cov-more"><?= e(cfg('site.phone')) ?> <?= icon('arrow-right') ?></span>
      </a>
    </div>
  </div>
</section>

<!-- ============ ANY ID ============ -->
<section class="section section-top0" aria-labelledby="idTitle">
  <div class="wrap">
    <div class="id-banner reveal">
      <div class="id-art" aria-hidden="true">
        <div class="id-card id-c1"><span class="id-photo"><?= icon('user') ?></span><span class="id-lines"><i></i><i></i><i></i></span><b><?= e(L('Driver license', 'Licencia')) ?></b></div>
        <div class="id-card id-c2"><span class="id-photo"><?= icon('globe') ?></span><span class="id-lines"><i></i><i></i><i></i></span><b><?= e(L('Passport', 'Pasaporte')) ?></b></div>
        <div class="id-card id-c3"><span class="id-photo"><?= icon('id') ?></span><span class="id-lines"><i></i><i></i><i></i></span><b>Matrícula</b></div>
        <span class="id-check"><?= icon('check') ?></span>
      </div>
      <div class="id-copy">
        <p class="eyebrow"><?= e(L('No license? No problem', '¿Sin licencia? No hay problema')) ?></p>
        <h2 id="idTitle"><?= e(L('We accept any type of ID', 'Aceptamos cualquier tipo de ID')) ?></h2>
        <p><?= e(L(
            'US license, foreign license, passport, matrícula consular or state ID. Everyone deserves to drive protected, and we work with the companies that make it possible.',
            'Licencia de EE. UU., licencia extranjera, pasaporte, matrícula consular o ID del estado. Todos merecen manejar protegidos, y trabajamos con las compañías que lo hacen posible.'
        )) ?></p>
        <ul class="id-list">
          <li><?= icon('check') ?><?= e(L('Personal and commercial auto', 'Auto personal y comercial')) ?></li>
          <li><?= icon('check') ?><?= e(L('Foreign licenses welcome', 'Licencias extranjeras bienvenidas')) ?></li>
          <li><?= icon('check') ?><?= e(L('Service in English and Spanish', 'Atención en inglés y español')) ?></li>
        </ul>
        <button class="btn btn-accent" type="button" data-quote="personal_auto"><?= e(L('Get my auto quote', 'Cotizar mi auto')) ?> <?= icon('arrow-right') ?></button>
      </div>
    </div>
  </div>
</section>

<!-- ============ HOW IT WORKS ============ -->
<section class="section section-tint" aria-labelledby="howTitle">
  <div class="wrap">
    <header class="section-head reveal">
      <p class="eyebrow"><?= e(L('How it works', 'Cómo funciona')) ?></p>
      <h2 id="howTitle"><?= e(L('From "I need insurance" to options in three steps', 'De "necesito seguro" a tener opciones en tres pasos')) ?></h2>
      <p><?= e(L('No long phone calls, no repeating yourself. Tell us once and your agent does the shopping.', 'Sin llamadas largas ni repetir tus datos. Dinos una vez y tu agente compara por ti.')) ?></p>
    </header>
    <ol class="steps">
      <li class="step-card reveal">
        <span class="step-n">01</span>
        <span class="step-ic"><?= icon('user') ?></span>
        <h3><?= e(L('Tell us what you need', 'Dinos qué necesitas')) ?></h3>
        <p><?= e(L('Pick a service and add your details. For auto, add every driver and car with one tap.', 'Elige un servicio y agrega tus datos. Para auto, agrega a todos los conductores y autos con un toque.')) ?></p>
      </li>
      <li class="step-card reveal">
        <span class="step-n">02</span>
        <span class="step-ic"><?= icon('search') ?></span>
        <h3><?= e(L('We compare ' . cfg('site.carriers') . '+ companies', 'Comparamos más de ' . cfg('site.carriers') . ' compañías')) ?></h3>
        <p><?= e(L('Your licensed agent shops your coverage across the companies we work with.', 'Tu agente con licencia compara tu cobertura entre las compañías con las que trabajamos.')) ?></p>
      </li>
      <li class="step-card reveal">
        <span class="step-n">03</span>
        <span class="step-ic"><?= icon('shield') ?></span>
        <h3><?= e(L('You choose and save', 'Tú eliges y ahorras')) ?></h3>
        <p><?= e(L('We reach out with your best options, usually within one business day.', 'Te contactamos con tus mejores opciones, normalmente en un día hábil.')) ?></p>
      </li>
    </ol>
  </div>
</section>

<!-- ============ PRODUCT SHOWCASE ============ -->
<section class="section" aria-labelledby="showTitle">
  <div class="wrap show">
    <div class="show-copy reveal">
      <p class="eyebrow"><?= e(L('Made for real families and businesses', 'Hecho para familias y negocios reales')) ?></p>
      <h2 id="showTitle"><?= e(L('One form for every driver and every car in the driveway.', 'Un solo formulario para todos tus conductores y autos.')) ?></h2>
      <p><?= e(L('Most quote forms make you start over for every driver. Ours doesn\'t. Add everyone at once and choose coverage for each vehicle.', 'Otros formularios te hacen empezar de nuevo por cada conductor. El nuestro no. Agrega a todos de una vez y elige la cobertura de cada vehículo.')) ?></p>
      <ul class="feat-list">
        <li><span><?= icon('plus') ?></span><div><b><?= e(L('Add drivers with one tap', 'Agrega conductores con un toque')) ?></b><?= e(L('Spouse, kids, employees: with a US license, foreign license or any ID.', 'Pareja, hijos, empleados: con licencia de EE. UU., extranjera o cualquier ID.')) ?></div></li>
        <li><span><?= icon('scan') ?></span><div><b><?= e(L('VIN auto-detect', 'Detecta tu VIN')) ?></b><?= e(L('Type the VIN and we show the year, make and model instantly.', 'Escribe el VIN y te mostramos el año, la marca y el modelo al instante.')) ?></div></li>
        <li><span><?= icon('truck') ?></span><div><b><?= e(L('Personal or commercial', 'Personal o comercial')) ?></b><?= e(L('Insure work trucks under your company name with higher limits.', 'Asegura trocas de trabajo a nombre de tu compañía con límites más altos.')) ?></div></li>
        <li><span><?= icon('lock') ?></span><div><b><?= e(L('Private by design', 'Privado y seguro')) ?></b><?= e(L('ID numbers are encrypted and seen only by your agent.', 'Los números de ID están cifrados y solo los ve tu agente.')) ?></div></li>
      </ul>
      <button class="btn btn-primary" type="button" data-quote="personal_auto"><?= e(L('Try the quote form', 'Probar la cotización')) ?> <?= icon('arrow-right') ?></button>
    </div>

    <div class="mock reveal" data-mock aria-hidden="true">
      <div class="mock-win">
        <div class="mock-bar"><i></i><i></i><i></i><span><?= e(L('Vehicles · Step 5 of 7', 'Vehículos · Paso 5 de 7')) ?></span></div>
        <div class="mock-body">
          <div class="mock-card">
            <div class="mock-head"><span class="mock-av"><?= icon('car') ?></span><b data-mock-title><?= e(L('Vehicle 1', 'Vehículo 1')) ?></b></div>
            <div class="mock-input"><?= icon('scan') ?><span data-mock-vin></span><i class="caret"></i></div>
            <div class="mock-result" data-mock-result>
              <span class="mock-ok"><?= icon('check') ?></span>
              <div><b>2020 Toyota Camry SE</b><small><?= e(L('Sedan · Gasoline · FWD', 'Sedán · Gasolina · FWD')) ?></small></div>
            </div>
            <div class="mock-chips">
              <span class="on" data-mock-chip><?= icon('shield-plus') ?><?= e(L('Full coverage', 'Cobertura completa')) ?></span>
              <span data-mock-chip><?= icon('wrench') ?><?= e(L('Roadside', 'Asistencia')) ?></span>
              <span data-mock-chip><?= icon('key') ?><?= e(L('Rental', 'Auto de renta')) ?></span>
            </div>
          </div>
          <div class="mock-add"><?= icon('plus') ?> <?= e(L('Add another vehicle', 'Agregar otro vehículo')) ?></div>
        </div>
      </div>
      <div class="mock-float">
        <span class="mock-avs"><i>A</i><i>R</i><i>K</i></span>
        <div><b><?= e(L('3 drivers', '3 conductores')) ?></b><small><?= e(L('2 vehicles · Ready to send', '2 vehículos · Lista para enviar')) ?></small></div>
      </div>
    </div>
  </div>
</section>

<!-- ============ STATS ============ -->
<section class="stats" aria-label="<?= e(L('At a glance', 'En resumen')) ?>">
  <div class="wrap stats-grid">
    <div class="stat reveal"><b><span data-count="<?= (int) cfg('site.years') ?>">0</span>+</b><span><?= e(L('years serving Austin', 'años sirviendo a Austin')) ?></span></div>
    <div class="stat reveal"><b><span data-count="<?= (int) cfg('site.carriers') ?>">0</span>+</b><span><?= e(L('insurance companies compared', 'compañías de seguros comparadas')) ?></span></div>
    <div class="stat reveal"><b><span data-count="<?= count(offices()) ?>">0</span></b><span><?= e(L('offices across Austin', 'oficinas en Austin')) ?></span></div>
    <div class="stat reveal"><b><span data-count="2">0</span></b><span><?= e(L('languages: English & Spanish', 'idiomas: inglés y español')) ?></span></div>
  </div>
</section>

<!-- ============ LOCATIONS ============ -->
<section class="section" aria-labelledby="locTitle">
  <div class="wrap">
    <header class="section-head reveal">
      <p class="eyebrow"><?= e(L('Visit us', 'Visítanos')) ?></p>
      <h2 id="locTitle"><?= e(L('4 offices across Austin, open 7 days a week', '4 oficinas en Austin, abiertas los 7 días')) ?></h2>
      <p><?= e(L('Every office offers every service: insurance, titles, plates, bonds and LLC registration.', 'Cada oficina ofrece todos los servicios: seguros, títulos, placas, bonos y registro de LLC.')) ?></p>
    </header>
    <div class="loc-grid">
      <?php foreach (offices() as $o): ?>
      <article class="loc-card reveal" data-office-hours="<?= e(office_hours_js($o)) ?>">
        <div class="loc-top">
          <span class="loc-ic"><?= icon('pin') ?></span>
          <span class="open-badge" data-open-badge hidden></span>
        </div>
        <h3><?= e($o['name']) ?></h3>
        <p class="loc-addr"><?= e($o['street']) ?><br><?= e($o['city'] . ', ' . $o['state'] . ' ' . $o['zip']) ?></p>
        <dl class="loc-hours">
          <?php foreach (office_hours_rows($o) as [$d, $h]): ?><div><dt><?= e($d) ?></dt><dd><?= e($h) ?></dd></div><?php endforeach; ?>
        </dl>
        <a class="cov-more" href="<?= e(maps_link($o)) ?>" target="_blank" rel="noopener"><?= icon('directions') ?> <?= e(L('Get directions', 'Cómo llegar')) ?></a>
      </article>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ============ FAQ ============ -->
<section class="section section-tint" aria-labelledby="faqTitle">
  <div class="wrap faq-wrap">
    <header class="section-head reveal">
      <p class="eyebrow"><?= e(L('Questions', 'Preguntas')) ?></p>
      <h2 id="faqTitle"><?= e(L('Good to know', 'Bueno saber')) ?></h2>
      <p><?= e(L('More answers on our', 'Más respuestas en nuestra página de')) ?> <a href="<?= e(lurl('auto-insurance')) ?>#faq"><?= e(L('auto insurance page', 'seguro de auto')) ?></a>, <?= e(L('or just', 'o simplemente')) ?> <a href="<?= e(lurl('contact')) ?>"><?= e(L('ask us', 'pregúntanos')) ?></a>.</p>
    </header>
    <div class="faq reveal">
      <?php foreach ($faqs as [$q, $a]): ?>
      <details><summary><?= e($q) ?><?= icon('chevron-down') ?></summary><div><p><?= e($a) ?></p></div></details>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<?php require APP_ROOT . '/partials/cta.php'; ?>
<?php require APP_ROOT . '/partials/footer.php'; ?>
