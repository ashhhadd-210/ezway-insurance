<?php
require __DIR__ . '/app/bootstrap.php';

$page = [
    'slug'        => 'contact',
    'path'        => 'contact',
    'title'       => 'Contact us',
    'description' => 'Call, email or visit ' . cfg('site.name') . '. Questions about a quote, your policy or a claim? We\'re here to help.',
];
$addr = cfg('site.address');
require APP_ROOT . '/partials/header.php';
?>

<section class="page-hero">
  <div class="hero-bg" aria-hidden="true"><span class="blob b1"></span><span class="grid-lines"></span><?= pinwheel_svg('pinwheel page-pin') ?></div>
  <div class="wrap page-hero-in">
    <p class="pill reveal"><span class="pill-dot"></span>Contact</p>
    <h1 class="reveal">Talk to a real person. <span class="text-teal">We're happy to help.</span></h1>
    <p class="lead reveal">Questions about a quote, your policy or a claim? Call, email, stop by, or send us a message below.</p>
  </div>
</section>

<section class="section section-tight">
  <div class="wrap contact-cards">
    <a class="cc reveal" href="<?= e(tel_href(cfg('site.phone'))) ?>">
      <span class="cc-ic"><?= icon('phone') ?></span>
      <span class="cc-l">Call us</span>
      <b><?= e(cfg('site.phone')) ?></b>
      <small>Fastest way to get help</small>
    </a>
    <a class="cc reveal" href="mailto:<?= e(cfg('site.email')) ?>">
      <span class="cc-ic"><?= icon('mail') ?></span>
      <span class="cc-l">Email us</span>
      <b><?= e(cfg('site.email')) ?></b>
      <small>We reply within one business day</small>
    </a>
    <a class="cc reveal" href="https://www.google.com/maps/search/?api=1&amp;query=<?= e(rawurlencode(full_address())) ?>" target="_blank" rel="noopener">
      <span class="cc-ic"><?= icon('pin') ?></span>
      <span class="cc-l">Visit us</span>
      <b><?= e($addr['street']) ?></b>
      <small><?= e($addr['city'] . ', ' . $addr['state'] . ' ' . $addr['zip']) ?></small>
    </a>
  </div>
</section>

<section class="section section-top0">
  <div class="wrap contact-grid">
    <div class="card-panel reveal">
      <h2>Send us a message</h2>
      <p class="muted">Looking for a price? The <button class="link-btn" type="button" data-quote>quote form</button> is the fastest way to get one.</p>
      <form class="contact-form" data-contact data-token="<?= e(form_token()) ?>" data-api="<?= e(url('api/contact.php')) ?>" data-turnstile="<?= e(turnstile_enabled() ? cfg('security.turnstile_site_key') : '') ?>" novalidate>
        <div class="hp-wrap" aria-hidden="true"><label>Leave this empty<input type="text" name="website" tabindex="-1" autocomplete="off"></label></div>
        <div class="grid g2">
          <label class="fld"><span class="fld-l">Your name</span><input name="name" autocomplete="name" maxlength="120" required><span class="fld-e"></span></label>
          <label class="fld"><span class="fld-l">Email</span><input name="email" type="email" autocomplete="email" maxlength="160" required><span class="fld-e"></span></label>
          <label class="fld"><span class="fld-l">Phone <i>(optional)</i></span><input name="phone" type="tel" autocomplete="tel-national" maxlength="14" data-mask="phone" placeholder="(555) 555-5555"><span class="fld-e"></span></label>
          <label class="fld"><span class="fld-l">Topic</span>
            <select name="topic">
              <option>General question</option><option>Existing policy</option><option>Claims help</option><option>Billing</option><option>Other</option>
            </select><span class="fld-e"></span></label>
          <label class="fld span2"><span class="fld-l">Message</span><textarea name="message" rows="5" maxlength="3000" required placeholder="How can we help?"></textarea><span class="fld-e"></span></label>
        </div>
        <div class="ts-box" data-turnstile-box></div>
        <div class="form-foot">
          <p class="qw-secure"><?= icon('lock') ?> Sent securely. We never share your information.</p>
          <button class="btn btn-primary" type="submit"><span>Send message</span><?= icon('arrow-right') ?><span class="btn-spin" aria-hidden="true"></span></button>
        </div>
        <div class="form-done" hidden>
          <svg class="done-check" viewBox="0 0 80 80" aria-hidden="true"><circle cx="40" cy="40" r="36"/><path d="m25 41 10 10 21-22"/></svg>
          <h3>Message sent!</h3>
          <p>Thanks for reaching out. We'll get back to you within one business day.</p>
        </div>
      </form>
    </div>

    <aside class="contact-side reveal">
      <div class="card-panel">
        <h2 class="h-sm"><?= icon('clock') ?> Office hours</h2>
        <dl class="hours">
          <?php foreach (cfg('site.hours', []) as [$d, $h]): ?>
          <div><dt><?= e($d) ?></dt><dd><?= e($h) ?></dd></div>
          <?php endforeach; ?>
        </dl>
      </div>
      <div class="map">
        <iframe title="Map showing our office" loading="lazy" referrerpolicy="no-referrer-when-downgrade"
          src="https://www.google.com/maps?q=<?= e(rawurlencode(full_address())) ?>&amp;output=embed"></iframe>
      </div>
    </aside>
  </div>
</section>

<?php require APP_ROOT . '/partials/footer.php'; ?>
