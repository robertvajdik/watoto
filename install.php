<?php
// Malezi na Watoto — installer
// Creates the database schema and a default admin user.
// After running, DELETE this file.

require_once __DIR__ . '/includes/config.php';

$errors = [];
$done = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $adminUser = trim($_POST['admin_user'] ?? '');
    $adminEmail = trim($_POST['admin_email'] ?? '');
    $adminPass = $_POST['admin_pass'] ?? '';

    if (strlen($adminUser) < 3) $errors[] = 'Admin username must be at least 3 characters.';
    if (!filter_var($adminEmail, FILTER_VALIDATE_EMAIL)) $errors[] = 'Valid admin email required.';
    if (strlen($adminPass) < 6) $errors[] = 'Admin password must be at least 6 characters.';

    if (!$errors) {
        try {
            // Connect without a DB to create it
            $pdo = new PDO('mysql:host=' . DB_HOST . ';charset=' . DB_CHARSET, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            ]);
            $pdo->exec("CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            $pdo->exec("USE `" . DB_NAME . "`");

            $sql = file_get_contents(__DIR__ . '/database.sql');
            // Strip the CREATE DATABASE + USE statements (already handled)
            $sql = preg_replace('/CREATE DATABASE.*?;/is', '', $sql);
            $sql = preg_replace('/USE\s+`?[^;`]+`?;/i', '', $sql);
            $pdo->exec($sql);

            // Create admin
            $hash = password_hash($adminPass, PASSWORD_DEFAULT);
            $st = $pdo->prepare("INSERT INTO users (username, email, password, full_name, role)
                VALUES (?, ?, ?, ?, 'admin')
                ON DUPLICATE KEY UPDATE password = VALUES(password), email = VALUES(email)");
            $st->execute([$adminUser, $adminEmail, $hash, $adminUser]);

            // Seed some sample content if empty
            $count = (int)$pdo->query("SELECT COUNT(*) FROM programs")->fetchColumn();
            if ($count === 0) {
                $programs = [
                    ['Positive Parenting', 'positive-parenting', 'heart', 'Promoting positive parenting and responsive caregiving.', 'Parenting sessions that help parents and caregivers build warm, responsive relationships and respond to their children’s needs with care and confidence.'],
                    ['Early Childhood Development', 'early-childhood-development', 'child', 'Supporting early childhood development and learning.', 'We help families give young children the care, protection, and stimulation they need in their earliest years to reach their full potential.'],
                    ['Dialogic Booksharing', 'dialogic-booksharing', 'book', 'Sharing books through conversation to build language and connection.', 'Parents and caregivers learn to share picture books interactively — pointing, asking, and talking together — so children grow in language, attention, and confidence.'],
                    ['Child-Centered Learning', 'child-centered-learning', 'puzzle', 'Learning activities where children explore, communicate, and discover.', 'Play-based, child-centered activities that encourage children to explore, communicate, learn, and develop with confidence.'],
                    ['Parent & Caregiver Empowerment', 'parent-caregiver-empowerment', 'family', 'Practical parenting knowledge and skills for families.', 'We equip parents and caregivers with practical knowledge, skills, and opportunities that strengthen children’s development and family well-being.'],
                    ['Community Engagement', 'community-engagement', 'users', 'Mobilizing communities and stakeholders for children’s well-being.', 'Working with community members and local stakeholders to create sustainable solutions and supportive environments for children and families.'],
                ];
                $ins = $pdo->prepare("INSERT INTO programs (title, slug, icon, summary, body, sort_order) VALUES (?, ?, ?, ?, ?, ?)");
                foreach ($programs as $i => $p) { $ins->execute([$p[0], $p[1], $p[2], $p[3], $p[4], $i]); }

                $posts = [
                    ['Welcome to Malezi na Watoto', 'Supporting young children and their families to learn, grow, and thrive.', "We are proud to launch our new home online — a hub for parents, caregivers, partners, and friends. Explore our programs, meet the team, and see how we are supporting young children and their families.\n\nJoin us in our mission to empower parents, caregivers, and communities with knowledge and practical opportunities that promote positive parenting and early childhood development."],
                    ['Dialogic Booksharing sessions', 'Parents discover how sharing a picture book can open a world of conversation.', "This season, parents and caregivers joined our Dialogic Booksharing sessions to learn how to share books interactively with their young children — pointing at pictures, asking questions, and following the child’s lead. Families tell us their children are talking more, listening longer, and asking for books every day."],
                    ['Positive parenting workshop recap', 'A day of learning and sharing for parents and caregivers.', "Last Saturday, parents and caregivers gathered at the community centre to talk about responsive caregiving, everyday play, and supporting children’s early learning at home. Thank you to every facilitator and every family who showed up ready to learn."],
                ];
                $ins = $pdo->prepare("INSERT INTO posts (title, slug, excerpt, body, status) VALUES (?, ?, ?, ?, 'published')");
                foreach ($posts as $p) { $ins->execute([$p[0], slugify($p[0]), $p[1], $p[2]]); }
            }

            $done = true;
        } catch (Exception $e) {
            $errors[] = 'Setup failed: ' . $e->getMessage();
        }
    }
}

function slugify($text) {
    $text = preg_replace('~[^\pL\d]+~u', '-', $text);
    $text = iconv('utf-8', 'us-ascii//TRANSLIT//IGNORE', $text);
    $text = preg_replace('~[^-\w]+~', '', $text);
    $text = trim($text, '-');
    $text = preg_replace('~-+~', '-', $text);
    return strtolower($text ?: 'item-' . time());
}
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Install — Malezi na Watoto</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" href="assets/css/style.css">
<style>
  body { background: linear-gradient(135deg, #f5efe4, #F5F1FB); min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 40px 20px; font-family: 'Inter', system-ui, sans-serif; }
  .install-card { background:#fff; max-width:560px; width:100%; padding:48px; border-radius:16px; box-shadow:0 20px 60px rgba(35,52,30,.12); }
  .install-card h1 { margin:0 0 8px; font-family:'Playfair Display', serif; color:#52279B; font-size:28px; }
  .install-card p.lead { color:#5f5670; margin:0 0 28px; }
  .install-card label { display:block; margin:14px 0 6px; font-weight:600; color:#52279B; font-size:14px; }
  .install-card input { width:100%; padding:12px 14px; border:1px solid #DCCBF4; border-radius:8px; font-size:15px; }
  .install-card input:focus { outline:none; border-color:#52279B; box-shadow:0 0 0 3px rgba(82,39,155,.15); }
  .install-card button { margin-top:24px; width:100%; background:#52279B; color:#fff; border:0; padding:14px; border-radius:8px; font-size:16px; font-weight:600; cursor:pointer; }
  .install-card button:hover { background:#3B1C72; }
  .msg { padding:12px 16px; border-radius:8px; margin-bottom:16px; }
  .msg.error { background:#fef2f2; color:#991b1b; border:1px solid #fecaca; }
  .msg.success { background:#f0fdf4; color:#166534; border:1px solid #bbf7d0; }
  .msg.success a { color:#166534; font-weight:600; }
</style>
</head>
<body>
<div class="install-card">
  <h1>Install Malezi na Watoto</h1>
  <p class="lead">One-time setup — creates the database schema and your admin account.</p>

  <?php if ($errors): ?>
    <div class="msg error"><?php echo implode('<br>', array_map('htmlspecialchars', $errors)); ?></div>
  <?php endif; ?>

  <?php if ($done): ?>
    <div class="msg success">
      <strong>Installed.</strong> Your site is ready.<br><br>
      <a href="index.php">Visit the site</a> · <a href="admin/index.php">Log into admin</a><br><br>
      <em>Now delete <code>install.php</code> from the server for security.</em>
    </div>
  <?php else: ?>
    <form method="post" autocomplete="off">
      <label>Admin username</label>
      <input type="text" name="admin_user" required minlength="3" value="<?php echo htmlspecialchars($_POST['admin_user'] ?? 'admin'); ?>">
      <label>Admin email</label>
      <input type="email" name="admin_email" required value="<?php echo htmlspecialchars($_POST['admin_email'] ?? ''); ?>">
      <label>Admin password</label>
      <input type="password" name="admin_pass" required minlength="6">
      <button type="submit">Install</button>
    </form>
  <?php endif; ?>
</div>
</body>
</html>
