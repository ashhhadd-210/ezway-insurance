<?php
defined('APP') or exit;

/**
 * Preview mode (Vercel only, switched on with the DEMO_MODE env variable).
 * The preview has no permanent disk, so every fresh start gets a ready-made
 * agent login and a set of sample requests to show what the dashboard looks
 * like. Never enabled on the real site.
 */
function demo_enabled(): bool
{
    return (bool) cfg('demo.enabled', false);
}

function demo_seed(): void
{
    if (!demo_enabled() || (int) db_val('SELECT COUNT(*) FROM admins') > 0) {
        return;
    }
    $email = (string) cfg('demo.email');
    $pass = (string) cfg('demo.password');
    if ($email === '' || $pass === '') {
        return;
    }
    db_exec('INSERT INTO admins (name, email, password_hash, created_at) VALUES (?, ?, ?, ?)', ['Hector', strtolower($email), password_hash($pass, PASSWORD_DEFAULT), now_utc()]);

    $note = 'Sample request for the preview.';
    foreach (demo_samples() as [$minutesAgo, $status, $q]) {
        $saved = save_quote($q + ['lang' => 'en', 'drivers' => [], 'vehicles' => [], 'coverage' => null, 'details' => []]);
        $at = gmdate('Y-m-d H:i:s', time() - $minutesAgo * 60);
        db_exec('UPDATE quotes SET status = ?, notes = ?, created_at = ?, updated_at = ?, is_read = ? WHERE id = ?',
            [$status, $note, $at, $at, $status === 'new' ? 0 : 1, $saved['id']]);
    }
    db_exec('INSERT INTO messages (is_read, name, email, phone, topic, message, ip_hash, created_at) VALUES (0, ?, ?, ?, ?, ?, ?, ?)', [
        'Patricia Gómez', 'patricia@example.com', '(512) 555-0147', 'Existing policy',
        "Hi, I need to add my son to my auto policy. He just got his license. Can someone call me after 5 PM?\n\n(Sample message for the preview.)",
        '', gmdate('Y-m-d H:i:s', time() - 95 * 60),
    ]);
}

function demo_samples(): array
{
    $app = fn (string $first, string $last, string $dob, string $phone, string $pref = 'phone', string $company = '', string $trade = '') => [
        'first' => $first, 'last' => $last, 'dob' => $dob, 'email' => strtolower(strtr($first, ['á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u', 'ñ' => 'n'])) . '@example.com',
        'phone' => $phone, 'contact_pref' => $pref, 'company' => $company, 'business_type' => $trade,
    ];
    $addr = fn (string $street, string $zip, string $residence = '') => ['street' => $street, 'unit' => '', 'city' => 'Austin', 'state' => 'TX', 'zip' => $zip, 'residence' => $residence];
    $driver = fn (string $first, string $last, string $dob, string $rel, string $type, string $kind, string $state, string $country, string $num) => [
        'first' => $first, 'last' => $last, 'dob' => $dob, 'relationship' => $rel, 'id_type' => $type, 'id_kind' => $kind,
        'license_state' => $state, 'license_country' => $country, 'license_number' => $num,
    ];
    $vehicle = fn (string $vin, string $year, string $make, string $model, string $trim, string $body, bool $full, string $lien = '', string $lienholder = '', bool $roadside = false, bool $rental = false) => [
        'vin' => $vin, 'year' => $year, 'make' => $make, 'model' => $model, 'trim' => $trim, 'body' => $body, 'decoded' => true,
        'full_coverage' => $full, 'deductible' => $full ? '500' : '', 'lien' => $lien, 'lienholder' => $lienholder, 'roadside' => $roadside, 'rental' => $rental,
    ];
    $cov = fn (string $liability, bool $pip, bool $um, string $insured = '', string $carrier = '', int $expiresInDays = 0, string $comments = '') => [
        'liability' => $liability, 'pip' => $pip, 'um' => $um, 'start_date' => '', 'currently_insured' => $insured, 'current_carrier' => $carrier,
        'current_expiration' => $expiresInDays ? date('Y-m-d', strtotime("+$expiresInDays days")) : '', 'comments' => $comments,
    ];

    return [
        [25, 'new', ['service' => 'bonds', 'applicant' => $app('Daniel', 'Martínez', '1985-03-14', '(512) 555-0110'), 'address' => $addr('', '78744'),
            'details' => ['bond_type' => 'permit', 'bond_amount' => '$10,000', 'notes' => 'Permit for a fence installation business.']]],
        [130, 'new', ['service' => 'personal_auto', 'lang' => 'es', 'applicant' => $app('María', 'López', '1988-04-12', '(512) 555-0142', 'text'),
            'address' => $addr('500 E Rundberg Ln', '78753', 'rent'),
            'drivers' => [
                $driver('María', 'López', '1988-04-12', 'self', 'us', '', 'TX', '', '41234567'),
                $driver('Carlos', 'López', '1985-09-30', 'spouse', 'other', 'matricula', '', 'México', ''),
            ],
            'vehicles' => [
                $vehicle('1HGCV1F34LA012345', '2020', 'Honda', 'Accord', 'Sport', 'Sedan/Saloon', true, 'yes', 'Capital One Auto Finance', true),
                $vehicle('5YJ3E1EA7KF317000', '2019', 'Tesla', 'Model 3', '', 'Sedan/Saloon', false, '', '', false, true),
            ],
            'coverage' => $cov('state_min', true, true, 'yes', 'Progressive', 18, 'Looking for the cheapest option.')]],
        [310, 'new', ['service' => 'business', 'applicant' => $app('Carlos', 'Ramírez', '1979-11-02', '(512) 555-0163', 'phone', 'Ramírez Landscaping', 'Landscaping'),
            'address' => $addr('', '78745'),
            'details' => ['coverages' => ['general_liability', 'workers_comp'], 'payroll' => '$60,000', 'gross_income' => '$180,000', 'employees' => '2_5', 'years' => '3_plus', 'notes' => 'Need a certificate of insurance for a new client by Friday.']]],
        [1440, 'contacted', ['service' => 'commercial_auto', 'applicant' => $app('José', 'Hernández', '1990-07-22', '(512) 555-0178', 'phone', 'Hernández Roofing LLC', 'Roofing'),
            'address' => $addr('9515 N Lamar Blvd', '78753'),
            'drivers' => [
                $driver('José', 'Hernández', '1990-07-22', 'self', 'us', '', 'TX', '', '39876543'),
                $driver('Luis', 'Pérez', '1995-01-02', 'employee', 'other', 'foreign_license', '', 'México', 'MX123456'),
            ],
            'vehicles' => [$vehicle('1FTFW1E50MFA12345', '2021', 'Ford', 'F-150', 'XLT', 'Pickup', true, 'no', '', true)],
            'coverage' => $cov('csl_1m', false, true, 'no')]],
        [2900, 'quoted', ['service' => 'home', 'applicant' => $app('Linda', 'Johnson', '1962-06-08', '(512) 555-0191', 'phone'),
            'address' => $addr('1405 Parker Ln', '78741'),
            'details' => ['coverage_for' => 'own', 'property_type' => 'house', 'year_built' => '1998', 'insured' => 'yes', 'roof_year' => '2015', 'claims' => 'no', 'notes' => 'Current policy renews next month.']]],
        [3400, 'new', ['service' => 'home', 'applicant' => $app('Kevin', 'Nguyen', '1996-02-19', '(512) 555-0125', 'email'),
            'address' => $addr('2201 Wickersham Ln', '78741'),
            'details' => ['coverage_for' => 'rent', 'property_type' => 'apartment', 'insured' => 'no', 'additional_insured' => 'yes', 'additional_insured_name' => 'Riverside Apartments LLC']]],
        [4300, 'won', ['service' => 'title', 'applicant' => $app('Robert', 'Smith', '1971-12-01', '(512) 555-0136', 'phone'),
            'address' => $addr('', '78754'),
            'details' => ['needs' => 'transfer', 'vin' => '4T1G11AK5LU312845', 'vin_vehicle' => '2020 Toyota Camry SE']]],
        [5800, 'contacted', ['service' => 'llc', 'applicant' => $app('Ana', 'Torres', '1993-08-27', '(512) 555-0158', 'text'),
            'address' => $addr('', '78758'),
            'details' => ['business_name' => 'Torres Cleaning Services LLC', 'notes' => 'Also interested in general liability once registered.']]],
    ];
}
