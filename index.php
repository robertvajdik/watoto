<?php
require_once __DIR__ . '/includes/functions.php';
$pageTitle = t('nav.home');
$pageDescription = t('meta.home.description');
$pageKeywords    = t('meta.home.keywords');
$bodyClass       = 'page-home';

$posts = $pdo->query("SELECT id, title, slug, excerpt, body, image, created_at FROM posts WHERE status = 'published' ORDER BY created_at DESC LIMIT 3")->fetchAll();
$programs = $pdo->query("SELECT id, title, slug, icon, summary FROM programs WHERE is_active = 1 ORDER BY sort_order ASC LIMIT 6")->fetchAll();
$galleryPreview = $pdo->query("SELECT id, image, caption FROM gallery ORDER BY sort_order ASC, id DESC LIMIT 8")->fetchAll();

// Static photo set used when the DB gallery is empty. Dimensions match the
// files written by assets/fotos/_convert.py (full/ copies).
$staticFotos = [
    ['file' => 'wakulima01', 'w' => 1440, 'h' => 1920],
    ['file' => 'wakulima02', 'w' => 1440, 'h' => 1920],
    ['file' => 'wakulima03', 'w' => 1440, 'h' => 1920],
    ['file' => 'wakulima04', 'w' => 1080, 'h' => 1436],
    ['file' => 'wakulima05', 'w' => 810,  'h' => 1080],
    ['file' => 'wakulima06', 'w' => 1080, 'h' => 810],
    ['file' => 'wakulima07', 'w' => 810,  'h' => 1080],
    ['file' => 'wakulima08', 'w' => 1062, 'h' => 1600],
    ['file' => 'wakulima09', 'w' => 1600, 'h' => 1062],
];
$hasGallery = true;

$icons = [
    'leaf' => '🌱', 'users' => '🤝', 'coins' => '💰', 'book' => '📖',
    'wheat' => '🌾', 'home' => '🏡', 'sun' => '☀️', 'water' => '💧',
    'child' => '🧒', 'heart' => '💛', 'family' => '👪', 'puzzle' => '🧩',
];

require __DIR__ . '/includes/header.php';
?>

<section class="hero">
  <div class="container hero-inner">
    <div>
      <span class="eyebrow"><?php echo e(t('hero.eyebrow')); ?></span>
      <h1><?php echo e(t('hero.title')); ?></h1>
      <p class="lead"><?php echo e(t('hero.subtitle')); ?></p>
      <div class="hero-actions">
        <a href="programs.php" class="btn btn-accent"><?php echo e(t('hero.cta_primary')); ?></a>
        <a href="about.php" class="btn btn-outline"><?php echo e(t('hero.cta_secondary')); ?></a>
      </div>
    </div>
    <div class="hero-visual">
      <div class="frame">
        <picture>
          <source type="image/webp" srcset="<?php echo SITE_URL; ?>/assets/images/fotos/full/wakulima01.webp">
          <img src="<?php echo SITE_URL; ?>/assets/images/fotos/full/wakulima01.jpg" alt="<?php echo current_lang() === 'sw' ? 'Wazazi na watoto wakijifunza pamoja' : 'Parents and children learning together'; ?>" width="1440" height="1920" fetchpriority="high" decoding="async">
        </picture>
      </div>
      <div class="tag">
        <span class="dot"></span>
        <span><?php echo current_lang() === 'sw' ? 'Malezi chanya' : 'Positive parenting'; ?></span>
      </div>
    </div>
  </div>
</section>

<section>
  <div class="container two-col">
    <div>
      <span class="eyebrow"><?php echo e(t('section.who_we_are')); ?></span>
      <h2><?php echo current_lang() === 'sw' ? 'Kila mtoto apate nafasi ya kustawi.' : 'Helping every child reach their full potential.'; ?></h2>
      <p><?php echo e(setting('about_text', t('home.about_lead'))); ?></p>
      <a href="about.php" class="btn"><?php echo e(t('home.learn_more')); ?></a>
    </div>
    <div class="pillars">
      <div class="pillar">
        <h3><?php echo e(t('section.vision')); ?></h3>
        <p><?php echo e(setting('vision_text')); ?></p>
      </div>
      <div class="pillar">
        <h3><?php echo e(t('section.mission')); ?></h3>
        <p><?php echo e(setting('mission_text')); ?></p>
      </div>
      <div class="pillar pillar-full-cream">
        <h3><?php echo e(t('section.what_we_do')); ?></h3>
        <p><?php echo e(t('wwd.item1')) . ' ' . e(t('wwd.item3')) . ' ' . e(t('wwd.item5')); ?></p>
      </div>
    </div>
  </div>
</section>

<section class="alt">
  <div class="container">
    <div class="section-head">
      <span class="eyebrow"><?php echo e(t('section.programs')); ?></span>
      <h2><?php echo current_lang() === 'sw' ? 'Jinsi tunavyosaidia watoto na familia.' : 'How we support children and families.'; ?></h2>
    </div>
    <div class="program-grid">
      <?php foreach ($programs as $p): ?>
        <div class="program-card">
          <div class="icon"><?php echo $icons[$p['icon']] ?? '🌱'; ?></div>
          <h3><?php echo e($p['title']); ?></h3>
          <p><?php echo e($p['summary']); ?></p>
        </div>
      <?php endforeach; ?>
    </div>
    <p class="section-actions"><a href="programs.php" class="btn"><?php echo e(t('home.view_all')); ?> →</a></p>
  </div>
</section>

<section class="dark">
  <div class="container">
    <div class="section-head">
      <span class="eyebrow eyebrow-accent"><?php echo e(t('section.our_impact')); ?></span>
      <h2><?php echo current_lang() === 'sw' ? 'Athari inayoonekana katika jamii zetu.' : 'Real impact in the communities we serve.'; ?></h2>
    </div>
    <div class="stats-row">
      <div class="stat"><div class="num">1,200+</div><div class="label"><?php echo e(t('stats.farmers')); ?></div></div>
      <div class="stat"><div class="num">35</div><div class="label"><?php echo e(t('stats.communities')); ?></div></div>
      <div class="stat"><div class="num"><?php echo count($programs); ?></div><div class="label"><?php echo e(t('stats.programs')); ?></div></div>
    </div>
  </div>
</section>

<section>
  <div class="container">
    <div class="section-head">
      <span class="eyebrow"><?php echo e(t('section.gallery')); ?></span>
      <h2><?php echo current_lang() === 'sw' ? 'Muhtasari wa kazi yetu.' : 'A glimpse of our work.'; ?></h2>
    </div>
    <div class="gallery-grid">
      <?php if ($galleryPreview): ?>
        <?php foreach (array_slice($galleryPreview, 0, 8) as $g): ?>
          <a href="<?php echo e(image_url($g['image'])); ?>"
             class="gallery-item"
             data-fancybox="home"
             <?php if ($g['caption']): ?>data-caption="<?php echo e($g['caption']); ?>"<?php endif; ?>>
            <img src="<?php echo e(image_url($g['image'])); ?>" alt="<?php echo e($g['caption']); ?>" loading="lazy" decoding="async">
            <?php if ($g['caption']): ?><div class="caption"><?php echo e($g['caption']); ?></div><?php endif; ?>
          </a>
        <?php endforeach; ?>
      <?php else: ?>
        <?php foreach ($staticFotos as $f):
            $full  = SITE_URL . '/assets/images/fotos/full/'  . $f['file'];
            $thumb = SITE_URL . '/assets/images/fotos/thumb/' . $f['file']; ?>
          <a href="<?php echo e($full . '.jpg'); ?>"
             class="gallery-item"
             data-fancybox="home">
            <picture>
              <source type="image/webp" srcset="<?php echo e($thumb . '.webp'); ?>">
              <img src="<?php echo e($thumb . '.jpg'); ?>" alt="<?php echo current_lang() === 'sw' ? 'Wanachama wa Malezi na Watoto' : 'Members of Malezi na Watoto'; ?>" loading="lazy" decoding="async">
            </picture>
          </a>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
    <p class="section-actions"><a href="gallery.php" class="btn btn-outline"><?php echo e(t('home.view_all')); ?> →</a></p>
  </div>
</section>

<section class="join-band">
  <div class="container">
    <span class="eyebrow"><?php echo current_lang() === 'sw' ? 'Jiunge nasi' : 'Get involved'; ?></span>
    <h2><?php echo current_lang() === 'sw' ? 'Watoto wanapostawi, jamii nzima hustawi.' : 'When children thrive, whole communities thrive.'; ?></h2>
    <p class="lead"><?php echo current_lang() === 'sw' ? 'Shirikiana nasi kama mzazi, mlezi, mshirika au mfadhili — na tuchangie kujenga mustakabali bora kwa watoto wa Tanzania.' : 'Partner with us as a parent, caregiver, collaborator or supporter — and help build a brighter future for Tanzania’s children.'; ?></p>
    <div class="hero-actions">
      <a href="contact.php" class="btn"><?php echo current_lang() === 'sw' ? 'Wasiliana nasi' : 'Get in touch'; ?></a>
      <a href="what-we-do.php" class="btn btn-outline"><?php echo e(t('home.learn_more')); ?></a>
    </div>
  </div>
</section>

<section class="alt">
  <div class="container">
    <div class="section-head">
      <span class="eyebrow"><?php echo e(t('section.latest_news')); ?></span>
      <h2><?php echo current_lang() === 'sw' ? 'Habari kutoka kwenye jamii.' : 'News from the community.'; ?></h2>
    </div>
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
    <?php if ($posts): ?>
      <p class="section-actions"><a href="news.php" class="btn btn-outline"><?php echo e(t('home.view_all')); ?> →</a></p>
    <?php endif; ?>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
