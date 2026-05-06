<?php
$pageTitle = 'Modifier projet - Tantana';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';
requireLogin();
requireRole('chef_projet');
$user = getCurrentUser();

$id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
if (!$id || !isChefProjet($id, $user['id'])) { flashSet('error','Acces refuse.'); redirect('projets/index.php'); }

try {
    $pdo    = getDB();
    $stmt   = $pdo->prepare("SELECT * FROM projets WHERE id_projet=?");
    $stmt->execute([$id]);
    $projet = $stmt->fetch();
    if (!$projet) { flashSet('error','Projet introuvable.'); redirect('projets/index.php'); }
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

    if (empty($nom)) $errors[] = 'Le nom est obligatoire.';
    if ($date_debut && $date_fin && $date_fin < $date_debut) $errors[] = 'Date de fin invalide.';

    if (empty($errors)) {
        try {
            $pdo->prepare("UPDATE projets SET nom=?,description=?,date_debut=?,date_fin=?,date_limite=? WHERE id_projet=?")
                ->execute([$nom, $description?:null, $date_debut?:null, $date_fin?:null, $date_limite?:null, $id]);
            logAction("A modifie le projet \"$nom\"", $id);
            flashSet('success', 'Projet mis a jour.');
            redirect('projets/view.php?id=' . $id);
        } catch (PDOException $e) {
            $errors[] = $e->getMessage();
        }
    }
    $projet = array_merge($projet, compact('nom','description','date_debut','date_fin','date_limite'));
}

require_once __DIR__ . '/../includes/header.php';
?>
<div class="app-layout">
  <?php require __DIR__ . '/../includes/sidebar.php'; ?>
  <main class="main-content">
    <div class="page-header">
      <h1>Modifier le projet</h1>
      <p><a href="view.php?id=<?= $id ?>">Retour au projet</a></p>
    </div>

    <?php if ($errors): ?>
      <div class="alert alert-error">
        <ul style="margin:0;padding-left:16px;"><?php foreach ($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?></ul>
      </div>
    <?php endif; ?>

    <div class="card" style="max-width:680px;">
      <form method="POST">
        <input type="hidden" name="id" value="<?= $id ?>">
        <div class="form-group">
          <label class="form-label">Nom *</label>
          <input type="text" name="nom" class="form-control" required value="<?= htmlspecialchars($projet['nom']) ?>">
        </div>
        <div class="form-group">
          <label class="form-label">Description</label>
          <textarea name="description" class="form-control" rows="4" style="resize:vertical;"><?= htmlspecialchars($projet['description'] ?? '') ?></textarea>
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:12px;">
          <div class="form-group">
            <label class="form-label">Date de debut</label>
            <input type="date" name="date_debut" class="form-control" value="<?= htmlspecialchars($projet['date_debut'] ?? '') ?>">
          </div>
          <div class="form-group">
            <label class="form-label">Date de fin</label>
            <input type="date" name="date_fin" class="form-control" value="<?= htmlspecialchars($projet['date_fin'] ?? '') ?>">
          </div>
          <div class="form-group">
            <label class="form-label">Date limite</label>
            <input type="date" name="date_limite" class="form-control" value="<?= htmlspecialchars($projet['date_limite'] ?? '') ?>">
          </div>
        </div>
        <div style="display:flex;gap:10px;margin-top:8px;">
          <button type="submit" class="btn btn-primary">Enregistrer</button>
          <a href="view.php?id=<?= $id ?>" class="btn btn-outline">Annuler</a>
        </div>
      </form>
    </div>
  </main>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
