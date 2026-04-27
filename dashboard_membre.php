<?php
$pageTitle = 'Tableau de bord - Membre';
require_once __DIR__ . '/includes/auth.php';
requireLogin();
requireRole('membre');

$user = getCurrentUser();

try {
    $pdo        = getDB();
    $totalUsers = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
    $dbError    = null;
} catch (PDOException $e) {
    error_log('[Tantana][dashboard_membre] ' . $e->getMessage());
    $dbError    = 'Erreur base de donnees : ' . htmlspecialchars($e->getMessage());
    $totalUsers = '?';
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="app-layout">
  <aside class="sidebar">
    <div class="sidebar-section">
      <div class="sidebar-label">Navigation</div>
      <a class="sidebar-link active" href="dashboard_membre.php">
        <svg class="sidebar-icon" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
        Tableau de bord
      </a>
      <a class="sidebar-link" href="#">
        <svg class="sidebar-icon" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M22 19a2 2 0 01-2 2H4a2 2 0 01-2-2V5a2 2 0 012-2h5l2 3h9a2 2 0 012 2z"/></svg>
        Mes projets
        <span class="badge badge-blue" style="margin-left:auto;">Bientot</span>
      </a>
      <a class="sidebar-link" href="#">
        <svg class="sidebar-icon" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="9 11 12 14 22 4"/><path d="M21 12v7a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2h11"/></svg>
        Mes taches
        <span class="badge badge-blue" style="margin-left:auto;">Bientot</span>
      </a>
    </div>
    <div class="sidebar-section">
      <div class="sidebar-label">Compte</div>
      <a class="sidebar-link" href="#">
        <svg class="sidebar-icon" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
        Mon profil
      </a>
      <a class="sidebar-link" href="#">
        <svg class="sidebar-icon" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M18 8A6 6 0 006 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 01-3.46 0"/></svg>
        Notifications
      </a>
    </div>
    <div style="margin-top:auto;padding-top:24px;border-top:1px solid var(--border);">
      <a class="sidebar-link" href="logout.php" style="color:var(--red);">
        <svg class="sidebar-icon" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
        Deconnexion
      </a>
    </div>
  </aside>

  <main class="main-content">
    <div class="page-header fade-up">
      <div style="display:flex;align-items:center;justify-content:space-between;">
        <div>
          <h1>Bonjour, <?= htmlspecialchars($user['nom']) ?></h1>
          <p>Votre espace de travail personnel.</p>
        </div>
        <span class="badge badge-blue" style="padding:8px 16px;font-size:.85rem;">Membre</span>
      </div>
    </div>

    <?php if ($dbError): ?>
      <div class="alert alert-error" style="margin-bottom:24px;">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
        <?= $dbError ?>
      </div>
    <?php endif; ?>

    <div class="stats-grid mb-24 fade-up delay-1">
      <div class="stat-card blue">
        <div class="stat-label">Projets assignes</div>
        <div class="stat-value">&mdash;</div>
        <div class="stat-sub">Module a venir</div>
      </div>
      <div class="stat-card green">
        <div class="stat-label">Taches en cours</div>
        <div class="stat-value">&mdash;</div>
        <div class="stat-sub">Module a venir</div>
      </div>
      <div class="stat-card orange">
        <div class="stat-label">Taches terminees</div>
        <div class="stat-value">&mdash;</div>
        <div class="stat-sub">Module a venir</div>
      </div>
      <div class="stat-card purple">
        <div class="stat-label">Notifications</div>
        <div class="stat-value">0</div>
        <div class="stat-sub">Non lues</div>
      </div>
    </div>

    <div class="grid-2 fade-up delay-2">
      <div class="card">
        <div class="card-header">
          <span class="card-title">Mes taches recentes</span>
          <span class="badge badge-orange">A venir</span>
        </div>
        <div style="text-align:center;padding:40px 0;color:var(--text-muted);">
          <div style="font-size:2rem;font-family:var(--font-head);font-weight:800;color:var(--border);margin-bottom:12px;">0</div>
          <p style="font-size:.9rem;">Aucune tache assignee pour l'instant.<br>Votre chef de projet vous en attribuera bientot.</p>
        </div>
      </div>

      <div class="card">
        <div class="card-header">
          <span class="card-title">Mon profil</span>
          <a href="#" class="btn btn-outline btn-sm">Modifier</a>
        </div>
        <div style="display:flex;flex-direction:column;gap:16px;padding-top:8px;">
          <div style="display:flex;align-items:center;gap:16px;">
            <div class="avatar" style="width:56px;height:56px;font-size:1.4rem;">
              <?= strtoupper(substr($user['nom'], 0, 1)) ?>
            </div>
            <div>
              <div style="font-family:var(--font-head);font-weight:700;font-size:1.1rem;"><?= htmlspecialchars($user['nom']) ?></div>
              <div style="color:var(--text-muted);font-size:.85rem;"><?= htmlspecialchars($user['email']) ?></div>
            </div>
          </div>
          <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-top:8px;">
            <div style="background:var(--bg3);border-radius:var(--radius-sm);padding:12px;">
              <div style="font-size:.75rem;color:var(--text-muted);margin-bottom:4px;text-transform:uppercase;letter-spacing:.06em;">Role</div>
              <div style="font-weight:600;">Membre</div>
            </div>
            <div style="background:var(--bg3);border-radius:var(--radius-sm);padding:12px;">
              <div style="font-size:.75rem;color:var(--text-muted);margin-bottom:4px;text-transform:uppercase;letter-spacing:.06em;">Statut</div>
              <div style="font-weight:600;color:var(--green);">Actif</div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="card mt-24 fade-up delay-3">
      <div class="card-header">
        <span class="card-title">Mes projets</span>
        <span class="badge badge-blue">Module a venir</span>
      </div>
      <div style="text-align:center;padding:40px 0;color:var(--text-muted);">
        <div style="font-size:2rem;font-family:var(--font-head);font-weight:800;color:var(--border);margin-bottom:12px;">0</div>
        <p style="font-size:.9rem;">Vous n'etes encore assigne a aucun projet.<br>Votre chef de projet vous ajoutera a un projet prochainement.</p>
      </div>
    </div>
  </main>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
