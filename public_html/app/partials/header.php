<?php
defined('APP') or exit;
/**
 * Expects $page = [
 *   'slug' => 'home', 'path' => '', 'title' => '...', 'description' => '...',
 *   'schema' => [extra JSON-LD objects], 'noindex' => false,
 * ]
 */
send_security_headers();

$siteName = (string) cfg('site.name');
$pageTitle = ($page['title'] ?? '') !== '' ? $page['title'] . ' | ' . $siteName : $siteName . ' | ' . cfg('site.tagline');
$desc = $page['description'] ?? cfg('site.description');
$canonical = site_url($page['path'] ?? '');
$addr = cfg('site.address');

$nav = [
    'home'     => ['Home', ''],
    'about'    => ['About', 'about'],
    'coverage' => ['Coverage', 'coverage'],
    'contact'  => ['Contact', 'contact'],
];

$days = ['Mon – Fri' => ['Mo', 'Tu', 'We', 'Th', 'Fr'], 'Saturday' => ['Sa'], 'Sunday' => ['Su']];
$hours = [];
foreach (cfg('site.hours', []) as [$label, $time]) {
    if (isset($days[$label]) && preg_match('/(\d{1,2}):(\d{2})\s*(AM|PM)\s*[–-]\s*(\d{1,2}):(\d{2})\s*(AM|PM)/i', $time, $m)) {
        $to24 = fn ($h, $min, $ap) => sprintf('%02d:%s', ((int) $h % 12) + (strtoupper($ap) === 'PM' ? 12 : 0), $min);
        $hours[] = implode(',', $days[$label]) . ' ' . $to24($m[1], $m[2], $m[3]) . '-' . $to24($m[4], $m[5], $m[6]);
    }
}

$schema = array_merge([[
    '@context'  => 'https://schema.org',
    '@type'     => 'InsuranceAgency',
    '@id'       => site_url('#agency'),
    'name'      => $siteName,
    'legalName' => cfg('site.legal_name'),
    'url'       => site_url(),
    'logo'      => site_url('assets/img/apple-touch-icon.png'),
    'image'     => site_url('assets/img/og-image.png'),
    'description' => cfg('site.description'),
    'telephone' => cfg('site.phone'),
    'email'     => cfg('site.email'),
    'address'   => [
        '@type' => 'PostalAddress',
        'streetAddress'   => $addr['street'],
        'addressLocality' => $addr['city'],
        'addressRegion'   => $addr['state'],
        'postalCode'      => $addr['zip'],
        'addressCountry'  => 'US',
    ],
    'openingHours' => $hours,
    'sameAs' => array_values(array_filter(cfg('site.social', []))),
]], $page['schema'] ?? []);
?><!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<title><?= e($pageTitle) ?></title>
<meta name="description" content="<?= e($desc) ?>">
<link rel="canonical" href="<?= e($canonical) ?>">
<?php if (!empty($page['noindex'])): ?><meta name="robots" content="noindex"><?php endif; ?>
<meta name="theme-color" content="#0D1640">
<meta property="og:type" content="website">
<meta property="og:site_name" content="<?= e($siteName) ?>">
<meta property="og:title" content="<?= e($pageTitle) ?>">
<meta property="og:description" content="<?= e($desc) ?>">
<meta property="og:url" content="<?= e($canonical) ?>">
<meta property="og:image" content="<?= e(site_url('assets/img/og-image.png')) ?>">
<meta property="og:image:width" content="1200">
<meta property="og:image:height" content="630">
<meta name="twitter:card" content="summary_large_image">
<link rel="icon" href="<?= e(asset('img/favicon.png')) ?>" type="image/png">
<link rel="apple-touch-icon" href="<?= e(asset('img/apple-touch-icon.png')) ?>">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,600;12..96,700;12..96,800&family=Figtree:wght@400;500;600;700&display=swap">
<link rel="stylesheet" href="<?= e(asset('css/site.css')) ?>">
<script type="application/ld+json"><?= json_encode(count($schema) === 1 ? $schema[0] : ['@context' => 'https://schema.org', '@graph' => array_map(fn ($s) => array_diff_key($s, ['@context' => 1]), $schema)], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) ?></script>
</head>
<body class="page-<?= e($page['slug'] ?? 'page') ?>">
<a class="skip" href="#main">Skip to content</a>
<?php require APP_ROOT . '/partials/icons.php'; ?>

<header class="nav" data-nav>
  <div class="nav-in wrap">
    <a class="logo" href="<?= e(url()) ?>" aria-label="<?= e($siteName) ?> home"><?= logo_html() ?></a>

    <nav class="nav-links" aria-label="Main">
      <?php foreach ($nav as $slug => [$label, $href]): ?>
        <a href="<?= e(url($href)) ?>"<?= ($page['slug'] ?? '') === $slug ? ' aria-current="page"' : '' ?>><?= e($label) ?></a>
      <?php endforeach; ?>
    </nav>

    <div class="nav-cta">
      <a class="nav-phone" href="<?= e(tel_href(cfg('site.phone'))) ?>"><?= icon('phone') ?><span><?= e(cfg('site.phone')) ?></span></a>
      <a class="btn btn-primary btn-sm" href="<?= e(url('quote')) ?>" data-quote>Get a quote</a>
      <button class="nav-burger" type="button" aria-expanded="false" aria-controls="drawer" aria-label="Open menu" data-burger>
        <span></span><span></span><span></span>
      </button>
    </div>
  </div>

  <div class="drawer" id="drawer" data-drawer hidden>
    <nav aria-label="Mobile">
      <?php foreach ($nav as $slug => [$label, $href]): ?>
        <a href="<?= e(url($href)) ?>"<?= ($page['slug'] ?? '') === $slug ? ' aria-current="page"' : '' ?>><?= e($label) ?><?= icon('arrow-right') ?></a>
      <?php endforeach; ?>
    </nav>
    <div class="drawer-foot">
      <a class="btn btn-primary btn-block" href="<?= e(url('quote')) ?>" data-quote>Start your quote <?= icon('arrow-right') ?></a>
      <a class="btn btn-ghost btn-block" href="<?= e(tel_href(cfg('site.phone'))) ?>"><?= icon('phone') ?> Call <?= e(cfg('site.phone')) ?></a>
    </div>
  </div>
</header>

<main id="main">
