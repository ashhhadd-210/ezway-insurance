<?php
require __DIR__ . '/app/bootstrap.php';

header('Content-Type: application/xml; charset=utf-8');
$pages = ['' => '1.0', 'quote' => '0.9', 'auto-insurance' => '0.9', 'services' => '0.8', 'contact' => '0.8', 'about' => '0.6', 'privacy' => '0.2'];
echo '<?xml version="1.0" encoding="UTF-8"?>', "\n";
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xhtml="http://www.w3.org/1999/xhtml">
<?php foreach ($pages as $path => $priority):
    $file = __DIR__ . '/' . ($path === '' ? 'index' : $path) . '.php';
    foreach (spanish_enabled() ? ['en', 'es'] : ['en'] as $lang): ?>
  <url>
    <loc><?= e(page_url($path, $lang)) ?></loc>
<?php if (spanish_enabled()): ?>
    <xhtml:link rel="alternate" hreflang="en" href="<?= e(page_url($path, 'en')) ?>"/>
    <xhtml:link rel="alternate" hreflang="es" href="<?= e(page_url($path, 'es')) ?>"/>
<?php endif; ?>
    <lastmod><?= date('Y-m-d', filemtime($file)) ?></lastmod>
    <priority><?= $priority ?></priority>
  </url>
<?php endforeach; endforeach; ?>
</urlset>
