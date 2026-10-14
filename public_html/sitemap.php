<?php
require __DIR__ . '/app/bootstrap.php';

header('Content-Type: application/xml; charset=utf-8');
$pages = ['' => '1.0', 'quote' => '0.9', 'coverage' => '0.8', 'about' => '0.6', 'contact' => '0.6', 'privacy' => '0.2'];
echo '<?xml version="1.0" encoding="UTF-8"?>', "\n";
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
<?php foreach ($pages as $path => $priority):
    $file = __DIR__ . '/' . ($path === '' ? 'index' : $path) . '.php'; ?>
  <url>
    <loc><?= e(site_url($path)) ?></loc>
    <lastmod><?= date('Y-m-d', filemtime($file)) ?></lastmod>
    <priority><?= $priority ?></priority>
  </url>
<?php endforeach; ?>
</urlset>
