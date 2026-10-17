<?php
require dirname(__DIR__) . '/app/bootstrap.php';

require_admin();

/** Stops spreadsheet apps from treating a cell as a formula. */
function csv_safe(string $v): string
{
    return preg_match('/^[=+\-@\t\r]/', $v) ? "'" . $v : $v;
}

$status = (string) ($_GET['status'] ?? '');
$rows = isset(QUOTE_STATUSES[$status])
    ? db_all('SELECT * FROM quotes WHERE status = ? ORDER BY created_at DESC', [$status])
    : db_all('SELECT * FROM quotes ORDER BY created_at DESC');

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="ezway-requests-' . date('Y-m-d') . '.csv"');
header('Cache-Control: no-store');

$out = fopen('php://output', 'w');
fwrite($out, "\xEF\xBB\xBF"); // UTF-8 BOM so Excel shows accents correctly
fputcsv($out, [
    'Reference', 'Received', 'Status', 'Service', 'Language', 'Company', 'First name', 'Last name', 'Date of birth', 'Email', 'Phone', 'Prefers',
    'Street', 'Unit', 'City', 'State', 'ZIP', 'Drivers', 'Vehicles', 'Liability', 'PIP', 'Uninsured motorist',
    'Start date', 'Currently insured', 'Current carrier', 'Customer notes', 'Request details', 'Internal notes',
]);

foreach ($rows as $r) {
    $q = json_decode($r['data'], true) ?: [];
    $q['service'] ??= $r['service'];
    $q['details'] ??= [];
    $details = implode(' | ', array_map(fn ($k, $v) => "$k: $v", array_keys(details_rows($q)), details_rows($q)));
    $a = $q['applicant'] ?? [];
    $ad = $q['address'] ?? [];
    $c = $q['coverage'] ?? [] ?: [];
    // License numbers are masked in exports; view them in the dashboard.
    $drivers = implode(' | ', array_map(fn ($d) => sprintf(
        '%s %s (%s, DOB %s, %s)',
        $d['first'], $d['last'], relationships()[$d['relationship']] ?? '', $d['dob'], driver_license_label($d)
    ), $q['drivers'] ?? []));
    $vehicles = implode(' | ', array_map(fn ($v) => sprintf(
        '%s, VIN %s [%s]', vehicle_title($v), $v['vin'], implode('; ', vehicle_coverage_list($v))
    ), $q['vehicles'] ?? []));

    fputcsv($out, array_map(fn ($v) => csv_safe((string) $v), [
        $r['ref'], fmt_date($r['created_at'], 'Y-m-d H:i'), QUOTE_STATUSES[$r['status']] ?? $r['status'],
        service_title($q['service']), ($r['lang'] ?? 'en') === 'es' ? 'Spanish' : 'English', $a['company'] ?? '',
        $a['first'] ?? '', $a['last'] ?? '', $a['dob'] ?? '', $a['email'] ?? '', $a['phone'] ?? '', contact_prefs()[$a['contact_pref'] ?? ''] ?? '',
        $ad['street'] ?? '', $ad['unit'] ?? '', $ad['city'] ?? '', $ad['state'] ?? '', $ad['zip'] ?? '',
        $drivers, $vehicles, $c ? (liability_levels($q['service'])[$c['liability'] ?? ''][0] ?? '') : '', $c ? (!empty($c['pip']) ? 'Yes' : 'No') : '', $c ? (!empty($c['um']) ? 'Yes' : 'No') : '',
        $c['start_date'] ?? '', $c['currently_insured'] ?? '', $c['current_carrier'] ?? '', $c['comments'] ?? '', $details, $r['notes'] ?? '',
    ]));
}
fclose($out);
