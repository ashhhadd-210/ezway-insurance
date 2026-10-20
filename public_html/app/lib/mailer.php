<?php
defined('APP') or exit;

/**
 * Sends an HTML email with a plain-text alternative, using either PHP's
 * mail() or an SMTP mailbox (see config 'mail.transport').
 * Failures are logged to /storage/mail.log; the request is never lost because
 * every submission is saved to the dashboard first.
 */
function send_mail(string $to, string $subject, string $html, string $text, ?string $replyTo = null): bool
{
    $from = mail_from();
    $fromName = (string) cfg('mail.from_name');
    if ($to === '' || $from === '') {
        return false;
    }

    $boundary = 'b_' . bin2hex(random_bytes(12));
    $encodedSubject = '=?UTF-8?B?' . base64_encode($subject) . '?=';
    $encodedName = '=?UTF-8?B?' . base64_encode($fromName) . '?=';

    $headers = [
        'Date: ' . date('r'),
        'From: ' . $encodedName . ' <' . $from . '>',
        'MIME-Version: 1.0',
        'Content-Type: multipart/alternative; boundary="' . $boundary . '"',
        'X-Mailer: AgencySite',
    ];
    if ($replyTo && filter_var($replyTo, FILTER_VALIDATE_EMAIL)) {
        $headers[] = 'Reply-To: ' . $replyTo;
    }

    $body = "--$boundary\r\n"
        . "Content-Type: text/plain; charset=UTF-8\r\nContent-Transfer-Encoding: base64\r\n\r\n"
        . chunk_split(base64_encode($text))
        . "--$boundary\r\n"
        . "Content-Type: text/html; charset=UTF-8\r\nContent-Transfer-Encoding: base64\r\n\r\n"
        . chunk_split(base64_encode($html))
        . "--$boundary--\r\n";

    try {
        if (cfg('mail.transport') === 'smtp') {
            smtp_send($from, $to, array_merge(['To: ' . $to, 'Subject: ' . $encodedSubject], $headers), $body);
            return true;
        }
        $ok = mail($to, $encodedSubject, $body, implode("\r\n", $headers), '-f' . $from);
        if (!$ok) {
            log_line('mail.log', "mail() failed for $to: $subject");
        }
        return $ok;
    } catch (Throwable $ex) {
        log_line('mail.log', "SMTP failed for $to: " . $ex->getMessage());
        return false;
    }
}

function smtp_send(string $from, string $to, array $headers, string $body): void
{
    $c = cfg('mail.smtp');
    $secure = strtolower((string) ($c['secure'] ?? 'ssl'));
    $remote = ($secure === 'ssl' ? 'ssl://' : 'tcp://') . $c['host'] . ':' . (int) $c['port'];
    $sock = @stream_socket_client($remote, $errno, $errstr, 15);
    if (!$sock) {
        throw new RuntimeException("Connect failed: $errstr ($errno)");
    }
    stream_set_timeout($sock, 15);

    $read = function () use ($sock): string {
        $data = '';
        while (($line = fgets($sock, 515)) !== false) {
            $data .= $line;
            if (isset($line[3]) && $line[3] === ' ') {
                break;
            }
        }
        return $data;
    };
    $cmd = function (string $line, array $expect) use ($sock, $read): string {
        fwrite($sock, $line . "\r\n");
        $resp = $read();
        if (!in_array((int) substr($resp, 0, 3), $expect, true)) {
            throw new RuntimeException('SMTP error after "' . strtok($line, ' ') . '": ' . trim($resp));
        }
        return $resp;
    };

    $read();
    $helo = preg_replace('/[^a-z0-9.\-]/i', '', $_SERVER['SERVER_NAME'] ?? 'localhost') ?: 'localhost';
    $cmd("EHLO $helo", [250]);
    if ($secure === 'tls') {
        $cmd('STARTTLS', [220]);
        if (!stream_socket_enable_crypto($sock, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
            throw new RuntimeException('STARTTLS failed');
        }
        $cmd("EHLO $helo", [250]);
    }
    if (!empty($c['user'])) {
        $cmd('AUTH LOGIN', [334]);
        $cmd(base64_encode($c['user']), [334]);
        $cmd(base64_encode($c['pass']), [235]);
    }
    $cmd("MAIL FROM:<$from>", [250]);
    $cmd("RCPT TO:<$to>", [250, 251]);
    $cmd('DATA', [354]);
    $data = implode("\r\n", $headers) . "\r\n\r\n" . $body;
    $data = preg_replace('/^\./m', '..', $data);
    $cmd($data . "\r\n.", [250]);
    fwrite($sock, "QUIT\r\n");
    fclose($sock);
}

/** Sender address: mail.from, or no-reply@ the site's own domain. */
function mail_from(): string
{
    $from = (string) cfg('mail.from');
    if ($from !== '') {
        return $from;
    }
    $host = parse_url(site_url(), PHP_URL_HOST) ?: ($_SERVER['SERVER_NAME'] ?? 'localhost');
    return 'no-reply@' . preg_replace('/^www\./i', '', $host);
}

/** Shared HTML wrapper for all outgoing emails. */
function email_layout(string $title, string $inner, ?array $button = null): string
{
    $name = e(cfg('site.name'));
    $btn = $button
        ? '<p style="margin:28px 0 8px"><a href="' . e($button[1]) . '" style="display:inline-block;background:#2B3990;color:#ffffff;text-decoration:none;font-weight:700;padding:13px 22px;border-radius:10px">' . e($button[0]) . '</a></p>'
        : '';
    return '<!doctype html><html><body style="margin:0;background:#F0F3F9;font-family:-apple-system,Segoe UI,Roboto,Arial,sans-serif;color:#0D1640">'
        . '<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#F0F3F9;padding:28px 12px"><tr><td align="center">'
        . '<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:600px;background:#ffffff;border-radius:16px;overflow:hidden;border:1px solid #DFE4EE">'
        . '<tr><td style="background:#0D1640;padding:20px 28px;color:#ffffff;font-weight:800;font-size:18px">' . $name . '</td></tr>'
        . '<tr><td style="padding:28px">'
        . '<h1 style="margin:0 0 16px;font-size:22px;line-height:1.3">' . e($title) . '</h1>'
        . $inner . $btn
        . '</td></tr></table>'
        . '<p style="color:#6B7493;font-size:12px;margin:16px 0 0">' . e(cfg('site.legal_name')) . ' · ' . e(full_address()) . '</p>'
        . '</td></tr></table></body></html>';
}

function email_rows(array $rows): string
{
    $out = '<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;font-size:14px;margin:0 0 18px">';
    foreach ($rows as $label => $value) {
        $out .= '<tr><td style="padding:8px 0;border-bottom:1px solid #EEE;color:#6B7493;width:38%;vertical-align:top">' . e($label) . '</td>'
            . '<td style="padding:8px 0;border-bottom:1px solid #EEE;font-weight:600">' . nl2br(e($value)) . '</td></tr>';
    }
    return $out . '</table>';
}

function email_heading(string $text): string
{
    return '<h2 style="font-size:13px;letter-spacing:.06em;text-transform:uppercase;color:#2B3990;margin:22px 0 6px">' . e($text) . '</h2>';
}
