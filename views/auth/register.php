<?php
// views/auth/register.php
$pageTitle = 'Inscription';
require __DIR__ . '/../partials/header.php';
?>

<div class="min-h-screen flex">

    <!-- Panneau gauche décoratif -->
    <div class="hidden lg:flex lg:w-2/5 bg-jade flex-col justify-between p-12 relative overflow-hidden">
        <svg class="absolute inset-0 w-full h-full opacity-10" viewBox="0 0 400 800" fill="none">
            <rect x="-60" y="100" width="520" height="4" rx="2" fill="white" transform="rotate(-12 -60 100)"/>
            <rect x="-60" y="200" width="520" height="2" rx="1" fill="white" transform="rotate(-12 -60 200)"/>
            <rect x="-60" y="300" width="520" height="4" rx="2" fill="white" transform="rotate(-12 -60 300)"/>
            <rect x="-60" y="400" width="520" height="2" rx="1" fill="white" transform="rotate(-12 -60 400)"/>
            <rect x="-60" y="500" width="520" height="4" rx="2" fill="white" transform="rotate(-12 -60 500)"/>
            <rect x="-60" y="600" width="520" height="2" rx="1" fill="white" transform="rotate(-12 -60 600)"/>
        </svg>

        <div class="relative z-10">
            <a href="login" class="flex items-center gap-2">
                <div class="w-10 h-10 rounded-xl bg-white/20 flex items-center justify-center">
                    <svg viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.5" class="w-5 h-5">
                        <path d="M9 3H5a2 2 0 0 0-2 2v4m6-6h10a2 2 0 0 1 2 2v4M9 3v18m0 0h10a2 2 0 0 0 2-2V9M9 21H5a2 2 0 0 1-2-2V9m0 0h18"/>
                    </svg>
                </div>
                <span class="font-display text-2xl font-bold text-white">Tantana</span>
            </a>
        </div>

        <div class="relative z-10 space-y-8">
            <h2 class="font-display text-4xl font-extrabold text-white leading-tight">
                Rejoignez<br>votre équipe<br>aujourd'hui.
            </h2>
            <div class="space-y-4">
                <?php
                $features = [
                    ['icon' => 'M5 13l4 4L19 7', 'text' => 'Gestion de projets intuitive'],
                    ['icon' => 'M17 20h5v-2a3 3 0 0 0-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 0 1 5.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 0 1 9.288 0', 'text' => 'Collaboration en équipe'],
                    ['icon' => 'M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2M9 5a2 2 0 0 0 2 2h2a2 2 0 0 0 2-2M9 5a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2', 'text' => 'Suivi des tâches en temps réel'],
                ];
                foreach ($features as $f): ?>
                <div class="flex items-center gap-3">
                    <div class="w-7 h-7 rounded-full bg-white/20 flex items-center justify-center flex-shrink-0">
                        <svg viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" class="w-3.5 h-3.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="<?= $f['icon'] ?>"/>
                        </svg>
                    </div>
                    <span class="text-white/90 text-sm"><?= $f['text'] ?></span>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="absolute -bottom-20 -right-20 w-60 h-60 bg-white/10 rounded-full blur-3xl"></div>
    </div>

    <!-- Panneau droit : formulaire -->
    <div class="flex-1 flex flex-col justify-center px-6 sm:px-12 lg:px-16 py-12 bg-white overflow-y-auto page-in">

        <?php require __DIR__ . '/../partials/flash.php'; ?>

        <div class="max-w-md w-full mx-auto">

            <div class="lg:hidden flex items-center gap-2 mb-8">
                <div class="w-8 h-8 rounded-lg bg-ink flex items-center justify-center">
                    <svg viewBox="0 0 24 24" fill="none" stroke="#00A67E" stroke-width="2.5" class="w-4 h-4">
                        <path d="M9 3H5a2 2 0 0 0-2 2v4m6-6h10a2 2 0 0 1 2 2v4M9 3v18m0 0h10a2 2 0 0 0 2-2V9M9 21H5a2 2 0 0 1-2-2V9m0 0h18"/>
                    </svg>
                </div>
                <span class="font-display text-xl font-bold">Tantana</span>
            </div>

            <h1 class="font-display text-3xl font-extrabold text-ink">Créer un compte</h1>
            <p class="text-ink-500 mt-1 text-sm">Rejoignez votre équipe sur Tantana</p>

            <form action="register" method="POST" class="mt-8 space-y-4" novalidate>

                <!-- Nom & Prénom -->
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-ink mb-1.5" for="prenom">Prénom</label>
                        <input type="text" id="prenom" name="prenom" class="t-input"
                               placeholder="Jean"
                               value="<?= e($_POST['prenom'] ?? '') ?>" required>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-ink mb-1.5" for="nom">Nom</label>
                        <input type="text" id="nom" name="nom" class="t-input"
                               placeholder="Dupont"
                               value="<?= e($_POST['nom'] ?? '') ?>" required>
                    </div>
                </div>

                <!-- Email -->
                <div>
                    <label class="block text-sm font-semibold text-ink mb-1.5" for="email">Adresse email</label>
                    <input type="email" id="email" name="email" class="t-input"
                           placeholder="vous@exemple.com"
                           value="<?= e($_POST['email'] ?? '') ?>" required>
                </div>

                <!-- Rôle -->
                <div>
                    <label class="block text-sm font-semibold text-ink mb-1.5">Rôle</label>
                    <div class="grid grid-cols-2 gap-3">
                        <?php foreach ($roles as $role): ?>
                        <label class="role-card cursor-pointer">
                            <input type="radio" name="id_role" value="<?= $role['id_role'] ?>"
                                   class="sr-only"
                                   <?= (($_POST['id_role'] ?? '1') == $role['id_role']) ? 'checked' : '' ?>>
                            <div class="border-2 rounded-xl p-3.5 transition-all duration-200 border-ink-100 hover:border-jade
                                        flex flex-col gap-1 role-box">
                                <span class="text-sm font-semibold text-ink"><?= e($role['libelle']) ?></span>
                                <span class="text-xs text-ink-500">
                                    <?= $role['libelle'] === 'Chef de projet'
                                        ? 'Créer & gérer les projets'
                                        : 'Participer aux projets' ?>
                                </span>
                            </div>
                        </label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Mot de passe -->
                <div>
                    <label class="block text-sm font-semibold text-ink mb-1.5" for="password">Mot de passe</label>
                    <input type="password" id="password" name="password" class="t-input"
                           placeholder="Min. 8 caractères"
                           autocomplete="new-password" required>
                </div>

                <!-- Confirmation -->
                <div>
                    <label class="block text-sm font-semibold text-ink mb-1.5" for="confirm">Confirmer le mot de passe</label>
                    <input type="password" id="confirm" name="confirm" class="t-input"
                           placeholder="••••••••"
                           autocomplete="new-password" required>
                </div>

                <button type="submit" class="btn-jade w-full justify-center py-3 text-base mt-2">
                    Créer mon compte
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="w-4 h-4">
                        <path d="M5 12h14M12 5l7 7-7 7"/>
                    </svg>
                </button>
            </form>

            <p class="mt-6 text-center text-sm text-ink-500">
                Déjà un compte ?
                <a href="login" class="font-semibold text-ink hover:text-jade transition-colors">Se connecter</a>
            </p>
        </div>
    </div>
</div>

<style>
/* Sélection du rôle */
.role-card input:checked + .role-box {
    border-color: #00A67E;
    background: #E6F7F2;
}
</style>

<?php require __DIR__ . '/../partials/footer.php'; ?>
