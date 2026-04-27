<?php
$pageTitle = 'Inscription — Tantana';
$showNavbar = false;
require_once __DIR__ . '/includes/auth.php';
startSession();

if (isLoggedIn()) {
    header('Location: dashboard.php');
    exit;
}

$error   = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom      = trim($_POST['nom'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm'] ?? '';
    $id_role  = (int)($_POST['id_role'] ?? 2);

    if (empty($nom) || empty($email) || empty($password) || empty($confirm)) {
        $error = 'Veuillez remplir tous les champs.';
    } elseif (strlen($password) < 8) {
        $error = 'Le mot de passe doit contenir au moins 8 caractères.';
    } elseif ($password !== $confirm) {
        $error = 'Les mots de passe ne correspondent pas.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Adresse email invalide.';
    } else {
        $result = registerUser($nom, $email, $password, $id_role);
        if ($result['success']) {
            $success = 'Compte créé avec succès ! Vous pouvez maintenant vous connecter.';
        } else {
            $error = $result['message'];
        }
    }
}

$roles = getRoles();
require_once __DIR__ . '/includes/header.php';
?>

<div class="auth-page">
  <div class="auth-box fade-up" style="max-width:480px;">
    <a href="index.php" class="auth-logo">Tantana</a>
    <p class="auth-subtitle">Créez votre compte et rejoignez votre équipe.</p>

    <?php if ($error): ?>
      <div class="alert alert-error">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
        <?= htmlspecialchars($error) ?>
      </div>
    <?php endif; ?>

    <?php if ($success): ?>
      <div class="alert alert-success">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M22 11.08V12a10 10 0 11-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
        <?= htmlspecialchars($success) ?>
        <a href="login.php" style="margin-left:8px;font-weight:600;">Se connecter →</a>
      </div>
    <?php else: ?>

    <form method="POST" action="">
      <div class="form-group">
        <label class="form-label" for="nom">Nom complet</label>
        <input
          type="text" id="nom" name="nom" class="form-control"
          placeholder="Jean Dupont"
          value="<?= htmlspecialchars($_POST['nom'] ?? '') ?>"
          required autofocus
        >
      </div>
      <div class="form-group">
        <label class="form-label" for="email">Adresse email</label>
        <input
          type="email" id="email" name="email" class="form-control"
          placeholder="vous@exemple.com"
          value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
          required
        >
      </div>
      <div class="grid-2">
        <div class="form-group">
          <label class="form-label" for="password">Mot de passe</label>
          <input type="password" id="password" name="password" class="form-control" placeholder="Min. 8 caractères" required>
        </div>
        <div class="form-group">
          <label class="form-label" for="confirm">Confirmer</label>
          <input type="password" id="confirm" name="confirm" class="form-control" placeholder="Répétez" required>
        </div>
      </div>
      <div class="form-group">
        <label class="form-label" for="id_role">Rôle</label>
        <select id="id_role" name="id_role" class="form-control">
          <?php foreach ($roles as $role): ?>
            <option value="<?= $role['id_role'] ?>"
              <?= (($_POST['id_role'] ?? 2) == $role['id_role']) ? 'selected' : '' ?>>
              <?= $role['id_role'] === 1 ? 'Chef de projet' : 'Membre' ?>
            </option>
          <?php endforeach; ?>
        </select>
        <div style="font-size:.78rem;color:var(--text-muted);margin-top:6px;">
          Le chef de projet peut créer et gérer des projets.
        </div>
      </div>
      <button type="submit" class="btn btn-primary btn-block btn-lg" style="margin-top:8px;">
        Créer mon compte
      </button>
    </form>

    <?php endif; ?>

    <div class="auth-footer" style="margin-top:20px;">
      Déjà inscrit ?
      <a href="login.php" style="font-weight:600;">Se connecter</a>
    </div>
    <div class="auth-footer" style="margin-top:8px;">
      <a href="index.php" style="color:var(--text-muted);font-size:.82rem;">← Retour à l'accueil</a>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
