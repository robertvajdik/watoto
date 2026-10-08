</main>
<?php
// Newsletter block above the footer
$nlSuccess = flash('newsletter_success');
$nlError   = flash('newsletter_error');
?>
<section class="newsletter-band" id="newsletter">
  <div class="container newsletter-inner">
    <div class="newsletter-copy">
      <span class="eyebrow"><?php echo e(t('newsletter.eyebrow', 'Newsletter')); ?></span>
      <h2><?php echo e(t('newsletter.title', 'Stay in the loop.')); ?></h2>
      <p><?php echo e(t('newsletter.lead', 'Get occasional updates on our programs, parenting sessions, and community stories. No spam — ever.')); ?></p>
    </div>
    <form class="newsletter-form" method="post" action="<?php echo SITE_URL; ?>/newsletter-subscribe.php" novalidate>
      <?php echo csrf_field(); ?>
      <?php echo recaptcha_field('newsletter'); ?>
      <label for="nl-email" class="sr-only"><?php echo e(t('newsletter.email_label', 'Email address')); ?></label>
      <div class="newsletter-row">
        <input id="nl-email" type="email" name="email" required maxlength="190"
               autocomplete="email"
               placeholder="<?php echo e(t('newsletter.placeholder', 'you@example.com')); ?>">
        <button type="submit" class="btn"><?php echo e(t('newsletter.subscribe', 'Subscribe')); ?></button>
      </div>
      <?php if ($nlSuccess): ?>
        <div class="newsletter-msg newsletter-msg-ok" role="status"><?php echo e($nlSuccess); ?></div>
      <?php elseif ($nlError): ?>
        <div class="newsletter-msg newsletter-msg-err" role="alert"><?php echo e($nlError); ?></div>
      <?php endif; ?>
      <p class="newsletter-fine"><?php echo e(t('newsletter.privacy', 'We only use your email to send our newsletter. You can unsubscribe any time.')); ?></p>
    </form>
  </div>
</section>
<footer class="site-footer">
  <div class="container footer-grid">
    <div>
      <div class="footer-brand">
        <img src="<?php echo SITE_URL; ?>/assets/images/watoto_mark.jpg" alt="" width="256" height="256" loading="lazy" decoding="async">
        <span><?php echo e(setting('site_name')); ?></span>
      </div>
      <p><?php echo e(t('footer.about_short')); ?></p>
    </div>
    <div>
      <h4><?php echo e(t('footer.quick_links')); ?></h4>
      <ul class="footer-links">
        <li><a href="<?php echo SITE_URL; ?>/about.php"><?php echo e(t('nav.about')); ?></a></li>
        <li><a href="<?php echo SITE_URL; ?>/what-we-do.php"><?php echo e(t('nav.what_we_do')); ?></a></li>
        <li><a href="<?php echo SITE_URL; ?>/programs.php"><?php echo e(t('nav.programs')); ?></a></li>
        <li><a href="<?php echo SITE_URL; ?>/news.php"><?php echo e(t('nav.news')); ?></a></li>
        <li><a href="<?php echo SITE_URL; ?>/gallery.php"><?php echo e(t('nav.gallery')); ?></a></li>
        <li><a href="<?php echo SITE_URL; ?>/contact.php"><?php echo e(t('nav.contact')); ?></a></li>
      </ul>
    </div>
    <div>
      <h4><?php echo e(t('footer.contact')); ?></h4>
      <ul class="footer-links">
        <?php if (setting('contact_address')): ?><li><?php echo nl2br(e(setting('contact_address'))); ?></li><?php endif; ?>
        <?php if (setting('contact_email')): ?><li><?php echo safe_email(setting('contact_email')); ?></li><?php endif; ?>
        <?php if (setting('contact_phone')): ?><li><a href="tel:<?php echo e(preg_replace('/[^\d+]/', '', setting('contact_phone'))); ?>"><?php echo e(setting('contact_phone')); ?></a></li><?php endif; ?>
      </ul>
    </div>
    <div>
      <h4><?php echo e(t('footer.follow')); ?></h4>
      <div class="socials">
        <?php if (setting('facebook_url')): ?><a href="<?php echo e(setting('facebook_url')); ?>" target="_blank" rel="noopener">Facebook</a><?php endif; ?>
        <?php if (setting('twitter_url')): ?><a href="<?php echo e(setting('twitter_url')); ?>" target="_blank" rel="noopener">Twitter</a><?php endif; ?>
        <?php if (setting('instagram_url')): ?><a href="<?php echo e(setting('instagram_url')); ?>" target="_blank" rel="noopener">Instagram</a><?php endif; ?>
      </div>
    </div>
  </div>
  <div class="footer-bottom container">
    <div><a href="<?php echo SITE_URL; ?>/admin/index.php" class="admin-dot" aria-label="Admin" rel="nofollow">&copy;</a> <?php echo date('Y'); ?> <?php echo e(setting('site_name')); ?>. <?php echo e(t('footer.rights')); ?></div>
    <div class="partner">
      <span><?php echo e(t('footer.partner', 'In partnership with')); ?></span>
      <a href="https://maendeleo.cz/" target="_blank" rel="noopener">Nadace Maendeleo — maendeleo.cz</a>
    </div>
  </div>
</footer>
<script nonce="<?php echo e(csp_nonce()); ?>" src="<?php echo SITE_URL; ?>/assets/js/main.js"></script>
<?php if (!empty($hasGallery) || !empty($hasLightbox)): ?>
<script type="module" nonce="<?php echo e(csp_nonce()); ?>">
import { Fancybox } from 'https://cdn.jsdelivr.net/npm/@fancyapps/ui@5.0/dist/fancybox/fancybox.esm.js';

Fancybox.bind('[data-fancybox]', {
  Hash: false,
  Thumbs: { type: 'classic' },
  Toolbar: {
    display: { left: ['infobar'], middle: [], right: ['slideshow', 'fullscreen', 'thumbs', 'close'] },
  },
  Images: { zoom: true },
});
</script>
<?php endif; ?>
</body>
</html>
