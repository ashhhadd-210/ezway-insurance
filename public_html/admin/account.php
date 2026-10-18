<?php
require dirname(__DIR__) . '/app/bootstrap.php';
require APP_ROOT . '/partials/admin.php';

$me = require_admin();
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $action = $_POST['action'] ?? '';
    if (demo_enabled() && in_array($action, ['password', 'profile', 'remove'], true)) {
        flash('Account changes are turned off on the preview. They work on your own site.', 'err');
        redirect(url('admin/account.php'));
    }
    $full = db_one('SELECT * FROM admins WHERE id = ?', [$me['id']]);

    if ($action === 'password') {
        $new = (string) ($_POST['new'] ?? '');
        if (!password_verify((string) ($_POST['current'] ?? ''), $full['password_hash'])) $errors['current'] = 'Your current password is incorrect.';
        if (strlen($new) < 10) $errors['new'] = 'Use at least 10 characters.';
        if ($new !== (string) ($_POST['new2'] ?? '')) $errors['new2'] = 'Passwords don\'t match.';
        if (!$errors) {
            db_exec('UPDATE admins SET password_hash = ? WHERE id = ?', [password_hash($new, PASSWORD_DEFAULT), $me['id']]);
            session_regenerate_id(true);
            flash('Password updated.');
            redirect(url('admin/account.php'));
        }
    } elseif ($action === 'profile') {
        $name = str_in($_POST['name'] ?? '', 80);
        $email = strtolower(str_in($_POST['email'] ?? '', 160));
        if ($name === '') $errors['name'] = 'Enter your name.';
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors['email'] = 'Enter a valid email.';
        elseif (db_val('SELECT id FROM admins WHERE email = ? AND id <> ?', [$email, $me['id']])) $errors['email'] = 'Another account already uses this email.';
        if (!$errors) {
            db_exec('UPDATE admins SET name = ?, email = ? WHERE id = ?', [$name, $email, $me['id']]);
            flash('Profile updated.');
            redirect(url('admin/account.php'));
        }
    } elseif ($action === 'add') {
        $name = str_in($_POST['name'] ?? '', 80);
        $email = strtolower(str_in($_POST['email'] ?? '', 160));
        $pass = (string) ($_POST['password'] ?? '');
        if ($name === '') $errors['add_name'] = 'Enter a name.';
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors['add_email'] = 'Enter a valid email.';
        elseif (db_val('SELECT id FROM admins WHERE email = ?', [$email])) $errors['add_email'] = 'This email already has an account.';
        if (strlen($pass) < 10) $errors['add_password'] = 'Use at least 10 characters.';
        if (!$errors) {
            db_exec('INSERT INTO admins (name, email, password_hash, created_at) VALUES (?, ?, ?, ?)', [$name, $email, password_hash($pass, PASSWORD_DEFAULT), now_utc()]);
            flash($name . ' can now sign in.');
            redirect(url('admin/account.php'));
        }
    } elseif ($action === 'remove') {
        $rid = (int) ($_POST['id'] ?? 0);
        if ($rid !== (int) $me['id']) {
            db_exec('DELETE FROM admins WHERE id = ?', [$rid]);
            flash('Account removed.');
        }
        redirect(url('admin/account.php'));
    }
}

$admins = db_all('SELECT id, name, email, last_login_at, created_at FROM admins ORDER BY created_at');
$err = fn (string $k) => isset($errors[$k]) ? '<em>' . e($errors[$k]) . '</em>' : '';

admin_head('Account', 'account');
?>
<header class="a-top">
  <div><h1>Account</h1><p class="a-muted">Your sign-in details and who can access the dashboard</p></div>
</header>

<div class="a-grid-2">
  <section class="a-card">
    <h2><?= icon('user') ?> Your profile</h2>
    <form method="post" class="a-form">
      <?= csrf_field() ?><input type="hidden" name="action" value="profile">
      <label class="a-fld"><span>Name</span><input name="name" value="<?= e($me['name']) ?>" required><?= $err('name') ?></label>
      <label class="a-fld"><span>Email (used to sign in)</span><input name="email" type="email" value="<?= e($me['email']) ?>" required><?= $err('email') ?></label>
      <button class="a-btn a-btn-primary" type="submit">Save profile</button>
    </form>
  </section>

  <section class="a-card">
    <h2><?= icon('lock') ?> Change password</h2>
    <form method="post" class="a-form">
      <?= csrf_field() ?><input type="hidden" name="action" value="password">
      <label class="a-fld"><span>Current password</span><input name="current" type="password" autocomplete="current-password" required><?= $err('current') ?></label>
      <label class="a-fld"><span>New password <small>(10+ characters)</small></span><input name="new" type="password" autocomplete="new-password" minlength="10" required><?= $err('new') ?></label>
      <label class="a-fld"><span>Confirm new password</span><input name="new2" type="password" autocomplete="new-password" required><?= $err('new2') ?></label>
      <button class="a-btn a-btn-primary" type="submit">Update password</button>
    </form>
  </section>

  <section class="a-card a-span-2">
    <h2><?= icon('users') ?> Team access</h2>
    <div class="a-table-wrap a-table-flat">
      <table class="a-table">
        <thead><tr><th>Name</th><th>Email</th><th>Last sign-in</th><th></th></tr></thead>
        <tbody>
          <?php foreach ($admins as $ad): ?>
          <tr>
            <td><b><?= e($ad['name']) ?></b><?= (int) $ad['id'] === (int) $me['id'] ? ' <span class="a-tag">You</span>' : '' ?></td>
            <td><?= e($ad['email']) ?></td>
            <td><?= $ad['last_login_at'] ? e(time_ago($ad['last_login_at'])) : '<span class="a-muted">Never</span>' ?></td>
            <td class="a-right">
              <?php if ((int) $ad['id'] !== (int) $me['id']): ?>
              <form method="post" data-confirm="Remove <?= e($ad['name']) ?>'s access?"><?= csrf_field() ?><input type="hidden" name="action" value="remove"><input type="hidden" name="id" value="<?= (int) $ad['id'] ?>"><button class="a-btn a-btn-sm a-btn-danger" type="submit"><?= icon('trash') ?> Remove</button></form>
              <?php endif; ?>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <details class="a-add" <?= array_intersect_key($errors, ['add_name' => 1, 'add_email' => 1, 'add_password' => 1]) ? 'open' : '' ?>>
      <summary class="a-btn"><?= icon('plus') ?> Add a team member</summary>
      <form method="post" class="a-form a-form-row">
        <?= csrf_field() ?><input type="hidden" name="action" value="add">
        <label class="a-fld"><span>Name</span><input name="name" required><?= $err('add_name') ?></label>
        <label class="a-fld"><span>Email</span><input name="email" type="email" required><?= $err('add_email') ?></label>
        <label class="a-fld"><span>Temporary password</span><input name="password" type="text" minlength="10" autocomplete="off" required><?= $err('add_password') ?></label>
        <button class="a-btn a-btn-primary" type="submit">Add account</button>
      </form>
    </details>
  </section>
</div>

<?php admin_foot(); ?>
