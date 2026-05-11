<?php
// views/partials/404.php
$pageTitle = '404';
require __DIR__ . '/header.php';
?>
<div class="min-h-screen flex flex-col items-center justify-center text-center px-4">
    <span class="font-display text-9xl font-extrabold text-ink-100">404</span>
    <h1 class="font-display text-3xl font-bold text-ink mt-4">Page introuvable</h1>
    <p class="text-ink-500 mt-2">La page que vous cherchez n'existe pas ou a été déplacée.</p>
    <a href="projets" class="btn-primary mt-8">← Retour au tableau de bord</a>
</div>
<?php require __DIR__ . '/footer.php'; ?>
