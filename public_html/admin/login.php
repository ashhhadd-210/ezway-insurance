<?php
require dirname(__DIR__) . '/app/bootstrap.php';
require APP_ROOT . '/partials/admin.php';

if (current_admin()) {
    redirect(url('admin/'));
}
if ((int) db_val('SELECT COUNT(*) FROM admins') === 0) {
    redirect(url('admin/setup.php'));
}

$error = '';
$email = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $email = strtolower(str_in($_POST['email'] ?? '', 160));
    $pass = (string) ($_POST['password'] ?? '');
    $key = ip_hash() . '|' . $email;

    if (!throttle('login', $key, 5, 900, false) || !throttle('login-ip', ip_hash(), 25, 3600, false)) {
        $error = 'Too many attempts. Please wait 15 minutes and try again.';
    } else {
        $admin = db_one('SELECT * FROM admins WHERE email = ?', [$email]);
        if ($admin && password_verify($pass, $admin['password_hash'])) {
            throttle_clear('login', $key);
            if (password_needs_rehash($admin['password_hash'], PASSWORD_DEFAULT)) {
                db_exec('UPDATE admins SET password_hash = ? WHERE id = ?', [password_hash($pass, PASSWORD_DEFAULT), $admin['id']]);
            }
            session_regenerate_id(true);
            $_SESSION['admin_id'] = (int) $admin['id'];
            $_SESSION['csrf'] = bin2hex(random_bytes(32));
            db_exec('UPDATE admins SET last_login_at = ? WHERE id = ?', [now_utc(), $admin['id']]);
            redirect(url('admin/'));
        }
        throttle('login', $key, PHP_INT_MAX, 900);
        throttle('login-ip', ip_hash(), PHP_INT_MAX, 3600);
        usleep(random_int(200000, 400000));
        $error = 'That email and password don\'t match.';
    }
}

admin_head('Sign in', '', true);
?>
<div class="a-auth">
  <div class="a-auth-card">
    <div class="a-auth-brand"><?= logo_html() ?></div>
    <h1>Welcome back</h1>
    <p class="a-muted">Sign in to see your quote requests.</p>
    <?php if ($error): ?><p class="a-alert" role="alert"><?= icon('alert') ?><?= e($error) ?></p><?php endif; ?>
    <form method="post" class="a-form">
      <?= csrf_field() ?>
      <label class="a-fld"><span>Email</span><input name="email" type="email" value="<?= e($email) ?>" autocomplete="username" required autofocus></label>
      <label class="a-fld"><span>Password</span>
        <span class="a-pw"><input name="password" type="password" autocomplete="current-password" required><button type="button" class="a-pw-t" data-pw aria-label="Show password"><?= icon('eye') ?></button></span>
      </label>
      <button class="a-btn a-btn-primary a-btn-block" type="submit"><?= icon('lock') ?> Sign in</button>
    </form>
    <p class="a-auth-foot"><a href="<?= e(url()) ?>">← Back to website</a></p>
  </div>
</div>
<?php admin_foot(); ?>
