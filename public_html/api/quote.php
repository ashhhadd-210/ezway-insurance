<?php
require dirname(__DIR__) . '/app/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Allow: POST');
    json_response(['ok' => false, 'error' => 'Method not allowed.'], 405);
}
same_origin_or_fail();
$in = read_json_body();
set_lang((string) ($in['lang'] ?? 'en'));

// Honeypot: real visitors never see or fill this field. Pretend success so bots move on.
if (str_in($in['website'] ?? '', 200) !== '') {
    json_response(['ok' => true, 'ref' => new_ref()]);
}
if (!form_token_valid((string) ($in['token'] ?? ''), 8)) {
    json_response(['ok' => false, 'error' => L('This form expired. Please refresh the page and try again.', 'Este formulario expiró. Recarga la página e inténtalo de nuevo.')], 400);
}
if (!throttle('quote', ip_hash(), (int) cfg('security.max_quotes_per_hour', 5), 3600, false)) {
    json_response(['ok' => false, 'error' => L('Too many requests from your connection. Please call us instead, or try again in an hour.', 'Demasiadas solicitudes desde tu conexión. Llámanos o inténtalo en una hora.')], 429);
}
if (!turnstile_ok((string) ($in['turnstile'] ?? ''))) {
    json_response(['ok' => false, 'error' => L('Please complete the security check and try again.', 'Completa la verificación de seguridad e inténtalo de nuevo.')], 400);
}

[$quote, $errors] = validate_quote($in);
if ($errors) {
    json_response(['ok' => false, 'error' => L('Please fix the highlighted fields.', 'Corrige los campos marcados.'), 'errors' => $errors], 422);
}

try {
    $saved = save_quote($quote);
} catch (Throwable $ex) {
    error_log('Quote save failed: ' . $ex->getMessage());
    json_response(['ok' => false, 'error' => L('We could not save your request. Please try again or call us.', 'No pudimos guardar tu solicitud. Inténtalo de nuevo o llámanos.')], 500);
}
throttle('quote', ip_hash(), PHP_INT_MAX, 3600);

// Reply to the visitor first, then send emails, so they never wait on the mail server.
ignore_user_abort(true);
$body = json_encode(['ok' => true, 'ref' => $saved['ref']]);
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
header('Content-Length: ' . strlen($body));
header('Connection: close');
echo $body;
if (function_exists('fastcgi_finish_request')) {
    fastcgi_finish_request();
} else {
    @ob_end_flush();
    flush();
}

email_new_quote($quote, $saved);
email_quote_confirmation($quote, $saved);
