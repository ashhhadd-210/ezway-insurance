<?php
require __DIR__ . '/app/bootstrap.php';

$faqs = [
    [L('What\'s the difference between personal and commercial auto?', '¿Cuál es la diferencia entre auto personal y comercial?'),
     L('Personal auto covers the cars you and your family drive. Commercial auto is for vehicles used for work: the policy is in your company name and has higher liability limits, which many contracts require.', 'El auto personal cubre los autos que manejan tú y tu familia. El auto comercial es para vehículos de trabajo: la póliza va a nombre de tu compañía y tiene límites de responsabilidad más altos, que piden muchos contratos.')],
    [L('Can I get insured without a driver license?', '¿Puedo asegurarme sin licencia de manejo?'),
     L('Yes. We accept any type of ID: foreign licenses, passports, matrícula consular and state IDs. Choose "Other ID" in the quote form.', 'Sí. Aceptamos cualquier tipo de ID: licencias extranjeras, pasaportes, matrícula consular e ID del estado. Elige "Otra ID" en la cotización.')],
    [L('What does "full coverage" mean?', '¿Qué significa "cobertura completa"?'),
     L('It usually means liability plus comprehensive and collision. Liability covers damage you cause to others; comprehensive and collision protect your own car.', 'Normalmente significa responsabilidad civil más comprensivo y colisión. La responsabilidad cubre los daños que causas a otros; comprensivo y colisión protegen tu propio auto.')],
    [L('My car is financed. What do I need?', 'Mi auto está financiado. ¿Qué necesito?'),
     L('Your lender (the lienholder) usually requires full coverage and must be listed on the policy. Tell us the bank or finance company in the quote form and we\'ll take care of it.', 'Tu financiera (el lienholder) normalmente exige cobertura completa y debe aparecer en la póliza. Escribe el banco o financiera en la cotización y nosotros nos encargamos.')],
    [L('How much liability coverage do I need?', '¿Cuánta responsabilidad civil necesito?'),
     L('Texas requires at least 30/60/25, but that is often too low for a serious accident. Many people choose 100/300/100. Your agent can recommend limits based on what you want to protect.', 'Texas exige mínimo 30/60/25, pero muchas veces no alcanza para un accidente grave. Mucha gente elige 100/300/100. Tu agente te recomienda límites según lo que quieras proteger.')],
    [L('Can I choose different coverage for each car?', '¿Puedo elegir cobertura diferente para cada auto?'),
     L('Yes. In the quote form you choose full coverage, roadside and rental separately for every vehicle.', 'Sí. En la cotización eliges cobertura completa, asistencia y auto de renta por separado para cada vehículo.')],
    [L('How fast will I hear back?', '¿Qué tan rápido me contestan?'),
     L('Usually within one business day. If you need coverage today, call us at ' . cfg('site.phone') . ' or visit one of our 4 Austin offices.', 'Normalmente en un día hábil. Si necesitas cobertura hoy, llámanos al ' . cfg('site.phone') . ' o visita una de nuestras 4 oficinas en Austin.')],
];

$page = [
    'slug'        => 'auto',
    'path'        => 'auto-insurance',
    'title'       => L('Personal & commercial auto insurance in Austin, TX', 'Seguro de auto personal y comercial en Austin, TX'),
    'description' => L('Personal and commercial auto insurance from 20+ companies. Any type of ID accepted. Liability, full coverage, PIP, uninsured motorist, roadside and rental explained.', 'Seguro de auto personal y comercial con más de 20 compañías. Aceptamos cualquier tipo de ID. Responsabilidad civil, cobertura completa, PIP, conductor sin seguro, asistencia y auto de renta.'),
    'schema'      => [[
        '@type'      => 'FAQPage',
        'mainEntity' => array_map(fn ($f) => ['@type' => 'Question', 'name' => $f[0], 'acceptedAnswer' => ['@type' => 'Answer', 'text' => $f[1]]], $faqs),
    ]],
];
$cov = auto_coverages();
require APP_ROOT . '/partials/header.php';
?>

<section class="page-hero">
  <div class="hero-bg" aria-hidden="true"><span class="blob b2"></span><span class="grid-lines"></span><?= pinwheel_svg('pinwheel page-pin') ?></div>
  <div class="wrap page-hero-in">
    <p class="pill reveal"><span class="pill-dot"></span><?= e(L('Auto insurance', 'Seguro de auto')) ?></p>
    <h1 class="reveal"><?= e(L('Personal & commercial auto,', 'Auto personal y comercial,')) ?> <span class="text-teal"><?= e(L('explained in plain words.', 'explicado fácil.')) ?></span></h1>
    <p class="lead reveal"><?= e(L(
        'Here\'s what each coverage does and who it\'s for. We compare ' . cfg('site.carriers') . '+ companies and accept any type of ID.',
        'Esto es lo que hace cada cobertura y para quién es. Comparamos más de ' . cfg('site.carriers') . ' compañías y aceptamos cualquier tipo de ID.'
    )) ?></p>
    <div class="split-actions center reveal">
      <button class="btn btn-primary btn-lg" type="button" data-quote="personal_auto"><?= e(L('Quote personal auto', 'Cotizar auto personal')) ?> <?= icon('arrow-right') ?></button>
      <button class="btn btn-ghost btn-lg" type="button" data-quote="commercial_auto"><?= icon('truck') ?> <?= e(L('Quote commercial auto', 'Cotizar auto comercial')) ?></button>
    </div>
  </div>
</section>

<nav class="cov-nav" aria-label="<?= e(L('On this page', 'En esta página')) ?>" data-covnav>
  <div class="wrap">
    <a href="#personal"><?= icon('car') ?><?= e(L('Personal', 'Personal')) ?></a>
    <a href="#commercial"><?= icon('truck') ?><?= e(L('Commercial', 'Comercial')) ?></a>
    <?php foreach ($cov as $key => $c): ?>
      <a href="#<?= e($key) ?>"><?= icon($c['icon']) ?><?= e($c['title']) ?></a>
    <?php endforeach; ?>
    <a href="#compare"><?= icon('grid') ?><?= e(L('Compare', 'Comparar')) ?></a>
    <a href="#faq"><?= icon('info') ?><?= e(L('FAQ', 'Preguntas')) ?></a>
  </div>
</nav>

<section class="section section-tight2" aria-label="<?= e(L('Personal or commercial', 'Personal o comercial')) ?>">
  <div class="wrap type-grid">
    <article class="type-card reveal" id="personal">
      <span class="type-ic"><?= icon('car') ?></span>
      <p class="eyebrow"><?= e(L('For you & your family', 'Para ti y tu familia')) ?></p>
      <h2><?= e(L('Personal auto', 'Auto personal')) ?></h2>
      <p><?= e(L('Your everyday cars, trucks and SUVs. Add every driver in your household and choose coverage for each vehicle.', 'Tus autos, trocas y SUVs de todos los días. Agrega a todos los conductores de tu casa y elige la cobertura de cada vehículo.')) ?></p>
      <ul class="tick-list">
        <li><?= icon('check') ?><?= e(L('Texas minimum or higher limits', 'Mínimo de Texas o límites más altos')) ?></li>
        <li><?= icon('check') ?><?= e(L('Full coverage for financed cars', 'Cobertura completa para autos financiados')) ?></li>
        <li><?= icon('check') ?><?= e(L('Any type of ID accepted', 'Aceptamos cualquier tipo de ID')) ?></li>
      </ul>
      <button class="btn btn-primary" type="button" data-quote="personal_auto"><?= e(L('Get a personal quote', 'Cotizar auto personal')) ?> <?= icon('arrow-right') ?></button>
    </article>
    <article class="type-card type-card-dark reveal" id="commercial">
      <span class="type-ic"><?= icon('truck') ?></span>
      <p class="eyebrow"><?= e(L('For your business', 'Para tu negocio')) ?></p>
      <h2><?= e(L('Commercial auto', 'Auto comercial')) ?></h2>
      <p><?= e(L('Work trucks, vans, dump trucks and company cars, insured in your company name with the higher limits contracts ask for.', 'Trocas de trabajo, vans, volteos y autos de la compañía, asegurados a nombre de tu negocio con los límites altos que piden los contratos.')) ?></p>
      <ul class="tick-list">
        <li><?= icon('check') ?><?= e(L('Policy in your company name', 'Póliza a nombre de tu compañía')) ?></li>
        <li><?= icon('check') ?><?= e(L('Limits up to $1,000,000', 'Límites de hasta $1,000,000')) ?></li>
        <li><?= icon('check') ?><?= e(L('Employees as drivers', 'Empleados como conductores')) ?></li>
      </ul>
      <button class="btn btn-accent" type="button" data-quote="commercial_auto"><?= e(L('Get a commercial quote', 'Cotizar auto comercial')) ?> <?= icon('arrow-right') ?></button>
    </article>
  </div>
</section>

<section class="section section-top0">
  <div class="wrap">
    <header class="section-head reveal">
      <p class="eyebrow"><?= e(L('Coverage options', 'Opciones de cobertura')) ?></p>
      <h2><?= e(L('Build the protection that fits you', 'Arma la protección a tu medida')) ?></h2>
    </header>
    <div class="cov-detail">
      <?php foreach ($cov as $key => $c): ?>
      <article class="cd reveal" id="<?= e($key) ?>">
        <div class="cd-side">
          <span class="cd-ic"><?= icon($c['icon']) ?></span>
          <span class="cov-tag"><?= e($c['tag']) ?></span>
        </div>
        <div class="cd-main">
          <h2><?= e($c['title']) ?></h2>
          <p class="cd-short"><?= e($c['short']) ?></p>
          <p><?= e($c['long']) ?></p>
        </div>
        <div class="cd-good">
          <h3><?= e(L('Good for', 'Ideal para')) ?></h3>
          <ul><?php foreach ($c['good'] as $g): ?><li><?= icon('check') ?><?= e($g) ?></li><?php endforeach; ?></ul>
        </div>
      </article>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="section section-tint" id="compare" aria-labelledby="cmpTitle">
  <div class="wrap">
    <header class="section-head reveal">
      <p class="eyebrow"><?= e(L('Compare', 'Comparar')) ?></p>
      <h2 id="cmpTitle"><?= e(L('Liability only vs. full coverage', 'Solo responsabilidad vs. cobertura completa')) ?></h2>
      <p><?= e(L('The biggest decision for each vehicle. Here\'s what each one pays for.', 'La decisión más importante para cada vehículo. Esto es lo que paga cada una.')) ?></p>
    </header>
    <div class="cmp-wrap reveal">
      <table class="cmp">
        <thead><tr><th scope="col"><?= e(L('What happens', 'Qué pasa')) ?></th><th scope="col"><?= e(L('Liability only', 'Solo responsabilidad')) ?></th><th scope="col" class="cmp-hl"><?= e(L('Full coverage', 'Cobertura completa')) ?></th></tr></thead>
        <tbody>
          <?php
          $rows = [
              [L('Injuries you cause to others', 'Lesiones que causas a otros'), true, true],
              [L('Damage you cause to other cars or property', 'Daños que causas a otros autos o propiedad'), true, true],
              [L('Repairs to your car after an accident', 'Reparación de tu auto después de un choque'), false, true],
              [L('Theft, vandalism, fire, hail and flood', 'Robo, vandalismo, incendio, granizo e inundación'), false, true],
              [L('Hitting an animal or a falling object', 'Chocar con un animal u objeto que cae'), false, true],
              [L('Required for financed or leased cars', 'Exigida para autos financiados o en leasing'), false, true],
          ];
          foreach ($rows as [$label, $a, $b]): ?>
          <tr>
            <th scope="row"><?= e($label) ?></th>
            <td><?= $a ? '<span class="yes">' . icon('check') . '<span class="sr">' . e(L('Covered', 'Cubierto')) . '</span></span>' : '<span class="no">' . icon('x') . '<span class="sr">' . e(L('Not covered', 'No cubierto')) . '</span></span>' ?></td>
            <td class="cmp-hl"><?= $b ? '<span class="yes">' . icon('check') . '<span class="sr">' . e(L('Covered', 'Cubierto')) . '</span></span>' : '<span class="no">' . icon('x') . '<span class="sr">' . e(L('Not covered', 'No cubierto')) . '</span></span>' ?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
      <p class="cmp-note"><?= icon('info') ?><span><?= e(L('Add', 'Agrega')) ?> <b><?= e(L('roadside', 'asistencia')) ?></b> <?= e(L('and', 'y')) ?> <b><?= e(L('rental', 'auto de renta')) ?></b> <?= e(L('to any vehicle, and', 'a cualquier vehículo, y')) ?> <b>PIP</b> <?= e(L('and', 'y')) ?> <b><?= e(L('uninsured motorist', 'conductor sin seguro')) ?></b> <?= e(L('to the whole policy.', 'a toda la póliza.')) ?></span></p>
    </div>
  </div>
</section>

<section class="section" id="faq" aria-labelledby="faqTitle">
  <div class="wrap faq-wrap">
    <header class="section-head reveal">
      <p class="eyebrow"><?= e(L('FAQ', 'Preguntas frecuentes')) ?></p>
      <h2 id="faqTitle"><?= e(L('Frequently asked questions', 'Preguntas frecuentes')) ?></h2>
      <p><?= e(L('Can\'t find your answer?', '¿No encuentras tu respuesta?')) ?> <a href="<?= e(lurl('contact')) ?>"><?= e(L('Send us a message', 'Envíanos un mensaje')) ?></a>.</p>
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
