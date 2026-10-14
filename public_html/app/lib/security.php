<?php
defined('APP') or exit;

function send_security_headers(bool $isAdmin = false): void
{
    if (headers_sent()) {
        return;
    }
    $csp = implode('; ', [
        "default-src 'self'",
        "script-src 'self' https://challenges.cloudflare.com",
        "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com",
        "font-src 'self' https://fonts.gstatic.com",
        "img-src 'self' data:",
        "connect-src 'self' https://vpic.nhtsa.dot.gov https://api.zippopotam.us",
        "frame-src https://www.google.com https://challenges.cloudflare.com",
        "base-uri 'self'",
        "form-action 'self'",
        "frame-ancestors 'self'",
    ]);
    header('Content-Security-Policy: ' . $csp);
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: SAMEORIGIN');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('Permissions-Policy: camera=(), microphone=(), geolocation=(), payment=()');
    if (is_https()) {
        header('Strict-Transport-Security: max-age=31536000');
    }
    if ($isAdmin) {
        header('Cache-Control: no-store, private');
        header('X-Robots-Tag: noindex, nofollow');
    }
}

/* ------------------------------------------------------------------ */
/* Admin sessions & CSRF                                              */
/* ------------------------------------------------------------------ */

function admin_session_start(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }
    session_name('agency_admin');
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => base_path() . '/admin',
        'secure'   => is_https(),
        'httponly' => true,
        'samesite' => 'Strict',
    ]);
    ini_set('session.use_strict_mode', '1');
    session_start();

    // Sign out after 4 hours of inactivity.
    if (!empty($_SESSION['admin_id']) && time() - ($_SESSION['seen'] ?? 0) > 4 * 3600) {
        $_SESSION = [];
        session_regenerate_id(true);
    }
    $_SESSION['seen'] = time();
}

function current_admin(): ?array
{
    admin_session_start();
    if (empty($_SESSION['admin_id'])) {
        return null;
    }
    static $admin = null;
    return $admin ??= db_one('SELECT id, name, email FROM admins WHERE id = ?', [$_SESSION['admin_id']]);
}

function require_admin(): array
{
    $admin = current_admin();
    if (!$admin) {
        if ((int) db_val('SELECT COUNT(*) FROM admins') === 0) {
            redirect(url('admin/setup.php'));
        }
        redirect(url('admin/login.php'));
    }
    return $admin;
}

function csrf_token(): string
{
    admin_session_start();
    return $_SESSION['csrf'] ??= bin2hex(random_bytes(32));
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf" value="' . e(csrf_token()) . '">';
}

function csrf_check(): void
{
    admin_session_start();
    $sent = (string) ($_POST['csrf'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');
    if (empty($_SESSION['csrf']) || !hash_equals($_SESSION['csrf'], $sent)) {
        http_response_code(419);
        exit('Your session expired. Go back, refresh the page and try again.');
    }
}

function flash(?string $message = null, string $type = 'ok'): ?array
{
    admin_session_start();
    if ($message !== null) {
        $_SESSION['flash'] = [$type, $message];
        return null;
    }
    $f = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $f;
}

/* ------------------------------------------------------------------ */
/* Public form spam protection                                        */
/* ------------------------------------------------------------------ */

/** Signed timestamp embedded in public forms. Bots that post instantly fail the check. */
function form_token(): string
{
    $ts = (string) time();
    return $ts . '.' . substr(hash_hmac('sha256', 'form|' . $ts, app_key()), 0, 32);
}

function form_token_valid(string $token, int $minSeconds = 3, int $maxSeconds = 86400): bool
{
    [$ts, $sig] = array_pad(explode('.', $token, 2), 2, '');
    if (!ctype_digit($ts) || !hash_equals(substr(hash_hmac('sha256', 'form|' . $ts, app_key()), 0, 32), $sig)) {
        return false;
    }
    $age = time() - (int) $ts;
    return $age >= $minSeconds && $age <= $maxSeconds;
}

/** Accept only same-site browser requests for public JSON endpoints. */
function same_origin_or_fail(): void
{
    $origin = $_SERVER['HTTP_ORIGIN'] ?? $_SERVER['HTTP_REFERER'] ?? '';
    if ($origin === '') {
        return; // Some privacy tools strip both; other checks still apply.
    }
    $host = parse_url($origin, PHP_URL_HOST);
    $self = preg_replace('/:\d+$/', '', $_SERVER['HTTP_HOST'] ?? '');
    if (!$host || strcasecmp($host, $self) !== 0) {
        json_response(['ok' => false, 'error' => 'Request blocked.'], 403);
    }
}

/**
 * Sliding-window rate limit. Returns false once $limit hits happen within
 * $window seconds for this bucket/key.
 */
function throttle(string $bucket, string $key, int $limit, int $window, bool $record = true): bool
{
    $hash = hash('sha256', $key);
    $since = gmdate('Y-m-d H:i:s', time() - $window);
    $count = (int) db_val('SELECT COUNT(*) FROM throttle WHERE bucket = ? AND key_hash = ? AND created_at > ?', [$bucket, $hash, $since]);
    if ($count >= $limit) {
        return false;
    }
    if ($record) {
        db_exec('INSERT INTO throttle (bucket, key_hash, created_at) VALUES (?, ?, ?)', [$bucket, $hash, now_utc()]);
        if (random_int(1, 50) === 1) {
            db_exec('DELETE FROM throttle WHERE created_at < ?', [gmdate('Y-m-d H:i:s', time() - 172800)]);
        }
    }
    return true;
}

function throttle_clear(string $bucket, string $key): void
{
    db_exec('DELETE FROM throttle WHERE bucket = ? AND key_hash = ?', [$bucket, hash('sha256', $key)]);
}

function turnstile_enabled(): bool
{
    return cfg('security.turnstile_site_key') !== '' && cfg('security.turnstile_secret_key') !== '';
}

function turnstile_ok(string $response): bool
{
    if (!turnstile_enabled()) {
        return true;
    }
    if ($response === '') {
        return false;
    }
    $result = http_post_form('https://challenges.cloudflare.com/turnstile/v0/siteverify', [
        'secret'   => cfg('security.turnstile_secret_key'),
        'response' => $response,
        'remoteip' => client_ip(),
    ]);
    return is_array($result) && !empty($result['success']);
}

/* ------------------------------------------------------------------ */
/* Outbound HTTP                                                      */
/* ------------------------------------------------------------------ */

function http_get_json(string $url, int $timeout = 6): ?array
{
    if (function_exists('curl_init')) {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => $timeout,
            CURLOPT_CONNECTTIMEOUT => 4,
            CURLOPT_USERAGENT      => 'AgencyQuoteSite/1.0',
            CURLOPT_HTTPHEADER     => ['Accept: application/json'],
        ]);
        $body = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($body === false || $code >= 400) {
            return null;
        }
    } else {
        $ctx = stream_context_create(['http' => ['timeout' => $timeout, 'header' => "Accept: application/json\r\n"]]);
        $body = @file_get_contents($url, false, $ctx);
        if ($body === false) {
            return null;
        }
    }
    $data = json_decode((string) $body, true);
    return is_array($data) ? $data : null;
}

function http_post_form(string $url, array $fields, int $timeout = 8): ?array
{
    $payload = http_build_query($fields);
    if (function_exists('curl_init')) {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $payload,
            CURLOPT_TIMEOUT        => $timeout,
        ]);
        $body = curl_exec($ch);
        curl_close($ch);
    } else {
        $ctx = stream_context_create(['http' => [
            'method' => 'POST', 'timeout' => $timeout, 'content' => $payload,
            'header' => "Content-Type: application/x-www-form-urlencoded\r\n",
        ]]);
        $body = @file_get_contents($url, false, $ctx);
    }
    $data = is_string($body) ? json_decode($body, true) : null;
    return is_array($data) ? $data : null;
}
