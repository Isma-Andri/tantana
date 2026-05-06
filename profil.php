<?php
$pageTitle = 'Mon profil - Tantana';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/helpers.php';
requireLogin();
$user = getCurrentUser();

$errors  = [];
$success = '';

// Chargement données complètes
try {
    $pdo  = getDB();
    $stmt = $pdo->prepare("SELECT u.*,r.libelle AS role_lib FROM users u JOIN roles r ON u.id_role=r.id_role WHERE u.id_user=?");
    $stmt->execute([$user['id']]);
    $profil = $stmt->fetch();

    // Statistiques
    $nb_projets = (int)$pdo->prepare("SELECT COUNT(*) FROM participations WHERE id_user=?")->execute([$user['id']]) ? $pdo->query("SELECT COUNT(*) FROM participations WHERE id_user={$user['id']}")->fetchColumn() : 0;
    $stP = $pdo->prepare("SELECT COUNT(*) FROM participations WHERE id_user=?"); $stP->execute([$user['id']]); $nb_projets = (int)$stP->fetchColumn();
    $stT = $pdo->prepare("SELECT COUNT(*) FROM affectations WHERE id_user=?");   $stT->execute([$user['id']]); $nb_taches  = (int)$stT->fetchColumn();
    $stC = $pdo->prepare("SELECT COUNT(*) FROM commentaires WHERE id_user=?");   $stC->execute([$user['id']]); $nb_comms   = (int)$stC->fetchColumn();
    $dbErr = null;
} catch (PDOException $e) {
    $dbErr = $e->getMessage();
    $profil = ['nom'=>$user['nom'],'email'=>$user['email'],'role_lib'=>$user['role']];
    $nb_projets = $nb_taches = $nb_comms = 0;
}

// Mise à jour infos
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_info'])) {
    $nom   = trim($_POST['nom'] ?? '');
    $email = trim($_POST['email'] ?? '');
    if (empty($nom))  $errors[] = 'Le nom est obligatoire.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Email invalide.';
    if (empty($errors)) {
        try {
            $check = $pdo->prepare("SELECT id_user FROM users WHERE email=? AND id_user!=?");
            $check->execute([$email, $user['id']]);
            if ($check->fetch()) {
                $errors[] = 'Cet email est deja utilise.';
            } else {
                $pdo->prepare("UPDATE users SET nom=?,email=? WHERE id_user=?")->execute([$nom,$email,$user['id']]);
                $_SESSION['user_nom']   = $nom;
                $_SESSION['user_email'] = $email;
                logAction("A mis a jour son profil");
                flashSet('success','Profil mis a jour.');
                redirect('profil.php');
            }
        } catch (PDOException $e) { $errors[] = $e->getMessage(); }
    }
}

// Changement mot de passe
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_password'])) {
    $ancien    = $_POST['ancien'] ?? '';
    $nouveau   = $_POST['nouveau'] ?? '';
    $confirmer = $_POST['confirmer'] ?? '';
    if (!password_verify($ancien, $profil['mot_de_passe'])) $errors[] = 'Mot de passe actuel incorrect.';
    if (strlen($nouveau) < 8) $errors[] = 'Nouveau mot de passe trop court (8 car. min).';
    if ($nouveau !== $confirmer) $errors[] = 'Les nouveaux mots de passe ne correspondent pas.';
    if (empty($errors)) {
        try {
            $hash = password_hash($nouveau, PASSWORD_BCRYPT, ['cost'=>12]);
            $pdo->prepare("UPDATE users SET mot_de_passe=? WHERE id_user=?")->execute([$hash,$user['id']]);
            logAction("A change son mot de passe");
            flashSet('success','Mot de passe modifie.');
            redirect('profil.php');
        } catch (PDOException $e) { $errors[] = $e->getMessage(); }
    }
}

require_once __DIR__ . '/includes/header.php';
?>
<div class="app-layout">
  <?php require __DIR__ . '/includes/sidebar.php'; ?>
  <main class="main-content">
    <div class="page-header">
      <h1>Mon profil</h1>
    </div>

    <?= flashHtml() ?>
    <?php if (isset($dbErr)): ?><div class="alert alert-error"><?= htmlspecialchars($dbErr) ?></div><?php endif; ?>
    <?php if ($errors): ?>
      <div class="alert alert-error">
        <ul style="margin:0;padding-left:16px;"><?php foreach($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?></ul>
      </div>
    <?php endif; ?>

    <div class="grid-2" style="align-items:start;gap:20px;">
      <!-- Stats + avatar -->
      <div style="display:flex;flex-direction:column;gap:16px;">
        <div class="card" style="text-align:center;padding:32px 24px;">
          <div class="avatar" style="width:72px;height:72px;font-size:1.8rem;margin:0 auto 16px;">
            <?= strtoupper(substr($profil['nom'],0,1)) ?>
          </div>
          <div style="font-family:var(--font-head);font-size:1.3rem;font-weight:600;margin-bottom:4px;"><?= htmlspecialchars($profil['nom']) ?></div>
          <div style="color:var(--text-muted);font-size:.875rem;margin-bottom:12px;"><?= htmlspecialchars($profil['email']) ?></div>
          <span class="badge <?= $profil['role_lib']==='chef_projet'?'badge-purple':'badge-blue' ?>" style="font-size:.8rem;padding:4px 12px;">
            <?= $profil['role_lib']==='chef_projet'?'Chef de projet':'Membre' ?>
          </span>
        </div>

        <div class="stats-grid" style="grid-template-columns:1fr;">
          <div class="stat-card blue">
            <div class="stat-label">Projets</div>
            <div class="stat-value"><?= $nb_projets ?></div>
            <div class="stat-sub">Participations</div>
          </div>
          <div class="stat-card green">
            <div class="stat-label">Taches</div>
            <div class="stat-value"><?= $nb_taches ?></div>
            <div class="stat-sub">Assignees</div>
          </div>
          <div class="stat-card orange">
            <div class="stat-label">Commentaires</div>
            <div class="stat-value"><?= $nb_comms ?></div>
            <div class="stat-sub">Publies</div>
          </div>
        </div>
      </div>

      <div style="display:flex;flex-direction:column;gap:16px;">
        <!-- Modifier infos -->
        <div class="card">
          <div class="card-header"><span class="card-title">Informations personnelles</span></div>
          <form method="POST">
            <div class="form-group">
              <label class="form-label">Nom</label>
              <input type="text" name="nom" class="form-control" value="<?= htmlspecialchars($profil['nom']) ?>" required>
            </div>
            <div class="form-group">
              <label class="form-label">Email</label>
              <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($profil['email']) ?>" required>
            </div>
            <button name="update_info" type="submit" class="btn btn-primary">Enregistrer</button>
          </form>
        </div>

        <!-- Changer mot de passe -->
        <div class="card">
          <div class="card-header"><span class="card-title">Changer le mot de passe</span></div>
          <form method="POST">
            <div class="form-group">
              <label class="form-label">Mot de passe actuel</label>
              <input type="password" name="ancien" class="form-control" required>
            </div>
            <div class="form-group">
              <label class="form-label">Nouveau mot de passe</label>
              <input type="password" name="nouveau" class="form-control" placeholder="8 caracteres minimum" required>
            </div>
            <div class="form-group">
              <label class="form-label">Confirmer</label>
              <input type="password" name="confirmer" class="form-control" required>
            </div>
            <button name="update_password" type="submit" class="btn btn-primary">Modifier</button>
          </form>
        </div>
      </div>
    </div>
  </main>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
