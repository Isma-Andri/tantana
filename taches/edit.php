<?php
$pageTitle = 'Modifier tache - Tantana';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';
requireLogin();
$user = getCurrentUser();

$id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
if (!$id) { flashSet('error','Tache introuvable.'); redirect('projets/index.php'); }

try {
    $pdo  = getDB();
    $stmt = $pdo->prepare("SELECT t.*,p.id_chef FROM taches t JOIN projets p ON t.id_projet=p.id_projet WHERE t.id_tache=?");
    $stmt->execute([$id]);
    $tache = $stmt->fetch();
    if (!$tache) { flashSet('error','Tache introuvable.'); redirect('projets/index.php'); }
    if (!canAccessProjet($tache['id_projet'], $user['id'])) { flashSet('error','Acces refuse.'); redirect('projets/index.php'); }

    $canEdit = ($tache['id_chef']==$user['id'] || $tache['id_createur']==$user['id']);
    if (!$canEdit) { flashSet('error','Vous ne pouvez pas modifier cette tache.'); redirect('taches/view.php?id='.$id); }

    $statuts   = $pdo->query("SELECT * FROM statuts")->fetchAll();
    $priorites = $pdo->query("SELECT * FROM priorites")->fetchAll();
    $stmt = $pdo->prepare("SELECT u.id_user,u.nom FROM participations pa JOIN users u ON pa.id_user=u.id_user WHERE pa.id_projet=? ORDER BY u.nom");
    $stmt->execute([$tache['id_projet']]);
    $membres = $stmt->fetchAll();

    $stmt = $pdo->prepare("SELECT id_user FROM affectations WHERE id_tache=?");
    $stmt->execute([$id]);
    $assignes_ids = array_column($stmt->fetchAll(), 'id_user');
} catch (PDOException $e) {
    flashSet('error', $e->getMessage()); redirect('projets/index.php');
}

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom         = trim($_POST['nom'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $date_debut  = $_POST['date_debut'] ?? '';
    $date_fin    = $_POST['date_fin'] ?? '';
    $date_limite = $_POST['date_limite'] ?? '';
    $id_statut   = (int)($_POST['id_statut'] ?? 1);
    $id_priorite = (int)($_POST['id_priorite'] ?? 2);
    $assignes    = $_POST['assignes'] ?? [];

    if (empty($nom)) $errors[] = 'Nom obligatoire.';

    if (empty($errors)) {
        try {
            $pdo->beginTransaction();
            $pdo->prepare("UPDATE taches SET nom=?,description=?,date_debut=?,date_fin=?,date_limite=?,id_statut=?,id_priorite=? WHERE id_tache=?")
                ->execute([$nom, $description?:null, $date_debut?:null, $date_fin?:null, $date_limite?:null, $id_statut, $id_priorite, $id]);

            // Recalcule affectations
            $pdo->prepare("DELETE FROM affectations WHERE id_tache=?")->execute([$id]);
            $stmtAff = $pdo->prepare("INSERT IGNORE INTO affectations (id_user,id_tache) VALUES (?,?)");
            foreach ($assignes as $uid) { $stmtAff->execute([(int)$uid, $id]); }

            $pdo->commit();
            logAction("A modifie la tache \"$nom\"", $tache['id_projet'], $id);
            flashSet('success','Tache mise a jour.');
            redirect('taches/view.php?id='.$id);
        } catch (PDOException $e) {
            $pdo->rollBack();
            $errors[] = $e->getMessage();
        }
    }
    $tache = array_merge($tache, compact('nom','description','date_debut','date_fin','date_limite','id_statut','id_priorite'));
    $assignes_ids = array_map('intval', $assignes);
}

require_once __DIR__ . '/../includes/header.php';
?>
<div class="app-layout">
  <?php require __DIR__ . '/../includes/sidebar.php'; ?>
  <main class="main-content">
    <div class="page-header">
      <h1>Modifier la tache</h1>
      <p><a href="view.php?id=<?= $id ?>">Retour a la tache</a></p>
    </div>
    <?php if ($errors): ?><div class="alert alert-error"><ul style="margin:0;padding-left:16px;"><?php foreach ($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?></ul></div><?php endif; ?>
    <div class="card" style="max-width:720px;">
      <form method="POST">
        <input type="hidden" name="id" value="<?= $id ?>">
        <div class="form-group">
          <label class="form-label">Nom *</label>
          <input type="text" name="nom" class="form-control" required value="<?= htmlspecialchars($tache['nom']) ?>">
        </div>
        <div class="form-group">
          <label class="form-label">Description</label>
          <textarea name="description" class="form-control" rows="3" style="resize:vertical;"><?= htmlspecialchars($tache['description']??'') ?></textarea>
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
          <div class="form-group">
            <label class="form-label">Statut</label>
            <select name="id_statut" class="form-control">
              <?php foreach ($statuts as $s): ?><option value="<?= $s['id_statut'] ?>" <?= $tache['id_statut']==$s['id_statut']?'selected':'' ?>><?= labelStatut($s['libelle']) ?></option><?php endforeach; ?>
            </select>
          </div>
          <div class="form-group">
            <label class="form-label">Priorite</label>
            <select name="id_priorite" class="form-control">
              <?php foreach ($priorites as $p): ?><option value="<?= $p['id_priorite'] ?>" <?= $tache['id_priorite']==$p['id_priorite']?'selected':'' ?>><?= labelPriorite($p['libelle']) ?></option><?php endforeach; ?>
            </select>
          </div>
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:12px;">
          <div class="form-group"><label class="form-label">Date debut</label><input type="date" name="date_debut" class="form-control" value="<?= htmlspecialchars($tache['date_debut']??'') ?>"></div>
          <div class="form-group"><label class="form-label">Date fin</label><input type="date" name="date_fin" class="form-control" value="<?= htmlspecialchars($tache['date_fin']??'') ?>"></div>
          <div class="form-group"><label class="form-label">Date limite</label><input type="date" name="date_limite" class="form-control" value="<?= htmlspecialchars($tache['date_limite']??'') ?>"></div>
        </div>
        <?php if (!empty($membres)): ?>
        <div class="form-group">
          <label class="form-label">Assignes</label>
          <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(160px,1fr));gap:8px;background:var(--bg2);padding:12px;border-radius:var(--radius-sm);border:1px solid var(--border);">
            <?php foreach ($membres as $m): ?>
              <label style="display:flex;align-items:center;gap:8px;font-size:.875rem;cursor:pointer;">
                <input type="checkbox" name="assignes[]" value="<?= $m['id_user'] ?>" <?= in_array($m['id_user'],$assignes_ids)?'checked':'' ?>>
                <?= htmlspecialchars($m['nom']) ?>
              </label>
            <?php endforeach; ?>
          </div>
        </div>
        <?php endif; ?>
        <div style="display:flex;gap:10px;margin-top:8px;">
          <button type="submit" class="btn btn-primary">Enregistrer</button>
          <a href="view.php?id=<?= $id ?>" class="btn btn-outline">Annuler</a>
        </div>
      </form>
    </div>
  </main>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
