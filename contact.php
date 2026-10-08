<?php
require_once __DIR__ . '/includes/functions.php';
$pageTitle = t('nav.contact');
$pageDescription = t('meta.contact.description');
$pageKeywords    = t('meta.contact.keywords');

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_check($_POST['csrf'] ?? '')) {
        $errors[] = 'Session expired, please try again.';
    } elseif (!recaptcha_verify($_POST['recaptcha_token'] ?? '', 'contact')) {
        $errors[] = current_lang() === 'sw'
            ? 'Uthibitisho wa usalama umeshindwa. Tafadhali jaribu tena.'
            : 'Security check failed. Please try again.';
    } else {
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $subject = trim($_POST['subject'] ?? '');
        $body = trim($_POST['message'] ?? '');

        if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || $body === '') {
            $errors[] = t('contact.error');
        } else {
            $st = $pdo->prepare("INSERT INTO messages (name, email, phone, subject, body, ip_address) VALUES (?, ?, ?, ?, ?, ?)");
            $st->execute([$name, $email, $phone, $subject, $body, $_SERVER['REMOTE_ADDR'] ?? null]);
            $success = true;
            $_POST = [];
        }
    }
}
require __DIR__ . '/includes/header.php';
?>
<section class="page-hero">
  <div class="container">
    <span class="eyebrow"><?php echo e(t('nav.contact')); ?></span>
    <h1><?php echo e(t('section.get_in_touch')); ?></h1>
    <p class="lead"><?php echo current_lang() === 'sw' ? 'Tungependa kusikia kutoka kwako.' : 'We would love to hear from you.'; ?></p>
  </div>
</section>

<section>
  <div class="container contact-grid">
    <div class="contact-info">
      <h3><?php echo e(setting('site_name')); ?></h3>
      <p class="info-lead"><?php echo e(t('footer.about_short')); ?></p>
      <?php $contactEmail2 = trim((string)setting('contact_email_2')); ?>
      <dl>
        <dt><?php echo e(t('contact.address')); ?></dt>
        <dd><?php echo nl2br(e(setting('contact_address'))); ?></dd>
        <dt><?php echo e(t('contact.email_label')); ?></dt>
        <dd>
          <?php echo safe_email(setting('contact_email')); ?>
          <?php if ($contactEmail2 !== ''): ?><br><?php echo safe_email($contactEmail2); ?><?php endif; ?>
        </dd>
        <dt><?php echo e(t('contact.phone_label')); ?></dt>
        <dd><a href="tel:<?php echo e(setting('contact_phone')); ?>"><?php echo e(setting('contact_phone')); ?></a></dd>
      </dl>
    </div>

    <div>
      <?php if ($success): ?>
        <div class="alert alert-success"><?php echo e(t('contact.success')); ?></div>
      <?php endif; ?>
      <?php foreach ($errors as $err): ?>
        <div class="alert alert-error"><?php echo e($err); ?></div>
      <?php endforeach; ?>

      <form method="post" novalidate>
        <?php echo csrf_field(); ?>
        <?php echo recaptcha_field('contact'); ?>
        <div class="form-grid">
          <div>
            <label><?php echo e(t('contact.name')); ?></label>
            <input type="text" name="name" required value="<?php echo e($_POST['name'] ?? ''); ?>">
          </div>
          <div>
            <label><?php echo e(t('contact.email')); ?></label>
            <input type="email" name="email" required value="<?php echo e($_POST['email'] ?? ''); ?>">
          </div>
          <div>
            <label><?php echo e(t('contact.phone')); ?></label>
            <input type="tel" name="phone" value="<?php echo e($_POST['phone'] ?? ''); ?>">
          </div>
          <div>
            <label><?php echo e(t('contact.subject')); ?></label>
            <input type="text" name="subject" value="<?php echo e($_POST['subject'] ?? ''); ?>">
          </div>
          <div class="form-full">
            <label><?php echo e(t('contact.message')); ?></label>
            <textarea name="message" required><?php echo e($_POST['message'] ?? ''); ?></textarea>
          </div>
        </div>
        <button type="submit" class="btn btn-accent form-submit"><?php echo e(t('contact.send')); ?></button>
      </form>
    </div>
  </div>
</section>

<?php
$mapQuery = trim(setting('contact_map_query')) ?: trim(setting('contact_address'));
if ($mapQuery !== ''):
    $mapEmbed = 'https://maps.google.com/maps?q=' . urlencode($mapQuery) . '&hl=' . urlencode(current_lang()) . '&z=14&output=embed';
    $mapLink  = 'https://www.google.com/maps/search/?api=1&query=' . urlencode($mapQuery);
?>
<section class="map-band" aria-label="<?php echo e(current_lang() === 'sw' ? 'Ramani' : 'Map'); ?>">
  <div class="map-wrap">
    <iframe
        src="<?php echo e($mapEmbed); ?>"
        title="<?php echo e(setting('site_name')); ?> — <?php echo e($mapQuery); ?>"
        loading="lazy"
        referrerpolicy="no-referrer-when-downgrade"
        allowfullscreen></iframe>
  </div>
  <div class="container map-footer">
    <a href="<?php echo e($mapLink); ?>" target="_blank" rel="noopener">
      <?php echo e(current_lang() === 'sw' ? 'Fungua kwenye Google Maps' : 'Open in Google Maps'); ?> ↗
    </a>
  </div>
</section>
<?php endif; ?>

<?php require __DIR__ . '/includes/footer.php'; ?>
