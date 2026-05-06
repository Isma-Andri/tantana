<?php
$pageTitle = 'Tableau de bord - Tantana';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/helpers.php';
requireLogin();
requireRole('membre');
$user = getCurrentUser();

try {
    $pdo = getDB();

    $stP = $pdo->prepare("SELECT COUNT(*) FROM participations WHERE id_user=?"); $stP->execute([$user['id']]); $nb_projets = (int)$stP->fetchColumn();
    $stT = $pdo->prepare("SELECT COUNT(*) FROM affectations a JOIN taches t ON a.id_tache=t.id_tache WHERE a.id_user=?"); $stT->execute([$user['id']]); $nb_taches = (int)$stT->fetchColumn();
    $stD = $pdo->prepare("SELECT COUNT(*) FROM affectations a JOIN taches t ON a.id_tache=t.id_tache WHERE a.id_user=? AND t.id_statut=3"); $stD->execute([$user['id']]); $nb_done = (int)$stD->fetchColumn();
    $stN = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE id_user=? AND est_lue=0"); $stN->execute([$user['id']]); $nb_notifs = (int)$stN->fetchColumn();

    // Mes taches récentes
    $stmt = $pdo->prepare("
        SELECT t.*,p.nom AS projet_nom,s.libelle AS statut_lib,pr.libelle AS priorite_lib
        FROM affectations a
        JOIN taches t ON a.id_tache=t.id_tache
        JOIN projets p ON t.id_projet=p.id_projet
        JOIN statuts s ON t.id_statut=s.id_statut
        JOIN priorites pr ON t.id_priorite=pr.id_priorite
        WHERE a.id_user=? AND t.id_statut != 3
        ORDER BY pr.id_priorite DESC, t.date_limite ASC
        LIMIT 6
    ");
    $stmt->execute([$user['id']]);
    $mes_taches = $stmt->fetchAll();

    // Mes projets
    $stmt = $pdo->prepare("
        SELECT p.*,(SELECT COUNT(*) FROM taches t WHERE t.id_projet=p.id_projet AND t.id_statut=3) AS nb_d,(SELECT COUNT(*) FROM taches t WHERE t.id_projet=p.id_projet) AS nb_t
        FROM participations pa
        JOIN projets p ON pa.id_projet=p.id_projet
        WHERE pa.id_user=?
        ORDER BY p.date_creation DESC LIMIT 4
    ");
    $stmt->execute([$user['id']]);
    $mes_projets = $stmt->fetchAll();

    // Activité récente perso
    $stmt = $pdo->prepare("SELECT a.*,p.nom AS projet_nom FROM actions a LEFT JOIN projets p ON a.id_projet=p.id_projet WHERE a.id_user=? ORDER BY a.date_action DESC LIMIT 6");
    $stmt->execute([$user['id']]);
    $activites = $stmt->fetchAll();

    $dbErr = null;
} catch (PDOException $e) {
    error_log('[dashboard_membre] ' . $e->getMessage());
    $dbErr = $e->getMessage();
    $nb_projets = $nb_taches = $nb_done = $nb_notifs = 0;
    $mes_taches = $mes_projets = $activites = [];
}

require_once __DIR__ . '/includes/header.php';
?>
<div class="app-layout">
  <?php require __DIR__ . '/includes/sidebar.php'; ?>
  <main class="main-content">
    <div class="page-header fade-up">
      <div style="display:flex;align-items:flex-start;justify-content:space-between;flex-wrap:wrap;gap:8px;">
        <div>
          <h1>Bonjour, <?= htmlspecialchars($user['nom']) ?></h1>
          <p>Votre espace de travail.</p>
        </div>
        <span class="badge badge-blue" style="padding:6px 14px;">Membre</span>
      </div>
    </div>

    <?= flashHtml() ?>
    <?php if (isset($dbErr)): ?><div class="alert alert-error"><?= htmlspecialchars($dbErr) ?></div><?php endif; ?>

    <div class="stats-grid mb-24 fade-up delay-1">
      <div class="stat-card blue"><div class="stat-label">Projets</div><div class="stat-value"><?= $nb_projets ?></div><div class="stat-sub">Participations</div></div>
      <div class="stat-card orange"><div class="stat-label">Taches</div><div class="stat-value"><?= $nb_taches ?></div><div class="stat-sub">Assignees</div></div>
      <div class="stat-card green"><div class="stat-label">Terminees</div><div class="stat-value"><?= $nb_done ?></div><div class="stat-sub">Taches closes</div></div>
      <div class="stat-card purple"><div class="stat-label">Notifications</div><div class="stat-value"><?= $nb_notifs ?></div><div class="stat-sub">Non lues</div></div>
    </div>

    <div class="grid-2 fade-up delay-2" style="align-items:start;gap:20px;margin-bottom:20px;">
      <!-- Mes taches -->
      <div>
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;">
          <h2 style="font-family:var(--font-head);font-size:1.05rem;font-weight:600;">Mes taches en cours</h2>
          <a href="taches/mes_taches.php" class="btn btn-outline btn-sm">Voir toutes</a>
        </div>
        <?php if (empty($mes_taches)): ?>
          <div class="card" style="text-align:center;padding:32px;color:var(--text-muted);">Aucune tache assignee.</div>
        <?php else: ?>
          <div style="display:flex;flex-direction:column;gap:10px;">
            <?php foreach ($mes_taches as $t): $j = daysUntil($t['date_limite']); ?>
            <div class="card" style="padding:14px 18px;">
              <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:8px;margin-bottom:5px;">
                <a href="taches/view.php?id=<?= $t['id_tache'] ?>" style="font-weight:600;font-size:.875rem;color:var(--text);"><?= htmlspecialchars($t['nom']) ?></a>
                <div style="display:flex;gap:5px;flex-shrink:0;">
                  <span class="badge <?= classBadgeStatut($t['statut_lib']) ?>"><?= labelStatut($t['statut_lib']) ?></span>
                  <span class="badge <?= classBadgePriorite($t['priorite_lib']) ?>"><?= labelPriorite($t['priorite_lib']) ?></span>
                </div>
              </div>
              <div style="font-size:.78rem;color:var(--text-muted);display:flex;gap:10px;flex-wrap:wrap;">
                <span><?= htmlspecialchars($t['projet_nom']) ?></span>
                <?php if ($t['date_limite'] && $j!==null): ?>
                  <span style="color:<?= $j<0?'var(--red)':($j<3?'var(--orange)':'inherit') ?>;">
                    <?= $j<0?'Retard '.abs($j).'j':($j===0?"Auj.".$j.'j':$j.'j') ?>
                  </span>
                <?php endif; ?>
              </div>
            </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>

      <!-- Mes projets + activite -->
      <div style="display:flex;flex-direction:column;gap:16px;">
        <div class="card">
          <div class="card-header">
            <span class="card-title">Mes projets</span>
            <a href="projets/index.php" style="font-size:.8rem;color:var(--accent-dark);">Voir tous</a>
          </div>
          <?php if (empty($mes_projets)): ?>
            <p style="color:var(--text-muted);font-size:.875rem;">Aucun projet.</p>
          <?php else: ?>
            <div style="display:flex;flex-direction:column;gap:10px;">
              <?php foreach ($mes_projets as $p): $pct=$p['nb_t']>0?round($p['nb_d']/$p['nb_t']*100):0; ?>
              <div>
                <div style="display:flex;justify-content:space-between;margin-bottom:5px;">
                  <a href="projets/view.php?id=<?= $p['id_projet'] ?>" style="font-size:.875rem;font-weight:500;color:var(--text);"><?= htmlspecialchars($p['nom']) ?></a>
                  <span style="font-size:.75rem;color:var(--text-muted);"><?= $pct ?>%</span>
                </div>
                <div class="progress-bar"><div class="progress-fill" style="width:<?= $pct ?>%"></div></div>
              </div>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
        </div>

        <div class="card">
          <div class="card-header">
            <span class="card-title">Mon activite recente</span>
            <a href="historique/index.php" style="font-size:.8rem;color:var(--accent-dark);">Tout voir</a>
          </div>
          <?php if (empty($activites)): ?>
            <p style="color:var(--text-muted);font-size:.875rem;">Aucune activite.</p>
          <?php else: ?>
            <div style="display:flex;flex-direction:column;gap:8px;">
              <?php foreach ($activites as $a): ?>
              <div style="font-size:.82rem;padding:7px 0;border-bottom:1px solid var(--border);line-height:1.4;">
                <?= htmlspecialchars($a['description']) ?>
                <div style="font-size:.72rem;color:var(--text-muted);margin-top:2px;"><?= fmtDatetime($a['date_action']) ?><?= $a['projet_nom']?' &bull; '.htmlspecialchars($a['projet_nom']):'' ?></div>
              </div>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </div>

  </main>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
