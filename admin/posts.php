<?php
$pageTitle = 'News / Posts';
require __DIR__ . '/includes/header.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete') {
    if (!csrf_check($_POST['csrf'] ?? '')) { flash('error', 'Session expired.'); redirect('posts.php'); }
    $id = (int)($_POST['id'] ?? 0);
    if ($id) {
        $st = $pdo->prepare("SELECT image FROM posts WHERE id = ?");
        $st->execute([$id]);
        $img = $st->fetchColumn();
        $pdo->prepare("DELETE FROM posts WHERE id = ?")->execute([$id]);
        if ($img && is_file(UPLOAD_DIR . '/' . $img)) @unlink(UPLOAD_DIR . '/' . $img);
        flash('success', 'Post deleted.');
    }
    redirect('posts.php');
}
$posts = $pdo->query("SELECT id, title, slug, status, created_at, image FROM posts ORDER BY created_at DESC")->fetchAll();
?>
<div class="page-actions">
  <div class="toolbar">
    <input type="search" data-filter-input data-filter-target="#postsTable tbody tr" placeholder="Search posts…">
  </div>
  <a href="post-edit.php" class="btn-primary">+ New post</a>
</div>

<div class="card">
  <?php if (!$posts): ?>
    <div class="empty-state">
      <div class="icon">✎</div>
      <h3>No posts yet</h3>
      <p>Create your first news article to share with the community.</p>
      <a href="post-edit.php" class="btn-primary">+ Create the first post</a>
    </div>
  <?php else: ?>
    <div class="table-wrap">
      <table id="postsTable">
        <thead><tr><th></th><th>Title</th><th>Status</th><th>Created</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($posts as $p): ?>
          <tr>
            <td class="col-thumb" data-label="Image"><div class="thumb-mini"><img src="<?php echo e(image_url($p['image'], SITE_URL . '/assets/images/watoto_mark.jpg')); ?>" alt=""></div></td>
            <td data-label="Title"><a href="post-edit.php?id=<?php echo $p['id']; ?>"><?php echo e($p['title']); ?></a>
                <div class="row-sub"><?php echo e($p['slug']); ?></div></td>
            <td data-label="Status"><span class="badge <?php echo $p['status']; ?>"><?php echo e($p['status']); ?></span></td>
            <td data-label="Created"><?php echo e(format_date($p['created_at'])); ?></td>
            <td class="actions">
              <a href="<?php echo SITE_URL; ?>/news-single.php?slug=<?php echo urlencode($p['slug']); ?>" target="_blank">View</a>
              <a href="post-edit.php?id=<?php echo $p['id']; ?>">Edit</a>
              <form method="post" data-confirm="Delete this post?">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="id" value="<?php echo $p['id']; ?>">
                <button class="link-danger" type="submit">Delete</button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
