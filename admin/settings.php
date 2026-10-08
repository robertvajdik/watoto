<?php
$pageTitle = 'Site Settings';

// ── Sitemap generation (must run BEFORE header include so we can redirect) ──
$sitemapPath = __DIR__ . '/../sitemap.xml';
require_once __DIR__ . '/includes/auth.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'generate_sitemap') {
    if (!csrf_check($_POST['csrf'] ?? '')) { flash('error', 'Session expired.'); redirect('settings.php'); }
    try {
        ob_start();
        include __DIR__ . '/../sitemap.php';
        $xml = ob_get_clean();
        // sitemap.php sends a Content-Type header; strip any preceding headers/newlines from output.
        $xml = ltrim($xml);
        if (@file_put_contents($sitemapPath, $xml) === false) {
            throw new RuntimeException('Could not write sitemap.xml. Check filesystem permissions on the site root.');
        }
        flash('success', 'sitemap.xml regenerated (' . number_format(strlen($xml)) . ' bytes).');
    } catch (Exception $ex) {
        flash('error', 'Sitemap generation failed: ' . $ex->getMessage());
    }
    redirect('settings.php');
}

require __DIR__ . '/includes/header.php';

$fields = [
    'site_name'       => 'Site name',
    'site_tagline'    => 'Site tagline',
    'contact_email'   => 'Contact email (primary)',
    'contact_email_2' => 'Contact email (secondary)',
    'contact_phone'   => 'Contact phone',
    'contact_address' => 'Contact address (multi-line, first line is company/venue)',
    'contact_map_query' => 'Map location (Google Maps search query, e.g. "Mwanza, Tanzania")',
    'about_text'      => 'About text',
    'vision_text'     => 'Vision statement',
    'mission_text'    => 'Mission statement',
    'facebook_url'    => 'Facebook URL',
    'twitter_url'     => 'Twitter URL',
    'instagram_url'   => 'Instagram URL',
];

$integrationFields = [
    'recaptcha_site_key'   => 'reCAPTCHA v3 site key',
    'recaptcha_secret_key' => 'reCAPTCHA v3 secret key',
    'recaptcha_min_score'  => 'reCAPTCHA min. score (0.0 – 1.0)',
    'ga_measurement_id'    => 'Google Analytics 4 measurement ID (G-XXXXXXXXXX)',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') !== 'generate_sitemap') {
    if (!csrf_check($_POST['csrf'] ?? '')) { flash('error','Session expired.'); redirect('settings.php'); }
    $up = $pdo->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
    // Only save keys that were actually posted, so the two forms on this page (main + integrations)
    // don't wipe each other's fields when saved independently.
    foreach ($fields as $k => $_) {
        if (!array_key_exists($k, $_POST)) continue;
        $up->execute([$k, $_POST[$k]]);
    }
    foreach ($integrationFields as $k => $_) {
        if (!array_key_exists($k, $_POST)) continue;
        $up->execute([$k, trim((string)$_POST[$k])]);
    }
    flash('success', 'Settings saved.');
    redirect('settings.php');
}

$values = [];
foreach ($pdo->query("SELECT setting_key, setting_value FROM settings") as $r) $values[$r['setting_key']] = $r['setting_value'];

$sitemapExists = is_file($sitemapPath);
$sitemapMtime  = $sitemapExists ? date('M j, Y H:i', filemtime($sitemapPath)) : null;
$sitemapSize   = $sitemapExists ? number_format(filesize($sitemapPath)) : null;
?>
<form method="post" class="card">
  <?php echo csrf_field(); ?>
  <div class="grid-2">
    <?php $textareas = ['about_text','vision_text','mission_text','contact_address']; ?>
    <?php foreach ($fields as $k => $label): ?>
      <div<?php echo in_array($k,$textareas,true)?' class="col-full"':''; ?>>
        <label><?php echo e($label); ?></label>
        <?php if (in_array($k, $textareas, true)): ?>
          <textarea name="<?php echo e($k); ?>" rows="3"><?php echo e($values[$k] ?? ''); ?></textarea>
        <?php else: ?>
          <input type="text" name="<?php echo e($k); ?>" value="<?php echo e($values[$k] ?? ''); ?>">
        <?php endif; ?>
      </div>
    <?php endforeach; ?>
  </div>
  <div class="side-actions mt-md"><button class="btn-primary" type="submit">Save settings</button></div>
</form>

<div class="card">
  <h2>Integrations</h2>
  <p class="muted">Optional third-party keys. Leave empty to disable. Constants in <code>includes/config.php</code> take precedence over these values if set.</p>
  <?php
    $recaptchaConst = defined('RECAPTCHA_SITE_KEY') && RECAPTCHA_SITE_KEY !== '';
    $gaConst        = defined('GA_MEASUREMENT_ID') && GA_MEASUREMENT_ID !== '';
  ?>
  <form method="post">
    <?php echo csrf_field(); ?>
    <div class="grid-2">
      <div>
        <label>reCAPTCHA v3 — site key</label>
        <input type="text" name="recaptcha_site_key" value="<?php echo e($values['recaptcha_site_key'] ?? ''); ?>" placeholder="6Lc...">
        <?php if ($recaptchaConst): ?><p class="muted" style="font-size:12px;margin:4px 0 0;">Overridden by <code>RECAPTCHA_SITE_KEY</code> constant.</p><?php endif; ?>
      </div>
      <div>
        <label>reCAPTCHA v3 — secret key</label>
        <input type="password" name="recaptcha_secret_key" value="<?php echo e($values['recaptcha_secret_key'] ?? ''); ?>" autocomplete="new-password" placeholder="6Lc...">
      </div>
      <div>
        <label>Min. score (0.0 – 1.0)</label>
        <input type="text" name="recaptcha_min_score" value="<?php echo e($values['recaptcha_min_score'] ?? ''); ?>" placeholder="0.5">
      </div>
      <div>
        <label>Google Analytics 4 measurement ID</label>
        <input type="text" name="ga_measurement_id" value="<?php echo e($values['ga_measurement_id'] ?? ''); ?>" placeholder="G-XXXXXXXXXX">
        <?php if ($gaConst): ?><p class="muted" style="font-size:12px;margin:4px 0 0;">Overridden by <code>GA_MEASUREMENT_ID</code> constant.</p><?php endif; ?>
      </div>
    </div>
    <div class="side-actions mt-md"><button class="btn-primary" type="submit">Save integrations</button></div>
  </form>
</div>

<div class="card">
  <h2>Sitemap</h2>
  <p class="muted">Regenerate <code>sitemap.xml</code> at the site root. Search engines will fetch it faster than the on-the-fly version.</p>
  <?php if ($sitemapExists): ?>
    <p style="margin:12px 0;"><strong>Status:</strong> exists · <?php echo e($sitemapSize); ?> bytes · last generated <?php echo e($sitemapMtime); ?></p>
  <?php else: ?>
    <p style="margin:12px 0;"><strong>Status:</strong> not generated yet (served dynamically via <code>sitemap.php</code>).</p>
  <?php endif; ?>
  <form method="post" style="display:flex;gap:10px;flex-wrap:wrap;align-items:center;">
    <?php echo csrf_field(); ?>
    <input type="hidden" name="action" value="generate_sitemap">
    <button type="submit" class="btn-primary">Regenerate sitemap.xml</button>
    <a class="btn-secondary" href="<?php echo SITE_URL; ?>/sitemap.xml" target="_blank" rel="noopener">View sitemap →</a>
  </form>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
