<?php
require_once __DIR__ . '/config/database.php';

header('Content-Type: application/xml; charset=UTF-8');

$scheme = isHttpsRequest() ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$base = rtrim($scheme . '://' . $host . (BASE_URL === '/' ? '' : BASE_URL), '/');

$pages = [
    ['loc' => $base . '/', 'priority' => '1.0'],
    ['loc' => $base . '/login.php', 'priority' => '0.8'],
];

echo '<?xml version="1.0" encoding="UTF-8"?>';
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
<?php foreach ($pages as $page): ?>
    <url>
        <loc><?= htmlspecialchars($page['loc'], ENT_QUOTES, 'UTF-8') ?></loc>
        <changefreq>weekly</changefreq>
        <priority><?= htmlspecialchars($page['priority'], ENT_QUOTES, 'UTF-8') ?></priority>
    </url>
<?php endforeach; ?>
</urlset>
