<?php
require __DIR__ . '/app/bootstrap.php';

$page = [
    'slug'        => 'home',
    'path'        => '',
    'title'       => '',
    'description' => cfg('site.description'),
    'schema'      => [[
        '@context' => 'https://schema.org',
        '@type'    => 'WebSite',
        'name'     => cfg('site.name'),
        'url'      => site_url(),
    ]],
];
$cov = coverage_info();
require APP_ROOT . '/partials/header.php';
?>

<!-- ============ HERO ============ -->
<section class="hero">
  <div class="hero-bg" aria-hidden="true"><span class="blob b1"></span><span class="blob b2"></span><span class="grid-lines"></span><?= pinwheel_svg('pinwheel hero-pin') ?></div>
  <div class="wrap hero-in">
    <div class="hero-copy">
      <p class="pill reveal"><span class="pill-dot"></span>Licensed local agency · Free quotes</p>
      <h1 class="hero-h reveal">
        Insurance for every driver
        <span class="hl">&amp; every ride.<svg viewBox="0 0 300 20" preserveAspectRatio="none" aria-hidden="true"><path d="M3 15c60-9 140-13 294-6" /></svg></span>
      </h1>
      <p class="lead reveal">Add your whole household in one simple form: drivers, vehicles and the coverage you want. A licensed agent compares your options and gets back to you fast, with no obligation.</p>

      <form class="zip-form reveal" data-zipform>
        <label class="zip-field">
          <span class="sr">ZIP code</span>
          <?= icon('pin') ?>
          <input name="zip" inputmode="numeric" maxlength="5" placeholder="Enter your ZIP code" autocomplete="postal-code">
        </label>
        <button class="btn btn-primary btn-lg" type="submit">Start my quote <?= icon('arrow-right') ?></button>
      </form>

      <ul class="hero-ticks reveal">
        <li><?= icon('check') ?> No obligation</li>
        <li><?= icon('clock') ?> About 3 minutes</li>
        <li><?= icon('lock') ?> Encrypted &amp; secure</li>
      </ul>
    </div>

    <div class="hero-art reveal" data-parallax>
      <svg class="scene" viewBox="0 0 560 520" role="img" aria-label="Illustration of a car driving under a protective shield">
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

          <!-- skyline -->
          <g fill="#142259">
            <rect x="20" y="250" width="46" height="150"/><rect x="72" y="214" width="38" height="186"/><rect x="116" y="262" width="54" height="138"/>
            <rect x="378" y="236" width="42" height="164"/><rect x="426" y="200" width="50" height="200"/><rect x="482" y="256" width="60" height="144"/>
          </g>
          <g class="windows" fill="#FFD48A">
            <rect x="30" y="266" width="8" height="10"/><rect x="48" y="290" width="8" height="10"/><rect x="82" y="232" width="8" height="10"/><rect x="94" y="268" width="8" height="10"/>
            <rect x="130" y="280" width="8" height="10"/><rect x="152" y="306" width="8" height="10"/><rect x="390" y="252" width="8" height="10"/><rect x="404" y="290" width="8" height="10"/>
            <rect x="438" y="220" width="8" height="10"/><rect x="458" y="258" width="8" height="10"/><rect x="496" y="276" width="8" height="10"/><rect x="520" y="304" width="8" height="10"/>
          </g>

          <!-- hills -->
          <path d="M0 360c80-40 160-46 250-24s170 20 310-18v202H0Z" fill="#1C2C70"/>
          <path d="M0 392c110-26 220-22 320-4s170 10 240-8v140H0Z" fill="#26399A"/>

          <!-- protective shield -->
          <g class="shield-big">
            <path d="M280 96 168 138v86c0 74 47 126 112 148 65-22 112-74 112-148v-86L280 96Z" fill="url(#shieldG)" stroke="#8ED3EC" stroke-opacity=".7" stroke-width="2.5"/>
            <path class="shield-check" d="m240 214 28 28 54-56" fill="none" stroke="#8ED3EC" stroke-width="10" stroke-linecap="round" stroke-linejoin="round"/>
          </g>

          <!-- road -->
          <rect y="414" width="560" height="106" fill="#0A1233"/>
          <rect y="414" width="560" height="5" fill="#1E2C66"/>
          <path class="lane" d="M-40 470H600" stroke="#FFD48A" stroke-width="5" stroke-dasharray="34 26" stroke-linecap="round"/>

          <!-- car -->
          <g class="car" transform="translate(132 322)">
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
        <span class="chip-ic teal"><?= icon('scan') ?></span>
        <span><small>VIN detected</small><b>2021 Honda Civic EX</b></span>
        <span class="chip-ok"><?= icon('check') ?></span>
      </div>
      <div class="chip chip-2" data-depth="28">
        <span class="chip-avs"><i>M</i><i>J</i><i class="add"><?= icon('plus') ?></i></span>
        <span><small>Household</small><b>3 drivers added</b></span>
      </div>
      <div class="chip chip-3" data-depth="12">
        <span class="chip-ic amber"><?= icon('shield') ?></span>
        <span><small>Quote request</small><b>Sent to your agent</b></span>
      </div>
    </div>
  </div>
</section>

<!-- ============ COVERAGE MARQUEE ============ -->
<div class="marquee" aria-hidden="true">
  <div class="marquee-track">
    <?php for ($r = 0; $r < 2; $r++): foreach ($cov as $c): ?>
      <span><?= icon($c['icon']) ?><?= e($c['title']) ?></span>
    <?php endforeach; endfor; ?>
  </div>
</div>

<!-- ============ HOW IT WORKS ============ -->
<section class="section" aria-labelledby="howTitle">
  <div class="wrap">
    <header class="section-head reveal">
      <p class="eyebrow">How it works</p>
      <h2 id="howTitle">From "I need insurance" to options in three steps</h2>
      <p>No long phone calls, no repeating yourself. Tell us once and your agent does the shopping.</p>
    </header>
    <ol class="steps">
      <li class="step-card reveal">
        <span class="step-n">01</span>
        <span class="step-ic"><?= icon('user') ?></span>
        <h3>Tell us about you</h3>
        <p>Your name, address and the best way to reach you. That's the hard part done.</p>
      </li>
      <li class="step-card reveal">
        <span class="step-n">02</span>
        <span class="step-ic"><?= icon('users') ?></span>
        <h3>Add drivers &amp; vehicles</h3>
        <p>Tap <b>+</b> for each driver and car. Enter a VIN and we identify the vehicle for you.</p>
      </li>
      <li class="step-card reveal">
        <span class="step-n">03</span>
        <span class="step-ic"><?= icon('shield') ?></span>
        <h3>Get your options</h3>
        <p>A licensed agent compares coverage and reaches out, usually within one business day.</p>
      </li>
    </ol>
  </div>
</section>

<!-- ============ COVERAGE GRID ============ -->
<section class="section section-tint" aria-labelledby="covTitle">
  <div class="wrap">
    <header class="section-head reveal">
      <p class="eyebrow">Coverage</p>
      <h2 id="covTitle">Build the protection that fits your life</h2>
      <p>Pick coverage per vehicle and for the whole policy. Your agent explains every option in plain English.</p>
    </header>
    <div class="cov-grid">
      <?php foreach ($cov as $key => $c): ?>
      <a class="cov-card reveal" href="<?= e(url('coverage')) ?>#<?= e($key) ?>">
        <span class="cov-ic"><?= icon($c['icon']) ?></span>
        <span class="cov-tag"><?= e($c['tag']) ?></span>
        <h3><?= e($c['title']) ?></h3>
        <p><?= e($c['short']) ?></p>
        <span class="cov-more">Learn more <?= icon('arrow-right') ?></span>
      </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ============ PRODUCT SHOWCASE ============ -->
<section class="section" aria-labelledby="showTitle">
  <div class="wrap show">
    <div class="show-copy reveal">
      <p class="eyebrow">Made for real households</p>
      <h2 id="showTitle">One form for the whole family, and every car in the driveway.</h2>
      <p>Most quote forms make you start over for every driver. Ours doesn't. Add everyone at once and choose coverage for each vehicle.</p>
      <ul class="feat-list">
        <li><span><?= icon('plus') ?></span><div><b>Add drivers with one tap</b>Spouse, teen drivers and roommates, with a US or foreign license.</div></li>
        <li><span><?= icon('scan') ?></span><div><b>VIN auto-detect</b>Type the VIN and we show the year, make and model instantly.</div></li>
        <li><span><?= icon('shield-plus') ?></span><div><b>Coverage per vehicle</b>Full coverage, roadside and rental, right next to each car.</div></li>
        <li><span><?= icon('lock') ?></span><div><b>Private by design</b>License numbers are encrypted and seen only by your agent.</div></li>
      </ul>
      <button class="btn btn-primary" type="button" data-quote>Try the quote form <?= icon('arrow-right') ?></button>
    </div>

    <div class="mock reveal" data-mock aria-hidden="true">
      <div class="mock-win">
        <div class="mock-bar"><i></i><i></i><i></i><span>Vehicles · Step 4 of 6</span></div>
        <div class="mock-body">
          <div class="mock-card">
            <div class="mock-head"><span class="mock-av"><?= icon('car') ?></span><b data-mock-title>Vehicle 1</b></div>
            <div class="mock-input"><?= icon('scan') ?><span data-mock-vin></span><i class="caret"></i></div>
            <div class="mock-result" data-mock-result>
              <span class="mock-ok"><?= icon('check') ?></span>
              <div><b>2020 Toyota Camry SE</b><small>Sedan · Gasoline · FWD</small></div>
            </div>
            <div class="mock-chips">
              <span class="on" data-mock-chip><?= icon('shield-plus') ?>Full coverage</span>
              <span data-mock-chip><?= icon('wrench') ?>Roadside</span>
              <span data-mock-chip><?= icon('key') ?>Rental</span>
            </div>
          </div>
          <div class="mock-add"><?= icon('plus') ?> Add another vehicle</div>
        </div>
      </div>
      <div class="mock-float">
        <span class="mock-avs"><i>A</i><i>R</i><i>K</i></span>
        <div><b>3 drivers</b><small>2 vehicles · Ready to send</small></div>
      </div>
    </div>
  </div>
</section>

<!-- ============ STATS ============ -->
<section class="stats" aria-label="At a glance">
  <div class="wrap stats-grid">
    <div class="stat reveal"><b><span data-count="3">0</span> min</b><span>to request a quote</span></div>
    <div class="stat reveal"><b><span data-count="6">0</span></b><span>coverage options to choose from</span></div>
    <div class="stat reveal"><b><span data-count="1">0</span> day</b><span>typical response time</span></div>
    <div class="stat reveal"><b><span data-count="256">0</span>-bit</b><span>encryption for sensitive data</span></div>
  </div>
</section>

<!-- ============ CHECKLIST ============ -->
<section class="section" aria-labelledby="readyTitle">
  <div class="wrap ready">
    <div class="ready-art reveal" aria-hidden="true">
      <svg viewBox="0 0 360 320">
        <rect x="40" y="40" width="250" height="250" rx="28" fill="var(--brand-100)"/>
        <rect x="70" y="20" width="250" height="250" rx="28" fill="#fff" stroke="var(--line)" stroke-width="2"/>
        <rect x="150" y="6" width="90" height="30" rx="12" fill="var(--navy-900)"/>
        <g class="ready-rows">
          <g><rect x="100" y="70" width="26" height="26" rx="8" fill="var(--brand-500)"/><path d="m106 83 5 5 9-10" stroke="#fff" stroke-width="3" fill="none" stroke-linecap="round" stroke-linejoin="round"/><rect x="140" y="76" width="140" height="12" rx="6" fill="var(--sand-200)"/></g>
          <g><rect x="100" y="120" width="26" height="26" rx="8" fill="var(--brand-500)"/><path d="m106 133 5 5 9-10" stroke="#fff" stroke-width="3" fill="none" stroke-linecap="round" stroke-linejoin="round"/><rect x="140" y="126" width="110" height="12" rx="6" fill="var(--sand-200)"/></g>
          <g><rect x="100" y="170" width="26" height="26" rx="8" fill="var(--brand-500)"/><path d="m106 183 5 5 9-10" stroke="#fff" stroke-width="3" fill="none" stroke-linecap="round" stroke-linejoin="round"/><rect x="140" y="176" width="126" height="12" rx="6" fill="var(--sand-200)"/></g>
          <g><rect x="100" y="220" width="26" height="26" rx="8" fill="var(--amber-400)"/><path d="m106 233 5 5 9-10" stroke="#fff" stroke-width="3" fill="none" stroke-linecap="round" stroke-linejoin="round"/><rect x="140" y="226" width="92" height="12" rx="6" fill="var(--sand-200)"/></g>
        </g>
      </svg>
    </div>
    <div class="reveal">
      <p class="eyebrow">Before you start</p>
      <h2 id="readyTitle">Have these handy for the fastest quote</h2>
      <ul class="ready-list">
        <li><?= icon('user') ?><div><b>Your details</b>Name, date of birth, address, phone and email.</div></li>
        <li><?= icon('id') ?><div><b>Each driver's license</b>US or foreign license, issuing state or country and license number.</div></li>
        <li><?= icon('car') ?><div><b>Each vehicle's VIN</b>On the dashboard, door jamb, registration or insurance card.</div></li>
        <li><?= icon('shield') ?><div><b>Your current policy (optional)</b>Helps us compare like for like and find savings.</div></li>
      </ul>
    </div>
  </div>
</section>

<!-- ============ FAQ ============ -->
<section class="section section-tint" aria-labelledby="faqTitle">
  <div class="wrap faq-wrap">
    <header class="section-head reveal">
      <p class="eyebrow">Questions</p>
      <h2 id="faqTitle">Good to know</h2>
      <p>More answers on our <a href="<?= e(url('coverage')) ?>#faq">coverage page</a>, or just <a href="<?= e(url('contact')) ?>">ask us</a>.</p>
    </header>
    <div class="faq reveal">
      <details><summary>Does requesting a quote cost anything?<?= icon('chevron-down') ?></summary><div><p>No. Quotes are free and there's no obligation to buy. You'll get options and can decide in your own time.</p></div></details>
      <details><summary>Can I add drivers with a foreign license?<?= icon('chevron-down') ?></summary><div><p>Yes. For each driver, choose "Foreign license", enter the issuing country and license number. Your agent will find carriers that accept it.</p></div></details>
      <details><summary>Is my personal information safe?<?= icon('chevron-down') ?></summary><div><p>Your request is sent over an encrypted connection, license numbers are encrypted when stored, and only your agent can see them. We never sell your information.</p></div></details>
      <details><summary>What if I don't know my VIN?<?= icon('chevron-down') ?></summary><div><p>Check the driver-side dashboard, the sticker inside the driver's door, or your registration or insurance card. If you're stuck, call us and we'll help.</p></div></details>
    </div>
  </div>
</section>

<?php require APP_ROOT . '/partials/cta.php'; ?>
<?php require APP_ROOT . '/partials/footer.php'; ?>
