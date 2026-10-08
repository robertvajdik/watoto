<?php
$pageTitle = 'Team Members';
require __DIR__ . '/includes/header.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete') {
    if (!csrf_check($_POST['csrf'] ?? '')) { flash('error', 'Session expired.'); redirect('members.php'); }
    $id = (int)($_POST['id'] ?? 0);
    $st = $pdo->prepare("SELECT photo FROM members WHERE id = ?"); $st->execute([$id]);
    $photo = $st->fetchColumn();
    $pdo->prepare("DELETE FROM members WHERE id = ?")->execute([$id]);
    if ($photo && is_file(UPLOAD_DIR . '/' . $photo)) @unlink(UPLOAD_DIR . '/' . $photo);
    flash('success', 'Member removed.');
    redirect('members.php');
}
$rows = $pdo->query("SELECT * FROM members ORDER BY sort_order ASC, id ASC")->fetchAll();
?>
<div class="page-actions">
  <div class="toolbar">
    <input type="search" data-filter-input data-filter-target="#membersTable tbody tr" placeholder="Search members…">
  </div>
  <a href="member-edit.php" class="btn-primary">+ New member</a>
</div>
<div class="card">
  <?php if (!$rows): ?>
    <div class="empty-state">
      <div class="icon">♦</div>
      <h3>No team members yet</h3>
      <p>Add the people behind Malezi na Watoto so visitors can meet the team.</p>
      <a href="member-edit.php" class="btn-primary">+ Add a member</a>
    </div>
  <?php else: ?>
    <div class="table-wrap">
      <table id="membersTable">
        <thead><tr><th></th><th>Name</th><th>Role</th><th>Order</th><th>Active</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($rows as $r): ?>
          <tr>
            <td class="col-thumb" data-label="Photo"><div class="thumb-mini"><img src="<?php echo e(image_url($r['photo'], SITE_URL . '/assets/images/watoto_mark.jpg')); ?>" alt=""></div></td>
            <td data-label="Name"><a href="member-edit.php?id=<?php echo $r['id']; ?>"><?php echo e($r['full_name']); ?></a></td>
            <td class="muted" data-label="Role"><?php echo e($r['role_title']); ?></td>
            <td data-label="Order"><?php echo (int)$r['sort_order']; ?></td>
            <td data-label="Active"><?php echo $r['is_active'] ? '<span class="badge published">yes</span>' : '<span class="badge draft">no</span>'; ?></td>
            <td class="actions">
              <a href="member-edit.php?id=<?php echo $r['id']; ?>">Edit</a>
              <form method="post" data-confirm="Delete this member?">
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
