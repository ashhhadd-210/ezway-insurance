<?php
require __DIR__ . '/app/bootstrap.php';
http_response_code(404);

$page = [
    'slug'    => 'notfound',
    'path'    => '',
    'title'   => 'Page not found',
    'noindex' => true,
];
require APP_ROOT . '/partials/header.php';
?>

<section class="page-hero nf">
  <div class="hero-bg" aria-hidden="true"><span class="blob b1"></span><span class="grid-lines"></span><?= pinwheel_svg('pinwheel page-pin') ?></div>
  <div class="wrap page-hero-in">
    <svg class="nf-art" viewBox="0 0 320 140" aria-hidden="true">
      <path d="M10 120h300" stroke="var(--navy-900)" stroke-width="4" stroke-linecap="round"/>
      <path class="lane" d="M10 132h300" stroke="var(--amber-400)" stroke-width="4" stroke-dasharray="20 16"/>
      <g transform="translate(110 64)">
        <path transform="translate(140 0) scale(-1 1)" d="M4 40c0-7 4-10 11-11l23-3c7-9 17-15 31-16h23c10 0 17 5 24 14l9 3c7 2 10 6 10 12v4c0 2-1 3-3 3H6c-1 0-2-1-2-3Z" fill="var(--amber-400)"/>
        <circle cx="36" cy="46" r="10" fill="var(--navy-900)"/><circle cx="108" cy="46" r="10" fill="var(--navy-900)"/>
      </g>
      <g transform="translate(250 40)"><path d="M0 0v80" stroke="var(--navy-900)" stroke-width="4"/><path d="M0 4h40l-8 12 8 12H0Z" fill="var(--brand-500)"/></g>
    </svg>
    <h1>Looks like a wrong turn.</h1>
    <p class="lead">We couldn't find that page. Let's get you back on the road.</p>
    <div class="split-actions center">
      <a class="btn btn-primary" href="<?= e(url()) ?>">Back to home</a>
      <button class="btn btn-ghost" type="button" data-quote>Start your quote</button>
    </div>
  </div>
</section>

<?php require APP_ROOT . '/partials/footer.php'; ?>
