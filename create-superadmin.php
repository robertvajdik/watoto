<?php
// Malezi na Watoto — one-off superadmin creator.
// Run once, then DELETE this file.

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';

$errors = [];
$done = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $u = trim($_POST['username'] ?? '');
    $e = trim($_POST['email'] ?? '');
    $p = $_POST['password'] ?? '';
    $f = trim($_POST['full_name'] ?? '') ?: $u;

    if (strlen($u) < 3) $errors[] = 'Username must be at least 3 characters.';
    if (!filter_var($e, FILTER_VALIDATE_EMAIL)) $errors[] = 'Valid email required.';
    if (strlen($p) < 8) $errors[] = 'Password must be at least 8 characters.';

    if (!$errors) {
        try {
            // Ensure the users table has room for the superadmin role.
            try {
                $pdo->exec("ALTER TABLE users MODIFY COLUMN role ENUM('superadmin','admin','editor') NOT NULL DEFAULT 'admin'");
            } catch (Exception $ex) { /* already migrated */ }

            $hash = password_hash($p, PASSWORD_DEFAULT);
            $st = $pdo->prepare("INSERT INTO users (username, email, password, full_name, role)
                VALUES (?, ?, ?, ?, 'superadmin')
                ON DUPLICATE KEY UPDATE password = VALUES(password), email = VALUES(email), role = 'superadmin', full_name = VALUES(full_name)");
            $st->execute([$u, $e, $hash, $f]);
            $done = true;
        } catch (Exception $ex) {
            $errors[] = 'Failed: ' . $ex->getMessage();
        }
    }
}
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Create Superadmin — Malezi na Watoto</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<main class="container container-narrow mt-8">
  <div class="panel">
    <h1>Create superadmin</h1>
    <p class="info-lead">Creates a superadmin user in the database. Delete this file after use.</p>

    <?php foreach ($errors as $er): ?><div class="alert alert-error"><?php echo htmlspecialchars($er); ?></div><?php endforeach; ?>

    <?php if ($done): ?>
      <div class="alert alert-success">
        <strong>Superadmin ready.</strong> You can now <a href="admin/index.php">sign in</a>.<br>
        Now delete <code>create-superadmin.php</code> from the server.
      </div>
    <?php else: ?>
      <form method="post" autocomplete="off">
        <label>Username</label>
        <input type="text" name="username" required minlength="3" value="<?php echo htmlspecialchars($_POST['username'] ?? 'superadmin'); ?>">
        <label>Full name</label>
        <input type="text" name="full_name" value="<?php echo htmlspecialchars($_POST['full_name'] ?? ''); ?>">
        <label>Email</label>
        <input type="email" name="email" required value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
        <label>Password</label>
        <input type="password" name="password" required minlength="8">
        <div class="mt-4"><button type="submit" class="btn">Create superadmin</button></div>
      </form>
    <?php endif; ?>
  </div>
</main>
</body>
</html>
