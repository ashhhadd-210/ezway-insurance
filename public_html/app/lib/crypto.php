<?php
defined('APP') or exit;

/**
 * Sensitive values (driver license numbers) are encrypted with AES-256-GCM
 * before they are saved, and decrypted only inside the admin dashboard.
 */

function app_key(): string
{
    static $key = null;
    if ($key !== null) {
        return $key;
    }
    $configured = (string) cfg('app_key');
    if ($configured !== '') {
        $raw = base64_decode($configured, true);
        return $key = ($raw !== false && strlen($raw) === 32) ? $raw : hash('sha256', $configured, true);
    }
    $file = STORAGE . '/app.key';
    if (!is_file($file)) {
        $fh = @fopen($file, 'x');
        if ($fh) {
            fwrite($fh, base64_encode(random_bytes(32)));
            fclose($fh);
            @chmod($file, 0600);
        }
    }
    $raw = base64_decode(trim((string) @file_get_contents($file)), true);
    if ($raw === false || strlen($raw) !== 32) {
        throw new RuntimeException('Encryption key missing or unreadable: check that /storage is writable.');
    }
    return $key = $raw;
}

function encrypt_str(string $plain): string
{
    if ($plain === '') {
        return '';
    }
    $iv = random_bytes(12);
    $tag = '';
    $cipher = openssl_encrypt($plain, 'aes-256-gcm', app_key(), OPENSSL_RAW_DATA, $iv, $tag);
    if ($cipher === false) {
        throw new RuntimeException('Encryption failed.');
    }
    return 'v1:' . base64_encode($iv . $tag . $cipher);
}

function decrypt_str(string $stored): string
{
    if (!str_starts_with($stored, 'v1:')) {
        return '';
    }
    $raw = base64_decode(substr($stored, 3), true);
    if ($raw === false || strlen($raw) < 29) {
        return '';
    }
    $plain = openssl_decrypt(substr($raw, 28), 'aes-256-gcm', app_key(), OPENSSL_RAW_DATA, substr($raw, 0, 12), substr($raw, 12, 16));
    return $plain === false ? '' : $plain;
}

function mask_tail(string $value, int $show = 4): string
{
    $len = mb_strlen($value);
    if ($len === 0) {
        return '';
    }
    return str_repeat('•', max(0, min(8, $len - $show))) . mb_substr($value, -min($show, $len));
}
