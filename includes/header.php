<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';
startSession();
$user = getCurrentUser();

$scriptDir = dirname($_SERVER['SCRIPT_NAME']);
$assetBase = rtrim($scriptDir, '/');
// si on est dans un sous-dossier (projets/, taches/, etc.) remonter d'un cran
if ($assetBase !== '/') {
    $assetBase = dirname($assetBase);
}
$assetBase = rtrim($assetBase, '/');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($pageTitle ?? 'Tantana') ?></title>
  <link rel="stylesheet" href="<?= $assetBase ?>/assets/css/style.css">
</head>
<body>
<?php if ($showNavbar ?? true): ?>
<nav class="navbar">
  <a href="<?= $assetBase ?>/index.php" class="navbar-brand">Tan<span>tana</span></a>
  <div class="navbar-nav">
    <?php if ($user): ?>
      <a href="<?= $assetBase ?>/dashboard.php">Tableau de bord</a>
      <a href="<?= $assetBase ?>/projets/index.php">Projets</a>
      <?php if ($user['role'] === 'chef_projet'): ?>
        <a href="<?= $assetBase ?>/membres/index.php">Utilisateurs</a>
      <?php else: ?>
        <a href="<?= $assetBase ?>/taches/mes_taches.php">Mes taches</a>
      <?php endif; ?>
    <?php else: ?>
      <a href="<?= $assetBase ?>/index.php">Accueil</a>
    <?php endif; ?>
  </div>
  <div class="navbar-user">
    <?php if ($user): ?>
      <?php $nc = countNotifNonLues($user['id']); ?>
      <a href="<?= $assetBase ?>/notifications/index.php" style="position:relative;display:flex;align-items:center;color:var(--text-muted);padding:4px 8px;border-radius:var(--radius-sm);" title="Notifications">
        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M18 8A6 6 0 006 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 01-3.46 0"/></svg>
        <?php if ($nc > 0): ?><span style="position:absolute;top:0;right:0;background:var(--red);color:#fff;font-size:.6rem;font-weight:700;width:14px;height:14px;border-radius:50%;display:flex;align-items:center;justify-content:center;"><?= $nc ?></span><?php endif; ?>
      </a>
      <div class="dropdown">
        <div style="display:flex;align-items:center;gap:8px;cursor:pointer;">
          <div class="avatar"><?= strtoupper(substr($user['nom'],0,1)) ?></div>
          <div style="display:flex;flex-direction:column;">
            <span style="font-size:.85rem;font-weight:600;line-height:1.2;"><?= htmlspecialchars($user['nom']) ?></span>
            <span style="font-size:.72rem;color:var(--text-muted);"><?= $user['role']==='chef_projet'?'Chef de projet':'Membre' ?></span>
          </div>
          <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M6 9l6 6 6-6"/></svg>
        </div>
        <div class="dropdown-menu">
          <a class="dropdown-item" href="<?= $assetBase ?>/profil.php">Mon profil</a>
          <div class="dropdown-divider"></div>
          <a class="dropdown-item" href="<?= $assetBase ?>/logout.php" style="color:var(--red);">Deconnexion</a>
        </div>
      </div>
    <?php else: ?>
      <a href="<?= $assetBase ?>/login.php" class="btn btn-outline btn-sm">Connexion</a>
      <a href="<?= $assetBase ?>/register.php" class="btn btn-primary btn-sm">S'inscrire</a>
    <?php endif; ?>
  </div>
</nav>
<?php endif; ?>
