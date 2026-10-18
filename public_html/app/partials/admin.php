<?php
defined('APP') or exit;

function admin_counts(): array
{
    static $c = null;
    return $c ??= [
        'new_quotes'      => (int) db_val("SELECT COUNT(*) FROM quotes WHERE is_read = 0"),
        'unread_messages' => (int) db_val('SELECT COUNT(*) FROM messages WHERE is_read = 0'),
    ];
}

function admin_head(string $title, string $active = '', bool $bare = false): void
{
    send_security_headers(true);
    $admin = $bare ? null : current_admin();
    $counts = $bare ? [] : admin_counts();
    $nav = [
        'quotes'   => ['Requests', 'admin/', 'inbox', $counts['new_quotes'] ?? 0],
        'messages' => ['Messages', 'admin/messages.php', 'chat', $counts['unread_messages'] ?? 0],
        'account'  => ['Account', 'admin/account.php', 'settings', 0],
    ];
    ?><!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="robots" content="noindex, nofollow">
<title><?= e($title) ?> · <?= e(cfg('site.name')) ?> dashboard</title>
<link rel="icon" href="<?= e(asset('img/favicon.png')) ?>" type="image/png">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,700;12..96,800&family=Figtree:wght@400;500;600;700&display=swap">
<link rel="stylesheet" href="<?= e(asset('css/admin.css')) ?>">
</head>
<body class="<?= $bare ? 'a-bare' : 'a-app' ?>">
<?php require APP_ROOT . '/partials/icons.php'; ?>
<?php if (!$bare): ?>
<aside class="a-side">
  <a class="a-logo" href="<?= e(url('admin/')) ?>"><?= logo_file('logo_dark') ? '<img class="a-logo-img" src="' . e(asset('img/' . logo_file('logo_dark'))) . '" alt="' . e(cfg('site.name')) . '">' : logo_mark('a-mark') . '<span>' . e(cfg('site.name')) . '</span>' ?><small>Agent dashboard</small></a>
  <nav class="a-nav" aria-label="Dashboard">
    <?php foreach ($nav as $key => [$label, $href, $ic, $badge]): ?>
      <a href="<?= e(url($href)) ?>"<?= $active === $key ? ' aria-current="page"' : '' ?>><?= icon($ic) ?><span><?= e($label) ?></span><?php if ($badge): ?><b class="a-badge"><?= $badge ?></b><?php endif; ?></a>
    <?php endforeach; ?>
    <a href="<?= e(url('admin/export.php')) ?>"><?= icon('download') ?><span>Export CSV</span></a>
  </nav>
  <div class="a-side-foot">
    <a href="<?= e(url()) ?>" target="_blank" rel="noopener"><?= icon('globe') ?><span>View website</span></a>
    <form method="post" action="<?= e(url('admin/logout.php')) ?>"><?= csrf_field() ?><button type="submit"><?= icon('logout') ?><span>Sign out</span></button></form>
    <p class="a-user"><?= icon('user') ?><span><?= e($admin['name'] ?? '') ?></span></p>
  </div>
</aside>
<?php endif; ?>
<main class="a-main">
<?php
    if (!$bare && demo_enabled()): ?>
  <div class="a-flash a-flash-info a-demo"><?= icon('info') ?><span><b>Preview dashboard.</b> The requests marked "Sample request for the preview" are examples. Quotes you send from the preview website also show up here, but the preview resets from time to time. On your own hosting everything is saved permanently.</span></div>
<?php endif;
    $f = $bare ? null : flash();
    if ($f): ?>
  <div class="a-flash a-flash-<?= e($f[0]) ?>" role="status"><?= icon($f[0] === 'ok' ? 'check' : 'alert') ?><?= e($f[1]) ?></div>
<?php endif;
}

function admin_foot(): void
{
    ?>
</main>
<script src="<?= e(asset('js/admin.js')) ?>" defer></script>
</body>
</html>
<?php
}

function status_pill(string $status): string
{
    return '<span class="pill-s s-' . e($status) . '">' . e(QUOTE_STATUSES[$status] ?? $status) . '</span>';
}

function pager(int $page, int $pages, array $query): string
{
    if ($pages <= 1) {
        return '';
    }
    $link = function (int $p, string $label, bool $disabled = false, bool $current = false) use ($query) {
        if ($disabled) return '<span class="a-pg is-off">' . $label . '</span>';
        $q = http_build_query(array_merge($query, ['page' => $p]));
        return '<a class="a-pg' . ($current ? ' is-on' : '') . '" href="?' . e($q) . '"' . ($current ? ' aria-current="page"' : '') . '>' . $label . '</a>';
    };
    $out = '<nav class="a-pager" aria-label="Pages">' . $link($page - 1, '← Prev', $page <= 1);
    for ($p = max(1, $page - 2); $p <= min($pages, $page + 2); $p++) {
        $out .= $link($p, (string) $p, false, $p === $page);
    }
    return $out . $link($page + 1, 'Next →', $page >= $pages) . '</nav>';
}

function service_pill(?string $key): string
{
    $s = service((string) $key) ?? service('personal_auto');
    return '<span class="svc-pill svc-' . e((string) $key) . '">' . icon($s['icon']) . e($s['title']) . '</span>';
}
