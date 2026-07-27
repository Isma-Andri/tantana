<?php
// views/projets/create.php
$pageTitle = 'Nouveau projet';
require __DIR__ . '/../partials/header.php';
require __DIR__ . '/../partials/navbar.php';
require __DIR__ . '/../partials/flash.php';
?>

<main class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-10 page-in">

    <nav class="flex items-center gap-2 text-sm text-ink-500 mb-8">
        <a href="/projets" class="hover:text-ink transition-colors">Projets</a>
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="w-3 h-3"><path d="M9 18l6-6-6-6"/></svg>
        <span class="text-ink font-medium">Nouveau dossier</span>
    </nav>

    <div class="bg-white rounded-2xl shadow-card overflow-hidden">
        <div class="bg-[#064e3b] px-8 py-6 flex items-center justify-between gap-4">
            <div>
                <h1 class="font-display text-2xl font-bold text-white">Nouveau dossier de politique</h1>
                <p class="text-white/70 text-sm mt-1">Définissez les informations essentielles de votre dossier.</p>
            </div>
            <img src="/img/signed_treaty.jpg" alt="Traité" class="w-16 h-16 rounded-xl object-cover border-2 border-white/20 shadow-sm flex-shrink-0 hidden sm:block">
        </div>

        <form action="/projets/create" method="POST" class="p-8 space-y-6" novalidate>
            <div>
                <label class="block text-sm font-semibold text-ink mb-1.5" for="nom">
                    Nom du dossier <span class="text-rose">*</span>
                </label>
                <input type="text" id="nom" name="nom" class="t-input"
                       placeholder="Ex : Refonte du site web"
                       value="<?= e($_POST['nom'] ?? '') ?>"
                       maxlength="255" required autofocus>
            </div>

            <div>
                <label class="block text-sm font-semibold text-ink mb-1.5" for="description">Description</label>
                <textarea id="description" name="description" rows="4" class="t-input resize-none"
                          placeholder="Décrivez les objectifs et le contexte du projet…"
                          maxlength="2000"><?= e($_POST['description'] ?? '') ?></textarea>
                <p class="text-xs text-ink-500 mt-1">Optionnel, max 2000 caractères</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <?php
                $dateFields = [
                    ['date_debut',  'Date de début', ''],
                    ['date_fin',    'Date de fin',   ''],
                    ['date_limite', 'Date limite',   '(deadline)'],
                ];
                foreach ($dateFields as [$name, $label, $hint]): ?>
                <div>
                    <label class="block text-sm font-semibold text-ink mb-1.5" for="<?= $name ?>">
                        <?= $label ?>
                        <?php if ($hint): ?><span class="text-xs font-normal text-rose"><?= $hint ?></span><?php endif; ?>
                    </label>
                    <input type="date" id="<?= $name ?>" name="<?= $name ?>" class="t-input"
                           value="<?= e($_POST[$name] ?? '') ?>">
                </div>
                <?php endforeach; ?>
            </div>

            <div class="flex items-center gap-3 pt-2">
                <button type="submit" class="btn-jade">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="w-4 h-4"><path d="M12 5v14M5 12h14"/></svg>
                    Créer le dossier
                </button>
                <a href="/projets" class="btn-ghost">Annuler</a>
            </div>
        </form>
    </div>

</main>

<?php require __DIR__ . '/../partials/footer.php'; ?>
