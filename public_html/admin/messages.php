<?php
require dirname(__DIR__) . '/app/bootstrap.php';
require APP_ROOT . '/partials/admin.php';

require_admin();

$id = (int) ($_GET['id'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $mid = (int) ($_POST['id'] ?? 0);
    if (($_POST['action'] ?? '') === 'delete') {
        db_exec('DELETE FROM messages WHERE id = ?', [$mid]);
        flash('Message deleted.');
        redirect(url('admin/messages.php'));
    }
    if (($_POST['action'] ?? '') === 'unread') {
        db_exec('UPDATE messages SET is_read = 0 WHERE id = ?', [$mid]);
        flash('Marked as unread.');
        redirect(url('admin/messages.php'));
    }
}

$page = max(1, (int) ($_GET['page'] ?? 1));
$per = 30;
$total = (int) db_val('SELECT COUNT(*) FROM messages');
$pages = max(1, (int) ceil($total / $per));
$list = db_all('SELECT id, is_read, name, email, topic, message, created_at FROM messages ORDER BY created_at DESC, id DESC LIMIT ' . $per . ' OFFSET ' . (($page - 1) * $per));

$current = $id ? db_one('SELECT * FROM messages WHERE id = ?', [$id]) : null;
if ($current && !$current['is_read']) {
    db_exec('UPDATE messages SET is_read = 1 WHERE id = ?', [$id]);
    foreach ($list as &$m) {
        if ((int) $m['id'] === $id) $m['is_read'] = 1;
    }
    unset($m);
}

admin_head('Messages', 'messages');
?>
<header class="a-top">
  <div>
    <h1>Messages</h1>
    <p class="a-muted">From the contact form on your website</p>
  </div>
</header>

<?php if (!$list): ?>
  <div class="a-empty">
    <span class="a-empty-ic"><?= icon('chat') ?></span>
    <h2>No messages yet</h2>
    <p>Messages sent from your Contact page will appear here.</p>
  </div>
<?php else: ?>
<div class="a-mail <?= $current ? 'has-open' : '' ?>">
  <div class="a-mail-list">
    <?php foreach ($list as $m): ?>
    <a class="a-mail-row <?= $m['is_read'] ? '' : 'is-unread' ?> <?= $current && (int) $current['id'] === (int) $m['id'] ? 'is-on' : '' ?>" href="?<?= e(http_build_query(['id' => $m['id'], 'page' => $page])) ?>">
      <span class="a-av"><?= e(mb_strtoupper(mb_substr($m['name'], 0, 1))) ?></span>
      <span class="a-mail-t">
        <span class="a-mail-top"><b><?= e($m['name']) ?></b><small><?= e(time_ago($m['created_at'])) ?></small></span>
        <span class="a-mail-topic"><?= e($m['topic']) ?></span>
        <small class="a-mail-snip"><?= e(mb_strimwidth($m['message'], 0, 90, '…')) ?></small>
      </span>
    </a>
    <?php endforeach; ?>
    <?= pager($page, $pages, []) ?>
  </div>

  <div class="a-mail-view">
    <?php if ($current): ?>
    <article class="a-card">
      <a class="a-back a-only-mobile" href="?page=<?= $page ?>"><?= icon('arrow-left') ?> All messages</a>
      <header class="a-msg-h">
        <span class="a-av a-av-lg"><?= e(mb_strtoupper(mb_substr($current['name'], 0, 1))) ?></span>
        <div>
          <h2><?= e($current['name']) ?></h2>
          <p class="a-muted"><?= e($current['topic']) ?> · <?= e(fmt_date($current['created_at'])) ?></p>
        </div>
      </header>
      <div class="a-msg-body"><?= nl2br(e($current['message'])) ?></div>
      <dl class="a-dl">
        <div><dt>Email</dt><dd><a href="mailto:<?= e($current['email']) ?>"><?= e($current['email']) ?></a></dd></div>
        <?php if ($current['phone']): ?><div><dt>Phone</dt><dd><a href="<?= e(tel_href($current['phone'])) ?>"><?= e($current['phone']) ?></a></dd></div><?php endif; ?>
      </dl>
      <div class="a-actions">
        <a class="a-btn a-btn-primary" href="mailto:<?= e($current['email']) ?>?subject=<?= e(rawurlencode('Re: ' . $current['topic'])) ?>"><?= icon('mail') ?> Reply by email</a>
        <?php if ($current['phone']): ?><a class="a-btn" href="<?= e(tel_href($current['phone'])) ?>"><?= icon('phone') ?> Call</a><?php endif; ?>
        <form method="post"><?= csrf_field() ?><input type="hidden" name="id" value="<?= (int) $current['id'] ?>"><input type="hidden" name="action" value="unread"><button class="a-btn" type="submit"><?= icon('inbox') ?> Mark unread</button></form>
        <form method="post" data-confirm="Delete this message permanently?"><?= csrf_field() ?><input type="hidden" name="id" value="<?= (int) $current['id'] ?>"><input type="hidden" name="action" value="delete"><button class="a-btn a-btn-danger" type="submit"><?= icon('trash') ?> Delete</button></form>
      </div>
    </article>
    <?php else: ?>
    <div class="a-empty a-empty-sm"><span class="a-empty-ic"><?= icon('chat') ?></span><p>Select a message to read it.</p></div>
    <?php endif; ?>
  </div>
</div>
<?php endif; ?>

<?php admin_foot(); ?>
