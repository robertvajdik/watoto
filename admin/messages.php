<?php
$pageTitle = 'Messages';
require __DIR__ . '/includes/header.php';

// Preserve list state across redirects
$filter = in_array($_GET['filter'] ?? '', ['unread', 'read'], true) ? $_GET['filter'] : 'all';
$q      = trim((string)($_GET['q'] ?? ''));
$viewId = (int)($_GET['id'] ?? 0);
$stateQs = function ($extra = []) use ($filter, $q, $viewId) {
    $parts = [];
    if ($filter !== 'all') $parts['filter'] = $filter;
    if ($q !== '')         $parts['q'] = $q;
    if ($viewId)           $parts['id'] = $viewId;
    return http_build_query(array_merge($parts, $extra));
};

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_check($_POST['csrf'] ?? '')) { flash('error', 'Session expired.'); redirect('messages.php'); }
    $action = $_POST['action'] ?? '';
    $id = (int)($_POST['id'] ?? 0);
    $redirParts = [];
    if ($filter !== 'all') $redirParts['filter'] = $filter;
    if ($q !== '')         $redirParts['q'] = $q;

    if ($action === 'delete' && $id) {
        $pdo->prepare("DELETE FROM messages WHERE id = ?")->execute([$id]);
        flash('success', 'Message deleted.');
        redirect('messages.php' . ($redirParts ? '?' . http_build_query($redirParts) : ''));
    } elseif ($action === 'mark_read' && $id) {
        $pdo->prepare("UPDATE messages SET is_read = 1 WHERE id = ?")->execute([$id]);
    } elseif ($action === 'mark_unread' && $id) {
        $pdo->prepare("UPDATE messages SET is_read = 0 WHERE id = ?")->execute([$id]);
        // Return to list (unread state is more useful there)
        redirect('messages.php' . ($redirParts ? '?' . http_build_query($redirParts) : ''));
    } elseif ($action === 'mark_all_read') {
        $pdo->prepare("UPDATE messages SET is_read = 1 WHERE is_read = 0")->execute();
        flash('success', 'All messages marked as read.');
        redirect('messages.php' . ($redirParts ? '?' . http_build_query($redirParts) : ''));
    }

    if ($id) $redirParts['id'] = $id;
    redirect('messages.php' . ($redirParts ? '?' . http_build_query($redirParts) : ''));
}

// Live counts
$countAll    = (int)$pdo->query("SELECT COUNT(*) FROM messages")->fetchColumn();
$countUnread = (int)$pdo->query("SELECT COUNT(*) FROM messages WHERE is_read = 0")->fetchColumn();
$countRead   = $countAll - $countUnread;

// Build list query
$where = [];
$params = [];
if ($filter === 'unread') $where[] = 'is_read = 0';
if ($filter === 'read')   $where[] = 'is_read = 1';
if ($q !== '') {
    $where[] = '(name LIKE ? OR email LIKE ? OR subject LIKE ? OR body LIKE ?)';
    $like = '%' . $q . '%';
    array_push($params, $like, $like, $like, $like);
}
$whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';
$listSql = "SELECT id, name, email, subject, body, is_read, created_at FROM messages $whereSql ORDER BY created_at DESC LIMIT 200";
$st = $pdo->prepare($listSql); $st->execute($params); $rows = $st->fetchAll();

$viewing = null;
$senderHistory = 0;
if ($viewId) {
    $st = $pdo->prepare("SELECT * FROM messages WHERE id = ?"); $st->execute([$viewId]);
    $viewing = $st->fetch();
    if ($viewing && !$viewing['is_read']) {
        $pdo->prepare("UPDATE messages SET is_read = 1 WHERE id = ?")->execute([$viewId]);
        $viewing['is_read'] = 1;
    }
    if ($viewing) {
        $sh = $pdo->prepare("SELECT COUNT(*) FROM messages WHERE email = ? AND id <> ?");
        $sh->execute([$viewing['email'], $viewing['id']]);
        $senderHistory = (int)$sh->fetchColumn();
    }
}

$makeFilterUrl = function ($f) use ($q) {
    $parts = [];
    if ($f !== 'all') $parts['filter'] = $f;
    if ($q !== '')    $parts['q'] = $q;
    return 'messages.php' . ($parts ? '?' . http_build_query($parts) : '');
};
?>
<div class="msg-toolbar">
  <div class="msg-tabs" role="tablist">
    <a class="tab <?php echo $filter==='all'?'active':''; ?>"    href="<?php echo e($makeFilterUrl('all')); ?>">All <span class="badge"><?php echo $countAll; ?></span></a>
    <a class="tab <?php echo $filter==='unread'?'active':''; ?>" href="<?php echo e($makeFilterUrl('unread')); ?>">Unread <span class="badge"><?php echo $countUnread; ?></span></a>
    <a class="tab <?php echo $filter==='read'?'active':''; ?>"   href="<?php echo e($makeFilterUrl('read')); ?>">Read <span class="badge"><?php echo $countRead; ?></span></a>
  </div>
  <form method="get" class="msg-search">
    <?php if ($filter !== 'all'): ?><input type="hidden" name="filter" value="<?php echo e($filter); ?>"><?php endif; ?>
    <input type="search" name="q" value="<?php echo e($q); ?>" placeholder="Search name, email, subject, body…">
    <?php if ($q !== ''): ?><a class="btn-secondary" href="<?php echo e($makeFilterUrl($filter)); ?>">Clear</a><?php endif; ?>
    <button type="submit" class="btn-secondary">Search</button>
  </form>
  <?php if ($countUnread > 0): ?>
  <form method="post" class="msg-bulk" data-confirm="Mark all unread messages as read?">
    <?php echo csrf_field(); ?>
    <input type="hidden" name="action" value="mark_all_read">
    <button type="submit" class="btn-secondary">Mark all read (<?php echo $countUnread; ?>)</button>
  </form>
  <?php endif; ?>
</div>

<div class="two-panel">
  <div class="card list-panel">
    <?php if (!$rows): ?>
      <div class="empty-state">
        <div class="icon">✉</div>
        <h3><?php echo $q !== '' || $filter !== 'all' ? 'No matching messages' : 'No messages'; ?></h3>
        <p><?php echo $q !== '' || $filter !== 'all' ? 'Try clearing filters or search.' : 'Messages sent via the contact form will appear here.'; ?></p>
      </div>
    <?php else: ?>
      <ul class="msg-list">
      <?php foreach ($rows as $r): ?>
        <li class="<?php echo $r['is_read']?'':'unread'; ?> <?php echo $r['id']==$viewId?'active':''; ?>">
          <a href="messages.php?<?php echo e($stateQs(['id' => $r['id']])); ?>">
            <div class="from"><?php echo e($r['name']); ?><span class="when"><?php echo e(format_date($r['created_at'], 'M j')); ?></span></div>
            <div class="subj"><?php echo e($r['subject'] ?: '(no subject)'); ?></div>
            <div class="snippet"><?php echo e(excerpt($r['body'], 90)); ?></div>
          </a>
        </li>
      <?php endforeach; ?>
      </ul>
    <?php endif; ?>
  </div>

  <div class="card view-panel">
    <?php if (!$viewing): ?>
      <div class="empty-state">
        <div class="icon">✉</div>
        <h3>Select a message</h3>
        <p>Choose a message from the list to read it here.</p>
      </div>
    <?php else: ?>
      <?php
        $replySubject = 'Re: ' . ($viewing['subject'] ?: '(no subject)');
        $replyBody    = "\n\n\n---\nOn " . format_date($viewing['created_at'], 'M j, Y g:i A') . ", " . $viewing['name'] . " <" . $viewing['email'] . "> wrote:\n" . preg_replace('/^/m', '> ', $viewing['body']);
      ?>
      <div class="msg-head">
        <h2><?php echo e($viewing['subject'] ?: '(no subject)'); ?></h2>
        <div class="meta">
          From <strong><?php echo e($viewing['name']); ?></strong>
          &lt;<a href="mailto:<?php echo e($viewing['email']); ?>"><?php echo e($viewing['email']); ?></a>&gt;
          <?php if ($viewing['phone']): ?> · <a href="tel:<?php echo e($viewing['phone']); ?>"><?php echo e($viewing['phone']); ?></a><?php endif; ?>
          <?php if (!empty($viewing['ip_address'])): ?> · <span title="Sender IP"><?php echo e($viewing['ip_address']); ?></span><?php endif; ?>
          <br>
          <?php echo e(format_date($viewing['created_at'], 'M j, Y · g:i A')); ?>
          <?php if ($senderHistory > 0): ?>
            · <a href="messages.php?<?php echo e(http_build_query(['q' => $viewing['email']])); ?>"><?php echo $senderHistory; ?> other message<?php echo $senderHistory === 1 ? '' : 's'; ?> from this sender</a>
          <?php endif; ?>
        </div>
      </div>
      <div class="msg-body"><?php echo nl2br(e($viewing['body'])); ?></div>
      <div class="msg-actions">
        <a class="btn-primary" href="mailto:<?php echo e($viewing['email']); ?>?subject=<?php echo rawurlencode($replySubject); ?>&amp;body=<?php echo rawurlencode($replyBody); ?>">Reply by email</a>
        <form method="post" class="inline-form">
          <?php echo csrf_field(); ?>
          <input type="hidden" name="action" value="mark_unread">
          <input type="hidden" name="id" value="<?php echo $viewing['id']; ?>">
          <button type="submit" class="btn-secondary">Mark unread</button>
        </form>
        <form method="post" class="inline-form" data-confirm="Delete this message?">
          <?php echo csrf_field(); ?>
          <input type="hidden" name="action" value="delete">
          <input type="hidden" name="id" value="<?php echo $viewing['id']; ?>">
          <button type="submit" class="btn-danger">Delete</button>
        </form>
      </div>
    <?php endif; ?>
  </div>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
