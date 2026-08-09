<?php
// views/auth/login.php
$pageTitle = 'Connexion';
require __DIR__ . '/../partials/header.php';
?>

<div class="min-h-screen flex flex-col justify-between py-12 px-4 sm:px-6 lg:px-8 page-in relative">
    <div class="absolute top-4 right-4 z-50">
        <button onclick="toggleTheme()" class="w-9 h-9 flex items-center justify-center rounded-md border border-border text-muted-foreground hover:bg-accent hover:text-accent-foreground transition-colors bg-card" title="Changer de thème">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="w-4 h-4 hidden dark:block">
                <circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/>
            </svg>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="w-4 h-4 dark:hidden">
                <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/>
            </svg>
        </button>
    </div>
    
    <script>
    function toggleTheme() {
        if (document.documentElement.classList.contains('dark')) {
            document.documentElement.classList.remove('dark');
            localStorage.setItem('theme', 'light');
        } else {
            document.documentElement.classList.add('dark');
            localStorage.setItem('theme', 'dark');
        }
    }
    </script>

    <div class="max-w-md w-full mx-auto my-auto space-y-6">
        
        <!-- Header logo -->
        <div class="text-center">
            <a href="/" class="inline-flex items-center gap-3 group">
                <img src="/img/diplomatic_seal.jpg" alt="Logo Sceau" class="w-12 h-12 rounded-full border border-border shadow-sm group-hover:scale-105 transition-transform duration-300">
                <span class="text-3xl font-extrabold text-foreground tracking-tight">Tantana</span>
            </a>
            <h1 class="text-2xl font-bold text-foreground mt-6">Accès Sécurisé</h1>
            <p class="text-muted-foreground text-sm mt-1">Plateforme centrale des affaires diplomatiques et gouvernementales</p>
        </div>

        <!-- Login Card -->
        <div class="bg-card rounded-lg shadow-card border border-border overflow-hidden hover-lift">
            
            <div class="p-8">
                <?php require __DIR__ . '/../partials/flash.php'; ?>

                <div class="flex items-center gap-4 mb-6 p-3 bg-emerald-50/60 rounded-xl border border-emerald-100">
                    <img src="/img/signed_treaty.jpg" alt="Traité" class="w-16 h-16 rounded-lg object-cover flex-shrink-0 border border-emerald-200/60">
                    <div>
                        <p class="text-xs font-semibold text-jade uppercase tracking-wider">Espace Réservé</p>
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
                        <div class="relative">
                            <input type="password" id="password" name="password" class="t-input pr-16"
                                   placeholder="••••••••" autocomplete="current-password" required>
                            <button type="button" onclick="togglePasswordVisibility('password', this)" class="absolute inset-y-0 right-0 pr-3 flex items-center text-xs font-semibold text-slate-500 hover:text-slate-800">
                                Afficher
                            </button>
                        </div>
                    </div>

                    <script>
                    function togglePasswordVisibility(id, btn) {
                        const input = document.getElementById(id);
                        if (input.type === 'password') {
                            input.type = 'text';
                            btn.textContent = 'Masquer';
                        } else {
                            input.type = 'password';
                            btn.textContent = 'Afficher';
                        }
                    }
                    </script>

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
                <a href="register" class="font-semibold text-jade hover:underline">Demander une inscription</a>
            </div>
        </div>

    </div>

    <!-- Footer -->
    <footer class="text-center text-xs text-slate-400 mt-8">
        Développé par Ismaël Andrimalala | © <?= date('Y') ?> Tantana Agenda Diplomatique
    </footer>
</div>

<?php require __DIR__ . '/../partials/footer.php'; ?>
