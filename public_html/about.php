<?php
require __DIR__ . '/app/bootstrap.php';

$page = [
    'slug'        => 'about',
    'path'        => 'about',
    'title'       => 'About us',
    'description' => 'Meet ' . cfg('site.name') . ': a licensed local insurance agency that compares coverage for you, explains it in plain English and stays with you at claim time.',
];
require APP_ROOT . '/partials/header.php';
?>

<section class="page-hero">
  <div class="hero-bg" aria-hidden="true"><span class="blob b1"></span><span class="grid-lines"></span><?= pinwheel_svg('pinwheel page-pin') ?></div>
  <div class="wrap page-hero-in">
    <p class="pill reveal"><span class="pill-dot"></span>About us</p>
    <h1 class="reveal">A local agency with a simple promise: <span class="text-teal">we've got your back.</span></h1>
    <p class="lead reveal">We started <?= e(cfg('site.name')) ?> because buying car insurance shouldn't feel like a puzzle. You tell us what you need once, and we do the comparing, the explaining and the follow-up.</p>
  </div>
</section>

<section class="section">
  <div class="wrap split">
    <div class="reveal">
      <p class="eyebrow">Our story</p>
      <h2>Real people, real advice, right here in <?= e(cfg('site.address.city')) ?>.</h2>
      <p>As an independent agency, we don't work for one insurance company. We work for you. That means we can compare coverage from several carriers and recommend what actually fits your household, your vehicles and your budget.</p>
      <p>Whether you're insuring your first car, adding a teen driver, or driving with a license from another country, we'll find options that work and explain them without the jargon.</p>
      <div class="split-actions">
        <button class="btn btn-primary" type="button" data-quote>Start your quote <?= icon('arrow-right') ?></button>
        <a class="btn btn-ghost" href="<?= e(url('contact')) ?>">Contact us</a>
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
          <path d="M6 50c0-9 5-13 14-14l29-4c9-11 21-19 39-20h29c13 0 21 6 30 18l11 4c9 2 13 8 13 15v5c0 3-2 4-4 4H9c-2 0-3-1-3-4Z" fill="#FFB547"/>
          <path d="M58 33c6-9 15-14 28-14h9v14Zm43-14h15c9 0 15 5 21 14h-36Z" fill="#CFE9F5"/>
          <circle cx="44" cy="58" r="13" fill="#0D1640"/><circle cx="44" cy="58" r="6" fill="#9FB3C8"/>
          <circle cx="134" cy="58" r="13" fill="#0D1640"/><circle cx="134" cy="58" r="6" fill="#9FB3C8"/>
        </g>
        <g fill="#26399A"><circle cx="420" cy="330" r="22"/><circle cx="440" cy="316" r="18"/><rect x="426" y="330" width="6" height="24" fill="#0D1640"/></g>
      </svg>
    </div>
  </div>
</section>

<section class="section section-tint" aria-labelledby="valTitle">
  <div class="wrap">
    <header class="section-head reveal">
      <p class="eyebrow">What we stand for</p>
      <h2 id="valTitle">The way insurance should feel</h2>
    </header>
    <div class="val-grid">
      <article class="val reveal"><span class="val-ic"><?= icon('search') ?></span><h3>We shop, you save</h3><p>We compare options from multiple carriers so you don't have to fill out form after form.</p></article>
      <article class="val reveal"><span class="val-ic"><?= icon('chat') ?></span><h3>Plain answers</h3><p>No jargon and no fine-print surprises. We explain what you're paying for and why.</p></article>
      <article class="val reveal"><span class="val-ic"><?= icon('users') ?></span><h3>Every driver welcome</h3><p>New drivers, teen drivers, and drivers with US or foreign licenses. We'll find you a fit.</p></article>
      <article class="val reveal"><span class="val-ic"><?= icon('shield') ?></span><h3>Here at claim time</h3><p>When something happens, you call a person who knows you, not a call center.</p></article>
    </div>
  </div>
</section>

<section class="section" aria-labelledby="flowTitle">
  <div class="wrap">
    <header class="section-head reveal">
      <p class="eyebrow">How we work</p>
      <h2 id="flowTitle">What happens after you request a quote</h2>
    </header>
    <ol class="timeline">
      <li class="reveal"><span class="tl-dot"><?= icon('doc') ?></span><div><h3>You send your request</h3><p>Every driver and vehicle in one form, in about three minutes.</p></div></li>
      <li class="reveal"><span class="tl-dot"><?= icon('search') ?></span><div><h3>We compare carriers</h3><p>Your agent checks rates and coverage across the companies we work with.</p></div></li>
      <li class="reveal"><span class="tl-dot"><?= icon('phone') ?></span><div><h3>We walk you through it</h3><p>We reach out by phone, text or email, whichever you prefer, and explain your options.</p></div></li>
      <li class="reveal"><span class="tl-dot"><?= icon('shield') ?></span><div><h3>You're covered, and we stay close</h3><p>Need to add a car, change coverage or file a claim? We're one call away.</p></div></li>
    </ol>
  </div>
</section>

<?php require APP_ROOT . '/partials/cta.php'; ?>
<?php require APP_ROOT . '/partials/footer.php'; ?>
