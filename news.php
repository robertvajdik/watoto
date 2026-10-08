<?php
require_once __DIR__ . '/includes/functions.php';
$pageTitle = t('nav.news');
$pageDescription = t('meta.news.description');
$pageKeywords    = t('meta.news.keywords');
$posts = $pdo->query("SELECT id, title, slug, excerpt, body, image, created_at FROM posts WHERE status = 'published' ORDER BY created_at DESC")->fetchAll();
require __DIR__ . '/includes/header.php';
?>
<section class="page-hero">
  <div class="container">
    <span class="eyebrow"><?php echo e(t('section.latest_news')); ?></span>
    <h1><?php echo current_lang() === 'sw' ? 'Habari na hadithi.' : 'News & stories.'; ?></h1>
  </div>
</section>
<section>
  <div class="container">
    <?php if (!$posts): ?>
      <p><?php echo e(t('news.no_posts')); ?></p>
    <?php else: ?>
      <div class="card-grid">
        <?php foreach ($posts as $p): ?>
          <a class="card" href="news-single.php?slug=<?php echo urlencode($p['slug']); ?>">
            <div class="thumb"><img src="<?php echo e(image_url($p['image'], SITE_URL . '/assets/images/watoto_mark.jpg')); ?>" alt="" loading="lazy" decoding="async"></div>
            <div class="body">
              <div class="meta"><?php echo e(format_date($p['created_at'])); ?></div>
              <h3><?php echo e($p['title']); ?></h3>
              <p><?php echo e($p['excerpt'] ?: excerpt($p['body'] ?? '')); ?></p>
              <span class="more"><?php echo e(t('home.read_more')); ?> →</span>
            </div>
          </a>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
