<?php
require dirname(__DIR__) . '/app/bootstrap.php';
require APP_ROOT . '/partials/admin.php';

require_admin();

$id = (int) ($_GET['id'] ?? 0);
$row = db_one('SELECT * FROM quotes WHERE id = ?', [$id]);
if (!$row) {
    flash('That request no longer exists.', 'err');
    redirect(url('admin/'));
}
$self = url('admin/quote.php?id=' . $id);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $action = $_POST['action'] ?? '';
    if ($action === 'status' && isset(QUOTE_STATUSES[$_POST['status'] ?? ''])) {
        db_exec('UPDATE quotes SET status = ?, updated_at = ? WHERE id = ?', [$_POST['status'], now_utc(), $id]);
        flash('Status changed to "' . QUOTE_STATUSES[$_POST['status']] . '".');
    } elseif ($action === 'notes') {
        db_exec('UPDATE quotes SET notes = ?, updated_at = ? WHERE id = ?', [str_in($_POST['notes'] ?? '', 5000), now_utc(), $id]);
        flash('Notes saved.');
    } elseif ($action === 'unread') {
        db_exec('UPDATE quotes SET is_read = 0 WHERE id = ?', [$id]);
        flash('Marked as unread.');
        redirect(url('admin/'));
    } elseif ($action === 'delete') {
        db_exec('DELETE FROM quotes WHERE id = ?', [$id]);
        flash('Request ' . $row['ref'] . ' was deleted.');
        redirect(url('admin/'));
    }
    redirect($self);
}

if (!$row['is_read']) {
    db_exec('UPDATE quotes SET is_read = 1 WHERE id = ?', [$id]);
}

$q = json_decode($row['data'], true) ?: [];
$q['service'] ??= $row['service'] ?? 'personal_auto';
$svc = service($q['service']) ?? service('personal_auto');
$isAuto = $svc['flow'] === 'auto';
$a = $q['applicant'] + ['company' => '', 'business_type' => ''];
$addr = $q['address'];
$cov = $q['coverage'] ?? null;
$name = $a['first'] . ' ' . $a['last'];
$addrLine = trim($addr['street'] . ($addr['unit'] ? ', ' . $addr['unit'] : ''));
$cityLine = $addr['city'] . ', ' . $addr['state'] . ' ' . $addr['zip'];
$isEs = ($row['lang'] ?? 'en') === 'es';
$emailSubject = $isEs ? 'Tu cotización de ' . mb_strtolower(with_lang('es', fn () => service_title($q['service']))) . ' (' . $row['ref'] . ')' : 'Your ' . mb_strtolower($svc['title']) . ' quote (' . $row['ref'] . ')';

$prev = db_val('SELECT id FROM quotes WHERE created_at > ? OR (created_at = ? AND id > ?) ORDER BY created_at ASC, id ASC LIMIT 1', [$row['created_at'], $row['created_at'], $id]);
$next = db_val('SELECT id FROM quotes WHERE created_at < ? OR (created_at = ? AND id < ?) ORDER BY created_at DESC, id DESC LIMIT 1', [$row['created_at'], $row['created_at'], $id]);

admin_head($name . ' · ' . $row['ref'], 'quotes');
?>
<header class="a-top a-top-detail">
  <div>
    <a class="a-back" href="<?= e(url('admin/')) ?>"><?= icon('arrow-left') ?> All requests</a>
    <h1><?= e($name) ?> <?= status_pill($row['status']) ?></h1>
    <p class="a-muted a-meta-line">
      <?= service_pill($q['service']) ?>
      <?php if ($isEs): ?><span class="a-lang a-lang-lg" title="This customer used the Spanish site">Español</span><?php endif; ?>
      <span><span class="mono"><?= e($row['ref']) ?></span> · Received <?= e(fmt_date($row['created_at'])) ?> (<?= e(time_ago($row['created_at'])) ?>)</span>
    </p>
  </div>
  <div class="a-actions">
    <a class="a-btn a-btn-primary" href="<?= e(tel_href($a['phone'])) ?>"><?= icon('phone') ?> Call</a>
    <a class="a-btn" href="sms:+1<?= e(substr(digits($a['phone']), -10)) ?>"><?= icon('chat') ?> Text</a>
    <a class="a-btn" href="mailto:<?= e($a['email']) ?>?subject=<?= e(rawurlencode($emailSubject)) ?>"><?= icon('mail') ?> Email</a>
    <button class="a-btn a-btn-icon" type="button" data-print aria-label="Print"><?= icon('printer') ?></button>
    <span class="a-nav-pn">
      <?php if ($prev): ?><a class="a-btn a-btn-icon" href="<?= e(url('admin/quote.php?id=' . $prev)) ?>" aria-label="Newer request" title="Newer"><?= icon('arrow-left') ?></a><?php endif; ?>
      <?php if ($next): ?><a class="a-btn a-btn-icon" href="<?= e(url('admin/quote.php?id=' . $next)) ?>" aria-label="Older request" title="Older"><?= icon('arrow-right') ?></a><?php endif; ?>
    </span>
  </div>
</header>

<?php if ($isEs): ?>
<div class="a-flash a-flash-info"><?= icon('language') ?> This customer filled out the form in Spanish. Reply in Spanish.</div>
<?php endif; ?>

<div class="a-detail">
  <div class="a-col">

    <section class="a-card">
      <h2><?= icon('user') ?> Contact</h2>
      <dl class="a-dl">
        <?php if ($a['company']): ?>
        <div><dt>Company</dt><dd><?= e($a['company']) ?><?= $a['business_type'] ? ' <span class="a-muted">· ' . e($a['business_type']) . '</span>' : '' ?></dd></div>
        <?php endif; ?>
        <div><dt>Name</dt><dd><?= e($name) ?></dd></div>
        <div><dt>Date of birth</dt><dd><?= e(fmt_dob($a['dob'])) ?></dd></div>
        <div><dt>Phone</dt><dd><a href="<?= e(tel_href($a['phone'])) ?>"><?= e($a['phone']) ?></a></dd></div>
        <div><dt>Email</dt><dd><a href="mailto:<?= e($a['email']) ?>"><?= e($a['email']) ?></a></dd></div>
        <div><dt>Prefers</dt><dd><?= e(contact_prefs()[$a['contact_pref']] ?? '') ?></dd></div>
        <div><dt><?= $q['service'] === 'home' ? 'Property address' : 'Address' ?></dt><dd><?= $addrLine !== '' ? e($addrLine) . '<br>' : '' ?><?= e($cityLine) ?> <a class="a-link-sm" href="https://www.google.com/maps/search/?api=1&amp;query=<?= e(rawurlencode(trim($addrLine . ', ' . $cityLine, ', '))) ?>" target="_blank" rel="noopener">Map ↗</a></dd></div>
        <?php if (!empty($addr['residence'])): ?><div><dt>Home</dt><dd><?= $addr['residence'] === 'own' ? 'Owns their home' : 'Rents their home' ?></dd></div><?php endif; ?>
      </dl>
    </section>

    <?php if (!$isAuto): ?>
    <section class="a-card">
      <h2><?= icon($svc['icon']) ?> <?= e($svc['title']) ?> request</h2>
      <?php $rows = details_rows($q); ?>
      <?php if ($rows): ?>
      <dl class="a-dl">
        <?php foreach ($rows as $label => $value): ?>
        <div><dt><?= e($label) ?></dt><dd><?= nl2br(e($value)) ?></dd></div>
        <?php endforeach; ?>
      </dl>
      <?php else: ?>
      <p class="a-muted">No extra details were given. Call the customer to learn more.</p>
      <?php endif; ?>
    </section>
    <?php else: ?>

    <section class="a-card">
      <h2><?= icon('users') ?> Drivers <span class="a-count"><?= count($q['drivers']) ?></span></h2>
      <div class="a-items">
        <?php foreach ($q['drivers'] as $d):
            $d += ['id_kind' => ''];
            $lic = decrypt_str($d['license_enc'] ?? '');
            $where = match ($d['id_type']) {
                'us'      => ['Issuing state', US_STATES[$d['license_state']] ?? $d['license_state']],
                'foreign' => ['Issuing country', $d['license_country']],
                default   => ['Type of ID', implode(' · ', array_filter([id_kinds()[$d['id_kind']] ?? '—', US_STATES[$d['license_state'] ?? ''] ?? '', $d['license_country'] ?? '']))],
            }; ?>
        <article class="a-item">
          <header>
            <span class="a-av"><?= e(mb_strtoupper(mb_substr($d['first'], 0, 1) . mb_substr($d['last'], 0, 1))) ?></span>
            <div><b><?= e($d['first'] . ' ' . $d['last']) ?></b><small><?= e(relationships()[$d['relationship']] ?? '') ?></small></div>
          </header>
          <dl class="a-dl a-dl-2">
            <div><dt>Date of birth</dt><dd><?= e(fmt_dob($d['dob'])) ?></dd></div>
            <div><dt>ID type</dt><dd><span class="a-tag <?= $d['id_type'] !== 'us' ? 't-amber' : '' ?>"><?= icon(['us' => 'flag', 'foreign' => 'globe'][$d['id_type']] ?? 'id') ?><?= e(id_type_label($d['id_type'])) ?></span></dd></div>
            <div><dt><?= e($where[0]) ?></dt><dd><?= e($where[1]) ?></dd></div>
            <div><dt><?= $d['id_type'] === 'us' || $d['id_type'] === 'foreign' ? 'License number' : 'ID number' ?></dt><dd>
              <?php if ($lic === '' && ($d['license_last4'] ?? '') === ''): ?>
                <span class="a-muted">Not given</span>
              <?php else: ?>
              <span class="a-secret" data-secret>
                <span class="mono" data-masked>••••••<?= e($d['license_last4'] ?? '') ?></span>
                <span class="mono" data-plain hidden><?= e($lic !== '' ? $lic : 'Unable to decrypt') ?></span>
                <button type="button" class="a-mini" data-reveal aria-label="Show ID number"><?= icon('eye') ?></button>
                <button type="button" class="a-mini" data-copy="<?= e($lic) ?>" aria-label="Copy ID number"><?= icon('doc') ?></button>
              </span>
              <?php endif; ?>
            </dd></div>
          </dl>
        </article>
        <?php endforeach; ?>
      </div>
    </section>

    <section class="a-card">
      <h2><?= icon('car') ?> Vehicles <span class="a-count"><?= count($q['vehicles']) ?></span></h2>
      <div class="a-items">
        <?php foreach ($q['vehicles'] as $v): $v += ['lien' => '', 'lienholder' => '']; ?>
        <article class="a-item">
          <header>
            <span class="a-av a-av-car"><?= icon($q['service'] === 'commercial_auto' ? 'truck' : 'car') ?></span>
            <div>
              <b><?= e(vehicle_title($v) . ($v['trim'] ? ' ' . $v['trim'] : '')) ?></b>
              <small><?= e($v['body'] ?: '') ?> <?= $v['decoded'] ? '<span class="a-ok">' . icon('check') . 'VIN verified</span>' : '<span class="a-warn">' . icon('alert') . 'Not verified</span>' ?></small>
            </div>
          </header>
          <p class="a-vin"><span class="mono"><?= e($v['vin']) ?></span>
            <button type="button" class="a-mini" data-copy="<?= e($v['vin']) ?>" aria-label="Copy VIN"><?= icon('doc') ?></button>
            <a class="a-link-sm" href="https://vpic.nhtsa.dot.gov/decoder/Decoder?VIN=<?= e($v['vin']) ?>" target="_blank" rel="noopener">Decode ↗</a>
          </p>
          <div class="a-tags">
            <?php if ($v['full_coverage']): ?>
              <span class="a-tag t-teal"><?= icon('shield-plus') ?>Full coverage (comp + collision)</span>
              <?php if ($v['deductible']): ?><span class="a-tag">Deductible: <?= e(deductibles()[$v['deductible']] ?? '') ?></span><?php endif; ?>
              <?php if ($v['lien'] === 'yes'): ?><span class="a-tag t-amber"><?= icon('doc') ?>Lienholder: <?= e($v['lienholder'] ?: 'name not given') ?></span>
              <?php elseif ($v['lien'] === 'no'): ?><span class="a-tag">No lienholder (paid off)</span><?php endif; ?>
            <?php else: ?>
              <span class="a-tag t-off">Liability only</span>
            <?php endif; ?>
            <span class="a-tag <?= $v['roadside'] ? 't-teal' : 't-off' ?>"><?= icon('wrench') ?>Roadside <?= $v['roadside'] ? '' : '— no' ?></span>
            <span class="a-tag <?= $v['rental'] ? 't-teal' : 't-off' ?>"><?= icon('key') ?>Rental <?= $v['rental'] ? '' : '— no' ?></span>
          </div>
        </article>
        <?php endforeach; ?>
      </div>
    </section>

    <section class="a-card">
      <h2><?= icon('shield') ?> Policy coverage</h2>
      <dl class="a-dl">
        <div><dt>Liability</dt><dd><?= e(liability_levels($q['service'])[$cov['liability']][0] ?? '') ?></dd></div>
        <div><dt>Personal injury protection</dt><dd><?= $cov['pip'] ? '<span class="a-tag t-teal">' . icon('check') . 'Yes</span>' : '<span class="a-tag t-off">No</span>' ?></dd></div>
        <div><dt>Uninsured motorist</dt><dd><?= $cov['um'] ? '<span class="a-tag t-teal">' . icon('check') . 'Yes</span>' : '<span class="a-tag t-off">No</span>' ?></dd></div>
        <div><dt>Coverage start</dt><dd><?= $cov['start_date'] ? e(date('M j, Y', strtotime($cov['start_date']))) : '<span class="a-muted">Not given</span>' ?></dd></div>
        <div><dt>Currently insured</dt><dd><?= $cov['currently_insured'] ? e(ucfirst($cov['currently_insured'])) . ($cov['current_carrier'] ? ' · ' . e($cov['current_carrier']) : '') . (!empty($cov['current_expiration']) ? ' · <b>expires ' . e(date('M j, Y', strtotime($cov['current_expiration']))) . '</b>' : '') : '<span class="a-muted">Not given</span>' ?></dd></div>
        <?php if ($cov['comments']): ?><div class="a-dl-full"><dt>Customer notes</dt><dd class="a-quote"><?= nl2br(e($cov['comments'])) ?></dd></div><?php endif; ?>
      </dl>
    </section>
    <?php endif; ?>
  </div>

  <aside class="a-col a-aside">
    <section class="a-card">
      <h2><?= icon('settings') ?> Status</h2>
      <form method="post" class="a-status-form">
        <?= csrf_field() ?><input type="hidden" name="action" value="status">
        <div class="a-status-grid">
          <?php foreach (QUOTE_STATUSES as $key => $label): ?>
          <label><input type="radio" name="status" value="<?= $key ?>" <?= $row['status'] === $key ? 'checked' : '' ?> data-autosubmit><span><i class="dot s-<?= $key ?>"></i><?= e($label) ?></span></label>
          <?php endforeach; ?>
        </div>
        <noscript><button class="a-btn" type="submit">Update</button></noscript>
      </form>
    </section>

    <section class="a-card">
      <h2><?= icon('edit') ?> Internal notes</h2>
      <form method="post">
        <?= csrf_field() ?><input type="hidden" name="action" value="notes">
        <textarea class="a-textarea" name="notes" rows="6" placeholder="Carriers quoted, prices, follow-up dates… (only visible here)"><?= e($row['notes'] ?? '') ?></textarea>
        <button class="a-btn a-btn-primary a-btn-block" type="submit">Save notes</button>
      </form>
    </section>

    <section class="a-card a-meta">
      <h2><?= icon('info') ?> Details</h2>
      <dl class="a-dl">
        <div><dt>Reference</dt><dd class="mono"><?= e($row['ref']) ?></dd></div>
        <div><dt>Submitted</dt><dd><?= e(fmt_date($row['created_at'])) ?></dd></div>
        <div><dt>Last updated</dt><dd><?= e(fmt_date($row['updated_at'])) ?></dd></div>
        <div><dt>Device</dt><dd class="a-small"><?= e(preg_match('/Mobile|Android|iPhone/i', (string) $row['user_agent']) ? 'Phone' : 'Computer') ?></dd></div>
      </dl>
      <div class="a-danger-zone">
        <form method="post"><?= csrf_field() ?><input type="hidden" name="action" value="unread"><button class="a-btn a-btn-sm" type="submit"><?= icon('inbox') ?> Mark unread</button></form>
        <form method="post" data-confirm="Delete this request permanently? This can't be undone."><?= csrf_field() ?><input type="hidden" name="action" value="delete"><button class="a-btn a-btn-sm a-btn-danger" type="submit"><?= icon('trash') ?> Delete</button></form>
      </div>
    </section>
  </aside>
</div>

<?php admin_foot(); ?>
