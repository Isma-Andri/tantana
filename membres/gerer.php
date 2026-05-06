<?php
$pageTitle = 'Gestion des membres - Tantana';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';
requireLogin();
requireRole('chef_projet');
$user = getCurrentUser();

$id_projet = (int)($_GET['projet'] ?? $_POST['id_projet'] ?? 0);
if (!$id_projet || !isChefProjet($id_projet, $user['id'])) {
    flashSet('error','Acces refuse.'); redirect('projets/index.php');
}

try {
    $pdo  = getDB();
    $stmt = $pdo->prepare("SELECT * FROM projets WHERE id_projet=?");
    $stmt->execute([$id_projet]);
    $projet = $stmt->fetch();
    if (!$projet) { flashSet('error','Projet introuvable.'); redirect('projets/index.php'); }

    // Ajouter un membre
    if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['add_member'])) {
        $id_user = (int)($_POST['id_user'] ?? 0);
        if ($id_user) {
            try {
                $pdo->prepare("INSERT IGNORE INTO participations (id_user,id_projet) VALUES (?,?)")->execute([$id_user, $id_projet]);
                $uName = $pdo->query("SELECT nom FROM users WHERE id_user=$id_user")->fetchColumn();
                $act = logAction("A ajoute $uName au projet \"{$projet['nom']}\"", $id_projet);
                notifier($id_user, "Vous avez ete ajoute au projet \"{$projet['nom']}\".", $act);
                flashSet('success','Membre ajoute.');
            } catch (PDOException $e) { flashSet('error',$e->getMessage()); }
        }
        redirect('membres/gerer.php?projet='.$id_projet);
    }

    // Retirer un membre
    if (isset($_GET['retirer'])) {
        $id_user = (int)$_GET['retirer'];
        if ($id_user === $user['id']) { flashSet('error','Vous ne pouvez pas vous retirer.'); redirect('membres/gerer.php?projet='.$id_projet); }
        try {
            $uName = $pdo->query("SELECT nom FROM users WHERE id_user=$id_user")->fetchColumn();
            $pdo->prepare("DELETE FROM participations WHERE id_user=? AND id_projet=?")->execute([$id_user,$id_projet]);
            logAction("A retire $uName du projet \"{$projet['nom']}\"", $id_projet);
            flashSet('success','Membre retire.');
        } catch (PDOException $e) { flashSet('error',$e->getMessage()); }
        redirect('membres/gerer.php?projet='.$id_projet);
    }

    // Membres actuels
    $stmt = $pdo->prepare("
        SELECT u.id_user,u.nom,u.email,r.libelle AS role,pa.date_ajout,
               (SELECT COUNT(*) FROM affectations a JOIN taches t ON a.id_tache=t.id_tache WHERE a.id_user=u.id_user AND t.id_projet=?) AS nb_taches
        FROM participations pa
        JOIN users u ON pa.id_user=u.id_user
        JOIN roles r ON u.id_role=r.id_role
        WHERE pa.id_projet=?
        ORDER BY u.nom
    ");
    $stmt->execute([$id_projet, $id_projet]);
    $membres = $stmt->fetchAll();

    // Utilisateurs disponibles (non encore membres)
    $membres_ids = array_column($membres, 'id_user');
    $placeholders = count($membres_ids) ? implode(',', array_fill(0, count($membres_ids), '?')) : '0';
    $stmt = $pdo->prepare("SELECT id_user,nom,email FROM users WHERE id_user NOT IN ($placeholders) ORDER BY nom");
    $stmt->execute($membres_ids);
    $disponibles = $stmt->fetchAll();

    $dbErr = null;
} catch (PDOException $e) {
    error_log('[membres/gerer] ' . $e->getMessage());
    $dbErr = $e->getMessage();
    $membres = $disponibles = [];
}

require_once __DIR__ . '/../includes/header.php';
?>
<div class="app-layout">
  <?php require __DIR__ . '/../includes/sidebar.php'; ?>
  <main class="main-content">
    <div class="page-header">
      <h1>Gestion des membres</h1>
      <p>Projet : <a href="../projets/view.php?id=<?= $id_projet ?>"><?= htmlspecialchars($projet['nom']) ?></a></p>
    </div>

    <?= flashHtml() ?>
    <?php if (isset($dbErr)): ?><div class="alert alert-error"><?= htmlspecialchars($dbErr) ?></div><?php endif; ?>

    <div class="grid-2" style="align-items:start;">
      <!-- Membres actuels -->
      <div class="card">
        <div class="card-header">
          <span class="card-title">Membres actuels (<?= count($membres) ?>)</span>
        </div>
        <?php if (empty($membres)): ?>
          <p style="color:var(--text-muted);font-size:.875rem;">Aucun membre.</p>
        <?php else: ?>
          <div style="display:flex;flex-direction:column;gap:10px;">
            <?php foreach ($membres as $m): ?>
            <div style="display:flex;align-items:center;justify-content:space-between;gap:10px;padding:10px 12px;background:var(--bg2);border-radius:var(--radius-sm);">
              <div style="display:flex;align-items:center;gap:10px;flex:1;min-width:0;">
                <div class="avatar" style="width:32px;height:32px;font-size:.8rem;"><?= strtoupper(substr($m['nom'],0,1)) ?></div>
                <div style="min-width:0;">
                  <div style="font-weight:600;font-size:.875rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?= htmlspecialchars($m['nom']) ?></div>
                  <div style="font-size:.75rem;color:var(--text-muted);"><?= (int)$m['nb_taches'] ?> tache(s) &bull; <?= $m['role']==='chef_projet'?'Chef de projet':'Membre' ?></div>
                </div>
              </div>
              <?php if ($m['id_user']===$projet['id_chef']): ?>
                <span class="badge badge-purple">Chef</span>
              <?php elseif ($m['id_user']!==$user['id']): ?>
                <a href="?projet=<?= $id_projet ?>&retirer=<?= $m['id_user'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Retirer ce membre ?')">Retirer</a>
              <?php endif; ?>
            </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>

      <!-- Ajouter un membre -->
      <div class="card">
        <div class="card-header"><span class="card-title">Ajouter un membre</span></div>
        <?php if (empty($disponibles)): ?>
          <p style="color:var(--text-muted);font-size:.875rem;">Tous les utilisateurs sont deja membres.</p>
        <?php else: ?>
          <form method="POST">
            <input type="hidden" name="id_projet" value="<?= $id_projet ?>">
            <div class="form-group">
              <label class="form-label">Choisir un utilisateur</label>
              <select name="id_user" class="form-control">
                <option value="">-- Selectionner --</option>
                <?php foreach ($disponibles as $d): ?>
                  <option value="<?= $d['id_user'] ?>"><?= htmlspecialchars($d['nom']) ?> (<?= htmlspecialchars($d['email']) ?>)</option>
                <?php endforeach; ?>
              </select>
            </div>
            <button name="add_member" type="submit" class="btn btn-primary">Ajouter</button>
          </form>
        <?php endif; ?>
      </div>
    </div>
  </main>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
