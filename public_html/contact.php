<?php
require __DIR__ . '/app/bootstrap.php';

$page = [
    'slug'        => 'contact',
    'path'        => 'contact',
    'title'       => L('Locations & contact: 4 offices in Austin, TX', 'Oficinas y contacto: 4 oficinas en Austin, TX'),
    'description' => L(
        'Visit EZ Way Insurance at 7901 Cameron Rd, 2004 E William Cannon Dr, 9515 N Lamar Blvd or 1200 W Howard Ln in Austin, TX. Call ' . cfg('site.phone') . '. Se habla español.',
        'Visita EZ Way Insurance en 7901 Cameron Rd, 2004 E William Cannon Dr, 9515 N Lamar Blvd o 1200 W Howard Ln en Austin, TX. Llama al ' . cfg('site.phone') . '.'
    ),
];
$topics = [
    'General question' => L('General question', 'Pregunta general'),
    'Existing policy'  => L('My existing policy', 'Mi póliza actual'),
    'Claims help'      => L('Help with a claim', 'Ayuda con un reclamo'),
    'Title & registration' => L('Titles, plates & registration', 'Títulos, placas y registro'),
    'Billing'          => L('Payments & billing', 'Pagos'),
    'Other'            => L('Other', 'Otro'),
];
$offices = offices();
require APP_ROOT . '/partials/header.php';
?>

<section class="page-hero">
  <div class="hero-bg" aria-hidden="true"><span class="blob b1"></span><span class="grid-lines"></span><?= pinwheel_svg('pinwheel page-pin') ?></div>
  <div class="wrap page-hero-in">
    <p class="pill reveal"><span class="pill-dot"></span><?= e(L('Locations & contact', 'Oficinas y contacto')) ?></p>
    <h1 class="reveal"><?= e(L('Talk to a real person.', 'Habla con una persona real.')) ?> <span class="text-teal"><?= e(L('4 offices across Austin.', '4 oficinas en Austin.')) ?></span></h1>
    <p class="lead reveal"><?= e(L('Questions about a quote, your policy, a claim or your paperwork? Call, stop by or send us a message, in English or Spanish.', '¿Preguntas sobre una cotización, tu póliza, un reclamo o tus trámites? Llámanos, visítanos o envíanos un mensaje, en inglés o en español.')) ?></p>
    <div class="split-actions center reveal">
      <a class="btn btn-primary btn-lg" href="<?= e(tel_href(cfg('site.phone'))) ?>"><?= icon('phone') ?> <?= e(cfg('site.phone')) ?></a>
      <a class="btn btn-ghost btn-lg" href="#message"><?= icon('chat') ?> <?= e(L('Send a message', 'Envía un mensaje')) ?></a>
    </div>
  </div>
</section>

<section class="section section-top0" aria-labelledby="offTitle">
  <div class="wrap">
    <h2 id="offTitle" class="sr"><?= e(L('Our offices', 'Nuestras oficinas')) ?></h2>
    <div class="loc-grid">
      <?php foreach ($offices as $o): ?>
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
        <div class="loc-actions">
          <a class="btn btn-ghost btn-sm" href="<?= e(maps_link($o)) ?>" target="_blank" rel="noopener"><?= icon('directions') ?> <?= e(L('Directions', 'Cómo llegar')) ?></a>
          <a class="btn btn-ghost btn-sm" href="<?= e(tel_href(cfg('site.phone'))) ?>"><?= icon('phone') ?> <?= e(L('Call', 'Llamar')) ?></a>
        </div>
      </article>
      <?php endforeach; ?>
    </div>

    <div class="map-box reveal">
      <div class="map-tabs" role="tablist" aria-label="<?= e(L('Show on map', 'Ver en el mapa')) ?>">
        <?php foreach ($offices as $i => $o): ?>
        <button type="button" role="tab" aria-selected="<?= $i === 0 ? 'true' : 'false' ?>" data-map-tab="<?= e(maps_embed($o)) ?>"><?= icon('pin') ?><?= e($o['name']) ?></button>
        <?php endforeach; ?>
      </div>
      <div class="map">
        <iframe title="<?= e(L('Map of our office', 'Mapa de nuestra oficina')) ?>" loading="lazy" referrerpolicy="no-referrer-when-downgrade" data-map-frame src="<?= e(maps_embed($offices[0])) ?>"></iframe>
      </div>
    </div>
  </div>
</section>

<section class="section section-tint">
  <div class="wrap contact-grid">
    <div class="card-panel reveal" id="message">
      <h2><?= e(L('Send us a message', 'Envíanos un mensaje')) ?></h2>
      <p class="muted"><?= e(L('Looking for a price? The', '¿Buscas un precio? La')) ?> <button class="link-btn" type="button" data-quote><?= e(L('quote form', 'cotización en línea')) ?></button> <?= e(L('is the fastest way to get one.', 'es la forma más rápida.')) ?></p>
      <form class="contact-form" data-contact data-token="<?= e(form_token()) ?>" data-api="<?= e(url('api/contact.php')) ?>" data-turnstile="<?= e(turnstile_enabled() ? cfg('security.turnstile_site_key') : '') ?>" novalidate>
        <div class="hp-wrap" aria-hidden="true"><label>Leave this empty<input type="text" name="website" tabindex="-1" autocomplete="off"></label></div>
        <div class="grid g2">
          <label class="fld"><span class="fld-l"><?= e(L('Your name', 'Tu nombre')) ?></span><input name="name" autocomplete="name" maxlength="120" required><span class="fld-e"></span></label>
          <label class="fld"><span class="fld-l"><?= e(L('Email', 'Correo electrónico')) ?></span><input name="email" type="email" autocomplete="email" maxlength="160" required><span class="fld-e"></span></label>
          <label class="fld"><span class="fld-l"><?= e(L('Phone', 'Teléfono')) ?> <i><?= e(L('(optional)', '(opcional)')) ?></i></span><input name="phone" type="tel" autocomplete="tel-national" maxlength="14" data-mask="phone" placeholder="(512) 555-5555"><span class="fld-e"></span></label>
          <label class="fld"><span class="fld-l"><?= e(L('Topic', 'Tema')) ?></span>
            <select name="topic">
              <?php foreach ($topics as $val => $label): ?><option value="<?= e($val) ?>"><?= e($label) ?></option><?php endforeach; ?>
            </select><span class="fld-e"></span></label>
          <label class="fld span2"><span class="fld-l"><?= e(L('Message', 'Mensaje')) ?></span><textarea name="message" rows="5" maxlength="3000" required placeholder="<?= e(L('How can we help?', '¿Cómo te podemos ayudar?')) ?>"></textarea><span class="fld-e"></span></label>
        </div>
        <div class="ts-box" data-turnstile-box></div>
        <div class="form-foot">
          <p class="qw-secure"><?= icon('lock') ?> <?= e(L('Sent securely. We never share your information.', 'Envío seguro. Nunca compartimos tu información.')) ?></p>
          <button class="btn btn-primary" type="submit"><span><?= e(L('Send message', 'Enviar mensaje')) ?></span><?= icon('arrow-right') ?><span class="btn-spin" aria-hidden="true"></span></button>
        </div>
        <div class="form-done" hidden>
          <svg class="done-check" viewBox="0 0 80 80" aria-hidden="true"><circle cx="40" cy="40" r="36"/><path d="m25 41 10 10 21-22"/></svg>
          <h3><?= e(L('Message sent!', '¡Mensaje enviado!')) ?></h3>
          <p><?= e(L('Thanks for reaching out. We\'ll get back to you within one business day.', 'Gracias por escribirnos. Te contestamos en un día hábil.')) ?></p>
        </div>
      </form>
    </div>

    <aside class="contact-side reveal">
      <a class="cc" href="<?= e(tel_href(cfg('site.phone'))) ?>">
        <span class="cc-ic"><?= icon('phone') ?></span>
        <span class="cc-l"><?= e(L('Call us', 'Llámanos')) ?></span>
        <b><?= e(cfg('site.phone')) ?></b>
        <small><?= e(L('Fastest way to get help', 'La forma más rápida de recibir ayuda')) ?></small>
      </a>
      <a class="cc" href="#offTitle">
        <span class="cc-ic"><?= icon('pin') ?></span>
        <span class="cc-l"><?= e(L('Visit us', 'Visítanos')) ?></span>
        <b><?= e(L('4 offices in Austin', '4 oficinas en Austin')) ?></b>
        <small><?= e(L('Every service at every office', 'Todos los servicios en cada oficina')) ?></small>
      </a>
      <div class="cc cc-static">
        <span class="cc-ic"><?= icon('language') ?></span>
        <span class="cc-l"><?= e(L('Languages', 'Idiomas')) ?></span>
        <b><?= e(L('English & Español', 'Español e inglés')) ?></b>
        <small><?= e(L('Se habla español', 'We speak English too')) ?></small>
      </div>
    </aside>
  </div>
</section>

<?php require APP_ROOT . '/partials/footer.php'; ?>
