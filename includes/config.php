<?php
// Malezi na Watoto — application config

// Database
define('DB_HOST', '127.0.0.1');
define('DB_NAME', 'basketubcz03');
define('DB_USER', 'basketubcz004');
define('DB_PASS', 'VKhpl8Ne');
define('DB_CHARSET', 'utf8mb4');

// Site
// Auto-detect scheme so asset URLs match the request scheme (HTTP vs. HTTPS).
// Handles reverse-proxy TLS termination via X-Forwarded-Proto.
$_wmg_https = (
    (!empty($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off')
    || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && strtolower($_SERVER['HTTP_X_FORWARDED_PROTO']) === 'https')
    || (isset($_SERVER['SERVER_PORT']) && (int)$_SERVER['SERVER_PORT'] === 443)
);
define('SITE_URL', ($_wmg_https ? 'https' : 'http') . '://watoto.basketub.cz'); // no trailing slash
unset($_wmg_https);
define('UPLOAD_DIR', __DIR__ . '/../uploads');
define('UPLOAD_URL', SITE_URL . '/uploads');

// reCAPTCHA v3 — leave empty to disable. Configure at https://www.google.com/recaptcha/admin
define('RECAPTCHA_SITE_KEY',   '');
define('RECAPTCHA_SECRET_KEY', '');
define('RECAPTCHA_MIN_SCORE',  0.5);

// Google Analytics 4 — leave empty to disable. Format: G-XXXXXXXXXX
define('GA_MEASUREMENT_ID', '');

// Session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Errors — turn on during development
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Timezone
date_default_timezone_set('Africa/Nairobi');
