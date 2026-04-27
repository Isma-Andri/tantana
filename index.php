<?php
$pageTitle = 'Tantana — Gestion de projets collaboratifs';
require_once __DIR__ . '/includes/auth.php';
startSession();
if (isLoggedIn()) {
    header('Location: dashboard.php');
    exit;
}
require_once __DIR__ . '/includes/header.php';
?>

<section class="hero">
  <div class="hero-bg">
    <div class="hero-orb hero-orb-1"></div>
    <div class="hero-orb hero-orb-2"></div>
  </div>
  <div class="hero-content fade-up">
    <div class="hero-badge">✦ Outil de gestion de projet</div>
    <h1>Collaborez.<br><span>Organisez.</span> Avancez.</h1>
    <p>Tantana centralise vos projets, vos tâches et vos équipes dans une interface fluide et intuitive. Planifiez, suivez, livrez.</p>
    <div class="hero-actions">
      <a href="register.php" class="btn btn-primary btn-lg">Commencer gratuitement</a>
      <a href="login.php" class="btn btn-outline btn-lg">Se connecter</a>
    </div>
  </div>
</section>

<section class="features">
  <div class="container">
    <h2 class="section-title fade-up">Tout ce dont votre équipe a besoin</h2>
    <p class="section-sub fade-up delay-1">Une plateforme unique pour gérer de bout en bout vos projets.</p>
    <div class="features-grid">
      <div class="feature-card fade-up delay-1">
        <div class="feature-icon">📋</div>
        <h3>Gestion de projets</h3>
        <p>Créez, modifiez et suivez vos projets avec dates de début, fin et priorités.</p>
      </div>
      <div class="feature-card fade-up delay-2">
        <div class="feature-icon">✅</div>
        <h3>Tâches & sous-tâches</h3>
        <p>Décomposez chaque projet en tâches assignables avec statuts et dépendances.</p>
      </div>
      <div class="feature-card fade-up delay-3">
        <div class="feature-icon">👥</div>
        <h3>Collaboration d'équipe</h3>
        <p>Invitez des membres, attribuez des rôles et collaborez en temps réel.</p>
      </div>
      <div class="feature-card fade-up delay-1">
        <div class="feature-icon">💬</div>
        <h3>Commentaires</h3>
        <p>Commentez directement sur les tâches pour une communication centralisée.</p>
      </div>
      <div class="feature-card fade-up delay-2">
        <div class="feature-icon">📊</div>
        <h3>Suivi d'avancement</h3>
        <p>Visualisez la progression de chaque projet et l'implication des membres.</p>
      </div>
      <div class="feature-card fade-up delay-3">
        <div class="feature-icon">🔔</div>
        <h3>Notifications</h3>
        <p>Restez informé des actions importantes sur vos projets et tâches.</p>
      </div>
    </div>
  </div>
</section>

<section style="padding:80px 0; border-top:1px solid var(--border);">
  <div class="container" style="text-align:center;">
    <h2 class="section-title">Prêt à démarrer ?</h2>
    <p class="section-sub">Rejoignez Tantana et transformez la manière dont votre équipe travaille.</p>
    <a href="register.php" class="btn btn-primary btn-lg">Créer un compte</a>
  </div>
</section>

<footer style="border-top:1px solid var(--border); padding:24px 32px; display:flex; justify-content:space-between; align-items:center; color:var(--text-muted); font-size:.85rem;">
  <div class="navbar-brand" style="font-size:1.1rem;">Tantana</div>
  <div>© <?= date('Y') ?> ANDRIMALALA Ismaël — Tous droits réservés</div>
</footer>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
