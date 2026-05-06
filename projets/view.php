<?php
$pageTitle = 'Projet - Tantana';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';
requireLogin();
$user = getCurrentUser();

$id = (int)($_GET['id'] ?? 0);
if (!$id) { flashSet('error','Projet introuvable.'); redirect('projets/index.php'); }
if (!canAccessProjet($id, $user['id'])) { flashSet('error','Acces refuse.'); redirect('projets/index.php'); }

try {
    $pdo = getDB();

    // Projet
    $stmt = $pdo->prepare("SELECT p.*,u.nom AS chef_nom FROM projets p JOIN users u ON p.id_chef=u.id_user WHERE p.id_projet=?");
    $stmt->execute([$id]);
    $projet = $stmt->fetch();
    if (!$projet) { flashSet('error','Projet introuvable.'); redirect('projets/index.php'); }
    $pageTitle = htmlspecialchars($projet['nom']) . ' - Tantana';

    // Taches du projet
    $stmt = $pdo->prepare("
        SELECT t.*,
               s.libelle AS statut_lib, pr.libelle AS priorite_lib,
               u.nom AS createur_nom,
               GROUP_CONCAT(DISTINCT au.nom ORDER BY au.nom SEPARATOR ', ') AS assignes
        FROM taches t
        JOIN statuts s   ON t.id_statut=s.id_statut
        JOIN priorites pr ON t.id_priorite=pr.id_priorite
        JOIN users u      ON t.id_createur=u.id_user
        LEFT JOIN affectations a ON a.id_tache=t.id_tache
        LEFT JOIN users au ON au.id_user=a.id_user
        WHERE t.id_projet=? AND t.id_parent IS NULL
        GROUP BY t.id_tache
        ORDER BY pr.id_priorite DESC, t.date_limite ASC
    ");
    $stmt->execute([$id]);
    $taches = $stmt->fetchAll();

    // Membres
    $stmt = $pdo->prepare("
        SELECT u.id_user, u.nom, u.email, r.libelle AS role,
               (SELECT COUNT(*) FROM affectations a2 JOIN taches t2 ON a2.id_tache=t2.id_tache WHERE a2.id_user=u.id_user AND t2.id_projet=?) AS nb_taches
        FROM participations pa
        JOIN users u ON pa.id_user=u.id_user
        JOIN roles r ON u.id_role=r.id_role
        WHERE pa.id_projet=?
        ORDER BY u.nom
    ");
    $stmt->execute([$id, $id]);
    $membres = $stmt->fetchAll();

    // Stats
    $total     = count($taches);
    $terminees = count(array_filter($taches, fn($t) => $t['id_statut']==3));
    $en_cours  = count(array_filter($taches, fn($t) => $t['id_statut']==2));
    $pct       = $total > 0 ? round($terminees/$total*100) : 0;

    $dbErr = null;
} catch (PDOException $e) {
    error_log('[projets/view] ' . $e->getMessage());
    $dbErr = $e->getMessage();
    $projet = $taches = $membres = [];
}

$isChef = !empty($projet) && $projet['id_chef'] == $user['id'];

require_once __DIR__ . '/../includes/header.php';
?>
<div class="app-layout">
  <?php require __DIR__ . '/../includes/sidebar.php'; ?>
  <main class="main-content">

    <?= flashHtml() ?>
    <?php if (isset($dbErr)): ?><div class="alert alert-error"><?= htmlspecialchars($dbErr) ?></div><?php endif; ?>

    <!-- En-tete projet -->
    <div style="display:flex;align-items:flex-start;justify-content:space-between;flex-wrap:wrap;gap:12px;margin-bottom:28px;">
      <div>
        <div style="font-size:.8rem;color:var(--text-muted);margin-bottom:6px;">
          <a href="index.php" style="color:var(--text-muted);">Projets</a> / <?= htmlspecialchars($projet['nom']) ?>
        </div>
        <h1 style="font-family:var(--font-head);font-size:1.8rem;font-weight:600;margin-bottom:6px;"><?= htmlspecialchars($projet['nom']) ?></h1>
        <div style="font-size:.85rem;color:var(--text-muted);display:flex;gap:16px;flex-wrap:wrap;">
          <span>Chef : <strong><?= htmlspecialchars($projet['chef_nom']) ?></strong></span>
          <?php if ($projet['date_debut']): ?><span>Debut : <?= fmtDate($projet['date_debut']) ?></span><?php endif; ?>
          <?php if ($projet['date_fin']): ?><span>Fin : <?= fmtDate($projet['date_fin']) ?></span><?php endif; ?>
          <?php if ($projet['date_limite']): ?>
            <?php $j = daysUntil($projet['date_limite']); ?>
            <span style="color:<?= $j!==null&&$j<7?'var(--red)':'inherit' ?>;">
              Limite : <?= fmtDate($projet['date_limite']) ?>
              <?php if ($j!==null): ?>(<?= $j<0?'retard '.abs($j).'j':($j===0?'aujourd\'hui':$j.'j') ?>)<?php endif; ?>
            </span>
          <?php endif; ?>
        </div>
        <?php if ($projet['description']): ?>
          <p style="margin-top:10px;font-size:.9rem;color:var(--text-dim);max-width:600px;line-height:1.6;"><?= nl2br(htmlspecialchars($projet['description'])) ?></p>
        <?php endif; ?>
      </div>
      <div style="display:flex;gap:8px;">
        <?php if ($isChef): ?>
          <a href="edit.php?id=<?= $id ?>" class="btn btn-outline btn-sm">Modifier</a>
          <a href="../membres/gerer.php?projet=<?= $id ?>" class="btn btn-outline btn-sm">Gerer membres</a>
        <?php endif; ?>
        <a href="../taches/create.php?projet=<?= $id ?>" class="btn btn-primary btn-sm">+ Tache</a>
      </div>
    </div>

    <!-- Stats -->
    <div class="stats-grid mb-24" style="grid-template-columns:repeat(4,1fr);">
      <div class="stat-card blue">
        <div class="stat-label">Total taches</div>
        <div class="stat-value"><?= $total ?></div>
      </div>
      <div class="stat-card green">
        <div class="stat-label">Terminees</div>
        <div class="stat-value"><?= $terminees ?></div>
      </div>
      <div class="stat-card orange">
        <div class="stat-label">En cours</div>
        <div class="stat-value"><?= $en_cours ?></div>
      </div>
      <div class="stat-card purple">
        <div class="stat-label">Membres</div>
        <div class="stat-value"><?= count($membres) ?></div>
      </div>
    </div>

    <!-- Avancement global -->
    <div class="card mb-24">
      <div style="display:flex;justify-content:space-between;margin-bottom:8px;font-size:.875rem;">
        <span style="font-weight:600;">Avancement global</span>
        <span style="color:var(--text-muted);"><?= $pct ?>%</span>
      </div>
      <div class="progress-bar" style="height:8px;"><div class="progress-fill" style="width:<?= $pct ?>%"></div></div>
    </div>

    <div class="grid-2" style="align-items:start;">
      <!-- TACHES -->
      <div style="display:flex;flex-direction:column;gap:12px;">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:4px;">
          <h2 style="font-family:var(--font-head);font-size:1.1rem;font-weight:600;">Taches (<?= count($taches) ?>)</h2>
          <a href="../taches/create.php?projet=<?= $id ?>" class="btn btn-primary btn-sm">+ Ajouter</a>
        </div>

        <?php if (empty($taches)): ?>
          <div class="card" style="text-align:center;padding:32px;color:var(--text-muted);">Aucune tache.</div>
        <?php else: ?>
          <?php foreach ($taches as $t): ?>
          <div class="card" style="padding:16px 20px;<?= $t['id_statut']==3?'opacity:.7;':'' ?>">
            <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:8px;margin-bottom:6px;">
              <a href="../taches/view.php?id=<?= $t['id_tache'] ?>" style="font-weight:600;font-size:.95rem;color:var(--text);text-decoration:none;">
                <?= htmlspecialchars($t['nom']) ?>
              </a>
              <div style="display:flex;gap:6px;flex-shrink:0;">
                <span class="badge <?= classBadgeStatut($t['statut_lib']) ?>"><?= labelStatut($t['statut_lib']) ?></span>
                <span class="badge <?= classBadgePriorite($t['priorite_lib']) ?>"><?= labelPriorite($t['priorite_lib']) ?></span>
              </div>
            </div>
            <div style="font-size:.8rem;color:var(--text-muted);display:flex;gap:12px;flex-wrap:wrap;">
              <?php if ($t['assignes']): ?><span>Assignes : <?= htmlspecialchars($t['assignes']) ?></span><?php endif; ?>
              <?php if ($t['date_limite']): ?>
                <?php $j=daysUntil($t['date_limite']); ?>
                <span style="color:<?= $j!==null&&$j<3?'var(--red)':'inherit' ?>;">Limite : <?= fmtDate($t['date_limite']) ?></span>
              <?php endif; ?>
            </div>
            <div style="display:flex;gap:6px;margin-top:10px;">
              <a href="../taches/view.php?id=<?= $t['id_tache'] ?>" class="btn btn-outline btn-sm">Voir</a>
              <?php if ($isChef || $t['id_createur']==$user['id']): ?>
                <a href="../taches/edit.php?id=<?= $t['id_tache'] ?>" class="btn btn-outline btn-sm">Modifier</a>
                <a href="../taches/delete.php?id=<?= $t['id_tache'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Supprimer ?')">Suppr.</a>
              <?php endif; ?>
            </div>
          </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>

      <!-- MEMBRES -->
      <div class="card">
        <div class="card-header">
          <span class="card-title">Membres (<?= count($membres) ?>)</span>
          <?php if ($isChef): ?>
            <a href="../membres/gerer.php?projet=<?= $id ?>" class="btn btn-outline btn-sm">Gerer</a>
          <?php endif; ?>
        </div>
        <?php if (empty($membres)): ?>
          <p style="color:var(--text-muted);font-size:.875rem;">Aucun membre.</p>
        <?php else: ?>
          <div style="display:flex;flex-direction:column;gap:10px;">
            <?php foreach ($membres as $m): ?>
            <div style="display:flex;align-items:center;gap:10px;">
              <div class="avatar" style="width:32px;height:32px;font-size:.8rem;"><?= strtoupper(substr($m['nom'],0,1)) ?></div>
              <div style="flex:1;">
                <div style="font-size:.875rem;font-weight:600;">
                  <?= htmlspecialchars($m['nom']) ?>
                  <?php if ($m['id_user']==$projet['id_chef']): ?><span class="badge badge-purple" style="margin-left:4px;">Chef</span><?php endif; ?>
                </div>
                <div style="font-size:.78rem;color:var(--text-muted);"><?= (int)$m['nb_taches'] ?> tache(s) assignee(s)</div>
              </div>
            </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>
    </div>

  </main>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
