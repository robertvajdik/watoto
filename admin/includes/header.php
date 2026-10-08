<?php
require_once __DIR__ . '/auth.php';
require_admin();
$adminUser = admin_user();
$currentPage = basename($_SERVER['SCRIPT_NAME'], '.php');
$pageTitle = $pageTitle ?? 'Admin';

// Live counts for sidebar badges
try {
    $unreadCount = (int)$pdo->query("SELECT COUNT(*) FROM messages WHERE is_read = 0")->fetchColumn();
} catch (Exception $e) {
    $unreadCount = 0;
}
?><!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="theme-color" content="#26124A">
<title><?php echo e($pageTitle); ?> — <?php echo e(setting('site_name')); ?> Admin</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Josefin+Sans:wght@400;500;600;700&family=Figtree:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/admin.css">
<link rel="icon" type="image/png" href="<?php echo SITE_URL; ?>/assets/images/favicon.png">
</head>
<body>
<div class="admin-shell">
  <div class="admin-backdrop" data-close-sidebar></div>
  <aside class="admin-sidebar" id="adminSidebar">
    <a class="admin-brand" href="<?php echo SITE_URL; ?>/admin/dashboard.php">
      <img src="<?php echo SITE_URL; ?>/assets/images/watoto_mark.jpg" alt="">
      <div>
        <div class="brand-name">Malezi na Watoto</div>
        <div class="brand-tag">Admin panel</div>
      </div>
    </a>
    <nav>
      <a href="<?php echo SITE_URL; ?>/admin/dashboard.php" class="<?php echo $currentPage === 'dashboard' ? 'active' : ''; ?>"><span class="ico">◈</span> Dashboard</a>
      <a href="<?php echo SITE_URL; ?>/admin/posts.php" class="<?php echo in_array($currentPage, ['posts','post-edit']) ? 'active' : ''; ?>"><span class="ico">✎</span> News / Posts</a>
      <a href="<?php echo SITE_URL; ?>/admin/programs.php" class="<?php echo in_array($currentPage, ['programs','program-edit']) ? 'active' : ''; ?>"><span class="ico">◉</span> Programs</a>
      <a href="<?php echo SITE_URL; ?>/admin/gallery.php" class="<?php echo $currentPage === 'gallery' ? 'active' : ''; ?>"><span class="ico">▤</span> Gallery</a>
      <a href="<?php echo SITE_URL; ?>/admin/members.php" class="<?php echo in_array($currentPage, ['members','member-edit']) ? 'active' : ''; ?>"><span class="ico">♦</span> Members</a>
      <a href="<?php echo SITE_URL; ?>/admin/messages.php" class="<?php echo $currentPage === 'messages' ? 'active' : ''; ?>">
        <span class="ico">✉</span> Messages
        <?php if ($unreadCount > 0): ?><span class="pill"><?php echo $unreadCount; ?></span><?php endif; ?>
      </a>
      <a href="<?php echo SITE_URL; ?>/admin/newsletter.php" class="<?php echo $currentPage === 'newsletter' ? 'active' : ''; ?>"><span class="ico">◇</span> Newsletter</a>
      <a href="<?php echo SITE_URL; ?>/admin/users.php" class="<?php echo $currentPage === 'users' ? 'active' : ''; ?>"><span class="ico">◆</span> Admin Users</a>
      <a href="<?php echo SITE_URL; ?>/admin/settings.php" class="<?php echo $currentPage === 'settings' ? 'active' : ''; ?>"><span class="ico">⚙</span> Settings</a>
    </nav>
    <div class="admin-foot">
      <a href="<?php echo SITE_URL; ?>/index.php" target="_blank">↗ View site</a>
      <a href="<?php echo SITE_URL; ?>/admin/logout.php">← Log out</a>
    </div>
  </aside>
  <main class="admin-main">
    <header class="admin-topbar">
      <button class="admin-menu-btn" aria-label="Menu" onclick="document.body.classList.toggle('sidebar-open')">
        <span></span><span></span><span></span>
      </button>
      <div class="topbar-title">
        <h1><?php echo e($pageTitle); ?></h1>
      </div>
      <div class="user">
        <span class="who">Hello, <strong><?php echo e($adminUser['full_name']); ?></strong></span>
        <a class="avatar" href="<?php echo SITE_URL; ?>/admin/users.php" title="Manage users"><?php echo e(strtoupper(mb_substr($adminUser['full_name'], 0, 1))); ?></a>
      </div>
    </header>
    <?php foreach (['success' => 'success', 'error' => 'error'] as $k => $cls):
        $m = flash($k);
        if ($m): ?>
      <div class="alert alert-<?php echo $cls; ?>"><?php echo e($m); ?></div>
    <?php endif; endforeach; ?>
    <div class="admin-content">
