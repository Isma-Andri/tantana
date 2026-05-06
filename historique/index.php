<?php
$pageTitle = 'Historique - Tantana';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';
requireLogin();
$user = getCurrentUser();

$page    = max(1, (int)($_GET['page'] ?? 1));
$perPage = 25;
$offset  = ($page - 1) * $perPage;

try {
    $pdo = getDB();

    if ($user['role'] === 'chef_projet') {
        // Chef voit toutes les actions sur ses projets + ses propres actions
        $countStmt = $pdo->prepare("
            SELECT COUNT(*) FROM actions a
            WHERE a.id_user = ?
               OR a.id_projet IN (SELECT id_projet FROM projets WHERE id_chef=?)
               OR a.id_projet IN (SELECT id_projet FROM participations WHERE id_user=?)
        ");
        $countStmt->execute([$user['id'], $user['id'], $user['id']]);
        $total = (int)$countStmt->fetchColumn();

        $stmt = $pdo->prepare("
            SELECT a.*, u.nom AS user_nom, p.nom AS projet_nom, t.nom AS tache_nom
            FROM actions a
            JOIN users u ON a.id_user = u.id_user
            LEFT JOIN projets p ON a.id_projet = p.id_projet
            LEFT JOIN taches  t ON a.id_tache  = t.id_tache
            WHERE a.id_user = ?
               OR a.id_projet IN (SELECT id_projet FROM projets WHERE id_chef=?)
               OR a.id_projet IN (SELECT id_projet FROM participations WHERE id_user=?)
            ORDER BY a.date_action DESC
            LIMIT ? OFFSET ?
        ");
        $stmt->execute([$user['id'], $user['id'], $user['id'], $perPage, $offset]);
    } else {
        // Membre voit ses propres actions + celles sur ses projets
        $countStmt = $pdo->prepare("
            SELECT COUNT(*) FROM actions a
            WHERE a.id_user = ?
               OR a.id_projet IN (SELECT id_projet FROM participations WHERE id_user=?)
        ");
        $countStmt->execute([$user['id'], $user['id']]);
        $total = (int)$countStmt->fetchColumn();

        $stmt = $pdo->prepare("
            SELECT a.*, u.nom AS user_nom, p.nom AS projet_nom, t.nom AS tache_nom
            FROM actions a
            JOIN users u ON a.id_user = u.id_user
            LEFT JOIN projets p ON a.id_projet = p.id_projet
            LEFT JOIN taches  t ON a.id_tache  = t.id_tache
            WHERE a.id_user = ?
               OR a.id_projet IN (SELECT id_projet FROM participations WHERE id_user=?)
            ORDER BY a.date_action DESC
            LIMIT ? OFFSET ?
        ");
        $stmt->execute([$user['id'], $user['id'], $perPage, $offset]);
    }

    $actions   = $stmt->fetchAll();
    $totalPages = (int)ceil($total / $perPage);
    $dbErr      = null;
} catch (PDOException $e) {
    error_log('[historique/index] ' . $e->getMessage());
    $actions    = [];
    $total      = 0;
    $totalPages = 1;
    $dbErr      = $e->getMessage();
}

require_once __DIR__ . '/../includes/header.php';
?>
<div class="app-layout">
  <?php require __DIR__ . '/../includes/sidebar.php'; ?>
  <main class="main-content">
    <div class="page-header">
      <h1>Historique des activites</h1>
      <p><?= $total ?> action(s) enregistree(s)</p>
    </div>

    <?= flashHtml() ?>
    <?php if (isset($dbErr)): ?><div class="alert alert-error"><?= htmlspecialchars($dbErr) ?></div><?php endif; ?>

    <?php if (empty($actions)): ?>
      <div class="card" style="text-align:center;padding:60px 24px;color:var(--text-muted);">
        <p>Aucune activite enregistree.</p>
      </div>
    <?php else: ?>
      <div class="card" style="padding:0;overflow:hidden;">
        <div class="table-wrap">
          <table>
            <thead>
              <tr>
                <th>Date</th>
                <th>Utilisateur</th>
                <th>Action</th>
                <th>Projet</th>
                <th>Tache</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($actions as $a): ?>
              <tr>
                <td style="white-space:nowrap;color:var(--text-muted);font-size:.8rem;"><?= fmtDatetime($a['date_action']) ?></td>
                <td>
                  <div style="display:flex;align-items:center;gap:7px;">
                    <div class="avatar" style="width:24px;height:24px;font-size:.65rem;"><?= strtoupper(substr($a['user_nom'],0,1)) ?></div>
                    <span style="font-size:.875rem;"><?= htmlspecialchars($a['user_nom']) ?></span>
                  </div>
                </td>
                <td style="font-size:.875rem;max-width:300px;"><?= htmlspecialchars($a['description']) ?></td>
                <td style="font-size:.8rem;">
                  <?php if ($a['id_projet'] && $a['projet_nom']): ?>
                    <a href="../projets/view.php?id=<?= $a['id_projet'] ?>" style="color:var(--accent-dark);"><?= htmlspecialchars($a['projet_nom']) ?></a>
                  <?php else: ?>
                    <span style="color:var(--text-muted);">—</span>
                  <?php endif; ?>
                </td>
                <td style="font-size:.8rem;">
                  <?php if ($a['id_tache'] && $a['tache_nom']): ?>
                    <a href="../taches/view.php?id=<?= $a['id_tache'] ?>" style="color:var(--accent-dark);"><?= htmlspecialchars($a['tache_nom']) ?></a>
                  <?php else: ?>
                    <span style="color:var(--text-muted);">—</span>
                  <?php endif; ?>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Pagination -->
      <?php if ($totalPages > 1): ?>
      <div style="display:flex;justify-content:center;gap:6px;margin-top:20px;flex-wrap:wrap;">
        <?php if ($page > 1): ?>
          <a href="?page=<?= $page-1 ?>" class="btn btn-outline btn-sm">&larr; Precedent</a>
        <?php endif; ?>
        <?php for ($i = max(1,$page-2); $i <= min($totalPages,$page+2); $i++): ?>
          <a href="?page=<?= $i ?>" class="btn btn-sm <?= $i===$page?'btn-primary':'btn-outline' ?>"><?= $i ?></a>
        <?php endfor; ?>
        <?php if ($page < $totalPages): ?>
          <a href="?page=<?= $page+1 ?>" class="btn btn-outline btn-sm">Suivant &rarr;</a>
        <?php endif; ?>
      </div>
      <?php endif; ?>
    <?php endif; ?>
  </main>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
