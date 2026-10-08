<?php
$pageTitle = 'Gallery';
require __DIR__ . '/includes/header.php';

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_check($_POST['csrf'] ?? '')) { flash('error', 'Session expired.'); redirect('gallery.php'); }
    $action = $_POST['action'] ?? '';
    if ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        $st = $pdo->prepare("SELECT image FROM gallery WHERE id = ?"); $st->execute([$id]);
        $img = $st->fetchColumn();
        $pdo->prepare("DELETE FROM gallery WHERE id = ?")->execute([$id]);
        if ($img && is_file(UPLOAD_DIR . '/' . $img)) @unlink(UPLOAD_DIR . '/' . $img);
        flash('success', 'Image deleted.');
        redirect('gallery.php');
    } elseif ($action === 'update') {
        $id = (int)($_POST['id'] ?? 0);
        $pdo->prepare("UPDATE gallery SET title=?, caption=?, category=?, sort_order=? WHERE id=?")
            ->execute([trim($_POST['title'] ?? ''), trim($_POST['caption'] ?? ''), trim($_POST['category'] ?? ''), (int)($_POST['sort_order'] ?? 0), $id]);
        flash('success', 'Image updated.');
        redirect('gallery.php');
    } elseif ($action === 'upload') {
        try {
            if (!empty($_FILES['images']['name'][0])) {
                $count = count($_FILES['images']['name']);
                $ins = $pdo->prepare("INSERT INTO gallery (title, caption, image, category) VALUES (?,?,?,?)");
                $saved = 0;
                for ($i = 0; $i < $count; $i++) {
                    if ($_FILES['images']['error'][$i] !== UPLOAD_ERR_OK) continue;
                    $_FILES['single'] = [
                        'name' => $_FILES['images']['name'][$i],
                        'type' => $_FILES['images']['type'][$i],
                        'tmp_name' => $_FILES['images']['tmp_name'][$i],
                        'error' => $_FILES['images']['error'][$i],
                        'size' => $_FILES['images']['size'][$i],
                    ];
                    $path = upload_image('single', 'gallery');
                    if ($path) { $ins->execute([null, null, $path, trim($_POST['category'] ?? '') ?: null]); $saved++; }
                }
                flash('success', $saved . ' image' . ($saved === 1 ? '' : 's') . ' uploaded.');
            }
        } catch (Exception $e) {
            flash('error', $e->getMessage());
        }
        redirect('gallery.php');
    }
}

$rows = $pdo->query("SELECT * FROM gallery ORDER BY sort_order ASC, id DESC")->fetchAll();
$categories = array_values(array_unique(array_filter(array_column($rows, 'category'))));
?>
<div class="card">
  <h2 class="h-flush">Upload images</h2>
  <form method="post" enctype="multipart/form-data">
    <?php echo csrf_field(); ?>
    <input type="hidden" name="action" value="upload">
    <div class="editor-grid">
      <div class="editor-main">
        <label>Select or drop images</label>
        <div class="drop-zone">
          <div class="drop-icon">▤</div>
          <div>Drag &amp; drop image files here, or</div>
          <input type="file" name="images[]" accept="image/*" multiple required>
          <div class="drop-info" data-empty="No files chosen">No files chosen</div>
        </div>
        <div class="hint">JPG · PNG · WEBP · GIF, up to 5 MB each. You can select multiple files at once.</div>
      </div>
      <div class="editor-side">
        <div class="side-block">
          <label>Category (optional)</label>
          <input type="text" name="category" list="cat-list" placeholder="e.g. Trainings">
          <datalist id="cat-list">
            <?php foreach ($categories as $c): ?><option value="<?php echo e($c); ?>"><?php endforeach; ?>
          </datalist>
        </div>
        <div class="side-actions">
          <button class="btn-primary" type="submit">Upload</button>
        </div>
      </div>
    </div>
  </form>
</div>

<div class="card">
  <div class="toolbar">
    <h2 class="h-flush">Gallery (<?php echo count($rows); ?>)</h2>
    <span class="spacer"></span>
    <input type="search" data-filter-input data-filter-target=".admin-gallery-grid .gitem" placeholder="Search by title, caption or category…">
  </div>
  <?php if (!$rows): ?>
    <div class="empty-state">
      <div class="icon">▤</div>
      <h3>No images yet</h3>
      <p>Upload photos above to build your public gallery.</p>
    </div>
  <?php else: ?>
    <div class="admin-gallery-grid">
      <?php foreach ($rows as $g): ?>
        <div class="gitem">
          <div class="gitem-img"><img src="<?php echo e(image_url($g['image'])); ?>" alt="" loading="lazy"></div>
          <form method="post" class="gitem-form">
            <?php echo csrf_field(); ?>
            <input type="hidden" name="action" value="update">
            <input type="hidden" name="id" value="<?php echo $g['id']; ?>">
            <input type="text" name="title" placeholder="Title" value="<?php echo e($g['title']); ?>">
            <input type="text" name="caption" placeholder="Caption" value="<?php echo e($g['caption']); ?>">
            <input type="text" name="category" placeholder="Category" value="<?php echo e($g['category']); ?>">
            <input type="number" name="sort_order" placeholder="Order" value="<?php echo (int)$g['sort_order']; ?>">
            <div class="gitem-actions">
              <button class="btn-secondary" type="submit">Save</button>
            </div>
          </form>
          <form method="post" data-confirm="Delete this image?" class="gitem-del">
            <?php echo csrf_field(); ?>
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="id" value="<?php echo $g['id']; ?>">
            <button class="link-danger" type="submit">Delete</button>
          </form>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
