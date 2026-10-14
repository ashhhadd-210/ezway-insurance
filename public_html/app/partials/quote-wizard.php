<?php
defined('APP') or exit;
/** @var string $qwMode 'modal' or 'page' */
$qwMode ??= 'modal';

$stepNames = ['You', 'Address', 'Drivers', 'Vehicles', 'Coverage', 'Review'];
$countries = ['Mexico', 'Canada', 'Guatemala', 'Honduras', 'El Salvador', 'Nicaragua', 'Colombia', 'Venezuela', 'Cuba',
    'Dominican Republic', 'Haiti', 'Ecuador', 'Peru', 'Brazil', 'Argentina', 'India', 'Pakistan', 'Philippines', 'China',
    'Vietnam', 'South Korea', 'Nigeria', 'Ethiopia', 'United Kingdom', 'Germany', 'France', 'Italy', 'Spain', 'Ukraine', 'Russia'];

$stateOptions = function (string $placeholder): string {
    $out = '<option value="">' . e($placeholder) . '</option>';
    foreach (US_STATES as $code => $name) {
        $out .= '<option value="' . $code . '">' . e($name) . '</option>';
    }
    return $out;
};

$fld = function (string $label, string $input, string $class = '', string $hint = ''): string {
    return '<label class="fld ' . $class . '"><span class="fld-l">' . $label . '</span>' . $input
        . ($hint ? '<span class="fld-h">' . $hint . '</span>' : '')
        . '<span class="fld-e" aria-live="polite"></span></label>';
};
?>
<div class="qw qw-<?= e($qwMode) ?>" id="qw" data-qw data-mode="<?= e($qwMode) ?>"
     data-token="<?= e(form_token()) ?>" data-api="<?= e(url('api/')) ?>"
     data-turnstile="<?= e(turnstile_enabled() ? cfg('security.turnstile_site_key') : '') ?>"
     data-max-drivers="<?= MAX_DRIVERS ?>" data-max-vehicles="<?= MAX_VEHICLES ?>">

  <div class="qw-top">
    <div class="qw-title">
      <span class="qw-badge"><?= logo_mark('qw-mark') ?></span>
      <div>
        <h2 id="qwTitle">Your auto quote</h2>
        <p data-qw-steplabel>Step 1 of 6 · about 3 minutes</p>
      </div>
    </div>
    <?php if ($qwMode === 'modal'): ?>
    <button class="qw-close" type="button" data-qclose aria-label="Close quote form"><?= icon('x') ?></button>
    <?php endif; ?>
  </div>

  <ol class="qw-steps" data-qw-steps>
    <?php foreach ($stepNames as $i => $name): ?>
    <li data-s="<?= $i ?>"><button type="button" tabindex="-1" disabled><span class="qw-dot"><span><?= $i + 1 ?></span><?= icon('check') ?></span><span class="qw-sl"><?= e($name) ?></span></button></li>
    <?php endforeach; ?>
  </ol>
  <div class="qw-bar" aria-hidden="true"><span data-qw-bar></span></div>

  <form class="qw-form" data-qw-form novalidate>
    <div class="hp-wrap" aria-hidden="true">
      <label>Leave this empty<input type="text" name="website" tabindex="-1" autocomplete="off"></label>
    </div>

    <div class="qw-viewport" data-qw-viewport>

      <!-- 1. You -->
      <section class="qw-step" data-step="0" aria-labelledby="qs0">
        <header class="qw-head">
          <p class="eyebrow"><?= icon('user') ?> Step 1 · About you</p>
          <h3 id="qs0">Let's start with you</h3>
          <p>We'll use this to prepare your quote and to reach you with your options.</p>
        </header>
        <div class="grid g2">
          <?= $fld('First name', '<input name="applicant.first" autocomplete="given-name" maxlength="60" required>') ?>
          <?= $fld('Last name', '<input name="applicant.last" autocomplete="family-name" maxlength="60" required>') ?>
          <?= $fld('Date of birth', '<input name="applicant.dob" inputmode="numeric" autocomplete="bday" placeholder="MM/DD/YYYY" maxlength="10" data-mask="date" required>') ?>
          <?= $fld('Phone', '<input name="applicant.phone" type="tel" inputmode="tel" autocomplete="tel-national" placeholder="(555) 555-5555" maxlength="14" data-mask="phone" required>') ?>
          <?= $fld('Email', '<input name="applicant.email" type="email" autocomplete="email" maxlength="160" placeholder="you@example.com" required>', 'span2') ?>
        </div>
        <fieldset class="seg-fld">
          <legend>Best way to reach you</legend>
          <div class="seg seg-3">
            <?php foreach (CONTACT_PREFS as $val => $label): ?>
            <label><input type="radio" name="applicant.contact_pref" value="<?= $val ?>"><span><?= icon(['phone' => 'phone', 'text' => 'chat', 'email' => 'mail'][$val]) ?><?= e($label) ?></span></label>
            <?php endforeach; ?>
          </div>
        </fieldset>
      </section>

      <!-- 2. Address (like the Progressive AddressEdit step) -->
      <section class="qw-step" data-step="1" aria-labelledby="qs1" hidden>
        <header class="qw-head">
          <p class="eyebrow"><?= icon('pin') ?> Step 2 · Address</p>
          <h3 id="qs1">What's your home address?</h3>
          <p>Rates depend on where your vehicles are kept overnight.</p>
        </header>
        <div class="grid g3">
          <?= $fld('ZIP code', '<span class="fld-wrap"><input name="address.zip" inputmode="numeric" autocomplete="postal-code" maxlength="5" placeholder="77002" required data-zip><span class="fld-spin" aria-hidden="true"></span></span>', '', 'We\'ll fill in your city and state.') ?>
          <?= $fld('Street address', '<input name="address.street" autocomplete="address-line1" maxlength="120" placeholder="123 Main St" required>', 'span2') ?>
          <?= $fld('Apt / unit <i>(optional)</i>', '<input name="address.unit" autocomplete="address-line2" maxlength="30">') ?>
          <?= $fld('City', '<input name="address.city" autocomplete="address-level2" maxlength="80" required>') ?>
          <?= $fld('State', '<select name="address.state" autocomplete="address-level1" required>' . $stateOptions('Choose…') . '</select>') ?>
        </div>
      </section>

      <!-- 3. Drivers -->
      <section class="qw-step" data-step="2" aria-labelledby="qs2" hidden>
        <header class="qw-head">
          <p class="eyebrow"><?= icon('users') ?> Step 3 · Drivers</p>
          <h3 id="qs2">Who will be driving?</h3>
          <p>Add everyone in your household who drives your vehicles. Use <b>+ Add another driver</b> for each one.</p>
        </header>
        <div class="qw-list" data-list="drivers"></div>
        <button class="qw-add" type="button" data-add="drivers"><span class="qw-add-plus"><?= icon('plus') ?></span><span>Add another driver</span><small data-count="drivers"></small></button>
        <p class="qw-secure"><?= icon('lock') ?> License numbers are encrypted and only visible to your agent.</p>
      </section>

      <!-- 4. Vehicles -->
      <section class="qw-step" data-step="3" aria-labelledby="qs3" hidden>
        <header class="qw-head">
          <p class="eyebrow"><?= icon('car') ?> Step 4 · Vehicles</p>
          <h3 id="qs3">Which vehicles do you want covered?</h3>
          <p>Just enter the VIN and we'll identify the vehicle. Then pick the coverage for each one.</p>
        </header>
        <details class="tip">
          <summary><?= icon('info') ?> Where do I find my VIN?</summary>
          <div class="tip-body">
            <div class="tip-art" aria-hidden="true">
              <svg viewBox="0 0 220 90"><rect x="6" y="40" width="208" height="36" rx="12" fill="var(--brand-100)"/><path d="M30 40 52 14h104l26 26" fill="var(--brand-100)" stroke="var(--brand-600)" stroke-width="2"/><path d="M60 38 72 20h38v18Z M118 20h34l18 18h-52Z" fill="#fff"/><circle cx="56" cy="76" r="12" fill="var(--navy-900)"/><circle cx="166" cy="76" r="12" fill="var(--navy-900)"/><circle cx="66" cy="34" r="6" fill="var(--amber-400)"><animate attributeName="r" values="5;8;5" dur="1.6s" repeatCount="indefinite"/></circle><circle cx="114" cy="56" r="6" fill="var(--amber-400)"><animate attributeName="r" values="5;8;5" dur="1.6s" begin=".5s" repeatCount="indefinite"/></circle></svg>
            </div>
            <ul>
              <li><b>Dashboard:</b> look through the windshield on the driver's side.</li>
              <li><b>Door jamb:</b> on the sticker inside the driver's door.</li>
              <li><b>Paperwork:</b> your registration, title or current insurance card.</li>
            </ul>
          </div>
        </details>
        <div class="qw-list" data-list="vehicles"></div>
        <button class="qw-add" type="button" data-add="vehicles"><span class="qw-add-plus"><?= icon('plus') ?></span><span>Add another vehicle</span><small data-count="vehicles"></small></button>
      </section>

      <!-- 5. Coverage -->
      <section class="qw-step" data-step="4" aria-labelledby="qs4" hidden>
        <header class="qw-head">
          <p class="eyebrow"><?= icon('shield') ?> Step 5 · Coverage</p>
          <h3 id="qs4">How much protection do you want?</h3>
          <p>Not sure? Pick what feels right. Your agent will walk you through the options.</p>
        </header>

        <fieldset class="seg-fld">
          <legend>Liability limits <span class="legend-h">Pays for damage and injuries you cause to others</span></legend>
          <div class="opt-grid">
            <?php foreach (LIABILITY_LEVELS as $val => [$label, $desc, $tag]): ?>
            <label class="opt">
              <input type="radio" name="coverage.liability" value="<?= $val ?>">
              <span class="opt-body">
                <span class="opt-radio" aria-hidden="true"></span>
                <b><?= e($label) ?></b>
                <small><?= e($desc) ?></small>
                <?php if ($tag): ?><em class="opt-tag"><?= e($tag) ?></em><?php endif; ?>
              </span>
            </label>
            <?php endforeach; ?>
          </div>
          <span class="fld-e" aria-live="polite"></span>
        </fieldset>

        <fieldset class="seg-fld">
          <legend>Extra protection for everyone on the policy</legend>
          <div class="toggle-grid">
            <label class="toggle-card">
              <input type="checkbox" name="coverage.pip">
              <span class="tc-icon"><?= icon('heart') ?></span>
              <span class="tc-text"><b>Personal injury protection (PIP)</b><small>Medical bills and lost wages for you and your passengers, no matter who's at fault.</small></span>
              <span class="switch" aria-hidden="true"></span>
            </label>
            <label class="toggle-card">
              <input type="checkbox" name="coverage.um">
              <span class="tc-icon"><?= icon('umbrella') ?></span>
              <span class="tc-text"><b>Uninsured / underinsured motorist</b><small>Protects you when the other driver has no insurance, or not enough.</small></span>
              <span class="switch" aria-hidden="true"></span>
            </label>
          </div>
        </fieldset>

        <fieldset class="seg-fld">
          <legend>A few more details <i>(optional)</i></legend>
          <div class="grid g2">
            <?= $fld('When should coverage start?', '<input name="coverage.start_date" type="date">') ?>
            <div class="fld">
              <span class="fld-l">Currently insured?</span>
              <div class="seg seg-2">
                <label><input type="radio" name="coverage.currently_insured" value="yes"><span>Yes</span></label>
                <label><input type="radio" name="coverage.currently_insured" value="no"><span>No</span></label>
              </div>
            </div>
            <?= $fld('Current insurance company', '<input name="coverage.current_carrier" maxlength="60" placeholder="e.g. State Farm">', 'span2', '') ?>
            <?= $fld('Anything else we should know?', '<textarea name="coverage.comments" rows="3" maxlength="1000" placeholder="Recent tickets or accidents, discounts you\'re asking about, best time to call…"></textarea>', 'span2') ?>
          </div>
        </fieldset>
      </section>

      <!-- 6. Review -->
      <section class="qw-step" data-step="5" aria-labelledby="qs5" hidden>
        <header class="qw-head">
          <p class="eyebrow"><?= icon('doc') ?> Step 6 · Review</p>
          <h3 id="qs5">Does everything look right?</h3>
          <p>Check your details, then send your request. You can edit any section.</p>
        </header>
        <div class="rv" data-review></div>
        <label class="consent">
          <input type="checkbox" name="consent">
          <span class="consent-box" aria-hidden="true"><?= icon('check') ?></span>
          <span>I agree that <?= e(cfg('site.name')) ?> may contact me by phone, text or email about this quote. I confirm the information is accurate to the best of my knowledge. See our <a href="<?= e(url('privacy')) ?>" target="_blank" rel="noopener">privacy policy</a>.</span>
        </label>
        <span class="fld-e consent-e" aria-live="polite"></span>
        <div class="ts-box" data-turnstile-box></div>
        <div class="qw-alert" data-qw-alert role="alert" hidden></div>
      </section>
    </div>

    <footer class="qw-foot">
      <button class="btn btn-ghost" type="button" data-back><?= icon('arrow-left') ?><span>Back</span></button>
      <span class="qw-saved" data-saved><?= icon('check') ?> Progress saved</span>
      <button class="btn btn-primary" type="submit" data-next><span data-next-label>Continue</span><?= icon('arrow-right') ?><span class="btn-spin" aria-hidden="true"></span></button>
    </footer>
  </form>

  <!-- Success -->
  <div class="qw-done" data-qw-done hidden tabindex="-1">
    <canvas class="confetti" data-confetti aria-hidden="true"></canvas>
    <svg class="done-check" viewBox="0 0 80 80" aria-hidden="true"><circle cx="40" cy="40" r="36"/><path d="m25 41 10 10 21-22"/></svg>
    <h3>Your quote request is in!</h3>
    <p class="done-ref">Reference <b data-qw-ref></b></p>
    <p>Thanks, <span data-qw-first></span>. A licensed agent is reviewing your details now. We've also sent a confirmation to your email.</p>
    <ol class="done-steps">
      <li><span><?= icon('search') ?></span><div><b>We shop your coverage</b><small>Your agent compares options for every driver and vehicle.</small></div></li>
      <li><span><?= icon('phone') ?></span><div><b>We reach out</b><small>Usually within one business day, the way you prefer.</small></div></li>
      <li><span><?= icon('shield') ?></span><div><b>You choose</b><small>No pressure and no obligation. Pick what works for you.</small></div></li>
    </ol>
    <div class="done-actions">
      <a class="btn btn-ghost" href="<?= e(tel_href(cfg('site.phone'))) ?>"><?= icon('phone') ?> Call <?= e(cfg('site.phone')) ?></a>
      <button class="btn btn-primary" type="button" data-qw-finish>Done</button>
    </div>
  </div>
</div>

<template id="tpl-driver">
  <article class="qw-card" data-item>
    <header class="qw-card-head">
      <span class="qw-avatar" data-avatar><?= icon('user') ?></span>
      <div class="qw-card-t"><h4 data-title>Driver</h4><p data-subtitle></p></div>
      <button class="qw-remove" type="button" data-remove aria-label="Remove this driver"><?= icon('trash') ?></button>
    </header>
    <div class="grid g2">
      <?= $fld('First name', '<input data-f="first" autocomplete="off" maxlength="60">') ?>
      <?= $fld('Last name', '<input data-f="last" autocomplete="off" maxlength="60">') ?>
      <?= $fld('Date of birth', '<input data-f="dob" inputmode="numeric" placeholder="MM/DD/YYYY" maxlength="10" data-mask="date" autocomplete="off">') ?>
      <?= $fld('Relationship to you', '<select data-f="relationship"><option value="">Choose…</option>' . implode('', array_map(fn ($k, $v) => $k === 'self' ? '' : '<option value="' . $k . '">' . e($v) . '</option>', array_keys(RELATIONSHIPS), RELATIONSHIPS)) . '</select>', 'only-extra') ?>
    </div>
    <fieldset class="seg-fld">
      <legend>ID type</legend>
      <div class="seg seg-2">
        <label><input type="radio" data-f="id_type" value="us"><span><?= icon('flag') ?>US license</span></label>
        <label><input type="radio" data-f="id_type" value="foreign"><span><?= icon('globe') ?>Foreign license</span></label>
      </div>
      <span class="fld-e" aria-live="polite"></span>
    </fieldset>
    <div class="grid g2">
      <?= $fld('Issuing state', '<select data-f="license_state">' . $stateOptions('Choose…') . '</select>', 'show-us') ?>
      <?= $fld('Issuing country', '<input data-f="license_country" list="qw-countries" maxlength="60" autocomplete="off" placeholder="e.g. Mexico">', 'show-foreign') ?>
      <?= $fld('License number', '<span class="fld-wrap"><input data-f="license_number" maxlength="25" autocomplete="off" autocapitalize="characters" spellcheck="false" class="mono"><span class="fld-lock">' . icon('lock') . '</span></span>') ?>
    </div>
  </article>
</template>

<template id="tpl-vehicle">
  <article class="qw-card" data-item>
    <header class="qw-card-head">
      <span class="qw-avatar qw-avatar-car" data-avatar><?= icon('car') ?></span>
      <div class="qw-card-t"><h4 data-title>Vehicle</h4><p data-subtitle>Enter the VIN to identify it</p></div>
      <button class="qw-remove" type="button" data-remove aria-label="Remove this vehicle"><?= icon('trash') ?></button>
    </header>
    <div class="vin-row">
      <?= $fld('VIN (vehicle identification number)', '<span class="fld-wrap vin-wrap"><span class="vin-ic">' . icon('scan') . '</span><input data-f="vin" maxlength="17" autocomplete="off" autocapitalize="characters" spellcheck="false" placeholder="17 letters and numbers" class="mono"><span class="vin-count" data-vincount>0/17</span></span>', 'fld-vin') ?>
      <div class="vin-res" data-vinres aria-live="polite"></div>
    </div>
    <p class="vcov-l">Coverage for this vehicle</p>
    <div class="vcov">
      <label class="chk">
        <input type="checkbox" data-f="full_coverage">
        <span class="chk-box" aria-hidden="true"><?= icon('check') ?></span>
        <span class="chk-ic"><?= icon('shield-plus') ?></span>
        <span class="chk-t"><b>Full coverage</b><small>Comprehensive + collision</small></span>
      </label>
      <label class="chk">
        <input type="checkbox" data-f="roadside">
        <span class="chk-box" aria-hidden="true"><?= icon('check') ?></span>
        <span class="chk-ic"><?= icon('wrench') ?></span>
        <span class="chk-t"><b>Roadside</b><small>Towing, jump-starts, lockouts</small></span>
      </label>
      <label class="chk">
        <input type="checkbox" data-f="rental">
        <span class="chk-box" aria-hidden="true"><?= icon('check') ?></span>
        <span class="chk-ic"><?= icon('key') ?></span>
        <span class="chk-t"><b>Rental</b><small>A car while yours is repaired</small></span>
      </label>
    </div>
    <div class="deduct" data-deduct>
      <span class="deduct-l">Comp / collision deductible</span>
      <div class="seg seg-4 seg-sm">
        <?php foreach (DEDUCTIBLES as $val => $label): ?>
        <label><input type="radio" data-f="deductible" value="<?= $val ?>"><span><?= e($label) ?></span></label>
        <?php endforeach; ?>
      </div>
    </div>
  </article>
</template>

<datalist id="qw-countries">
  <?php foreach ($countries as $c): ?><option value="<?= e($c) ?>"><?php endforeach; ?>
</datalist>
