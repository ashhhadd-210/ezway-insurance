<?php
defined('APP') or exit;

/* ------------------------------------------------------------------ */
/* Options shared by the quote form, validation, emails and dashboard */
/* ------------------------------------------------------------------ */

const US_STATES = [
    'AL' => 'Alabama', 'AK' => 'Alaska', 'AZ' => 'Arizona', 'AR' => 'Arkansas', 'CA' => 'California',
    'CO' => 'Colorado', 'CT' => 'Connecticut', 'DE' => 'Delaware', 'DC' => 'District of Columbia',
    'FL' => 'Florida', 'GA' => 'Georgia', 'HI' => 'Hawaii', 'ID' => 'Idaho', 'IL' => 'Illinois',
    'IN' => 'Indiana', 'IA' => 'Iowa', 'KS' => 'Kansas', 'KY' => 'Kentucky', 'LA' => 'Louisiana',
    'ME' => 'Maine', 'MD' => 'Maryland', 'MA' => 'Massachusetts', 'MI' => 'Michigan', 'MN' => 'Minnesota',
    'MS' => 'Mississippi', 'MO' => 'Missouri', 'MT' => 'Montana', 'NE' => 'Nebraska', 'NV' => 'Nevada',
    'NH' => 'New Hampshire', 'NJ' => 'New Jersey', 'NM' => 'New Mexico', 'NY' => 'New York',
    'NC' => 'North Carolina', 'ND' => 'North Dakota', 'OH' => 'Ohio', 'OK' => 'Oklahoma', 'OR' => 'Oregon',
    'PA' => 'Pennsylvania', 'RI' => 'Rhode Island', 'SC' => 'South Carolina', 'SD' => 'South Dakota',
    'TN' => 'Tennessee', 'TX' => 'Texas', 'UT' => 'Utah', 'VT' => 'Vermont', 'VA' => 'Virginia',
    'WA' => 'Washington', 'WV' => 'West Virginia', 'WI' => 'Wisconsin', 'WY' => 'Wyoming',
];

const RELATIONSHIPS = [
    'self'     => 'Policyholder (you)',
    'spouse'   => 'Spouse / partner',
    'child'    => 'Child',
    'parent'   => 'Parent',
    'relative' => 'Other relative',
    'other'    => 'Other / roommate',
];

const ID_TYPES = [
    'us'      => 'US driver license',
    'foreign' => 'Foreign license',
];

const LIABILITY_LEVELS = [
    'state_min' => ['State minimum', 'The lowest limits your state requires by law.', 'Lowest price'],
    'standard'  => ['Standard · 50/100/50', '$50k per person, $100k per accident, $50k property damage.', ''],
    'better'    => ['Better · 100/300/100', '$100k per person, $300k per accident, $100k property damage.', 'Most popular'],
    'best'      => ['Best · 250/500/250', '$250k per person, $500k per accident, $250k property damage.', 'Most protection'],
    'unsure'    => ['Not sure yet', 'Your agent will recommend limits that fit you.', ''],
];

const DEDUCTIBLES = [
    '250'    => '$250',
    '500'    => '$500',
    '1000'   => '$1,000',
    'unsure' => 'Not sure',
];

const CONTACT_PREFS = ['phone' => 'Phone call', 'text' => 'Text message', 'email' => 'Email'];

const QUOTE_STATUSES = [
    'new'       => 'New',
    'contacted' => 'Contacted',
    'quoted'    => 'Quote sent',
    'won'       => 'Won',
    'lost'      => 'Closed',
];

const MAX_DRIVERS = 8;
const MAX_VEHICLES = 6;

/* ------------------------------------------------------------------ */
/* Validation                                                         */
/* ------------------------------------------------------------------ */

/** Accepts MM/DD/YYYY or YYYY-MM-DD. Returns YYYY-MM-DD or null. */
function parse_date_in(string $s): ?string
{
    if (preg_match('#^(\d{1,2})/(\d{1,2})/(\d{4})$#', $s, $m)) {
        [$mo, $d, $y] = [(int) $m[1], (int) $m[2], (int) $m[3]];
    } elseif (preg_match('#^(\d{4})-(\d{2})-(\d{2})$#', $s, $m)) {
        [$y, $mo, $d] = [(int) $m[1], (int) $m[2], (int) $m[3]];
    } else {
        return null;
    }
    return checkdate($mo, $d, $y) ? sprintf('%04d-%02d-%02d', $y, $mo, $d) : null;
}

function age_on(string $isoDate): int
{
    return (new DateTimeImmutable($isoDate))->diff(new DateTimeImmutable('today'))->y;
}

function name_ok(string $s): bool
{
    return $s !== '' && (bool) preg_match("/^[\p{L}][\p{L}\p{M} .'\-]{0,59}$/u", $s);
}

/**
 * Validate a submitted quote. Mirrors the browser-side checks, so anything
 * a visitor sees as an error here is a bug in the form or a tampered request.
 *
 * @return array{0: array, 1: array<string,string>} [clean data, errors keyed by field path]
 */
function validate_quote(array $in): array
{
    $err = [];
    $a = is_array($in['applicant'] ?? null) ? $in['applicant'] : [];
    $addr = is_array($in['address'] ?? null) ? $in['address'] : [];
    $cov = is_array($in['coverage'] ?? null) ? $in['coverage'] : [];

    // You
    $applicant = [
        'first' => str_in($a['first'] ?? '', 60),
        'last'  => str_in($a['last'] ?? '', 60),
        'dob'   => parse_date_in(str_in($a['dob'] ?? '', 10)),
        'email' => strtolower(str_in($a['email'] ?? '', 160)),
        'phone' => format_phone(str_in($a['phone'] ?? '', 30)),
        'contact_pref' => str_in($a['contact_pref'] ?? 'phone', 10),
    ];
    if (!name_ok($applicant['first'])) $err['applicant.first'] = 'Enter your first name.';
    if (!name_ok($applicant['last'])) $err['applicant.last'] = 'Enter your last name.';
    if (!$applicant['dob'] || age_on($applicant['dob']) < 16 || age_on($applicant['dob']) > 110) $err['applicant.dob'] = 'Enter a valid date of birth.';
    if (!filter_var($applicant['email'], FILTER_VALIDATE_EMAIL)) $err['applicant.email'] = 'Enter a valid email address.';
    if (strlen(digits($applicant['phone'])) !== 10) $err['applicant.phone'] = 'Enter a 10-digit phone number.';
    if (!isset(CONTACT_PREFS[$applicant['contact_pref']])) $applicant['contact_pref'] = 'phone';

    // Address
    $address = [
        'street' => str_in($addr['street'] ?? '', 120),
        'unit'   => str_in($addr['unit'] ?? '', 30),
        'city'   => str_in($addr['city'] ?? '', 80),
        'state'  => strtoupper(str_in($addr['state'] ?? '', 2)),
        'zip'    => str_in($addr['zip'] ?? '', 10),
    ];
    if (mb_strlen($address['street']) < 3) $err['address.street'] = 'Enter your street address.';
    if ($address['city'] === '') $err['address.city'] = 'Enter your city.';
    if (!isset(US_STATES[$address['state']])) $err['address.state'] = 'Choose your state.';
    if (!preg_match('/^\d{5}$/', $address['zip'])) $err['address.zip'] = 'Enter a 5-digit ZIP code.';

    // Drivers
    $drivers = [];
    $rawDrivers = array_values(is_array($in['drivers'] ?? null) ? $in['drivers'] : []);
    if (count($rawDrivers) < 1) $err['drivers'] = 'Add at least one driver.';
    foreach (array_slice($rawDrivers, 0, MAX_DRIVERS) as $i => $d) {
        $d = is_array($d) ? $d : [];
        $p = "drivers.$i.";
        $row = [
            'first'           => str_in($d['first'] ?? '', 60),
            'last'            => str_in($d['last'] ?? '', 60),
            'dob'             => parse_date_in(str_in($d['dob'] ?? '', 10)),
            'relationship'    => str_in($d['relationship'] ?? '', 20),
            'id_type'         => str_in($d['id_type'] ?? '', 10),
            'license_state'   => strtoupper(str_in($d['license_state'] ?? '', 2)),
            'license_country' => str_in($d['license_country'] ?? '', 60),
            'license_number'  => strtoupper(preg_replace('/\s+/', '', str_in($d['license_number'] ?? '', 30)) ?? ''),
        ];
        if ($i === 0) $row['relationship'] = 'self';
        if (!name_ok($row['first'])) $err[$p . 'first'] = 'Enter a first name.';
        if (!name_ok($row['last'])) $err[$p . 'last'] = 'Enter a last name.';
        if (!$row['dob'] || age_on($row['dob']) < 14 || age_on($row['dob']) > 110) $err[$p . 'dob'] = 'Enter a valid date of birth.';
        if (!isset(RELATIONSHIPS[$row['relationship']])) $err[$p . 'relationship'] = 'Choose a relationship.';
        if (!isset(ID_TYPES[$row['id_type']])) $err[$p . 'id_type'] = 'Choose an ID type.';
        if ($row['id_type'] === 'us') {
            $row['license_country'] = '';
            if (!isset(US_STATES[$row['license_state']])) $err[$p . 'license_state'] = 'Choose the issuing state.';
        } elseif ($row['id_type'] === 'foreign') {
            $row['license_state'] = '';
            if ($row['license_country'] === '') $err[$p . 'license_country'] = 'Enter the issuing country.';
        }
        if (!preg_match('/^[A-Z0-9\-*]{4,25}$/', $row['license_number'])) $err[$p . 'license_number'] = 'Enter the license number (letters and numbers).';
        $drivers[] = $row;
    }

    // Vehicles
    $vehicles = [];
    $rawVehicles = array_values(is_array($in['vehicles'] ?? null) ? $in['vehicles'] : []);
    if (count($rawVehicles) < 1) $err['vehicles'] = 'Add at least one vehicle.';
    $seen = [];
    foreach (array_slice($rawVehicles, 0, MAX_VEHICLES) as $i => $v) {
        $v = is_array($v) ? $v : [];
        $p = "vehicles.$i.";
        $vin = strtoupper(preg_replace('/[\s\-]+/', '', str_in($v['vin'] ?? '', 30)) ?? '');
        $row = [
            'vin'           => $vin,
            'year'          => preg_match('/^\d{4}$/', str_in($v['year'] ?? '', 4)) ? str_in($v['year'], 4) : '',
            'make'          => str_in($v['make'] ?? '', 40),
            'model'         => str_in($v['model'] ?? '', 60),
            'trim'          => str_in($v['trim'] ?? '', 60),
            'body'          => str_in($v['body'] ?? '', 60),
            'decoded'       => false,
            'full_coverage' => bool_in($v['full_coverage'] ?? false),
            'deductible'    => str_in($v['deductible'] ?? '', 10),
            'roadside'      => bool_in($v['roadside'] ?? false),
            'rental'        => bool_in($v['rental'] ?? false),
        ];
        if (!vin_format_ok($vin)) {
            $err[$p . 'vin'] = 'Enter the 17-character VIN (no I, O or Q).';
        } elseif (isset($seen[$vin])) {
            $err[$p . 'vin'] = 'This VIN is already listed.';
        } else {
            $seen[$vin] = true;
            // Prefer our own cached decode over whatever the browser sent.
            $cached = db_one('SELECT data FROM vin_cache WHERE vin = ?', [$vin]);
            if ($cached && ($dec = json_decode($cached['data'], true)) && !empty($dec['ok'])) {
                foreach (['year', 'make', 'model', 'trim', 'body'] as $k) {
                    $row[$k] = (string) $dec[$k];
                }
                $row['decoded'] = true;
            }
        }
        if (!$row['full_coverage']) {
            $row['deductible'] = '';
        } elseif (!isset(DEDUCTIBLES[$row['deductible']])) {
            $row['deductible'] = 'unsure';
        }
        $vehicles[] = $row;
    }

    // Coverage & details
    $start = parse_date_in(str_in($cov['start_date'] ?? '', 10));
    $coverage = [
        'liability'         => str_in($cov['liability'] ?? '', 12),
        'pip'               => bool_in($cov['pip'] ?? false),
        'um'                => bool_in($cov['um'] ?? false),
        'start_date'        => $start ?? '',
        'currently_insured' => in_array($cov['currently_insured'] ?? '', ['yes', 'no'], true) ? $cov['currently_insured'] : '',
        'current_carrier'   => str_in($cov['current_carrier'] ?? '', 60),
        'comments'          => str_in($cov['comments'] ?? '', 1000),
    ];
    if (!isset(LIABILITY_LEVELS[$coverage['liability']])) $err['coverage.liability'] = 'Choose a liability level.';
    if ($start) {
        $days = (int) (new DateTimeImmutable('today'))->diff(new DateTimeImmutable($start))->format('%r%a');
        if ($days < 0 || $days > 365) $err['coverage.start_date'] = 'Choose a start date within the next 12 months.';
    }
    if ($coverage['currently_insured'] !== 'yes') $coverage['current_carrier'] = '';

    if (!bool_in($in['consent'] ?? false)) $err['consent'] = 'Please agree so we can contact you about your quote.';

    return [compact('applicant', 'address', 'drivers', 'vehicles', 'coverage'), $err];
}

/* ------------------------------------------------------------------ */
/* Saving                                                             */
/* ------------------------------------------------------------------ */

function new_ref(string $prefix = 'Q'): string
{
    $alphabet = 'ABCDEFGHJKMNPQRSTUVWXYZ23456789';
    $s = '';
    for ($i = 0; $i < 6; $i++) {
        $s .= $alphabet[random_int(0, strlen($alphabet) - 1)];
    }
    return $prefix . '-' . $s;
}

/** Saves a validated quote. License numbers are encrypted at rest. */
function save_quote(array $q): array
{
    $stored = $q;
    foreach ($stored['drivers'] as &$d) {
        $d['license_last4'] = substr($d['license_number'], -4);
        $d['license_enc'] = encrypt_str($d['license_number']);
        unset($d['license_number']);
    }
    unset($d);

    $a = $q['applicant'];
    $search = mb_strtolower(implode(' ', array_merge(
        [$a['first'], $a['last'], $a['email'], digits($a['phone']), $q['address']['city'], $q['address']['zip']],
        array_map(fn ($d) => $d['first'] . ' ' . $d['last'], $q['drivers']),
        array_map(fn ($v) => $v['vin'] . ' ' . vehicle_title($v), $q['vehicles'])
    )));

    $now = now_utc();
    for ($attempt = 0; $attempt < 5; $attempt++) {
        $ref = new_ref();
        try {
            db_exec(
                'INSERT INTO quotes (ref, status, is_read, first_name, last_name, email, phone, city, state, zip,
                    drivers_count, vehicles_count, search, data, ip_hash, user_agent, created_at, updated_at)
                 VALUES (?, \'new\', 0, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
                [
                    $ref, $a['first'], $a['last'], $a['email'], $a['phone'],
                    $q['address']['city'], $q['address']['state'], $q['address']['zip'],
                    count($q['drivers']), count($q['vehicles']), $search,
                    json_encode($stored, JSON_UNESCAPED_UNICODE), ip_hash(),
                    mb_substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255), $now, $now,
                ]
            );
            return ['id' => (int) db()->lastInsertId(), 'ref' => $ref];
        } catch (PDOException $ex) {
            if ($attempt === 4) {
                throw $ex;
            }
        }
    }
    throw new RuntimeException('Could not save quote.');
}

/* ------------------------------------------------------------------ */
/* Display helpers                                                    */
/* ------------------------------------------------------------------ */

function fmt_dob(?string $iso): string
{
    return $iso ? date('m/d/Y', strtotime($iso)) . ' (age ' . age_on($iso) . ')' : '';
}

function driver_license_label(array $d, bool $masked = true): string
{
    $where = $d['id_type'] === 'us'
        ? (US_STATES[$d['license_state']] ?? $d['license_state'])
        : $d['license_country'];
    $num = $masked ? '••••' . ($d['license_last4'] ?? '') : ($d['license_number'] ?? '');
    return ID_TYPES[$d['id_type']] . ' · ' . $where . ' · ' . $num;
}

function vehicle_coverage_list(array $v): array
{
    $out = [];
    if ($v['full_coverage']) {
        $out[] = 'Full coverage (comp + collision)' . ($v['deductible'] && $v['deductible'] !== 'unsure' ? ', ' . DEDUCTIBLES[$v['deductible']] . ' deductible' : '');
    } else {
        $out[] = 'Liability only';
    }
    if ($v['roadside']) $out[] = 'Roadside assistance';
    if ($v['rental']) $out[] = 'Rental reimbursement';
    return $out;
}

function policy_coverage_list(array $c): array
{
    $out = ['Liability: ' . (LIABILITY_LEVELS[$c['liability']][0] ?? '—')];
    if ($c['pip']) $out[] = 'Personal injury protection (PIP)';
    if ($c['um']) $out[] = 'Uninsured / underinsured motorist';
    return $out;
}

/* ------------------------------------------------------------------ */
/* Emails                                                             */
/* ------------------------------------------------------------------ */

function email_new_quote(array $q, array $saved): void
{
    $a = $q['applicant'];
    $addr = $q['address'];
    $name = $a['first'] . ' ' . $a['last'];
    $nd = count($q['drivers']);
    $nv = count($q['vehicles']);

    $html = '<p style="margin:0 0 6px;color:#475174">Reference <b>' . e($saved['ref']) . '</b> · ' . e(fmt_date(now_utc())) . '</p>';
    $text = "New quote request {$saved['ref']}\n\n";

    $contact = [
        'Name'      => $name,
        'Phone'     => $a['phone'],
        'Email'     => $a['email'],
        'Prefers'   => CONTACT_PREFS[$a['contact_pref']],
        'Address'   => trim($addr['street'] . ' ' . $addr['unit']) . "\n" . $addr['city'] . ', ' . $addr['state'] . ' ' . $addr['zip'],
    ];
    $html .= email_heading('Contact') . email_rows($contact);
    foreach ($contact as $k => $v) $text .= "$k: $v\n";

    $html .= email_heading("Drivers ($nd)");
    $text .= "\nDRIVERS ($nd)\n";
    foreach ($q['drivers'] as $i => $d) {
        $rows = [
            'Driver ' . ($i + 1) => $d['first'] . ' ' . $d['last'] . ' · ' . RELATIONSHIPS[$d['relationship']],
            'Date of birth'      => fmt_dob($d['dob']),
            // Full license numbers are never emailed, only shown in the dashboard.
            'License'            => driver_license_label(['license_last4' => substr($d['license_number'], -4)] + $d),
        ];
        $html .= email_rows($rows);
        foreach ($rows as $k => $v) $text .= "$k: $v\n";
    }

    $html .= email_heading("Vehicles ($nv)");
    $text .= "\nVEHICLES ($nv)\n";
    foreach ($q['vehicles'] as $i => $v) {
        $rows = [
            'Vehicle ' . ($i + 1) => vehicle_title($v) . ($v['trim'] ? ' ' . $v['trim'] : ''),
            'VIN'                 => $v['vin'],
            'Coverage'            => implode("\n", vehicle_coverage_list($v)),
        ];
        $html .= email_rows($rows);
        foreach ($rows as $k => $v2) $text .= "$k: " . str_replace("\n", ', ', $v2) . "\n";
    }

    $c = $q['coverage'];
    $policy = ['Coverage' => implode("\n", policy_coverage_list($c))];
    if ($c['start_date']) $policy['Start date'] = date('M j, Y', strtotime($c['start_date']));
    if ($c['currently_insured']) $policy['Currently insured'] = ucfirst($c['currently_insured']) . ($c['current_carrier'] ? ' · ' . $c['current_carrier'] : '');
    if ($c['comments']) $policy['Notes'] = $c['comments'];
    $html .= email_heading('Policy') . email_rows($policy);
    $text .= "\nPOLICY\n";
    foreach ($policy as $k => $v) $text .= "$k: " . str_replace("\n", ', ', $v) . "\n";

    $link = site_url('admin/quote.php?id=' . $saved['id']);
    $html .= '<p style="color:#6B7493;font-size:13px;margin:8px 0 0">For security, full license numbers are only shown in your dashboard.</p>';
    $text .= "\nOpen in dashboard: $link\n";

    send_mail(
        (string) cfg('mail.notify_to'),
        "New quote: $name · $nd driver" . ($nd > 1 ? 's' : '') . ", $nv vehicle" . ($nv > 1 ? 's' : '') . " ({$saved['ref']})",
        email_layout('New quote request from ' . $name, $html, ['Open in dashboard', $link]),
        $text,
        $a['email']
    );
}

function email_quote_confirmation(array $q, array $saved): void
{
    if (!cfg('mail.confirm_customer')) {
        return;
    }
    $a = $q['applicant'];
    $phone = (string) cfg('site.phone');
    $vehicles = implode("\n", array_map('vehicle_title', $q['vehicles']));
    $html = '<p style="margin:0 0 14px;line-height:1.6">Hi ' . e($a['first']) . ', thanks for requesting a quote. A licensed agent is reviewing your details and will reach out by ' . e(strtolower(CONTACT_PREFS[$a['contact_pref']])) . ', usually within one business day.</p>'
        . email_rows([
            'Reference' => $saved['ref'],
            'Drivers'   => (string) count($q['drivers']),
            'Vehicles'  => $vehicles,
        ])
        . '<p style="margin:0;line-height:1.6;color:#475174">Need to change something or in a hurry? Call us at <b>' . e($phone) . '</b> and mention your reference number.</p>';
    $text = "Hi {$a['first']},\n\nThanks for requesting a quote. A licensed agent will reach out, usually within one business day.\n\nReference: {$saved['ref']}\nVehicles: " . str_replace("\n", ', ', $vehicles) . "\n\nQuestions? Call $phone.\n\n" . cfg('site.name');
    send_mail($a['email'], 'We received your quote request (' . $saved['ref'] . ')', email_layout('We got your quote request', $html), $text, (string) cfg('site.email'));
}

/* ------------------------------------------------------------------ */
/* Marketing copy shared by the Home and Coverage pages               */
/* ------------------------------------------------------------------ */

function coverage_info(): array
{
    return [
        'liability' => [
            'icon'  => 'scale',
            'title' => 'Liability',
            'short' => 'Required in almost every state. Pays for injuries and damage you cause to other people.',
            'long'  => 'If you cause an accident, liability coverage pays for the other driver\'s medical bills (bodily injury) and repairs to their car or property (property damage), up to your limits. It also helps cover legal costs if you are sued.',
            'good'  => ['Every driver: it is required by law in almost every state', 'Anyone with savings or a home to protect: higher limits are worth it'],
            'tag'   => 'Required',
        ],
        'full' => [
            'icon'  => 'shield-plus',
            'title' => 'Full coverage',
            'short' => 'Comprehensive and collision together, so your own car is protected too.',
            'long'  => 'Collision pays to repair or replace your car after an accident, whoever is at fault. Comprehensive covers almost everything else: theft, vandalism, hail, flood, fire, falling objects and hitting an animal. Together with liability, this is what most people call "full coverage".',
            'good'  => ['Financed or leased cars: your lender usually requires it', 'Newer or valuable vehicles you couldn\'t easily replace'],
            'tag'   => 'Per vehicle',
        ],
        'pip' => [
            'icon'  => 'heart',
            'title' => 'Personal injury protection',
            'short' => 'Medical bills and lost wages for you and your passengers, no matter who caused the accident.',
            'long'  => 'PIP pays medical expenses, a portion of lost income, and other costs like childcare for you and your passengers after an accident, regardless of fault. It is required in some states and optional in others.',
            'good'  => ['Drivers who regularly carry passengers or family', 'Anyone with a high health-insurance deductible'],
            'tag'   => 'Whole policy',
        ],
        'um' => [
            'icon'  => 'umbrella',
            'title' => 'Uninsured motorist',
            'short' => 'Protects you when the other driver has no insurance, or not enough to cover your costs.',
            'long'  => 'Roughly one in seven US drivers is uninsured. Uninsured/underinsured motorist coverage pays for your injuries, and in many states your car repairs, when the at-fault driver can\'t pay. It also helps in many hit-and-run accidents.',
            'good'  => ['Everyone: it is inexpensive for the protection it gives', 'Drivers in areas with many uninsured motorists'],
            'tag'   => 'Whole policy',
        ],
        'roadside' => [
            'icon'  => 'wrench',
            'title' => 'Roadside assistance',
            'short' => 'Towing, jump-starts, flat tires, lockouts and fuel delivery, 24/7.',
            'long'  => 'If your car breaks down, roadside assistance sends help: towing to a nearby shop, battery jump-starts, flat-tire changes, lockout service and fuel delivery. It usually costs just a few dollars a month per vehicle.',
            'good'  => ['Commuters and long-distance drivers', 'Older vehicles and new drivers'],
            'tag'   => 'Per vehicle',
        ],
        'rental' => [
            'icon'  => 'key',
            'title' => 'Rental reimbursement',
            'short' => 'Pays for a rental car while yours is in the shop after a covered claim.',
            'long'  => 'After a covered accident or loss, rental reimbursement pays for a rental car or rideshare up to a daily limit while your car is being repaired, so you\'re never stuck without a ride.',
            'good'  => ['Households with only one car', 'Anyone who needs a car for work every day'],
            'tag'   => 'Per vehicle',
        ],
    ];
}
