<?php
// Malezi na Watoto — dynamic sitemap.xml generator.
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';

header('Content-Type: application/xml; charset=utf-8');

$urls = [];

$staticPages = [
    ['loc' => '/index.php',       'priority' => '1.0', 'changefreq' => 'weekly'],
    ['loc' => '/about.php',       'priority' => '0.8', 'changefreq' => 'monthly'],
    ['loc' => '/what-we-do.php',  'priority' => '0.8', 'changefreq' => 'monthly'],
    ['loc' => '/programs.php',    'priority' => '0.8', 'changefreq' => 'monthly'],
    ['loc' => '/gallery.php',     'priority' => '0.7', 'changefreq' => 'weekly'],
    ['loc' => '/news.php',        'priority' => '0.7', 'changefreq' => 'weekly'],
    ['loc' => '/contact.php',     'priority' => '0.6', 'changefreq' => 'yearly'],
];
foreach ($staticPages as $p) {
    $urls[] = [
        'loc' => SITE_URL . $p['loc'],
        'lastmod' => date('Y-m-d'),
        'priority' => $p['priority'],
        'changefreq' => $p['changefreq'],
    ];
}

try {
    foreach ($pdo->query("SELECT slug, updated_at, created_at FROM posts WHERE status = 'published' ORDER BY created_at DESC") as $row) {
        $urls[] = [
            'loc' => SITE_URL . '/news-single.php?slug=' . urlencode($row['slug']),
            'lastmod' => date('Y-m-d', strtotime($row['updated_at'] ?: $row['created_at'])),
            'priority' => '0.6',
            'changefreq' => 'monthly',
        ];
    }
} catch (Exception $e) { /* posts table missing */ }

// Programs are listed on /programs.php (no single-page URL), so no per-program entries.

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"' . "\n";
echo '        xmlns:xhtml="http://www.w3.org/1999/xhtml">' . "\n";
foreach ($urls as $u) {
    echo "  <url>\n";
    echo '    <loc>' . htmlspecialchars($u['loc'], ENT_XML1) . "</loc>\n";
    echo '    <lastmod>' . $u['lastmod'] . "</lastmod>\n";
    echo '    <changefreq>' . $u['changefreq'] . "</changefreq>\n";
    echo '    <priority>' . $u['priority'] . "</priority>\n";
    // hreflang alternates
    $langs = ['en', 'sw'];
    foreach ($langs as $l) {
        $sep = strpos($u['loc'], '?') === false ? '?' : '&';
        echo '    <xhtml:link rel="alternate" hreflang="' . $l . '" href="' . htmlspecialchars($u['loc'] . $sep . 'lang=' . $l, ENT_XML1) . '"/>' . "\n";
    }
    echo "  </url>\n";
}
echo '</urlset>' . "\n";
