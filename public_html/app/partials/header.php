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
$pageTitle = ($page['title'] ?? '') !== '' ? $page['title'] . ' | ' . $siteName : $siteName . ' | ' . LA(cfg('site.tagline'));
$desc = $page['description'] ?? LA(cfg('site.description'));
$path = $page['path'] ?? '';
$canonical = page_url($path, lang());
$otherLang = lang() === 'es' ? 'en' : 'es';

$nav = [
    'home'     => [L('Home', 'Inicio'), ''],
    'auto'     => [L('Auto insurance', 'Seguro de auto'), 'auto-insurance'],
    'services' => [L('Services', 'Servicios'), 'services'],
    'about'    => [L('About', 'Nosotros'), 'about'],
    'contact'  => [L('Locations', 'Oficinas'), 'contact'],
];

// One InsuranceAgency per office so each shows up with its own address and hours.
$agencies = [];
foreach (offices() as $o) {
    $agencies[] = [
        '@type'     => 'InsuranceAgency',
        '@id'       => site_url('#office-' . $o['key']),
        'name'      => $siteName . ' – ' . $o['name'],
        'url'       => site_url(),
        'logo'      => site_url('assets/img/logo.png'),
        'image'     => site_url('assets/img/og-image.png'),
        'telephone' => cfg('site.phone'),
        'email'     => cfg('site.email'),
        'priceRange'=> '$$',
        'knowsLanguage' => ['en', 'es'],
        'areaServed'=> ['@type' => 'State', 'name' => 'Texas'],
        'address'   => [
            '@type' => 'PostalAddress',
            'streetAddress'   => $o['street'],
            'addressLocality' => $o['city'],
            'addressRegion'   => $o['state'],
            'postalCode'      => $o['zip'],
            'addressCountry'  => 'US',
        ],
        'openingHoursSpecification' => office_schema_hours($o),
    ];
}
$graph = array_merge($agencies, array_map(fn ($s) => array_diff_key($s, ['@context' => 1]), $page['schema'] ?? []));
?><!doctype html>
<html lang="<?= lang() ?>">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<title><?= e($pageTitle) ?></title>
<meta name="description" content="<?= e($desc) ?>">
<link rel="canonical" href="<?= e($canonical) ?>">
<?php if (spanish_enabled()): ?>
<link rel="alternate" hreflang="en" href="<?= e(page_url($path, 'en')) ?>">
<link rel="alternate" hreflang="es" href="<?= e(page_url($path, 'es')) ?>">
<link rel="alternate" hreflang="x-default" href="<?= e(page_url($path, 'en')) ?>">
<?php endif; ?>
<?php if (!empty($page['noindex'])): ?><meta name="robots" content="noindex"><?php endif; ?>
<meta name="theme-color" content="#0D1640">
<meta property="og:type" content="website">
<meta property="og:locale" content="<?= lang() === 'es' ? 'es_US' : 'en_US' ?>">
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
<link rel="preload" as="image" href="<?= e(asset('img/logo.png')) ?>">
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,600;12..96,700;12..96,800&family=Figtree:wght@400;500;600;700&display=swap">
<link rel="stylesheet" href="<?= e(asset('css/site.css')) ?>">
<script type="application/ld+json"><?= json_encode(['@context' => 'https://schema.org', '@graph' => $graph], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) ?></script>
</head>
<body class="page-<?= e($page['slug'] ?? 'page') ?> lang-<?= lang() ?>">
<a class="skip" href="#main"><?= e(L('Skip to content', 'Ir al contenido')) ?></a>
<?php require APP_ROOT . '/partials/icons.php'; ?>

<header class="nav" data-nav>
  <div class="nav-in wrap">
    <a class="logo" href="<?= e(lurl()) ?>" aria-label="<?= e($siteName) ?> – <?= e(L('home', 'inicio')) ?>"><?= logo_html() ?></a>

    <nav class="nav-links" aria-label="<?= e(L('Main', 'Principal')) ?>">
      <?php foreach ($nav as $slug => [$label, $href]): ?>
        <a href="<?= e(lurl($href)) ?>"<?= ($page['slug'] ?? '') === $slug ? ' aria-current="page"' : '' ?>><?= e($label) ?></a>
      <?php endforeach; ?>
    </nav>

    <div class="nav-cta">
      <?php if (spanish_enabled()): ?>
      <a class="lang-switch" href="<?= e(url(($otherLang === 'es' ? 'es/' : '') . $path)) ?>" hreflang="<?= $otherLang ?>" lang="<?= $otherLang ?>" aria-label="<?= e(L('Ver en español', 'View in English')) ?>">
        <span class="<?= lang() === 'en' ? 'on' : '' ?>">EN</span><span class="<?= lang() === 'es' ? 'on' : '' ?>">ES</span>
      </a>
      <?php endif; ?>
      <a class="nav-phone" href="<?= e(tel_href(cfg('site.phone'))) ?>"><?= icon('phone') ?><span><?= e(cfg('site.phone')) ?></span></a>
      <a class="btn btn-primary btn-sm" href="<?= e(lurl('quote')) ?>" data-quote><?= e(L('Get a quote', 'Cotizar')) ?></a>
      <button class="nav-burger" type="button" aria-expanded="false" aria-controls="drawer" aria-label="<?= e(L('Open menu', 'Abrir menú')) ?>" data-burger data-label-open="<?= e(L('Open menu', 'Abrir menú')) ?>" data-label-close="<?= e(L('Close menu', 'Cerrar menú')) ?>">
        <span></span><span></span><span></span>
      </button>
    </div>
  </div>

  <div class="drawer" id="drawer" data-drawer hidden>
    <nav aria-label="<?= e(L('Mobile', 'Móvil')) ?>">
      <?php foreach ($nav as $slug => [$label, $href]): ?>
        <a href="<?= e(lurl($href)) ?>"<?= ($page['slug'] ?? '') === $slug ? ' aria-current="page"' : '' ?>><?= e($label) ?><?= icon('arrow-right') ?></a>
      <?php endforeach; ?>
    </nav>
    <div class="drawer-foot">
      <a class="btn btn-primary btn-block" href="<?= e(lurl('quote')) ?>" data-quote><?= e(L('Start your quote', 'Empieza tu cotización')) ?> <?= icon('arrow-right') ?></a>
      <a class="btn btn-ghost btn-block" href="<?= e(tel_href(cfg('site.phone'))) ?>"><?= icon('phone') ?> <?= e(L('Call', 'Llamar al')) ?> <?= e(cfg('site.phone')) ?></a>
      <?php if (spanish_enabled()): ?>
      <a class="btn btn-ghost btn-block" href="<?= e(url(($otherLang === 'es' ? 'es/' : '') . $path)) ?>" lang="<?= $otherLang ?>"><?= icon('language') ?> <?= e(L('Ver en español', 'View in English')) ?></a>
      <?php endif; ?>
    </div>
  </div>
</header>

<main id="main">
