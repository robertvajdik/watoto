<?php
require_once __DIR__ . '/includes/functions.php';
$pageTitle = t('nav.what_we_do');
$pageDescription = t('meta.what_we_do.description');
$pageKeywords    = t('meta.what_we_do.keywords');
require __DIR__ . '/includes/header.php';
?>
<section class="page-hero">
  <div class="container">
    <span class="eyebrow"><?php echo e(t('section.what_we_do')); ?></span>
    <h1><?php echo current_lang() === 'sw' ? 'Kile tunachofanya kila siku.' : 'What we do, every day.'; ?></h1>
    <p class="lead"><?php echo current_lang() === 'sw'
        ? 'Kazi zetu za msingi zinazosaidia watoto wadogo, wazazi, na walezi.'
        : 'The core activities that support young children, parents, and caregivers.'; ?></p>
  </div>
</section>

<section>
  <div class="container-narrow">
    <ul class="checklist">
      <li><?php echo e(t('wwd.item1')); ?></li>
      <li><?php echo e(t('wwd.item2')); ?></li>
      <li><?php echo e(t('wwd.item3')); ?></li>
      <li><?php echo e(t('wwd.item4')); ?></li>
      <li><?php echo e(t('wwd.item5')); ?></li>
      <li><?php echo e(t('wwd.item6')); ?></li>
    </ul>
    <p class="section-actions centered">
      <a href="programs.php" class="btn btn-accent"><?php echo e(t('hero.cta_primary')); ?> →</a>
    </p>
  </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
