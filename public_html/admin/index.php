<?php
require dirname(__DIR__) . '/app/bootstrap.php';
require APP_ROOT . '/partials/admin.php';

require_admin();

$status = (string) ($_GET['status'] ?? '');
$svc = (string) ($_GET['svc'] ?? '');
$q = str_in($_GET['q'] ?? '', 80);
$page = max(1, (int) ($_GET['page'] ?? 1));
$per = 25;

$where = [];
$params = [];
if (isset(QUOTE_STATUSES[$status])) {
    $where[] = 'status = ?';
    $params[] = $status;
} else {
    $status = '';
}
if (service($svc)) {
    $where[] = 'service = ?';
    $params[] = $svc;
} else {
    $svc = '';
}
if ($q !== '') {
    $where[] = '(search LIKE ? OR ref LIKE ?)';
    $like = '%' . mb_strtolower(str_replace(['%', '_'], '', $q)) . '%';
    $digitsQ = digits($q);
    $params[] = strlen($digitsQ) >= 4 && strlen($digitsQ) === strlen(preg_replace('/[\s()\-.]/', '', $q)) ? '%' . $digitsQ . '%' : $like;
    $params[] = '%' . strtoupper($q) . '%';
}
$sqlWhere = $where ? 'WHERE ' . implode(' AND ', $where) : '';

$total = (int) db_val("SELECT COUNT(*) FROM quotes $sqlWhere", $params);
$pages = max(1, (int) ceil($total / $per));
$page = min($page, $pages);
$rows = db_all("SELECT id, ref, service, lang, status, is_read, first_name, last_name, email, phone, city, state, drivers_count, vehicles_count, data, created_at
                FROM quotes $sqlWhere ORDER BY created_at DESC, id DESC LIMIT $per OFFSET " . (($page - 1) * $per), $params);

$byStatus = [];
foreach (db_all('SELECT status, COUNT(*) AS n FROM quotes GROUP BY status') as $r) {
    $byStatus[$r['status']] = (int) $r['n'];
}
$allCount = array_sum($byStatus);
$bySvc = [];
foreach (db_all('SELECT service, COUNT(*) AS n FROM quotes GROUP BY service') as $r) {
    $bySvc[$r['service']] = (int) $r['n'];
}
$keep = fn (array $extra) => http_build_query(array_filter(array_merge(['status' => $status, 'svc' => $svc, 'q' => $q], $extra), fn ($v) => $v !== '' && $v !== null));
$todayStart = (new DateTimeImmutable('today'))->setTimezone(new DateTimeZone('UTC'))->format('Y-m-d H:i:s');
$weekStart = (new DateTimeImmutable('monday this week'))->setTimezone(new DateTimeZone('UTC'))->format('Y-m-d H:i:s');
$stats = [
    ['Unread', admin_counts()['new_quotes'], 'inbox', 'teal'],
    ['Today', (int) db_val('SELECT COUNT(*) FROM quotes WHERE created_at >= ?', [$todayStart]), 'clock', 'amber'],
    ['This week', (int) db_val('SELECT COUNT(*) FROM quotes WHERE created_at >= ?', [$weekStart]), 'calendar', 'navy'],
    ['Won', $byStatus['won'] ?? 0, 'star', 'green'],
];

admin_head('Requests', 'quotes');
?>
<header class="a-top">
  <div>
    <h1>Requests</h1>
    <p class="a-muted"><?= $allCount ?> total · newest first</p>
  </div>
  <form class="a-search" method="get" role="search">
    <?php if ($status): ?><input type="hidden" name="status" value="<?= e($status) ?>"><?php endif; ?>
    <?php if ($svc): ?><input type="hidden" name="svc" value="<?= e($svc) ?>"><?php endif; ?>
    <?= icon('search') ?>
    <input name="q" value="<?= e($q) ?>" placeholder="Search name, company, phone, email, VIN or ref" aria-label="Search requests">
    <?php if ($q !== ''): ?><a class="a-search-x" href="?<?= e($keep(['q' => ''])) ?>" aria-label="Clear search"><?= icon('x') ?></a><?php endif; ?>
  </form>
</header>

<section class="a-stats">
  <?php foreach ($stats as [$label, $n, $ic, $tone]): ?>
  <div class="a-stat t-<?= $tone ?>"><span class="a-stat-ic"><?= icon($ic) ?></span><div><b><?= $n ?></b><small><?= e($label) ?></small></div></div>
  <?php endforeach; ?>
</section>

<nav class="a-chips" aria-label="Filter by service">
  <a href="?<?= e($keep(['svc' => ''])) ?>" class="<?= $svc === '' ? 'is-on' : '' ?>"><?= icon('grid') ?>All services <span><?= $allCount ?></span></a>
  <?php foreach (services() as $key => $s): ?>
  <a href="?<?= e($keep(['svc' => $key])) ?>" class="<?= $svc === $key ? 'is-on' : '' ?>"><?= icon($s['icon']) ?><?= e($s['title']) ?> <span><?= $bySvc[$key] ?? 0 ?></span></a>
  <?php endforeach; ?>
</nav>
<nav class="a-chips" aria-label="Filter by status">
  <a href="?<?= e($keep(['status' => ''])) ?>" class="<?= $status === '' ? 'is-on' : '' ?>">Any status</a>
  <?php foreach (QUOTE_STATUSES as $key => $label): ?>
  <a href="?<?= e($keep(['status' => $key])) ?>" class="<?= $status === $key ? 'is-on' : '' ?>"><i class="dot s-<?= $key ?>"></i><?= e($label) ?> <span><?= $byStatus[$key] ?? 0 ?></span></a>
  <?php endforeach; ?>
</nav>

<?php if (!$rows): ?>
  <div class="a-empty">
    <span class="a-empty-ic"><?= icon('inbox') ?></span>
    <?php if ($q !== '' || $status !== '' || $svc !== ''): ?>
      <h2>No matches</h2><p>Try a different search or filter.</p>
    <?php else: ?>
      <h2>No requests yet</h2><p>When someone sends the quote form on your website, it shows up here and you get an email.</p>
      <a class="a-btn a-btn-primary" href="<?= e(url('quote')) ?>" target="_blank" rel="noopener">Open the quote form</a>
    <?php endif; ?>
  </div>
<?php else: ?>
  <div class="a-table-wrap">
    <table class="a-table">
      <thead>
        <tr><th>Customer</th><th>Contact</th><th>Service</th><th>Request</th><th>Status</th><th>Received</th></tr>
      </thead>
      <tbody>
      <?php foreach ($rows as $r):
          $data = json_decode($r['data'], true) ?: [];
          $data['service'] ??= $r['service'];
          $isAuto = (service($r['service'])['flow'] ?? 'auto') === 'auto';
          $summary = request_summary($data);
          $company = $data['applicant']['company'] ?? '';
          $href = url('admin/quote.php?id=' . $r['id']); ?>
        <tr class="<?= $r['is_read'] ? '' : 'is-unread' ?>" data-href="<?= e($href) ?>">
          <td>
            <a class="a-cust" href="<?= e($href) ?>">
              <span class="a-av"><?= e(mb_strtoupper(mb_substr($r['first_name'], 0, 1) . mb_substr($r['last_name'], 0, 1))) ?></span>
              <span><b><?= e($r['first_name'] . ' ' . $r['last_name']) ?></b><small><?= $company ? e($company) . ' · ' : '' ?><span class="mono"><?= e($r['ref']) ?></span><?= $r['lang'] === 'es' ? ' <span class="a-lang">ES</span>' : '' ?></small></span>
            </a>
          </td>
          <td><span class="a-stack"><span><?= e($r['phone']) ?></span><small><?= e($r['email']) ?></small></span></td>
          <td><?= service_pill($r['service']) ?></td>
          <td>
            <span class="a-stack">
              <?php if ($isAuto): ?>
              <span><?= icon('users') ?> <?= (int) $r['drivers_count'] ?> · <?= icon('car') ?> <?= (int) $r['vehicles_count'] ?> · <?= e($r['city']) ?></span>
              <?php else: ?>
              <span><?= icon('pin') ?> <?= e($r['city']) ?>, <?= e($r['state']) ?></span>
              <?php endif; ?>
              <small title="<?= e($summary) ?>"><?= e($summary) ?></small>
            </span>
          </td>
          <td><?= status_pill($r['status']) ?></td>
          <td><span class="a-stack"><span><?= e(time_ago($r['created_at'])) ?></span><small><?= e(fmt_date($r['created_at'], 'M j, g:i A')) ?></small></span></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?= pager($page, $pages, array_filter(['status' => $status, 'svc' => $svc, 'q' => $q])) ?>
<?php endif; ?>

<?php admin_foot(); ?>
