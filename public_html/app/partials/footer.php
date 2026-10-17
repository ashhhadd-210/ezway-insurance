<?php
defined('APP') or exit;
$isQuotePage = ($page['slug'] ?? '') === 'quote';

// Text used by the JavaScript (quote form, contact form, office hours), in the page language.
$js = [
    'steps' => [
        'service' => L('Service', 'Servicio'), 'you' => L('You', 'Tú'), 'address' => L('Address', 'Dirección'),
        'drivers' => L('Drivers', 'Conductores'), 'vehicles' => L('Vehicles', 'Vehículos'), 'coverage' => L('Coverage', 'Cobertura'),
        'details' => L('Details', 'Detalles'), 'review' => L('Review', 'Revisar'),
    ],
    'stepOf' => L('Step {n} of {t}', 'Paso {n} de {t}'),
    'continue' => L('Continue', 'Continuar'), 'reviewBtn' => L('Review my request', 'Revisar mi solicitud'), 'send' => L('Send my request', 'Enviar mi solicitud'),
    'sent' => L('Request sent', 'Solicitud enviada'),
    'driver' => L('Driver', 'Conductor'), 'vehicle' => L('Vehicle', 'Vehículo'), 'of' => L('of', 'de'),
    'policyholder' => L('Policyholder (you)', 'Titular (tú)'), 'additionalDriver' => L('Additional driver', 'Conductor adicional'),
    'you' => L('You', 'Tú'), 'enterVin' => L('Enter the VIN to identify it', 'Escribe el VIN para identificarlo'),
    'vehicleIdentified' => L('Vehicle identified', 'Vehículo identificado'), 'vinOnly' => L('Vehicle (VIN only)', 'Vehículo (solo VIN)'),
    'born' => L('Born', 'Nació'), 'prefers' => L('Prefers', 'Prefiere'), 'company' => L('Company', 'Compañía'),
    'fullCoverage' => L('Full coverage', 'Cobertura completa'), 'deductible' => L('deductible', 'deducible'),
    'liabilityOnly' => L('Liability only', 'Solo responsabilidad civil'), 'roadside' => L('Roadside', 'Asistencia'), 'rental' => L('Rental', 'Auto de renta'),
    'financed' => L('Financed', 'Financiado'), 'noLien' => L('Not financed', 'Sin financiar'),
    'liability' => L('Liability', 'Responsabilidad civil'), 'pip' => L('Personal injury protection', 'Protección contra lesiones'),
    'um' => L('Uninsured motorist', 'Conductor sin seguro'), 'startDate' => L('Start date', 'Fecha de inicio'),
    'insured' => L('Currently insured', 'Tiene seguro'), 'yes' => L('Yes', 'Sí'), 'no' => L('No', 'No'), 'notes' => L('Notes', 'Notas'),
    'edit' => L('Edit', 'Editar'), 'aboutYou' => L('About you', 'Tus datos'), 'address' => L('Address', 'Dirección'),
    'drivers' => L('Drivers', 'Conductores'), 'vehicles' => L('Vehicles', 'Vehículos'), 'coverage' => L('Coverage', 'Cobertura'),
    'details' => L('Details', 'Detalles'), 'service' => L('Service', 'Servicio'),
    'vinLooking' => L('Looking up your vehicle…', 'Buscando tu vehículo…'),
    'vinNotFound' => L('We couldn\'t identify this VIN. Please double-check it. You can still continue and your agent will verify it.', 'No pudimos identificar este VIN. Revísalo. Puedes continuar y tu agente lo verificará.'),
    'vinBusy' => L('Vehicle lookup is busy right now. You can continue and your agent will verify the VIN.', 'La búsqueda del vehículo no está disponible ahora. Puedes continuar y tu agente verificará el VIN.'),
    'vinIOQ' => L('VINs never contain the letters I, O or Q. These are usually the numbers 1 and 0.', 'Los VIN nunca tienen las letras I, O ni Q. Normalmente son los números 1 y 0.'),
    'err' => [
        'first' => L('Enter your first name.', 'Escribe tu nombre.'), 'last' => L('Enter your last name.', 'Escribe tu apellido.'),
        'firstD' => L('Enter a first name.', 'Escribe el nombre.'), 'lastD' => L('Enter a last name.', 'Escribe el apellido.'),
        'dob' => L('Enter your date of birth.', 'Escribe tu fecha de nacimiento.'), 'dobD' => L('Enter a date of birth.', 'Escribe la fecha de nacimiento.'),
        'dobBad' => L('Enter a real date as MM/DD/YYYY.', 'Escribe una fecha real como MM/DD/AAAA.'),
        'dobAge' => L('Enter a real date as MM/DD/YYYY (you must be 16 or older).', 'Escribe una fecha real como MM/DD/AAAA (debes tener 16 años o más).'),
        'phone' => L('Enter a 10-digit phone number.', 'Escribe un teléfono de 10 dígitos.'), 'email' => L('Enter a valid email address.', 'Escribe un correo válido.'),
        'company' => L('Enter your company name.', 'Escribe el nombre de tu compañía.'),
        'zip' => L('Enter a 5-digit ZIP code.', 'Escribe un código postal de 5 dígitos.'), 'street' => L('Enter the street address.', 'Escribe la dirección.'),
        'city' => L('Enter the city.', 'Escribe la ciudad.'), 'state' => L('Choose the state.', 'Elige el estado.'),
        'rel' => L('Choose how this driver is related to you.', 'Elige el parentesco con este conductor.'), 'idType' => L('Choose an ID type.', 'Elige un tipo de ID.'),
        'idKind' => L('Choose the type of ID.', 'Elige el tipo de identificación.'),
        'licState' => L('Choose the state that issued the license.', 'Elige el estado que emitió la licencia.'),
        'licCountry' => L('Enter the country that issued the license.', 'Escribe el país que emitió la licencia.'),
        'licNum' => L('Enter the license number.', 'Escribe el número de licencia.'),
        'licBad' => L('Use letters and numbers only (4–25 characters).', 'Usa solo letras y números (4 a 25 caracteres).'),
        'vin' => L('Enter the VIN.', 'Escribe el VIN.'), 'vinLen' => L('A VIN has exactly 17 characters (you have {n}).', 'Un VIN tiene exactamente 17 caracteres (tienes {n}).'),
        'vinIOQ' => L('VINs never contain I, O or Q. Check for 1 and 0.', 'Los VIN no tienen I, O ni Q. Revisa si son 1 y 0.'),
        'vinDup' => L('This VIN is already listed above.', 'Este VIN ya está en la lista.'),
        'lien' => L('Tell us if this vehicle is financed or leased.', 'Dinos si este vehículo está financiado o en leasing.'),
        'liability' => L('Choose a liability level.', 'Elige un nivel de responsabilidad.'),
        'startDate' => L('Choose a date within the next 12 months.', 'Elige una fecha dentro de los próximos 12 meses.'),
        'consent' => L('Please check the box so we can contact you about your request.', 'Marca la casilla para que podamos contactarte.'),
        'service' => L('Choose what you need.', 'Elige lo que necesitas.'),
        'choose' => L('Please choose an option.', 'Elige una opción.'), 'chooseAny' => L('Choose at least one.', 'Elige al menos una opción.'),
        'fill' => L('Please fill this in.', 'Completa este campo.'), 'check' => L('Please check this value.', 'Revisa este dato.'),
        'vinOpt' => L('Enter a valid 17-character VIN, or leave it blank.', 'Escribe un VIN válido de 17 caracteres, o déjalo en blanco.'),
        'name' => L('Enter your name.', 'Escribe tu nombre.'), 'phoneOpt' => L('Enter a 10-digit phone number, or leave it blank.', 'Escribe un teléfono de 10 dígitos, o déjalo en blanco.'),
        'message' => L('Tell us a little more (at least 10 characters).', 'Cuéntanos un poco más (al menos 10 caracteres).'),
    ],
    'welcomeBack' => L('Welcome back! We saved your progress.', '¡Bienvenido de nuevo! Guardamos tu progreso.'),
    'progressSaved' => L('Your progress is saved. Pick up where you left off any time.', 'Tu progreso está guardado. Continúa cuando quieras.'),
    'fixFields' => L('Please fix the highlighted fields.', 'Corrige los campos marcados.'),
    'serverError' => L('Something went wrong. Please try again.', 'Algo salió mal. Inténtalo de nuevo.'),
    'offline' => L('We couldn\'t reach the server. Check your connection and try again. Your answers are saved.', 'No pudimos conectar con el servidor. Revisa tu conexión e inténtalo de nuevo. Tus respuestas están guardadas.'),
    'network' => L('Network error. Please check your connection and try again.', 'Error de conexión. Revisa tu internet e inténtalo de nuevo.'),
    'zipBad' => L('Please enter a 5-digit ZIP code.', 'Escribe un código postal de 5 dígitos.'),
    'openNow' => L('Open', 'Abierto'), 'closedNow' => L('Closed', 'Cerrado'), 'opensToday' => L('opens {t}', 'abre {t}'),
    'closesAt' => L('closes {t}', 'cierra {t}'), 'opensAt' => L('opens {d} {t}', 'abre {d} {t}'),
    'today' => L('today', 'hoy'), 'tomorrow' => L('tomorrow', 'mañana'),
    'days' => lang() === 'es' ? ['domingo', 'lunes', 'martes', 'miércoles', 'jueves', 'viernes', 'sábado'] : ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'],
    'am' => L('AM', 'a. m.'), 'pm' => L('PM', 'p. m.'),
    'dateLocale' => L('en-US', 'es-US'),
];
?>
</main>

<footer class="footer">
  <div class="wrap footer-grid">
    <div class="footer-brand">
      <a class="logo logo-light" href="<?= e(lurl()) ?>"><?= logo_html(true) ?></a>
      <p><?= e(L(
          'Licensed agents in Austin who compare ' . cfg('site.carriers') . '+ insurance companies for you. Serving Texas for ' . cfg('site.years') . '+ years.',
          'Agentes con licencia en Austin que comparan más de ' . cfg('site.carriers') . ' compañías de seguros por ti. Más de ' . cfg('site.years') . ' años sirviendo a Texas.'
      )) ?></p>
      <ul class="footer-badges">
        <li><?= icon('shield') ?><?= e(L('Licensed in', 'Con licencia en')) ?> <?= e(cfg('site.licensed_in')) ?><?= cfg('site.license') ? ' · #' . e(cfg('site.license')) : '' ?></li>
        <li><?= icon('language') ?><?= e(L('Se habla español', 'English & Spanish')) ?></li>
        <li><?= icon('id') ?><?= e(L('Any type of ID accepted', 'Aceptamos cualquier tipo de ID')) ?></li>
      </ul>
      <a class="btn btn-primary" href="<?= e(lurl('quote')) ?>" data-quote><?= e(L('Start your quote', 'Empieza tu cotización')) ?> <?= icon('arrow-right') ?></a>
    </div>
    <div>
      <h2 class="footer-h"><?= e(L('Explore', 'Explora')) ?></h2>
      <ul class="footer-links">
        <li><a href="<?= e(lurl()) ?>"><?= e(L('Home', 'Inicio')) ?></a></li>
        <li><a href="<?= e(lurl('auto-insurance')) ?>"><?= e(L('Auto insurance', 'Seguro de auto')) ?></a></li>
        <li><a href="<?= e(lurl('services')) ?>"><?= e(L('All services', 'Todos los servicios')) ?></a></li>
        <li><a href="<?= e(lurl('about')) ?>"><?= e(L('About us', 'Nosotros')) ?></a></li>
        <li><a href="<?= e(lurl('contact')) ?>"><?= e(L('Locations & contact', 'Oficinas y contacto')) ?></a></li>
        <li><a href="<?= e(lurl('quote')) ?>"><?= e(L('Get a quote', 'Cotizar')) ?></a></li>
      </ul>
    </div>
    <div>
      <h2 class="footer-h"><?= e(L('Services', 'Servicios')) ?></h2>
      <ul class="footer-links">
        <?php foreach (services() as $key => $s): ?>
        <li><a href="<?= e(lurl($s['page'])) ?>"><?= e($s['title']) ?></a></li>
        <?php endforeach; ?>
      </ul>
    </div>
    <div class="footer-offices">
      <h2 class="footer-h"><?= e(L('Our Austin offices', 'Nuestras oficinas en Austin')) ?></h2>
      <ul class="footer-links">
        <?php foreach (offices() as $o): ?>
        <li>
          <a href="<?= e(maps_link($o)) ?>" target="_blank" rel="noopener"><?= icon('pin') ?><span><b><?= e($o['name']) ?></b><br><?= e(office_address($o)) ?></span></a>
        </li>
        <?php endforeach; ?>
        <li><a href="<?= e(tel_href(cfg('site.phone'))) ?>"><?= icon('phone') ?><span><?= e(cfg('site.phone')) ?></span></a></li>
        <li><a href="mailto:<?= e(cfg('site.email')) ?>"><?= icon('mail') ?><span><?= e(cfg('site.email')) ?></span></a></li>
      </ul>
      <a class="footer-more" href="<?= e(lurl('contact')) ?>"><?= e(L('Hours for every office', 'Horarios de cada oficina')) ?> <?= icon('arrow-right') ?></a>
    </div>
  </div>
  <div class="wrap footer-bottom">
    <p>© <?= date('Y') ?> <?= e(cfg('site.legal_name')) ?>. <?= e(L('All rights reserved.', 'Todos los derechos reservados.')) ?></p>
    <p class="footer-legal"><?= e(L(
        'Quotes are estimates and not a guarantee of coverage. Coverage is subject to underwriting and the terms of the policy issued.',
        'Las cotizaciones son estimados y no garantizan la cobertura. La cobertura depende de la aprobación de la aseguradora y de los términos de la póliza.'
    )) ?> <a href="<?= e(lurl('privacy')) ?>"><?= e(L('Privacy policy', 'Política de privacidad')) ?></a></p>
  </div>
</footer>

<?php if (!$isQuotePage): ?>
<button class="fab" type="button" data-quote data-fab aria-label="<?= e(L('Start your quote', 'Empieza tu cotización')) ?>">
  <span class="fab-ring" aria-hidden="true"></span>
  <span class="fab-icon"><?= icon('shield') ?></span>
  <span class="fab-text"><?= e(L('Start your quote', 'Empieza tu cotización')) ?></span>
  <?= icon('arrow-right', 'fab-arrow') ?>
</button>

<div class="q-overlay" data-qoverlay hidden>
  <div class="q-sheet" role="dialog" aria-modal="true" aria-labelledby="qwTitle">
    <?php $qwMode = 'modal'; require APP_ROOT . '/partials/quote-wizard.php'; ?>
  </div>
</div>
<?php endif; ?>

<div class="toast" role="status" aria-live="polite" data-toast></div>

<script type="application/json" id="i18n"><?= json_encode($js, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) ?></script>
<script src="<?= e(asset('js/site.js')) ?>" defer></script>
<script src="<?= e(asset('js/quote.js')) ?>" defer></script>
<?php if (turnstile_enabled()): ?>
<script src="https://challenges.cloudflare.com/turnstile/v0/api.js?render=explicit" defer></script>
<?php endif; ?>
</body>
</html>
