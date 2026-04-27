<?php
$pageTitle = 'Connexion — Tantana';
$showNavbar = false;
require_once __DIR__ . '/includes/auth.php';
startSession();

if (isLoggedIn()) {
    header('Location: dashboard.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = 'Veuillez remplir tous les champs.';
    } else {
        $result = loginUser($email, $password);
        if ($result['success']) {
            header('Location: dashboard.php');
            exit;
        } else {
            $error = $result['message'];
        }
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="auth-page">
  <div class="auth-box fade-up">
    <a href="index.php" class="auth-logo">Tantana</a>
    <p class="auth-subtitle">Bienvenue ! Connectez-vous à votre espace.</p>

    <?php if ($error): ?>
      <div class="alert alert-error">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
        <?= htmlspecialchars($error) ?>
      </div>
    <?php endif; ?>

    <form method="POST" action="">
      <div class="form-group">
        <label class="form-label" for="email">Adresse email</label>
        <input
          type="email" id="email" name="email" class="form-control"
          placeholder="vous@exemple.com"
          value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
          required autofocus
        >
      </div>
      <div class="form-group">
        <label class="form-label" for="password">
          Mot de passe
          <a href="#" style="float:right;font-size:.82rem;text-transform:none;letter-spacing:0;">Mot de passe oublié ?</a>
        </label>
        <input type="password" id="password" name="password" class="form-control" placeholder="••••••••" required>
      </div>
      <button type="submit" class="btn btn-primary btn-block btn-lg" style="margin-top:8px;">
        Se connecter
      </button>
    </form>

    <div class="divider">ou</div>

    <div class="auth-footer">
      Pas encore de compte ?
      <a href="register.php" style="font-weight:600;">S'inscrire</a>
    </div>
    <div class="auth-footer" style="margin-top:12px;">
      <a href="index.php" style="color:var(--text-muted);font-size:.82rem;">← Retour à l'accueil</a>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
