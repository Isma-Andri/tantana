<?php
// views/auth/login.php
$pageTitle = 'Connexion';
require __DIR__ . '/../partials/header.php';
?>

<div class="min-h-screen flex">

    <!-- Panneau gauche décoratif -->
    <div class="hidden lg:flex lg:w-1/2 bg-ink flex-col justify-between p-12 relative overflow-hidden">
        <!-- Motif géométrique SVG de fond -->
        <svg class="absolute inset-0 w-full h-full opacity-5" viewBox="0 0 500 800" fill="none">
            <circle cx="250" cy="400" r="350" stroke="white" stroke-width="60"/>
            <circle cx="250" cy="400" r="200" stroke="white" stroke-width="40"/>
            <circle cx="250" cy="400" r="80"  stroke="white" stroke-width="20"/>
            <line x1="0" y1="0" x2="500" y2="800" stroke="white" stroke-width="2"/>
            <line x1="500" y1="0" x2="0" y2="800" stroke="white" stroke-width="2"/>
        </svg>

        <div class="relative z-10">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-jade flex items-center justify-center">
                    <svg viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.5" class="w-5 h-5">
                        <path d="M9 3H5a2 2 0 0 0-2 2v4m6-6h10a2 2 0 0 1 2 2v4M9 3v18m0 0h10a2 2 0 0 0 2-2V9M9 21H5a2 2 0 0 1-2-2V9m0 0h18"/>
                    </svg>
                </div>
                <span class="font-display text-2xl font-bold text-white">Tantana</span>
            </div>
        </div>

        <div class="relative z-10 space-y-6">
            <blockquote class="text-white/80 text-lg leading-relaxed italic font-light">
                "Organiser, collaborer, livrer.<br>Votre équipe mérite un outil à la hauteur."
            </blockquote>
            <div class="flex gap-3">
                <div class="w-8 h-8 rounded-full bg-white/10 backdrop-blur flex items-center justify-center">
                    <svg viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" class="w-4 h-4">
                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2M9 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8zM23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/>
                    </svg>
                </div>
                <div>
                    <p class="text-white text-sm font-semibold">Gestion d'équipe simplifiée</p>
                    <p class="text-white/60 text-xs">Membres, chefs de projet, tâches</p>
                </div>
            </div>
        </div>

        <!-- Accent jade -->
        <div class="absolute bottom-0 right-0 w-64 h-64 bg-jade/20 rounded-full blur-3xl"></div>
    </div>

    <!-- Panneau droit : formulaire -->
    <div class="flex-1 flex flex-col justify-center px-6 sm:px-12 lg:px-16 py-12 bg-white page-in">

        <?php require __DIR__ . '/../partials/flash.php'; ?>

        <div class="max-w-sm w-full mx-auto">

            <!-- Mobile logo -->
            <div class="lg:hidden flex items-center gap-2 mb-10">
                <div class="w-8 h-8 rounded-lg bg-ink flex items-center justify-center">
                    <svg viewBox="0 0 24 24" fill="none" stroke="#00A67E" stroke-width="2.5" class="w-4 h-4">
                        <path d="M9 3H5a2 2 0 0 0-2 2v4m6-6h10a2 2 0 0 1 2 2v4M9 3v18m0 0h10a2 2 0 0 0 2-2V9M9 21H5a2 2 0 0 1-2-2V9m0 0h18"/>
                    </svg>
                </div>
                <span class="font-display text-xl font-bold">Tantana</span>
            </div>

            <h1 class="font-display text-3xl font-extrabold text-ink">Bon retour 👋</h1>
            <p class="text-ink-500 mt-1 text-sm">Connectez-vous à votre espace de travail</p>

            <form action="login" method="POST" class="mt-8 space-y-5" novalidate>

                <div>
                    <label class="block text-sm font-semibold text-ink mb-1.5" for="email">
                        Adresse email
                    </label>
                    <input type="email" id="email" name="email" class="t-input"
                           placeholder="vous@exemple.com"
                           value="<?= e($_POST['email'] ?? '') ?>"
                           autocomplete="email" required>
                </div>

                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label class="text-sm font-semibold text-ink" for="password">Mot de passe</label>
                    </div>
                    <input type="password" id="password" name="password" class="t-input"
                           placeholder="••••••••"
                           autocomplete="current-password" required>
                </div>

                <button type="submit" class="btn-primary w-full justify-center py-3 text-base">
                    Se connecter
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="w-4 h-4">
                        <path d="M5 12h14M12 5l7 7-7 7"/>
                    </svg>
                </button>

            </form>

            <p class="mt-6 text-center text-sm text-ink-500">
                Pas encore de compte ?
                <a href="register" class="font-semibold text-jade hover:text-jade-dark transition-colors">Créer un compte</a>
            </p>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../partials/footer.php'; ?>
