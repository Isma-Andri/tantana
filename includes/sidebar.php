<?php
// Variables attendues depuis la page appelante : $user, $assetBase
$notifCount = countNotifNonLues($user['id']);
$current    = basename($_SERVER['PHP_SELF']);
?>
<aside class="sidebar">
  <div class="sidebar-section">
    <div class="sidebar-label">Navigation</div>
    <a class="sidebar-link <?= in_array($current,['dashboard_chef.php','dashboard_membre.php']) ? 'active':'' ?>" href="<?= $assetBase ?>/dashboard.php">
      <svg class="sidebar-icon" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
      Tableau de bord
    </a>

    <?php if ($user['role'] === 'chef_projet'): ?>
    <a class="sidebar-link <?= in_array($current,['projets/index.php','projets/create.php']) ? 'active':'' ?>" href="<?= $assetBase ?>/projets/index.php">
      <svg class="sidebar-icon" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M22 19a2 2 0 01-2 2H4a2 2 0 01-2-2V5a2 2 0 012-2h5l2 3h9a2 2 0 012 2z"/></svg>
      Mes projets
    </a>
    <a class="sidebar-link <?= $current==='membres/index.php' ? 'active':'' ?>" href="<?= $assetBase ?>/membres/index.php">
      <svg class="sidebar-icon" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87"/><path d="M16 3.13a4 4 0 010 7.75"/></svg>
      Utilisateurs
    </a>
    <?php else: ?>
    <a class="sidebar-link <?= in_array($current,['projets/index.php']) ? 'active':'' ?>" href="<?= $assetBase ?>/projets/index.php">
      <svg class="sidebar-icon" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M22 19a2 2 0 01-2 2H4a2 2 0 01-2-2V5a2 2 0 012-2h5l2 3h9a2 2 0 012 2z"/></svg>
      Mes projets
    </a>
    <a class="sidebar-link <?= $current==='taches/mes_taches.php' ? 'active':'' ?>" href="<?= $assetBase ?>/taches/mes_taches.php">
      <svg class="sidebar-icon" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="9 11 12 14 22 4"/><path d="M21 12v7a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2h11"/></svg>
      Mes taches
    </a>
    <?php endif; ?>

    <a class="sidebar-link <?= $current==='historique/index.php' ? 'active':'' ?>" href="<?= $assetBase ?>/historique/index.php">
      <svg class="sidebar-icon" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
      Historique
    </a>
  </div>

  <div class="sidebar-section">
    <div class="sidebar-label">Compte</div>
    <a class="sidebar-link <?= $current==='profil.php' ? 'active':'' ?>" href="<?= $assetBase ?>/profil.php">
      <svg class="sidebar-icon" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
      Mon profil
    </a>
    <a class="sidebar-link <?= $current==='notifications/index.php' ? 'active':'' ?>" href="<?= $assetBase ?>/notifications/index.php">
      <svg class="sidebar-icon" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M18 8A6 6 0 006 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 01-3.46 0"/></svg>
      Notifications
      <?php if ($notifCount > 0): ?>
        <span class="badge badge-red" style="margin-left:auto;"><?= $notifCount ?></span>
      <?php endif; ?>
    </a>
  </div>

  <div style="margin-top:auto;padding-top:20px;border-top:1px solid var(--border);">
    <div style="padding:0 10px 12px;font-size:.78rem;color:var(--text-muted);">
      <strong style="color:var(--text-dim);"><?= htmlspecialchars($user['nom']) ?></strong><br>
      <?= $user['role'] === 'chef_projet' ? 'Chef de projet' : 'Membre' ?>
    </div>
    <a class="sidebar-link" href="<?= $assetBase ?>/logout.php" style="color:var(--red);">
      <svg class="sidebar-icon" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
      Deconnexion
    </a>
  </div>
</aside>
