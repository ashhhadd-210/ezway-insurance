<?php
defined('APP') or exit;

/**
 * English lives at /, Spanish at /es/. Both use the same page files; copy is
 * written inline as L('English', 'Español') so each text sits next to its
 * translation.
 */

function detect_lang(): string
{
    $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
    $base = base_path();
    if ($base !== '' && str_starts_with($path, $base)) {
        $path = substr($path, strlen($base));
    }
    return preg_match('#^/es(/|$)#', $path) ? 'es' : 'en';
}

function lang(): string
{
    return $GLOBALS['__lang'] ??= detect_lang();
}

function set_lang(string $lang): void
{
    $GLOBALS['__lang'] = $lang === 'es' ? 'es' : 'en';
}

function L(string $en, string $es): string
{
    return lang() === 'es' ? $es : $en;
}

/** Pick the current language from a ['en' => .., 'es' => ..] value. */
function LA(mixed $value): string
{
    return is_array($value) ? (string) ($value[lang()] ?? $value['en'] ?? '') : (string) $value;
}

/** Site path in the current language: lurl('about') -> /about or /es/about. */
function lurl(string $path = ''): string
{
    return url((lang() === 'es' ? 'es/' : '') . ltrim($path, '/'));
}

/** Absolute URL of a page in a given language (canonical, hreflang, sitemap). */
function page_url(string $path, string $lang): string
{
    return site_url(($lang === 'es' ? 'es/' : '') . ltrim($path, '/'));
}

/** "19:30" -> "7:30 PM" / "7:30 p. m." */
function fmt_time(string $hhmm): string
{
    [$h, $m] = array_map('intval', explode(':', $hhmm));
    $suffix = $h >= 12 ? L('PM', 'p. m.') : L('AM', 'a. m.');
    $h12 = $h % 12 ?: 12;
    return $h12 . ($m ? ':' . sprintf('%02d', $m) : '') . ' ' . $suffix;
}

function fmt_range(?array $range): string
{
    return $range ? fmt_time($range[0]) . ' – ' . fmt_time($range[1]) : L('Closed', 'Cerrado');
}

/* ------------------------------------------------------------------ */
/* Offices                                                            */
/* ------------------------------------------------------------------ */

function offices(): array
{
    return cfg('site.offices', []);
}

function main_office(): array
{
    return offices()[0];
}

function office_address(array $o, bool $withCity = true): string
{
    return $o['street'] . ($withCity ? ', ' . $o['city'] . ', ' . $o['state'] . ' ' . $o['zip'] : '');
}

function full_address(): string
{
    return office_address(main_office());
}

/** Rows for an hours table: [label, "9 AM – 7:30 PM"]. */
function office_hours_rows(array $o): array
{
    $h = $o['hours'];
    return [
        [L('Mon – Fri', 'Lun – Vie'), fmt_range($h['weekdays'])],
        [L('Saturday', 'Sábado'), fmt_range($h['sat'])],
        [L('Sunday', 'Domingo'), fmt_range($h['sun'])],
    ];
}

/** Hours keyed by JS weekday (0 = Sunday) for the live "Open now" badge. */
function office_hours_js(array $o): string
{
    $h = $o['hours'];
    $out = ['0' => $h['sun'], '6' => $h['sat']];
    for ($d = 1; $d <= 5; $d++) {
        $out[(string) $d] = $h['weekdays'];
    }
    return json_encode($out);
}

/** Google Maps link that lands on the office's Google Business listing. */
function maps_link(array $o): string
{
    return 'https://www.google.com/maps/search/?api=1&query=' . rawurlencode(cfg('site.name') . ' ' . office_address($o));
}

function maps_embed(array $o): string
{
    return 'https://www.google.com/maps?q=' . rawurlencode(cfg('site.name') . ', ' . office_address($o)) . '&output=embed';
}

/** schema.org openingHoursSpecification for one office. */
function office_schema_hours(array $o): array
{
    $spec = [];
    $map = ['weekdays' => ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'], 'sat' => ['Saturday'], 'sun' => ['Sunday']];
    foreach ($map as $k => $days) {
        if (!empty($o['hours'][$k])) {
            $spec[] = ['@type' => 'OpeningHoursSpecification', 'dayOfWeek' => $days, 'opens' => $o['hours'][$k][0], 'closes' => $o['hours'][$k][1]];
        }
    }
    return $spec;
}
