<?php
require dirname(__DIR__) . '/app/bootstrap.php';
require APP_ROOT . '/partials/admin.php';

admin_session_start();

// System checks shown to whoever is installing the site.
$checks = [];
$checks[] = ['PHP ' . PHP_VERSION, PHP_VERSION_ID >= 80100, 'PHP 8.1+ required. Change it in cPanel > MultiPHP Manager.'];
$checks[] = ['Storage folder is writable', is_writable(STORAGE), 'Set /storage to permission 755 (or 775) in File Manager.'];
$checks[] = ['OpenSSL encryption', function_exists('openssl_encrypt'), 'Ask HostGator support to enable the OpenSSL extension.'];
$dbOk = false;
$dbError = '';
try {
    db();
    $dbOk = true;
} catch (Throwable $ex) {
    $dbError = $ex->getMessage();
}
$checks[] = ['Database connection (' . cfg('db.driver') . ')', $dbOk, 'Check the database settings in app/config.php. ' . $dbError];
$checks[] = ['Notification email set', cfg('mail.notify_to') !== 'hello@youragency.com', 'Set mail.notify_to in app/config.php to the agency inbox.'];
$checks[] = ['HTTPS', is_https(), 'Turn on SSL in cPanel (AutoSSL), then enable the HTTPS redirect in .htaccess.'];

if ($dbOk && (int) db_val('SELECT COUNT(*) FROM admins') > 0) {
    redirect(url('admin/login.php'));
}

$errors = [];
$old = ['name' => '', 'email' => ''];
if ($dbOk && $_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $old['name'] = str_in($_POST['name'] ?? '', 80);
    $old['email'] = strtolower(str_in($_POST['email'] ?? '', 160));
    $pass = (string) ($_POST['password'] ?? '');
    if ($old['name'] === '') $errors['name'] = 'Enter your name.';
    if (!filter_var($old['email'], FILTER_VALIDATE_EMAIL)) $errors['email'] = 'Enter a valid email.';
    if (strlen($pass) < 10) $errors['password'] = 'Use at least 10 characters.';
    if ($pass !== (string) ($_POST['password2'] ?? '')) $errors['password2'] = 'Passwords don\'t match.';
    if (!$errors) {
        db_exec('INSERT INTO admins (name, email, password_hash, created_at) VALUES (?, ?, ?, ?)', [$old['name'], $old['email'], password_hash($pass, PASSWORD_DEFAULT), now_utc()]);
        session_regenerate_id(true);
        $_SESSION['admin_id'] = (int) db()->lastInsertId();
        flash('Welcome! Your dashboard is ready. New quote requests will appear here.');
        redirect(url('admin/'));
    }
}

admin_head('Set up', '', true);
?>
<div class="a-auth">
  <div class="a-auth-card a-auth-wide">
    <div class="a-auth-brand"><?= logo_html() ?></div>
    <h1>Set up your dashboard</h1>
    <p class="a-muted">Create the first account. You can add more people later under Account.</p>

    <ul class="a-checks">
      <?php foreach ($checks as [$label, $ok, $fix]): ?>
      <li class="<?= $ok ? 'ok' : 'warn' ?>"><?= icon($ok ? 'check' : 'alert') ?><div><b><?= e($label) ?></b><?php if (!$ok): ?><small><?= e($fix) ?></small><?php endif; ?></div></li>
      <?php endforeach; ?>
    </ul>

    <?php if ($dbOk): ?>
    <form method="post" class="a-form" novalidate>
      <?= csrf_field() ?>
      <label class="a-fld"><span>Your name</span><input name="name" value="<?= e($old['name']) ?>" autocomplete="name" required><?php if (isset($errors['name'])): ?><em><?= e($errors['name']) ?></em><?php endif; ?></label>
      <label class="a-fld"><span>Email</span><input name="email" type="email" value="<?= e($old['email']) ?>" autocomplete="username" required><?php if (isset($errors['email'])): ?><em><?= e($errors['email']) ?></em><?php endif; ?></label>
      <label class="a-fld"><span>Password <small>(10+ characters)</small></span><input name="password" type="password" autocomplete="new-password" minlength="10" required><?php if (isset($errors['password'])): ?><em><?= e($errors['password']) ?></em><?php endif; ?></label>
      <label class="a-fld"><span>Confirm password</span><input name="password2" type="password" autocomplete="new-password" required><?php if (isset($errors['password2'])): ?><em><?= e($errors['password2']) ?></em><?php endif; ?></label>
      <button class="a-btn a-btn-primary a-btn-block" type="submit">Create account &amp; open dashboard</button>
    </form>
    <?php else: ?>
    <p class="a-alert">Fix the database connection above, then reload this page.</p>
    <?php endif; ?>
  </div>
</div>
<?php admin_foot(); ?>
