<?php
require dirname(__DIR__) . '/app/bootstrap.php';

const CONTACT_TOPICS = ['General question', 'Existing policy', 'Claims help', 'Billing', 'Other'];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Allow: POST');
    json_response(['ok' => false, 'error' => 'Method not allowed.'], 405);
}
same_origin_or_fail();
$in = read_json_body(16384);

if (str_in($in['website'] ?? '', 200) !== '') {
    json_response(['ok' => true]);
}
if (!form_token_valid((string) ($in['token'] ?? ''), 3)) {
    json_response(['ok' => false, 'error' => 'This form expired. Please refresh the page and try again.'], 400);
}
if (!throttle('contact', ip_hash(), (int) cfg('security.max_messages_per_hour', 5), 3600, false)) {
    json_response(['ok' => false, 'error' => 'Too many messages from your connection. Please call or email us directly.'], 429);
}
if (!turnstile_ok((string) ($in['turnstile'] ?? ''))) {
    json_response(['ok' => false, 'error' => 'Please complete the security check and try again.'], 400);
}

$m = [
    'name'    => str_in($in['name'] ?? '', 120),
    'email'   => strtolower(str_in($in['email'] ?? '', 160)),
    'phone'   => format_phone(str_in($in['phone'] ?? '', 30)),
    'topic'   => str_in($in['topic'] ?? '', 60),
    'message' => str_in($in['message'] ?? '', 3000),
];
$errors = [];
if (mb_strlen($m['name']) < 2) $errors['name'] = 'Enter your name.';
if (!filter_var($m['email'], FILTER_VALIDATE_EMAIL)) $errors['email'] = 'Enter a valid email address.';
if ($m['phone'] !== '' && strlen(digits($m['phone'])) !== 10) $errors['phone'] = 'Enter a 10-digit phone number, or leave it blank.';
if (!in_array($m['topic'], CONTACT_TOPICS, true)) $m['topic'] = 'General question';
if (mb_strlen($m['message']) < 10) $errors['message'] = 'Tell us a little more (at least 10 characters).';
if (preg_match_all('#https?://#i', $m['message']) > 3) $errors['message'] = 'Please include fewer links.';
if ($errors) {
    json_response(['ok' => false, 'error' => 'Please fix the highlighted fields.', 'errors' => $errors], 422);
}

db_exec(
    'INSERT INTO messages (is_read, name, email, phone, topic, message, ip_hash, created_at) VALUES (0, ?, ?, ?, ?, ?, ?, ?)',
    [$m['name'], $m['email'], $m['phone'], $m['topic'], $m['message'], ip_hash(), now_utc()]
);
throttle('contact', ip_hash(), PHP_INT_MAX, 3600);

$html = email_rows(['Name' => $m['name'], 'Email' => $m['email'], 'Phone' => $m['phone'] ?: '—', 'Topic' => $m['topic']])
    . '<div style="background:#F1F4FA;border-radius:10px;padding:14px 16px;line-height:1.6;white-space:pre-wrap">' . e($m['message']) . '</div>';
$text = "Name: {$m['name']}\nEmail: {$m['email']}\nPhone: {$m['phone']}\nTopic: {$m['topic']}\n\n{$m['message']}";
send_mail(
    (string) cfg('mail.notify_to'),
    'New message: ' . $m['topic'] . ' from ' . $m['name'],
    email_layout('New website message', $html, ['Open messages', site_url('admin/messages.php')]),
    $text,
    $m['email']
);

json_response(['ok' => true]);
