<?php
defined('APP') or exit;
/** @var string $qwMode 'modal' or 'page' */
$qwMode ??= 'modal';

$countries = ['México', 'Guatemala', 'Honduras', 'El Salvador', 'Nicaragua', 'Colombia', 'Venezuela', 'Cuba', 'República Dominicana',
    'Ecuador', 'Perú', 'Bolivia', 'Argentina', 'Chile', 'Brasil', 'Haití', 'Canada', 'India', 'Pakistan', 'Philippines', 'China', 'Vietnam',
    'South Korea', 'Nigeria', 'Ethiopia', 'United Kingdom', 'Germany', 'France', 'Italy', 'Spain', 'Ukraine', 'Russia'];

$stateOptions = function (string $placeholder): string {
    $out = '<option value="">' . e($placeholder) . '</option>';
    foreach (US_STATES as $code => $name) {
        $out .= '<option value="' . $code . '">' . e($name) . '</option>';
    }
    return $out;
};

$fld = function (string $label, string $input, string $class = '', string $hint = '', string $attrs = ''): string {
    return '<label class="fld ' . $class . '"' . $attrs . '><span class="fld-l">' . $label . '</span>' . $input
        . ($hint ? '<span class="fld-h">' . $hint . '</span>' : '')
        . '<span class="fld-e" aria-live="polite"></span></label>';
};
$opt = '<i>' . e(L('(optional)', '(opcional)')) . '</i>';

// Liability choices for both kinds of auto policy; the form shows the right set.
$liabilitySets = [];
foreach (['personal_auto', 'commercial_auto'] as $svcKey) {
    foreach (liability_levels($svcKey) as $val => [$label, $desc, $tag]) {
        $liabilitySets[$svcKey][] = compact('val', 'label', 'desc', 'tag');
    }
}
$defaults = ['personal_auto' => 'state_min', 'commercial_auto' => 'c_min'];
$services = services();
?>
<div class="qw qw-<?= e($qwMode) ?>" id="qw" data-qw data-mode="<?= e($qwMode) ?>" data-lang="<?= lang() ?>"
     data-token="<?= e(form_token()) ?>" data-api="<?= e(url('api/')) ?>"
     data-turnstile="<?= e(turnstile_enabled() ? cfg('security.turnstile_site_key') : '') ?>"
     data-max-drivers="<?= MAX_DRIVERS ?>" data-max-vehicles="<?= MAX_VEHICLES ?>"
     data-flows="<?= e(json_encode(array_map(fn ($s) => $s['flow'], $services))) ?>"
     data-liability-defaults="<?= e(json_encode($defaults)) ?>">

  <div class="qw-top">
    <div class="qw-title">
      <span class="qw-badge"><?= logo_mark('qw-mark') ?></span>
      <div>
        <h2 id="qwTitle"><?= e(L('Your free quote', 'Tu cotización gratis')) ?></h2>
        <p data-qw-steplabel><?= e(L('About 3 minutes', 'Unos 3 minutos')) ?></p>
      </div>
    </div>
    <div class="qw-tools">
    <button class="qw-size" type="button" data-textsize aria-pressed="false" title="<?= e(L('Make the text bigger', 'Agrandar el texto')) ?>"><span class="qw-size-a" aria-hidden="true">A</span><span class="qw-size-b" aria-hidden="true">A</span><span class="qw-size-t"><?= e(L('Larger text', 'Letra grande')) ?></span></button>
    <?php if ($qwMode === 'modal'): ?>
    <button class="qw-close" type="button" data-qclose aria-label="<?= e(L('Close quote form', 'Cerrar cotización')) ?>"><?= icon('x') ?></button>
    <?php endif; ?>
    </div>
  </div>

  <ol class="qw-steps" data-qw-steps></ol>
  <div class="qw-bar" aria-hidden="true"><span data-qw-bar></span></div>

  <form class="qw-form" data-qw-form novalidate>
    <div class="hp-wrap" aria-hidden="true">
      <label>Leave this empty<input type="text" name="website" tabindex="-1" autocomplete="off"></label>
    </div>

    <div class="qw-viewport" data-qw-viewport>

      <!-- Service -->
      <section class="qw-step" data-step="service" aria-labelledby="qs-service">
        <header class="qw-head">
          <p class="eyebrow"><?= icon('sparkle') ?> <?= e(L('Let\'s get started', 'Empecemos')) ?></p>
          <h3 id="qs-service"><?= e(L('What can we help you with?', '¿En qué te podemos ayudar?')) ?></h3>
          <p><?= e(L('Pick one. Need more than one? Mention it in the notes and we\'ll quote everything together.', 'Elige una opción. ¿Necesitas más de una? Escríbelo en las notas y te cotizamos todo junto.')) ?></p>
        </header>
        <fieldset class="seg-fld">
          <legend class="sr"><?= e(L('Service', 'Servicio')) ?></legend>
          <div class="svc-pick">
            <?php foreach ($services as $key => $s): ?>
            <label class="svc-opt">
              <input type="radio" name="service" value="<?= e($key) ?>">
              <span class="svc-opt-body">
                <span class="svc-opt-ic"><?= icon($s['icon']) ?></span>
                <span class="svc-opt-t"><b><?= e($s['title']) ?></b><small><?= e($s['short']) ?></small></span>
                <span class="svc-opt-check" aria-hidden="true"><?= icon('check') ?></span>
              </span>
            </label>
            <?php endforeach; ?>
          </div>
          <span class="fld-e" aria-live="polite"></span>
        </fieldset>
      </section>

      <!-- You -->
      <section class="qw-step" data-step="you" aria-labelledby="qs-you" hidden>
        <header class="qw-head">
          <p class="eyebrow"><?= icon('user') ?> <?= e(L('About you', 'Tus datos')) ?></p>
          <h3 id="qs-you"><?= e(L('Let\'s start with you', 'Empecemos contigo')) ?></h3>
          <p><?= e(L('We\'ll use this to prepare your quote and to reach you with your options.', 'Lo usamos para preparar tu cotización y contactarte con tus opciones.')) ?></p>
        </header>
        <div class="grid g2 qw-company" data-only="commercial_auto business">
          <?= $fld(e(L('Business / company name', 'Nombre del negocio o compañía')) . ' ' . $opt, '<input name="applicant.company" autocomplete="organization" maxlength="120">') ?>
          <?= $fld(e(L('Type of business or trade', 'Tipo de negocio u oficio')) . ' <i data-only="commercial_auto">' . e(L('(optional)', '(opcional)')) . '</i>', '<input name="applicant.business_type" maxlength="120" placeholder="' . e(L('e.g. Roofing, landscaping, delivery', 'Ej. techos, jardinería, entregas')) . '">') ?>
        </div>
        <div class="grid g2">
          <?= $fld(e(L('First name', 'Nombre')), '<input name="applicant.first" autocomplete="given-name" maxlength="60" required>') ?>
          <?= $fld(e(L('Last name', 'Apellido')), '<input name="applicant.last" autocomplete="family-name" maxlength="60" required>') ?>
          <?= $fld(e(L('Date of birth', 'Fecha de nacimiento')), '<input name="applicant.dob" inputmode="numeric" autocomplete="bday" placeholder="' . e(L('MM/DD/YYYY', 'MM/DD/AAAA')) . '" maxlength="10" data-mask="date" required>', '', lang() === 'es' ? e('Mes / día / año') : '') ?>
          <?= $fld(e(L('Phone', 'Teléfono')), '<input name="applicant.phone" type="tel" inputmode="tel" autocomplete="tel-national" placeholder="(512) 555-5555" maxlength="14" data-mask="phone" required>') ?>
          <?= $fld(e(L('Email', 'Correo electrónico')), '<input name="applicant.email" type="email" autocomplete="email" maxlength="160" placeholder="' . e(L('you@example.com', 'tu@correo.com')) . '" required>', 'span2') ?>
        </div>
        <fieldset class="seg-fld">
          <legend><?= e(L('Best way to reach you', 'La mejor forma de contactarte')) ?></legend>
          <div class="seg seg-3">
            <?php foreach (contact_prefs() as $val => $label): ?>
            <label><input type="radio" name="applicant.contact_pref" value="<?= $val ?>"><span><?= icon(['phone' => 'phone', 'text' => 'chat', 'email' => 'mail'][$val]) ?><?= e($label) ?></span></label>
            <?php endforeach; ?>
          </div>
        </fieldset>
      </section>

      <!-- Address (like the Progressive AddressEdit step) -->
      <section class="qw-step" data-step="address" aria-labelledby="qs-address" hidden>
        <header class="qw-head">
          <p class="eyebrow"><?= icon('pin') ?> <?= e(L('Address', 'Dirección')) ?></p>
          <h3 id="qs-address">
            <span data-flow="auto"><?= e(L('Where are your vehicles kept?', '¿Dónde guardas tus vehículos?')) ?></span>
            <span data-only="home"><?= e(L('Where is the property?', '¿Dónde está la propiedad?')) ?></span>
            <span data-not="home" data-flow="details"><?= e(L('What\'s your address?', '¿Cuál es tu dirección?')) ?></span>
          </h3>
          <p data-flow="auto"><?= e(L('Rates depend on where your vehicles are parked overnight.', 'El precio depende de dónde se quedan tus vehículos en la noche.')) ?></p>
          <p data-only="home"><?= e(L('We\'ll use it to find the right coverage and price for the home.', 'La usamos para encontrar la cobertura y el precio correctos.')) ?></p>
        </header>
        <div class="grid g3">
          <?= $fld(e(L('ZIP code', 'Código postal')), '<span class="fld-wrap"><input name="address.zip" inputmode="numeric" autocomplete="postal-code" maxlength="5" placeholder="78753" required data-zip><span class="fld-spin" aria-hidden="true"></span></span>', '', e(L('We\'ll fill in your city and state.', 'Llenamos tu ciudad y estado.'))) ?>
          <?= $fld(e(L('Street address', 'Calle y número')) . ' <i data-street-opt>' . e(L('(optional)', '(opcional)')) . '</i>', '<input name="address.street" autocomplete="address-line1" maxlength="120" placeholder="' . e(L('123 Main St', '123 Main St')) . '">', 'span2') ?>
          <?= $fld(e(L('Apt / unit', 'Apto / unidad')) . ' ' . $opt, '<input name="address.unit" autocomplete="address-line2" maxlength="30">') ?>
          <?= $fld(e(L('City', 'Ciudad')), '<input name="address.city" autocomplete="address-level2" maxlength="80" required>') ?>
          <?= $fld(e(L('State', 'Estado')), '<select name="address.state" autocomplete="address-level1" required>' . $stateOptions(L('Choose…', 'Elige…')) . '</select>') ?>
        </div>
        <fieldset class="seg-fld" data-only="personal_auto">
          <legend><?= e(L('Do you own or rent your home?', '¿Eres dueño o rentas tu casa?')) ?> <span class="legend-h"><?= e(L('Homeowners often get a better price on auto insurance.', 'Los dueños de casa muchas veces consiguen mejor precio en su seguro de auto.')) ?></span></legend>
          <div class="seg seg-2">
            <label><input type="radio" name="address.residence" value="own"><span><?= icon('home') ?><?= e(L('I own', 'Soy dueño')) ?></span></label>
            <label><input type="radio" name="address.residence" value="rent"><span><?= icon('key') ?><?= e(L('I rent', 'Rento')) ?></span></label>
          </div>
          <span class="fld-e" aria-live="polite"></span>
        </fieldset>
      </section>

      <!-- Drivers -->
      <section class="qw-step" data-step="drivers" aria-labelledby="qs-drivers" hidden>
        <header class="qw-head">
          <p class="eyebrow"><?= icon('users') ?> <?= e(L('Drivers', 'Conductores')) ?></p>
          <h3 id="qs-drivers"><?= e(L('Who will be driving?', '¿Quién va a manejar?')) ?></h3>
          <p><?= e(L('Add everyone who drives these vehicles. No license? No problem: we accept any type of ID.', 'Agrega a todos los que manejan estos vehículos. ¿Sin licencia? No hay problema: aceptamos cualquier tipo de ID.')) ?></p>
        </header>
        <div class="qw-list" data-list="drivers"></div>
        <button class="qw-add" type="button" data-add="drivers"><span class="qw-add-plus"><?= icon('plus') ?></span><span><?= e(L('Add another driver', 'Agregar otro conductor')) ?></span><small data-count="drivers"></small></button>
        <label class="done-box"><input type="checkbox" data-done="drivers"><span class="consent-box" aria-hidden="true"><?= icon('check') ?></span><span><b><?= e(L('No more drivers to add', 'No hay más conductores')) ?></b><small><?= e(L('Everyone who drives these vehicles is listed above.', 'Todos los que manejan estos vehículos están en la lista.')) ?></small></span></label>
        <span class="fld-e done-e" data-done-e="drivers" aria-live="polite"></span>
        <p class="qw-secure"><?= icon('lock') ?> <?= e(L('ID numbers are encrypted and only visible to your agent.', 'Los números de ID están cifrados y solo los ve tu agente.')) ?></p>
      </section>

      <!-- Vehicles -->
      <section class="qw-step" data-step="vehicles" aria-labelledby="qs-vehicles" hidden>
        <header class="qw-head">
          <p class="eyebrow"><?= icon('car') ?> <?= e(L('Vehicles', 'Vehículos')) ?></p>
          <h3 id="qs-vehicles"><?= e(L('Which vehicles do you want covered?', '¿Qué vehículos quieres asegurar?')) ?></h3>
          <p><?= e(L('Just enter the VIN and we\'ll identify the vehicle. Then pick the coverage for each one.', 'Solo escribe el VIN y nosotros identificamos el vehículo. Luego elige la cobertura de cada uno.')) ?></p>
        </header>
        <details class="tip">
          <summary><?= icon('info') ?> <?= e(L('Where do I find my VIN?', '¿Dónde encuentro mi VIN?')) ?></summary>
          <div class="tip-body">
            <div class="tip-art" aria-hidden="true">
              <svg viewBox="0 0 220 90"><rect x="6" y="40" width="208" height="36" rx="12" fill="var(--brand-100)"/><path d="M30 40 52 14h104l26 26" fill="var(--brand-100)" stroke="var(--brand-600)" stroke-width="2"/><path d="M60 38 72 20h38v18Z M118 20h34l18 18h-52Z" fill="#fff"/><circle cx="56" cy="76" r="12" fill="var(--navy-900)"/><circle cx="166" cy="76" r="12" fill="var(--navy-900)"/><circle cx="66" cy="34" r="6" fill="var(--amber-400)"><animate attributeName="r" values="5;8;5" dur="1.6s" repeatCount="indefinite"/></circle><circle cx="114" cy="56" r="6" fill="var(--amber-400)"><animate attributeName="r" values="5;8;5" dur="1.6s" begin=".5s" repeatCount="indefinite"/></circle></svg>
            </div>
            <ul>
              <li><b><?= e(L('Dashboard:', 'Tablero:')) ?></b> <?= e(L('look through the windshield on the driver\'s side.', 'mira por el parabrisas del lado del conductor.')) ?></li>
              <li><b><?= e(L('Door jamb:', 'Marco de la puerta:')) ?></b> <?= e(L('on the sticker inside the driver\'s door.', 'en la etiqueta dentro de la puerta del conductor.')) ?></li>
              <li><b><?= e(L('Paperwork:', 'Papeles:')) ?></b> <?= e(L('your registration, title or insurance card.', 'tu registro, título o tarjeta de seguro.')) ?></li>
            </ul>
          </div>
        </details>
        <div class="qw-list" data-list="vehicles"></div>
        <button class="qw-add" type="button" data-add="vehicles"><span class="qw-add-plus"><?= icon('plus') ?></span><span><?= e(L('Add another vehicle', 'Agregar otro vehículo')) ?></span><small data-count="vehicles"></small></button>
        <label class="done-box"><input type="checkbox" data-done="vehicles"><span class="consent-box" aria-hidden="true"><?= icon('check') ?></span><span><b><?= e(L('No more vehicles to add', 'No hay más vehículos')) ?></b><small><?= e(L('All the vehicles you want covered are listed above.', 'Todos los vehículos que quieres asegurar están en la lista.')) ?></small></span></label>
        <span class="fld-e done-e" data-done-e="vehicles" aria-live="polite"></span>
      </section>

      <!-- Coverage -->
      <section class="qw-step" data-step="coverage" aria-labelledby="qs-coverage" hidden>
        <header class="qw-head">
          <p class="eyebrow"><?= icon('shield') ?> <?= e(L('Coverage', 'Cobertura')) ?></p>
          <h3 id="qs-coverage"><?= e(L('How much protection do you want?', '¿Cuánta protección quieres?')) ?></h3>
          <p><?= e(L('Not sure? Pick what feels right. Your agent will walk you through the options.', '¿No estás seguro? Elige lo que te parezca mejor. Tu agente te explicará las opciones.')) ?></p>
        </header>

        <fieldset class="seg-fld" data-only="commercial_auto">
          <legend><?= e(L('Liability limits', 'Límites de responsabilidad civil')) ?> <span class="legend-h"><?= e(L('Pays for damage and injuries you cause to others', 'Paga los daños y lesiones que causes a otros')) ?></span></legend>
          <div class="opt-grid" data-liability data-sets="<?= e(json_encode($liabilitySets, JSON_UNESCAPED_UNICODE)) ?>"></div>
          <span class="fld-e" aria-live="polite"></span>
        </fieldset>

        <fieldset class="seg-fld">
          <legend><?= e(L('Extra protection for everyone on the policy', 'Protección extra para todos en la póliza')) ?></legend>
          <div class="toggle-grid">
            <label class="toggle-card">
              <input type="checkbox" name="coverage.pip">
              <span class="tc-icon"><?= icon('heart') ?></span>
              <span class="tc-text"><b><?= e(L('Personal injury protection (PIP)', 'Protección contra lesiones (PIP)')) ?></b><small><?= e(L('Medical bills and lost wages for you and your passengers, no matter who\'s at fault.', 'Gastos médicos y salario perdido para ti y tus pasajeros, sin importar quién tuvo la culpa.')) ?></small></span>
              <span class="switch" aria-hidden="true"></span>
            </label>
            <label class="toggle-card">
              <input type="checkbox" name="coverage.um">
              <span class="tc-icon"><?= icon('umbrella') ?></span>
              <span class="tc-text"><b><?= e(L('Uninsured / underinsured motorist', 'Conductor sin seguro')) ?></b><small><?= e(L('Protects you when the other driver has no insurance, or not enough.', 'Te protege cuando el otro conductor no tiene seguro, o no le alcanza.')) ?></small></span>
              <span class="switch" aria-hidden="true"></span>
            </label>
          </div>
        </fieldset>

        <fieldset class="seg-fld">
          <legend><?= e(L('A few more details', 'Unos detalles más')) ?> <?= $opt ?></legend>
          <div class="grid g2">
            <?= $fld(e(L('When should coverage start?', '¿Cuándo debe empezar la cobertura?')), '<input name="coverage.start_date" type="date">') ?>
            <div class="fld">
              <span class="fld-l"><?= e(L('Currently insured?', '¿Tienes seguro ahora?')) ?></span>
              <div class="seg seg-2">
                <label><input type="radio" name="coverage.currently_insured" value="yes"><span><?= e(L('Yes', 'Sí')) ?></span></label>
                <label><input type="radio" name="coverage.currently_insured" value="no"><span><?= e(L('No', 'No')) ?></span></label>
              </div>
            </div>
            <?= $fld(e(L('Current insurance company', 'Compañía de seguro actual')), '<input name="coverage.current_carrier" maxlength="60" placeholder="' . e(L('e.g. State Farm', 'Ej. State Farm')) . '">', 'if-insured', '', ' hidden') ?>
            <?= $fld(e(L('When does it expire?', '¿Cuándo vence?')), '<input name="coverage.current_expiration" type="date">', 'if-insured', e(L('So your new policy starts right on time.', 'Para que tu nueva póliza empiece a tiempo.')), ' hidden') ?>
            <?= $fld(e(L('Anything else we should know?', '¿Algo más que debamos saber?')), '<textarea name="coverage.comments" rows="3" maxlength="1000" placeholder="' . e(L('Recent tickets or accidents, SR-22, discounts you\'re asking about, best time to call…', 'Multas o accidentes recientes, SR-22, descuentos, mejor hora para llamarte…')) . '"></textarea>', 'span2', icon('alert') . ' <b>' . e(L('Let us know if you need an SR-22.', 'Avísanos si necesitas un SR-22.')) . '</b>') ?>
          </div>
        </fieldset>
      </section>

      <!-- Details (non-auto services) -->
      <section class="qw-step" data-step="details" aria-labelledby="qs-details" hidden>
        <?php foreach ($services as $key => $s): if ($s['flow'] !== 'details') continue; ?>
        <div class="qw-details" data-details="<?= e($key) ?>" hidden
             data-fields="<?= e(json_encode(array_map(fn ($f) => ['key' => $f['key'], 'type' => $f['type'], 'optional' => !empty($f['optional']), 'pattern' => isset($f['pattern']) ? trim($f['pattern'], '/') : null, 'show_if' => $f['show_if'] ?? null], $s['fields']))) ?>">
          <header class="qw-head">
            <p class="eyebrow"><?= icon($s['icon']) ?> <?= e($s['title']) ?></p>
            <h3 id="qs-details-<?= e($key) ?>"><?= e(L('Tell us what you need', 'Cuéntanos qué necesitas')) ?></h3>
            <p><?= e($s['short']) ?></p>
          </header>
          <div class="grid g2">
            <?php foreach ($s['fields'] as $f):
                $name = 'details.' . $key . '.' . $f['key'];
                $label = e($f['label']) . (!empty($f['optional']) ? ' ' . $opt : '');
                $hint = e($f['hint'] ?? '');
                $dataLabel = ' data-label="' . e($f['label']) . '"' . (!empty($f['show_if']) ? ' data-show-if="' . e(json_encode($f['show_if'])) . '" hidden' : '');
                switch ($f['type']):
                    case 'radio': ?>
            <fieldset class="seg-fld span2"<?= $dataLabel ?>>
              <legend><?= $label ?><?= $hint ? ' <span class="legend-h">' . $hint . '</span>' : '' ?></legend>
              <div class="pick-grid">
                <?php foreach ($f['options'] as $val => $olabel): ?>
                <label class="pick"><input type="radio" name="<?= e($name) ?>" value="<?= e($val) ?>"><span><?= e($olabel) ?></span></label>
                <?php endforeach; ?>
              </div>
              <span class="fld-e" aria-live="polite"></span>
            </fieldset>
            <?php break; case 'checks': ?>
            <fieldset class="seg-fld span2"<?= $dataLabel ?>>
              <legend><?= $label ?></legend>
              <div class="pick-grid">
                <?php foreach ($f['options'] as $val => $olabel): ?>
                <label class="pick pick-check"><input type="checkbox" name="<?= e($name) ?>" value="<?= e($val) ?>"><span><i class="pick-box" aria-hidden="true"><?= icon('check') ?></i><?= e($olabel) ?></span></label>
                <?php endforeach; ?>
              </div>
              <span class="fld-e" aria-live="polite"></span>
            </fieldset>
            <?php break; case 'select': ?>
            <?= $fld($label, '<select name="' . e($name) . '"><option value="">' . e(L('Choose…', 'Elige…')) . '</option>' . implode('', array_map(fn ($v, $l) => '<option value="' . e($v) . '">' . e($l) . '</option>', array_keys($f['options']), $f['options'])) . '</select>', '', $hint, $dataLabel) ?>
            <?php break; case 'textarea': ?>
            <?= $fld($label, '<textarea name="' . e($name) . '" rows="3" maxlength="1000" placeholder="' . e($f['placeholder'] ?? '') . '"></textarea>', 'span2', $hint, $dataLabel) ?>
            <?php break; case 'vin': ?>
            <?= $fld($label, '<span class="fld-wrap vin-wrap"><span class="vin-ic">' . icon('scan') . '</span><input name="' . e($name) . '" maxlength="17" autocomplete="off" autocapitalize="characters" spellcheck="false" placeholder="' . e(L('17 letters and numbers', '17 letras y números')) . '" class="mono" data-vin-field></span>', 'span2 fld-vin', e($f['hint'] ?? ''), $dataLabel) ?>
            <div class="vin-res span2" data-vinres-field="<?= e($name) ?>" aria-live="polite"<?= !empty($f['show_if']) ? ' data-show-if="' . e(json_encode($f['show_if'])) . '"' : '' ?>></div>
            <?php break; default: ?>
            <?= $fld($label, '<input name="' . e($name) . '" maxlength="' . (int) ($f['maxlength'] ?? 120) . '"' . (!empty($f['inputmode']) ? ' inputmode="' . e($f['inputmode']) . '"' : '') . ' placeholder="' . e($f['placeholder'] ?? '') . '"' . (!empty($f['pattern']) ? ' data-pattern="' . e(trim($f['pattern'], '/')) . '"' : '') . '>', '', $hint, $dataLabel) ?>
            <?php endswitch; endforeach; ?>
          </div>
        </div>
        <?php endforeach; ?>
      </section>

      <!-- Review -->
      <section class="qw-step" data-step="review" aria-labelledby="qs-review" hidden>
        <header class="qw-head">
          <p class="eyebrow"><?= icon('doc') ?> <?= e(L('Review', 'Revisar')) ?></p>
          <h3 id="qs-review"><?= e(L('Does everything look right?', '¿Todo está correcto?')) ?></h3>
          <p><?= e(L('Check your details, then send your request. You can edit any section.', 'Revisa tus datos y envía tu solicitud. Puedes editar cualquier sección.')) ?></p>
        </header>
        <div class="rv" data-review></div>
        <label class="consent">
          <input type="checkbox" name="consent">
          <span class="consent-box" aria-hidden="true"><?= icon('check') ?></span>
          <span><?= e(L(
              'I agree that EZ Way Insurance may contact me by phone, text or email about this request. I confirm the information is accurate to the best of my knowledge. See our',
              'Acepto que EZ Way Insurance me contacte por teléfono, mensaje de texto o correo sobre esta solicitud. Confirmo que la información es correcta según mi conocimiento. Ver nuestra'
          )) ?> <a href="<?= e(lurl('privacy')) ?>" target="_blank" rel="noopener"><?= e(L('privacy policy', 'política de privacidad')) ?></a>.</span>
        </label>
        <span class="fld-e consent-e" aria-live="polite"></span>
        <div class="ts-box" data-turnstile-box></div>
        <div class="qw-alert" data-qw-alert role="alert" hidden></div>
      </section>
    </div>

    <footer class="qw-foot">
      <button class="btn btn-ghost" type="button" data-back><?= icon('arrow-left') ?><span><?= e(L('Back', 'Atrás')) ?></span></button>
      <span class="qw-saved" data-saved><?= icon('check') ?> <?= e(L('Progress saved', 'Progreso guardado')) ?></span>
      <button class="btn btn-primary" type="submit" data-next><span data-next-label><?= e(L('Continue', 'Continuar')) ?></span><?= icon('arrow-right') ?><span class="btn-spin" aria-hidden="true"></span></button>
    </footer>
  </form>

  <!-- Success -->
  <div class="qw-done" data-qw-done hidden tabindex="-1">
    <canvas class="confetti" data-confetti aria-hidden="true"></canvas>
    <svg class="done-check" viewBox="0 0 80 80" aria-hidden="true"><circle cx="40" cy="40" r="36"/><path d="m25 41 10 10 21-22"/></svg>
    <h3><?= e(L('Your request is in!', '¡Recibimos tu solicitud!')) ?></h3>
    <p class="done-ref"><?= e(L('Reference', 'Referencia')) ?> <b data-qw-ref></b></p>
    <p><?= e(L('Thanks,', 'Gracias,')) ?> <span data-qw-first></span>. <?= e(L('A licensed agent is reviewing your details now. We\'ve also sent a confirmation to your email.', 'Un agente con licencia ya está revisando tus datos. También te enviamos una confirmación a tu correo.')) ?></p>
    <ol class="done-steps">
      <li><span><?= icon('search') ?></span><div><b><?= e(L('We shop your coverage', 'Comparamos por ti')) ?></b><small><?= e(L('Your agent compares ' . cfg('site.carriers') . '+ insurance companies for you.', 'Tu agente compara más de ' . cfg('site.carriers') . ' compañías de seguros por ti.')) ?></small></div></li>
      <li><span><?= icon('phone') ?></span><div><b><?= e(L('We reach out', 'Te contactamos')) ?></b><small><?= e(L('Usually within one business day, the way you prefer.', 'Normalmente en un día hábil, como tú prefieras.')) ?></small></div></li>
      <li><span><?= icon('shield') ?></span><div><b><?= e(L('You choose', 'Tú decides')) ?></b><small><?= e(L('No pressure and no obligation. Pick what works for you.', 'Sin presión ni compromiso. Elige lo que te convenga.')) ?></small></div></li>
    </ol>
    <div class="done-actions">
      <a class="btn btn-ghost" href="<?= e(tel_href(cfg('site.phone'))) ?>"><?= icon('phone') ?> <?= e(L('Call', 'Llamar al')) ?> <?= e(cfg('site.phone')) ?></a>
      <button class="btn btn-primary" type="button" data-qw-finish><?= e(L('Done', 'Listo')) ?></button>
    </div>
  </div>
</div>

<template id="tpl-driver">
  <article class="qw-card" data-item>
    <header class="qw-card-head">
      <span class="qw-avatar" data-avatar><?= icon('user') ?></span>
      <div class="qw-card-t"><h4 data-title><?= e(L('Driver', 'Conductor')) ?></h4><p data-subtitle></p></div>
      <button class="qw-remove" type="button" data-remove aria-label="<?= e(L('Remove this driver', 'Quitar este conductor')) ?>"><?= icon('trash') ?></button>
    </header>
    <div class="grid g2">
      <?= $fld(e(L('First name', 'Nombre')), '<input data-f="first" autocomplete="off" maxlength="60">') ?>
      <?= $fld(e(L('Last name', 'Apellido')), '<input data-f="last" autocomplete="off" maxlength="60">') ?>
      <?= $fld(e(L('Date of birth', 'Fecha de nacimiento')), '<input data-f="dob" inputmode="numeric" placeholder="' . e(L('MM/DD/YYYY', 'MM/DD/AAAA')) . '" maxlength="10" data-mask="date" autocomplete="off">') ?>
      <?= $fld(e(L('Relationship to you', 'Parentesco contigo')), '<select data-f="relationship"><option value="">' . e(L('Choose…', 'Elige…')) . '</option>' . implode('', array_map(fn ($k, $v) => $k === 'self' ? '' : '<option value="' . $k . '">' . e($v) . '</option>', array_keys(relationships()), relationships())) . '</select>', 'only-extra') ?>
    </div>
    <fieldset class="seg-fld">
      <legend><?= e(L('ID type', 'Tipo de identificación')) ?></legend>
      <div class="seg seg-2 seg-id">
        <label><input type="radio" data-f="id_type" value="us"><span><?= icon('flag') ?><?= e(L('US license', 'Licencia EE. UU.')) ?></span></label>
        <label><input type="radio" data-f="id_type" value="other"><span><?= icon('id') ?><?= e(L('Other ID', 'Otra ID')) ?></span></label>
      </div>
      <span class="fld-e" aria-live="polite"></span>
    </fieldset>
    <p class="id-note show-other"><?= icon('check') ?> <?= e(L('No license? No problem. A foreign license, state ID, passport, matrícula consular or any other ID works.', '¿Sin licencia? No hay problema. Sirve licencia extranjera, ID del estado, pasaporte, matrícula consular o cualquier otra ID.')) ?></p>
    <div class="grid g2">
      <?= $fld(e(L('Type of ID', 'Tipo de ID')), '<select data-f="id_kind"><option value="">' . e(L('Choose…', 'Elige…')) . '</option>' . implode('', array_map(fn ($k, $v) => '<option value="' . $k . '">' . e($v) . '</option>', array_keys(id_kinds()), id_kinds())) . '</select>', 'show-other') ?>
      <?= $fld(e(L('Issuing state', 'Estado que la emitió')), '<select data-f="license_state">' . $stateOptions(L('Choose…', 'Elige…')) . '</select>', 'show-state', '', ' hidden') ?>
      <?= $fld(e(L('Issuing country', 'País que la emitió')), '<input data-f="license_country" list="qw-countries" maxlength="60" autocomplete="off" placeholder="' . e(L('e.g. Mexico', 'Ej. México')) . '">', 'show-country', '', ' hidden') ?>
      <?= $fld('<span class="hide-other">' . e(L('License number', 'Número de licencia')) . '</span><span class="show-other">' . e(L('ID number', 'Número de ID')) . ' ' . $opt . '</span>', '<span class="fld-wrap"><input data-f="license_number" maxlength="25" autocomplete="off" autocapitalize="characters" spellcheck="false" class="mono"><span class="fld-lock">' . icon('lock') . '</span></span>') ?>
    </div>
  </article>
</template>

<template id="tpl-vehicle">
  <article class="qw-card" data-item>
    <header class="qw-card-head">
      <span class="qw-avatar qw-avatar-car" data-avatar><?= icon('car') ?></span>
      <div class="qw-card-t"><h4 data-title><?= e(L('Vehicle', 'Vehículo')) ?></h4><p data-subtitle></p></div>
      <button class="qw-remove" type="button" data-remove aria-label="<?= e(L('Remove this vehicle', 'Quitar este vehículo')) ?>"><?= icon('trash') ?></button>
    </header>
    <div class="vin-row">
      <?= $fld(e(L('VIN (vehicle identification number)', 'VIN (número de identificación del vehículo)')), '<span class="fld-wrap vin-wrap"><span class="vin-ic">' . icon('scan') . '</span><input data-f="vin" maxlength="17" autocomplete="off" autocapitalize="characters" spellcheck="false" placeholder="' . e(L('17 letters and numbers', '17 letras y números')) . '" class="mono"><span class="vin-count" data-vincount>0/17</span></span>', 'fld-vin') ?>
      <div class="vin-res" data-vinres aria-live="polite"></div>
    </div>
    <p class="vcov-l"><?= e(L('Coverage for this vehicle', 'Cobertura para este vehículo')) ?></p>
    <div class="vcov">
      <div class="chk chk-liab" data-liab-chip role="checkbox" aria-checked="true" aria-disabled="true" title="<?= e(L('Liability is required in Texas and always included', 'La responsabilidad civil es obligatoria en Texas y siempre está incluida')) ?>">
        <span class="chk-box" aria-hidden="true"><?= icon('check') ?></span>
        <span class="chk-ic"><?= icon('scale') ?></span>
        <span class="chk-t"><b><?= e(L('Liability · Texas minimum', 'Responsabilidad civil · Mínimo de Texas')) ?></b><small><?= e(L('30/60/25 · Cheapest price possible', '30/60/25 · El precio más barato posible')) ?></small></span>
        <span class="chk-incl"><?= icon('lock') ?><?= e(L('Always included', 'Siempre incluido')) ?></span>
      </div>
      <label class="chk">
        <input type="checkbox" data-f="full_coverage">
        <span class="chk-box" aria-hidden="true"><?= icon('check') ?></span>
        <span class="chk-ic"><?= icon('shield-plus') ?></span>
        <span class="chk-t"><b><?= e(L('Full coverage', 'Cobertura completa')) ?></b><small><?= e(L('Comprehensive + collision', 'Comprensivo + colisión')) ?></small></span>
      </label>
      <label class="chk">
        <input type="checkbox" data-f="roadside">
        <span class="chk-box" aria-hidden="true"><?= icon('check') ?></span>
        <span class="chk-ic"><?= icon('wrench') ?></span>
        <span class="chk-t"><b><?= e(L('Roadside', 'Asistencia')) ?></b><small><?= e(L('Towing, jump-starts, lockouts', 'Grúa, corriente, llaves')) ?></small></span>
      </label>
      <label class="chk">
        <input type="checkbox" data-f="rental">
        <span class="chk-box" aria-hidden="true"><?= icon('check') ?></span>
        <span class="chk-ic"><?= icon('key') ?></span>
        <span class="chk-t"><b><?= e(L('Rental', 'Auto de renta')) ?></b><small><?= e(L('A car while yours is repaired', 'Un auto mientras reparan el tuyo')) ?></small></span>
      </label>
    </div>
    <div class="full-extra" data-full>
      <div class="deduct">
        <span class="deduct-l"><?= e(L('Comp / collision deductible', 'Deducible de comprensivo / colisión')) ?></span>
        <div class="seg seg-4 seg-sm">
          <?php foreach (deductibles() as $val => $label): ?>
          <label><input type="radio" data-f="deductible" value="<?= $val ?>"><span><?= e($label) ?></span></label>
          <?php endforeach; ?>
        </div>
      </div>
      <fieldset class="seg-fld lien">
        <legend><?= icon('doc') ?> <?= e(L('Is this vehicle financed or leased?', '¿Este vehículo está financiado o en leasing?')) ?> <span class="legend-h"><?= e(L('Your lender (lienholder) needs to be listed on the policy.', 'Tu financiera (lienholder) debe aparecer en la póliza.')) ?></span></legend>
        <div class="seg seg-2 seg-sm">
          <label><input type="radio" data-f="lien" value="yes"><span><?= e(L('Yes, it\'s financed', 'Sí, está financiado')) ?></span></label>
          <label><input type="radio" data-f="lien" value="no"><span><?= e(L('No, it\'s paid off', 'No, ya está pagado')) ?></span></label>
        </div>
        <span class="fld-e" aria-live="polite"></span>
      </fieldset>
      <?= $fld(e(L('Lienholder (bank or finance company)', 'Financiera o banco (lienholder)')) . ' ' . $opt, '<input data-f="lienholder" maxlength="80" autocomplete="off" placeholder="' . e(L('e.g. Capital One Auto Finance', 'Ej. Capital One Auto Finance')) . '">', 'show-lien') ?>
    </div>
  </article>
</template>

<datalist id="qw-countries">
  <?php foreach ($countries as $c): ?><option value="<?= e($c) ?>"><?php endforeach; ?>
</datalist>
