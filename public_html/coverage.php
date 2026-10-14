<?php
require __DIR__ . '/app/bootstrap.php';

$faqs = [
    ['What does "full coverage" mean?', 'It usually means liability plus comprehensive and collision. Liability covers damage you cause to others; comprehensive and collision protect your own car.'],
    ['How much liability coverage do I need?', 'Your state sets a minimum, but it is often too low to cover a serious accident. Many people choose 100/300/100 for better protection. Your agent can recommend limits based on what you want to protect.'],
    ['Do I need PIP?', 'Some states require personal injury protection; others make it optional. It\'s worth considering if you carry passengers or have a high health-insurance deductible.'],
    ['Is uninsured motorist coverage worth it?', 'For most people, yes. About one in seven drivers is uninsured, and this coverage is usually inexpensive compared to the protection it gives.'],
    ['Can I choose different coverage for each car?', 'Yes. In the quote form you can choose full coverage, roadside and rental separately for every vehicle. For example, full coverage on a newer car and liability only on an older one.'],
    ['What is a deductible?', 'It is the amount you pay toward a comprehensive or collision claim before insurance pays the rest. A higher deductible usually means a lower premium.'],
    ['Can drivers with a foreign license get covered?', 'Yes. Choose "Foreign license" for that driver in the quote form and enter the issuing country. We\'ll find carriers that accept it.'],
    ['How fast will I hear back?', 'Usually within one business day. If you need coverage today, call us at ' . cfg('site.phone') . '.'],
];

$page = [
    'slug'        => 'coverage',
    'path'        => 'coverage',
    'title'       => 'Auto insurance coverage options',
    'description' => 'Liability, full coverage (comprehensive & collision), personal injury protection, uninsured motorist, roadside assistance and rental reimbursement, explained in plain English.',
    'schema'      => [[
        '@context'   => 'https://schema.org',
        '@type'      => 'FAQPage',
        'mainEntity' => array_map(fn ($f) => [
            '@type' => 'Question',
            'name'  => $f[0],
            'acceptedAnswer' => ['@type' => 'Answer', 'text' => $f[1]],
        ], $faqs),
    ]],
];
$cov = coverage_info();
require APP_ROOT . '/partials/header.php';
?>

<section class="page-hero">
  <div class="hero-bg" aria-hidden="true"><span class="blob b2"></span><span class="grid-lines"></span><?= pinwheel_svg('pinwheel page-pin') ?></div>
  <div class="wrap page-hero-in">
    <p class="pill reveal"><span class="pill-dot"></span>Coverage</p>
    <h1 class="reveal">Coverage, <span class="text-teal">explained in plain English.</span></h1>
    <p class="lead reveal">Here's what each option does, who it's for and how to pick. Choose coverage per vehicle and for the whole policy right in your quote.</p>
  </div>
</section>

<nav class="cov-nav" aria-label="Coverage types" data-covnav>
  <div class="wrap">
    <?php foreach ($cov as $key => $c): ?>
      <a href="#<?= e($key) ?>"><?= icon($c['icon']) ?><?= e($c['title']) ?></a>
    <?php endforeach; ?>
    <a href="#compare"><?= icon('grid') ?>Compare</a>
    <a href="#faq"><?= icon('info') ?>FAQ</a>
  </div>
</nav>

<section class="section">
  <div class="wrap cov-detail">
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
        <h3>Good for</h3>
        <ul><?php foreach ($c['good'] as $g): ?><li><?= icon('check') ?><?= e($g) ?></li><?php endforeach; ?></ul>
      </div>
    </article>
    <?php endforeach; ?>
  </div>
</section>

<section class="section section-tint" id="compare" aria-labelledby="cmpTitle">
  <div class="wrap">
    <header class="section-head reveal">
      <p class="eyebrow">Compare</p>
      <h2 id="cmpTitle">Liability only vs. full coverage</h2>
      <p>The biggest decision for each vehicle. Here's what each one pays for.</p>
    </header>
    <div class="cmp-wrap reveal">
      <table class="cmp">
        <thead><tr><th scope="col">What happens</th><th scope="col">Liability only</th><th scope="col" class="cmp-hl">Full coverage</th></tr></thead>
        <tbody>
          <?php
          $rows = [
              ['Injuries you cause to others', true, true],
              ['Damage you cause to other cars or property', true, true],
              ['Repairs to your car after an accident', false, true],
              ['Theft, vandalism, fire, hail and flood', false, true],
              ['Hitting an animal or a falling object', false, true],
              ['Usually required for financed or leased cars', false, true],
          ];
          foreach ($rows as [$label, $a, $b]): ?>
          <tr>
            <th scope="row"><?= e($label) ?></th>
            <td><?= $a ? '<span class="yes">' . icon('check') . '<span class="sr">Covered</span></span>' : '<span class="no">' . icon('x') . '<span class="sr">Not covered</span></span>' ?></td>
            <td class="cmp-hl"><?= $b ? '<span class="yes">' . icon('check') . '<span class="sr">Covered</span></span>' : '<span class="no">' . icon('x') . '<span class="sr">Not covered</span></span>' ?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
      <p class="cmp-note"><?= icon('info') ?> Add <b>roadside</b> and <b>rental</b> to any vehicle, and <b>PIP</b> and <b>uninsured motorist</b> to the whole policy.</p>
    </div>
  </div>
</section>

<section class="section" id="faq" aria-labelledby="faqTitle">
  <div class="wrap faq-wrap">
    <header class="section-head reveal">
      <p class="eyebrow">FAQ</p>
      <h2 id="faqTitle">Frequently asked questions</h2>
      <p>Can't find your answer? <a href="<?= e(url('contact')) ?>">Send us a message</a>.</p>
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
