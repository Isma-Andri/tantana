<?php
$pageTitle = 'Mes taches - Tantana';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';
requireLogin();
$user = getCurrentUser();

$filtre = $_GET['statut'] ?? 'all';

try {
    $pdo = getDB();
    $sql = "
        SELECT t.*, p.nom AS projet_nom, s.libelle AS statut_lib, pr.libelle AS priorite_lib
        FROM affectations a
        JOIN taches t  ON a.id_tache=t.id_tache
        JOIN projets p ON t.id_projet=p.id_projet
        JOIN statuts s  ON t.id_statut=s.id_statut
        JOIN priorites pr ON t.id_priorite=pr.id_priorite
        WHERE a.id_user=?
    ";
    $params = [$user['id']];
    if ($filtre !== 'all') {
        $sql .= " AND s.libelle=?";
        $params[] = $filtre;
    }
    $sql .= " ORDER BY pr.id_priorite DESC, t.date_limite ASC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $taches = $stmt->fetchAll();
    $dbErr  = null;
} catch (PDOException $e) {
    error_log('[taches/mes_taches] ' . $e->getMessage());
    $taches = [];
    $dbErr  = $e->getMessage();
}

require_once __DIR__ . '/../includes/header.php';
?>
<div class="app-layout">
  <?php require __DIR__ . '/../includes/sidebar.php'; ?>
  <main class="main-content">
    <div class="page-header">
      <h1>Mes taches</h1>
      <p><?= count($taches) ?> tache(s) <?= $filtre!=='all' ? 'filtrée(s)' : '' ?></p>
    </div>

    <?= flashHtml() ?>
    <?php if (isset($dbErr)): ?><div class="alert alert-error"><?= htmlspecialchars($dbErr) ?></div><?php endif; ?>

    <!-- Filtres -->
    <div style="display:flex;gap:8px;margin-bottom:20px;flex-wrap:wrap;">
      <?php foreach (['all'=>'Toutes','a_faire'=>'A faire','en_cours'=>'En cours','termine'=>'Terminees','bloque'=>'Bloquees'] as $val=>$lab): ?>
        <a href="?statut=<?= $val ?>" class="btn btn-sm <?= $filtre===$val?'btn-primary':'btn-outline' ?>"><?= $lab ?></a>
      <?php endforeach; ?>
    </div>

    <?php if (empty($taches)): ?>
      <div class="card" style="text-align:center;padding:48px;color:var(--text-muted);">Aucune tache.</div>
    <?php else: ?>
      <div style="display:flex;flex-direction:column;gap:10px;">
        <?php foreach ($taches as $t):
          $j = daysUntil($t['date_limite']);
        ?>
        <div class="card" style="padding:16px 20px;">
          <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:12px;flex-wrap:wrap;">
            <div style="flex:1;min-width:0;">
              <div style="margin-bottom:4px;">
                <a href="view.php?id=<?= $t['id_tache'] ?>" style="font-weight:600;font-size:.95rem;color:var(--text);"><?= htmlspecialchars($t['nom']) ?></a>
              </div>
              <div style="font-size:.8rem;color:var(--text-muted);display:flex;gap:10px;flex-wrap:wrap;">
                <span>Projet : <a href="../projets/view.php?id=<?= $t['id_projet'] ?>"><?= htmlspecialchars($t['projet_nom']) ?></a></span>
                <?php if ($t['date_limite']): ?>
                  <span style="color:<?= $j!==null&&$j<3?'var(--red)':($j<7?'var(--orange)':'inherit') ?>;">
                    Limite : <?= fmtDate($t['date_limite']) ?>
                    <?= $j!==null ? '('.(($j<0)?'retard '.abs($j).'j':($j===0?'aujourd\'hui':$j.'j')).')' : '' ?>
                  </span>
                <?php endif; ?>
              </div>
            </div>
            <div style="display:flex;gap:6px;flex-shrink:0;flex-wrap:wrap;">
              <span class="badge <?= classBadgeStatut($t['statut_lib']) ?>"><?= labelStatut($t['statut_lib']) ?></span>
              <span class="badge <?= classBadgePriorite($t['priorite_lib']) ?>"><?= labelPriorite($t['priorite_lib']) ?></span>
            </div>
          </div>
          <div style="margin-top:10px;">
            <a href="view.php?id=<?= $t['id_tache'] ?>" class="btn btn-outline btn-sm">Voir la tache</a>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </main>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
