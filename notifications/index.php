<?php
$pageTitle = 'Notifications - Tantana';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';
requireLogin();
$user = getCurrentUser();

// Marquer tout comme lu
if (isset($_GET['tout_lire'])) {
    try {
        getDB()->prepare("UPDATE notifications SET est_lue=1 WHERE id_user=?")->execute([$user['id']]);
        flashSet('success', 'Toutes les notifications marquees comme lues.');
    } catch (PDOException $e) {
        flashSet('error', $e->getMessage());
    }
    redirect('notifications/index.php');
}

// Marquer une comme lue
if (isset($_GET['lire'])) {
    try {
        getDB()->prepare("UPDATE notifications SET est_lue=1 WHERE id_notif=? AND id_user=?")->execute([(int)$_GET['lire'], $user['id']]);
    } catch (PDOException $e) { /* silencieux */ }
    $back = $_GET['back'] ?? 'notifications/index.php';
    redirect($back);
}

try {
    $pdo  = getDB();
    $stmt = $pdo->prepare("
        SELECT n.*, a.description AS action_desc, a.date_action
        FROM notifications n
        LEFT JOIN actions a ON n.id_action = a.id_action
        WHERE n.id_user = ?
        ORDER BY n.date_notif DESC
        LIMIT 100
    ");
    $stmt->execute([$user['id']]);
    $notifs  = $stmt->fetchAll();
    $nonLues = array_filter($notifs, fn($n) => !$n['est_lue']);
    $dbErr   = null;
} catch (PDOException $e) {
    error_log('[notifications/index] ' . $e->getMessage());
    $notifs  = [];
    $nonLues = [];
    $dbErr   = $e->getMessage();
}

require_once __DIR__ . '/../includes/header.php';
?>
<div class="app-layout">
  <?php require __DIR__ . '/../includes/sidebar.php'; ?>
  <main class="main-content">
    <div class="page-header">
      <div style="display:flex;align-items:flex-start;justify-content:space-between;flex-wrap:wrap;gap:12px;">
        <div>
          <h1>Notifications</h1>
          <p><?= count($nonLues) ?> non lue(s) sur <?= count($notifs) ?></p>
        </div>
        <?php if (!empty($nonLues)): ?>
          <a href="?tout_lire=1" class="btn btn-outline btn-sm">Tout marquer comme lu</a>
        <?php endif; ?>
      </div>
    </div>

    <?= flashHtml() ?>
    <?php if (isset($dbErr)): ?><div class="alert alert-error"><?= htmlspecialchars($dbErr) ?></div><?php endif; ?>

    <?php if (empty($notifs)): ?>
      <div class="card" style="text-align:center;padding:60px 24px;color:var(--text-muted);">
        <div style="font-size:2rem;margin-bottom:12px;color:var(--border);">—</div>
        <p>Aucune notification pour l'instant.</p>
      </div>
    <?php else: ?>
      <div style="display:flex;flex-direction:column;gap:8px;">
        <?php foreach ($notifs as $n): ?>
          <div style="display:flex;align-items:flex-start;gap:14px;padding:14px 18px;background:var(--card);border:1px solid var(--border);border-radius:var(--radius);<?= !$n['est_lue'] ? 'border-left:3px solid var(--accent);' : 'opacity:.75;' ?>">
            <div style="width:8px;height:8px;border-radius:50%;background:<?= !$n['est_lue']?'var(--accent)':'var(--border)' ?>;margin-top:6px;flex-shrink:0;"></div>
            <div style="flex:1;min-width:0;">
              <div style="font-size:.9rem;margin-bottom:3px;<?= !$n['est_lue']?'font-weight:500;':'' ?>">
                <?= htmlspecialchars($n['contenu']) ?>
              </div>
              <div style="font-size:.75rem;color:var(--text-muted);"><?= fmtDatetime($n['date_notif']) ?></div>
            </div>
            <?php if (!$n['est_lue']): ?>
              <a href="?lire=<?= $n['id_notif'] ?>" class="btn btn-outline btn-sm" style="flex-shrink:0;">Lu</a>
            <?php endif; ?>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </main>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
