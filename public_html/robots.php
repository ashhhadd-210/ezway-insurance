<?php
require __DIR__ . '/app/bootstrap.php';

header('Content-Type: text/plain; charset=utf-8');
echo "User-agent: *\n";
foreach (['admin', 'api', 'app', 'storage'] as $dir) {
    echo 'Disallow: ' . url($dir) . "/\n";
}
echo "\nSitemap: " . site_url('sitemap.xml') . "\n";
