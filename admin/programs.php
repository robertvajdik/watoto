<?php
$pageTitle = 'Programs';
require __DIR__ . '/includes/header.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete') {
    if (!csrf_check($_POST['csrf'] ?? '')) { flash('error', 'Session expired.'); redirect('programs.php'); }
    $id = (int)($_POST['id'] ?? 0);
    if ($id) {
        $pdo->prepare("DELETE FROM programs WHERE id = ?")->execute([$id]);
        flash('success', 'Program deleted.');
    }
    redirect('programs.php');
}
$rows = $pdo->query("SELECT id, title, slug, icon, summary, sort_order, is_active FROM programs ORDER BY sort_order ASC, id ASC")->fetchAll();
?>
<div class="page-actions">
  <div class="toolbar">
    <input type="search" data-filter-input data-filter-target="#programsTable tbody tr" placeholder="Search programs…">
  </div>
  <a href="program-edit.php" class="btn-primary">+ New program</a>
</div>
<div class="card">
  <?php if (!$rows): ?>
    <div class="empty-state">
      <div class="icon">◉</div>
      <h3>No programs yet</h3>
      <p>Set up the programs you offer to children, families, and communities.</p>
      <a href="program-edit.php" class="btn-primary">+ Add a program</a>
    </div>
  <?php else: ?>
    <div class="table-wrap">
      <table id="programsTable">
        <thead><tr><th>Order</th><th>Title</th><th>Summary</th><th>Active</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($rows as $r): ?>
          <tr>
            <td data-label="Order"><?php echo (int)$r['sort_order']; ?></td>
            <td data-label="Title"><a href="program-edit.php?id=<?php echo $r['id']; ?>"><?php echo e($r['title']); ?></a></td>
            <td class="muted" data-label="Summary"><?php echo e(excerpt($r['summary'], 90)); ?></td>
            <td data-label="Active"><?php echo $r['is_active'] ? '<span class="badge published">yes</span>' : '<span class="badge draft">no</span>'; ?></td>
            <td class="actions">
              <a href="program-edit.php?id=<?php echo $r['id']; ?>">Edit</a>
              <form method="post" data-confirm="Delete this program?">
                <?php echo csrf_field(); ?><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?php echo $r['id']; ?>">
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
