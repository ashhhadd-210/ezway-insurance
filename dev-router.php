<?php
/**
 * Local preview only (not uploaded). Mimics the .htaccess rules for PHP's
 * built-in server:  php -S localhost:8080 -t public_html dev-router.php
 */
$root = __DIR__ . '/public_html';
$path = rawurldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));

if (preg_match('#^/(app|storage)(/|$)|/\.#', $path)) {
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
if (isset($aliases[$path])) {
    require $root . $aliases[$path];
    return true;
}
if ($path !== '/' && is_file($root . $path)) {
    return false; // let the server handle real files
}
if (is_dir($root . $path) && is_file(rtrim($root . $path, '/') . '/index.php')) {
    $_SERVER['SCRIPT_NAME'] = rtrim($path, '/') . '/index.php';
    require rtrim($root . $path, '/') . '/index.php';
    return true;
}
$candidate = $root . rtrim($path, '/') . '.php';
if (is_file($candidate)) {
    require $candidate;
    return true;
}
require $root . '/404.php';
return true;
