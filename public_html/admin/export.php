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
header('Content-Disposition: attachment; filename="quote-requests-' . date('Y-m-d') . '.csv"');
header('Cache-Control: no-store');

$out = fopen('php://output', 'w');
fwrite($out, "\xEF\xBB\xBF"); // UTF-8 BOM so Excel shows accents correctly
fputcsv($out, [
    'Reference', 'Received', 'Status', 'First name', 'Last name', 'Date of birth', 'Email', 'Phone', 'Prefers',
    'Street', 'Unit', 'City', 'State', 'ZIP', 'Drivers', 'Vehicles', 'Liability', 'PIP', 'Uninsured motorist',
    'Start date', 'Currently insured', 'Current carrier', 'Customer notes', 'Internal notes',
]);

foreach ($rows as $r) {
    $q = json_decode($r['data'], true) ?: [];
    $a = $q['applicant'] ?? [];
    $ad = $q['address'] ?? [];
    $c = $q['coverage'] ?? [];
    // License numbers are masked in exports; view them in the dashboard.
    $drivers = implode(' | ', array_map(fn ($d) => sprintf(
        '%s %s (%s, DOB %s, %s)',
        $d['first'], $d['last'], RELATIONSHIPS[$d['relationship']] ?? '', $d['dob'], driver_license_label($d)
    ), $q['drivers'] ?? []));
    $vehicles = implode(' | ', array_map(fn ($v) => sprintf(
        '%s, VIN %s [%s]', vehicle_title($v), $v['vin'], implode('; ', vehicle_coverage_list($v))
    ), $q['vehicles'] ?? []));

    fputcsv($out, array_map(fn ($v) => csv_safe((string) $v), [
        $r['ref'], fmt_date($r['created_at'], 'Y-m-d H:i'), QUOTE_STATUSES[$r['status']] ?? $r['status'],
        $a['first'] ?? '', $a['last'] ?? '', $a['dob'] ?? '', $a['email'] ?? '', $a['phone'] ?? '', CONTACT_PREFS[$a['contact_pref'] ?? ''] ?? '',
        $ad['street'] ?? '', $ad['unit'] ?? '', $ad['city'] ?? '', $ad['state'] ?? '', $ad['zip'] ?? '',
        $drivers, $vehicles, LIABILITY_LEVELS[$c['liability'] ?? ''][0] ?? '', !empty($c['pip']) ? 'Yes' : 'No', !empty($c['um']) ? 'Yes' : 'No',
        $c['start_date'] ?? '', $c['currently_insured'] ?? '', $c['current_carrier'] ?? '', $c['comments'] ?? '', $r['notes'] ?? '',
    ]));
}
fclose($out);
