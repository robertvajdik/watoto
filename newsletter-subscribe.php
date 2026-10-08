<?php
// Malezi na Watoto — newsletter subscribe handler.
// POST { email, csrf }. Redirects back with a flash message.

require_once __DIR__ . '/includes/functions.php';

$back = $_SERVER['HTTP_REFERER'] ?? (SITE_URL . '/index.php');
// Only allow same-origin redirect back
$host = parse_url(SITE_URL, PHP_URL_HOST);
if ($host && parse_url($back, PHP_URL_HOST) !== $host) {
    $back = SITE_URL . '/index.php';
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect($back);
}

if (!csrf_check($_POST['csrf'] ?? '')) {
    flash('newsletter_error', t('newsletter.error_generic', 'Something went wrong. Please try again.'));
    redirect($back);
}

if (!recaptcha_verify($_POST['recaptcha_token'] ?? '', 'newsletter')) {
    flash('newsletter_error', t('newsletter.error_generic', 'Something went wrong. Please try again.'));
    redirect($back);
}

$email = trim($_POST['email'] ?? '');
if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 190) {
    flash('newsletter_error', t('newsletter.error_email', 'Please enter a valid email address.'));
    redirect($back);
}

try {
    $token = bin2hex(random_bytes(32));
    $lang  = current_lang();
    $ip    = $_SERVER['REMOTE_ADDR'] ?? null;

    $st = $pdo->prepare(
        "INSERT INTO newsletter_subscribers (email, lang, unsubscribe_token, ip_address)
         VALUES (?, ?, ?, ?)
         ON DUPLICATE KEY UPDATE
             is_active       = 1,
             lang            = VALUES(lang),
             unsubscribed_at = NULL"
    );
    $st->execute([$email, $lang, $token, $ip]);

    flash('newsletter_success', t('newsletter.success', 'Thanks! You are on the list.'));
} catch (Exception $ex) {
    error_log('[newsletter-subscribe] ' . $ex->getMessage());
    flash('newsletter_error', t('newsletter.error_generic', 'Something went wrong. Please try again.'));
}

redirect($back . '#newsletter');
