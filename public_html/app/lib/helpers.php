<?php
defined('APP') or exit;

/** Read a config value using dot notation, e.g. cfg('site.phone'). */
function cfg(string $path, mixed $default = null): mixed
{
    $node = $GLOBALS['__config'];
    foreach (explode('.', $path) as $key) {
        if (!is_array($node) || !array_key_exists($key, $node)) {
            return $default;
        }
        $node = $node[$key];
    }
    return $node;
}

function e(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/** URL path of the site root, so the site also works from a sub-folder. */
function base_path(): string
{
    static $base = null;
    if ($base === null) {
        $doc = realpath($_SERVER['DOCUMENT_ROOT'] ?? '') ?: '';
        $pub = realpath(PUBLIC_ROOT) ?: PUBLIC_ROOT;
        $base = ($doc !== '' && str_starts_with($pub, $doc))
            ? rtrim(str_replace('\\', '/', substr($pub, strlen($doc))), '/')
            : '';
    }
    return $base;
}

function url(string $path = ''): string
{
    return base_path() . '/' . ltrim($path, '/');
}

function is_https(): bool
{
    return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || ($_SERVER['SERVER_PORT'] ?? '') === '443'
        || strtolower($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https';
}

function site_url(string $path = ''): string
{
    $base = rtrim((string) cfg('site.url'), '/');
    if ($base === '') {
        $host = preg_replace('/[^a-z0-9.:\-]/i', '', $_SERVER['HTTP_HOST'] ?? 'localhost');
        $base = (is_https() ? 'https' : 'http') . '://' . $host . base_path();
    }
    return $base . '/' . ltrim($path, '/');
}

/** Asset URL with a cache-busting version based on the file's last change. */
function asset(string $path): string
{
    $file = PUBLIC_ROOT . '/assets/' . ltrim($path, '/');
    $v = is_file($file) ? filemtime($file) : 0;
    return url('assets/' . ltrim($path, '/')) . '?v=' . $v;
}

function now_utc(): string
{
    return gmdate('Y-m-d H:i:s');
}

/** Format a stored UTC timestamp in the site's timezone. */
function fmt_date(?string $utc, string $format = 'M j, Y · g:i A'): string
{
    if (!$utc) {
        return '';
    }
    $d = new DateTimeImmutable($utc, new DateTimeZone('UTC'));
    return $d->setTimezone(new DateTimeZone(date_default_timezone_get()))->format($format);
}

function time_ago(?string $utc): string
{
    if (!$utc) {
        return '';
    }
    $diff = time() - (new DateTimeImmutable($utc, new DateTimeZone('UTC')))->getTimestamp();
    return match (true) {
        $diff < 60     => 'just now',
        $diff < 3600   => floor($diff / 60) . ' min ago',
        $diff < 86400  => floor($diff / 3600) . ' h ago',
        $diff < 604800 => floor($diff / 86400) . ' d ago',
        default        => fmt_date($utc, 'M j, Y'),
    };
}

function json_response(array $data, int $status = 200): never
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store');
    echo json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    exit;
}

function redirect(string $to): never
{
    header('Location: ' . $to, true, 303);
    exit;
}

function client_ip(): string
{
    return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
}

/** Visitor IPs are stored only as a keyed hash, never in plain text. */
function ip_hash(): string
{
    return substr(hash_hmac('sha256', client_ip(), app_key()), 0, 32);
}

function digits(string $s): string
{
    return preg_replace('/\D+/', '', $s) ?? '';
}

function format_phone(string $s): string
{
    $d = digits($s);
    if (strlen($d) === 11 && $d[0] === '1') {
        $d = substr($d, 1);
    }
    return strlen($d) === 10
        ? sprintf('(%s) %s-%s', substr($d, 0, 3), substr($d, 3, 3), substr($d, 6))
        : $s;
}

function tel_href(string $phone): string
{
    return 'tel:+1' . substr(digits($phone), -10);
}

function full_address(): string
{
    $a = cfg('site.address');
    return trim(sprintf('%s, %s, %s %s', $a['street'], $a['city'], $a['state'], $a['zip']), ' ,');
}

/** Read and decode a JSON request body, with a size cap. */
function read_json_body(int $maxBytes = 65536): array
{
    $raw = file_get_contents('php://input', false, null, 0, $maxBytes + 1);
    if ($raw === false || strlen($raw) > $maxBytes) {
        json_response(['ok' => false, 'error' => 'Request too large.'], 413);
    }
    $data = json_decode($raw, true);
    if (!is_array($data)) {
        json_response(['ok' => false, 'error' => 'Invalid request.'], 400);
    }
    return $data;
}

/** Trimmed, length-limited string from untrusted input. */
function str_in(mixed $v, int $max = 120): string
{
    if (!is_scalar($v)) {
        return '';
    }
    $s = trim(preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', (string) $v) ?? '');
    return mb_substr($s, 0, $max);
}

function bool_in(mixed $v): bool
{
    return $v === true || $v === 1 || $v === '1' || $v === 'true' || $v === 'on';
}

function log_line(string $file, string $message): void
{
    @file_put_contents(STORAGE . '/' . $file, '[' . date('c') . '] ' . $message . PHP_EOL, FILE_APPEND | LOCK_EX);
}

function icon(string $name, string $class = ''): string
{
    return '<svg class="i' . ($class ? ' ' . $class : '') . '" aria-hidden="true" focusable="false"><use href="#i-' . $name . '"/></svg>';
}

/** Path of a logo file from config (inside /assets/img), or '' when not set or missing. */
function logo_file(string $key): string
{
    $file = (string) cfg('site.' . $key);
    return $file !== '' && is_file(PUBLIC_ROOT . '/assets/img/' . $file) ? $file : '';
}

/** Small square brand mark: the agency's icon if configured, otherwise a shield. */
function logo_mark(string $class = 'logo-mark'): string
{
    if ($mark = logo_file('logo_mark')) {
        return '<img class="' . $class . '" src="' . e(asset('img/' . $mark)) . '" alt="" aria-hidden="true">';
    }
    return '<svg class="' . $class . '" viewBox="0 0 40 40" aria-hidden="true" focusable="false">'
        . '<path d="M20 2.5 5 8v11c0 9.2 6.4 15.9 15 18.5 8.6-2.6 15-9.3 15-18.5V8L20 2.5Z" fill="currentColor"/>'
        . '<path d="M20 2.5 35 8v11c0 9.2-6.4 15.9-15 18.5V2.5Z" fill="#000" opacity=".14"/>'
        . '<path d="M14.5 29.5 18.4 12h3.2l3.9 17.5" fill="none" stroke="#fff" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"/>'
        . '<path d="M20 15.5v2.6M20 21.2v2.6" stroke="var(--accent,#FFB547)" stroke-width="2.2" stroke-linecap="round"/>'
        . '</svg>';
}

/** Full logo. $onDark picks the light version made for dark backgrounds, when there is one. */
function logo_html(bool $onDark = false): string
{
    $logo = ($onDark ? logo_file('logo_dark') : '') ?: logo_file('logo');
    if ($logo !== '') {
        return '<img class="logo-img" src="' . e(asset('img/' . $logo)) . '" alt="' . e(cfg('site.name')) . '">';
    }
    return logo_mark() . '<span class="logo-text">' . e(cfg('site.name')) . '</span>';
}

/** Decorative ring of tilted squares, echoing the EZ Way logo. */
function pinwheel_svg(string $class = 'pinwheel'): string
{
    $out = '<svg class="' . $class . '" viewBox="0 0 400 400" aria-hidden="true" focusable="false">';
    for ($i = 0; $i < 9; $i++) {
        $a = $i * 40;
        $fill = $i % 3 === 2 ? 'var(--cyan-200)' : 'var(--brand-600)';
        $out .= '<rect x="-30" y="-30" width="60" height="60" rx="4" fill="' . $fill . '" transform="rotate(' . $a . ' 200 200) translate(200 58) rotate(' . (18 + $a % 30) . ')"/>';
    }
    return $out . '</svg>';
}
