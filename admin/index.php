<?php
require_once __DIR__ . '/includes/auth.php';

if (admin_user()) redirect(SITE_URL . '/admin/dashboard.php');

$error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_check($_POST['csrf'] ?? '')) {
        $error = 'Session expired, please try again.';
    } elseif (admin_login(trim($_POST['username'] ?? ''), $_POST['password'] ?? '')) {
        redirect(SITE_URL . '/admin/dashboard.php');
    } else {
        $error = 'Invalid username or password.';
    }
}
?><!doctype html>
<html lang="en">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Sign in — <?php echo e(setting('site_name')); ?> Admin</title>
<link href="https://fonts.googleapis.com/css2?family=Josefin+Sans:wght@400;500;600;700&family=Figtree:wght@400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/admin.css">
</head>
<body class="login-body">
<div class="login-card">
  <img src="<?php echo SITE_URL; ?>/assets/images/watoto_mark.jpg" alt="" class="login-logo">
  <h1>Welcome back</h1>
  <p class="lead">Sign in to manage <?php echo e(setting('site_name')); ?>.</p>
  <?php if ($error): ?><div class="alert alert-error"><?php echo e($error); ?></div><?php endif; ?>
  <form method="post" autocomplete="off">
    <?php echo csrf_field(); ?>
    <label>Username or email</label>
    <input type="text" name="username" required autofocus>
    <label>Password</label>
    <input type="password" name="password" required>
    <button type="submit" class="btn-primary">Sign in</button>
  </form>
  <p class="foot"><a href="<?php echo SITE_URL; ?>/index.php">← Back to site</a></p>
</div>
</body>
</html>
