<?php
$pageTitle = 'Nouveau projet - Tantana';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';
requireLogin();
requireRole('chef_projet');
$user = getCurrentUser();

$errors = [];
$data   = ['nom'=>'','description'=>'','date_debut'=>'','date_fin'=>'','date_limite'=>''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'nom'          => trim($_POST['nom'] ?? ''),
        'description'  => trim($_POST['description'] ?? ''),
        'date_debut'   => $_POST['date_debut'] ?? '',
        'date_fin'     => $_POST['date_fin'] ?? '',
        'date_limite'  => $_POST['date_limite'] ?? '',
    ];

    if (empty($data['nom']))         $errors[] = 'Le nom du projet est obligatoire.';
    if (strlen($data['nom']) > 150)  $errors[] = 'Nom trop long (150 car. max).';
    if ($data['date_debut'] && $data['date_fin'] && $data['date_fin'] < $data['date_debut'])
        $errors[] = 'La date de fin doit etre apres la date de debut.';

    if (empty($errors)) {
        try {
            $pdo  = getDB();
            $stmt = $pdo->prepare("
                INSERT INTO projets (nom,description,date_debut,date_fin,date_limite,id_chef)
                VALUES (?,?,?,?,?,?)
            ");
            $stmt->execute([
                $data['nom'],
                $data['description'] ?: null,
                $data['date_debut'] ?: null,
                $data['date_fin']   ?: null,
                $data['date_limite']?: null,
                $user['id'],
            ]);
            $id = (int)$pdo->lastInsertId();

            // Chef est aussi participant
            $pdo->prepare("INSERT IGNORE INTO participations (id_user,id_projet) VALUES (?,?)")
                ->execute([$user['id'], $id]);

            logAction("A cree le projet \"{$data['nom']}\"", $id);
            flashSet('success', 'Projet cree avec succes.');
            redirect('projets/view.php?id=' . $id);
        } catch (PDOException $e) {
            error_log('[projets/create] ' . $e->getMessage());
            $errors[] = 'Erreur base de donnees : ' . $e->getMessage();
        }
    }
}

require_once __DIR__ . '/../includes/header.php';
?>
<div class="app-layout">
  <?php require __DIR__ . '/../includes/sidebar.php'; ?>
  <main class="main-content">
    <div class="page-header">
      <h1>Nouveau projet</h1>
      <p>Remplissez les informations pour creer un projet.</p>
    </div>

    <?php if ($errors): ?>
      <div class="alert alert-error">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
        <ul style="margin:0;padding-left:16px;"><?php foreach ($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?></ul>
      </div>
    <?php endif; ?>

    <div class="card" style="max-width:680px;">
      <form method="POST">
        <div class="form-group">
          <label class="form-label" for="nom">Nom du projet *</label>
          <input type="text" id="nom" name="nom" class="form-control" required maxlength="150" value="<?= htmlspecialchars($data['nom']) ?>">
        </div>
        <div class="form-group">
          <label class="form-label" for="description">Description</label>
          <textarea id="description" name="description" class="form-control" rows="4" style="resize:vertical;"><?= htmlspecialchars($data['description']) ?></textarea>
        </div>
        <div class="grid-3" style="grid-template-columns:1fr 1fr 1fr;">
          <div class="form-group">
            <label class="form-label" for="date_debut">Date de debut</label>
            <input type="date" id="date_debut" name="date_debut" class="form-control" value="<?= htmlspecialchars($data['date_debut']) ?>">
          </div>
          <div class="form-group">
            <label class="form-label" for="date_fin">Date de fin</label>
            <input type="date" id="date_fin" name="date_fin" class="form-control" value="<?= htmlspecialchars($data['date_fin']) ?>">
          </div>
          <div class="form-group">
            <label class="form-label" for="date_limite">Date limite</label>
            <input type="date" id="date_limite" name="date_limite" class="form-control" value="<?= htmlspecialchars($data['date_limite']) ?>">
          </div>
        </div>
        <div style="display:flex;gap:10px;margin-top:8px;">
          <button type="submit" class="btn btn-primary">Creer le projet</button>
          <a href="index.php" class="btn btn-outline">Annuler</a>
        </div>
      </form>
    </div>
  </main>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
