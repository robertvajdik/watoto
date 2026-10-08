<?php
require_once __DIR__ . '/includes/auth.php';
require_admin();
$id = (int)($_GET['id'] ?? 0);
$row = ['id' => 0, 'title' => '', 'slug' => '', 'icon' => 'leaf', 'summary' => '', 'body' => '', 'image' => '', 'sort_order' => 0, 'is_active' => 1];
if ($id) {
    $st = $pdo->prepare("SELECT * FROM programs WHERE id = ?"); $st->execute([$id]);
    $row = $st->fetch(); if (!$row) { flash('error', 'Not found.'); redirect('programs.php'); }
}
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_check($_POST['csrf'] ?? '')) $errors[] = 'Session expired.';
    $title = trim($_POST['title'] ?? '');
    $icon = trim($_POST['icon'] ?? 'leaf');
    $summary = trim($_POST['summary'] ?? '');
    $body = trim($_POST['body'] ?? '');
    $order = (int)($_POST['sort_order'] ?? 0);
    $active = isset($_POST['is_active']) ? 1 : 0;
    if ($title === '') $errors[] = 'Title required.';
    if (!$errors) {
        try {
            $slug = unique_slug($pdo, 'programs', slugify($_POST['slug'] ?: $title), $row['id'] ?: null);
            $image = $row['image']; $up = upload_image('image', 'programs'); if ($up) $image = $up;
            if ($row['id']) {
                $st = $pdo->prepare("UPDATE programs SET title=?, slug=?, icon=?, summary=?, body=?, image=?, sort_order=?, is_active=? WHERE id=?");
                $st->execute([$title, $slug, $icon, $summary, $body, $image, $order, $active, $row['id']]);
                flash('success', 'Program updated.');
            } else {
                $st = $pdo->prepare("INSERT INTO programs (title, slug, icon, summary, body, image, sort_order, is_active) VALUES (?,?,?,?,?,?,?,?)");
                $st->execute([$title, $slug, $icon, $summary, $body, $image, $order, $active]);
                flash('success', 'Program created.');
            }
            redirect('programs.php');
        } catch (Exception $e) { $errors[] = $e->getMessage(); }
    }
    $row = array_merge($row, ['title'=>$title,'icon'=>$icon,'summary'=>$summary,'body'=>$body,'sort_order'=>$order,'is_active'=>$active]);
}
$pageTitle = $row['id'] ? 'Edit program' : 'New program';
require __DIR__ . '/includes/header.php';
$iconChoices = ['child','heart','family','book','puzzle','users','home','leaf','sun','water','coins','wheat'];
?>
<form method="post" enctype="multipart/form-data" class="editor">
  <?php echo csrf_field(); ?>
  <?php foreach ($errors as $e): ?><div class="alert alert-error"><?php echo e($e); ?></div><?php endforeach; ?>
  <div class="editor-grid">
    <div class="editor-main">
      <label>Title</label>
      <input type="text" name="title" required value="<?php echo e($row['title']); ?>">
      <label>Slug — leave empty to auto</label>
      <input type="text" name="slug" value="<?php echo e($row['slug']); ?>">
      <label>Summary (one-line)</label>
      <input type="text" name="summary" maxlength="500" value="<?php echo e($row['summary']); ?>">
      <label>Body</label>
      <textarea name="body" rows="10"><?php echo e($row['body']); ?></textarea>
    </div>
    <div class="editor-side">
      <div class="side-block">
        <label>Icon</label>
        <select name="icon">
          <?php foreach ($iconChoices as $ic): ?>
            <option value="<?php echo $ic; ?>"<?php echo $row['icon']===$ic?' selected':''; ?>><?php echo $ic; ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="side-block">
        <label>Sort order</label>
        <input type="number" name="sort_order" value="<?php echo (int)$row['sort_order']; ?>">
      </div>
      <div class="side-block">
        <label><input type="checkbox" name="is_active" value="1"<?php echo $row['is_active']?' checked':''; ?>> Active (show on site)</label>
      </div>
      <div class="side-block">
        <label>Image</label>
        <?php if (!empty($row['image'])): ?><div class="thumb-preview"><img src="<?php echo e(image_url($row['image'])); ?>" alt=""></div><?php endif; ?>
        <input type="file" name="image" accept="image/*">
      </div>
      <div class="side-actions">
        <button type="submit" class="btn-primary"><?php echo $row['id']?'Update':'Create'; ?></button>
        <a href="programs.php" class="btn-secondary">Cancel</a>
      </div>
    </div>
  </div>
</form>
<?php require __DIR__ . '/includes/footer.php'; ?>
