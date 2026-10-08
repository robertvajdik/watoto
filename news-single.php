<?php
require_once __DIR__ . '/includes/functions.php';
$slug = $_GET['slug'] ?? '';
$st = $pdo->prepare("SELECT * FROM posts WHERE slug = ? AND status = 'published' LIMIT 1");
$st->execute([$slug]);
$post = $st->fetch();

if (!$post) {
    http_response_code(404);
    $pageTitle = t('error.404_title');
    require __DIR__ . '/includes/header.php';
    echo '<section class="page-hero"><div class="container"><h1>' . e(t('error.404_title')) . '</h1><p class="lead">' . e(t('error.404_text')) . '</p><p><a href="' . SITE_URL . '/index.php" class="btn">' . e(t('go_home')) . '</a></p></div></section>';
    require __DIR__ . '/includes/footer.php';
    exit;
}

$pageTitle = $post['title'];
$pageDescription = $post['excerpt'] ?: excerpt($post['body'] ?? '');
$jsonLd = [
    '@context' => 'https://schema.org',
    '@type' => 'NewsArticle',
    'headline' => $post['title'],
    'description' => $pageDescription,
    'datePublished' => date('c', strtotime($post['created_at'])),
    'dateModified' => date('c', strtotime($post['updated_at'] ?: $post['created_at'])),
    'mainEntityOfPage' => SITE_URL . '/news-single.php?slug=' . urlencode($post['slug']),
    'image' => $post['image'] ? image_url($post['image']) : SITE_URL . '/assets/images/watoto_logo.jpeg',
    'author' => [
        '@type' => 'Organization',
        'name' => setting('site_name', 'Malezi na Watoto'),
    ],
    'publisher' => [
        '@type' => 'Organization',
        'name' => setting('site_name', 'Malezi na Watoto'),
        'logo' => [
            '@type' => 'ImageObject',
            'url' => SITE_URL . '/assets/images/watoto_logo.jpeg',
        ],
    ],
];
$hasLightbox = !empty($post['image']);
require __DIR__ . '/includes/header.php';
?>
<article>
  <div class="post-hero container">
    <p><a href="news.php" class="back-link">← <?php echo e(t('news.back')); ?></a></p>
    <span class="eyebrow"><?php echo e(t('section.latest_news')); ?></span>
    <h1><?php echo e($post['title']); ?></h1>
    <div class="meta"><?php echo e(t('news.published_on')); ?> <?php echo e(format_date($post['created_at'])); ?></div>
  </div>
  <?php if ($post['image']): ?>
    <div class="container-narrow">
      <a class="photo-zoom" href="<?php echo e(image_url($post['image'])); ?>"
         data-fancybox="post-<?php echo (int)$post['id']; ?>"
         data-caption="<?php echo e($post['title']); ?>">
        <?php [$pw, $ph] = image_dimensions($post['image']); ?>
        <img src="<?php echo e(image_url($post['image'])); ?>" alt="<?php echo e($post['title']); ?>" class="rounded-hero-img" width="<?php echo (int)$pw; ?>" height="<?php echo (int)$ph; ?>" decoding="async">
      </a>
    </div>
  <?php endif; ?>
  <div class="post-body">
    <?php echo nl2br(e($post['body'])); ?>
  </div>
</article>
<?php require __DIR__ . '/includes/footer.php'; ?>
