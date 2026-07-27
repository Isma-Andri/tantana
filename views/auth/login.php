<?php
// views/auth/login.php
$pageTitle = 'Connexion';
require __DIR__ . '/../partials/header.php';
?>

<div class="min-h-screen flex flex-col justify-between py-12 px-4 sm:px-6 lg:px-8 page-in">
    <div class="max-w-md w-full mx-auto my-auto space-y-6">
        
        <!-- Header logo -->
        <div class="text-center">
            <a href="/" class="inline-flex items-center gap-3 group">
                <img src="/img/diplomatic_seal.jpg" alt="Logo Sceau" class="w-12 h-12 rounded-full border-2 border-[#064e3b] shadow-sm group-hover:scale-105 transition-transform duration-300">
                <span class="font-display text-3xl font-extrabold text-slate-900 tracking-tight">Tantana</span>
            </a>
            <h1 class="font-display text-2xl font-bold text-slate-900 mt-6">Accès Sécurisé</h1>
            <p class="text-slate-500 text-sm mt-1">Plateforme centrale des affaires diplomatiques et gouvernementales</p>
        </div>

        <!-- Login Card -->
        <div class="bg-white rounded-2xl shadow-card border border-slate-200/80 overflow-hidden hover-lift">
            <div class="h-2 bg-[#064e3b]"></div>
            
            <div class="p-8">
                <?php require __DIR__ . '/../partials/flash.php'; ?>

                <div class="flex items-center gap-4 mb-6 p-3 bg-emerald-50/60 rounded-xl border border-emerald-100">
                    <img src="/img/signed_treaty.jpg" alt="Traité" class="w-16 h-16 rounded-lg object-cover flex-shrink-0 border border-emerald-200/60">
                    <div>
                        <p class="text-xs font-semibold text-[#064e3b] uppercase tracking-wider">Espace Réservé</p>
                        <p class="text-xs text-slate-600 mt-0.5">Authentification requise pour la consultation et le suivi des dossiers d'État.</p>
                    </div>
                </div>

                <form action="login" method="POST" class="space-y-5" novalidate>
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1.5" for="email">
                            Adresse Email Officielle
                        </label>
                        <input type="email" id="email" name="email" class="t-input"
                               placeholder="nom@gov.mg"
                               value="<?= e($_POST['email'] ?? '') ?>"
                               autocomplete="email" required autofocus>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1.5" for="password">
                            Mot de Passe
                        </label>
                        <input type="password" id="password" name="password" class="t-input"
                               placeholder="••••••••" autocomplete="current-password" required>
                    </div>

                    <button type="submit" class="btn-primary w-full justify-center py-3 text-sm mt-2">
                        Se connecter à l'espace
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="w-4 h-4">
                            <path d="M5 12h14M12 5l7 7-7 7"/>
                        </svg>
                    </button>
                </form>
            </div>

            <div class="bg-slate-50 border-t border-slate-100 p-4 text-center text-xs text-slate-500">
                Pas encore de compte ? 
                <a href="register" class="font-semibold text-[#064e3b] hover:underline">Demander une inscription</a>
            </div>
        </div>

    </div>

    <!-- Footer -->
    <footer class="text-center text-xs text-slate-400 mt-8">
        Développé par Ismaël Andrimalala | © <?= date('Y') ?> Tantana Agenda Diplomatique
    </footer>
</div>

<?php require __DIR__ . '/../partials/footer.php'; ?>
