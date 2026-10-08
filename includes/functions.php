<?php
require_once __DIR__ . '/db.php';

// ── i18n ──
function current_lang() {
    if (isset($_GET['lang']) && in_array($_GET['lang'], ['en', 'sw'], true)) {
        $_SESSION['lang'] = $_GET['lang'];
        setcookie('wmg_lang', $_GET['lang'], time() + 60*60*24*365, '/');
        return $_GET['lang'];
    }
    if (!empty($_SESSION['lang'])) return $_SESSION['lang'];
    if (!empty($_COOKIE['wmg_lang']) && in_array($_COOKIE['wmg_lang'], ['en', 'sw'], true)) {
        $_SESSION['lang'] = $_COOKIE['wmg_lang'];
        return $_SESSION['lang'];
    }
    return 'sw';
}

function t($key, $default = null) {
    static $strings = null;
    if ($strings === null) {
        $lang = current_lang();
        $file = __DIR__ . '/../lang/' . $lang . '.php';
        $strings = is_file($file) ? require $file : [];
    }
    return $strings[$key] ?? ($default ?? $key);
}

function lang_switch_url($target) {
    // Route through /lang-switch.php so the language is persisted in session+cookie
    // and the user is redirected back to a URL without ?lang= in it.
    $back = $_SERVER['REQUEST_URI'] ?? '/';
    return SITE_URL . '/lang-switch.php?lang=' . urlencode($target) . '&back=' . urlencode($back);
}

function e($v) { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }

function csp_nonce() {
    static $nonce = null;
    if ($nonce === null) $nonce = base64_encode(random_bytes(16));
    return $nonce;
}

function is_https() {
    if (!empty($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off') return true;
    if (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https') return true;
    if ((int)($_SERVER['SERVER_PORT'] ?? 0) === 443) return true;
    return false;
}

function send_security_headers() {
    if (headers_sent()) return;
    $nonce = csp_nonce();
    // 'strict-dynamic' + nonce is the modern-browser policy; the host allowlist
    // and 'unsafe-inline' are fallbacks for older browsers that ignore strict-dynamic.
    $csp = "default-src 'self'; "
         . "base-uri 'self'; "
         . "object-src 'none'; "
         . "frame-ancestors 'self'; "
         . "form-action 'self'; "
         . "script-src 'self' 'nonce-{$nonce}' 'strict-dynamic' 'unsafe-inline' https: http:; "
         . "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://cdn.jsdelivr.net; "
         . "font-src 'self' https://fonts.gstatic.com data:; "
         . "img-src 'self' data: blob: https:; "
         . "connect-src 'self' https://www.google-analytics.com https://region1.google-analytics.com https://www.google.com https://www.gstatic.com; "
         . "frame-src https://www.google.com https://maps.google.com https://www.youtube.com https://www.youtube-nocookie.com; "
         . "worker-src 'self' blob:; "
         . "manifest-src 'self'";
    header('Content-Security-Policy: ' . $csp);
    header('X-Frame-Options: SAMEORIGIN');
    header('X-Content-Type-Options: nosniff');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('Permissions-Policy: camera=(), microphone=(), geolocation=(), payment=(), usb=(), interest-cohort=()');
    if (is_https()) {
        header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
    }
}

// Encode an email address as numeric HTML entities so harvesters that scan
// raw HTML for "@" or "mailto:" don't pick it up. Browsers decode entities
// normally, so both the displayed text and the mailto: href still work.
function safe_email($email, $label = null) {
    $email = trim((string)$email);
    if ($email === '') return '';
    $enc = '';
    foreach (str_split($email) as $ch) {
        $enc .= '&#' . ord($ch) . ';';
    }
    $shown = $label !== null ? htmlspecialchars($label, ENT_QUOTES, 'UTF-8') : $enc;
    return '<a href="&#109;&#97;&#105;&#108;&#116;&#111;&#58;' . $enc . '">' . $shown . '</a>';
}

function redirect($url) { header('Location: ' . $url); exit; }

function csrf_token() {
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrf_check($token) {
    return !empty($token) && !empty($_SESSION['csrf']) && hash_equals($_SESSION['csrf'], $token);
}

function csrf_field() {
    return '<input type="hidden" name="csrf" value="' . e(csrf_token()) . '">';
}

function flash($key, $message = null) {
    if ($message === null) {
        if (isset($_SESSION['flash'][$key])) {
            $m = $_SESSION['flash'][$key];
            unset($_SESSION['flash'][$key]);
            return $m;
        }
        return null;
    }
    $_SESSION['flash'][$key] = $message;
}

function slugify($text) {
    $text = preg_replace('~[^\pL\d]+~u', '-', $text);
    $text = iconv('utf-8', 'us-ascii//TRANSLIT//IGNORE', $text);
    $text = preg_replace('~[^-\w]+~', '', $text);
    $text = trim($text, '-');
    $text = preg_replace('~-+~', '-', $text);
    $text = strtolower($text);
    return $text ?: 'item-' . time();
}

function unique_slug(PDO $pdo, $table, $slug, $ignoreId = null) {
    $base = $slug; $i = 1;
    while (true) {
        $sql = "SELECT id FROM `$table` WHERE slug = ?" . ($ignoreId ? " AND id <> ?" : "");
        $st = $pdo->prepare($sql);
        $params = [$slug];
        if ($ignoreId) $params[] = $ignoreId;
        $st->execute($params);
        if (!$st->fetch()) return $slug;
        $slug = $base . '-' . (++$i);
    }
}

function setting($key, $default = '') {
    static $cache = null;
    global $pdo;
    if ($cache === null) {
        $cache = [];
        try {
            foreach ($pdo->query("SELECT setting_key, setting_value FROM settings") as $r) {
                $cache[$r['setting_key']] = $r['setting_value'];
            }
        } catch (Exception $e) { /* table may not exist yet */ }
    }
    return $cache[$key] ?? $default;
}

function nav_active($page) {
    $current = basename($_SERVER['SCRIPT_NAME'], '.php');
    return $current === $page ? ' class="active"' : '';
}

function upload_image($fileField, $subdir) {
    if (empty($_FILES[$fileField]) || $_FILES[$fileField]['error'] === UPLOAD_ERR_NO_FILE) return null;
    if ($_FILES[$fileField]['error'] !== UPLOAD_ERR_OK) throw new Exception('Upload failed.');

    $allowed = ['jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'png' => 'image/png', 'webp' => 'image/webp', 'gif' => 'image/gif'];
    $ext = strtolower(pathinfo($_FILES[$fileField]['name'], PATHINFO_EXTENSION));
    if (!isset($allowed[$ext])) throw new Exception('Only JPG, PNG, WEBP, GIF allowed.');

    $mime = mime_content_type($_FILES[$fileField]['tmp_name']);
    if (!in_array($mime, $allowed, true)) throw new Exception('Invalid image type.');

    $max = 5 * 1024 * 1024;
    if ($_FILES[$fileField]['size'] > $max) throw new Exception('Image too large (max 5 MB).');

    $dir = UPLOAD_DIR . '/' . $subdir;
    if (!is_dir($dir)) mkdir($dir, 0775, true);

    $name = bin2hex(random_bytes(8)) . '.' . $ext;
    $dest = $dir . '/' . $name;
    if (!move_uploaded_file($_FILES[$fileField]['tmp_name'], $dest)) throw new Exception('Could not save upload.');

    return $subdir . '/' . $name;
}

function image_url($path, $fallback = null) {
    if (!$path) return $fallback;
    return UPLOAD_URL . '/' . ltrim($path, '/');
}

// Returns [width, height] for an uploaded image. Falls back to 1600x1200 when
// the file is missing or unreadable. Used to seed PhotoSwipe with slide dims.
function image_dimensions($path) {
    if (!$path) return [1600, 1200];
    $abs = UPLOAD_DIR . '/' . ltrim($path, '/');
    if (is_file($abs) && ($size = @getimagesize($abs))) {
        return [(int)$size[0], (int)$size[1]];
    }
    return [1600, 1200];
}

function excerpt($text, $len = 160) {
    $text = trim(strip_tags($text));
    if (mb_strlen($text) <= $len) return $text;
    return mb_substr($text, 0, $len) . '…';
}

function format_date($dt, $fmt = 'M j, Y') {
    if (!$dt) return '';
    return date($fmt, strtotime($dt));
}

// ── reCAPTCHA v3 ──
// Prefer constants in includes/config.php; fall back to DB settings for backwards compatibility.
function recaptcha_site_key() {
    if (defined('RECAPTCHA_SITE_KEY') && RECAPTCHA_SITE_KEY !== '') return RECAPTCHA_SITE_KEY;
    return (string)setting('recaptcha_site_key');
}
function recaptcha_secret_key() {
    if (defined('RECAPTCHA_SECRET_KEY') && RECAPTCHA_SECRET_KEY !== '') return RECAPTCHA_SECRET_KEY;
    return (string)setting('recaptcha_secret_key');
}
function recaptcha_min_score() {
    if (defined('RECAPTCHA_MIN_SCORE')) return (float)RECAPTCHA_MIN_SCORE;
    $s = (float)setting('recaptcha_min_score');
    return $s > 0 ? $s : 0.5;
}

function ga_measurement_id() {
    if (defined('GA_MEASUREMENT_ID') && GA_MEASUREMENT_ID !== '') return GA_MEASUREMENT_ID;
    return trim((string)setting('ga_measurement_id'));
}
function recaptcha_enabled() {
    return trim(recaptcha_site_key()) !== '' && trim(recaptcha_secret_key()) !== '';
}

function recaptcha_field($action) {
    if (!recaptcha_enabled()) return '';
    return '<input type="hidden" name="recaptcha_token" value="" data-recaptcha-action="' . e($action) . '">';
}

// Verifies the submitted reCAPTCHA token. Returns true if verification passes
// (or if reCAPTCHA is not configured — fails open on missing keys, closed on bad tokens).
function recaptcha_verify($token, $expectedAction = null, $minScore = null) {
    if (!recaptcha_enabled()) return true;
    if ($minScore === null) $minScore = recaptcha_min_score();
    $token = trim((string)$token);
    if ($token === '') return false;

    $postFields = http_build_query([
        'secret'   => recaptcha_secret_key(),
        'response' => $token,
        'remoteip' => $_SERVER['REMOTE_ADDR'] ?? '',
    ]);

    if (function_exists('curl_init')) {
        $ch = curl_init('https://www.google.com/recaptcha/api/siteverify');
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $postFields,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 8,
        ]);
        $body = curl_exec($ch);
        curl_close($ch);
    } else {
        $ctx = stream_context_create(['http' => [
            'method'  => 'POST',
            'header'  => "Content-Type: application/x-www-form-urlencoded\r\n",
            'content' => $postFields,
            'timeout' => 8,
        ]]);
        $body = @file_get_contents('https://www.google.com/recaptcha/api/siteverify', false, $ctx);
    }

    if (!$body) return false;
    $data = json_decode($body, true);
    if (empty($data['success'])) return false;
    if ($expectedAction !== null && !empty($data['action']) && $data['action'] !== $expectedAction) return false;
    return isset($data['score']) && $data['score'] >= $minScore;
}
