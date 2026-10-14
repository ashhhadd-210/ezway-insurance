<?php
require __DIR__ . '/app/bootstrap.php';

$page = [
    'slug'        => 'privacy',
    'path'        => 'privacy',
    'title'       => 'Privacy policy',
    'description' => 'How ' . cfg('site.name') . ' collects, uses and protects the information you share with us.',
];
require APP_ROOT . '/partials/header.php';
// NOTE: This is a starting template. Have it reviewed for your state's requirements before launch.
?>

<section class="page-hero page-hero-sm">
  <div class="wrap page-hero-in">
    <h1>Privacy policy</h1>
    <p class="lead">Last updated <?= date('F j, Y', filemtime(__FILE__)) ?></p>
  </div>
</section>

<section class="section section-top0">
  <div class="wrap prose">
    <p><?= e(cfg('site.legal_name')) ?> ("we", "us") respects your privacy. This policy explains what we collect through this website, how we use it, and the choices you have.</p>

    <h2>Information we collect</h2>
    <ul>
      <li><b>Quote requests:</b> your name, date of birth, address, phone, email and contact preference; each driver's name, date of birth, relationship, license type, issuing state or country and license number; each vehicle's VIN and the coverage you select; and any notes you add.</li>
      <li><b>Messages:</b> the name, email, phone and message you send through our contact form.</li>
      <li><b>Technical data:</b> basic information your browser sends, such as browser type. We store only a one-way scrambled version of your IP address, used to prevent spam.</li>
    </ul>

    <h2>How we use it</h2>
    <p>We use your information to prepare insurance quotes, contact you about them, answer your questions, and service your policy. To quote coverage, we may share the information you provide with insurance carriers we work with. We do not sell your personal information.</p>

    <h2>How we protect it</h2>
    <p>This site uses an encrypted (HTTPS) connection. Driver license numbers are encrypted before they are stored and are visible only to authorized agency staff through a password-protected system. VINs are checked against the public U.S. National Highway Traffic Safety Administration (NHTSA) database to identify vehicles.</p>

    <h2>How long we keep it</h2>
    <p>We keep quote requests and messages only as long as needed to serve you and to meet legal and regulatory requirements, then delete them.</p>

    <h2>Your choices</h2>
    <p>You can ask us to access, correct or delete your information at any time by contacting us at <a href="mailto:<?= e(cfg('site.email')) ?>"><?= e(cfg('site.email')) ?></a> or <a href="<?= e(tel_href(cfg('site.phone'))) ?>"><?= e(cfg('site.phone')) ?></a>. You can opt out of marketing messages at any time; for texts, reply STOP.</p>

    <h2>Contact</h2>
    <p><?= e(cfg('site.legal_name')) ?><br><?= e(full_address()) ?><br><?= e(cfg('site.phone')) ?> · <?= e(cfg('site.email')) ?></p>
  </div>
</section>

<?php require APP_ROOT . '/partials/footer.php'; ?>
