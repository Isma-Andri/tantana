<?php
$pageTitle = 'Utilisateurs - Tantana';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';
requireLogin();
requireRole('chef_projet');
$user = getCurrentUser();

try {
    $pdo  = getDB();
    $stmt = $pdo->query("
        SELECT u.id_user,u.nom,u.email,r.libelle AS role,
               (SELECT COUNT(*) FROM participations pa WHERE pa.id_user=u.id_user) AS nb_projets,
               (SELECT COUNT(*) FROM affectations a WHERE a.id_user=u.id_user) AS nb_taches
        FROM users u JOIN roles r ON u.id_role=r.id_role
        ORDER BY r.id_role ASC, u.nom ASC
    ");
    $users = $stmt->fetchAll();
    $dbErr = null;
} catch (PDOException $e) {
    $users = []; $dbErr = $e->getMessage();
}

require_once __DIR__ . '/../includes/header.php';
?>
<div class="app-layout">
  <?php require __DIR__ . '/../includes/sidebar.php'; ?>
  <main class="main-content">
    <div class="page-header">
      <div style="display:flex;justify-content:space-between;align-items:flex-start;">
        <div><h1>Utilisateurs</h1><p><?= count($users) ?> utilisateur(s)</p></div>
        <a href="../register.php" class="btn btn-primary">+ Ajouter</a>
      </div>
    </div>
    <?= flashHtml() ?>
    <?php if (isset($dbErr)): ?><div class="alert alert-error"><?= htmlspecialchars($dbErr) ?></div><?php endif; ?>
    <div class="card">
      <div class="table-wrap">
        <table>
          <thead>
            <tr><th>#</th><th>Nom</th><th>Email</th><th>Role</th><th>Projets</th><th>Taches</th></tr>
          </thead>
          <tbody>
            <?php foreach ($users as $u): ?>
            <tr>
              <td style="color:var(--text-muted);"><?= $u['id_user'] ?></td>
              <td>
                <div style="display:flex;align-items:center;gap:9px;">
                  <div class="avatar" style="width:28px;height:28px;font-size:.72rem;"><?= strtoupper(substr($u['nom'],0,1)) ?></div>
                  <span style="font-weight:500;"><?= htmlspecialchars($u['nom']) ?></span>
                  <?php if ($u['id_user']==$user['id']): ?><span class="badge badge-green" style="font-size:.66rem;">Vous</span><?php endif; ?>
                </div>
              </td>
              <td style="color:var(--text-muted);font-size:.875rem;"><?= htmlspecialchars($u['email']) ?></td>
              <td><?= $u['role']==='chef_projet'?'<span class="badge badge-purple">Chef de projet</span>':'<span class="badge badge-blue">Membre</span>' ?></td>
              <td><?= (int)$u['nb_projets'] ?></td>
              <td><?= (int)$u['nb_taches'] ?></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </main>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
