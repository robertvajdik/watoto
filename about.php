<?php
require_once __DIR__ . '/includes/functions.php';
$pageTitle = t('nav.about');
$pageDescription = t('meta.about.description');
$pageKeywords    = t('meta.about.keywords');
$members = $pdo->query("SELECT * FROM members WHERE is_active = 1 ORDER BY sort_order ASC, id ASC")->fetchAll();
$hasLightbox = (bool)array_filter($members, fn($m) => !empty($m['photo']));
require __DIR__ . '/includes/header.php';
?>
<section class="page-hero">
  <div class="container">
    <span class="eyebrow"><?php echo e(t('section.who_we_are')); ?></span>
    <h1><?php echo e(setting('site_name')); ?></h1>
    <p class="lead"><?php echo e(setting('site_tagline')); ?></p>
  </div>
</section>

<section>
  <div class="container two-col">
    <div>
      <span class="eyebrow"><?php echo e(t('section.who_we_are')); ?></span>
      <h2><?php echo current_lang() === 'sw' ? 'Pamoja na wazazi, kwa ajili ya watoto.' : 'Together with parents, for every child.'; ?></h2>
      <p><?php echo e(setting('about_text')); ?></p>
      <p><?php echo current_lang() === 'sw'
          ? 'Tunafanya kazi kwa karibu na wazazi, walezi, na jamii kukuza malezi chanya, makuzi ya awali ya mtoto, na mazingira yanayosaidia ambapo kila mtoto anaweza kujifunza, kukua, na kustawi.'
          : 'We work closely with parents, caregivers, and communities to promote positive parenting, early childhood development, and supportive environments where every child can learn, grow, and thrive.'; ?></p>
      <p><?php echo current_lang() === 'sw'
          ? 'Kazi yetu inalenga hasa watoto katika miaka yao ya awali, tukiwasaidia wazazi na walezi kwa maarifa, ujuzi wa vitendo, na fursa zinazoimarisha makuzi ya watoto na ustawi wa familia.'
          : 'Our work focuses particularly on children in their early years, supporting parents and caregivers with knowledge, practical skills, and opportunities that strengthen children’s development and family well-being.'; ?></p>
      <p><?php echo current_lang() === 'sw'
          ? 'Kupitia ushiriki wa jamii, vikao vya malezi, usomaji wa vitabu kwa mazungumzo (Dialogic Booksharing), shughuli za kujifunza, na ushirikiano na wadau wa ndani, tunaunda mazingira ambapo watoto wanahamasishwa kuchunguza, kuwasiliana, kujifunza, na kukua kwa kujiamini.'
          : 'Through community engagement, parenting sessions, Dialogic Booksharing, learning activities, and collaboration with local stakeholders, we create spaces where children are encouraged to explore, communicate, learn, and develop with confidence.'; ?></p>
    </div>
    <div>
      <picture>
        <source type="image/webp" srcset="<?php echo SITE_URL; ?>/assets/images/fotos/full/wakulima02.webp">
        <img src="<?php echo SITE_URL; ?>/assets/images/fotos/full/wakulima02.jpg" alt="<?php echo current_lang() === 'sw' ? 'Wanachama wa Malezi na Watoto' : 'Members of Malezi na Watoto'; ?>" class="rounded-hero-img" width="1440" height="1920" decoding="async">
      </picture>
    </div>
  </div>
</section>

<section class="alt">
  <div class="container">
    <div class="pillars pillars-vm">
      <div class="pillar pillar-white">
        <h3><?php echo e(t('section.vision')); ?></h3>
        <p class="pillar-body-lg"><?php echo e(setting('vision_text')); ?></p>
      </div>
      <div class="pillar pillar-white">
        <h3><?php echo e(t('section.mission')); ?></h3>
        <p class="pillar-body-lg"><?php echo e(setting('mission_text')); ?></p>
      </div>
    </div>
  </div>
</section>

<?php if ($members): ?>
<section>
  <div class="container">
    <div class="section-head">
      <span class="eyebrow"><?php echo e(t('section.team')); ?></span>
      <h2><?php echo current_lang() === 'sw' ? 'Watu walio nyuma ya kazi yetu.' : 'The people behind our work.'; ?></h2>
    </div>
    <div class="team-grid">
      <?php foreach ($members as $m):
          $photoUrl = image_url($m['photo'], SITE_URL . '/assets/images/watoto_mark.jpg');
          $hasRealPhoto = !empty($m['photo']); ?>
        <div class="team-card">
          <div class="photo">
            <?php if ($hasRealPhoto): ?>
              <a class="photo-zoom" href="<?php echo e($photoUrl); ?>"
                 data-fancybox="member-<?php echo (int)$m['id']; ?>"
                 data-caption="<?php echo e($m['full_name']); ?>"
                 aria-label="<?php echo e($m['full_name']); ?>">
                <img src="<?php echo e($photoUrl); ?>" alt="<?php echo e($m['full_name']); ?>" loading="lazy" decoding="async">
              </a>
            <?php else: ?>
              <img src="<?php echo e($photoUrl); ?>" alt="<?php echo e($m['full_name']); ?>" loading="lazy" decoding="async">
            <?php endif; ?>
          </div>
          <h3><?php echo e($m['full_name']); ?></h3>
          <div class="role"><?php echo e($m['role_title']); ?></div>
          <?php if ($m['bio']): ?><p class="bio-line"><?php echo e($m['bio']); ?></p><?php endif; ?>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<?php require __DIR__ . '/includes/footer.php'; ?>
