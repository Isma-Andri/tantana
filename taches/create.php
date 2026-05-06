<?php
$pageTitle = 'Nouvelle tache - Tantana';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';
requireLogin();
$user = getCurrentUser();

$id_projet = (int)($_GET['projet'] ?? $_POST['id_projet'] ?? 0);
if (!$id_projet || !canAccessProjet($id_projet, $user['id'])) {
    flashSet('error','Acces refuse.'); redirect('projets/index.php');
}

try {
    $pdo = getDB();
    $pjt = $pdo->prepare("SELECT * FROM projets WHERE id_projet=?");
    $pjt->execute([$id_projet]);
    $projet = $pjt->fetch();

    $statuts   = $pdo->query("SELECT * FROM statuts ORDER BY id_statut")->fetchAll();
    $priorites = $pdo->query("SELECT * FROM priorites ORDER BY id_priorite")->fetchAll();

    // Membres du projet pour affectation
    $stmt = $pdo->prepare("SELECT u.id_user,u.nom FROM participations pa JOIN users u ON pa.id_user=u.id_user WHERE pa.id_projet=? ORDER BY u.nom");
    $stmt->execute([$id_projet]);
    $membres = $stmt->fetchAll();

    // Taches parentes possibles
    $stmt = $pdo->prepare("SELECT id_tache,nom FROM taches WHERE id_projet=? AND id_parent IS NULL ORDER BY nom");
    $stmt->execute([$id_projet]);
    $taches_parentes = $stmt->fetchAll();

    $dbErr = null;
} catch (PDOException $e) {
    error_log('[taches/create] ' . $e->getMessage());
    flashSet('error', $e->getMessage()); redirect('projets/view.php?id='.$id_projet);
}

$errors = [];
$data   = ['nom'=>'','description'=>'','date_debut'=>'','date_fin'=>'','date_limite'=>'','id_statut'=>1,'id_priorite'=>2,'id_parent'=>'','assignes'=>[]];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'nom'         => trim($_POST['nom'] ?? ''),
        'description' => trim($_POST['description'] ?? ''),
        'date_debut'  => $_POST['date_debut'] ?? '',
        'date_fin'    => $_POST['date_fin'] ?? '',
        'date_limite' => $_POST['date_limite'] ?? '',
        'id_statut'   => (int)($_POST['id_statut'] ?? 1),
        'id_priorite' => (int)($_POST['id_priorite'] ?? 2),
        'id_parent'   => ($_POST['id_parent'] ?? '') ?: null,
        'assignes'    => $_POST['assignes'] ?? [],
    ];

    if (empty($data['nom'])) $errors[] = 'Le nom de la tache est obligatoire.';

    if (empty($errors)) {
        try {
            $pdo->beginTransaction();
            $stmt = $pdo->prepare("
                INSERT INTO taches (nom,description,date_debut,date_fin,date_limite,id_projet,id_statut,id_priorite,id_parent,id_createur)
                VALUES (?,?,?,?,?,?,?,?,?,?)
            ");
            $stmt->execute([
                $data['nom'], $data['description']?:null,
                $data['date_debut']?:null, $data['date_fin']?:null, $data['date_limite']?:null,
                $id_projet, $data['id_statut'], $data['id_priorite'],
                $data['id_parent']?:null, $user['id']
            ]);
            $id_tache = (int)$pdo->lastInsertId();

            // Affectations
            $stmtAff = $pdo->prepare("INSERT IGNORE INTO affectations (id_user,id_tache) VALUES (?,?)");
            foreach ($data['assignes'] as $uid) {
                $uid = (int)$uid;
                $stmtAff->execute([$uid, $id_tache]);
                // Notifier
                if ($uid !== $user['id']) {
                    $act = logAction("A assigne la tache \"{$data['nom']}\"", $id_projet, $id_tache);
                    notifier($uid, "Vous avez ete assigne a la tache \"{$data['nom']}\" dans le projet \"{$projet['nom']}\".", $act);
                }
            }
            $pdo->commit();

            logAction("A cree la tache \"{$data['nom']}\"", $id_projet, $id_tache);
            flashSet('success', 'Tache creee.');
            redirect('taches/view.php?id=' . $id_tache);
        } catch (PDOException $e) {
            $pdo->rollBack();
            $errors[] = 'Erreur : ' . $e->getMessage();
        }
    }
}

require_once __DIR__ . '/../includes/header.php';
?>
<div class="app-layout">
  <?php require __DIR__ . '/../includes/sidebar.php'; ?>
  <main class="main-content">
    <div class="page-header">
      <h1>Nouvelle tache</h1>
      <p>Projet : <a href="../projets/view.php?id=<?= $id_projet ?>"><?= htmlspecialchars($projet['nom']) ?></a></p>
    </div>

    <?php if ($errors): ?>
      <div class="alert alert-error">
        <ul style="margin:0;padding-left:16px;"><?php foreach ($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?></ul>
      </div>
    <?php endif; ?>

    <div class="card" style="max-width:720px;">
      <form method="POST">
        <input type="hidden" name="id_projet" value="<?= $id_projet ?>">
        <div class="form-group">
          <label class="form-label">Nom de la tache *</label>
          <input type="text" name="nom" class="form-control" required value="<?= htmlspecialchars($data['nom']) ?>">
        </div>
        <div class="form-group">
          <label class="form-label">Description</label>
          <textarea name="description" class="form-control" rows="3" style="resize:vertical;"><?= htmlspecialchars($data['description']) ?></textarea>
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
          <div class="form-group">
            <label class="form-label">Statut</label>
            <select name="id_statut" class="form-control">
              <?php foreach ($statuts as $s): ?>
                <option value="<?= $s['id_statut'] ?>" <?= $data['id_statut']==$s['id_statut']?'selected':'' ?>><?= labelStatut($s['libelle']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-group">
            <label class="form-label">Priorite</label>
            <select name="id_priorite" class="form-control">
              <?php foreach ($priorites as $p): ?>
                <option value="<?= $p['id_priorite'] ?>" <?= $data['id_priorite']==$p['id_priorite']?'selected':'' ?>><?= labelPriorite($p['libelle']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:12px;">
          <div class="form-group">
            <label class="form-label">Date debut</label>
            <input type="date" name="date_debut" class="form-control" value="<?= $data['date_debut'] ?>">
          </div>
          <div class="form-group">
            <label class="form-label">Date fin</label>
            <input type="date" name="date_fin" class="form-control" value="<?= $data['date_fin'] ?>">
          </div>
          <div class="form-group">
            <label class="form-label">Date limite</label>
            <input type="date" name="date_limite" class="form-control" value="<?= $data['date_limite'] ?>">
          </div>
        </div>
        <?php if (!empty($taches_parentes)): ?>
        <div class="form-group">
          <label class="form-label">Sous-tache de</label>
          <select name="id_parent" class="form-control">
            <option value="">-- Aucune (tache principale) --</option>
            <?php foreach ($taches_parentes as $tp): ?>
              <option value="<?= $tp['id_tache'] ?>" <?= $data['id_parent']==$tp['id_tache']?'selected':'' ?>><?= htmlspecialchars($tp['nom']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <?php endif; ?>
        <?php if (!empty($membres)): ?>
        <div class="form-group">
          <label class="form-label">Assigner a</label>
          <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(160px,1fr));gap:8px;background:var(--bg2);padding:12px;border-radius:var(--radius-sm);border:1px solid var(--border);">
            <?php foreach ($membres as $m): ?>
              <label style="display:flex;align-items:center;gap:8px;font-size:.875rem;cursor:pointer;">
                <input type="checkbox" name="assignes[]" value="<?= $m['id_user'] ?>"
                  <?= in_array($m['id_user'], $data['assignes'])?'checked':'' ?>>
                <?= htmlspecialchars($m['nom']) ?>
              </label>
            <?php endforeach; ?>
          </div>
        </div>
        <?php endif; ?>
        <div style="display:flex;gap:10px;margin-top:8px;">
          <button type="submit" class="btn btn-primary">Creer la tache</button>
          <a href="../projets/view.php?id=<?= $id_projet ?>" class="btn btn-outline">Annuler</a>
        </div>
      </form>
    </div>
  </main>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
