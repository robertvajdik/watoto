<?php
require_once __DIR__ . '/config.php';

try {
    $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);
} catch (PDOException $e) {
    // Fallback rendered before the main stylesheet loads.
    // Uses an inline <style> block (single stylesheet, not inline style attributes).
    $installUrl = SITE_URL . '/install.php';
    $msg = htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
    echo <<<HTML
<!doctype html>
<html lang="en"><head><meta charset="utf-8"><title>Setup required</title>
<style>
  body { margin:0; font-family: system-ui, -apple-system, sans-serif; background:#f5efe4; color:#26221D; }
  .box { max-width: 560px; margin: 80px auto; background: #fff; border-radius: 16px; padding: 40px; box-shadow: 0 20px 60px rgba(38,34,29,.12); }
  h2 { margin: 0 0 12px; font-size: 22px; color: #8A5E17; }
  p { line-height: 1.6; }
  code { background: #f6e7c8; padding: 2px 6px; border-radius: 4px; font-size: 90%; }
  a { color: #8A5E17; font-weight: 600; }
  .err { color: #7f1d1d; font-size: 13px; margin-top: 20px; padding-top: 16px; border-top: 1px solid #eee; }
</style></head><body>
<div class="box">
  <h2>Setup required</h2>
  <p>The database connection failed. Verify your credentials in <code>includes/config.php</code>, make sure MySQL is running, and then run the installer.</p>
  <p><a href="{$installUrl}">Run install.php →</a></p>
  <p class="err">{$msg}</p>
</div></body></html>
HTML;
    exit;
}
