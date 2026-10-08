<?php
require_once __DIR__ . '/includes/functions.php';
http_response_code(404);
$pageTitle = t('error.404_title');
require __DIR__ . '/includes/header.php';
?>
<section class="page-hero hero-404">
  <div class="container">
    <h1><?php echo e(t('error.404_title')); ?></h1>
    <p class="lead"><?php echo e(t('error.404_text')); ?></p>
    <p><a href="<?php echo SITE_URL; ?>/index.php" class="btn"><?php echo e(t('go_home')); ?></a></p>
  </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
