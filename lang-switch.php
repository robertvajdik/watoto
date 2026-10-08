<?php
// Sets language preference and redirects the user back to where they came from.
require_once __DIR__ . '/includes/functions.php';

$target = $_GET['lang'] ?? '';
if (in_array($target, ['en', 'sw'], true)) {
    $_SESSION['lang'] = $target;
    setcookie('wmg_lang', $target, time() + 60 * 60 * 24 * 365, '/');
}

$back = $_GET['back'] ?? ($_SERVER['HTTP_REFERER'] ?? SITE_URL . '/index.php');

// Only allow same-origin redirects.
$host = parse_url($back, PHP_URL_HOST);
$siteHost = parse_url(SITE_URL, PHP_URL_HOST);
if ($host && $siteHost && $host !== $siteHost) {
    $back = SITE_URL . '/index.php';
}

// Strip any lang param from the target URL so it doesn't cycle.
$parts = parse_url($back);
$query = [];
if (!empty($parts['query'])) parse_str($parts['query'], $query);
unset($query['lang']);
$rebuilt = ($parts['scheme'] ?? 'http') . '://' . ($parts['host'] ?? $siteHost) .
    (isset($parts['port']) ? ':' . $parts['port'] : '') .
    ($parts['path'] ?? '/') .
    ($query ? '?' . http_build_query($query) : '');

redirect($rebuilt);
