<?php
require_once __DIR__ . '/../includes/auth.php';
startSession();
$user = getCurrentUser();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= $pageTitle ?? 'Tantana' ?></title>
  <link rel="stylesheet" href="/tantana/assets/css/style.css">
</head>
<body>
<?php if ($showNavbar ?? true): ?>
<nav class="navbar">
  <a href="/tantana/index.php" class="navbar-brand">Tantana</a>
  <div class="navbar-nav">
    <?php if ($user): ?>
      <a href="/tantana/dashboard.php" class="<?= basename($_SERVER['PHP_SELF']) === 'dashboard.php' ? 'active' : '' ?>">Tableau de bord</a>
      <?php if ($user['role'] === 'chef_projet'): ?>
        <a href="#">Projets</a>
        <a href="#">Membres</a>
      <?php else: ?>
        <a href="#">Mes tâches</a>
        <a href="#">Mes projets</a>
      <?php endif; ?>
    <?php else: ?>
      <a href="/tantana/index.php">Accueil</a>
      <a href="/tantana/login.php">Connexion</a>
    <?php endif; ?>
  </div>
  <div class="navbar-user">
    <?php if ($user): ?>
      <div class="dropdown">
        <div style="display:flex;align-items:center;gap:10px;cursor:pointer;">
          <div class="avatar"><?= strtoupper(substr($user['nom'], 0, 1)) ?></div>
          <div>
            <div style="font-size:.85rem;font-weight:600;"><?= htmlspecialchars($user['nom']) ?></div>
            <div style="font-size:.75rem;color:var(--text-muted);"><?= $user['role'] === 'chef_projet' ? 'Chef de projet' : 'Membre' ?></div>
          </div>
          <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M6 9l6 6 6-6"/></svg>
        </div>
        <div class="dropdown-menu">
          <a class="dropdown-item" href="#">Mon profil</a>
          <a class="dropdown-item" href="#">Paramètres</a>
          <div class="dropdown-divider"></div>
          <a class="dropdown-item" href="/tantana/logout.php" style="color:var(--red);">Déconnexion</a>
        </div>
      </div>
    <?php else: ?>
      <a href="/tantana/login.php" class="btn btn-outline btn-sm">Connexion</a>
      <a href="/tantana/register.php" class="btn btn-primary btn-sm">S'inscrire</a>
    <?php endif; ?>
  </div>
</nav>
<?php endif; ?>
