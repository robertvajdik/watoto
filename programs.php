<?php
require_once __DIR__ . '/includes/functions.php';
$pageTitle = t('nav.programs');
$pageDescription = t('meta.programs.description');
$pageKeywords    = t('meta.programs.keywords');
$programs = $pdo->query("SELECT * FROM programs WHERE is_active = 1 ORDER BY sort_order ASC, id ASC")->fetchAll();
$icons = ['leaf' => '🌱', 'users' => '🤝', 'coins' => '💰', 'book' => '📖', 'wheat' => '🌾', 'home' => '🏡', 'sun' => '☀️', 'water' => '💧', 'child' => '🧒', 'heart' => '💛', 'family' => '👪', 'puzzle' => '🧩'];
require __DIR__ . '/includes/header.php';
?>
<section class="page-hero">
  <div class="container">
    <span class="eyebrow"><?php echo e(t('section.programs')); ?></span>
    <h1><?php echo current_lang() === 'sw' ? 'Programu zetu.' : 'Our programs.'; ?></h1>
    <p class="lead"><?php echo current_lang() === 'sw'
        ? 'Njia halisi tunazotumia kuleta mabadiliko katika maisha ya watoto na familia.'
        : 'The concrete ways we make a difference for children and families.'; ?></p>
  </div>
</section>
<section>
  <div class="container">
    <?php if (!$programs): ?>
      <p><?php echo current_lang() === 'sw' ? 'Hakuna programu bado.' : 'No programs yet.'; ?></p>
    <?php else: ?>
      <div class="program-grid">
        <?php foreach ($programs as $p): ?>
          <div class="program-card">
            <div class="icon"><?php echo $icons[$p['icon']] ?? '🌱'; ?></div>
            <h3><?php echo e($p['title']); ?></h3>
            <p><?php echo e($p['summary']); ?></p>
            <?php if (!empty($p['body'])): ?>
              <p class="program-body"><?php echo nl2br(e($p['body'])); ?></p>
            <?php endif; ?>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
