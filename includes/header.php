<?php
require_once __DIR__ . '/functions.php';
if (!headers_sent()) {
    // no-cache (not no-store) still revalidates but allows bfcache / instant back-forward.
    header('Cache-Control: no-cache, must-revalidate, max-age=0');
    header('Pragma: no-cache');
    header('Expires: 0');
}
send_security_headers();
$cspNonce = csp_nonce();
$lang = current_lang();
$altLang = $lang === 'sw' ? 'en' : 'sw';
if (!headers_sent()) header('Content-Language: ' . $lang);
$pageTitle = $pageTitle ?? setting('site_name', 'Malezi na Watoto');
$siteName = setting('site_name', 'Malezi na Watoto');
?><!doctype html>
<html lang="<?php echo e($lang); ?>">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="theme-color" content="#52279B">
<title><?php echo e($pageTitle . ' — ' . $siteName); ?></title>
<?php
$metaDescription = $pageDescription ?? t('meta.site.description', setting('site_tagline'));
$metaKeywords    = $pageKeywords    ?? t('meta.site.keywords', '');
?>
<meta name="description" content="<?php echo e($metaDescription); ?>">
<?php if ($metaKeywords !== ''): ?>
<meta name="keywords" content="<?php echo e($metaKeywords); ?>">
<?php endif; ?>
<meta name="author" content="Robert Vajdik">
<meta name="reply-to" content="robert.vajdik@gmail.com">
<meta name="robots" content="index, follow">
<link rel="author" href="mailto:robert.vajdik@gmail.com">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Josefin+Sans:wght@300;400;500;600;700&family=Bodoni+Moda:ital,opsz,wght@0,6..96,400..900;1,6..96,400..900&family=Figtree:ital,wght@0,300..900;1,300..900&display=swap" rel="stylesheet">
<link rel="icon" type="image/png" href="<?php echo SITE_URL; ?>/assets/images/favicon.png">
<link rel="apple-touch-icon" href="<?php echo SITE_URL; ?>/assets/images/apple-touch-icon.png">
<link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/style.css">
<?php if (!empty($hasGallery) || !empty($hasLightbox)): ?>
<link rel="preconnect" href="https://cdn.jsdelivr.net" crossorigin>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fancyapps/ui@5.0/dist/fancybox/fancybox.css">
<?php endif; ?>
<?php if (($bodyClass ?? '') === 'page-home'): ?>
<link rel="preload" as="image" href="<?php echo SITE_URL; ?>/assets/images/fotos/full/wakulima01.webp" type="image/webp" fetchpriority="high">
<?php endif; ?>

<?php
// ── SEO: canonical + hreflang alternates ──
$canonicalPath = strtok($_SERVER['REQUEST_URI'] ?? '/', '?');
// Strip .php so canonical matches the pretty URL served via .htaccess.
$canonicalPath = preg_replace('~\.php$~', '', $canonicalPath);
if ($canonicalPath === '/index') $canonicalPath = '/';
$canonical = SITE_URL . $canonicalPath;
?>
<link rel="canonical" href="<?php echo e($canonical); ?>">
<link rel="alternate" hreflang="en" href="<?php echo e($canonical . '?lang=en'); ?>">
<link rel="alternate" hreflang="sw" href="<?php echo e($canonical . '?lang=sw'); ?>">
<link rel="alternate" hreflang="x-default" href="<?php echo e($canonical); ?>">

<?php
// ── Open Graph + Twitter ──
// Pages can override $pageOgImage with a page-specific absolute URL (and
// optional $pageOgImageW / $pageOgImageH). Default is a wide landscape foto
// which previews well on Facebook / LinkedIn / X.
$ogImage = $pageOgImage ?? SITE_URL . '/assets/images/fotos/full/wakulima09.jpg';
$ogImageW = $pageOgImageW ?? 1600;
$ogImageH = $pageOgImageH ?? 1062;
$ogImageAlt = $pageOgImageAlt ?? $pageTitle;
$ogDescription = $pageDescription ?? setting('site_tagline');
$ogType = $pageOgType ?? 'website';
$ogLocale = $lang === 'sw' ? 'sw_TZ' : 'en_US';
$ogLocaleAlt = $lang === 'sw' ? 'en_US' : 'sw_TZ';
?>
<meta property="og:site_name" content="<?php echo e($siteName); ?>">
<meta property="og:title" content="<?php echo e($pageTitle); ?>">
<meta property="og:description" content="<?php echo e($ogDescription); ?>">
<meta property="article:author" content="Robert Vajdik">
<meta property="og:image" content="<?php echo e($ogImage); ?>">
<meta property="og:image:width" content="<?php echo (int)$ogImageW; ?>">
<meta property="og:image:height" content="<?php echo (int)$ogImageH; ?>">
<meta property="og:image:alt" content="<?php echo e($ogImageAlt); ?>">
<meta property="og:url" content="<?php echo e($canonical); ?>">
<meta property="og:type" content="<?php echo e($ogType); ?>">
<meta property="og:locale" content="<?php echo e($ogLocale); ?>">
<meta property="og:locale:alternate" content="<?php echo e($ogLocaleAlt); ?>">
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="<?php echo e($pageTitle); ?>">
<meta name="twitter:description" content="<?php echo e($ogDescription); ?>">
<meta name="twitter:image" content="<?php echo e($ogImage); ?>">
<meta name="twitter:image:alt" content="<?php echo e($ogImageAlt); ?>">

<?php
// ── JSON-LD: Organization + WebSite (rendered on every page) ──
$orgJsonLd = [
    '@context' => 'https://schema.org',
    '@type' => 'NGO',
    'name' => $siteName,
    'url' => SITE_URL,
    'logo' => SITE_URL . '/assets/images/watoto_logo.jpeg',
    'description' => setting('site_tagline'),
    'address' => [
        '@type' => 'PostalAddress',
        'streetAddress' => setting('contact_address'),
        'addressLocality' => 'Mwanza',
        'addressRegion' => 'Mwanza',
        'addressCountry' => 'TZ',
    ],
    'contactPoint' => [
        '@type' => 'ContactPoint',
        'contactType' => 'customer support',
        'email' => setting('contact_email'),
        'telephone' => setting('contact_phone'),
        'availableLanguage' => ['English', 'Swahili'],
    ],
    'sameAs' => array_values(array_filter([
        setting('facebook_url'),
        setting('twitter_url'),
        setting('instagram_url'),
        'https://maendeleo.cz/',
    ])),
];
$websiteJsonLd = [
    '@context' => 'https://schema.org',
    '@type' => 'WebSite',
    'name' => $siteName,
    'url' => SITE_URL,
    'inLanguage' => [$lang === 'sw' ? 'sw' : 'en'],
];
?>
<script type="application/ld+json" nonce="<?php echo e($cspNonce); ?>"><?php echo json_encode($orgJsonLd, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE); ?></script>
<script type="application/ld+json" nonce="<?php echo e($cspNonce); ?>"><?php echo json_encode($websiteJsonLd, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE); ?></script>
<?php if (recaptcha_enabled()): $rcKey = recaptcha_site_key(); ?>
<script nonce="<?php echo e($cspNonce); ?>">window.WMG_RECAPTCHA_KEY = <?php echo json_encode($rcKey); ?>;</script>
<script nonce="<?php echo e($cspNonce); ?>" src="https://www.google.com/recaptcha/api.js?render=<?php echo urlencode($rcKey); ?>" async defer></script>
<?php endif; ?>
<?php $gaId = ga_measurement_id(); if ($gaId !== ''): ?>
<!-- Google Analytics 4 -->
<script nonce="<?php echo e($cspNonce); ?>" async src="https://www.googletagmanager.com/gtag/js?id=<?php echo urlencode($gaId); ?>"></script>
<script nonce="<?php echo e($cspNonce); ?>">
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());
  gtag('config', <?php echo json_encode($gaId); ?>, { anonymize_ip: true });
</script>
<?php endif; ?>
<?php if (!empty($jsonLd) && is_array($jsonLd)): ?>
<script type="application/ld+json" nonce="<?php echo e($cspNonce); ?>"><?php echo json_encode($jsonLd, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE); ?></script>
<?php endif; ?>
</head>
<body<?php echo !empty($bodyClass) ? ' class="' . e($bodyClass) . '"' : ''; ?>>
<a class="skip-link" href="#main"><?php echo e(t('nav.skip', 'Skip to content')); ?></a>
<header class="site-header">
  <div class="container header-inner">
    <a href="<?php echo SITE_URL; ?>/index.php" class="brand">
      <img src="<?php echo SITE_URL; ?>/assets/images/watoto_mark.jpg" alt="<?php echo e($siteName); ?>" width="256" height="256" decoding="async">
      <div class="brand-text">
        <span class="brand-name"><?php echo e($siteName); ?></span>
        <span class="brand-tag"><?php echo e(setting('site_tagline')); ?></span>
      </div>
    </a>
    <button class="nav-toggle" type="button" id="navToggle"
            aria-controls="siteNav" aria-expanded="false"
            aria-label="<?php echo e(t('nav.open_menu', 'Open menu')); ?>">
      <span></span><span></span><span></span>
    </button>
    <nav class="site-nav" id="siteNav" aria-label="<?php echo e(t('nav.primary', 'Primary')); ?>">
      <ul>
        <li<?php echo nav_active('index'); ?>><a href="<?php echo SITE_URL; ?>/index.php"><?php echo e(t('nav.home')); ?></a></li>
        <li<?php echo nav_active('about'); ?>><a href="<?php echo SITE_URL; ?>/about.php"><?php echo e(t('nav.about')); ?></a></li>
        <li<?php echo nav_active('what-we-do'); ?>><a href="<?php echo SITE_URL; ?>/what-we-do.php"><?php echo e(t('nav.what_we_do')); ?></a></li>
        <li<?php echo nav_active('programs'); ?>><a href="<?php echo SITE_URL; ?>/programs.php"><?php echo e(t('nav.programs')); ?></a></li>
        <li<?php echo nav_active('news'); ?>><a href="<?php echo SITE_URL; ?>/news.php"><?php echo e(t('nav.news')); ?></a></li>
        <li<?php echo nav_active('gallery'); ?>><a href="<?php echo SITE_URL; ?>/gallery.php"><?php echo e(t('nav.gallery')); ?></a></li>
        <li<?php echo nav_active('contact'); ?>><a href="<?php echo SITE_URL; ?>/contact.php"><?php echo e(t('nav.contact')); ?></a></li>
      </ul>
      <div class="lang-switch" aria-label="<?php echo e(t('nav.language')); ?>">
        <a href="<?php echo e(lang_switch_url('en')); ?>" class="<?php echo $lang === 'en' ? 'active' : ''; ?>" hreflang="en">
          <svg class="flag" viewBox="0 0 60 30" aria-hidden="true"><clipPath id="uk-t"><path d="M30,15h30v15zv-15h-30zh-30v15zv-15h30z"/></clipPath><path fill="#012169" d="M0,0h60v30H0z"/><path stroke="#fff" stroke-width="6" d="M0,0l60,30m0-30L0,30"/><path stroke="#C8102E" stroke-width="4" clip-path="url(#uk-t)" d="M0,0l60,30m0-30L0,30"/><path stroke="#fff" stroke-width="10" d="M30,0v30M0,15h60"/><path stroke="#C8102E" stroke-width="6" d="M30,0v30M0,15h60"/></svg>
          <span>EN</span>
        </a>
        <a href="<?php echo e(lang_switch_url('sw')); ?>" class="<?php echo $lang === 'sw' ? 'active' : ''; ?>" hreflang="sw">
          <svg class="flag" viewBox="0 0 9 6" aria-hidden="true"><path fill="#1eb53a" d="M0,0h9v6H0z"/><polygon fill="#00a3dd" points="0,6 9,0 9,6"/><polygon fill="#fcd116" points="0,6 1,6 9,1 9,0 8,0 0,5"/><polygon fill="#000" points="0,6 0.6,6 9,1.4 9,0.6 8.4,0 0,5.4"/></svg>
          <span>SW</span>
        </a>
      </div>
    </nav>
  </div>
</header>
<main class="site-main" id="main" tabindex="-1">
