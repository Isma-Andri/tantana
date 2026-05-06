<?php
$pageTitle = 'Projets - Tantana';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';
requireLogin();
$user = getCurrentUser();

try {
    $pdo = getDB();
    if ($user['role'] === 'chef_projet') {
        $stmt = $pdo->prepare("
            SELECT p.*, u.nom AS chef_nom,
                   (SELECT COUNT(*) FROM taches t WHERE t.id_projet=p.id_projet) AS nb_taches,
                   (SELECT COUNT(*) FROM taches t WHERE t.id_projet=p.id_projet AND t.id_statut=3) AS nb_terminees,
                   (SELECT COUNT(*) FROM participations pa2 WHERE pa2.id_projet=p.id_projet) AS nb_membres
            FROM projets p
            JOIN users u ON p.id_chef = u.id_user
            WHERE p.id_chef = ?
               OR p.id_projet IN (SELECT id_projet FROM participations WHERE id_user=?)
            GROUP BY p.id_projet
            ORDER BY p.date_creation DESC
        ");
        $stmt->execute([$user['id'], $user['id']]);
    } else {
        $stmt = $pdo->prepare("
            SELECT p.*, u.nom AS chef_nom,
                   (SELECT COUNT(*) FROM taches t WHERE t.id_projet=p.id_projet) AS nb_taches,
                   (SELECT COUNT(*) FROM taches t WHERE t.id_projet=p.id_projet AND t.id_statut=3) AS nb_terminees,
                   (SELECT COUNT(*) FROM participations pa2 WHERE pa2.id_projet=p.id_projet) AS nb_membres
            FROM projets p
            JOIN users u ON p.id_chef = u.id_user
            JOIN participations pa ON pa.id_projet=p.id_projet AND pa.id_user=?
            ORDER BY p.date_creation DESC
        ");
        $stmt->execute([$user['id']]);
    }
    $projets = $stmt->fetchAll();
    $dbErr   = null;
} catch (PDOException $e) {
    error_log('[projets/index] ' . $e->getMessage());
    $projets = [];
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
          <h1><?= $user['role']==='chef_projet' ? 'Mes projets' : 'Mes projets' ?></h1>
          <p><?= count($projets) ?> projet(s)</p>
        </div>
        <?php if ($user['role']==='chef_projet'): ?>
          <a href="create.php" class="btn btn-primary">+ Nouveau projet</a>
        <?php endif; ?>
      </div>
    </div>

    <?= flashHtml() ?>
    <?php if (isset($dbErr)): ?><div class="alert alert-error"><?= htmlspecialchars($dbErr) ?></div><?php endif; ?>

    <?php if (empty($projets)): ?>
      <div class="card" style="text-align:center;padding:60px 24px;">
        <p style="color:var(--text-muted);margin-bottom:16px;">Aucun projet pour l'instant.</p>
        <?php if ($user['role']==='chef_projet'): ?>
          <a href="create.php" class="btn btn-primary">Creer mon premier projet</a>
        <?php endif; ?>
      </div>
    <?php else: ?>
      <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(310px,1fr));gap:16px;">
        <?php foreach ($projets as $p):
          $total  = (int)$p['nb_taches'];
          $done   = (int)$p['nb_terminees'];
          $pct    = $total > 0 ? round($done/$total*100) : 0;
          $jours  = daysUntil($p['date_limite']);
          $isChef = ($p['id_chef'] == $user['id']);
        ?>
        <div class="card" style="display:flex;flex-direction:column;gap:14px;">
          <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:8px;">
            <div style="flex:1;min-width:0;">
              <div style="font-family:var(--font-head);font-size:1.05rem;font-weight:600;margin-bottom:3px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                <?= htmlspecialchars($p['nom']) ?>
              </div>
              <div style="font-size:.8rem;color:var(--text-muted);">Chef : <?= htmlspecialchars($p['chef_nom']) ?></div>
            </div>
            <?php if ($isChef): ?>
              <span class="badge badge-purple" style="flex-shrink:0;">Responsable</span>
            <?php else: ?>
              <span class="badge badge-blue" style="flex-shrink:0;">Participant</span>
            <?php endif; ?>
          </div>

          <?php if ($p['description']): ?>
            <p style="font-size:.85rem;color:var(--text-muted);line-height:1.55;margin:0;"><?= htmlspecialchars(mb_substr($p['description'],0,100)) ?><?= mb_strlen($p['description'])>100?'...':'' ?></p>
          <?php endif; ?>

          <div style="display:flex;gap:14px;font-size:.8rem;color:var(--text-muted);flex-wrap:wrap;">
            <span><?= $total ?> tache(s)</span>
            <span><?= (int)$p['nb_membres'] ?> membre(s)</span>
            <?php if ($p['date_limite'] && $jours !== null): ?>
              <span style="color:<?= $jours<0?'var(--red)':($jours<7?'var(--orange)':'var(--text-muted)') ?>;">
                <?= $jours<0 ? 'Retard '.abs($jours).'j' : ($jours===0?'Echeance aujourd\'hui':$jours.'j restants') ?>
              </span>
            <?php endif; ?>
          </div>

          <div>
            <div style="display:flex;justify-content:space-between;font-size:.75rem;color:var(--text-muted);margin-bottom:5px;">
              <span>Avancement</span><span><?= $pct ?>%</span>
            </div>
            <div class="progress-bar"><div class="progress-fill" style="width:<?= $pct ?>%"></div></div>
          </div>

          <div style="display:flex;gap:8px;padding-top:4px;">
            <a href="view.php?id=<?= $p['id_projet'] ?>" class="btn btn-outline btn-sm" style="flex:1;justify-content:center;">Voir le projet</a>
            <?php if ($isChef): ?>
              <a href="edit.php?id=<?= $p['id_projet'] ?>" class="btn btn-outline btn-sm">Modifier</a>
              <a href="delete.php?id=<?= $p['id_projet'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Supprimer ce projet et toutes ses taches ?')">Suppr.</a>
            <?php endif; ?>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </main>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
