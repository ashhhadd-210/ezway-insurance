<?php
defined('APP') or exit;

/* ------------------------------------------------------------------ */
/* Options shared by the quote form, validation, emails and dashboard */
/* (labels follow the current language: lang())                      */
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

/** Dashboard statuses (the dashboard is in English). */
const QUOTE_STATUSES = [
    'new'       => 'New',
    'contacted' => 'Contacted',
    'quoted'    => 'Quote sent',
    'won'       => 'Won',
    'lost'      => 'Closed',
];

const MAX_DRIVERS = 8;
const MAX_VEHICLES = 6;

function relationships(): array
{
    return [
        'self'     => L('Policyholder (you)', 'Titular (tú)'),
        'spouse'   => L('Spouse / partner', 'Esposo(a) / pareja'),
        'child'    => L('Child', 'Hijo(a)'),
        'parent'   => L('Parent', 'Padre / madre'),
        'relative' => L('Other relative', 'Otro familiar'),
        'employee' => L('Employee', 'Empleado(a)'),
        'other'    => L('Other / roommate', 'Otro / compañero de casa'),
    ];
}

/** "No license? No problem": any type of ID is accepted. */
function id_types(): array
{
    return [
        'us'    => L('US driver license', 'Licencia de EE. UU.'),
        'other' => L('Other ID / no license', 'Otra ID / sin licencia'),
    ];
}

/** Label for an ID type, including 'foreign' from requests sent before it became a kind of "Other". */
function id_type_label(string $type): string
{
    return id_types()[$type] ?? ($type === 'foreign' ? L('Foreign license', 'Licencia extranjera') : $type);
}

function id_kinds(): array
{
    return [
        'foreign_license' => L('Foreign license', 'Licencia extranjera'),
        'state_id'        => L('State ID card', 'ID del estado'),
        'passport'        => L('Passport', 'Pasaporte'),
        'matricula'       => L('Matrícula consular', 'Matrícula consular'),
        'other'           => L('Other ID', 'Otra identificación'),
    ];
}

/** Liability choices. Commercial policies get higher limits. [label, description, badge] */
function liability_levels(string $service = 'personal_auto'): array
{
    if ($service === 'commercial_auto') {
        return [
            'c_min'   => [L('Texas minimum · 30/60/25', 'Mínimo de Texas · 30/60/25'), L('The lowest limits Texas allows. Some contracts require more.', 'Los límites más bajos que permite Texas. Algunos contratos piden más.'), L('Cheapest', 'Más barato')],
            'c_100'   => [L('100/300/100', '100/300/100'), L('$100k per person, $300k per accident, $100k property damage.', '$100k por persona, $300k por accidente, $100k en daños a propiedad.'), ''],
            'csl_500' => [L('$500,000 combined', '$500,000 combinado'), L('One combined limit for injuries and damage per accident.', 'Un solo límite combinado para lesiones y daños por accidente.'), ''],
            'csl_1m'  => [L('$1,000,000 combined', '$1,000,000 combinado'), L('The limit most contracts and brokers ask for.', 'El límite que piden la mayoría de los contratos.'), L('Most requested', 'El más pedido')],
            'unsure'  => [L('Not sure yet', 'Todavía no sé'), L('Tell us about your work and we\'ll recommend limits.', 'Cuéntanos de tu trabajo y te recomendamos límites.'), ''],
        ];
    }
    return [
        'state_min' => [L('Texas minimum · 30/60/25', 'Mínimo de Texas · 30/60/25'), L('The lowest limits Texas requires by law.', 'Los límites más bajos que exige la ley de Texas.'), L('Cheapest price possible', 'El precio más barato posible')],
        'standard'  => [L('Standard · 50/100/50', 'Estándar · 50/100/50'), L('$50k per person, $100k per accident, $50k property damage.', '$50k por persona, $100k por accidente, $50k en daños a propiedad.'), ''],
        'better'    => [L('Better · 100/300/100', 'Mejor · 100/300/100'), L('$100k per person, $300k per accident, $100k property damage.', '$100k por persona, $300k por accidente, $100k en daños a propiedad.'), L('Most popular', 'El más popular')],
        'best'      => [L('Best · 250/500/250', 'Máxima · 250/500/250'), L('$250k per person, $500k per accident, $250k property damage.', '$250k por persona, $500k por accidente, $250k en daños a propiedad.'), L('Most protection', 'Más protección')],
        'unsure'    => [L('Not sure yet', 'Todavía no sé'), L('Your agent will recommend limits that fit you.', 'Tu agente te recomendará los límites ideales.'), ''],
    ];
}

function deductibles(): array
{
    return ['250' => '$250', '500' => '$500', '1000' => '$1,000', 'unsure' => L('Not sure', 'No sé')];
}

function contact_prefs(): array
{
    return ['phone' => L('Phone call', 'Llamada'), 'text' => L('Text message', 'Mensaje de texto'), 'email' => L('Email', 'Correo')];
}

/** Runs $fn with the given language active, then restores the previous one. */
function with_lang(string $lang, callable $fn): mixed
{
    $prev = lang();
    set_lang($lang);
    try {
        return $fn();
    } finally {
        set_lang($prev);
    }
}

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
 * Validate a submitted request. Mirrors the browser-side checks, so anything
 * a visitor sees as an error here is a bug in the form or a tampered request.
 *
 * @return array{0: array, 1: array<string,string>} [clean data, errors keyed by field path]
 */
function validate_quote(array $in): array
{
    $err = [];
    $serviceKey = str_in($in['service'] ?? '', 30);
    $svc = service($serviceKey);
    if (!$svc) {
        $err['service'] = L('Choose what you need a quote for.', 'Elige qué necesitas cotizar.');
        $serviceKey = 'personal_auto';
        $svc = service($serviceKey);
    }
    $isAuto = $svc['flow'] === 'auto';
    $a = is_array($in['applicant'] ?? null) ? $in['applicant'] : [];
    $addr = is_array($in['address'] ?? null) ? $in['address'] : [];

    // You
    $applicant = [
        'first' => str_in($a['first'] ?? '', 60),
        'last'  => str_in($a['last'] ?? '', 60),
        'dob'   => parse_date_in(str_in($a['dob'] ?? '', 10)),
        'email' => strtolower(str_in($a['email'] ?? '', 160)),
        'phone' => format_phone(str_in($a['phone'] ?? '', 30)),
        'contact_pref'  => str_in($a['contact_pref'] ?? 'phone', 10),
        'company'       => str_in($a['company'] ?? '', 120),
        'business_type' => str_in($a['business_type'] ?? '', 120),
    ];
    if (!name_ok($applicant['first'])) $err['applicant.first'] = L('Enter your first name.', 'Escribe tu nombre.');
    if (!name_ok($applicant['last'])) $err['applicant.last'] = L('Enter your last name.', 'Escribe tu apellido.');
    if (!$applicant['dob'] || age_on($applicant['dob']) < 16 || age_on($applicant['dob']) > 110) $err['applicant.dob'] = L('Enter a valid date of birth.', 'Escribe una fecha de nacimiento válida.');
    if (!filter_var($applicant['email'], FILTER_VALIDATE_EMAIL)) $err['applicant.email'] = L('Enter a valid email address.', 'Escribe un correo válido.');
    if (strlen(digits($applicant['phone'])) !== 10) $err['applicant.phone'] = L('Enter a 10-digit phone number.', 'Escribe un teléfono de 10 dígitos.');
    if (!isset(contact_prefs()[$applicant['contact_pref']])) $applicant['contact_pref'] = 'phone';
    if ($serviceKey === 'business') {
        if (mb_strlen($applicant['business_type']) < 2) $err['applicant.business_type'] = L('Enter your type of business or trade.', 'Escribe tu tipo de negocio u oficio.');
    } elseif ($serviceKey !== 'commercial_auto') {
        $applicant['company'] = $applicant['business_type'] = '';
    }

    // Address (street is only required where it affects the price)
    $address = [
        'street' => str_in($addr['street'] ?? '', 120),
        'unit'   => str_in($addr['unit'] ?? '', 30),
        'city'   => str_in($addr['city'] ?? '', 80),
        'state'  => strtoupper(str_in($addr['state'] ?? '', 2)),
        'zip'    => str_in($addr['zip'] ?? '', 10),
        'residence' => in_array($addr['residence'] ?? '', ['own', 'rent'], true) ? $addr['residence'] : '',
    ];
    if ($serviceKey === 'personal_auto') {
        if ($address['residence'] === '') $err['address.residence'] = L('Tell us if you own or rent your home.', 'Dinos si eres dueño o rentas tu casa.');
    } else {
        $address['residence'] = '';
    }
    $needStreet = $isAuto || $serviceKey === 'home';
    if ($needStreet && mb_strlen($address['street']) < 3) $err['address.street'] = L('Enter the street address.', 'Escribe la dirección.');
    if ($address['city'] === '') $err['address.city'] = L('Enter the city.', 'Escribe la ciudad.');
    if (!isset(US_STATES[$address['state']])) $err['address.state'] = L('Choose the state.', 'Elige el estado.');
    if (!preg_match('/^\d{5}$/', $address['zip'])) $err['address.zip'] = L('Enter a 5-digit ZIP code.', 'Escribe un código postal de 5 dígitos.');

    $drivers = $vehicles = [];
    $coverage = null;
    $details = [];

    if ($isAuto) {
        [$drivers, $vehicles, $coverage] = validate_auto($in, $serviceKey, $err);
    } else {
        $details = validate_details($svc, is_array($in['details'] ?? null) ? $in['details'] : [], $err);
    }

    if (!bool_in($in['consent'] ?? false)) $err['consent'] = L('Please agree so we can contact you about your quote.', 'Acepta para que podamos contactarte sobre tu cotización.');

    $service = $serviceKey;
    $lang = lang();
    return [compact('service', 'lang', 'applicant', 'address', 'drivers', 'vehicles', 'coverage', 'details'), $err];
}

function validate_auto(array $in, string $serviceKey, array &$err): array
{
    $relationships = relationships();
    $drivers = [];
    $rawDrivers = array_values(is_array($in['drivers'] ?? null) ? $in['drivers'] : []);
    if (count($rawDrivers) < 1) $err['drivers'] = L('Add at least one driver.', 'Agrega al menos un conductor.');
    foreach (array_slice($rawDrivers, 0, MAX_DRIVERS) as $i => $d) {
        $d = is_array($d) ? $d : [];
        $p = "drivers.$i.";
        $row = [
            'first'           => str_in($d['first'] ?? '', 60),
            'last'            => str_in($d['last'] ?? '', 60),
            'dob'             => parse_date_in(str_in($d['dob'] ?? '', 10)),
            'relationship'    => str_in($d['relationship'] ?? '', 20),
            'id_type'         => str_in($d['id_type'] ?? '', 10),
            'id_kind'         => str_in($d['id_kind'] ?? '', 20),
            'license_state'   => strtoupper(str_in($d['license_state'] ?? '', 2)),
            'license_country' => str_in($d['license_country'] ?? '', 60),
            'license_number'  => strtoupper(preg_replace('/\s+/', '', str_in($d['license_number'] ?? '', 30)) ?? ''),
        ];
        if ($i === 0) $row['relationship'] = 'self';
        if (!name_ok($row['first'])) $err[$p . 'first'] = L('Enter a first name.', 'Escribe el nombre.');
        if (!name_ok($row['last'])) $err[$p . 'last'] = L('Enter a last name.', 'Escribe el apellido.');
        if (!$row['dob'] || age_on($row['dob']) < 14 || age_on($row['dob']) > 110) $err[$p . 'dob'] = L('Enter a valid date of birth.', 'Escribe una fecha de nacimiento válida.');
        if (!isset($relationships[$row['relationship']])) $err[$p . 'relationship'] = L('Choose a relationship.', 'Elige el parentesco.');
        if (!isset(id_types()[$row['id_type']])) $err[$p . 'id_type'] = L('Choose an ID type.', 'Elige un tipo de ID.');

        $numberRequired = true;
        if ($row['id_type'] === 'us') {
            $row['license_country'] = $row['id_kind'] = '';
            if (!isset(US_STATES[$row['license_state']])) $err[$p . 'license_state'] = L('Choose the issuing state.', 'Elige el estado que la emitió.');
        } elseif ($row['id_type'] === 'other') {
            $numberRequired = false;
            if (!isset(id_kinds()[$row['id_kind']])) $err[$p . 'id_kind'] = L('Choose the type of ID.', 'Elige el tipo de identificación.');
            if ($row['id_kind'] === 'state_id') {
                $row['license_country'] = '';
                if (!isset(US_STATES[$row['license_state']])) $err[$p . 'license_state'] = L('Choose the state that issued the ID.', 'Elige el estado que emitió la ID.');
            } elseif ($row['id_kind'] !== '') {
                $row['license_state'] = '';
                if ($row['license_country'] === '') $err[$p . 'license_country'] = L('Enter the country that issued the ID.', 'Escribe el país que emitió la ID.');
            }
        }
        if ($row['license_number'] === '' ? $numberRequired : !preg_match('/^[A-Z0-9\-*]{4,25}$/', $row['license_number'])) {
            $err[$p . 'license_number'] = L('Enter the ID number (letters and numbers).', 'Escribe el número (letras y números).');
        }
        $drivers[] = $row;
    }

    if (!bool_in($in['drivers_done'] ?? false)) $err['drivers_done'] = L('Add another driver, or check "No more drivers to add".', 'Agrega otro conductor, o marca "No hay más conductores".');

    $vehicles = [];
    $rawVehicles = array_values(is_array($in['vehicles'] ?? null) ? $in['vehicles'] : []);
    if (count($rawVehicles) < 1) $err['vehicles'] = L('Add at least one vehicle.', 'Agrega al menos un vehículo.');
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
            'lien'          => str_in($v['lien'] ?? '', 3),
            'lienholder'    => str_in($v['lienholder'] ?? '', 80),
            'roadside'      => bool_in($v['roadside'] ?? false),
            'rental'        => bool_in($v['rental'] ?? false),
        ];
        if (!vin_format_ok($vin)) {
            $err[$p . 'vin'] = L('Enter the 17-character VIN (no I, O or Q).', 'Escribe el VIN de 17 caracteres (sin I, O ni Q).');
        } elseif (isset($seen[$vin])) {
            $err[$p . 'vin'] = L('This VIN is already listed.', 'Este VIN ya está en la lista.');
        } else {
            $seen[$vin] = true;
            // Use our own decode (cached, or looked up now) over whatever the browser sent.
            $dec = vin_decode($vin);
            if (!empty($dec['ok'])) {
                foreach (['year', 'make', 'model', 'trim', 'body'] as $k) {
                    $row[$k] = (string) $dec[$k];
                }
                $row['decoded'] = true;
            }
        }
        if (!$row['full_coverage']) {
            $row['deductible'] = $row['lien'] = $row['lienholder'] = '';
        } else {
            if (!isset(deductibles()[$row['deductible']])) $row['deductible'] = 'unsure';
            if (!in_array($row['lien'], ['yes', 'no'], true)) $err[$p . 'lien'] = L('Tell us if this vehicle is financed or leased.', 'Dinos si este vehículo está financiado o en leasing.');
            if ($row['lien'] !== 'yes') $row['lienholder'] = '';
        }
        $vehicles[] = $row;
    }

    if (!bool_in($in['vehicles_done'] ?? false)) $err['vehicles_done'] = L('Add another vehicle, or check "No more vehicles to add".', 'Agrega otro vehículo, o marca "No hay más vehículos".');

    $cov = is_array($in['coverage'] ?? null) ? $in['coverage'] : [];
    // Personal auto is quoted at the Texas minimum; commercial chooses its limits.
    if ($serviceKey === 'personal_auto') $cov['liability'] = 'state_min';
    $start = parse_date_in(str_in($cov['start_date'] ?? '', 10));
    $coverage = [
        'liability'         => str_in($cov['liability'] ?? '', 12),
        'pip'               => bool_in($cov['pip'] ?? false),
        'um'                => bool_in($cov['um'] ?? false),
        'start_date'        => $start ?? '',
        'currently_insured' => in_array($cov['currently_insured'] ?? '', ['yes', 'no'], true) ? $cov['currently_insured'] : '',
        'current_carrier'   => str_in($cov['current_carrier'] ?? '', 60),
        'current_expiration'=> parse_date_in(str_in($cov['current_expiration'] ?? '', 10)) ?? '',
        'comments'          => str_in($cov['comments'] ?? '', 1000),
    ];
    if (!isset(liability_levels($serviceKey)[$coverage['liability']])) $err['coverage.liability'] = L('Choose a liability level.', 'Elige un nivel de responsabilidad.');
    if ($start) {
        $days = (int) (new DateTimeImmutable('today'))->diff(new DateTimeImmutable($start))->format('%r%a');
        if ($days < 0 || $days > 365) $err['coverage.start_date'] = L('Choose a start date within the next 12 months.', 'Elige una fecha dentro de los próximos 12 meses.');
    }
    if ($coverage['currently_insured'] !== 'yes') $coverage['current_carrier'] = $coverage['current_expiration'] = '';

    return [$drivers, $vehicles, $coverage];
}

/** Is a field asked, given the answers so far? ('show_if' => [key, [values]]) */
function field_shown(array $f, array $answers): bool
{
    if (empty($f['show_if'])) {
        return true;
    }
    [$key, $values] = $f['show_if'];
    $v = $answers[$key] ?? '';
    return is_array($v) ? (bool) array_intersect($v, $values) : in_array($v, $values, true);
}

/** Validates the service-specific questions defined in services(). */
function validate_details(array $svc, array $raw, array &$err): array
{
    $out = [];
    foreach ($svc['fields'] ?? [] as $f) {
        $k = $f['key'];
        $p = 'details.' . $k;
        if (!field_shown($f, $out)) {
            $out[$k] = $f['type'] === 'checks' ? [] : '';
            continue;
        }
        $optional = !empty($f['optional']);
        $v = $raw[$k] ?? '';
        switch ($f['type']) {
            case 'radio':
            case 'select':
                $v = str_in($v, 40);
                if (!isset($f['options'][$v])) {
                    $v = '';
                    if (!$optional) $err[$p] = L('Please choose an option.', 'Elige una opción.');
                }
                break;
            case 'checks':
                $v = array_values(array_filter(is_array($v) ? $v : [], fn ($x) => is_string($x) && isset($f['options'][$x])));
                if (!$v && !$optional) $err[$p] = L('Choose at least one.', 'Elige al menos una opción.');
                break;
            case 'vin':
                $v = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', str_in($v, 30)) ?? '');
                if ($v === '') {
                    if (!$optional) $err[$p] = L('Enter the 17-character VIN.', 'Escribe el VIN de 17 caracteres.');
                    break;
                }
                if (!vin_format_ok($v)) {
                    $err[$p] = L('Enter a valid 17-character VIN (no I, O or Q).', 'Escribe un VIN válido de 17 caracteres (sin I, O ni Q).');
                } elseif (($dec = vin_decode($v)) && !empty($dec['ok'])) {
                    $out[$k . '_vehicle'] = vehicle_title($dec) . ($dec['trim'] ? ' ' . $dec['trim'] : '');
                }
                break;
            case 'textarea':
                $v = str_in($v, 1000);
                if ($v === '' && !$optional) $err[$p] = L('Please fill this in.', 'Completa este campo.');
                break;
            default:
                $v = str_in($v, $f['maxlength'] ?? 120);
                if ($v === '' && !$optional) $err[$p] = L('Please fill this in.', 'Completa este campo.');
                elseif ($v !== '' && !empty($f['pattern']) && !preg_match($f['pattern'], $v)) $err[$p] = L('Please check this value.', 'Revisa este dato.');
        }
        $out[$k] = $v;
    }
    return $out;
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

/** Saves a validated request. ID numbers are encrypted at rest. */
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
    $detailWords = [];
    array_walk_recursive($q['details'], function ($v) use (&$detailWords) { $detailWords[] = (string) $v; });
    $search = mb_strtolower(implode(' ', array_merge(
        [$a['first'], $a['last'], $a['email'], digits($a['phone']), $a['company'], $q['address']['city'], $q['address']['zip'], $q['service']],
        array_map(fn ($d) => $d['first'] . ' ' . $d['last'], $q['drivers']),
        array_map(fn ($v) => $v['vin'] . ' ' . vehicle_title($v), $q['vehicles']),
        $detailWords
    )));

    $now = now_utc();
    for ($attempt = 0; $attempt < 5; $attempt++) {
        $ref = new_ref();
        try {
            db_exec(
                'INSERT INTO quotes (ref, service, lang, status, is_read, first_name, last_name, email, phone, city, state, zip,
                    drivers_count, vehicles_count, search, data, ip_hash, user_agent, created_at, updated_at)
                 VALUES (?, ?, ?, \'new\', 0, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
                [
                    $ref, $q['service'], $q['lang'], $a['first'], $a['last'], $a['email'], $a['phone'],
                    $q['address']['city'], $q['address']['state'], $q['address']['zip'],
                    count($q['drivers']), count($q['vehicles']), mb_substr($search, 0, 4000),
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
/* Display helpers (dashboard, emails, CSV)                           */
/* ------------------------------------------------------------------ */

function fmt_dob(?string $iso): string
{
    return $iso ? date('m/d/Y', strtotime($iso)) . ' (' . L('age', 'edad') . ' ' . age_on($iso) . ')' : '';
}

function service_title(?string $key): string
{
    return service((string) $key)['title'] ?? L('Auto insurance', 'Seguro de auto');
}

function driver_license_label(array $d, bool $masked = true): string
{
    $type = $d['id_type'] ?? 'us';
    $where = match ($type) {
        'us'      => US_STATES[$d['license_state']] ?? $d['license_state'],
        'foreign' => $d['license_country'],
        default   => implode(' · ', array_filter([id_kinds()[$d['id_kind'] ?? ''] ?? '', US_STATES[$d['license_state'] ?? ''] ?? '', $d['license_country'] ?? ''])),
    };
    $last4 = $d['license_last4'] ?? '';
    $num = $masked ? ($last4 !== '' ? '••••' . $last4 : '') : ($d['license_number'] ?? '');
    return implode(' · ', array_filter([id_type_label($type), $where, $num]));
}

function vehicle_coverage_list(array $v): array
{
    $out = [];
    if ($v['full_coverage']) {
        $ded = deductibles()[$v['deductible']] ?? '';
        $out[] = L('Full coverage (comp + collision)', 'Cobertura completa (comprensivo + colisión)') . ($v['deductible'] && $v['deductible'] !== 'unsure' ? ', ' . $ded . ' ' . L('deductible', 'deducible') : '');
        if (($v['lien'] ?? '') === 'yes') $out[] = L('Financed/leased', 'Financiado') . (!empty($v['lienholder']) ? ': ' . $v['lienholder'] : '');
        elseif (($v['lien'] ?? '') === 'no') $out[] = L('No lienholder', 'Sin financiera');
    } else {
        $out[] = L('Liability only', 'Solo responsabilidad civil');
    }
    if ($v['roadside']) $out[] = L('Roadside assistance', 'Asistencia en el camino');
    if ($v['rental']) $out[] = L('Rental reimbursement', 'Auto de renta');
    return $out;
}

function policy_coverage_list(array $c, string $service = 'personal_auto'): array
{
    $out = [L('Liability', 'Responsabilidad civil') . ': ' . (liability_levels($service)[$c['liability']][0] ?? '—')];
    if ($c['pip']) $out[] = L('Personal injury protection (PIP)', 'Protección contra lesiones (PIP)');
    if ($c['um']) $out[] = L('Uninsured / underinsured motorist', 'Conductor sin seguro');
    return $out;
}

/** Service questions as [label => readable answer], skipping blanks. */
function details_rows(array $q): array
{
    $svc = service($q['service'] ?? '');
    if (!$svc || empty($svc['fields'])) {
        return [];
    }
    $rows = [];
    foreach ($svc['fields'] as $f) {
        $v = $q['details'][$f['key']] ?? '';
        if ($v === '' || $v === []) continue;
        if ($f['type'] === 'checks') {
            $v = implode(', ', array_map(fn ($x) => $f['options'][$x] ?? $x, (array) $v));
        } elseif (isset($f['options'])) {
            $v = $f['options'][$v] ?? $v;
        }
        if ($f['type'] === 'vin' && !empty($q['details'][$f['key'] . '_vehicle'])) {
            $v .= ' (' . $q['details'][$f['key'] . '_vehicle'] . ')';
        }
        $rows[$f['label']] = (string) $v;
    }
    return $rows;
}

/** One-line summary for lists: "2 drivers · 1 vehicle" or the chosen options. */
function request_summary(array $q): string
{
    if (service($q['service'] ?? 'personal_auto')['flow'] === 'auto') {
        return implode(', ', array_map('vehicle_title', $q['vehicles'] ?? []));
    }
    $rows = details_rows($q);
    unset($rows[L('Anything else we should know?', '¿Algo más que debamos saber?')]);
    return mb_strimwidth(implode(' · ', array_values($rows)), 0, 120, '…');
}

/* ------------------------------------------------------------------ */
/* Emails                                                             */
/* ------------------------------------------------------------------ */

/** Notification to the agency (always in English). */
function email_new_quote(array $q, array $saved): void
{
    with_lang('en', function () use ($q, $saved) {
        $a = $q['applicant'];
        $addr = $q['address'];
        $name = $a['first'] . ' ' . $a['last'];
        $svcTitle = service_title($q['service']);
        $isAuto = service($q['service'])['flow'] === 'auto';

        $html = '<p style="margin:0 0 6px;color:#475174"><b>' . e($svcTitle) . '</b> · Reference <b>' . e($saved['ref']) . '</b> · ' . e(fmt_date(now_utc())) . '</p>';
        if ($q['lang'] === 'es') {
            $html .= '<p style="margin:0 0 6px;color:#B86E0C;font-weight:700">Customer used the Spanish site. Reply in Spanish.</p>';
        }
        $text = "New request ({$svcTitle}) {$saved['ref']}" . ($q['lang'] === 'es' ? " · SPANISH" : '') . "\n\n";

        $contact = array_filter([
            'Name'     => $name,
            'Company'  => $a['company'],
            'Business' => $a['business_type'],
            'Phone'    => $a['phone'],
            'Email'    => $a['email'],
            'Prefers'  => contact_prefs()[$a['contact_pref']],
            'Address'  => trim(trim($addr['street'] . ' ' . $addr['unit']) . "\n" . $addr['city'] . ', ' . $addr['state'] . ' ' . $addr['zip']),
            'Home'     => ['own' => 'Owns', 'rent' => 'Rents'][$addr['residence'] ?? ''] ?? '',
        ]);
        $html .= email_heading('Contact') . email_rows($contact);
        foreach ($contact as $k => $v) $text .= "$k: $v\n";

        if ($isAuto) {
            $nd = count($q['drivers']);
            $html .= email_heading("Drivers ($nd)");
            $text .= "\nDRIVERS ($nd)\n";
            foreach ($q['drivers'] as $i => $d) {
                $rows = [
                    'Driver ' . ($i + 1) => $d['first'] . ' ' . $d['last'] . ' · ' . relationships()[$d['relationship']],
                    'Date of birth'      => fmt_dob($d['dob']),
                    // Full ID numbers are never emailed, only shown in the dashboard.
                    'ID'                 => driver_license_label(['license_last4' => substr($d['license_number'], -4)] + $d),
                ];
                $html .= email_rows($rows);
                foreach ($rows as $k => $v) $text .= "$k: $v\n";
            }
            $nv = count($q['vehicles']);
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
            $policy = ['Coverage' => implode("\n", policy_coverage_list($c, $q['service']))];
            if ($c['start_date']) $policy['Start date'] = date('M j, Y', strtotime($c['start_date']));
            if ($c['currently_insured']) $policy['Currently insured'] = ucfirst($c['currently_insured']) . ($c['current_carrier'] ? ' · ' . $c['current_carrier'] : '') . (!empty($c['current_expiration']) ? ' · expires ' . date('M j, Y', strtotime($c['current_expiration'])) : '');
            if ($c['comments']) $policy['Notes'] = $c['comments'];
            $html .= email_heading('Policy') . email_rows($policy);
            $text .= "\nPOLICY\n";
            foreach ($policy as $k => $v) $text .= "$k: " . str_replace("\n", ', ', $v) . "\n";
            $html .= '<p style="color:#6B7493;font-size:13px;margin:8px 0 0">For security, full ID numbers are only shown in your dashboard.</p>';
        } else {
            $rows = details_rows($q);
            $html .= email_heading('Request') . email_rows($rows);
            $text .= "\nREQUEST\n";
            foreach ($rows as $k => $v) $text .= "$k: $v\n";
        }

        $link = site_url('admin/quote.php?id=' . $saved['id']);
        $text .= "\nOpen in dashboard: $link\n";
        $subject = "New {$svcTitle} request: {$name}" . ($a['company'] ? " ({$a['company']})" : '') . ($q['lang'] === 'es' ? ' [ES]' : '') . " · {$saved['ref']}";

        send_mail(
            (string) cfg('mail.notify_to'),
            $subject,
            email_layout('New ' . strtolower($svcTitle) . ' request from ' . $name, $html, ['Open in dashboard', $link]),
            $text,
            $a['email']
        );
    });
}

/** "We got your request" email, in the language the customer used. */
function email_quote_confirmation(array $q, array $saved): void
{
    if (!cfg('mail.confirm_customer')) {
        return;
    }
    with_lang($q['lang'] ?? 'en', function () use ($q, $saved) {
        $a = $q['applicant'];
        $phone = (string) cfg('site.phone');
        $rows = [L('Reference', 'Referencia') => $saved['ref'], L('Service', 'Servicio') => service_title($q['service'])];
        if ($q['vehicles']) $rows[L('Vehicles', 'Vehículos')] = implode("\n", array_map('vehicle_title', $q['vehicles']));
        $intro = L(
            'Hi %s, thanks for your request. A licensed agent is reviewing your details and will reach out by %s, usually within one business day.',
            'Hola %s, gracias por tu solicitud. Un agente con licencia está revisando tus datos y te contactará por %s, normalmente en un día hábil.'
        );
        $intro = sprintf($intro, $a['first'], mb_strtolower(contact_prefs()[$a['contact_pref']]));
        $html = '<p style="margin:0 0 14px;line-height:1.6">' . e($intro) . '</p>' . email_rows($rows)
            . '<p style="margin:0;line-height:1.6;color:#475174">' . e(L('Need to change something or in a hurry? Call us at', '¿Necesitas cambiar algo o tienes prisa? Llámanos al')) . ' <b>' . e($phone) . '</b> ' . e(L('and mention your reference number.', 'y menciona tu número de referencia.')) . '</p>';
        $text = $intro . "\n\n";
        foreach ($rows as $k => $v) $text .= "$k: " . str_replace("\n", ', ', $v) . "\n";
        $text .= "\n" . L('Questions? Call', '¿Preguntas? Llama al') . " $phone.\n\n" . cfg('site.name');
        send_mail(
            $a['email'],
            L('We received your request', 'Recibimos tu solicitud') . ' (' . $saved['ref'] . ')',
            email_layout(L('We got your request', 'Recibimos tu solicitud'), $html),
            $text,
            (string) (cfg('site.email') ?: cfg('mail.notify_to'))
        );
    });
}
