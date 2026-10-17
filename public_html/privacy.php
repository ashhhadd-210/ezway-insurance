<?php
require __DIR__ . '/app/bootstrap.php';

$page = [
    'slug'        => 'privacy',
    'path'        => 'privacy',
    'title'       => L('Privacy policy', 'Política de privacidad'),
    'description' => L('How EZ Way Insurance collects, uses and protects the information you share with us.', 'Cómo EZ Way Insurance recopila, usa y protege la información que compartes con nosotros.'),
];
$legal = cfg('site.legal_name');
require APP_ROOT . '/partials/header.php';
// NOTE: This is a starting template. Have it reviewed for Texas requirements before launch.
?>

<section class="page-hero page-hero-sm">
  <div class="wrap page-hero-in">
    <h1><?= e(L('Privacy policy', 'Política de privacidad')) ?></h1>
    <p class="lead"><?= e(L('Last updated', 'Última actualización')) ?> <?= e(lang() === 'es' ? date('j/n/Y', filemtime(__FILE__)) : date('F j, Y', filemtime(__FILE__))) ?></p>
  </div>
</section>

<section class="section section-top0">
  <div class="wrap prose">
    <p><?= e(L(
        $legal . ' ("we", "us") respects your privacy. This policy explains what we collect through this website, how we use it, and the choices you have.',
        $legal . ' ("nosotros") respeta tu privacidad. Esta política explica qué información recopilamos en este sitio, cómo la usamos y qué opciones tienes.'
    )) ?></p>

    <h2><?= e(L('Information we collect', 'Información que recopilamos')) ?></h2>
    <ul>
      <li><b><?= e(L('Quote and service requests:', 'Cotizaciones y solicitudes de servicio:')) ?></b> <?= e(L(
          'your name, date of birth, address, phone, email and contact preference; company details for commercial requests; for each driver, their name, date of birth, relationship, ID type and ID or license number; each vehicle\'s VIN, coverage choices and lienholder; and the answers and notes you add for other services.',
          'tu nombre, fecha de nacimiento, dirección, teléfono, correo y forma de contacto preferida; datos de tu compañía en solicitudes comerciales; de cada conductor, su nombre, fecha de nacimiento, parentesco, tipo de ID y número de ID o licencia; el VIN, la cobertura y la financiera de cada vehículo; y las respuestas y notas que agregues para otros servicios.'
      )) ?></li>
      <li><b><?= e(L('Messages:', 'Mensajes:')) ?></b> <?= e(L('the name, email, phone and message you send through our contact form.', 'el nombre, correo, teléfono y mensaje que nos envías en el formulario de contacto.')) ?></li>
      <li><b><?= e(L('Technical data:', 'Datos técnicos:')) ?></b> <?= e(L('basic information your browser sends, such as browser type. We store only a one-way scrambled version of your IP address, used to prevent spam.', 'información básica que envía tu navegador, como el tipo de navegador. Solo guardamos una versión cifrada de un solo sentido de tu dirección IP, para evitar spam.')) ?></li>
    </ul>

    <h2><?= e(L('How we use it', 'Cómo la usamos')) ?></h2>
    <p><?= e(L(
        'We use your information to prepare quotes, contact you about them, provide the services you request, answer your questions and service your policy. To quote coverage, we may share the information you provide with insurance companies we work with. We do not sell your personal information.',
        'Usamos tu información para preparar cotizaciones, contactarte, darte los servicios que pides, contestar tus preguntas y atender tu póliza. Para cotizar, podemos compartir tu información con las compañías de seguros con las que trabajamos. No vendemos tu información personal.'
    )) ?></p>

    <h2><?= e(L('How we protect it', 'Cómo la protegemos')) ?></h2>
    <p><?= e(L(
        'This site uses an encrypted (HTTPS) connection. Driver ID and license numbers are encrypted before they are stored and are visible only to authorized agency staff through a password-protected system. VINs are checked against the public U.S. National Highway Traffic Safety Administration (NHTSA) database to identify vehicles.',
        'Este sitio usa una conexión cifrada (HTTPS). Los números de ID y licencia se cifran antes de guardarse y solo los ve personal autorizado de la agencia en un sistema con contraseña. Los VIN se consultan en la base de datos pública de la NHTSA de EE. UU. para identificar los vehículos.'
    )) ?></p>

    <h2><?= e(L('How long we keep it', 'Cuánto tiempo la guardamos')) ?></h2>
    <p><?= e(L('We keep requests and messages only as long as needed to serve you and to meet legal and regulatory requirements, then delete them.', 'Guardamos las solicitudes y mensajes solo el tiempo necesario para atenderte y cumplir con la ley, y después los borramos.')) ?></p>

    <h2><?= e(L('Your choices', 'Tus opciones')) ?></h2>
    <p><?= e(L('You can ask us to access, correct or delete your information at any time by calling us at', 'Puedes pedirnos ver, corregir o borrar tu información en cualquier momento llamando al')) ?> <a href="<?= e(tel_href(cfg('site.phone'))) ?>"><?= e(cfg('site.phone')) ?></a>, <?= e(L('sending us a message from our contact page or visiting any of our offices.', 'enviándonos un mensaje desde la página de contacto o visitando cualquiera de nuestras oficinas.')) ?> <?= e(L('You can opt out of marketing messages at any time; for texts, reply STOP.', 'Puedes dejar de recibir mensajes de promoción cuando quieras; para mensajes de texto, responde STOP.')) ?></p>

    <h2><?= e(L('Contact', 'Contacto')) ?></h2>
    <p><?= e($legal) ?><br><?= e(full_address()) ?><br><?= e(cfg('site.phone')) ?></p>
  </div>
</section>

<?php require APP_ROOT . '/partials/footer.php'; ?>
