<?php
// Malezi na Watoto — one-click unsubscribe.
// GET ?token=... — deactivates the matching subscriber.

require_once __DIR__ . '/includes/functions.php';
$pageTitle = t('newsletter.unsub_title', 'Unsubscribe');

$token = trim($_GET['token'] ?? '');
$status = null;
$emailShown = null;

if ($token !== '' && preg_match('/^[a-f0-9]{64}$/', $token)) {
    try {
        $st = $pdo->prepare("SELECT id, email, is_active FROM newsletter_subscribers WHERE unsubscribe_token = ? LIMIT 1");
        $st->execute([$token]);
        $row = $st->fetch();
        if ($row) {
            $emailShown = $row['email'];
            if ((int)$row['is_active'] === 1) {
                $upd = $pdo->prepare("UPDATE newsletter_subscribers SET is_active = 0, unsubscribed_at = NOW() WHERE id = ?");
                $upd->execute([$row['id']]);
                $status = 'done';
            } else {
                $status = 'already';
            }
        } else {
            $status = 'not_found';
        }
    } catch (Exception $ex) {
        $status = 'error';
    }
} else {
    $status = 'invalid';
}

require __DIR__ . '/includes/header.php';
?>
<section class="page-hero">
  <div class="container">
    <span class="eyebrow"><?php echo e(t('newsletter.eyebrow', 'Newsletter')); ?></span>
    <h1><?php echo e($pageTitle); ?></h1>
    <?php if ($status === 'done'): ?>
      <p class="lead"><?php echo e(t('newsletter.unsub_done', 'You have been unsubscribed.')); ?> <?php if ($emailShown): ?><strong><?php echo safe_email($emailShown, $emailShown); ?></strong><?php endif; ?></p>
    <?php elseif ($status === 'already'): ?>
      <p class="lead"><?php echo e(t('newsletter.unsub_already', 'This email is already unsubscribed.')); ?></p>
    <?php else: ?>
      <p class="lead"><?php echo e(t('newsletter.unsub_invalid', 'This unsubscribe link is invalid or has expired.')); ?></p>
    <?php endif; ?>
    <p><a href="<?php echo SITE_URL; ?>/index.php" class="btn"><?php echo e(t('go_home', 'Back to homepage')); ?></a></p>
  </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
