<?php
require_once __DIR__ . '/includes/auth.php';
require_admin();

$id = (int)($_GET['id'] ?? 0);
$post = ['id' => 0, 'title' => '', 'slug' => '', 'excerpt' => '', 'body' => '', 'image' => '', 'status' => 'published'];
if ($id) {
    $st = $pdo->prepare("SELECT * FROM posts WHERE id = ?");
    $st->execute([$id]);
    $post = $st->fetch() ?: null;
    if (!$post) { flash('error', 'Post not found.'); redirect('posts.php'); }
}
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_check($_POST['csrf'] ?? '')) { $errors[] = 'Session expired.'; }
    $title = trim($_POST['title'] ?? '');
    $slug = trim($_POST['slug'] ?? '');
    $excerpt = trim($_POST['excerpt'] ?? '');
    $body = trim($_POST['body'] ?? '');
    $status = $_POST['status'] ?? 'published';
    if (!in_array($status, ['draft','published'], true)) $status = 'draft';
    if ($title === '') $errors[] = 'Title is required.';
    if ($body === '') $errors[] = 'Body is required.';
    if (!$errors) {
        try {
            $slug = $slug ?: slugify($title);
            $slug = unique_slug($pdo, 'posts', slugify($slug), $post['id'] ?: null);
            $image = $post['image'];
            $uploaded = upload_image('image', 'posts');
            if ($uploaded) $image = $uploaded;
            if ($post['id']) {
                $st = $pdo->prepare("UPDATE posts SET title=?, slug=?, excerpt=?, body=?, image=?, status=? WHERE id=?");
                $st->execute([$title, $slug, $excerpt, $body, $image, $status, $post['id']]);
                flash('success', 'Post updated.');
            } else {
                $st = $pdo->prepare("INSERT INTO posts (title, slug, excerpt, body, image, status, author_id) VALUES (?,?,?,?,?,?,?)");
                $st->execute([$title, $slug, $excerpt, $body, $image, $status, admin_user()['id']]);
                flash('success', 'Post created.');
            }
            redirect('posts.php');
        } catch (Exception $e) {
            $errors[] = $e->getMessage();
        }
    }
    $post = array_merge($post, compact('title','slug','excerpt','body','status'));
}

$pageTitle = $post['id'] ? 'Edit post' : 'New post';
require __DIR__ . '/includes/header.php';
?>
<form method="post" enctype="multipart/form-data" class="editor">
  <?php echo csrf_field(); ?>
  <?php foreach ($errors as $e): ?><div class="alert alert-error"><?php echo e($e); ?></div><?php endforeach; ?>

  <div class="editor-grid">
    <div class="editor-main">
      <label>Title</label>
      <input type="text" name="title" required value="<?php echo e($post['title']); ?>">
      <label>Slug (URL) — leave empty to auto-generate</label>
      <input type="text" name="slug" value="<?php echo e($post['slug']); ?>">
      <label>Excerpt</label>
      <textarea name="excerpt" rows="2" maxlength="500"><?php echo e($post['excerpt']); ?></textarea>
      <label>Body</label>
      <textarea name="body" rows="18" required><?php echo e($post['body']); ?></textarea>
    </div>
    <div class="editor-side">
      <div class="side-block">
        <label>Status</label>
        <select name="status">
          <option value="published"<?php echo $post['status']==='published'?' selected':''; ?>>Published</option>
          <option value="draft"<?php echo $post['status']==='draft'?' selected':''; ?>>Draft</option>
        </select>
      </div>
      <div class="side-block">
        <label>Featured image</label>
        <?php if (!empty($post['image'])): ?>
          <div class="thumb-preview"><img src="<?php echo e(image_url($post['image'])); ?>" alt=""></div>
        <?php endif; ?>
        <input type="file" name="image" accept="image/*">
        <div class="hint">JPG / PNG / WEBP, max 5 MB.</div>
      </div>
      <div class="side-actions">
        <button type="submit" class="btn-primary"><?php echo $post['id'] ? 'Update' : 'Create'; ?></button>
        <a href="posts.php" class="btn-secondary">Cancel</a>
      </div>
    </div>
  </div>
</form>
<?php require __DIR__ . '/includes/footer.php'; ?>
