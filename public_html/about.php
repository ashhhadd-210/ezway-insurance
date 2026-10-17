<?php
require __DIR__ . '/app/bootstrap.php';

$page = [
    'slug'        => 'about',
    'path'        => 'about',
    'title'       => L('About us', 'Nosotros'),
    'description' => L(
        'EZ Way Insurance: a licensed Texas agency with 4 offices in Austin, 10+ years of experience and 20+ insurance companies. We speak English and Spanish and accept any type of ID.',
        'EZ Way Insurance: agencia con licencia en Texas, 4 oficinas en Austin, más de 10 años de experiencia y más de 20 compañías de seguros. Hablamos inglés y español y aceptamos cualquier tipo de ID.'
    ),
];
$years = (int) cfg('site.years');
$carriers = (int) cfg('site.carriers');
require APP_ROOT . '/partials/header.php';
?>

<section class="page-hero">
  <div class="hero-bg" aria-hidden="true"><span class="blob b1"></span><span class="grid-lines"></span><?= pinwheel_svg('pinwheel page-pin') ?></div>
  <div class="wrap page-hero-in">
    <p class="pill reveal"><span class="pill-dot"></span><?= e(L('About us', 'Nosotros')) ?></p>
    <h1 class="reveal"><?= e(L('Austin\'s easy way to get insured.', 'La forma fácil de asegurarte en Austin.')) ?> <span class="text-teal"><?= e(L('For ' . $years . '+ years.', 'Desde hace más de ' . $years . ' años.')) ?></span></h1>
    <p class="lead reveal"><?= e(L(
        'We started EZ Way so buying insurance wouldn\'t feel complicated. You tell us what you need once, and we do the comparing, the explaining and the follow-up, in English or Spanish.',
        'Creamos EZ Way para que comprar seguro no fuera complicado. Nos dices qué necesitas una vez, y nosotros comparamos, te explicamos y damos seguimiento, en inglés o en español.'
    )) ?></p>
  </div>
</section>

<section class="section">
  <div class="wrap split">
    <div class="reveal">
      <p class="eyebrow"><?= e(L('Our story', 'Nuestra historia')) ?></p>
      <h2><?= e(L('Real people, real advice, right here in Austin.', 'Gente real, consejos reales, aquí en Austin.')) ?></h2>
      <p><?= e(L(
          'For more than ' . $years . ' years, families, drivers and business owners across Austin have trusted us with their insurance and their paperwork. Today we serve them from 4 offices: Cameron Rd, William Cannon, North Lamar and Howard Ln.',
          'Por más de ' . $years . ' años, familias, conductores y dueños de negocios en Austin nos han confiado sus seguros y sus trámites. Hoy los atendemos en 4 oficinas: Cameron Rd, William Cannon, North Lamar y Howard Ln.'
      )) ?></p>
      <p><?= e(L(
          'As an independent agency we work with ' . $carriers . '+ insurance companies, so we can shop around and find what fits your budget, whether it\'s your first car, a work truck, your home or your business. No license? No problem: we accept any type of ID.',
          'Como agencia independiente trabajamos con más de ' . $carriers . ' compañías de seguros, así que comparamos y encontramos lo que se ajusta a tu presupuesto: tu primer auto, una troca de trabajo, tu casa o tu negocio. ¿Sin licencia? No hay problema: aceptamos cualquier tipo de ID.'
      )) ?></p>
      <div class="split-actions">
        <button class="btn btn-primary" type="button" data-quote><?= e(L('Start your quote', 'Empieza tu cotización')) ?> <?= icon('arrow-right') ?></button>
        <a class="btn btn-ghost" href="<?= e(lurl('contact')) ?>"><?= icon('pin') ?> <?= e(L('Find an office', 'Encuentra una oficina')) ?></a>
      </div>
    </div>
    <div class="about-art reveal" aria-hidden="true">
      <svg viewBox="0 0 480 420">
        <defs><linearGradient id="abSky" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#E4EEFB"/><stop offset="1" stop-color="#F1F4FA"/></linearGradient></defs>
        <rect x="0" y="0" width="480" height="420" rx="32" fill="url(#abSky)"/>
        <circle cx="390" cy="86" r="34" fill="#FFD48A"/>
        <g class="cloud"><ellipse cx="110" cy="80" rx="46" ry="16" fill="#fff"/><ellipse cx="136" cy="70" rx="28" ry="18" fill="#fff"/></g>
        <!-- building -->
        <rect x="90" y="150" width="300" height="200" rx="10" fill="#fff" stroke="#0D1640" stroke-width="3"/>
        <rect x="90" y="150" width="300" height="34" rx="10" fill="#0D1640"/>
        <path d="M90 184h300l-14 36H104Z" fill="#2B3990"/>
        <path d="M104 220c10 0 10 10 20 10s10-10 20-10 10 10 20 10 10-10 20-10 10 10 20 10 10-10 20-10 10 10 20 10 10-10 20-10 10 10 20 10 10-10 20-10 10 10 20 10 10-10 20-10" fill="none" stroke="#2B3990" stroke-width="6"/>
        <rect x="120" y="246" width="90" height="74" rx="6" fill="#CFE9F5" stroke="#0D1640" stroke-width="3"/>
        <path d="M165 246v74M120 283h90" stroke="#0D1640" stroke-width="3"/>
        <rect x="244" y="240" width="62" height="110" rx="6" fill="#FFB547" stroke="#0D1640" stroke-width="3"/>
        <circle cx="294" cy="298" r="4" fill="#0D1640"/>
        <rect x="322" y="246" width="50" height="74" rx="6" fill="#CFE9F5" stroke="#0D1640" stroke-width="3"/>
        <!-- sign -->
        <g class="sign">
          <path d="M240 96 206 109v28c0 24 15 40 34 47 19-7 34-23 34-47v-28L240 96Z" fill="#2B3990" stroke="#0D1640" stroke-width="3"/>
          <path d="m226 138 10 10 18-20" fill="none" stroke="#fff" stroke-width="5" stroke-linecap="round" stroke-linejoin="round"/>
          <path d="M240 184v-4" stroke="#0D1640" stroke-width="3"/>
        </g>
        <!-- ground & car -->
        <rect x="0" y="350" width="480" height="70" fill="#0D1640"/>
        <path d="M0 386h480" stroke="#FFD48A" stroke-width="4" stroke-dasharray="26 20"/>
        <g class="about-car" transform="translate(40 312)">
          <g transform="translate(177 0) scale(-1 1)">
            <path d="M6 50c0-9 5-13 14-14l29-4c9-11 21-19 39-20h29c13 0 21 6 30 18l11 4c9 2 13 8 13 15v5c0 3-2 4-4 4H9c-2 0-3-1-3-4Z" fill="#FFB547"/>
            <path d="M58 33c6-9 15-14 28-14h9v14Zm43-14h15c9 0 15 5 21 14h-36Z" fill="#CFE9F5"/>
          </g>
          <rect x="160" y="40" width="9" height="6" rx="2" fill="#FFF3D1"/>
          <circle cx="44" cy="58" r="13" fill="#0D1640"/><circle cx="44" cy="58" r="6" fill="#9FB3C8"/>
          <circle cx="134" cy="58" r="13" fill="#0D1640"/><circle cx="134" cy="58" r="6" fill="#9FB3C8"/>
        </g>
        <g fill="#26399A"><circle cx="420" cy="330" r="22"/><circle cx="440" cy="316" r="18"/><rect x="426" y="330" width="6" height="24" fill="#0D1640"/></g>
      </svg>
    </div>
  </div>
</section>

<section class="stats" aria-label="<?= e(L('At a glance', 'En resumen')) ?>">
  <div class="wrap stats-grid">
    <div class="stat reveal"><b><span data-count="<?= $years ?>">0</span>+</b><span><?= e(L('years in business', 'años de experiencia')) ?></span></div>
    <div class="stat reveal"><b><span data-count="<?= $carriers ?>">0</span>+</b><span><?= e(L('insurance companies', 'compañías de seguros')) ?></span></div>
    <div class="stat reveal"><b><span data-count="<?= count(offices()) ?>">0</span></b><span><?= e(L('Austin offices', 'oficinas en Austin')) ?></span></div>
    <div class="stat reveal"><b><span data-count="7">0</span></b><span><?= e(L('services under one roof', 'servicios en un solo lugar')) ?></span></div>
  </div>
</section>

<section class="section section-tint" aria-labelledby="valTitle">
  <div class="wrap">
    <header class="section-head reveal">
      <p class="eyebrow"><?= e(L('What we stand for', 'Lo que nos distingue')) ?></p>
      <h2 id="valTitle"><?= e(L('The way insurance should feel', 'Así debería sentirse el seguro')) ?></h2>
    </header>
    <div class="val-grid">
      <article class="val reveal"><span class="val-ic"><?= icon('search') ?></span><h3><?= e(L('We shop, you save', 'Comparamos, tú ahorras')) ?></h3><p><?= e(L('We compare ' . $carriers . '+ companies so you don\'t have to fill out form after form.', 'Comparamos más de ' . $carriers . ' compañías para que no tengas que llenar formulario tras formulario.')) ?></p></article>
      <article class="val reveal"><span class="val-ic"><?= icon('language') ?></span><h3><?= e(L('Your language', 'Tu idioma')) ?></h3><p><?= e(L('We explain everything in English or Spanish, with no jargon and no surprises.', 'Te explicamos todo en inglés o en español, sin palabras complicadas ni sorpresas.')) ?></p></article>
      <article class="val reveal"><span class="val-ic"><?= icon('id') ?></span><h3><?= e(L('Every driver welcome', 'Todos son bienvenidos')) ?></h3><p><?= e(L('US license, foreign license, passport or matrícula. We\'ll find you a fit.', 'Licencia de EE. UU., extranjera, pasaporte o matrícula. Te encontramos una opción.')) ?></p></article>
      <article class="val reveal"><span class="val-ic"><?= icon('shield') ?></span><h3><?= e(L('Here when it counts', 'Aquí cuando más importa')) ?></h3><p><?= e(L('When something happens, you call a local person who knows you, not a call center.', 'Cuando algo pasa, llamas a una persona local que te conoce, no a un call center.')) ?></p></article>
    </div>
  </div>
</section>

<section class="section" aria-labelledby="flowTitle">
  <div class="wrap">
    <header class="section-head reveal">
      <p class="eyebrow"><?= e(L('How we work', 'Cómo trabajamos')) ?></p>
      <h2 id="flowTitle"><?= e(L('What happens after you request a quote', 'Qué pasa después de pedir tu cotización')) ?></h2>
    </header>
    <ol class="timeline">
      <li class="reveal"><span class="tl-dot"><?= icon('doc') ?></span><div><h3><?= e(L('You send your request', 'Envías tu solicitud')) ?></h3><p><?= e(L('Online in about three minutes, by phone, or at any of our offices.', 'En línea en unos tres minutos, por teléfono o en cualquiera de nuestras oficinas.')) ?></p></div></li>
      <li class="reveal"><span class="tl-dot"><?= icon('search') ?></span><div><h3><?= e(L('We compare companies', 'Comparamos compañías')) ?></h3><p><?= e(L('Your agent checks prices and coverage across ' . $carriers . '+ insurance companies.', 'Tu agente revisa precios y coberturas en más de ' . $carriers . ' compañías.')) ?></p></div></li>
      <li class="reveal"><span class="tl-dot"><?= icon('phone') ?></span><div><h3><?= e(L('We walk you through it', 'Te explicamos todo')) ?></h3><p><?= e(L('We reach out by phone, text or email, whichever you prefer, and explain your options.', 'Te contactamos por teléfono, mensaje o correo, como prefieras, y te explicamos tus opciones.')) ?></p></div></li>
      <li class="reveal"><span class="tl-dot"><?= icon('shield') ?></span><div><h3><?= e(L('You\'re covered, and we stay close', 'Quedas protegido, y seguimos cerca')) ?></h3><p><?= e(L('Need to add a car, renew plates or file a claim? We\'re one call or one visit away.', '¿Necesitas agregar un auto, renovar placas o hacer un reclamo? Estamos a una llamada o una visita.')) ?></p></div></li>
    </ol>
  </div>
</section>

<?php require APP_ROOT . '/partials/cta.php'; ?>
<?php require APP_ROOT . '/partials/footer.php'; ?>
