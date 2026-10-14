<?php
defined('APP') or exit;

/** 17 characters, no I, O or Q. */
function vin_format_ok(string $vin): bool
{
    return (bool) preg_match('/^[A-HJ-NPR-Z0-9]{17}$/', $vin);
}

/** North American VINs carry a check digit in position 9. */
function vin_check_digit_ok(string $vin): bool
{
    static $map = [
        'A' => 1, 'B' => 2, 'C' => 3, 'D' => 4, 'E' => 5, 'F' => 6, 'G' => 7, 'H' => 8,
        'J' => 1, 'K' => 2, 'L' => 3, 'M' => 4, 'N' => 5, 'P' => 7, 'R' => 9,
        'S' => 2, 'T' => 3, 'U' => 4, 'V' => 5, 'W' => 6, 'X' => 7, 'Y' => 8, 'Z' => 9,
    ];
    static $weights = [8, 7, 6, 5, 4, 3, 2, 10, 0, 9, 8, 7, 6, 5, 4, 3, 2];
    if (!vin_format_ok($vin)) {
        return false;
    }
    $sum = 0;
    for ($i = 0; $i < 17; $i++) {
        $c = $vin[$i];
        $sum += (ctype_digit($c) ? (int) $c : $map[$c]) * $weights[$i];
    }
    $r = $sum % 11;
    return $vin[8] === ($r === 10 ? 'X' : (string) $r);
}

function nice_make(string $make): string
{
    $upper = ['BMW', 'GMC', 'MINI', 'MG', 'VW', 'BYD', 'RAM', 'AMC', 'SRT'];
    $m = trim($make);
    if (in_array(strtoupper($m), $upper, true)) {
        return strtoupper($m) === 'RAM' ? 'Ram' : strtoupper($m);
    }
    return mb_convert_case(mb_strtolower($m), MB_CASE_TITLE);
}

/**
 * Decode a VIN with the free NHTSA vPIC service (no API key needed).
 * Results are cached so repeated lookups are instant.
 */
function vin_decode(string $vin): array
{
    $vin = strtoupper($vin);
    if (!vin_format_ok($vin)) {
        return ['ok' => false, 'error' => 'invalid'];
    }

    $cached = db_one('SELECT data FROM vin_cache WHERE vin = ?', [$vin]);
    if ($cached) {
        return json_decode($cached['data'], true) ?: ['ok' => false, 'error' => 'cache'];
    }

    $json = http_get_json('https://vpic.nhtsa.dot.gov/api/vehicles/DecodeVinValues/' . rawurlencode($vin) . '?format=json');
    $r = $json['Results'][0] ?? null;
    if (!$r) {
        return ['ok' => false, 'error' => 'unavailable'];
    }

    $engine = trim(implode(' ', array_filter([
        !empty($r['DisplacementL']) ? round((float) $r['DisplacementL'], 1) . 'L' : '',
        !empty($r['EngineCylinders']) ? $r['EngineCylinders'] . '-cyl' : '',
    ])));

    $result = [
        'ok'    => !empty($r['Make']) && !empty($r['ModelYear']),
        'vin'   => $vin,
        'year'  => (string) ($r['ModelYear'] ?? ''),
        'make'  => nice_make((string) ($r['Make'] ?? '')),
        'model' => trim((string) ($r['Model'] ?? '')),
        'trim'  => trim((string) ($r['Trim'] ?? '')),
        'body'  => trim((string) ($r['BodyClass'] ?? '')),
        'fuel'  => trim((string) ($r['FuelTypePrimary'] ?? '')),
        'drive' => trim((string) ($r['DriveType'] ?? '')),
        'engine'=> $engine,
        'check_digit_ok' => vin_check_digit_ok($vin),
    ];

    if ($result['ok']) {
        try {
            db_exec('INSERT INTO vin_cache (vin, data, created_at) VALUES (?, ?, ?)', [$vin, json_encode($result), now_utc()]);
        } catch (PDOException) {
            // Another request cached it first.
        }
    }
    return $result;
}

function vehicle_title(array $v): string
{
    $t = trim(implode(' ', array_filter([$v['year'] ?? '', $v['make'] ?? '', $v['model'] ?? ''])));
    return $t !== '' ? $t : 'Vehicle (VIN only)';
}
