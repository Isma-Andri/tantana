<?php
$pageTitle = 'Tableau de bord - Tantana';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/helpers.php';
requireLogin();
requireRole('chef_projet');
$user = getCurrentUser();

try {
    $pdo = getDB();
    $stP = $pdo->prepare("SELECT COUNT(*) FROM projets WHERE id_chef=?"); $stP->execute([$user['id']]); $nb_projets = (int)$stP->fetchColumn();
    $stM = $pdo->query("SELECT COUNT(*) FROM users u JOIN roles r ON u.id_role=r.id_role WHERE r.libelle='membre'"); $nb_membres = (int)$stM->fetchColumn();
    $stT = $pdo->prepare("SELECT COUNT(*) FROM taches t JOIN projets p ON t.id_projet=p.id_projet WHERE p.id_chef=?"); $stT->execute([$user['id']]); $nb_taches = (int)$stT->fetchColumn();
    $stD = $pdo->prepare("SELECT COUNT(*) FROM taches t JOIN projets p ON t.id_projet=p.id_projet WHERE p.id_chef=? AND t.id_statut=3"); $stD->execute([$user['id']]); $nb_done = (int)$stD->fetchColumn();

    $stmt = $pdo->prepare("SELECT p.*,(SELECT COUNT(*) FROM taches t WHERE t.id_projet=p.id_projet) AS nb_t,(SELECT COUNT(*) FROM taches t WHERE t.id_projet=p.id_projet AND t.id_statut=3) AS nb_d,(SELECT COUNT(*) FROM participations pa WHERE pa.id_projet=p.id_projet) AS nb_m FROM projets p WHERE p.id_chef=? ORDER BY p.date_creation DESC LIMIT 4");
    $stmt->execute([$user['id']]);
    $projets_recents = $stmt->fetchAll();

    $stmt = $pdo->prepare("SELECT t.id_tache,t.nom,t.date_limite,p.nom AS projet_nom FROM taches t JOIN projets p ON t.id_projet=p.id_projet WHERE p.id_chef=? AND t.date_limite < CURDATE() AND t.id_statut != 3 ORDER BY t.date_limite ASC LIMIT 5");
    $stmt->execute([$user['id']]);
    $taches_retard = $stmt->fetchAll();

    $stmt = $pdo->prepare("SELECT a.*,u.nom AS user_nom,p.nom AS projet_nom FROM actions a JOIN users u ON a.id_user=u.id_user LEFT JOIN projets p ON a.id_projet=p.id_projet WHERE a.id_projet IN (SELECT id_projet FROM projets WHERE id_chef=?) OR a.id_user=? ORDER BY a.date_action DESC LIMIT 8");
    $stmt->execute([$user['id'], $user['id']]);
    $activites = $stmt->fetchAll();

    $dbErr = null;
} catch (PDOException $e) {
    error_log('[dashboard_chef] ' . $e->getMessage());
    $dbErr = $e->getMessage();
    $nb_projets = $nb_membres = $nb_taches = $nb_done = 0;
    $projets_recents = $taches_retard = $activites = [];
}
$pct_global = $nb_taches > 0 ? round($nb_done / $nb_taches * 100) : 0;

require_once __DIR__ . '/includes/header.php';
?>
<div class="app-layout">
  <?php require __DIR__ . '/includes/sidebar.php'; ?>
  <main class="main-content">
    <div class="page-header fade-up">
      <div style="display:flex;align-items:flex-start;justify-content:space-between;flex-wrap:wrap;gap:8px;">
        <div>
          <h1>Bonjour, <?= htmlspecialchars($user['nom']) ?></h1>
          <p>Vue d'ensemble de vos projets.</p>
        </div>
        <span class="badge badge-purple" style="padding:6px 14px;">Chef de projet</span>
      </div>
    </div>

    <?= flashHtml() ?>
    <?php if (isset($dbErr)): ?><div class="alert alert-error"><?= htmlspecialchars($dbErr) ?></div><?php endif; ?>

    <div class="stats-grid mb-24 fade-up delay-1">
      <div class="stat-card blue"><div class="stat-label">Projets</div><div class="stat-value"><?= $nb_projets ?></div><div class="stat-sub">Crees par vous</div></div>
      <div class="stat-card purple"><div class="stat-label">Membres</div><div class="stat-value"><?= $nb_membres ?></div><div class="stat-sub">Utilisateurs</div></div>
      <div class="stat-card orange"><div class="stat-label">Taches totales</div><div class="stat-value"><?= $nb_taches ?></div><div class="stat-sub"><?= $nb_done ?> terminees</div></div>
      <div class="stat-card green"><div class="stat-label">Progression</div><div class="stat-value"><?= $pct_global ?>%</div><div class="stat-sub">Taches terminees</div></div>
    </div>

    <?php if ($nb_taches > 0): ?>
    <div class="card mb-24 fade-up delay-1" style="padding:16px 20px;">
      <div style="display:flex;justify-content:space-between;font-size:.875rem;margin-bottom:8px;">
        <span style="font-weight:500;">Progression globale</span>
        <span style="color:var(--text-muted);"><?= $nb_done ?>/<?= $nb_taches ?> taches</span>
      </div>
      <div class="progress-bar" style="height:8px;"><div class="progress-fill" style="width:<?= $pct_global ?>%"></div></div>
    </div>
    <?php endif; ?>

    <div class="grid-2 fade-up delay-2" style="align-items:start;gap:20px;margin-bottom:20px;">
      <div style="display:flex;flex-direction:column;gap:0;">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;">
          <h2 style="font-family:var(--font-head);font-size:1.05rem;font-weight:600;">Projets recents</h2>
          <a href="projets/index.php" class="btn btn-outline btn-sm">Voir tous</a>
        </div>
        <?php if (empty($projets_recents)): ?>
          <div class="card" style="text-align:center;padding:32px;color:var(--text-muted);">
            <p style="margin-bottom:12px;">Aucun projet.</p>
            <a href="projets/create.php" class="btn btn-primary btn-sm">Creer un projet</a>
          </div>
        <?php else: ?>
          <div style="display:flex;flex-direction:column;gap:10px;">
            <?php foreach ($projets_recents as $p): $pct = $p['nb_t']>0?round($p['nb_d']/$p['nb_t']*100):0; ?>
            <div class="card" style="padding:14px 18px;">
              <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px;">
                <a href="projets/view.php?id=<?= $p['id_projet'] ?>" style="font-weight:600;font-size:.9rem;color:var(--text);"><?= htmlspecialchars($p['nom']) ?></a>
                <span style="font-size:.78rem;color:var(--text-muted);"><?= $p['nb_m'] ?> mbr &bull; <?= $p['nb_t'] ?> tch</span>
              </div>
              <div style="display:flex;align-items:center;gap:8px;">
                <div class="progress-bar" style="flex:1;"><div class="progress-fill" style="width:<?= $pct ?>%"></div></div>
                <span style="font-size:.75rem;color:var(--text-muted);width:30px;text-align:right;"><?= $pct ?>%</span>
              </div>
            </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>

      <div style="display:flex;flex-direction:column;gap:16px;">
        <?php if (!empty($taches_retard)): ?>
        <div class="card">
          <div class="card-header"><span class="card-title" style="color:var(--red);">Taches en retard (<?= count($taches_retard) ?>)</span></div>
          <div style="display:flex;flex-direction:column;gap:8px;">
            <?php foreach ($taches_retard as $t): $j=daysUntil($t['date_limite']); ?>
            <div style="display:flex;justify-content:space-between;align-items:center;padding:8px 12px;background:rgba(196,122,106,.07);border-radius:var(--radius-sm);border:1px solid rgba(196,122,106,.2);">
              <div>
                <a href="taches/view.php?id=<?= $t['id_tache'] ?>" style="font-size:.875rem;font-weight:500;color:var(--text);"><?= htmlspecialchars($t['nom']) ?></a>
                <div style="font-size:.75rem;color:var(--text-muted);"><?= htmlspecialchars($t['projet_nom']) ?></div>
              </div>
              <span style="font-size:.75rem;color:var(--red);white-space:nowrap;">+<?= abs($j) ?>j</span>
            </div>
            <?php endforeach; ?>
          </div>
        </div>
        <?php endif; ?>

        <div class="card">
          <div class="card-header">
            <span class="card-title">Activite recente</span>
            <a href="historique/index.php" style="font-size:.8rem;color:var(--accent-dark);">Tout voir</a>
          </div>
          <?php if (empty($activites)): ?>
            <p style="color:var(--text-muted);font-size:.875rem;">Aucune activite.</p>
          <?php else: ?>
            <div style="display:flex;flex-direction:column;gap:10px;">
              <?php foreach ($activites as $a): ?>
              <div style="display:flex;align-items:flex-start;gap:9px;">
                <div class="avatar" style="width:26px;height:26px;font-size:.65rem;flex-shrink:0;"><?= strtoupper(substr($a['user_nom'],0,1)) ?></div>
                <div style="flex:1;min-width:0;">
                  <div style="font-size:.82rem;line-height:1.4;"><strong><?= htmlspecialchars($a['user_nom']) ?></strong> <?= htmlspecialchars($a['description']) ?></div>
                  <div style="font-size:.72rem;color:var(--text-muted);"><?= fmtDatetime($a['date_action']) ?><?= $a['projet_nom']?' &bull; '.htmlspecialchars($a['projet_nom']):'' ?></div>
                </div>
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
