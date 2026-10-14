<?php
require dirname(__DIR__) . '/app/bootstrap.php';

$vin = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', (string) ($_GET['vin'] ?? '')) ?? '');
if (!vin_format_ok($vin)) {
    json_response(['ok' => false, 'error' => 'invalid'], 422);
}
if (!throttle('vin', ip_hash(), 60, 3600)) {
    json_response(['ok' => false, 'error' => 'rate_limited'], 429);
}

$result = vin_decode($vin);
header('Cache-Control: private, max-age=86400');
json_response($result, ($result['error'] ?? '') === 'unavailable' ? 503 : 200);
