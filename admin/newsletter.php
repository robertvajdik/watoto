<?php
// CSV export must run BEFORE any HTML output.
require_once __DIR__ . '/includes/auth.php';
require_admin();

// Detect whether the newsletter_subscribers table has been created.
$tableReady = false;
try {
    $pdo->query("SELECT 1 FROM newsletter_subscribers LIMIT 1");
    $tableReady = true;
} catch (PDOException $e) {
    $tableReady = false;
}

// One-click install: run newsletter.sql from admin (requires CSRF + admin auth already checked above).
$installError = null;
if (!$tableReady && $_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'install') {
    if (!csrf_check($_POST['csrf'] ?? '')) {
        $installError = 'Session expired. Reload and try again.';
    } else {
        try {
            $sqlFile = __DIR__ . '/../newsletter.sql';
            if (!is_file($sqlFile)) {
                throw new RuntimeException('newsletter.sql not found in project root.');
            }
            $sql = file_get_contents($sqlFile);
            $pdo->exec($sql);
            flash('success', 'Newsletter table installed.');
            redirect('newsletter.php');
        } catch (Exception $ex) {
            $installError = 'Install failed: ' . $ex->getMessage();
        }
    }
}

if ($tableReady && isset($_GET['export']) && $_GET['export'] === 'csv') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="newsletter-subscribers-' . date('Y-m-d') . '.csv"');
    $out = fopen('php://output', 'w');
    fputcsv($out, ['id', 'email', 'lang', 'is_active', 'subscribed_at', 'unsubscribed_at', 'ip_address']);
    $q = $pdo->query("SELECT id, email, lang, is_active, subscribed_at, unsubscribed_at, ip_address FROM newsletter_subscribers ORDER BY subscribed_at DESC");
    foreach ($q as $row) fputcsv($out, $row);
    fclose($out);
    exit;
}

$pageTitle = 'Newsletter';
require __DIR__ . '/includes/header.php';

if ($tableReady && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_check($_POST['csrf'] ?? '')) { flash('error', 'Session expired.'); redirect('newsletter.php'); }
    $action = $_POST['action'] ?? '';
    $id = (int)($_POST['id'] ?? 0);
    if ($action === 'delete' && $id) {
        $pdo->prepare("DELETE FROM newsletter_subscribers WHERE id = ?")->execute([$id]);
        flash('success', 'Subscriber removed.');
    } elseif ($action === 'reactivate' && $id) {
        $pdo->prepare("UPDATE newsletter_subscribers SET is_active = 1, unsubscribed_at = NULL WHERE id = ?")->execute([$id]);
        flash('success', 'Subscriber reactivated.');
    } elseif ($action === 'unsubscribe' && $id) {
        $pdo->prepare("UPDATE newsletter_subscribers SET is_active = 0, unsubscribed_at = NOW() WHERE id = ?")->execute([$id]);
        flash('success', 'Subscriber unsubscribed.');
    }
    redirect('newsletter.php');
}

if (!$tableReady): ?>
  <div class="empty-state">
    <div class="icon">⚙</div>
    <h3>Newsletter table not installed</h3>
    <p>The <code>newsletter_subscribers</code> table doesn&rsquo;t exist yet in this database.</p>
    <?php if ($installError): ?>
      <div class="alert alert-error" style="display:inline-block;text-align:left;max-width:520px;"><?php echo e($installError); ?></div>
    <?php endif; ?>
    <form method="post" style="margin:18px 0;">
      <?php echo csrf_field(); ?>
      <input type="hidden" name="action" value="install">
      <button type="submit" class="btn-primary">Install newsletter table now</button>
    </form>
    <p class="muted">Or import <code>newsletter.sql</code> manually:</p>
    <pre style="display:inline-block;text-align:left;background:#f6f4ef;padding:12px 16px;border-radius:8px;font-size:13px;">mysql -u USER -p DBNAME &lt; newsletter.sql</pre>
  </div>
<?php
  require __DIR__ . '/includes/footer.php';
  exit;
endif;

$total  = (int)$pdo->query("SELECT COUNT(*) FROM newsletter_subscribers")->fetchColumn();
$active = (int)$pdo->query("SELECT COUNT(*) FROM newsletter_subscribers WHERE is_active = 1")->fetchColumn();
$rows = $pdo->query("SELECT id, email, lang, is_active, subscribed_at, unsubscribed_at
                     FROM newsletter_subscribers
                     ORDER BY subscribed_at DESC")->fetchAll();
?>
<div class="page-actions">
  <div class="muted"><strong><?php echo $active; ?></strong> active · <?php echo $total; ?> total</div>
  <div class="spacer"></div>
  <a class="btn-secondary" href="newsletter.php?export=csv">⬇ Export CSV</a>
</div>

<?php if (!$rows): ?>
  <div class="empty-state">
    <div class="icon">✉</div>
    <h3>No subscribers yet</h3>
    <p>People who sign up through the newsletter form will appear here.</p>
  </div>
<?php else: ?>
  <div class="card list-panel">
    <table>
      <thead>
        <tr>
          <th>Email</th>
          <th>Lang</th>
          <th>Status</th>
          <th>Subscribed</th>
          <th class="actions">Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($rows as $r): ?>
          <tr>
            <td><a href="mailto:<?php echo e($r['email']); ?>"><?php echo e($r['email']); ?></a></td>
            <td><?php echo e(strtoupper($r['lang'])); ?></td>
            <td>
              <?php if ((int)$r['is_active'] === 1): ?>
                <span class="badge published">Active</span>
              <?php else: ?>
                <span class="badge draft">Unsubscribed</span>
              <?php endif; ?>
            </td>
            <td><?php echo e(format_date($r['subscribed_at'], 'M j, Y')); ?></td>
            <td class="actions">
              <?php if ((int)$r['is_active'] === 1): ?>
                <form method="post" class="inline-form" onsubmit="return confirm('Unsubscribe this email?');">
                  <?php echo csrf_field(); ?>
                  <input type="hidden" name="id" value="<?php echo (int)$r['id']; ?>">
                  <input type="hidden" name="action" value="unsubscribe">
                  <button type="submit" class="link-danger">Unsubscribe</button>
                </form>
              <?php else: ?>
                <form method="post" class="inline-form">
                  <?php echo csrf_field(); ?>
                  <input type="hidden" name="id" value="<?php echo (int)$r['id']; ?>">
                  <input type="hidden" name="action" value="reactivate">
                  <button type="submit" class="link-danger">Reactivate</button>
                </form>
              <?php endif; ?>
              <form method="post" class="inline-form" onsubmit="return confirm('Permanently delete this subscriber?');">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="id" value="<?php echo (int)$r['id']; ?>">
                <input type="hidden" name="action" value="delete">
                <button type="submit" class="link-danger">Delete</button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
<?php endif; ?>

<?php require __DIR__ . '/includes/footer.php'; ?>
