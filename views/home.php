<?php
// views/home.php
$pageTitle = 'Accueil';
require __DIR__ . '/partials/header.php';
?>
<style>
body {
    background-color: #fcfbf9;
    color: #0f172a;
    font-family: 'DM Sans', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
    margin: 0;
}
.landing-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 2rem;
    min-height: 100vh;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
}
.navbar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 2rem 0;
}
.logo-title {
    font-weight: 700;
    font-size: 1.5rem;
    color: hsl(var(--jade));
    text-decoration: none;
    display: flex;
    align-items: center;
    gap: 0.75rem;
}
.nav-links a {
    color: #475569;
    text-decoration: none;
    font-size: 0.875rem;
    font-weight: 500;
    margin-left: 2rem;
    transition: color 0.2s;
}
.nav-links a:hover {
    color: hsl(var(--jade));
}
.hero-section {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 4rem;
    padding: 4rem 0;
    flex: 1;
}
.hero-content {
    max-width: 550px;
}
.hero-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.375rem 0.75rem;
    border-radius: 9999px;
    background-color: #ecfdf5;
    border: 1px solid #a7f3d0;
    color: #065f46;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    margin-bottom: 1.5rem;
}
.hero-title {
    font-size: 3.5rem;
    font-weight: 800;
    line-height: 1.15;
    color: #0f172a;
    margin-bottom: 1.5rem;
}
.hero-title span {
    color: hsl(var(--jade));
}
.hero-desc {
    font-size: 1.125rem;
    line-height: 1.6;
    color: #475569;
    margin-bottom: 2.5rem;
}
.hero-actions {
    display: flex;
    gap: 1rem;
}
.btn-primary {
    background-color: hsl(var(--jade));
    color: #ffffff;
    padding: 0.75rem 2rem;
    border-radius: 0.5rem;
    font-weight: 600;
    text-decoration: none;
    transition: background-color 0.2s;
    border: 1px solid transparent;
}
.btn-primary:hover {
    background-color: #047857;
}
.btn-secondary {
    background-color: transparent;
    color: #334155;
    padding: 0.75rem 2rem;
    border-radius: 0.5rem;
    font-weight: 600;
    text-decoration: none;
    border: 1px solid #cbd5e1;
    transition: background-color 0.2s, border-color 0.2s;
}
.btn-secondary:hover {
    background-color: #f1f5f9;
    border-color: #94a3b8;
}
.hero-image-container {
    flex: 1;
    display: flex;
    justify-content: center;
    align-items: center;
}
.seal-img {
    max-width: 400px;
    width: 100%;
    border-radius: 50%;
    border: 8px solid #ffffff;
    box-shadow: 0 10px 30px rgba(15, 23, 42, 0.08);
}
.landing-footer {
    border-top: 1px solid #e2e8f0;
    padding: 2rem 0;
    display: flex;
    justify-content: space-between;
    align-items: center;
    color: #64748b;
    font-size: 0.875rem;
}
@media (max-width: 968px) {
    .hero-section {
        flex-direction: column-reverse;
        text-align: center;
        gap: 2rem;
        padding: 2rem 0;
    }
    .hero-actions {
        justify-content: center;
    }
    .seal-img {
        max-width: 250px;
    }
    .landing-footer {
        flex-direction: column;
        gap: 1rem;
    }
}
</style>

<div class="landing-container">
    <header class="navbar">
        <a href="/" class="logo-title" style="letter-spacing: 0.25em; font-weight: 900; text-transform: uppercase; font-size: 1.5rem;">
            T<span style="font-weight: 300; opacity: 0.7;">A</span>N<span style="font-weight: 300; opacity: 0.7;">T</span>A<span style="font-weight: 300; opacity: 0.7;">N</span>A
        </a>
        <nav class="nav-links">
            <a href="/login">Se connecter</a>
            <a href="/register">S'inscrire</a>
        </nav>
    </header>

    <main class="hero-section">
        <div class="hero-content">
            <div class="hero-badge">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M20 6L9 17l-5-5"/></svg>
                Agenda Diplomatique
            </div>
            <h1 class="hero-title">
                Décisions et<br>
                <span>dossiers d'État</span>
            </h1>
            <p class="hero-desc">
                Centralisez vos accords, résolutions et pièces jointes. Tantana sécurise le partage d'informations sensibles entre ministères, partenaires et collaborateurs clés.
            </p>
            <div class="hero-actions">
                <a href="/login" class="btn-primary">Accéder à l'espace</a>
                <a href="/register" class="btn-secondary">Créer un compte</a>
            </div>
        </div>
        
        <div class="hero-image-container">
            <img src="/img/diplomatic_seal.jpg" alt="Sceau Officiel de l'État" class="seal-img">
        </div>
    </main>

    <footer class="landing-footer">
        <span>Tantana — Gestion d'agenda diplomatique</span>
        <span>Développé par Ismaël Andrimalala | © <?= date('Y') ?></span>
    </footer>
</div>
</body>
</html>
