<?php
$pageTitle = 'Admin Users';
require __DIR__ . '/includes/header.php';

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_check($_POST['csrf'] ?? '')) { flash('error','Session expired.'); redirect('users.php'); }
    $action = $_POST['action'] ?? '';
    if ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id && $id !== (int)admin_user()['id']) {
            $pdo->prepare("DELETE FROM users WHERE id = ?")->execute([$id]);
            flash('success', 'User removed.');
        } else {
            flash('error', 'You cannot delete your own account.');
        }
        redirect('users.php');
    }
    if ($action === 'create' || $action === 'password') {
        $username = trim($_POST['username'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $full = trim($_POST['full_name'] ?? '');
        $pass = $_POST['password'] ?? '';
        $role = $_POST['role'] ?? 'admin';
        if (!in_array($role, ['superadmin','admin','editor'], true)) $role = 'admin';
        if ($action === 'create') {
            if (strlen($username) < 3) $errors[] = 'Username too short.';
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Valid email required.';
            if (strlen($pass) < 6) $errors[] = 'Password must be at least 6 characters.';
            if (!$errors) {
                try {
                    $st = $pdo->prepare("INSERT INTO users (username, email, password, full_name, role) VALUES (?,?,?,?,?)");
                    $st->execute([$username, $email, password_hash($pass, PASSWORD_DEFAULT), $full ?: $username, $role]);
                    flash('success', 'User created.');
                    redirect('users.php');
                } catch (PDOException $e) { $errors[] = 'Username or email already exists.'; }
            }
        } elseif ($action === 'password') {
            $id = (int)($_POST['id'] ?? 0);
            if ($id && strlen($pass) >= 6) {
                $pdo->prepare("UPDATE users SET password = ? WHERE id = ?")->execute([password_hash($pass, PASSWORD_DEFAULT), $id]);
                flash('success', 'Password updated.');
                redirect('users.php');
            } else {
                $errors[] = 'Password must be at least 6 characters.';
            }
        }
    }
}
$users = $pdo->query("SELECT id, username, email, full_name, role, created_at FROM users ORDER BY id ASC")->fetchAll();
?>
<?php foreach ($errors as $e): ?><div class="alert alert-error"><?php echo e($e); ?></div><?php endforeach; ?>

<div class="grid-2">
  <div class="card">
    <h2 class="h-flush">Existing users</h2>
    <div class="table-wrap">
      <table>
        <thead><tr><th>User</th><th>Email</th><th>Role</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($users as $u): ?>
          <tr>
            <td data-label="User"><strong><?php echo e($u['username']); ?></strong><div class="row-sub"><?php echo e($u['full_name']); ?></div></td>
            <td data-label="Email"><?php echo e($u['email']); ?></td>
            <td data-label="Role"><span class="badge <?php echo $u['role']==='editor'?'draft':'published'; ?>"><?php echo e($u['role']); ?></span></td>
            <td class="actions">
              <details>
                <summary>Password</summary>
                <form method="post" class="mt-sm">
                  <?php echo csrf_field(); ?>
                  <input type="hidden" name="action" value="password">
                  <input type="hidden" name="id" value="<?php echo $u['id']; ?>">
                  <input type="password" name="password" placeholder="New password" required minlength="6">
                  <button class="btn-secondary mt-sm" type="submit">Update</button>
                </form>
              </details>
              <?php if ($u['id'] != admin_user()['id']): ?>
                <form method="post" data-confirm="Delete <?php echo e($u['username']); ?>?" class="mt-sm">
                  <?php echo csrf_field(); ?>
                  <input type="hidden" name="action" value="delete">
                  <input type="hidden" name="id" value="<?php echo $u['id']; ?>">
                  <button class="link-danger" type="submit">Delete</button>
                </form>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
  <div class="card">
    <h2 class="h-flush">Add new user</h2>
    <form method="post">
      <?php echo csrf_field(); ?>
      <input type="hidden" name="action" value="create">
      <label>Username</label><input type="text" name="username" required minlength="3">
      <label>Email</label><input type="email" name="email" required>
      <label>Full name</label><input type="text" name="full_name">
      <label>Role</label>
      <select name="role"><option value="admin">Admin</option><option value="editor">Editor</option><option value="superadmin">Superadmin</option></select>
      <label>Password</label><input type="password" name="password" required minlength="6">
      <div class="side-actions mt-md"><button class="btn-primary" type="submit">Create user</button></div>
    </form>
  </div>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
