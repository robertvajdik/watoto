<?php
require_once __DIR__ . '/includes/auth.php';
require_admin();
$id = (int)($_GET['id'] ?? 0);
$row = ['id'=>0,'full_name'=>'','role_title'=>'','bio'=>'','photo'=>'','email'=>'','phone'=>'','sort_order'=>0,'is_active'=>1];
if ($id) {
    $st = $pdo->prepare("SELECT * FROM members WHERE id = ?"); $st->execute([$id]);
    $row = $st->fetch(); if (!$row) { flash('error', 'Not found.'); redirect('members.php'); }
}
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_check($_POST['csrf'] ?? '')) $errors[] = 'Session expired.';
    $full = trim($_POST['full_name'] ?? '');
    $role = trim($_POST['role_title'] ?? '');
    $bio = trim($_POST['bio'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $order = (int)($_POST['sort_order'] ?? 0);
    $active = isset($_POST['is_active']) ? 1 : 0;
    if ($full === '') $errors[] = 'Name required.';
    if (!$errors) {
        try {
            $photo = $row['photo']; $up = upload_image('photo', 'members'); if ($up) $photo = $up;
            if ($row['id']) {
                $st = $pdo->prepare("UPDATE members SET full_name=?, role_title=?, bio=?, photo=?, email=?, phone=?, sort_order=?, is_active=? WHERE id=?");
                $st->execute([$full,$role,$bio,$photo,$email,$phone,$order,$active,$row['id']]);
                flash('success', 'Member updated.');
            } else {
                $st = $pdo->prepare("INSERT INTO members (full_name, role_title, bio, photo, email, phone, sort_order, is_active) VALUES (?,?,?,?,?,?,?,?)");
                $st->execute([$full,$role,$bio,$photo,$email,$phone,$order,$active]);
                flash('success', 'Member created.');
            }
            redirect('members.php');
        } catch (Exception $e) { $errors[] = $e->getMessage(); }
    }
    $row = array_merge($row, ['full_name'=>$full,'role_title'=>$role,'bio'=>$bio,'email'=>$email,'phone'=>$phone,'sort_order'=>$order,'is_active'=>$active]);
}
$pageTitle = $row['id'] ? 'Edit member' : 'New member';
require __DIR__ . '/includes/header.php';
?>
<form method="post" enctype="multipart/form-data" class="editor">
  <?php echo csrf_field(); ?>
  <?php foreach ($errors as $e): ?><div class="alert alert-error"><?php echo e($e); ?></div><?php endforeach; ?>
  <div class="editor-grid">
    <div class="editor-main">
      <label>Full name</label>
      <input type="text" name="full_name" required value="<?php echo e($row['full_name']); ?>">
      <label>Role / Title</label>
      <input type="text" name="role_title" value="<?php echo e($row['role_title']); ?>">
      <label>Bio</label>
      <textarea name="bio" rows="6"><?php echo e($row['bio']); ?></textarea>
      <div class="editor-grid grid-cols-2">
        <div><label>Email</label><input type="email" name="email" value="<?php echo e($row['email']); ?>"></div>
        <div><label>Phone</label><input type="text" name="phone" value="<?php echo e($row['phone']); ?>"></div>
      </div>
    </div>
    <div class="editor-side">
      <div class="side-block">
        <label>Photo</label>
        <?php if (!empty($row['photo'])): ?><div class="thumb-preview"><img src="<?php echo e(image_url($row['photo'])); ?>" alt=""></div><?php endif; ?>
        <input type="file" name="photo" accept="image/*">
      </div>
      <div class="side-block">
        <label>Sort order</label>
        <input type="number" name="sort_order" value="<?php echo (int)$row['sort_order']; ?>">
      </div>
      <div class="side-block">
        <label><input type="checkbox" name="is_active" value="1"<?php echo $row['is_active']?' checked':''; ?>> Active</label>
      </div>
      <div class="side-actions">
        <button class="btn-primary" type="submit"><?php echo $row['id']?'Update':'Create'; ?></button>
        <a class="btn-secondary" href="members.php">Cancel</a>
      </div>
    </div>
  </div>
</form>
<?php require __DIR__ . '/includes/footer.php'; ?>
