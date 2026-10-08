<?php
$pageTitle = 'Dashboard';
require __DIR__ . '/includes/header.php';

$counts = [
    'posts' => (int)$pdo->query("SELECT COUNT(*) FROM posts")->fetchColumn(),
    'programs' => (int)$pdo->query("SELECT COUNT(*) FROM programs")->fetchColumn(),
    'gallery' => (int)$pdo->query("SELECT COUNT(*) FROM gallery")->fetchColumn(),
    'members' => (int)$pdo->query("SELECT COUNT(*) FROM members")->fetchColumn(),
    'messages' => (int)$pdo->query("SELECT COUNT(*) FROM messages")->fetchColumn(),
    'unread' => (int)$pdo->query("SELECT COUNT(*) FROM messages WHERE is_read = 0")->fetchColumn(),
];
$recentMessages = $pdo->query("SELECT id, name, email, subject, is_read, created_at FROM messages ORDER BY created_at DESC LIMIT 5")->fetchAll();
$recentPosts = $pdo->query("SELECT id, title, status, created_at FROM posts ORDER BY created_at DESC LIMIT 5")->fetchAll();
?>
<div class="dash-welcome card">
  <div>
    <h2 class="h-flush">Welcome back, <?php echo e($adminUser['full_name']); ?> 👋</h2>
    <p class="muted">Here is a quick look at your site.</p>
  </div>
  <div class="dash-quick">
    <a href="post-edit.php" class="btn-primary">+ New post</a>
    <a href="gallery.php" class="btn-secondary">Upload photos</a>
  </div>
</div>

<div class="stat-cards">
  <a class="stat-card" href="posts.php"><span class="num"><?php echo $counts['posts']; ?></span><span class="lbl">News posts</span></a>
  <a class="stat-card" href="programs.php"><span class="num"><?php echo $counts['programs']; ?></span><span class="lbl">Programs</span></a>
  <a class="stat-card" href="gallery.php"><span class="num"><?php echo $counts['gallery']; ?></span><span class="lbl">Gallery images</span></a>
  <a class="stat-card" href="members.php"><span class="num"><?php echo $counts['members']; ?></span><span class="lbl">Team members</span></a>
  <a class="stat-card" href="messages.php">
    <span class="num"><?php echo $counts['unread']; ?><span class="num-sub">/ <?php echo $counts['messages']; ?></span></span>
    <span class="lbl">Unread messages</span>
  </a>
</div>

<div class="grid-2">
  <div class="card">
    <h2 class="h-flush">Recent messages</h2>
    <?php if (!$recentMessages): ?>
      <p class="muted">No messages yet.</p>
    <?php else: ?>
      <div class="table-wrap">
        <table>
          <thead><tr><th>From</th><th>Subject</th><th>When</th></tr></thead>
          <tbody>
          <?php foreach ($recentMessages as $m): ?>
            <tr class="<?php echo $m['is_read'] ? '' : 'unread'; ?>">
              <td data-label="From"><a href="messages.php?id=<?php echo $m['id']; ?>"><?php echo e($m['name']); ?></a></td>
              <td data-label="Subject"><?php echo e($m['subject'] ?: '—'); ?></td>
              <td data-label="When"><?php echo e(format_date($m['created_at'], 'M j')); ?></td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>
  <div class="card">
    <h2 class="h-flush">Latest posts</h2>
    <?php if (!$recentPosts): ?>
      <p class="muted">No posts yet.</p>
    <?php else: ?>
      <div class="table-wrap">
        <table>
          <thead><tr><th>Title</th><th>Status</th><th>When</th></tr></thead>
          <tbody>
          <?php foreach ($recentPosts as $p): ?>
            <tr>
              <td data-label="Title"><a href="post-edit.php?id=<?php echo $p['id']; ?>"><?php echo e($p['title']); ?></a></td>
              <td data-label="Status"><span class="badge <?php echo $p['status']; ?>"><?php echo e($p['status']); ?></span></td>
              <td data-label="When"><?php echo e(format_date($p['created_at'], 'M j')); ?></td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
