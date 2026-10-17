<?php
/**
 * Vercel entry point (not used on HostGator).
 *
 * Vercel runs PHP through a single serverless function, so this file plays the
 * role of .htaccess: it maps each URL to the matching file in public_html.
 *
 * Vercel's disk is read-only and not shared between instances, so the database
 * and keys live in /tmp. That is fine for a preview, but saved quotes and
 * dashboard logins can reset at any time. Production runs on HostGator.
 */
$root = dirname(__DIR__) . '/public_html';
$path = rawurldecode(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/');

$storage = '/tmp/ezway-storage';
if (!is_dir($storage)) {
    @mkdir($storage, 0700, true);
}
putenv('APP_STORAGE=' . $storage);
$_SERVER['DOCUMENT_ROOT'] = $root;
session_save_path($storage);

if (preg_match('#^/(app|storage)(/|$)|/\.(?!well-known/)|^/api/index\.php#', $path)) {
    http_response_code(403);
    exit('Forbidden');
}

// Old Coverage page is now Auto insurance
if (preg_match("#^(/es)?/coverage/?$#", $path, $m)) {
    header("Location: " . ($m[1] ?? "") . "/auto-insurance", true, 301);
    exit;
}
// Spanish pages use the same files: /es/about -> about.php (the language is read from the URL)
if (preg_match("#^/es(/.*)?$#", $path, $m)) {
    $path = ($m[1] ?? "") === "" ? "/" : $m[1];
}
$aliases = ['/sitemap.xml' => '/sitemap.php', '/robots.txt' => '/robots.php'];
$target = null;
if (isset($aliases[$path])) {
    $target = $aliases[$path];
} elseif (str_ends_with($path, '.php') && is_file($root . $path)) {
    $target = $path;
} elseif (is_file(rtrim($root . $path, '/') . '/index.php')) {
    $target = rtrim($path, '/') . '/index.php';
} elseif ($path !== '/' && is_file($root . rtrim($path, '/') . '.php')) {
    $target = rtrim($path, '/') . '.php';
}

if ($target === null) {
    $target = '/404.php';
}

$_SERVER['SCRIPT_NAME'] = $_SERVER['PHP_SELF'] = $target;
$_SERVER['SCRIPT_FILENAME'] = $root . $target;
chdir(dirname($root . $target));
require $root . $target;
