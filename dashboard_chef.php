<?php
$pageTitle = 'Tableau de bord — Chef de projet';
require_once __DIR__ . '/includes/auth.php';
requireLogin();
requireRole('chef_projet');

$user = getCurrentUser();
$pdo  = getDB();

// Stats
$totalUsers    = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$totalMembers  = $pdo->query("SELECT COUNT(*) FROM users u JOIN roles r ON u.id_role=r.id_role WHERE r.libelle='membre'")->fetchColumn();
$totalChefs    = $pdo->query("SELECT COUNT(*) FROM users u JOIN roles r ON u.id_role=r.id_role WHERE r.libelle='chef_projet'")->fetchColumn();

// Liste des utilisateurs
$stmt = $pdo->query("
    SELECT u.id_user, u.nom, u.email, r.libelle AS role
    FROM users u
    JOIN roles r ON u.id_role = r.id_role
    ORDER BY u.id_user DESC
");
$users = $stmt->fetchAll();

require_once __DIR__ . '/includes/header.php';
?>

<div class="app-layout">
  <!-- SIDEBAR -->
  <aside class="sidebar">
    <div class="sidebar-section">
      <div class="sidebar-label">Navigation</div>
      <a class="sidebar-link active" href="dashboard_chef.php">
        <svg class="sidebar-icon" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
        Tableau de bord
      </a>
      <a class="sidebar-link" href="#">
        <svg class="sidebar-icon" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M22 19a2 2 0 01-2 2H4a2 2 0 01-2-2V5a2 2 0 012-2h5l2 3h9a2 2 0 012 2z"/></svg>
        Projets
        <span class="badge badge-blue" style="margin-left:auto;">Bientôt</span>
      </a>
      <a class="sidebar-link" href="#">
        <svg class="sidebar-icon" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="9 11 12 14 22 4"/><path d="M21 12v7a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2h11"/></svg>
        Tâches
        <span class="badge badge-blue" style="margin-left:auto;">Bientôt</span>
      </a>
    </div>
    <div class="sidebar-section">
      <div class="sidebar-label">Équipe</div>
      <a class="sidebar-link" href="#">
        <svg class="sidebar-icon" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87"/><path d="M16 3.13a4 4 0 010 7.75"/></svg>
        Membres
      </a>
      <a class="sidebar-link" href="#">
        <svg class="sidebar-icon" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
        Activités
        <span class="badge badge-blue" style="margin-left:auto;">Bientôt</span>
      </a>
    </div>
    <div style="margin-top:auto; padding-top:24px; border-top:1px solid var(--border);">
      <a class="sidebar-link" href="logout.php" style="color:var(--red);">
        <svg class="sidebar-icon" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
        Déconnexion
      </a>
    </div>
  </aside>

  <!-- MAIN -->
  <main class="main-content">
    <div class="page-header fade-up">
      <div style="display:flex;align-items:center;justify-content:space-between;">
        <div>
          <h1>Bonjour, <?= htmlspecialchars($user['nom']) ?> 👋</h1>
          <p>Voici un aperçu de votre espace chef de projet.</p>
        </div>
        <span class="badge badge-purple" style="padding:8px 16px;font-size:.85rem;">Chef de projet</span>
      </div>
    </div>

    <!-- STATS -->
    <div class="stats-grid mb-24 fade-up delay-1">
      <div class="stat-card blue">
        <div class="stat-label">Utilisateurs total</div>
        <div class="stat-value"><?= $totalUsers ?></div>
        <div class="stat-sub">Tous rôles confondus</div>
      </div>
      <div class="stat-card purple">
        <div class="stat-label">Membres</div>
        <div class="stat-value"><?= $totalMembers ?></div>
        <div class="stat-sub">Rôle : membre</div>
      </div>
      <div class="stat-card green">
        <div class="stat-label">Chefs de projet</div>
        <div class="stat-value"><?= $totalChefs ?></div>
        <div class="stat-sub">Rôle : chef_projet</div>
      </div>
      <div class="stat-card orange">
        <div class="stat-label">Projets actifs</div>
        <div class="stat-value">—</div>
        <div class="stat-sub">Module à venir</div>
      </div>
    </div>

    <!-- PROJETS PLACEHOLDER -->
    <div class="grid-2 mb-24 fade-up delay-2">
      <div class="card">
        <div class="card-header">
          <span class="card-title">Projets récents</span>
          <a href="#" class="btn btn-primary btn-sm">+ Nouveau projet</a>
        </div>
        <div style="text-align:center;padding:40px 0;color:var(--text-muted);">
          <div style="font-size:2.5rem;margin-bottom:12px;">📁</div>
          <p style="font-size:.9rem;">Aucun projet pour l'instant.<br>Créez votre premier projet pour commencer.</p>
          <a href="#" class="btn btn-outline btn-sm" style="margin-top:16px;">Créer un projet</a>
        </div>
      </div>
      <div class="card">
        <div class="card-header">
          <span class="card-title">Activité récente</span>
        </div>
        <div style="text-align:center;padding:40px 0;color:var(--text-muted);">
          <div style="font-size:2.5rem;margin-bottom:12px;">📊</div>
          <p style="font-size:.9rem;">Aucune activité enregistrée.</p>
        </div>
      </div>
    </div>

    <!-- TEAM TABLE -->
    <div class="card fade-up delay-3">
      <div class="card-header">
        <span class="card-title">Liste des utilisateurs</span>
        <a href="register.php" class="btn btn-outline btn-sm">+ Ajouter</a>
      </div>
      <div class="table-wrap">
        <table>
          <thead>
            <tr>
              <th>#</th>
              <th>Nom</th>
              <th>Email</th>
              <th>Rôle</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($users as $u): ?>
            <tr>
              <td style="color:var(--text-muted);"><?= $u['id_user'] ?></td>
              <td>
                <div style="display:flex;align-items:center;gap:10px;">
                  <div class="avatar" style="width:30px;height:30px;font-size:.75rem;"><?= strtoupper(substr($u['nom'],0,1)) ?></div>
                  <strong><?= htmlspecialchars($u['nom']) ?></strong>
                  <?php if ($u['id_user'] == $user['id']): ?>
                    <span class="badge badge-green" style="font-size:.68rem;">Vous</span>
                  <?php endif; ?>
                </div>
              </td>
              <td style="color:var(--text-dim);"><?= htmlspecialchars($u['email']) ?></td>
              <td>
                <?php if ($u['role'] === 'chef_projet'): ?>
                  <span class="badge badge-purple">Chef de projet</span>
                <?php else: ?>
                  <span class="badge badge-blue">Membre</span>
                <?php endif; ?>
              </td>
              <td>
                <div style="display:flex;gap:8px;">
                  <a href="#" class="btn btn-outline btn-sm">Voir</a>
                  <?php if ($u['id_user'] != $user['id']): ?>
                    <a href="#" class="btn btn-danger btn-sm">Retirer</a>
                  <?php endif; ?>
                </div>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </main>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
